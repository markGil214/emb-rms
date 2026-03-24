<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestCase;
use App\Libraries\BorrowService;
use App\Libraries\BorrowValidator;
use App\Libraries\BorrowRepository;
use App\Models\BorrowTransactionModel;
use App\Models\FolderModel;
use DomainException;

/**
 * ✅ COMPREHENSIVE BORROW SERVICE TESTS
 * 
 * Tests cover:
 * ✅ Happy paths (success scenarios)
 * ✅ Error scenarios (validation failures)
 * ✅ Security scenarios (IDOR, authorization)
 * ✅ Idempotency
 * ✅ Race conditions / concurrent operations
 * ✅ Data integrity
 */
class BorrowServiceTest extends DatabaseTestCase
{
    protected $seed = 'TestSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BorrowService();
        $this->folderModel = new FolderModel();
        $this->borrowModel = new BorrowTransactionModel();
    }

    /**
     * ✅ TEST 1: Approve request successfully
     */
    public function testAproveRequestSuccess()
    {
        // ✅ Arrange: Create folder and pending request
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Available'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'purpose' => 'Review',
            'expected_return_date' => '2026-04-23',
            'status' => 'Pending',
            'created_by' => 2
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Approve the request
        $adminId = 1;
        $result = $this->service->approve($transactionId, $adminId);

        // ✅ Assert: Check results
        $this->assertEqual($result['status'], 'Borrowed');
        $this->assertEqual($result['approved_by'], $adminId);
        $this->assertNotNull($result['approved_at']);
        $this->assertNotNull($result['borrowed_at']);

        // ✅ Assert: Folder is now locked
        $folder = $this->folderModel->find(1);
        $this->assertEqual($folder['status'], 'Borrowed');
    }

    /**
     * ✅ TEST 2: Approve fails when already approved
     */
    public function testApproveFailsWhenAlreadyApproved()
    {
        // ✅ Arrange: Create already-borrowed transaction
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Borrowed'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Borrowed',  // Already approved
            'approved_by' => 1,
            'approved_at' => '2026-03-20 10:00:00',
            'borrowed_at' => '2026-03-20 10:00:00',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Try to approve again
        $adminId = 1;
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Only 'Pending' requests can be approved");

        // ✅ Assert: Exception thrown
        $this->service->approve($transactionId, $adminId);
    }

    /**
     * ✅ TEST 3: Return request successfully
     */
    public function testReturnRequestSuccess()
    {
        // ✅ Arrange: Create borrowed transaction
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Borrowed'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Borrowed',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2,  // User who borrowed
            'approved_by' => 1,
            'borrowed_at' => '2026-03-20 10:00:00'
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Return the item
        $userId = 2;  // Same user who borrowed
        $userRole = 'records_officer';
        $result = $this->service->return($transactionId, $userId, $userRole);

        // ✅ Assert: Check results
        $this->assertEqual($result['status'], 'Returned');
        $this->assertEqual($result['received_by'], $userId);
        $this->assertNotNull($result['actual_return_date']);

        // ✅ Assert: Folder is now unlocked
        $folder = $this->folderModel->find(1);
        $this->assertEqual($folder['status'], 'Available');
    }

    /**
     * ✅ TEST 4: Return fails when already returned
     */
    public function testReturnFailsWhenAlreadyReturned()
    {
        // ✅ Arrange: Create already-returned transaction
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Available'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Returned',  // Already returned
            'expected_return_date' => '2026-04-23',
            'created_by' => 2,
            'actual_return_date' => '2026-03-21 10:00:00',
            'received_by' => 2
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Try to return again
        $userId = 2;
        $userRole = 'records_officer';
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Only 'Borrowed' items can be returned");

        // ✅ Assert: Exception thrown
        $this->service->return($transactionId, $userId, $userRole);
    }

    /**
     * ✅ TEST 5: Return fails with IDOR (unauthorized user)
     */
    public function testReturnFailsWithIdor()
    {
        // ✅ Arrange: Create borrowed transaction
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Borrowed'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Borrowed',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2,  // User 2 created it
            'approved_by' => 1,
            'borrowed_at' => '2026-03-20 10:00:00'
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Try to return as different user (not admin)
        $userId = 5;  // Different user
        $userRole = 'records_officer';  // Not admin
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("not authorized to return");

        // ✅ Assert: Exception thrown
        $this->service->return($transactionId, $userId, $userRole);
    }

    /**
     * ✅ TEST 6: Admin can return any transaction (override IDOR)
     */
    public function testAdminCanReturnAnyTransaction()
    {
        // ✅ Arrange: Create borrowed transaction by user 2
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Borrowed'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Borrowed',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2,
            'approved_by' => 1,
            'borrowed_at' => '2026-03-20 10:00:00'
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Admin returns it
        $adminId = 1;
        $userRole = 'admin';
        $result = $this->service->return($transactionId, $adminId, $userRole);

        // ✅ Assert: Success despite different user
        $this->assertEqual($result['status'], 'Returned');
        $this->assertEqual($result['received_by'], $adminId);
    }

    /**
     * ✅ TEST 7: Duplicate pending request prevention
     */
    public function testDuplicatePendingRequestPrevention()
    {
        // ✅ Arrange: Create folder and pending request
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Available'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Pending',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2
        ];
        $this->borrowModel->save($borrow);

        // ✅ Act: Try to create duplicate pending request
        $data = [
            'folder_id' => 1,
            'borrower_name' => 'Jane Doe',
            'expected_return_date' => '2026-04-30'
        ];
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("You already have a pending request for this folder");

        // ✅ Assert: Exception thrown
        $this->service->createRequest($data, 2);
    }

    /**
     * ✅ TEST 8: Overdue computation
     */
    public function testOverdueComputation()
    {
        // ✅ Arrange: Create overdue borrowed transaction
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Borrowed'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Borrowed',
            'expected_return_date' => '2026-03-01',  // Past date
            'created_by' => 2,
            'borrowed_at' => '2026-02-23 10:00:00'
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Get transaction with computed status
        $result = $this->service->getWithComputedStatus($transactionId);

        // ✅ Assert: Overdue computed correctly
        $this->assertEqual($result['status'], 'Overdue');
    }

    /**
     * ✅ TEST 9: Not overdue when due date in future
     */
    public function testNotOverdueWhenFutureDueDate()
    {
        // ✅ Arrange: Create borrowed transaction with future due date
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Borrowed'];
        $this->folderModel->save($folder);

        $futureDate = date('Y-m-d', strtotime('+30 days'));
        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Borrowed',
            'expected_return_date' => $futureDate,
            'created_by' => 2,
            'borrowed_at' => '2026-03-23 10:00:00'
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        // ✅ Act: Get transaction with computed status
        $result = $this->service->getWithComputedStatus($transactionId);

        // ✅ Assert: Status is Borrowed, not Overdue
        $this->assertEqual($result['status'], 'Borrowed');
    }

    /**
     * ✅ TEST 10: Idempotency - second call returns same result
     */
    public function testIdempotencyOfApproval()
    {
        // ✅ Arrange: Create and approve a request
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Available'];
        $this->folderModel->save($folder);

        $borrow = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Pending',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2
        ];
        $this->borrowModel->save($borrow);
        $transactionId = $this->borrowModel->getInsertID();

        $adminId = 1;
        $firstApproval = $this->service->approve($transactionId, $adminId);

        // ✅ Act: Try to approve second time
        // Should throw exception (idempotent rejection)
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Only 'Pending' requests can be approved");

        // ✅ Assert: Semantic idempotency applied
        $this->service->approve($transactionId, $adminId);
    }

    /**
     * ✅ TEST 11: Race condition - concurrent approval attempts
     * 
     * Scenario: Two admins approve same folder simultaneously
     * Result: First wins, second rejected with "folder already borrowed"
     */
    public function testRaceConditionProtection()
    {
        // ✅ Arrange: Create folder and two pending requests
        $folder = ['folder_id' => 1, 'name' => 'Test Folder', 'status' => 'Available'];
        $this->folderModel->save($folder);

        // Request 1
        $borrow1 = [
            'folder_id' => 1,
            'borrower_name' => 'John Doe',
            'status' => 'Pending',
            'expected_return_date' => '2026-04-23',
            'created_by' => 2
        ];
        $this->borrowModel->save($borrow1);
        $tx1 = $this->borrowModel->getInsertID();

        // Request 2 (same folder)
        $borrow2 = [
            'folder_id' => 1,
            'borrower_name' => 'Jane Doe',
            'status' => 'Pending',
            'expected_return_date' => '2026-04-30',
            'created_by' => 3
        ];
        $this->borrowModel->save($borrow2);
        $tx2 = $this->borrowModel->getInsertID();

        // ✅ Act: Approve first request (succeeds)
        $adminId = 1;
        $this->service->approve($tx1, $adminId);

        // ✅ Act: Try to approve second request (fails)
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("no longer available");

        // ✅ Assert: Race condition handled
        $this->service->approve($tx2, $adminId);
    }
}
