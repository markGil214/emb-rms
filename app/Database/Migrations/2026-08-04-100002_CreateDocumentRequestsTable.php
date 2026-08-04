<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The original migration for this table was deleted from the repo after it
 * had already run, leaving the `document_requests` table with no migration
 * file even though DashboardController actively reads/writes it. This
 * recreates it (IF NOT EXISTS) from the live schema so fresh environments
 * built purely from `php spark migrate` don't end up missing the table.
 */
class CreateDocumentRequestsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `document_requests` (
  `request_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `borrower_id` int(10) unsigned NOT NULL,
  `priority` varchar(50) NOT NULL DEFAULT 'Normal',
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `decline_reason` text,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`request_id`),
  KEY `document_requests_folder_id_foreign` (`folder_id`),
  KEY `document_requests_borrower_id_foreign` (`borrower_id`),
  KEY `document_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `document_requests_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `document_requests_borrower_id_foreign` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `document_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('document_requests', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
