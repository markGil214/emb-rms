<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForeignKeysToBorrowTransactions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check which foreign keys already exist
        try {
            // Try to add FK for created_by -> users.user_id only if it doesn't exist
            $this->db->query(
                "ALTER TABLE borrow_transactions
                 ADD CONSTRAINT fk_borrow_transactions_created_by
                 FOREIGN KEY (created_by) REFERENCES users(user_id)
                 ON DELETE SET NULL ON UPDATE CASCADE"
            );
        } catch (\Throwable $e) {
            // Foreign key already exists, ignore
        }

        try {
            // Try to add FK for approved_by -> users.user_id only if it doesn't exist
            $this->db->query(
                "ALTER TABLE borrow_transactions
                 ADD CONSTRAINT fk_borrow_transactions_approved_by
                 FOREIGN KEY (approved_by) REFERENCES users(user_id)
                 ON DELETE SET NULL ON UPDATE CASCADE"
            );
        } catch (\Throwable $e) {
            // Foreign key already exists, ignore
        }
    }

    public function down()
    {
        $this->forge->dropForeignKey('borrow_transactions', 'fk_borrow_transactions_created_by');
        $this->forge->dropForeignKey('borrow_transactions', 'fk_borrow_transactions_approved_by');
    }
}
