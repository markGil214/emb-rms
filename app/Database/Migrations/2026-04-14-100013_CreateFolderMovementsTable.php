<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFolderMovementsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `folder_movements` (
  `movement_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `from_location_id` int(10) unsigned DEFAULT NULL,
  `to_location_id` int(10) unsigned DEFAULT NULL,
  `from_building` varchar(100) DEFAULT NULL,
  `to_building` varchar(100) DEFAULT NULL,
  `from_room` varchar(50) DEFAULT NULL,
  `to_room` varchar(50) DEFAULT NULL,
  `from_cabinet` varchar(50) DEFAULT NULL,
  `to_cabinet` varchar(50) DEFAULT NULL,
  `from_shelf` varchar(50) DEFAULT NULL,
  `to_shelf` varchar(50) DEFAULT NULL,
  `relocation_request_id` int(10) unsigned DEFAULT NULL,
  `moved_by` int(10) unsigned NOT NULL,
  `moved_at` datetime NOT NULL,
  `reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `from_location_label` varchar(255) DEFAULT NULL,
  `to_location_label` varchar(255) DEFAULT NULL,
  `confirmed_from_building` varchar(100) DEFAULT NULL,
  `confirmed_to_building` varchar(100) DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `completed_by` int(10) unsigned DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `movement_code` varchar(20) DEFAULT NULL,
  `folder_status_at_start` varchar(50) DEFAULT NULL,
  `folder_status_at_completion` varchar(50) DEFAULT NULL,
  `status_conflict_detected` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`movement_id`),
  KEY `folder_movements_folder_id_foreign` (`folder_id`),
  KEY `folder_movements_from_location_id_foreign` (`from_location_id`),
  KEY `folder_movements_to_location_id_foreign` (`to_location_id`),
  KEY `folder_movements_relocation_request_id_foreign` (`relocation_request_id`),
  KEY `folder_movements_moved_by_foreign` (`moved_by`),
  KEY `idx_movement_code` (`movement_code`),
  KEY `idx_fm_approved_by` (`approved_by`),
  KEY `idx_fm_completed_by` (`completed_by`),
  KEY `idx_status_conflict` (`status_conflict_detected`),
  KEY `idx_fm_audit_dates` (`approved_at`,`completed_at`),
  CONSTRAINT `fk_fm_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fm_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `folder_movements_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `folder_movements_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `folder_movements_moved_by_foreign` FOREIGN KEY (`moved_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `folder_movements_relocation_request_id_foreign` FOREIGN KEY (`relocation_request_id`) REFERENCES `relocation_requests` (`relocation_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `folder_movements_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('folder_movements', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
