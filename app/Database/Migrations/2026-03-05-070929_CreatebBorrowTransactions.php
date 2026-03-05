<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBorrowTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'transaction_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => ['type' => 'INT', 'unsigned' => true],
            'borrower_id' => ['type' => 'INT', 'unsigned' => true],
            'purpose' => ['type' => 'TEXT'],
            'borrowed_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'expected_return_date' => ['type' => 'DATETIME'],
            'actual_return_date' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Active'],
            'released_by' => ['type' => 'INT', 'unsigned' => true],
            'received_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'return_notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('transaction_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('borrower_id', 'borrowers', 'borrower_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('released_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('received_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('borrow_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('borrow_transactions');
    }
}