<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * UserModel::$allowedFields has included 'manager_id' and
 * OverdueNotificationService::escalateToManager() already reads
 * $user['manager_id'] to find who to notify on 7+ day overdue borrows, but
 * the column was never actually added to `users`. Escalation has been
 * silently falling back to the SuperAdmin every time as a result.
 */
class AddManagerIdToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldNames('users');

        if (!in_array('manager_id', $fields, true)) {
            // `users.updated_at` still carries the original migration's
            // '0000-00-00 00:00:00' default. Under this server's NO_ZERO_DATE
            // sql_mode, MySQL revalidates every column's default on ANY
            // ALTER TABLE, so altering `users` at all fails with "Invalid
            // default value for 'updated_at'" unless that mode is relaxed
            // for the duration of the statement.
            $originalSqlMode = $this->db->query('SELECT @@SESSION.sql_mode AS m')->getRow()->m;
            $this->db->query("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', '')");

            try {
                $this->forge->addColumn('users', [
                    'manager_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'role',
                    ],
                ]);

                $this->db->query('ALTER TABLE `users` ADD CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE');
            } finally {
                $this->db->query('SET SESSION sql_mode = ?', [$originalSqlMode]);
            }
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldNames('users');

        if (in_array('manager_id', $fields, true)) {
            try {
                $this->db->query('ALTER TABLE `users` DROP FOREIGN KEY `users_manager_id_foreign`');
            } catch (\Throwable $e) {
                // Some environments may not have created the FK.
            }
            $this->forge->dropColumn('users', 'manager_id');
        }
    }
}
