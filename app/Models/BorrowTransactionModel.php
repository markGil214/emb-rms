<?php

namespace App\Models;

use CodeIgniter\Model;

class BorrowTransactionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'borrow_transactions';
    protected $primaryKey       = 'transaction_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    /**
     * ✅ STATUS LIFECYCLE (STORED ONLY)
     * 
     * Valid stored values:
     * - "Pending": Initial state, awaiting admin approval
     * - "Borrowed": Approved and released, currently with borrower
     * - "Returned": Transaction complete, item returned
     * 
     * ⚠️ "Overdue" is NOT STORED
     * It's computed on-the-fly:
     *   IF status = 'Borrowed' AND expected_return_date < NOW()
     *   → display as 'Overdue'
     * 
     * ✅ AUDIT FIELDS (REQUIRED)
     * - approved_at: When approved
     * - approved_by: Who approved
     * - borrowed_at: When released to borrower  
     * - actual_return_date: When returned
     * - received_by: Who marked as returned
     * - created_by: Who requested the borrow
     */
    protected $allowedFields    = [
        'folder_id', 'borrower_name', 'borrower_email', 'purpose',
        'borrowed_at', 'expected_return_date', 'actual_return_date',
        'status', 'released_by', 'received_by', 'return_notes',
        'approved_at', 'approved_by', 'created_by',
        'notification_status', 'last_notification_sent_at', 'escalated_to_manager'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = true;  // ✅ Enable soft deletes for audit trail
    protected $deletedField  = 'deleted_at';

    /**
     * ✅ ALLOWED STATUS TRANSITIONS (Data Integrity)
     * 
     * Prevents invalid state changes:
     * - Pending can only go to Borrowed
     * - Borrowed can only go to Returned
     * - Can never go backwards
     */
    protected const ALLOWED_TRANSITIONS = [
        'Pending'  => ['Borrowed'],
        'Borrowed' => ['Returned'],
        'Returned' => [],  // Terminal state
    ];

    /**
     * ✅ VALIDATION RULES FOR CREATION
     * 
     * Status is auto-set to 'Pending' on server, NOT submitted by form
     * User only provides: folder_id, borrower_name, expected_return_date
     */
    protected $validationRules = [
        'folder_id'             => 'required|integer|is_natural_no_zero',
        'expected_return_date'  => 'required|valid_date[Y-m-d]',
        'borrower_name'         => 'required|string|min_length[2]|max_length[255]',
        'borrower_email'        => 'required|valid_email',
        // Status is NOT in creation validation—auto-set to 'Pending'
    ];

    /**
     * ✅ VALIDATION RULES FOR UPDATES (Admin operations)
     * 
     * When admin changes status via approve/return, status must be valid
     */
    protected $validationRulesUpdate = [
        'status'                => 'in_list[Pending,Borrowed,Returned]',
    ];

    protected $validationMessages = [
        'folder_id' => [
            'required' => 'Folder is required',
            'integer' => 'Folder ID must be a valid number',
        ],
        'expected_return_date' => [
            'required' => 'Expected return date is required',
            'valid_date' => 'Please enter a valid date',
        ],
        'borrower_name' => [
            'required' => 'Borrower name is required',
            'string' => 'Borrower name must be text',
            'min_length' => 'Borrower name must be at least 2 characters',
            'max_length' => 'Borrower name must not exceed 255 characters',
        ],
    ];

    /**
     * ✅ MODEL-LEVEL VALIDATION HOOK
     * 
     * Ensures validateDates() is called before ANY insert operation
     * (even if not going through controller's explicit validation)
     * 
     * @param array $data The data being inserted
     * @return array       Modified data if validation passes
     */
    protected function beforeInsert(array $data): array
    {
        // ✅ Validate date logic at model level
        if (!$this->validateDates($data['data'] ?? [])) {
            // Validation failed - errors are in $this->errors
            throw new \InvalidArgumentException('Date validation failed: ' . json_encode($this->errors));
        }

        return $data;
    }

    /**
     * ✅ MODEL-LEVEL VALIDATION HOOK
     * 
     * Ensures validateDates() is called before ANY update operation
     * 
     * @param array $data The data being updated
     * @return array       Modified data if validation passes
     */
    protected function beforeUpdate(array $data): array
    {
        // ✅ Validate date logic at model level if dates are being updated
        if (isset($data['data']['expected_return_date']) || isset($data['data']['borrowed_at'])) {
            if (!$this->validateDates($data['data'] ?? [])) {
                throw new \InvalidArgumentException('Date validation failed: ' . json_encode($this->errors));
            }
        }

        return $data;
    }

    /**
     * Get all pending borrow requests
     */
    public function getPending()
    {
        return $this->where('status', 'Pending')->findAll();
    }

    /**
     * Get all approved/borrowed items
     */
    public function getActive()
    {
        return $this->where('status', 'Borrowed')->findAll();
    }

    /**
     * Get overdue items
     */
    public function getOverdue()
    {
        return $this->where('status', 'Overdue')->findAll();
    }

    /**
     * Get items ready for X-day notification
     * Checks if X days overdue and notification not yet sent
     * 
     * @param int $days Days overdue threshold (1, 3, 7)
     * @return array Array of borrow transactions
     */
    public function getReadyForNotification($days)
    {
        $daysAgo = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('status', 'Borrowed')
                    ->where('actual_return_date IS NULL')
                    ->where('expected_return_date <', $daysAgo)
                    ->findAll();
    }

    /**
     * Get all overdue items for reporting/dashboard
     * 
     * @return array Array of overdue borrow transactions, ordered by due date
     */
    public function getAllOverdue()
    {
        return $this->where('status', 'Borrowed')
                    ->where('expected_return_date <', date('Y-m-d H:i:s'))
                    ->where('actual_return_date IS NULL')
                    ->orderBy('expected_return_date', 'ASC')
                    ->findAll();
    }

    /**
     * Get overdue items by days threshold
     * 
     * @param int $days Days overdue (1, 3, 7)
     * @return array Array of items overdue X days
     */
    public function getOverdueByDays($days = null)
    {
        $query = $this->where('status', 'Borrowed')
                      ->where('expected_return_date <', date('Y-m-d H:i:s'))
                      ->where('actual_return_date IS NULL');
        
        if ($days !== null) {
            $daysAgo = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $query = $query->where('expected_return_date <', $daysAgo);
        }
        
        return $query->findAll();
    }

    /**
     * Calculate actual status based on dates
     * 
     * ⚠️ IMPORTANT: This is COMPUTED, not STORED
     * 
     * Only these statuses are stored in DB:
     * - Pending: Awaiting admin approval
     * - Approved: Admin approved, awaiting release
     * - Borrowed: Currently in borrower's possession  
     * - Returned: Item returned, transaction complete
     * 
     * Overdue is COMPUTED on-the-fly:
     * IF status = 'Borrowed' AND due_date < now()
     *   → display as 'Overdue' (BUT NOT STORED)
     * 
     * ✅ Benefits:
     * - Always accurate (no stale data)
     * - No cron jobs needed
     * - Prevents data integrity issues
     */
    public function calculateStatus($borrow)
    {
        // Pending stays pending
        if ($borrow['status'] === 'Pending') {
            return 'Pending';
        }

        // If returned, show as returned
        if ($borrow['actual_return_date']) {
            return 'Returned';
        }

        // Check if overdue (COMPUTED, not stored)
        if ($this->isOverdue($borrow)) {
            return 'Overdue'; // Display as Overdue, but DB still says 'Borrowed'
        }

        // Otherwise return the stored status
        return $borrow['status'];
    }

    /**
     * Check if transaction is overdue (borrowed but past due date)
     * 
     * ✅ Uses timezone-aware comparison
     */
    public function isOverdue($borrow): bool
    {
        if ($borrow['status'] !== 'Borrowed' || $borrow['actual_return_date']) {
            return false;
        }
        
        // ✅ Use Time for timezone-aware comparison
        $now = new \CodeIgniter\I18n\Time('now', app_timezone());
        $dueDate = new \CodeIgniter\I18n\Time($borrow['expected_return_date'], app_timezone());
        return $dueDate < $now;
    }

    /**
     * Get borrow history for a user
     */
    /**
     * Get all borrow requests created by a specific user
     * ✅ Uses created_by field (who created the request)
     */
    public function getUserBorrows($userId)
    {
        return $this->where('created_by', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get borrow history for a folder
     */
    public function getFolderBorrows($folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('borrowed_at', 'DESC')
                    ->findAll();
    }

    /**
     * Mark item as returned
     */
    public function markReturned($transactionId)
    {
        return $this->update($transactionId, [
            'status' => 'Returned',
            'actual_return_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Approve pending borrow request
     */
    public function approveBorrow($transactionId)
    {
        return $this->update($transactionId, [
            'status' => 'Active',
        ]);
    }

    /**
     * ✅ VALIDATE STATUS TRANSITION (Data Integrity Guard)
     * 
     * Prevents invalid state changes like:
     * - Returned → Borrowed
     * - Pending → Returned
     * - Borrowed → Pending
     * 
     * @param string $currentStatus Current DB status
     * @param string $newStatus     Desired new status
     * @return bool                 True if transition is allowed
     */
    public function isValidTransition(string $currentStatus, string $newStatus): bool
    {
        // Ensure both statuses are valid
        if (!isset(self::ALLOWED_TRANSITIONS[$currentStatus])) {
            return false;
        }

        // Check if new status is in allowed transitions for current status
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$currentStatus]);
    }

    /**
     * ✅ VALIDATE SERVER-SIDE DATE LOGIC
     * 
     * Prevents invalid date configurations:
     * - expected_return_date must be after borrowed_at
     * - borrowed_at must not be in future
     * 
     * @param array $data The borrow data
     * @return bool       True if dates are valid
     */
    /**
     * ✅ VALIDATE DATES WITH TIMEZONE CONSISTENCY
     * 
     * Uses CodeIgniter Time class to ensure all comparisons
     * respect the configured app timezone (America/Chicago)
     */
    public function validateDates(array $data): bool
    {
        // ✅ Use Time for timezone-aware comparison
        $now = new \CodeIgniter\I18n\Time('now', app_timezone());
        
        // If borrowed_at is set, it must not be in future
        if (isset($data['borrowed_at'])) {
            $borrowedAt = new \CodeIgniter\I18n\Time($data['borrowed_at'], app_timezone());
            if ($borrowedAt > $now) {
                $this->errors['borrowed_at'] = 'Borrow date cannot be in the future';
                return false;
            }
        }

        // expected_return_date must be after borrowed_at (or now if not yet borrowed)
        $borrowedTime = isset($data['borrowed_at']) 
            ? new \CodeIgniter\I18n\Time($data['borrowed_at'], app_timezone())
            : $now;
        $returnDate = new \CodeIgniter\I18n\Time($data['expected_return_date'], app_timezone());
        if ($returnDate <= $borrowedTime) {
            $this->errors['expected_return_date'] = 'Expected return date must be after borrow date';
            return false;
        }

        return true;
    }

    /**
     * Reject pending borrow request (Soft Delete)
     */
    public function rejectBorrow($transactionId)
    {
        return $this->delete($transactionId);  // Uses soft delete via useSoftDeletes
    }
}
