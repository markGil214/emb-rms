<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentVersions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'version_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => ['type' => 'INT', 'unsigned' => true],
            'version_number' => ['type' => 'INT', 'unsigned' => true],
            'changes_made' => ['type' => 'TEXT', 'null' => true],
            'old_values' => ['type' => 'TEXT', 'null' => true],
            'new_values' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('version_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('document_versions');
    }

    public function down()
    {
        $this->forge->dropTable('document_versions');
    }
}