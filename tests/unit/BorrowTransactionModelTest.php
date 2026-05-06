<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\BorrowTransactionModel;

class BorrowTransactionModelTest extends CIUnitTestCase
{
    protected $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->model = new BorrowTransactionModel();
    }

    /**
     * Test: Model has allowed fields
     */
    public function testModelHasAllowedFields()
    {
        $allowedFields = $this->model->allowedFields;
        
        $this->assertIsArray($allowedFields);
        $this->assertContains('folder_id', $allowedFields);
        $this->assertContains('borrower_name', $allowedFields);
        $this->assertContains('purpose', $allowedFields);
        $this->assertContains('borrowed_at', $allowedFields);
        $this->assertContains('expected_return_date', $allowedFields);
        $this->assertContains('actual_return_date', $allowedFields);
        $this->assertContains('status', $allowedFields);
    }

    /**
     * Test: Validation rules require borrower_name
     */
    public function testValidationRequiresBorrowerName()
    {
        $rules = $this->model->validationRules;
        
        $this->assertArrayHasKey('borrower_name', $rules);
        $this->assertStringContainsString('required', $rules['borrower_name']);
        $this->assertStringContainsString('string', $rules['borrower_name']);
    }

    /**
     * Test: Validation rules require folder_id
     */
    public function testValidationRequiresFolderId()
    {
        $rules = $this->model->validationRules;
        
        $this->assertArrayHasKey('folder_id', $rules);
        $this->assertStringContainsString('required', $rules['folder_id']);
        $this->assertStringContainsString('integer', $rules['folder_id']);
    }

    /**
     * Test: Validation rules require expected_return_date
     */
    public function testValidationRequiresExpectedReturnDate()
    {
        $rules = $this->model->validationRules;
        
        $this->assertArrayHasKey('expected_return_date', $rules);
        $this->assertStringContainsString('required', $rules['expected_return_date']);
        $this->assertStringContainsString('valid_date', $rules['expected_return_date']);
    }

    /**
     * Test: Calculate status for returned item
     */
    public function testCalculateStatusForReturned()
    {
        $borrow = [
            'status' => 'Borrowed',  // Status should not be Pending for this test
            'actual_return_date' => date('Y-m-d'),
            'expected_return_date' => date('Y-m-d', strtotime('+7 days')),
        ];

        $calculatedStatus = $this->model->calculateStatus($borrow);
        
        $this->assertEquals('Available', $calculatedStatus);
    }

    /**
     * Test: Calculate status for overdue item
     */
    public function testCalculateStatusForOverdue()
    {
        $borrow = [
            'status' => 'Active',
            'actual_return_date' => null,
            'expected_return_date' => date('Y-m-d', strtotime('-7 days')),
        ];

        $calculatedStatus = $this->model->calculateStatus($borrow);
        
        $this->assertEquals('Overdue', $calculatedStatus);
    }

    /**
     * Test: Calculate status for borrowed item
     */
    public function testCalculateStatusForBorrowed()
    {
        $borrow = [
            'status' => 'Active',
            'actual_return_date' => null,
            'expected_return_date' => date('Y-m-d', strtotime('+7 days')),
        ];

        $calculatedStatus = $this->model->calculateStatus($borrow);
        
        $this->assertEquals('Borrowed', $calculatedStatus);
    }

    /**
     * Test: Calculate status respects Pending status
     */
    public function testCalculateStatusRespectsPending()
    {
        $borrow = [
            'status' => 'Pending',
            'actual_return_date' => null,
            'expected_return_date' => date('Y-m-d', strtotime('+7 days')),
        ];

        $calculatedStatus = $this->model->calculateStatus($borrow);
        
        $this->assertEquals('Pending', $calculatedStatus);
    }

    /**
     * Test: Validation messages are defined
     */
    public function testValidationMessagesAreDefined()
    {
        $messages = $this->model->validationMessages;
        
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('borrower_name', $messages);
        $this->assertArrayHasKey('folder_id', $messages);
        $this->assertArrayHasKey('expected_return_date', $messages);
    }

    /**
     * Test: Table name is correct
     */
    public function testTableNameIsCorrect()
    {
        $this->assertEquals('borrow_transactions', $this->model->table);
    }

    /**
     * Test: Primary key is transaction_id
     */
    public function testPrimaryKeyIsTransactionId()
    {
        $this->assertEquals('transaction_id', $this->model->primaryKey);
    }

    /**
     * Test: Model uses timestamps
     */
    public function testModelUsesTimestamps()
    {
        $this->assertTrue($this->model->useTimestamps);
    }
}
