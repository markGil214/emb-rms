<?php

namespace App\Config;

class Permissions
{
    /**
     * All available permissions, organized by group
     * Format: 'GROUP_NAME' => ['permission_key' => 'Description']
     */
    public static function all()
    {
        return [
            'DOCUMENT_MANAGEMENT' => [
                'search_documents' => 'Search and find documents',
                'view_shelf_map' => 'View 3D shelf visualization',
                'create_document_record' => 'Create new document records',
                'edit_document_metadata' => 'Edit document information',
            ],
            'BORROW_MANAGEMENT' => [
                'request_borrow' => 'Request to borrow documents',
                'view_own_borrow' => 'View own borrow request details',
                'view_all_borrow' => 'View all borrow request details',
                'process_borrow_release' => 'Release documents to borrower',
                'process_return' => 'Receive returned documents',
                'approve_borrow_requests' => 'Approve borrowing requests',
                'view_alerts_module' => 'View overdue tracking & alerts',
            ],
            'RELOCATION_MANAGEMENT' => [
                'initiate_relocation' => 'Initiate relocation for other users',
                'request_relocation' => 'Request document relocation',
                'approve_relocation' => 'Approve relocation requests',
            ],
            'ARCHIVE_DISPOSAL' => [
                'archive_document' => 'Flag documents for archival',
                'view_archive_module' => 'Access archive management',
                'manage_archive_policies' => 'Configure retention policies',
                'view_disposal_workflow' => 'View disposal queue/proposals',
                'approve_disposal' => 'Final approval for document disposal',
            ],
            'SYSTEM_ADMIN' => [
                'manage_users' => 'Create, edit, delete staff accounts',
                'manage_system_config' => 'System settings & configuration',
                'view_audit_logs' => 'Access compliance audit logs',
                'override_any_action' => 'Override any system restriction',
            ],
        ];
    }

    /**
     * Get flat array of all permissions with descriptions
     */
    public static function flat()
    {
        $flat = [];
        foreach (self::all() as $group => $permissions) {
            foreach ($permissions as $key => $description) {
                $flat[$key] = $description;
            }
        }
        return $flat;
    }

    /**
     * Get grouped permissions for UI rendering
     */
    public static function grouped()
    {
        return self::all();
    }

    /**
     * Get default permissions for each role
     */
    public static function roleDefaults()
    {
        return [
            'records_officer' => [
                'search_documents',
                'view_shelf_map',
                'create_document_record',
                'edit_document_metadata',
                'request_borrow',
                'view_own_borrow',
                'process_return',
                'request_relocation',
                'archive_document',
            ],
            'admin' => [
                // All records officer permissions
                'search_documents',
                'view_shelf_map',
                'create_document_record',
                'edit_document_metadata',
                'request_borrow',
                'view_all_borrow',
                'process_borrow_release',
                'process_return',
                'approve_borrow_requests',
                'view_alerts_module',
                'initiate_relocation',
                'request_relocation',
                'approve_relocation',
                'archive_document',
                'view_archive_module',
                'manage_archive_policies',
                'view_disposal_workflow',
            ],
            'super_admin' => [
                // All permissions
                'search_documents',
                'view_shelf_map',
                'create_document_record',
                'edit_document_metadata',
                'request_borrow',
                'view_all_borrow',
                'process_borrow_release',
                'process_return',
                'approve_borrow_requests',
                'view_alerts_module',
                'initiate_relocation',
                'request_relocation',
                'approve_relocation',
                'archive_document',
                'view_archive_module',
                'manage_archive_policies',
                'view_disposal_workflow',
                'approve_disposal',
                'manage_users',
                'manage_system_config',
                'view_audit_logs',
                'override_any_action',
            ],
        ];
    }
}