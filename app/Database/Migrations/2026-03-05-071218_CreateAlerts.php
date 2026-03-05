<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlerts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'alert_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'transaction_id' => ['type' => 'INT', 'unsigned' => true],
            'alert_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'message' => ['type' => 'TEXT'],
            'is_resolved' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'triggered_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);

        $this->forge->addKey('alert_id', true);
        $this->forge->addForeignKey('transaction_id', 'borrow_transactions', 'transaction_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('alerts');
    }

    public function down()
    {
        $this->forge->dropTable('alerts');
    }
}