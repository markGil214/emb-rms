<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Config\Permissions;

class RoleSeeder extends Seeder
{
    public function run()
    {
        if (! $this->tableExists('roles')) {
            echo "⚠️  Skipping RoleSeeder: roles table does not exist. Run migrations first.\n";
            return;
        }

        // Check if roles already exist
        $existingRoles = $this->db->table('roles')->countAllResults();

        $roles = [
            [
                'role_name' => 'records_officer',
                'description' => 'Standard user - Core document operations only',
                'is_system_role' => true,
            ],
            [
                'role_name' => 'admin',
                'description' => 'Records Head - Management & approvals, inherits all Records Officer permissions',
                'is_system_role' => true,
            ],
            [
                'role_name' => 'super_admin',
                'description' => 'MIS - Has ALL permissions, inherits all Admin permissions',
                'is_system_role' => true,
            ],
        ];

        // Insert roles only when missing
        if ($existingRoles === 0) {
            $this->db->table('roles')->insertBatch($roles);
        }

        // Get inserted role IDs
        $rolesInDb = $this->db->table('roles')->get()->getResult('array');
        $roleMap = [];
        foreach ($rolesInDb as $role) {
            $roleMap[$role['role_name']] = $role['role_id'];
        }

        // Get default permissions
        $defaults = Permissions::roleDefaults();

        // Build current role-permission map for idempotent sync
        $existingRolePerms = $this->db->table('role_permissions')->get()->getResultArray();
        $existingMap = [];
        foreach ($existingRolePerms as $rp) {
            $existingMap[$rp['role_id'] . '|' . $rp['permission_key']] = true;
        }

        // Insert missing role permissions
        $rolePermissions = [];
        foreach ($defaults as $roleName => $permissions) {
            if (! isset($roleMap[$roleName])) {
                continue;
            }

            $roleId = $roleMap[$roleName];
            foreach ($permissions as $permission) {
                $mapKey = $roleId . '|' . $permission;
                if (isset($existingMap[$mapKey])) {
                    continue;
                }

                $rolePermissions[] = [
                    'role_id' => $roleId,
                    'permission_key' => $permission,
                ];
            }
        }

        if (!empty($rolePermissions)) {
            $this->db->table('role_permissions')->insertBatch($rolePermissions);
        }

        if ($existingRoles > 0) {
            echo "✓ Role default permissions synced successfully\n";
            return;
        }

        echo "✓ Roles and default permissions seeded successfully\n";
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