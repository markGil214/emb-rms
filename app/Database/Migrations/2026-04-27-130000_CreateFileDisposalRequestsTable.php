<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFileDisposalRequestsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `file_disposal_requests` (
  `disposal_request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `requested_by` int(10) unsigned NOT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `requested_at` datetime NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `disposed_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`disposal_request_id`),
  KEY `file_disposal_requests_file_id_foreign` (`file_id`),
  KEY `file_disposal_requests_requested_by_foreign` (`requested_by`),
  KEY `file_disposal_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `file_disposal_requests_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `folder_files` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `file_disposal_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `file_disposal_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('file_disposal_requests', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
