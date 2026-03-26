<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MigrateExistingUsers extends Seeder
{
    public function run()
    {
        $existingAssignments = $this->db->table('user_roles')->countAllResults();
        if ($existingAssignments > 0) {
            echo "Info: user_roles already seeded. Skipping migration.\n";
            return;
        }

        $users = $this->db->table('users')->get()->getResult('array');

        if (empty($users)) {
            echo "No users to migrate\n";
            return;
        }

        // Map old role strings to new role IDs
        $roleMap = [
            'RecordsOfficer' => 'records_officer',
            'Admin' => 'admin',
            'SuperAdmin' => 'super_admin',
        ];

        $roles = $this->db->table('roles')->get()->getResult('array');
        $roleIdMap = [];
        foreach ($roles as $role) {
            $roleIdMap[$role['role_name']] = $role['role_id'];
        }

        $userRoles = [];
        foreach ($users as $user) {
            $newRoleName = $roleMap[$user['role']] ?? 'records_officer';
            if (!isset($roleIdMap[$newRoleName])) {
                continue;
            }

            $newRoleId = $roleIdMap[$newRoleName];

            $userRoles[] = [
                'user_id' => $user['user_id'],
                'role_id' => $newRoleId,
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by_id' => null,
            ];
        }

        if (!empty($userRoles)) {
            $this->db->table('user_roles')->insertBatch($userRoles);
            echo "✓ " . count($userRoles) . " users migrated to new role system\n";
        }
    }
}