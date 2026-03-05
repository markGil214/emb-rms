<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisposalRecords extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'disposal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'archive_id' => ['type' => 'INT', 'unsigned' => true],
            'disposal_date' => ['type' => 'DATE'],
            'disposal_method' => ['type' => 'VARCHAR', 'constraint' => 50],
            'approved_by' => ['type' => 'INT', 'unsigned' => true],
            'compliance_reference' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('disposal_id', true);
        $this->forge->addForeignKey('archive_id', 'archive_records', 'archive_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('disposal_records');
    }

    public function down()
    {
        $this->forge->dropTable('disposal_records');
    }
}