<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `audit_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `entity_type` varchar(50) DEFAULT NULL,
            `entity_id` int(11) DEFAULT NULL,
            `action` varchar(100) DEFAULT NULL,
            `old_data` text DEFAULT NULL,
            `new_data` text DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`log_id`),
            KEY `idx_user` (`user_id`),
            KEY `idx_entity` (`entity_type`, `entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `audit_logs`;");
    }
}
