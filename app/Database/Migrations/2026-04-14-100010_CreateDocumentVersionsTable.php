<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentVersionsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `document_versions` (
  `version_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `version_number` int(10) unsigned NOT NULL,
  `changes_made` text,
  `old_values` text,
  `new_values` text,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`version_id`),
  KEY `document_versions_folder_id_foreign` (`folder_id`),
  KEY `document_versions_created_by_foreign` (`created_by`),
  CONSTRAINT `document_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `document_versions_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('document_versions', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
