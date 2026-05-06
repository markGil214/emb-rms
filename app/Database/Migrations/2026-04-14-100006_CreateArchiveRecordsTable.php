<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArchiveRecordsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `archive_records` (
  `archive_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `archived_date` date NOT NULL,
  `archive_location_id` int(10) unsigned NOT NULL,
  `archived_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`archive_id`),
  KEY `archive_records_folder_id_foreign` (`folder_id`),
  KEY `archive_records_archive_location_id_foreign` (`archive_location_id`),
  KEY `archive_records_archived_by_foreign` (`archived_by`),
  CONSTRAINT `archive_records_archive_location_id_foreign` FOREIGN KEY (`archive_location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `archive_records_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `archive_records_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('archive_records', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
