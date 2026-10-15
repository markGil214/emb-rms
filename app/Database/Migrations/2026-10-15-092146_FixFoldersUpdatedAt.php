<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixFoldersUpdatedAt extends Migration
{
	public function up()
	{
		$this->db->query("
			ALTER TABLE folders
			MODIFY COLUMN updated_at TIMESTAMP
			NOT NULL DEFAULT CURRENT_TIMESTAMP
			ON UPDATE CURRENT_TIMESTAMP
		");
	}

	public function down()
	{
		$this->db->query("
			ALTER TABLE folders
			MODIFY COLUMN updated_at TIMESTAMP
			NOT NULL DEFAULT CURRENT_TIMESTAMP
		");
	}
}
