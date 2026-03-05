<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'request_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => ['type' => 'INT', 'unsigned' => true],
            'borrower_id' => ['type' => 'INT', 'unsigned' => true],
            'priority' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Normal'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Pending'],
            'approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'decline_reason' => ['type' => 'TEXT', 'null' => true],
            'requested_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('request_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('borrower_id', 'borrowers', 'borrower_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('document_requests');
    }

    public function down()
    {
        $this->forge->dropTable('document_requests');
    }
}