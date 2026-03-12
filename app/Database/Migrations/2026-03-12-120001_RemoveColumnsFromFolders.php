<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveColumnsFromFolders extends Migration
{
	public function up()
	{
		$fields = ['folder_class', 'project_name', 'reference_number'];
		$this->forge->dropColumn('folders', $fields);
	}

	public function down()
	{
		$fields = [
			'folder_class' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'after' => 'folder_id',
			],
			'project_name' => [
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => true,
				'after' => 'company_name',
			],
			'reference_number' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => true,
				'after' => 'project_name',
			],
		];
		$this->forge->addColumn('folders', $fields);
	}
}
