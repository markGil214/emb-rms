<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBorrowTransactionsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `borrow_transactions` (
  `transaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `borrower_name` varchar(255) DEFAULT NULL,
  `purpose` text NOT NULL,
  `borrowed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expected_return_date` datetime NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `released_by` int(10) unsigned DEFAULT NULL,
  `received_by` int(10) unsigned DEFAULT NULL,
  `return_notes` text,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `approved_at` datetime DEFAULT NULL COMMENT 'When admin approved the borrow request',
  `approved_by` int(11) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL COMMENT 'Soft delete timestamp',
  `created_by` int(11) unsigned DEFAULT NULL,
  `notification_status` varchar(50) DEFAULT 'None' COMMENT 'Tracks sent notifications: None | 1_day_sent | 3_day_sent | 7_day_sent',
  `last_notification_sent_at` datetime DEFAULT NULL COMMENT 'Timestamp of last notification sent',
  `escalated_to_manager` tinyint(1) DEFAULT '0' COMMENT 'True if escalated to manager at 7+ days overdue',
  `borrower_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `borrow_transactions_folder_id_foreign` (`folder_id`),
  KEY `borrow_transactions_released_by_foreign` (`released_by`),
  KEY `borrow_transactions_received_by_foreign` (`received_by`),
  KEY `fk_borrow_transactions_created_by` (`created_by`),
  KEY `fk_borrow_transactions_approved_by` (`approved_by`),
  CONSTRAINT `borrow_transactions_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `borrow_transactions_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `borrow_transactions_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_borrow_transactions_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_borrow_transactions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('borrow_transactions', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}

