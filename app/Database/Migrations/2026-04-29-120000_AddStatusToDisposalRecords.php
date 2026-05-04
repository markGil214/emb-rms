<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToDisposalRecords extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('disposal_records')) {
            return;
        }

        $fields = $this->db->getFieldNames('disposal_records');
        if (! in_array('status', $fields, true)) {
            $this->forge->addColumn('disposal_records', [
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 32,
                    'null' => false,
                    'default' => 'Pending',
                    'after' => 'disposal_method',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('disposal_records')) {
            return;
        }

        $fields = $this->db->getFieldNames('disposal_records');
        if (in_array('status', $fields, true)) {
            $this->forge->dropColumn('disposal_records', 'status');
        }
    }
}
