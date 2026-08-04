<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The original migration for this table was deleted from the repo after it
 * had already run, leaving `document_edit_requests` with no migration file
 * even though FolderController's edit-request flow actively reads/writes it.
 * This recreates it (IF NOT EXISTS) from the live schema so fresh
 * environments built purely from `php spark migrate` don't end up missing
 * the table.
 */
class CreateDocumentEditRequestsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `document_edit_requests` (
  `edit_request_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `proposed_changes` text NOT NULL,
  `current_values` text NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `requested_by` int(10) unsigned NOT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`edit_request_id`),
  KEY `document_edit_requests_folder_id_foreign` (`folder_id`),
  KEY `document_edit_requests_requested_by_foreign` (`requested_by`),
  KEY `document_edit_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `document_edit_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `document_edit_requests_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `document_edit_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('document_edit_requests', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
