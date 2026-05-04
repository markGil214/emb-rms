<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRestorationRequestsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `restoration_requests` (
  `restoration_request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `archive_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `requested_by` int(10) unsigned NOT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `requested_at` datetime NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`restoration_request_id`),
  KEY `restoration_requests_folder_id_foreign` (`folder_id`),
  KEY `restoration_requests_archive_id_foreign` (`archive_id`),
  KEY `restoration_requests_requested_by_foreign` (`requested_by`),
  KEY `restoration_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `restoration_requests_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restoration_requests_archive_id_foreign` FOREIGN KEY (`archive_id`) REFERENCES `archive_records` (`archive_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `restoration_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restoration_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );

        $this->syncPermissions();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        if ($this->db->tableExists('role_permissions')) {
            $this->db->table('role_permissions')->where('permission_key', 'approve_restore')->delete();
        }

        if ($this->db->tableExists('user_permissions')) {
            $this->db->table('user_permissions')->where('permission_key', 'approve_restore')->delete();
        }

        $this->forge->dropTable('restoration_requests', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    private function syncPermissions(): void
    {
        if (!$this->db->tableExists('roles') || !$this->db->tableExists('role_permissions')) {
            return;
        }

        $this->db->table('role_permissions')
            ->whereIn('permission_key', ['delete_archive', 'manage_retention'])
            ->delete();

        if ($this->db->tableExists('user_permissions')) {
            $this->db->table('user_permissions')
                ->whereIn('permission_key', ['delete_archive', 'manage_retention'])
                ->delete();
        }

        $roles = $this->db->table('roles')
            ->whereIn('role_name', ['admin', 'super_admin'])
            ->get()
            ->getResultArray();

        foreach ($roles as $role) {
            $exists = $this->db->table('role_permissions')
                ->where('role_id', $role['role_id'])
                ->where('permission_key', 'approve_restore')
                ->countAllResults();

            if (!$exists) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $role['role_id'],
                    'permission_key' => 'approve_restore',
                ]);
            }
        }
    }
}
