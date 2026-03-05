<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'user_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'username' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				'unique' => true,
			],
			'password' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'email' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
			],
			'role' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
				'default' => 'RecordsOfficer',
			],
			'created_at' => [
				'type' => 'TIMESTAMP',
				'null' => false,
			],
			'updated_at' => [
				'type' => 'TIMESTAMP',
				'null' => false,
			],
		]);

		$this->forge->addPrimaryKey('user_id');
		$this->forge->createTable('users');
	}

	public function down()
	{
		$this->forge->dropTable('users');
	}
}
