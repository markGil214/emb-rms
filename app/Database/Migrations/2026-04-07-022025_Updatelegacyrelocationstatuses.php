<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Updatelegacyrelocationstatuses extends Migration
{
	public function up()
	{
		// Update legacy Approved and In Progress statuses to Completed
		$this->db->table('relocation_requests')
			->whereIn('status', ['Approved', 'In Progress'])
			->update(['status' => 'Completed']);
	}

	public function down()
	{
		// No rollback - these are legacy records that should stay as Completed
	}
}
