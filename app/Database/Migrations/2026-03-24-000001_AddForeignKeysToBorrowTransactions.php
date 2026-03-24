<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForeignKeysToBorrowTransactions extends Migration
{
    public function up()
    {
        // Add missing FK for created_by -> users.user_id
        $this->db->query(
            "ALTER TABLE borrow_transactions
             ADD CONSTRAINT fk_borrow_transactions_created_by
             FOREIGN KEY (created_by) REFERENCES users(user_id)
             ON DELETE SET NULL ON UPDATE CASCADE"
        );

        // Add missing FK for approved_by -> users.user_id
        $this->db->query(
            "ALTER TABLE borrow_transactions
             ADD CONSTRAINT fk_borrow_transactions_approved_by
             FOREIGN KEY (approved_by) REFERENCES users(user_id)
             ON DELETE SET NULL ON UPDATE CASCADE"
        );
    }

    public function down()
    {
        $this->forge->dropForeignKey('borrow_transactions', 'fk_borrow_transactions_created_by');
        $this->forge->dropForeignKey('borrow_transactions', 'fk_borrow_transactions_approved_by');
    }
}
