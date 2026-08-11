<?php

namespace App\Libraries;

use App\Models\BorrowTransactionModel;
use App\Models\FolderModel;
use CodeIgniter\I18n\Time;
use DomainException;

/**
 * ✅ REPOSITORY LAYER
 * 
 * Encapsulates all database operations for borrow transactions.
 * Provides atomic operations with proper transaction handling.
 * 
 * Separation of concerns:
 * - Repository: "How to talk to DB"
 * - Service: "What operations to perform"
 * - Validator: "What rules to enforce"
 */
class BorrowRepository
{
    protected $borrowModel;
    protected $folderModel;

    public function __construct()
    {
        $this->borrowModel = new BorrowTransactionModel();
        $this->folderModel = new FolderModel();
    }

    /**
     * ✅ ATOMIC APPROVAL OPERATION
     * 
     * Executes approval in single transaction:
     * 1. Lock folder (UPDATE with status check)
     * 2. Update transaction status
     * 3. Commit or rollback atomically
     * 
     * @param int $transactionId
     * @param int $adminId
     * @param string $now
     * @return bool            True on success
     * @throws DomainException If folder no longer available
     */
    public function executeApproval($transactionId, $adminId, $now)
    {
        $borrow = $this->borrowModel->find($transactionId);
        if (!$borrow) {
            throw new DomainException("Transaction {$transactionId} not found");
        }

        $db = \Config\Database::connect();
        $folderId = $borrow['folder_id'];

        // ✅ START TRANSACTION
        $db->transStart();

        try {
            // ✅ ATOMIC: Only lock folder if Available (prevents double borrowing)
            $result = $db->table('folders')
                         ->where('folder_id', $folderId)
                         ->where('status', 'Available')
                         ->update([
                             'status' => 'Borrowed',
                             'current_borrow_transaction_id' => $transactionId,
                         ]);

            if ($result === 0) {
                throw new DomainException(
                    "Folder {$folderId} is no longer available for borrowing. " .
                    "(Likely already approved by another admin.)"
                );
            }

            // ✅ Update transaction with approval metadata
            $this->borrowModel->update($transactionId, [
                'status' => 'Borrowed',
                'approved_at' => $now,
                'approved_by' => $adminId,
                'borrowed_at' => $now,
                'released_by' => $adminId,
            ]);

            // ✅ COMPLETE TRANSACTION
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DomainException('Database transaction failed. Changes were rolled back.');
            }

            return true;

        } catch (\Throwable $e) {
            // transComplete() already handled rollback
            throw $e;
        }
    }

    /**
     * ✅ ATOMIC RETURN OPERATION
     * 
     * Executes return in single transaction:
     * 1. Update transaction status
     * 2. Unlock folder (UPDATE with status check)
     * 3. Commit or rollback atomically
     * 
     * @param int $transactionId
     * @param int $userId
     * @param string $now
     * @return bool                True on success
     * @throws DomainException     If folder state mismatch
     */
    public function executeReturn($transactionId, $userId, $now)
    {
        $borrow = $this->borrowModel->find($transactionId);
        if (!$borrow) {
            throw new DomainException("Transaction {$transactionId} not found");
        }

        $db = \Config\Database::connect();
        $folderId = $borrow['folder_id'];

        // ✅ START TRANSACTION
        $db->transStart();

        try {
            // ✅ Update transaction status
            $this->borrowModel->update($transactionId, [
                'status' => 'Returned',
                'actual_return_date' => $now,
                'received_by' => $userId,
            ]);

            // ✅ ATOMIC: Only unlock folder if currently Borrowed
            // Prevents unlocking wrong folder
            // Also clears current_borrow_transaction_id, which otherwise
            // keeps pointing at this now-Returned transaction forever.
            $result = $db->table('folders')
                         ->where('folder_id', $folderId)
                         ->where('status', 'Borrowed')
                         ->update([
                             'status' => 'Available',
                             'current_borrow_transaction_id' => null,
                         ]);

            if ($result === 0) {
                $currentFolder = $this->folderModel->find($folderId);
                throw new DomainException(
                    "Folder {$folderId} state mismatch. " .
                    "Transaction says Borrowed but folder is {$currentFolder['status']}. " .
                    "Data corruption detected or concurrent modification."
                );
            }

            // ✅ COMPLETE TRANSACTION
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DomainException('Database transaction failed. Changes were rolled back.');
            }

            return true;

        } catch (\Throwable $e) {
            // transComplete() already handled rollback
            throw $e;
        }
    }

    /**
     * Get transaction by ID
     */
    public function find($id)
    {
        return $this->borrowModel->find($id);
    }

    /**
     * Check if folder is currently borrowed
     */
    public function isFolderBorrowed($folderId)
    {
        $result = $this->borrowModel->where('folder_id', $folderId)
                                    ->where('status', 'Borrowed')
                                    ->where('actual_return_date IS NULL')
                                    ->first();
        return !empty($result);
    }

    /**
     * Count pending requests for a user on a folder
     * (Used for duplicate prevention)
     */
    public function countPendingForUserFolder($userId, $folderId)
    {
        return $this->borrowModel->where('created_by', $userId)
                                 ->where('folder_id', $folderId)
                                 ->where('status', 'Pending')
                                 ->where('actual_return_date IS NULL')
                                 ->countAllResults();
    }

    /**
     * Get all pending requests
     */
    public function getPending()
    {
        return $this->borrowModel->where('status', 'Pending')->findAll();
    }

    /**
     * Get all borrowed items (not yet returned)
     */
    public function getBorrowed()
    {
        return $this->borrowModel->where('status', 'Borrowed')
                                ->where('actual_return_date IS NULL')
                                ->orderBy('expected_return_date', 'ASC')
                                ->findAll();
    }

    /**
     * Get all active transactions (Pending or Borrowed, not returned)
     */
    public function getActive()
    {
        return $this->borrowModel->whereIn('status', ['Pending', 'Borrowed'])
                                ->where('actual_return_date IS NULL')
                                ->findAll();
    }

    /**
     * Get all transactions for a user
     */
    public function getUserTransactions($userId)
    {
        return $this->borrowModel->where('created_by', $userId)
                                ->orderBy('created_at', 'DESC')
                                ->findAll();
    }
}
