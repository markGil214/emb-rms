<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRememberTokenToUsers extends Migration
{
	public function up()
	{
		$fields = [
			'remember_token' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			]
		];
		$this->forge->addColumn('users', $fields);
	}

	public function down()
	{
	 $this->forge->dropColumn('users', 'remember_token');
	}
}
