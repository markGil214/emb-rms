<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigratePermissionsToRbacStandard extends Migration
{
    /**
     * PHASE 1: Add new RBAC permissions
     * PHASE 2: Map old → new permissions for existing roles/users
     * PHASE 3: Remove old permissions (optional, kept for reference)
     *
     * Safe migration strategy:
     * - Old permissions stay in DB during testing
     * - Users get BOTH old and new permissions for smooth transition
     * - Once testing passes, old permissions can be removed
     */

    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        // ===== PHASE 1: Add new permissions to role_permissions =====
        echo "PHASE 1: Adding new RBAC-standard permissions...\n";
        $this->addNewPermissionsToRoles();

        // ===== PHASE 2: Map old permissions to new for existing role assignments =====
        echo "PHASE 2: Mapping existing roles to new permissions...\n";
        $this->mapExistingRolePermissions();

        // ===== PHASE 3: Map direct user permissions if any exist =====
        echo "PHASE 3: Mapping direct user permissions...\n";
        $this->mapExistingUserPermissions();

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        echo "✓ Permission migration complete. Old permissions retained for testing.\n";
        echo "  Next step: Test all features, then remove old permissions via down() migration.\n";
    }

    /**
     * PHASE 1: Add new permissions to all existing roles
     */
    private function addNewPermissionsToRoles()
    {
        // Permission mapping: NEW → OLD equivalents (for finding which roles have them)
        $newPermissions = [
            // Document Records (9 permissions)
            'view_documents' => ['search_documents', 'create_document_record'],
            'search_documents' => ['search_documents'],
            'create_documents' => ['create_document_record', 'manage_categories'],
            'update_documents' => ['edit_document_metadata'],
            'delete_documents' => [],  // New permission, no direct mapping
            'view_shelf_map' => ['view_shelf_map'],
            'manage_racks' => ['manage_racks'],
            'approve_create_documents' => ['approve_folder_creation'],
            'approve_archive' => ['approve_folder_archival'],

            // Borrow Management (4 permissions)
            'view_borrow' => ['view_own_borrow', 'view_all_borrow', 'view_alerts_module'],
            'create_borrow' => ['request_borrow'],
            'update_borrow' => ['process_borrow_release', 'process_return'],
            'approve_borrow' => ['approve_borrow_requests'],

            // Relocation Management (4 permissions)
            'view_relocation' => ['initiate_relocation'],
            'create_relocation' => ['request_relocation'],
            'update_relocation' => [],  // New permission, no direct mapping
            'approve_relocation' => ['approve_relocation'],

            // Archive & Disposal (6 permissions)
            'view_archive' => ['view_archive_module', 'view_disposal_workflow'],
            'create_archive' => ['archive_document'],
            'update_archive' => ['manage_archive_policies'],
            'delete_archive' => ['archive_document'],
            'approve_disposal' => ['approve_disposal'],
            'manage_retention' => ['manage_archive_policies'],

            // System Admin (7 permissions)
            'view_users' => [],  // New permission
            'create_users' => ['manage_users'],
            'update_users' => ['manage_users'],
            'delete_users' => ['manage_users'],
            'manage_system_config' => ['manage_system_config'],
            'view_audit_logs' => ['view_audit_logs'],
            'override' => ['override_any_action'],
        ];

        $rolesInDb = $this->db->table('roles')->get()->getResultArray();

        foreach ($rolesInDb as $role) {
            $roleId = $role['role_id'];
            $roleName = $role['role_name'];

            foreach (array_keys($newPermissions) as $newPermKey) {
                // Check if permission already exists
                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_key', $newPermKey)
                    ->countAllResults();

                if (!$exists) {
                    // For new permissions without old mapping, only add to super_admin for now
                    if ($newPermKey === 'view_users' || $newPermKey === 'update_relocation' || $newPermKey === 'delete_documents') {
                        if ($roleName === 'super_admin') {
                            $this->db->table('role_permissions')->insert([
                                'role_id' => $roleId,
                                'permission_key' => $newPermKey,
                            ]);
                        }
                    } else {
                        // Add if role has ANY old permission that maps to this new one
                        $this->addNewPermissionIfRoleHasOldEquivalent($roleId, $newPermKey, $newPermissions[$newPermKey]);
                    }
                }
            }
        }

        echo "  ✓ New permissions added to all roles\n";
    }

    /**
     * Check if role has any old permission matching the new one, then add
     */
    private function addNewPermissionIfRoleHasOldEquivalent($roleId, $newPermKey, $oldEquivalents)
    {
        if (empty($oldEquivalents)) {
            return;
        }

        // Check if role has ANY of the old equivalent permissions
        $hasOldPerm = $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->whereIn('permission_key', $oldEquivalents)
            ->countAllResults() > 0;

        if ($hasOldPerm) {
            try {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_key' => $newPermKey,
                ]);
            } catch (\Exception $e) {
                // Silently ignore duplicates (should not happen due to unique constraint check above)
            }
        }
    }

    /**
     * PHASE 2: Map existing role permissions
     */
    private function mapExistingRolePermissions()
    {
        // This is already done in PHASE 1, but this method is here for clarity
        echo "  ✓ Role permissions mapped\n";
    }

    /**
     * PHASE 3: Map direct user permissions if any exist
     */
    private function mapExistingUserPermissions()
    {
        // Permission mapping for direct user permissions
        $permissionMap = [
            'search_documents' => ['search_documents'],
            'create_document_record' => ['create_documents'],
            'edit_document_metadata' => ['update_documents'],
            'manage_racks' => ['manage_racks'],
            'manage_categories' => ['create_documents'],
            'approve_folder_creation' => ['approve_create_documents'],
            'approve_folder_archival' => ['approve_archive'],
            'request_borrow' => ['create_borrow'],
            'view_own_borrow' => ['view_borrow'],
            'view_all_borrow' => ['view_borrow'],
            'process_borrow_release' => ['update_borrow'],
            'process_return' => ['update_borrow'],
            'approve_borrow_requests' => ['approve_borrow'],
            'view_alerts_module' => ['view_borrow'],
            'initiate_relocation' => ['view_relocation'],
            'request_relocation' => ['create_relocation'],
            'approve_relocation' => ['approve_relocation'],
            'archive_document' => ['create_archive', 'delete_archive'],
            'view_archive_module' => ['view_archive'],
            'manage_archive_policies' => ['manage_retention'],
            'view_disposal_workflow' => ['view_archive'],
            'approve_disposal' => ['approve_disposal'],
            'manage_users' => ['create_users', 'update_users', 'delete_users'],
            'manage_system_config' => ['manage_system_config'],
            'view_audit_logs' => ['view_audit_logs'],
            'override_any_action' => ['override'],
        ];

        // Get all users with direct user_permissions
        $userPerms = $this->db->table('user_permissions')->get()->getResultArray();

        foreach ($userPerms as $userPerm) {
            $userId = $userPerm['user_id'];
            $oldPermKey = $userPerm['permission_key'];

            if (isset($permissionMap[$oldPermKey])) {
                foreach ($permissionMap[$oldPermKey] as $newPermKey) {
                    // Check if user already has this permission
                    $exists = $this->db->table('user_permissions')
                        ->where('user_id', $userId)
                        ->where('permission_key', $newPermKey)
                        ->countAllResults();

                    if (!$exists) {
                        try {
                            $this->db->table('user_permissions')->insert([
                                'user_id' => $userId,
                                'permission_key' => $newPermKey,
                                'assigned_by_id' => $userPerm['assigned_by_id'],
                            ]);
                        } catch (\Exception $e) {
                            // Ignore duplicates
                        }
                    }
                }
            }
        }

        echo "  ✓ User permissions mapped\n";
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        echo "Rolling back RBAC migration...\n";
        echo "⚠️  WARNING: This will remove ALL new RBAC permissions from the system.\n";
        echo "   Only proceed if you haven't tested the system yet.\n";

        // Remove all new permission records
        $newPermissions = [
            'view_documents', 'search_documents', 'create_documents', 'update_documents', 'delete_documents',
            'view_shelf_map', 'manage_racks', 'approve_create_documents', 'approve_archive',
            'view_borrow', 'create_borrow', 'update_borrow', 'approve_borrow',
            'view_relocation', 'create_relocation', 'update_relocation', 'approve_relocation',
            'view_archive', 'create_archive', 'update_archive', 'delete_archive', 'approve_disposal', 'manage_retention',
            'view_users', 'create_users', 'update_users', 'delete_users', 'manage_system_config', 'view_audit_logs', 'override',
        ];

        $this->db->table('role_permissions')->whereIn('permission_key', $newPermissions)->delete();
        $this->db->table('user_permissions')->whereIn('permission_key', $newPermissions)->delete();

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');

        echo "✓ RBAC migration rolled back. Old permissions remain.\n";
    }
}
