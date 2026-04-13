<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `locations` (
  `location_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rack` varchar(50) DEFAULT NULL,
  `shelf` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT '0',
  `current_count` int(11) NOT NULL DEFAULT '0',
  `is_archive_location` tinyint(1) NOT NULL DEFAULT '0',
  `coordinates_3d` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('locations', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}

