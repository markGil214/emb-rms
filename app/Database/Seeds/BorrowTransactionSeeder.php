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

        // Get sample users and folders for foreign keys
        $users = $this->db->table('users')->get()->getResultArray();
        $folders = $this->db->table('folders')->limit(3)->get()->getResultArray();

        if (empty($users) || empty($folders)) {
            echo "⚠️  Skipping BorrowTransactionSeeder: Requires users and folders to be seeded first.\n";
            return;
        }

        $hasBorrowerName = $this->db->fieldExists('borrower_name', 'borrow_transactions');
        $hasBorrowerEmail = $this->db->fieldExists('borrower_email', 'borrow_transactions');
        $hasCreatedBy = $this->db->fieldExists('created_by', 'borrow_transactions');
        $hasApprovedBy = $this->db->fieldExists('approved_by', 'borrow_transactions');
        $hasNotificationStatus = $this->db->fieldExists('notification_status', 'borrow_transactions');
        $hasEscalatedToManager = $this->db->fieldExists('escalated_to_manager', 'borrow_transactions');

        $releaser = $users[1] ?? $users[0]; // Admin
        $receiver = $users[2] ?? $users[0]; // Records officer
        $borrowers = [
            [
                'name' => 'John Smith',
                'email' => 'john@company.com',
                'department' => 'Legal',
            ],
            [
                'name' => 'Jane Doe',
                'email' => 'jane@company.com',
                'department' => 'HR',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@company.com',
                'department' => 'Finance',
            ],
        ];

        $transactions = [];
        $sampleRows = [
            [
                'folder_id' => $folders[0]['folder_id'],
                'borrower' => $borrowers[0],
                'purpose' => 'Compliance audit verification',
                'borrowed_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'expected_return_date' => date('Y-m-d', strtotime('+15 days')),
                'actual_return_date' => null,
                'status' => 'Borrowed',
                'released_by' => $releaser['user_id'],
                'received_by' => $receiver['user_id'],
                'return_notes' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'folder_id' => $folders[1]['folder_id'],
                'borrower' => $borrowers[1],
                'purpose' => 'Legal case review - Court deadline extension',
                'borrowed_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'expected_return_date' => date('Y-m-d', strtotime('-10 days')),
                'actual_return_date' => null,
                'status' => 'Borrowed',
                'released_by' => $releaser['user_id'],
                'received_by' => $receiver['user_id'],
                'return_notes' => 'Still in use - extended deadline pending',
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'folder_id' => $folders[2]['folder_id'],
                'borrower' => $borrowers[2],
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
            ],
        ];

        foreach ($sampleRows as $row) {
            $transaction = [
                'folder_id' => $row['folder_id'],
                'purpose' => $row['purpose'],
                'borrowed_at' => $row['borrowed_at'],
                'expected_return_date' => $row['expected_return_date'],
                'actual_return_date' => $row['actual_return_date'],
                'status' => $row['status'],
                'released_by' => $row['released_by'],
                'received_by' => $row['received_by'],
                'return_notes' => $row['return_notes'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];

            if ($hasBorrowerName) {
                $transaction['borrower_name'] = $row['borrower']['name'];
            }

            if ($hasBorrowerEmail) {
                $transaction['borrower_email'] = $row['borrower']['email'];
            }

            if ($hasCreatedBy) {
                $transaction['created_by'] = $releaser['user_id'];
            }

            if ($hasApprovedBy && $row['status'] !== 'Pending') {
                $transaction['approved_by'] = $releaser['user_id'];
            }

            if ($hasNotificationStatus) {
                $transaction['notification_status'] = 'None';
            }

            if ($hasEscalatedToManager) {
                $transaction['escalated_to_manager'] = 0;
            }

            $transactions[] = $transaction;
        }

        $this->db->table('borrow_transactions')->insertBatch($transactions);
    }
}
