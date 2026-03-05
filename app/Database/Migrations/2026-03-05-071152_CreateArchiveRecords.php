<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArchiveRecords extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'archive_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => ['type' => 'INT', 'unsigned' => true],
            'archived_date' => ['type' => 'DATE'],
            'archive_location_id' => ['type' => 'INT', 'unsigned' => true],
            'retention_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Active'],
            'retention_expiry_date' => ['type' => 'DATE', 'null' => true],
            'retention_policy_reference' => ['type' => 'TEXT', 'null' => true],
            'archived_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('archive_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('archive_location_id', 'locations', 'location_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('archived_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('archive_records');
    }

    public function down()
    {
        $this->forge->dropTable('archive_records');
    }
}