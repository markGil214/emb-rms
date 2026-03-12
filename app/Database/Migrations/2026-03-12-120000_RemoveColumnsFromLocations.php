<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveColumnsFromLocations extends Migration
{
	public function up()
	{
		$fields = ['shelf', 'folder_label'];
		$this->forge->dropColumn('locations', $fields);
	}

	public function down()
	{
		$fields = [
			'shelf' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'after' => 'cabinet',
			],
			'folder_label' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => true,
				'after' => 'rack',
			],
		];
		$this->forge->addColumn('locations', $fields);
	}
}
