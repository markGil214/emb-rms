<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFolders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'folder_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'folder_class' => ['type' => 'VARCHAR', 'constraint' => 50],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'project_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'issuance_date' => ['type' => 'DATE', 'null' => true],
            'expiry_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Available'],
            'location_id' => ['type' => 'INT', 'unsigned' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);
        $this->forge->addKey('folder_id', true);
        $this->forge->addForeignKey('location_id', 'locations', 'location_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('folders');
    }

    public function down()
    {
        $this->forge->dropTable('folders');
    }
}