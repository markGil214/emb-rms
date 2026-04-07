<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Updatefolderlocationsforcompletedrelocations extends Migration
{
	public function up()
	{
		// Update all folder locations based on completed relocations
		$this->db->query("
			UPDATE folders f
			INNER JOIN relocation_requests rr ON f.folder_id = rr.folder_id
			SET f.location_id = rr.to_location_id
			WHERE rr.status = 'Completed'
		");
	}

	public function down()
	{
		// No rollback - folders should stay in their new locations
	}
}
