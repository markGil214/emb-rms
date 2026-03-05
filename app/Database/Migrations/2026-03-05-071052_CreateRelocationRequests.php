<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRelocationRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'relocation_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => ['type' => 'INT', 'unsigned' => true],
            'from_location_id' => ['type' => 'INT', 'unsigned' => true],
            'to_location_id' => ['type' => 'INT', 'unsigned' => true],
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

        $this->forge->addKey('relocation_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_location_id', 'locations', 'location_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('to_location_id', 'locations', 'location_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requested_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('relocation_requests');
    }

    public function down()
    {
        $this->forge->dropTable('relocation_requests');
    }
}