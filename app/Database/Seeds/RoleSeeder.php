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

        $rolesInDb = $this->db->table('roles')->get()->getResult('array');
        $roleMap = [];
        foreach ($rolesInDb as $role) {
            $roleMap[$role['role_name']] = $role;
        }

        $insertRows = [];
        foreach ($roles as $role) {
            if (isset($roleMap[$role['role_name']])) {
                $this->db->table('roles')
                    ->where('role_id', $roleMap[$role['role_name']]['role_id'])
                    ->update([
                        'description' => $role['description'],
                        'is_system_role' => $role['is_system_role'],
                    ]);
                continue;
            }

            $insertRows[] = $role;
        }

        if (!empty($insertRows)) {
            $this->db->table('roles')->insertBatch($insertRows);
            $rolesInDb = $this->db->table('roles')->get()->getResult('array');
            $roleMap = [];
            foreach ($rolesInDb as $role) {
                $roleMap[$role['role_name']] = $role;
            }
        }

        // Get default permissions
        $defaults = Permissions::roleDefaults();

        // Build current role-permission map for idempotent sync
        $existingRolePerms = $this->db->table('role_permissions')->get()->getResultArray();
        $existingMap = [];
        foreach ($existingRolePerms as $rp) {
            $existingMap[$rp['role_id'] . '|' . $rp['permission_key']] = true;
        }

        $defaultRoleIds = [];
        foreach (array_keys($defaults) as $roleName) {
            if (isset($roleMap[$roleName])) {
                $defaultRoleIds[] = $roleMap[$roleName]['role_id'];
            }
        }

        if (!empty($defaultRoleIds)) {
            $this->db->table('role_permissions')
                ->whereIn('role_id', $defaultRoleIds)
                ->delete();
        }

        if ($this->tableExists('user_permissions')) {
            $this->db->table('user_permissions')
                ->where('permission_key', 'delete_documents')
                ->delete();
        }

        $existingMap = [];

        // Insert missing role permissions
        $rolePermissions = [];
        foreach ($defaults as $roleName => $permissions) {
            if (! isset($roleMap[$roleName])) {
                continue;
            }

            $roleId = $roleMap[$roleName]['role_id'];
            foreach ($permissions as $permission) {
                $rolePermissions[] = [
                    'role_id' => $roleId,
                    'permission_key' => $permission,
                ];
            }
        }

        if (!empty($rolePermissions)) {
            $this->db->table('role_permissions')->insertBatch($rolePermissions);
        }

        // START: Grant ALL permissions to super_admin
        if (isset($roleMap['super_admin'])) {
            $superAdminRoleId = $roleMap['super_admin']['role_id'];
            $allPermissions = Permissions::flat();
            $superAdminPerms = [];

            // Get existing super_admin perms to avoid duplicates
            $existingSuperAdminPerms = $this->db->table('role_permissions')
                ->where('role_id', $superAdminRoleId)
                ->get()->getResultArray();
            $existingPermKeys = array_column($existingSuperAdminPerms, 'permission_key');

            foreach (array_keys($allPermissions) as $permKey) {
                if (!in_array($permKey, $existingPermKeys)) {
                    $superAdminPerms[] = [
                        'role_id' => $superAdminRoleId,
                        'permission_key' => $permKey,
                    ];
                }
            }

            if (!empty($superAdminPerms)) {
                $this->db->table('role_permissions')->insertBatch($superAdminPerms);
                echo "✓ Granted " . count($superAdminPerms) . " additional permissions to super_admin.\n";
            }
        }
        // END: Grant ALL permissions to super_admin

        echo empty($insertRows)
            ? "✓ Role default permissions synced successfully\n"
            : "✓ Roles and default permissions seeded successfully\n";
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