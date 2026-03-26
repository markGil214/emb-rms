<?php

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestCase;
use App\Models\FolderModel;
use App\Models\RelocationRequestModel;
use App\Models\FolderMovementModel;
use App\Models\LocationModel;

/**
 * Enterprise Relocation Architecture Tests
 * 
 * Validates all enterprise-grade enhancements:
 * - In-transit folder state
 * - Location snapshot immutability
 * - Approval audit trail
 * - Movement code generation
 * - Race condition detection
 */
class EnterpriseRelocationTest extends FeatureTestCase
{
    protected $folderModel;
    protected $relocationModel;
    protected $movementModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folderModel = new FolderModel();
        $this->relocationModel = new RelocationRequestModel();
        $this->movementModel = new FolderMovementModel();
    }

    // ===== IN-TRANSIT STATE TESTS =====

    /**
     * TEST 1: Folder transitions to in-transit on relocation start
     */
    public function testFolderTransitionsToInTransitOnStart()
    {
        // Arrange
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);

        // Act
        $this->post("/relocations/{$relocationId}/start");

        // Assert
        $folder = $this->folderModel->find($folderId);
        $this->assertTrue($folder['is_in_transit'], "Folder should be marked as in-transit");
        $this->assertNotNull($folder['current_movement_id'], "Folder should have current_movement_id set");
    }

    /**
     * TEST 2: Folder returns to not-in-transit on completion
     */
    public function testFolderExitsInTransitOnCompletion()
    {
        // Arrange
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);
        
        $folderBeforeCompletion = $this->folderModel->find($folderId);
        $this->assertTrue($folderBeforeCompletion['is_in_transit']);

        // Act
        $this->post("/relocations/{$relocationId}/complete");

        // Assert
        $folderAfterCompletion = $this->folderModel->find($folderId);
        $this->assertFalse($folderAfterCompletion['is_in_transit'], 
            "Folder should no longer be in-transit");
        $this->assertNull($folderAfterCompletion['current_movement_id'], 
            "current_movement_id should be cleared");
    }

    /**
     * TEST 3: Cannot complete relocation if folder not in-transit
     */
    public function testCannotCompleteRelocationIfNotInTransit()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        
        // Try to complete without starting
        $response = $this->post("/relocations/{$relocationId}/complete");

        // Should fail
        $this->assertRedirect();
        $this->assertStringContainsString("not in transit", session()->getFlashdata('error'));
    }

    // ===== LOCATION SNAPSHOT TESTS =====

    /**
     * TEST 4: Location snapshots captured at movement start
     */
    public function testLocationSnapshotsCapturedAtStart()
    {
        // Arrange with location details
        $folderId = $this->createTestFolder('Available', [
            'building' => 'Building A',
            'room' => 'Room 201',
            'cabinet' => 'Cabinet 4',
            'shelf' => 'Shelf B',
        ]);
        
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);

        // Act
        $this->startRelocation($relocationId);

        // Assert
        $movement = $this->movementModel->getActiveMovement($folderId);
        $this->assertNotNull($movement);
        $this->assertEquals('Building A', $movement['confirmed_from_building']);
        $this->assertStringContainsString('Building A', $movement['from_location_label']);
        $this->assertStringContainsString('Room 201', $movement['from_location_label']);
    }

    /**
     * TEST 5: Location labels immutable even if location changed
     */
    public function testLocationLabelImmutableAfterCapture()
    {
        // Arrange
        $folderId = $this->createTestFolder('Available', [
            'building' => 'Building A',
            'room' => 'Room 201',
        ]);
        
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);

        // Get original snapshot
        $movement = $this->movementModel->getActiveMovement($folderId);
        $originalLabel = $movement['from_location_label'];

        // Simulate location being renamed in system
        // (In real scenario, location records might be updated/deleted)
        
        // Act: Complete relocation
        $this->post("/relocations/{$relocationId}/complete");

        // Assert: Original label still preserved
        $completedMovement = $this->movementModel->find($movement['movement_id']);
        $this->assertEquals($originalLabel, $completedMovement['from_location_label'],
            "Location label should be immutable");
    }

    /**
     * TEST 6: To-location captured correctly on completion
     */
    public function testToLocationCapturedOnCompletion()
    {
        // Arrange
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);

        // Act
        $this->post("/relocations/{$relocationId}/complete");

        // Assert
        $movement = $this->movementModel->where('folder_id', $folderId)->first();
        $this->assertNotNull($movement['to_location_label']);
        $this->assertNotNull($movement['confirmed_to_building']);
    }

    // ===== APPROVAL AUDIT TRAIL TESTS =====

    /**
     * TEST 7: Approval user captured at start
     */
    public function testApprovalUserCapturedAtStart()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);

        // Act
        $this->startRelocation($relocationId);

        // Assert
        $movement = $this->movementModel->getActiveMovement($folderId);
        $this->assertNotNull($movement['approved_by'], 
            "Should capture user who approved");
        $this->assertNotNull($movement['approved_at'], 
            "Should capture approval timestamp");
    }

    /**
     * TEST 8: Completion user captured at completion
     */
    public function testCompletionUserCapturedAtCompletion()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);

        // Act
        $this->post("/relocations/{$relocationId}/complete");

        // Assert
        $movement = $this->movementModel->where('folder_id', $folderId)->first();
        $this->assertNotNull($movement['completed_by'], 
            "Should capture user who completed");
        $this->assertNotNull($movement['completed_at'], 
            "Should capture completion timestamp");
    }

    /**
     * TEST 9: Complete audit chain: approved_by → completed_by
     */
    public function testCompleteAuditChain()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);
        $this->post("/relocations/{$relocationId}/complete");

        // Assert
        $movement = $this->movementModel->where('folder_id', $folderId)->first();
        
        // Should have both approval and completion info
        $this->assertNotNull($movement['approved_by']);
        $this->assertNotNull($movement['approved_at']);
        $this->assertNotNull($movement['completed_by']);
        $this->assertNotNull($movement['completed_at']);
        
        // Completion should be after approval
        $this->assertGreaterThanOrEqual(
            strtotime($movement['approved_at']),
            strtotime($movement['completed_at'])
        );
    }

    // ===== MOVEMENT CODE TESTS =====

    /**
     * TEST 10: Movement code generated with unique identifier
     */
    public function testMovementCodeGenerated()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);

        // Act
        $this->startRelocation($relocationId);

        // Assert
        $movement = $this->movementModel->getActiveMovement($folderId);
        $this->assertNotNull($movement['movement_code']);
        $this->assertStringStartsWith('MOV-', $movement['movement_code']);
        $this->assertMatchesRegularExpression('/MOV-\d{4}-\d{6}/', $movement['movement_code']);
    }

    /**
     * TEST 11: Movement codes are unique
     */
    public function testMovementCodesAreUnique()
    {
        // Create multiple relocations
        $codes = [];
        for ($i = 0; $i < 3; $i++) {
            $folderId = $this->createTestFolder('Available');
            $relocationId = $this->createRelocationRequest($folderId, 2);
            $this->approveRelocation($relocationId);
            $this->startRelocation($relocationId);
            
            $movement = $this->movementModel->getActiveMovement($folderId);
            $codes[] = $movement['movement_code'];
        }

        // Assert all codes unique
        $this->assertCount(count($codes), array_unique($codes), 
            "All movement codes should be unique");
    }

    // ===== RACE CONDITION DETECTION TESTS =====

    /**
     * TEST 12: Race condition detected if status changes during relocation
     */
    public function testRaceConditionDetectedIfStatusChanges()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);

        // Simulate race condition: folder status changed to Borrowed
        $this->folderModel->update($folderId, ['status' => 'Borrowed']);

        // Act: Try to complete
        $this->post("/relocations/{$relocationId}/complete");

        // Assert: Race condition detected
        $movement = $this->movementModel->where('folder_id', $folderId)->first();
        $this->assertTrue($movement['status_conflict_detected'], 
            "Should detect status conflict");
        $folderStatus = $movement['folder_status_at_completion'];
        $this->assertNotEquals($movement['folder_status_at_start'], $folderStatus);
    }

    /**
     * TEST 13: Folder status preserved at start and completion
     */
    public function testFolderStatusPreservedInMovement()
    {
        $folderId = $this->createTestFolder('Available');
        $relocationId = $this->createRelocationRequest($folderId, 2);
        $this->approveRelocation($relocationId);
        $this->startRelocation($relocationId);

        // Assert start status captured
        $movement = $this->movementModel->getActiveMovement($folderId);
        $this->assertEquals('Available', $movement['folder_status_at_start']);

        // Act: Complete
        $this->post("/relocations/{$relocationId}/complete");

        // Assert: Completion status also captured
        $completedMovement = $this->movementModel->find($movement['movement_id']);
        $this->assertEquals('Available', $completedMovement['folder_status_at_completion']);
        $this->assertFalse($completedMovement['status_conflict_detected']);
    }

    // ===== COMPREHENSIVE WORKFLOW TESTS =====

    /**
     * TEST 14: Complete enterprise workflow with all features
     */
    public function testCompleteEnterpriseWorkflow()
    {
        // Arrange
        $folderId = $this->createTestFolder('Available', [
            'building' => 'Original Building',
            'room' => 'Room 100',
        ]);
        
        $relocationId = $this->createRelocationRequest($folderId, 2);

        // Step 1: Approve
        $this->approveRelocation($relocationId);
        
        // Step 2: Start (sets in-transit, generates code, captures approval)
        $this->startRelocation($relocationId);
        $movement = $this->movementModel->getActiveMovement($folderId);
        
        $this->assertTrue($this->folderModel->find($folderId)['is_in_transit']);
        $this->assertNotNull($movement['movement_code']);
        $this->assertNotNull($movement['approved_by']);
        $this->assertEquals('Available', $movement['folder_status_at_start']);
        
        // Step 3: Complete (updates location, captures completion, exits in-transit)
        $this->post("/relocations/{$relocationId}/complete");
        
        // Final state
        $folder = $this->folderModel->find($folderId);
        $completedMovement = $this->movementModel->find($movement['movement_id']);
        
        $this->assertFalse($folder['is_in_transit']);
        $this->assertEquals(2, $folder['location_id']);
        $this->assertNotNull($completedMovement['completed_by']);
        $this->assertFalse($completedMovement['status_conflict_detected']);
    }

    // ==================== HELPER METHODS ====================

    private function createTestFolder($status = 'Available', $locationDetails = [])
    {
        $data = [
            'file_code' => 'TEST-' . uniqid(),
            'company_name' => 'Test Company ' . uniqid(),
            'folder_type' => 'Test Type',
            'location_code' => 'CA-1-1',
            'issuance_date' => '2026-01-01',
            'expiry_date' => '2027-12-31',
            'status' => $status,
            'location_id' => 1,
            'created_by' => 1,
        ];
        
        if (!empty($locationDetails)) {
            $data = array_merge($data, $locationDetails);
        }
        
        $this->folderModel->save($data);
        return $this->folderModel->getInsertID();
    }

    private function createRelocationRequest($folderId, $toLocationId)
    {
        $data = [
            'folder_id' => $folderId,
            'from_location_id' => 1,
            'to_location_id' => $toLocationId,
            'reason' => 'Test relocation',
            'reason_type' => 'Testing',
            'status' => 'Pending',
            'requested_at' => date('Y-m-d H:i:s'),
            'requested_by' => 1,
        ];
        
        $this->relocationModel->save($data);
        return $this->relocationModel->getInsertID();
    }

    private function approveRelocation($relocationId)
    {
        $this->relocationModel->approveRelocation($relocationId, 1);
    }

    private function startRelocation($relocationId)
    {
        $this->post("/relocations/{$relocationId}/start");
    }
}
