<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;
use DomainException;

/**
 * ✅ SERVICE LAYER (ORCHESTRATION)
 * 
 * Coordinates business workflows by delegating to:
 * - BorrowValidator: Validate preconditions
 * - BorrowRepository: Execute DB operations
 * - LogHelper: Structured logging
 * 
 * Service layer is now THIN—just orchestration.
 * Makes it testable and maintainable.
 * 
 * Benefits:
 * - Easy to unit test (mock dependencies)
 * - Easy to extend (add new workflows)
 * - Clear separation of concerns
 * - Idempotency built-in (validator rejects already-processed requests)
 */
class BorrowService
{
    protected $validator;
    protected $repository;

    public function __construct(BorrowValidator $validator = null, BorrowRepository $repository = null)
    {
        $this->validator = $validator ?? new BorrowValidator();
        $this->repository = $repository ?? new BorrowRepository();
    }

    /**
     * ✅ APPROVE BORROW REQUEST AND RELEASE ITEM
     * 
     * Workflow:
     * 1. Validate approval preconditions (status, etc)
     * 2. Execute atomic DB operation (folder lock + transaction update)
     * 3. Log structured success event
     * 4. Return approved transaction
     * 
     * ✅ IDEMPOTENT: Safe if called multiple times
     * If called twice, second call will throw "already Pending" rejection
     * which is semantically correct and expected behavior
     * 
     * @param int $transactionId The transaction to approve
     * @param int $adminId       The admin approving
     * @return array             Approved transaction data
     * @throws DomainException   If validation fails or DB operation fails
     */
    public function approve($transactionId, $adminId)
    {
        // ✅ Step 1: Validate preconditions
        $borrow = $this->validator->validateApprovalRequest($transactionId, $adminId);

        // ✅ Step 2: Execute atomic operation
        $now = Time::now()->format('Y-m-d H:i:s');
        $this->repository->executeApproval($transactionId, $adminId, $now);

        // ✅ Step 3: Log success
        LogHelper::info('borrow_approved', [
            'transaction_id' => $transactionId,
            'folder_id' => $borrow['folder_id'],
            'borrower_name' => $borrow['borrower_name'],
            'approved_by_user_id' => $adminId,
            'approved_at' => $now,
        ]);

        // ✅ Step 4: Return updated transaction
        return $this->repository->find($transactionId);
    }

    /**
     * ✅ RETURN BORROWED ITEM
     * 
     * Workflow:
     * 1. Validate return preconditions (status, ownership, etc)
     * 2. Execute atomic DB operation (folder unlock + transaction update)
     * 3. Log structured success event
     * 4. Return completed transaction
     * 
     * ✅ IDEMPOTENT: Safe if called multiple times
     * If called twice, second call will throw "already Returned" rejection
     * which is semantically correct and expected behavior
     * 
     * @param int $transactionId The transaction to return
     * @param int $userId        The user returning (must be creator or admin)
     * @param string $userRole   User's role (for IDOR checks)
     * @return array             Returned transaction data
     * @throws DomainException   If validation fails or DB operation fails
     */
    public function return($transactionId, $userId, $userRole = 'records_officer')
    {
        // ✅ Step 1: Validate preconditions
        $borrow = $this->validator->validateReturnRequest($transactionId, $userId, $userRole);

        // ✅ Step 2: Execute atomic operation
        $now = Time::now()->format('Y-m-d H:i:s');
        $this->repository->executeReturn($transactionId, $userId, $now);

        // ✅ Step 3: Log success
        LogHelper::info('borrow_returned', [
            'transaction_id' => $transactionId,
            'folder_id' => $borrow['folder_id'],
            'borrower_name' => $borrow['borrower_name'],
            'returned_by_user_id' => $userId,
            'returned_at' => $now,
        ]);

        // ✅ Step 4: Return updated transaction
        return $this->repository->find($transactionId);
    }

    /**
     * ✅ CREATE NEW BORROW REQUEST (with duplicate prevention)
     * 
     * @param array $data Request data
     * @param int $userId User creating the request
     * @return int        Transaction ID
     * @throws DomainException If validation fails
     */
    public function createRequest($data, $userId)
    {
        // ✅ Check for duplicate pending request (abuse prevention)
        $this->validator->validateNoDuplicatePending($userId, $data['folder_id']);

        // ✅ Validate dates
        $data['status'] = 'Pending';
        $data['created_by'] = $userId;
        $this->validator->validateDates($data);

        // ✅ Save
        $borrowModel = new \App\Models\BorrowTransactionModel();
        if (!$borrowModel->save($data)) {
            throw new DomainException('Failed to create borrow request');
        }

        $transactionId = $borrowModel->getInsertID();

        // ✅ Log creation
        LogHelper::info('borrow_requested', [
            'transaction_id' => $transactionId,
            'folder_id' => $data['folder_id'],
            'borrower_name' => $data['borrower_name'],
            'created_by_user_id' => $userId,
            'expected_return_date' => $data['expected_return_date'],
        ]);

        return $transactionId;
    }

    /**
     * Get transaction with computed status
     */
    public function getWithComputedStatus($transactionId)
    {
        $transaction = $this->repository->find($transactionId);
        if (!$transaction) {
            throw new DomainException("Transaction {$transactionId} not found");
        }
        $transaction['status'] = $this->validator->getDisplayStatus($transaction);
        return $transaction;
    }

    /**
     * Get all pending requests
     */
    public function getPending()
    {
        return $this->repository->getPending();
    }

    /**
     * Get all currently borrowed items
     */
    public function getBorrowed()
    {
        return $this->repository->getBorrowed();
    }

    /**
     * Get all active transactions
     */
    public function getActive()
    {
        $transactions = $this->repository->getActive();
        foreach ($transactions as &$trans) {
            $trans['status'] = $this->validator->getDisplayStatus($trans);
        }
        return $transactions;
    }

    /**
     * Get user's transaction history
     */
    public function getUserTransactions($userId)
    {
        $transactions = $this->repository->getUserTransactions($userId);
        foreach ($transactions as &$trans) {
            $trans['status'] = $this->validator->getDisplayStatus($trans);
        }
        return $transactions;
    }
}
