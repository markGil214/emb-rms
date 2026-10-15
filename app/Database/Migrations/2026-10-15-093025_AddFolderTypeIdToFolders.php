<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Addfoldertypeidtofolders extends Migration
{
	public function up()
	{
		//Add the folder_type_id column
		$this->forge->addColumn('folders', [
			'folder_type_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => true,
				'after' => 'folder_type'
			],
		]);

		//Add the foreign key constraint
		$this->db->query("
			ALTER TABLE folders
			ADD CONSTRAINT fk_folder_type
			FOREIGN KEY (folder_type_id)
			REFERENCES folder_type(id)
			ON UPDATE CASCADE
			ON DELETE RESTRICT
		");
	}

	public function down()
	{
		$this->forge->dropForeignKey(
			'folders',
			'fk_folder_type'
		);

		$this->forge->dropColumn('folders', 'folder_type_id');
	}
}
