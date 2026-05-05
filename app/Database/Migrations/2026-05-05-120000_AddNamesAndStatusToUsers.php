<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNamesAndStatusToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldNames('users');

        if (! in_array('first_name', $fields, true)) {
            $this->forge->addColumn('users', [
                'first_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'username',
                ],
            ]);
        }

        $fields = $this->db->getFieldNames('users');
        if (! in_array('last_name', $fields, true)) {
            $this->forge->addColumn('users', [
                'last_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'first_name',
                ],
            ]);
        }

        $fields = $this->db->getFieldNames('users');
        if (! in_array('status', $fields, true)) {
            $this->forge->addColumn('users', [
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 16,
                    'null' => false,
                    'default' => 'Active',
                    'after' => 'role',
                ],
            ]);
        }

        $this->db->query("UPDATE users SET status = 'Active' WHERE status IS NULL OR TRIM(status) = ''");
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldNames('users');

        if (in_array('status', $fields, true)) {
            $this->forge->dropColumn('users', 'status');
        }

        $fields = $this->db->getFieldNames('users');
        if (in_array('last_name', $fields, true)) {
            $this->forge->dropColumn('users', 'last_name');
        }

        $fields = $this->db->getFieldNames('users');
        if (in_array('first_name', $fields, true)) {
            $this->forge->dropColumn('users', 'first_name');
        }
    }
}
