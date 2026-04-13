<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
	public function run()
	{
		if (! $this->tableExists('users')) {
			echo "⚠️  Skipping UserSeeder: users table does not exist. Run migrations first.\n";
			return;
		}

		// Check if users already exist
		$existingUsers = $this->db->table('users')->countAllResults();
		if ($existingUsers > 0) {
			echo "ℹ️  Users already seeded. Skipping UserSeeder.\n";
			return;
		}

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

	private function tableExists(string $table): bool
	{
		$result = $this->db->query(
			'SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
			[$table]
		)->getRowArray();

		return !empty($result) && (int) $result['cnt'] > 0;
	}
}