<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileIndexingToFolders extends Migration
{
	public function up()
	{
		$fields = [
			'file_code' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'unique' => true,
				'null' => true,
				'after' => 'folder_id',
			],
			'location_code' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => true,
				'after' => 'file_code',
			],
		];

		$this->forge->addColumn('folders', $fields);

		// Add index for location_code (since file_code already has unique index)
		$this->forge->addKey('location_code');
	}

	public function down()
	{
		$this->forge->dropColumn('folders', ['file_code', 'location_code']);
	}
}