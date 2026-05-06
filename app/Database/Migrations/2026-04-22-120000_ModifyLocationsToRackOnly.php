<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyLocationsToRackOnly extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `locations` MODIFY `rack` TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `locations` MODIFY `rack` varchar(50) DEFAULT NULL');
    }
}
