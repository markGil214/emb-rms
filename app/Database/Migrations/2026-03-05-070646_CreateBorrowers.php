<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBorrowers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'borrower_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'office_department' => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
        ]);
        $this->forge->addKey('borrower_id', true);
        $this->forge->createTable('borrowers');
    }

    public function down()
    {
        $this->forge->dropTable('borrowers');
    }
}