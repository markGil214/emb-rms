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

		$fields = $this->db->getFieldNames('users');
		$hasFirstName = in_array('first_name', $fields, true);
		$hasLastName = in_array('last_name', $fields, true);
		$hasStatus = in_array('status', $fields, true);
		$hasInactiveAt = in_array('inactive_at', $fields, true);

		// Check if users already exist
		$existingUsers = $this->db->table('users')->countAllResults();
		if ($existingUsers > 0) {
			$defaults = [
				'superadmin' => ['first_name' => 'Super', 'last_name' => 'Admin'],
				'adminuser' => ['first_name' => 'Admin', 'last_name' => 'User'],
				'ro_officer' => ['first_name' => 'Records', 'last_name' => 'Officer'],
			];

			foreach ($defaults as $username => $nameData) {
				$user = $this->db->table('users')
					->select('user_id, first_name, last_name, status')
					->where('username', $username)
					->get()
					->getRowArray();

				if (!$user) {
					continue;
				}

				$updateData = [];
				if ($hasFirstName && trim((string) ($user['first_name'] ?? '')) === '') {
					$updateData['first_name'] = $nameData['first_name'];
				}
				if ($hasLastName && trim((string) ($user['last_name'] ?? '')) === '') {
					$updateData['last_name'] = $nameData['last_name'];
				}
				if ($hasStatus && trim((string) ($user['status'] ?? '')) === '') {
					$updateData['status'] = 'Active';
				}
				if ($hasInactiveAt && ($updateData['status'] ?? $user['status'] ?? 'Active') === 'Active' && trim((string) ($user['inactive_at'] ?? '')) !== '') {
					$updateData['inactive_at'] = null;
				}

				if (!empty($updateData)) {
					$updateData['updated_at'] = date('Y-m-d H:i:s');
					$this->db->table('users')
						->where('user_id', (int) $user['user_id'])
						->update($updateData);
				}
			}

			if ($this->tableExists('user_roles') && $this->tableExists('roles')) {
				$roleLookup = [
					'superadmin' => 'super_admin',
					'adminuser' => 'admin',
					'ro_officer' => 'records_officer',
					'SuperAdmin' => 'super_admin',
					'Admin' => 'admin',
					'RecordsOfficer' => 'records_officer',
				];

				$roles = $this->db->table('roles')
					->select('role_id, role_name')
					->get()
					->getResultArray();

				$roleIdsByName = [];
				foreach ($roles as $role) {
					$roleIdsByName[$role['role_name']] = (int) $role['role_id'];
				}

				$existingAssignments = $this->db->table('user_roles')
					->select('user_id, role_id')
					->get()
					->getResultArray();

				$assignmentByUserId = [];
				foreach ($existingAssignments as $assignment) {
					$assignmentByUserId[(int) $assignment['user_id']] = (int) $assignment['role_id'];
				}

				$allUsers = $this->db->table('users')
					->select('user_id, username, role')
					->get()
					->getResultArray();

				foreach ($allUsers as $user) {
					$userId = (int) $user['user_id'];
					$username = trim((string) ($user['username'] ?? ''));
					$legacyRole = trim((string) ($user['role'] ?? ''));

					$roleName = $roleLookup[$username] ?? $roleLookup[$legacyRole] ?? null;
					if ($roleName === null || !isset($roleIdsByName[$roleName])) {
						continue;
					}

					$roleId = $roleIdsByName[$roleName];
					if (isset($assignmentByUserId[$userId])) {
						if ($assignmentByUserId[$userId] !== $roleId) {
							$this->db->table('user_roles')
								->where('user_id', $userId)
								->update([
									'role_id' => $roleId,
									'assigned_at' => date('Y-m-d H:i:s'),
									'assigned_by_id' => null,
								]);
						}
						continue;
					}

					$this->db->table('user_roles')->insert([
						'user_id' => $userId,
						'role_id' => $roleId,
						'assigned_at' => date('Y-m-d H:i:s'),
						'assigned_by_id' => null,
					]);
				}
			}

			// One-time fallback for all existing users: derive names from username pattern.
			$allUsers = $this->db->table('users')
				->select('user_id, username, first_name, last_name, status')
				->get()
				->getResultArray();

			foreach ($allUsers as $user) {
				$firstName = trim((string) ($user['first_name'] ?? ''));
				$lastName = trim((string) ($user['last_name'] ?? ''));
				$username = trim((string) ($user['username'] ?? ''));

				if ($username === '') {
					continue;
				}

				$parts = preg_split('/[._\-\s]+/', $username) ?: [];
				$parts = array_values(array_filter($parts, static function ($value) {
					return trim((string) $value) !== '';
				}));

				$guessedFirst = $parts[0] ?? $username;
				$guessedLast = $parts[1] ?? '';

				$updateData = [];
				if ($hasFirstName && $firstName === '') {
					$updateData['first_name'] = ucfirst(strtolower($guessedFirst));
				}
				if ($hasLastName && $lastName === '' && $guessedLast !== '') {
					$updateData['last_name'] = ucfirst(strtolower($guessedLast));
				}
				if ($hasStatus && trim((string) ($user['status'] ?? '')) === '') {
					$updateData['status'] = 'Active';
				}
				if ($hasInactiveAt && (($updateData['status'] ?? $user['status'] ?? 'Active') === 'Inactive') && trim((string) ($user['inactive_at'] ?? '')) === '') {
					$updateData['inactive_at'] = date('Y-m-d H:i:s');
				}

				if (!empty($updateData)) {
					$updateData['updated_at'] = date('Y-m-d H:i:s');
					$this->db->table('users')
						->where('user_id', (int) $user['user_id'])
						->update($updateData);
				}
			}

			echo "ℹ️  Users already seeded. Missing first/last name and status were backfilled (including username-based fallback).\n";
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

		if ($hasFirstName) {
			$datas[0]['first_name'] = 'Super';
			$datas[1]['first_name'] = 'Admin';
			$datas[2]['first_name'] = 'Records';
		}

		if ($hasLastName) {
			$datas[0]['last_name'] = 'Admin';
			$datas[1]['last_name'] = 'User';
			$datas[2]['last_name'] = 'Officer';
		}

		if ($hasStatus) {
			$datas[0]['status'] = 'Active';
			$datas[1]['status'] = 'Active';
			$datas[2]['status'] = 'Active';
		}

		if ($hasInactiveAt) {
			$datas[0]['inactive_at'] = null;
			$datas[1]['inactive_at'] = null;
			$datas[2]['inactive_at'] = null;
		}

		$result = $this->db->table('users')->insertBatch($datas);
		if ($result === false) {
			$error = $this->db->error();
			echo "❌ UserSeeder insert failed: " . ($error['message'] ?? 'Unknown database error') . "\n";
			return;
		}

		echo "✓ Seeded " . count($datas) . " users\n";
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