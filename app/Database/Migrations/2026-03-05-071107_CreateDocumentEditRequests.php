<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentEditRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'edit_request_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => ['type' => 'INT', 'unsigned' => true],
            'proposed_changes' => ['type' => 'TEXT'],
            'current_values' => ['type' => 'TEXT'],
            'reason' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Pending'],
            'requested_by' => ['type' => 'INT', 'unsigned' => true],
            'approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'requested_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'rejection_reason' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('edit_request_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requested_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('document_edit_requests');
    }

    public function down()
    {
        $this->forge->dropTable('document_edit_requests');
    }
}