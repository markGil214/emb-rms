<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFoldersTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `folders` (
  `folder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_code` varchar(50) DEFAULT NULL,
  `location_code` varchar(50) DEFAULT NULL,
  `company_name` varchar(100) NOT NULL,
  `folder_type` varchar(50) DEFAULT NULL,
  `folder_subtype` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Available',
  `current_borrow_transaction_id` int(10) unsigned DEFAULT NULL,
  `borrowed_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`folder_id`),
  UNIQUE KEY `file_code` (`file_code`),
  KEY `folders_location_id_foreign` (`location_id`),
  KEY `folders_created_by_foreign` (`created_by`),
  KEY `folders_updated_by_foreign` (`updated_by`),
  KEY `fk_folders_current_borrow_transaction` (`current_borrow_transaction_id`),
  CONSTRAINT `fk_folders_current_borrow_transaction` FOREIGN KEY (`current_borrow_transaction_id`) REFERENCES `borrow_transactions` (`transaction_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `folders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `folders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('folders', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}

