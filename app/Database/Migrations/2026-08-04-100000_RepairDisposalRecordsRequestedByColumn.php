<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RepairDisposalRecordsRequestedByColumn extends Migration
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
                try {
                    $this->db->query('ALTER TABLE `disposal_records` ADD CONSTRAINT `disposal_records_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE');
                } catch (\Throwable $e) {
                    // FK may already exist in some environments.
                }
            }
        }

        $fields = $this->db->getFieldNames('disposal_records');
        if (!in_array('updated_at', $fields, true)) {
            // Forge's addColumn() drops the NULL/DEFAULT clause for TIMESTAMP
            // columns on this MySQL config, tripping "Invalid default value"
            // under strict mode — use raw SQL instead.
            $this->db->query('ALTER TABLE `disposal_records` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`');
        }

        // Defensive: earlier repair migrations should have set these, but reassert
        // them here since this table's DDL has drifted from the migration ledger before.
        $fields = $this->db->getFieldNames('disposal_records');
        if (!in_array('status', $fields, true)) {
            $this->forge->addColumn('disposal_records', [
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => false,
                    'default'    => 'Pending',
                    'after'      => 'disposal_method',
                ],
            ]);
        }

        $this->db->query('ALTER TABLE `disposal_records` MODIFY `disposal_date` date NULL DEFAULT NULL');
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
                // Some environments may not have created the FK.
            }
            $this->forge->dropColumn('disposal_records', 'requested_by');
        }

        if (in_array('updated_at', $fields, true)) {
            $this->forge->dropColumn('disposal_records', 'updated_at');
        }
    }
}
