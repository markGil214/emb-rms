<?php

namespace Tests;

use App\Models\BorrowTransactionModel;
use App\Models\RelocationRequestModel;
use App\Models\ArchiveRecordModel;
use App\Models\DisposalRecordModel;
use App\Models\FolderModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

class FeatureWorkflowTest extends CIUnitTestCase
{
    protected $borrowModel;
    protected $relocationModel;
    protected $archiveModel;
    protected $disposalModel;
    protected $folderModel;
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->borrowModel = new BorrowTransactionModel();
        $this->relocationModel = new RelocationRequestModel();
        $this->archiveModel = new ArchiveRecordModel();
        $this->disposalModel = new DisposalRecordModel();
        $this->folderModel = new FolderModel();
        $this->userModel = new UserModel();
    }

    /**
     * TEST 1: Borrow workflow - Request → Approve → Return
     */
    public function testBorrowWorkflow()
    {
        // Ensure we have a test folder and user
        $folder = $this->folderModel->first();
        $user = $this->userModel->first();

        $this->assertNotNull($folder, 'Test folder must exist');
        $this->assertNotNull($user, 'Test user must exist');

        // Get borrower (from borrowers table)
        $db = \Config\Database::connect();
        $borrower = $db->table('borrowers')->first();
        $this->assertNotNull($borrower, 'Test borrower must exist');

        // Step 1: Create borrow request
        $borrowData = [
            'folder_id' => $folder['folder_id'],
            'borrower_id' => $borrower['borrower_id'],
            'purpose' => 'Document review for project',
            'borrowed_at' => date('Y-m-d H:i:s'),
            'expected_return_date' => date('Y-m-d', strtotime('+14 days')),
            'status' => 'Active',
            'released_by' => $user['user_id'],
        ];

        $borrowId = $this->borrowModel->insert($borrowData);
        $this->assertNotNull($borrowId, 'Borrow record should be created');

        // Verify record exists
        $borrow = $this->borrowModel->find($borrowId);
        $this->assertNotNull($borrow, 'Borrow record should be retrievable');
        $this->assertEquals('Active', $borrow['status']);

        // Step 2: Mark as returned
        $this->borrowModel->update($borrowId, [
            'actual_return_date' => date('Y-m-d H:i:s'),
            'received_by' => $user['user_id'],
            'status' => 'Returned'
        ]);
        $borrow = $this->borrowModel->find($borrowId);
        $this->assertEquals('Returned', $borrow['status']);
        $this->assertNotNull($borrow['actual_return_date']);
    }

    /**
     * TEST 2: Relocation workflow - Request → Approve → Complete
     */
    public function testRelocationWorkflow()
    {
        $folder = $this->folderModel->first();
        $user = $this->userModel->first();

        // Get a location ID from database
        $db = \Config\Database::connect();
        $location = $db->table('locations')->get()->getRow();
        $this->assertNotNull($location, 'Location must exist');

        // Step 1: Create relocation request
        $relocData = [
            'folder_id' => $folder['folder_id'],
            'from_location_id' => $location->location_id,
            'to_location_id' => $location->location_id,  // Same location for test
            'reason' => 'Consolidation project',
            'status' => 'Pending',
            'requested_by' => $user['user_id'],
            'requested_at' => date('Y-m-d H:i:s'),
        ];

        $relocId = $this->relocationModel->insert($relocData);
        $this->assertNotNull($relocId, 'Relocation record should be created');

        // Verify record exists
        $reloc = $this->relocationModel->find($relocId);
        $this->assertNotNull($reloc, 'Relocation record should be retrievable');
        $this->assertEquals('Pending', $reloc['status']);

        // Step 2: Approve relocation
        $this->relocationModel->update($relocId, [
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $user['user_id'],
            'status' => 'Approved'
        ]);
        $reloc = $this->relocationModel->find($relocId);
        $this->assertNotNull($reloc['approved_at']);
        $this->assertEquals('Approved', $reloc['status']);
    }

    /**
     * TEST 3: Archive workflow - Create → Validate
     */
    public function testArchiveWorkflow()
    {
        $folder = $this->folderModel->first();
        $user = $this->userModel->first();

        $db = \Config\Database::connect();
        $location = $db->table('locations')->get()->getRow();

        // Step 1: Create archive record
        $archiveData = [
            'folder_id' => $folder['folder_id'],
            'archived_date' => date('Y-m-d'),
            'archive_location_id' => $location->location_id,
            'retention_status' => 'Active',
            'retention_expiry_date' => date('Y-m-d', strtotime('+3 years')),
            'archived_by' => $user['user_id'],
        ];

        $archiveId = $this->archiveModel->insert($archiveData);
        $this->assertNotNull($archiveId, 'Archive record should be created');

        // Verify record exists
        $archive = $this->archiveModel->find($archiveId);
        $this->assertNotNull($archive, 'Archive record should be retrievable');
        $this->assertEquals('Active', $archive['retention_status']);
    }

    /**
     * TEST 4: Disposal workflow - Create → Approve
     */
    public function testDisposalWorkflow()
    {
        $db = \Config\Database::connect();

        // Get an existing archive record or create one first
        $archiveRow = $db->table('archive_records')->get()->getRow();
        if (!$archiveRow) {
            $this->markTestSkipped('No archive records exist for disposal test');
        }

        $user = $this->userModel->first();

        // Step 1: Create disposal request  
        // Note: disposal_date is required in migration, so setting it to today
        $disposalData = [
            'archive_id' => $archiveRow->archive_id,
            'disposal_method' => 'Destruction',
            'disposal_date' => date('Y-m-d'),
            'compliance_reference' => 'TEST-2026-001',
            'approved_by' => $user['user_id'],
        ];

        $disposalId = $this->disposalModel->insert($disposalData);
        $this->assertNotNull($disposalId, 'Disposal record should be created');

        // Verify record exists
        $disposal = $this->disposalModel->find($disposalId);
        $this->assertNotNull($disposal, 'Disposal record should be retrievable');
        $this->assertNotNull($disposal['disposal_date'], 'Disposal date should be set');
    }

    /**
     * TEST 5: Model validation rules
     */
    public function testModelValidationRules()
    {
        // Test borrow validation
        $invalidBorrow = [
            'folder_id' => 'invalid',  // Should be numeric
            'expected_return_date' => 'not-a-date'
        ];
        $this->assertFalse($this->borrowModel->validate($invalidBorrow));

        // Test relocation validation
        $invalidReloc = [
            'from_location_id' => 'invalid'
        ];
        $this->assertFalse($this->relocationModel->validate($invalidReloc));

        // Test archive validation
        $invalidArchive = [
            'folder_id' => 'invalid'
        ];
        $this->assertFalse($this->archiveModel->validate($invalidArchive));

        // Test disposal validation
        $invalidDisposal = [
            'archive_id' => 'invalid'
        ];
        $this->assertFalse($this->disposalModel->validate($invalidDisposal));
    }

    /**
     * TEST 6: Verify database structure matches models
     */
    public function testDatabaseStructure()
    {
        $db = \Config\Database::connect();

        // Check borrow_transactions table
        $this->assertTrue($db->tableExists('borrow_transactions'), 'borrow_transactions table should exist');
        $columns = $db->getFieldNames('borrow_transactions');
        $this->assertContains('transaction_id', $columns);
        $this->assertContains('folder_id', $columns);
        $this->assertContains('status', $columns);

        // Check relocation_requests table
        $this->assertTrue($db->tableExists('relocation_requests'), 'relocation_requests table should exist');
        $columns = $db->getFieldNames('relocation_requests');
        $this->assertContains('relocation_id', $columns);
        $this->assertContains('from_location_id', $columns);
        $this->assertContains('to_location_id', $columns);

        // Check archive_records table
        $this->assertTrue($db->tableExists('archive_records'), 'archive_records table should exist');
        $columns = $db->getFieldNames('archive_records');
        $this->assertContains('archive_id', $columns);
        $this->assertContains('archive_location_id', $columns);
        $this->assertContains('retention_status', $columns);

        // Check disposal_records table
        $this->assertTrue($db->tableExists('disposal_records'), 'disposal_records table should exist');
        $columns = $db->getFieldNames('disposal_records');
        $this->assertContains('disposal_id', $columns);
        $this->assertContains('archive_id', $columns);
        $this->assertContains('disposal_date', $columns);
    }
}
