<?php

// Load the framework
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

echo "DEBUG: Borrow Creation Test\n";
echo "===========================\n\n";

echo "--- Database Check ---\n";
try {
    $db = \Config\Database::connect();
    echo "✓ Database connected\n";
    
    $tables = $db->listTables();
    echo "✓ " . count($tables) . " tables found\n\n";
    
    // Check folders table
    echo "--- Folders Table ---\n";
    if (in_array('folders', $tables)) {
        $folders = $db->table('folders')->limit(5)->get()->getResultArray();
        echo "✓ folders table exists\n";
        echo "  Total: " . $db->table('folders')->countAllResults() . " folders\n";
        echo "  Available: " . $db->table('folders')->where('status', 'Available')->countAllResults() . " folders\n";
        
        if (count($folders) > 0) {
            echo "  Sample folders:\n";
            foreach ($folders as $f) {
                echo "    - ID {$f['folder_id']}: {$f['file_code']} (Status: {$f['status']})\n";
            }
        }
    }
    
    // Check borrow_transactions table
    echo "\n--- Borrow Transactions Table ---\n";
    if (in_array('borrow_transactions', $tables)) {
        echo "✓ borrow_transactions table exists\n";
        
        $fields = $db->getFieldData('borrow_transactions');
        echo "  Columns:\n";
        foreach ($fields as $field) {
            echo "    - {$field->name} ({$field->type})\n";
        }
    } else {
        echo "✗ borrow_transactions table MISSING\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
}

echo "\n--- Model Validation ---\n";
try {
    $model = new \App\Models\BorrowTransactionModel();
    echo "✓ BorrowTransactionModel loaded\n";
    echo "  Table: {$model->table}\n";
    echo "  Primary Key: {$model->primaryKey}\n";
    echo "  Allowed Fields:\n";
    foreach ($model->allowedFields as $field) {
        echo "    - $field\n";
    }
    
    echo "\n  Validation Rules:\n";
    foreach ($model->validationRules as $field => $rules) {
        echo "    - $field: $rules\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Model Error: " . $e->getMessage() . "\n";
}

echo "\n--- Test Validation ---\n";
try {
    $db = \Config\Database::connect();
    $folder = $db->table('folders')->where('status', 'Available')->first();
    
    if ($folder) {
        echo "✓ Found available folder ID: {$folder['folder_id']}\n\n";
        
        $testData = [
            'borrower_name'       => 'John Doe',
            'folder_id'          => $folder['folder_id'],
            'expected_return_date' => date('Y-m-d', strtotime('+7 days')),
            'notes'              => 'Test borrow request',
        ];
        
        echo "Test data:\n";
        foreach ($testData as $key => $value) {
            echo "  $key: $value\n";
        }
        
        $model = new \App\Models\BorrowTransactionModel();
        
        if ($model->validate($testData)) {
            echo "\n✓ VALIDATION PASSED\n";
            
            // Try to insert
            if ($model->save($testData)) {
                $insertedId = $model->getInsertID();
                echo "✓ BORROW CREATED - Transaction ID: $insertedId\n";
                
                // Verify insert
                $inserted = $db->table('borrow_transactions')->where('transaction_id', $insertedId)->first();
                if ($inserted) {
                    echo "\nInserted data:\n";
                    foreach ($inserted as $key => $value) {
                        echo "  $key: $value\n";
                    }
                }
            } else {
                echo "\n✗ SAVE FAILED\n";
                echo "Error: " . $model->errors() . "\n";
            }
        } else {
            echo "\n✗ VALIDATION FAILED\n";
            foreach ($model->errors() as $field => $error) {
                echo "  $field: $error\n";
            }
        }
    } else {
        echo "✗ NO AVAILABLE FOLDERS\n";
        $allfCount = $db->table('folders')->countAllResults();
        echo "Total folders in DB: $allfCount\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n===========================\n";
echo "Debug Complete\n";
