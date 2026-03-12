<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveNotesFromFolders extends Migration
{
	public function up()
	{
		$this->forge->dropColumn('folders', 'notes');
	}

	public function down()
	{
		$this->forge->addColumn('folders', [
			'notes' => [
				'type' => 'TEXT',
				'null' => true,
				'after' => 'location_id',
			],
		]);
	}
}
