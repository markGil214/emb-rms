<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRelocationRequestsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `relocation_requests` (
  `relocation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `from_location_id` int(10) unsigned NOT NULL,
  `to_location_id` int(10) unsigned NOT NULL,
  `reason` text NOT NULL,
  `reason_type` varchar(50) DEFAULT 'Other',
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `requested_by` int(10) unsigned NOT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`relocation_id`),
  KEY `relocation_requests_folder_id_foreign` (`folder_id`),
  KEY `relocation_requests_from_location_id_foreign` (`from_location_id`),
  KEY `relocation_requests_to_location_id_foreign` (`to_location_id`),
  KEY `relocation_requests_requested_by_foreign` (`requested_by`),
  KEY `relocation_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `relocation_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `relocation_requests_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `relocation_requests_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `relocation_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `relocation_requests_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('relocation_requests', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}

