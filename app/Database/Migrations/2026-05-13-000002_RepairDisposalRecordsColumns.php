<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RepairDisposalRecordsColumns extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('disposal_records')) {
            return;
        }

        $fields = $this->db->getFieldNames('disposal_records');

        // Ensure status column exists
        if (!in_array('status', $fields, true)) {
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

        // Ensure disposal_date allows NULL
        $this->db->query('ALTER TABLE `disposal_records` MODIFY `disposal_date` date NULL DEFAULT NULL');
    }

    public function down()
    {
        // No down needed for repair migration
    }
}
