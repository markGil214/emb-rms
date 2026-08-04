<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Repair migration: MigratePermissionsToRbacStandard (2026-04-27) is recorded
 * as applied in the migrations ledger, but the live role_permissions table is
 * missing almost all of the "new RBAC standard" permission keys it was meant
 * to seed. This re-runs that migration's own (idempotent) seeding logic so
 * the gap actually gets closed, without editing the ledger by hand.
 */
class ReseedRbacStandardPermissions extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('roles') || !$this->db->tableExists('role_permissions')) {
            return;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->addNewPermissionsToRoles();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // No down: this migration only fills in gaps another migration
        // already owns removing (see MigratePermissionsToRbacStandard::down()).
    }

    /**
     * Copied from MigratePermissionsToRbacStandard::addNewPermissionsToRoles().
     */
    private function addNewPermissionsToRoles()
    {
        $newPermissions = [
            'view_documents' => ['search_documents', 'create_document_record'],
            'search_documents' => ['search_documents'],
            'create_documents' => ['create_document_record', 'manage_categories'],
            'update_documents' => ['edit_document_metadata'],
            'view_shelf_map' => ['view_shelf_map'],
            'manage_racks' => ['manage_racks'],
            'approve_create_documents' => ['approve_folder_creation'],
            'approve_archive' => ['approve_folder_archival'],

            'view_borrow' => ['view_own_borrow', 'view_all_borrow', 'view_alerts_module'],
            'create_borrow' => ['request_borrow'],
            'update_borrow' => ['process_borrow_release', 'process_return'],
            'approve_borrow' => ['approve_borrow_requests'],

            'view_relocation' => ['initiate_relocation'],
            'create_relocation' => ['request_relocation'],
            'update_relocation' => [],
            'approve_relocation' => ['approve_relocation'],

            'view_archive' => ['view_archive_module', 'view_disposal_workflow'],
            'create_archive' => ['archive_document'],
            'update_archive' => ['manage_archive_policies'],
            'approve_disposal' => ['approve_disposal'],
            'request_restore' => ['archive_document'],
            'approve_restore' => [],

            'view_users' => [],
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
                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_key', $newPermKey)
                    ->countAllResults();

                if (!$exists) {
                    if ($newPermKey === 'view_users' || $newPermKey === 'update_relocation' || $newPermKey === 'approve_restore') {
                        if ($roleName === 'super_admin') {
                            $this->db->table('role_permissions')->insert([
                                'role_id' => $roleId,
                                'permission_key' => $newPermKey,
                            ]);
                        }
                    } else {
                        $this->addNewPermissionIfRoleHasOldEquivalent($roleId, $newPermKey, $newPermissions[$newPermKey]);
                    }
                }
            }
        }
    }

    /**
     * Copied from MigratePermissionsToRbacStandard::addNewPermissionIfRoleHasOldEquivalent().
     */
    private function addNewPermissionIfRoleHasOldEquivalent($roleId, $newPermKey, $oldEquivalents)
    {
        if (empty($oldEquivalents)) {
            return;
        }

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
                // Ignore duplicates.
            }
        }
    }
}
