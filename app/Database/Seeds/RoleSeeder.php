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
        if ($existingRoles > 0) {
            echo "ℹ️  Roles already seeded. Skipping RoleSeeder.\n";
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

        // Insert roles
        $this->db->table('roles')->insertBatch($roles);

        // Get inserted role IDs
        $rolesInDb = $this->db->table('roles')->get()->getResult('array');
        $roleMap = [];
        foreach ($rolesInDb as $role) {
            $roleMap[$role['role_name']] = $role['role_id'];
        }

        // Get default permissions
        $defaults = Permissions::roleDefaults();

        // Insert role permissions
        $rolePermissions = [];
        foreach ($defaults as $roleName => $permissions) {
            $roleId = $roleMap[$roleName];
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