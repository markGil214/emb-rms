<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLocations extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'location_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true
			],
			'cabinet' => ['type' => 'VARCHAR', 'constraint' => 50],
			'shelf' => ['type' => 'VARCHAR', 'constraint' => 50],
			'rack' => ['type' => 'VARCHAR', 'constraint' => 50],
			'folder_label' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
			'capacity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
			'current_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
			'is_archive_location' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
			'coordinates_3d' => ['type' => 'TEXT', 'null' => true],
			'created_at' => ['type' => 'TIMESTAMP', 'null' => false],
			'updated_at' => ['type' => 'TIMESTAMP', 'null' => false]
		]);
		$this->forge->addKey('location_id', true);
		$this->forge->createTable('locations');
	}

	public function down()
	{
		$this->forge->dropTable('locations');
	}
}