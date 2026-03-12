<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFolderFiles extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'file_id' => [
				'type' => 'INT',
				'unsigned' => true,
				'auto_increment' => true
			],
			'folder_id' => [
				'type' => 'INT',
				'unsigned' => true,
			],
			'file_name' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'file_path' => [
				'type' => 'VARCHAR',
				'constraint' => 500,
			],
			'file_size' => [
				'type' => 'BIGINT',
				'unsigned' => true,
			],
			'uploaded_by' => [
				'type' => 'INT',
				'unsigned' => true,
			],
			'created_at' => [
				'type' => 'TIMESTAMP',
				'null' => false,
			],
			'updated_at' => [
				'type' => 'TIMESTAMP',
				'null' => false,
			]
		]);
		
		$this->forge->addKey('file_id', true);
		$this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
		$this->forge->addForeignKey('uploaded_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
		$this->forge->createTable('folder_files');
	}

	public function down()
	{
		$this->forge->dropTable('folder_files');
	}
}
