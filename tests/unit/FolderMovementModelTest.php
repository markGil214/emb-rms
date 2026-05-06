<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\FolderMovementModel;
use App\Models\FolderModel;
use App\Models\RelocationRequestModel;

/**
 * FolderMovementModel Unit Tests
 * 
 * Tests the query methods for folder movement history tracking
 */
class FolderMovementModelTest extends CIUnitTestCase
{
    protected $model;
    protected $folderModel;
    protected $relocationModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new FolderMovementModel();
        $this->folderModel = new FolderModel();
        $this->relocationModel = new RelocationRequestModel();
    }

    /**
     * TEST 1: Model can be instantiated
     */
    public function testModelCanBeInstantiated()
    {
        $this->assertInstanceOf(FolderMovementModel::class, $this->model);
    }

    /**
     * TEST 2: Table name is configured
     */
    public function testTableNameIsConfigured()
    {
        $this->assertEquals('folder_movements', $this->model->getTable());
    }

    /**
     * TEST 3: Validation rules exist
     */
    public function testValidationRulesExist()
    {
        $rules = $this->model->validationRules ?? [];
        $this->assertNotEmpty($rules, "Validation rules should be defined");
        $this->assertArrayHasKey('folder_id', $rules);
        $this->assertArrayHasKey('moved_by', $rules);
    }

    /**
     * TEST 4: recordMovement method validates folder_id
     */
    public function testRecordMovementValidatesFolderId()
    {
        $invalidData = [
            'folder_id' => null,  // Missing required field
            'from_location_id' => 1,
            'to_location_id' => 2,
            'moved_by' => 1,
            'moved_at' => date('Y-m-d H:i:s'),
        ];
        
        $result = $this->model->recordMovement($invalidData);
        
        // Should fail validation or return false
        $this->assertFalse($result);
    }

    /**
     * TEST 5: recordMovement successfully saves valid data
     */
    public function testRecordMovementSavesValidData()
    {
        // Setup: Create test folder and relocation
        $folderId = $this->createTestFolder();
        $relocationId = $this->createTestRelocation($folderId);
        
        $validData = [
            'folder_id' => $folderId,
            'from_location_id' => 1,
            'to_location_id' => 2,
            'from_building' => 'Building A',
            'to_building' => 'Building B',
            'relocation_request_id' => $relocationId,
            'moved_by' => 1,
            'moved_at' => date('Y-m-d H:i:s'),
            'reason' => 'Test movement',
        ];
        
        $result = $this->model->recordMovement($validData);
        
        $this->assertNotFalse($result);
        $this->assertDatabaseHas('folder_movements', [
            'folder_id' => $folderId,
            'to_location_id' => 2,
        ]);
    }

    /**
     * TEST 6: getFolderMovements returns all movements for folder
     */
    public function testGetFolderMovementsReturnsAllMovements()
    {
        // Setup: Create folder with 3 movements
        $folderId = $this->createTestFolder();
        $this->createMovement($folderId, 1, 2);
        $this->createMovement($folderId, 2, 3);
        $this->createMovement($folderId, 3, 4);
        
        // Execute
        $movements = $this->model->getFolderMovements($folderId);
        
        // Assert
        $this->assertCount(3, $movements);
        $this->assertEquals($folderId, $movements[0]['folder_id']);
        $this->assertEquals($folderId, $movements[1]['folder_id']);
        $this->assertEquals($folderId, $movements[2]['folder_id']);
    }

    /**
     * TEST 7: getFolderMovements orders by date DESC (newest first)
     */
    public function testGetFolderMovementsOrdersByDateDesc()
    {
        // Setup
        $folderId = $this->createTestFolder();
        
        // Create movements with 1 hour apart
        $time1 = date('Y-m-d H:i:s', strtotime('-2 hours'));
        $time2 = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $time3 = date('Y-m-d H:i:s');
        
        $this->createMovementWithTime($folderId, 1, 2, $time1);
        $this->createMovementWithTime($folderId, 2, 3, $time2);
        $this->createMovementWithTime($folderId, 3, 4, $time3);
        
        // Execute
        $movements = $this->model->getFolderMovements($folderId);
        
        // Assert: Most recent first
        $this->assertEquals(4, $movements[0]['to_location_id']);  // Latest
        $this->assertEquals(3, $movements[1]['to_location_id']);  // Middle
        $this->assertEquals(2, $movements[2]['to_location_id']);  // Oldest
    }

    /**
     * TEST 8: getLatestMovement returns only the most recent
     */
    public function testGetLatestMovementReturnsMostRecent()
    {
        // Setup
        $folderId = $this->createTestFolder();
        $this->createMovement($folderId, 1, 2);
        $this->createMovement($folderId, 2, 3);
        
        // Execute
        $latest = $this->model->getLatestMovement($folderId);
        
        // Assert
        $this->assertNotNull($latest);
        $this->assertEquals(3, $latest['to_location_id']);
        $this->assertEquals($folderId, $latest['folder_id']);
    }

    /**
     * TEST 9: getMovementsBetweenDates filters by date range
     */
    public function testGetMovementsBetweenDatesFiltersCorrectly()
    {
        // Setup
        $folderId = $this->createTestFolder();
        
        $past = date('Y-m-d', strtotime('-10 days'));
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime('+10 days'));
        
        // Movement outside range
        $this->createMovementWithTime($folderId, 1, 2, $past . ' 10:00:00');
        
        // Movements inside range
        $this->createMovementWithTime($folderId, 2, 3, $today . ' 09:00:00');
        $this->createMovementWithTime($folderId, 3, 4, $today . ' 14:00:00');
        
        // Execute
        $movements = $this->model->getMovementsBetweenDates(
            $folderId,
            $today . ' 00:00:00',
            $future . ' 23:59:59'
        );
        
        // Assert
        $this->assertCount(2, $movements);
        foreach ($movements as $movement) {
            $this->assertGreaterThanOrEqual($today, date('Y-m-d', strtotime($movement['moved_at'])));
        }
    }

    /**
     * TEST 10: getMovementsByUser returns movements by specific user
     */
    public function testGetMovementsByUserReturnsUserMovements()
    {
        // Setup
        $folderId1 = $this->createTestFolder();
        $folderId2 = $this->createTestFolder();
        
        // User 1 moves
        $this->createMovement($folderId1, 1, 2, ['moved_by' => 1]);
        $this->createMovement($folderId2, 1, 2, ['moved_by' => 1]);
        
        // User 2 moves
        $this->createMovement($folderId1, 2, 3, ['moved_by' => 2]);
        
        // Execute
        $user1Movements = $this->model->getMovementsByUser(1);
        $user2Movements = $this->model->getMovementsByUser(2);
        
        // Assert
        $this->assertGreaterThanOrEqual(2, count($user1Movements));
        $this->assertGreaterThanOrEqual(1, count($user2Movements));
    }

    /**
     * TEST 11: getByRelocationRequest returns movements for request
     */
    public function testGetByRelocationRequestReturnsMoves()
    {
        // Setup
        $folderId = $this->createTestFolder();
        $relocationId = $this->createTestRelocation($folderId);
        
        $this->model->recordMovement([
            'folder_id' => $folderId,
            'from_location_id' => 1,
            'to_location_id' => 2,
            'relocation_request_id' => $relocationId,
            'moved_by' => 1,
            'moved_at' => date('Y-m-d H:i:s'),
        ]);
        
        // Execute
        $movements = $this->model->getByRelocationRequest($relocationId);
        
        // Assert
        $this->assertNotEmpty($movements);
        $this->assertEquals($relocationId, $movements[0]['relocation_request_id']);
    }

    /**
     * TEST 12: Model properly handles null values for optional fields
     */
    public function testModelHandlesNullOptionalFields()
    {
        $folderId = $this->createTestFolder();
        
        // Can create movement without building/room/cabinet fields
        $data = [
            'folder_id' => $folderId,
            'from_location_id' => 1,
            'to_location_id' => 2,
            'from_building' => null,
            'to_building' => null,
            'from_room' => null,
            'to_room' => null,
            'moved_by' => 1,
            'moved_at' => date('Y-m-d H:i:s'),
        ];
        
        $result = $this->model->recordMovement($data);
        
        // Should succeed with nulls
        $this->assertNotFalse($result);
    }

    /**
     * TEST 13: Movement record is linked to folder and relocation
     */
    public function testMovementLinksToFolderAndRelocation()
    {
        // Setup
        $folderId = $this->createTestFolder();
        $relocationId = $this->createTestRelocation($folderId);
        
        $this->model->recordMovement([
            'folder_id' => $folderId,
            'from_location_id' => 1,
            'to_location_id' => 2,
            'relocation_request_id' => $relocationId,
            'moved_by' => 1,
            'moved_at' => date('Y-m-d H:i:s'),
        ]);
        
        // Verify links
        $movement = $this->model->where('folder_id', $folderId)->first();
        
        $this->assertEquals($folderId, $movement['folder_id']);
        $this->assertEquals($relocationId, $movement['relocation_request_id']);
        
        // Folder and relocation should exist
        $this->assertNotNull($this->folderModel->find($folderId));
        $this->assertNotNull($this->relocationModel->find($relocationId));
    }

    // ==================== HELPER METHODS ====================

    private function createTestFolder()
    {
        $data = [
            'file_code' => 'MOVE-' . uniqid(),
            'company_name' => 'Movement Test ' . uniqid(),
            'folder_type' => 'Test',
            'location_code' => 'CA-1-1',
            'issuance_date' => '2026-01-01',
            'expiry_date' => '2027-12-31',
            'status' => 'Available',
            'location_id' => 1,
            'created_by' => 1,
        ];
        
        $this->folderModel->save($data);
        return $this->folderModel->insertID();
    }

    private function createTestRelocation($folderId)
    {
        $data = [
            'folder_id' => $folderId,
            'from_location_id' => 1,
            'to_location_id' => 2,
            'reason' => 'Unit test',
            'reason_type' => 'Testing',
            'status' => 'Completed',
            'requested_by' => 1,
        ];
        
        $this->relocationModel->save($data);
        return $this->relocationModel->insertID();
    }

    private function createMovement($folderId, $fromLoc, $toLoc, $userOverrides = [])
    {
        $data = array_merge([
            'folder_id' => $folderId,
            'from_location_id' => $fromLoc,
            'to_location_id' => $toLoc,
            'moved_by' => 1,
            'moved_at' => date('Y-m-d H:i:s'),
        ], $userOverrides);
        
        return $this->model->recordMovement($data);
    }

    private function createMovementWithTime($folderId, $fromLoc, $toLoc, $time)
    {
        $data = [
            'folder_id' => $folderId,
            'from_location_id' => $fromLoc,
            'to_location_id' => $toLoc,
            'moved_by' => 1,
            'moved_at' => $time,
        ];
        
        return $this->model->recordMovement($data);
    }
}
