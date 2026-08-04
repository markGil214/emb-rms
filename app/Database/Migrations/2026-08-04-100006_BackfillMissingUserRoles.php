<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * A `user_roles` row has only ever been created via Admin\UserController::store()
 * or by manually running UserSeeder — any user created another way, or existing
 * from before the RBAC tables were introduced, can be "legacy-only" (role
 * known only via `users.role`). Combined with the normalizeRoleName() bug
 * fixed alongside this migration, legacy-only Records Officers were
 * resolving to a role name matching nothing and silently getting zero
 * permissions. This backfills a `user_roles` row for every user missing one,
 * mapped from their legacy `users.role` value, so no environment can be left
 * with legacy-only users going forward.
 */
class BackfillMissingUserRoles extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('users') || !$this->db->tableExists('user_roles') || !$this->db->tableExists('roles')) {
            return;
        }

        $roles = $this->db->table('roles')->select('role_id, role_name')->get()->getResultArray();
        $roleIdByName = [];
        foreach ($roles as $role) {
            $roleIdByName[$role['role_name']] = (int) $role['role_id'];
        }

        $legacyToRbacName = [
            'superadmin' => 'super_admin',
            'admin' => 'admin',
            'recordsofficer' => 'records_officer',
        ];

        $assignedUserIds = array_map(
            'intval',
            array_column($this->db->table('user_roles')->select('user_id')->get()->getResultArray(), 'user_id')
        );

        $users = $this->db->table('users')->select('user_id, role')->get()->getResultArray();

        foreach ($users as $user) {
            $userId = (int) $user['user_id'];
            if (in_array($userId, $assignedUserIds, true)) {
                continue;
            }

            $legacyKey = strtolower(trim((string) ($user['role'] ?? '')));
            $roleName = $legacyToRbacName[$legacyKey] ?? null;
            if ($roleName === null || !isset($roleIdByName[$roleName])) {
                continue;
            }

            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleIdByName[$roleName],
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by_id' => null,
            ]);
        }
    }

    public function down()
    {
        // No down: rows this migration created can't be distinguished from
        // rows that already existed for other reasons.
    }
}
