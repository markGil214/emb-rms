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
				'password' => password_hash('SuperPass123', PASSWORD_BCRYPT),
				'email' => 'superadmin@example.com',
				'role' => 'SuperAdmin',
			],
			[
				'username' => 'adminuser',
				'password' => password_hash('AdminPass123', PASSWORD_BCRYPT),
				'email' => 'admin@example.com',
				'role' => 'Admin',
			],
			[
				'username' => 'ro_officer',
				'password' => password_hash('OfficerPass123', PASSWORD_BCRYPT),
				'email' => 'records@example.com',
				'role' => 'RecordsOfficer',
			],
		];

		foreach ($datas as $data) {
			$this->db->table('users')->insert($data);
		}
	}
}