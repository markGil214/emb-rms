<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisposalRecordsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `disposal_records` (
  `disposal_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `archive_id` int(10) unsigned NOT NULL,
  `disposal_date` date NOT NULL,
  `disposal_method` varchar(50) NOT NULL,
  `approved_by` int(10) unsigned NOT NULL,
  `compliance_reference` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`disposal_id`),
  KEY `disposal_records_archive_id_foreign` (`archive_id`),
  KEY `disposal_records_approved_by_foreign` (`approved_by`),
  CONSTRAINT `disposal_records_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `disposal_records_archive_id_foreign` FOREIGN KEY (`archive_id`) REFERENCES `archive_records` (`archive_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('disposal_records', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}

