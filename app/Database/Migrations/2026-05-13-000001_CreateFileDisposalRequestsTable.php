<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFileDisposalRequestsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('file_disposal_requests')) {
            return;
        }

        $this->forge->addField([
            'disposal_request_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'file_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Pending',
            ],
            'requested_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'requested_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'disposed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('disposal_request_id', true);
        $this->forge->addKey('file_id');
        $this->forge->addKey('requested_by');
        $this->forge->addKey('approved_by');
        $this->forge->createTable('file_disposal_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('file_disposal_requests', true);
    }
}
