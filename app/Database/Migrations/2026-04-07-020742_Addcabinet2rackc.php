<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Addcabinet2rackc extends Migration
{
	public function up()
	{
		// Add Cabinet 2 - Rack C location
		$data = [
			'cabinet' => '2',
			'rack' => 'C',
			'capacity' => 0,
			'current_count' => 0,
			'is_archive_location' => 0,
			'coordinates_3d' => NULL,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		];
		$this->db->table('locations')->insert($data);
	}

	public function down()
	{
		// Remove Cabinet 2 - Rack C location
		$this->db->table('locations')->where('cabinet', '2')->where('rack', 'C')->delete();
	}
}
