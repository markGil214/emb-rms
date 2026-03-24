<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BorrowTransactionSeeder extends Seeder
{
    public function run()
    {
        // Check if borrow transactions already exist
        $existing = $this->db->table('borrow_transactions')->get()->getNumRows();
        if ($existing > 0) {
            echo "ℹ️  Borrow transactions already seeded. Skipping BorrowTransactionSeeder.\n";
            return;
        }

        // Ensure borrowers exist
        $borrowers = $this->db->table('borrowers')->get()->getResultArray();
        if (empty($borrowers)) {
            // Create sample borrowers
            $sampleBorrowers = [
                ['full_name' => 'John Smith', 'office_department' => 'Legal', 'contact_number' => '555-0101', 'email' => 'john@company.com'],
                ['full_name' => 'Jane Doe', 'office_department' => 'HR', 'contact_number' => '555-0102', 'email' => 'jane@company.com'],
                ['full_name' => 'Bob Johnson', 'office_department' => 'Finance', 'contact_number' => '555-0103', 'email' => 'bob@company.com'],
            ];
            $this->db->table('borrowers')->insertBatch($sampleBorrowers);
            $borrowers = $this->db->table('borrowers')->get()->getResultArray();
        }

        // Get sample users and folders for foreign keys
        $users = $this->db->table('users')->get()->getResultArray();
        $folders = $this->db->table('folders')->limit(3)->get()->getResultArray();

        if (empty($users) || empty($folders)) {
            echo "⚠️  Skipping BorrowTransactionSeeder: Requires users and folders to be seeded first.\n";
            return;
        }

        $releaser = $users[1] ?? $users[0]; // Admin
        $receiver = $users[2] ?? $users[0]; // Records officer

        $transactions = [];
        $today = date('Y-m-d');
        
        // Active borrow (borrowed 5 days ago, due 20 days from borrow date)
        $transactions[] = [
            'folder_id' => $folders[0]['folder_id'],
            'borrower_id' => $borrowers[0]['borrower_id'],
            'purpose' => 'Compliance audit verification',
            'borrowed_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'expected_return_date' => date('Y-m-d', strtotime('+15 days')),
            'actual_return_date' => null,
            'status' => 'Active',
            'released_by' => $releaser['user_id'],
            'received_by' => $receiver['user_id'],
            'return_notes' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Overdue borrow (borrowed 30 days ago, was due 10 days ago)
        $transactions[] = [
            'folder_id' => $folders[1]['folder_id'],
            'borrower_id' => $borrowers[1]['borrower_id'],
            'purpose' => 'Legal case review - Court deadline extension',
            'borrowed_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'expected_return_date' => date('Y-m-d', strtotime('-10 days')),
            'actual_return_date' => null,
            'status' => 'Overdue',
            'released_by' => $releaser['user_id'],
            'received_by' => $receiver['user_id'],
            'return_notes' => 'Still in use - extended deadline pending',
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
        ];

        // Returned borrow (borrowed 20 days ago, returned 5 days ago)
        $transactions[] = [
            'folder_id' => $folders[2]['folder_id'],
            'borrower_id' => $borrowers[2]['borrower_id'],
            'purpose' => 'Annual retention review',
            'borrowed_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'expected_return_date' => date('Y-m-d', strtotime('-5 days')),
            'actual_return_date' => date('Y-m-d H:i:s', strtotime('-4 days')),
            'status' => 'Returned',
            'released_by' => $releaser['user_id'],
            'received_by' => $receiver['user_id'],
            'return_notes' => 'Good condition, no issues',
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
        ];

        $this->db->table('borrow_transactions')->insertBatch($transactions);
    }
}
