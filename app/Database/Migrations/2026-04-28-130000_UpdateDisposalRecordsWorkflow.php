<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateDisposalRecordsWorkflow extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('disposal_records')) {
            return;
        }

        $fields = $this->db->getFieldNames('disposal_records');

        if (!in_array('requested_by', $fields, true)) {
            $this->forge->addColumn('disposal_records', [
                'requested_by' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'disposal_method',
                ],
            ]);

            if ($this->db->tableExists('users')) {
                $this->db->query('ALTER TABLE `disposal_records` ADD CONSTRAINT `disposal_records_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE');
            }
        }

        if (in_array('disposal_date', $fields, true)) {
            $this->db->query('ALTER TABLE `disposal_records` MODIFY `disposal_date` date NULL DEFAULT NULL');
        }

        if (in_array('approved_by', $fields, true)) {
            $this->db->query('ALTER TABLE `disposal_records` MODIFY `approved_by` int(10) unsigned NULL DEFAULT NULL');
        }

        $fields = $this->db->getFieldNames('disposal_records');
        if (!in_array('updated_at', $fields, true)) {
            $this->forge->addColumn('disposal_records', [
                'updated_at' => [
                    'type' => 'TIMESTAMP',
                    'null' => true,
                    'default' => null,
                    'after' => 'created_at',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('disposal_records')) {
            return;
        }

        $fields = $this->db->getFieldNames('disposal_records');

        if (in_array('requested_by', $fields, true)) {
            try {
                $this->db->query('ALTER TABLE `disposal_records` DROP FOREIGN KEY `disposal_records_requested_by_foreign`');
            } catch (\Throwable $e) {
                // Some environments may not have created the optional FK.
            }
            $this->forge->dropColumn('disposal_records', 'requested_by');
        }

        if (in_array('updated_at', $fields, true)) {
            $this->forge->dropColumn('disposal_records', 'updated_at');
        }
    }
}
