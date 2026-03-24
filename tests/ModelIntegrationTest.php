<?php

namespace Tests;

use App\Models\FolderModel;
use App\Models\BorrowTransactionModel;
use CodeIgniter\Test\CIUnitTestCase;

class ModelIntegrationTest extends CIUnitTestCase
{
    /**
     * Verify BorrowTransactionModel works with actual schema
     */
    public function testBorrowModelStructure()
    {
        $model = new BorrowTransactionModel();
        
        // Verify table name
        $this->assertEquals('borrow_transactions', $model->table);
        
        // Verify primary key
        $this->assertEquals('transaction_id', $model->primaryKey);
        
        // Verify allowed fields include all migration columns
        $expectedFields = [
            'folder_id', 'borrower_id', 'purpose', 'borrowed_at', 
            'expected_return_date', 'actual_return_date', 'status', 
            'released_by', 'received_by', 'return_notes'
        ];
        
        foreach ($expectedFields as $field) {
            $this->assertContains($field, $model->allowedFields, 
                "Field '$field' should be in allowedFields");
        }
        
        // Verify validation rules
        $this->assertNotEmpty($model->validationRules);
        $this->assertArrayHasKey('folder_id', $model->validationRules);
        $this->assertArrayHasKey('borrower_id', $model->validationRules);
        $this->assertArrayHasKey('borrowed_at', $model->validationRules);
    }

    /**
     * Verify model validation rules work correctly
     */
    public function testBorrowModelValidation()
    {
        $model = new BorrowTransactionModel();
        
        // Test valid data passes
        $validData = [
            'folder_id'             => 1,
            'borrower_id'           => 1,
            'purpose'               => 'Document review',
            'borrowed_at'           => date('Y-m-d H:i:s'),
            'expected_return_date'  => date('Y-m-d', strtotime('+14 days')),
            'status'                => 'Active',
            'released_by'           => 1,
        ];
        
        $this->assertTrue($model->validate($validData), 
            'Valid data should pass validation');
        
        // Test invalid folder_id fails
        $invalidData = $validData;
        $invalidData['folder_id'] = 'not-a-number';
        
        $this->assertFalse($model->validate($invalidData), 
            'Invalid folder_id should fail validation');
    }

    /**
     * Verify all models structure match their migrations
     */
    public function testAllModelStructures()
    {
        // Test BorrowTransactionModel
        $borrowModel = new BorrowTransactionModel();
        $this->assertContains('borrowed_at', $borrowModel->allowedFields);
        $this->assertContains('purpose', $borrowModel->allowedFields);
        $this->assertContains('released_by', $borrowModel->allowedFields);
        
        // Test RelocationRequestModel  
        $relocModel = new \App\Models\RelocationRequestModel();
        $this->assertContains('from_location_id', $relocModel->allowedFields);
        $this->assertContains('to_location_id', $relocModel->allowedFields);
        $this->assertContains('status', $relocModel->allowedFields);
        
        // Test ArchiveRecordModel
        $archiveModel = new \App\Models\ArchiveRecordModel();
        $this->assertContains('archive_location_id', $archiveModel->allowedFields);
        $this->assertContains('retention_status', $archiveModel->allowedFields);
        $this->assertContains('archived_date', $archiveModel->allowedFields);
        
        // Test DisposalRecordModel
        $disposalModel = new \App\Models\DisposalRecordModel();
        $this->assertContains('archive_id', $disposalModel->allowedFields);
        $this->assertContains('disposal_date', $disposalModel->allowedFields);
        $this->assertContains('disposal_method', $disposalModel->allowedFields);
    }

    /**
     * Verify controller methods exist
     */
    public function testControllerMethods()
    {
        // Test BorrowRequestController
        $borrowController = new \App\Controllers\BorrowRequestController();
        $this->assertTrue(method_exists($borrowController, 'index'));
        $this->assertTrue(method_exists($borrowController, 'create'));
        $this->assertTrue(method_exists($borrowController, 'store'));
        $this->assertTrue(method_exists($borrowController, 'show'));
        $this->assertTrue(method_exists($borrowController, 'approve'));
        $this->assertTrue(method_exists($borrowController, 'return'));
        
        // Test RelocationController
        $relocController = new \App\Controllers\RelocationController();
        $this->assertTrue(method_exists($relocController, 'index'));
        $this->assertTrue(method_exists($relocController, 'create'));
        $this->assertTrue(method_exists($relocController, 'store'));
        $this->assertTrue(method_exists($relocController, 'approve'));
        
        // Test ArchiveDisposalController
        $archiveController = new \App\Controllers\ArchiveDisposalController();
        $this->assertTrue(method_exists($archiveController, 'index'));
        $this->assertTrue(method_exists($archiveController, 'createArchive'));
        $this->assertTrue(method_exists($archiveController, 'storeArchive'));
        $this->assertTrue(method_exists($archiveController, 'createDisposal'));
        $this->assertTrue(method_exists($archiveController, 'storeDisposal'));
    }
}
