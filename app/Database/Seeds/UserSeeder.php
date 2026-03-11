<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
	public function run()
	{
		$datas = [
			[
				'username' => 'superadmin',
				'password' => password_hash('SuperPass123', PASSWORD_DEFAULT),
				'email' => 'superadmin@example.com',
				'role' => 'SuperAdmin',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			],
			[
				'username' => 'adminuser',
				'password' => password_hash('AdminPass123', PASSWORD_DEFAULT),
				'email' => 'admin@example.com',
				'role' => 'Admin',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			],
			[
				'username' => 'ro_officer',
				'password' => password_hash('OfficerPass123', PASSWORD_DEFAULT),
				'email' => 'records@example.com',
				'role' => 'RecordsOfficer',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			],
		];

		$this->db->table('users')->insertBatch($datas);
	}
}