<?php

namespace App\Libraries;

use App\Models\BorrowTransactionModel;
use DomainException;

/**
 * ✅ VALIDATOR LAYER
 * 
 * Encapsulates all business rule validation.
 * Checks preconditions before operations execute.
 * 
 * Separation of concerns:
 * - Validator: "Is this operation allowed?"
 * - Repository: "How to execute in DB?"
 * - Service: "Coordinate the workflow"
 */
class BorrowValidator
{
    protected $borrowModel;
    protected $repository;

    public function __construct(BorrowRepository $repository = null)
    {
        $this->borrowModel = new BorrowTransactionModel();
        $this->repository = $repository ?? new BorrowRepository();
    }

    /**
     * ✅ VALIDATE APPROVAL REQUEST
     * 
     * Checks all preconditions for approval:
     * - Transaction exists
     * - Status is Pending
     * - Status transition is allowed
     * - Folder exists and is Available
     * 
     * @param int $transactionId
     * @param int $adminId
     * @throws DomainException If any validation fails
     * @return array           The transaction
     */
    public function validateApprovalRequest($transactionId, $adminId)
    {
        $borrow = $this->repository->find($transactionId);

        if (!$borrow) {
            throw new DomainException("Transaction {$transactionId} not found");
        }

        // ✅ IDEMPOTENCY CHECK: Is this already approved/returned?
        if ($borrow['status'] !== 'Pending') {
            throw new DomainException(
                "Cannot approve transaction in '{$borrow['status']}' status. " .
                "Only 'Pending' requests can be approved. " .
                "(If you approved this already, this is idempotent—no action needed.)"
            );
        }

        // ✅ BUSINESS RULE: Status transition allowed?
        if (!$this->borrowModel->isValidTransition($borrow['status'], 'Borrowed')) {
            throw new DomainException('Invalid status transition: Pending → Borrowed failed validation');
        }

        // ✅ BUSINESS RULE: Folder must exist and be available
        $folder = $this->borrowModel->db->table('folders')
                                        ->where('folder_id', $borrow['folder_id'])
                                        ->get()
                                        ->getRow();
        if (!$folder) {
            throw new DomainException("Folder {$borrow['folder_id']} not found");
        }

        // Note: We don't check folder.status = 'Available' here
        // That's checked atomically in the repository
        // This is just a pre-flight check

        return $borrow;
    }

    /**
     * ✅ VALIDATE RETURN REQUEST
     * 
     * Checks all preconditions for return:
     * - Transaction exists
     * - Status is Borrowed
     * - Status transition is allowed
     * - User is owner or admin
     * 
     * @param int $transactionId
     * @param int $userId
     * @param string $userRole
     * @throws DomainException If any validation fails
     * @return array           The transaction
     */
    public function validateReturnRequest($transactionId, $userId, $userRole)
    {
        $borrow = $this->repository->find($transactionId);

        if (!$borrow) {
            throw new DomainException("Transaction {$transactionId} not found");
        }

        // Normalize legacy/computed states that are still logically returnable.
        $statusForReturn = $this->normalizeReturnableStatus($borrow['status']);

        // ✅ IDEMPOTENCY CHECK: Is this already returned or not returnable?
        if ($statusForReturn !== 'Borrowed') {
            throw new DomainException(
                "Cannot return transaction in '{$borrow['status']}' status. " .
                "Only 'Borrowed', 'Overdue', or legacy 'Active' items can be returned. " .
                "(If you returned this already, this is idempotent—no action needed.)"
            );
        }

        // ✅ BUSINESS RULE: Status transition allowed?
        if (!$this->borrowModel->isValidTransition($statusForReturn, 'Returned')) {
            throw new DomainException("Invalid status transition: {$borrow['status']} → Returned failed validation");
        }

        // ✅ SECURITY: IDOR Protection
        $isAdmin = in_array($userRole, ['admin', 'super_admin']);
        $isCreator = $borrow['created_by'] === $userId;

        if (!$isCreator && !$isAdmin) {
            throw new DomainException(
                "User {$userId} is not authorized to return transaction {$transactionId}. " .
                "(Only the creator or admin can return borrows.)"
            );
        }

        return $borrow;
    }

    private function normalizeReturnableStatus(string $status): string
    {
        if (in_array($status, ['Overdue', 'Active'], true)) {
            return 'Borrowed';
        }

        return $status;
    }

    /**
     * ✅ VALIDATE REQUEST FOR DUPLICATE PREVENTION
     * 
     * Checks if user already has a pending request for this folder.
     * Used to prevent accidental duplicate submissions.
     * 
     * @param int $userId
     * @param int $folderId
     * @throws DomainException If duplicate pending request exists
     */
    public function validateNoDuplicatePending($userId, $folderId)
    {
        $count = $this->repository->countPendingForUserFolder($userId, $folderId);

        if ($count > 0) {
            throw new DomainException(
                "You already have a pending request for this folder. " .
                "Please wait for approval or cancel the previous request before submitting a new one."
            );
        }
    }

    /**
     * ✅ VALIDATE DATE LOGIC (Server-side)
     * 
     * Ensures dates make sense:
     * - expected_return_date > borrowed_at
     * - borrowed_at is not in the future
     * 
     * @param array $data
     * @throws DomainException If dates are invalid
     */
    public function validateDates($data)
    {
        if (!$this->borrowModel->validateDates($data)) {
            throw new DomainException(
                "Date validation failed: " . json_encode($this->borrowModel->errors)
            );
        }
    }

    /**
     * Check if transaction is overdue
     */
    public function isOverdue($borrow)
    {
        return $this->borrowModel->isOverdue($borrow);
    }

    /**
     * Get computed display status
     */
    public function getDisplayStatus($borrow)
    {
        return $this->borrowModel->calculateStatus($borrow);
    }
}
