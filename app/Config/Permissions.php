<?php

namespace App\Config;

class Permissions
{
    /**
     * All available permissions, organized by module
     * Uses standardized CRUD + approval pattern:
     * - view_*
     * - create_*
     * - update_*
     * - delete_*
     * - approve_*
     * - manage_* (advanced admin)
     */
    public static function all()
    {
        return [
            'DOCUMENT_RECORDS' => [
                'view_documents' => 'View folders and documents in the system',
                'search_documents' => 'Search and filter documents across system',
                'create_documents' => 'Create new folders and upload documents',
                'update_documents' => 'Edit folder details and document metadata',
                'delete_documents' => 'Permanently delete documents',
                'view_shelf_map' => 'View interactive shelf map and location visualization',
                'manage_racks' => 'Create, edit, and delete racks and shelves',
                'approve_create_documents' => 'Approve new folder creation requests',
                'approve_archive' => 'Approve folder archival requests',
            ],
            'BORROW_MANAGEMENT' => [
                'view_borrow' => 'View all borrow records and borrowing status',
                'create_borrow' => 'Create new borrow requests',
                'update_borrow' => 'Update borrow details and process returns',
                'approve_borrow' => 'Approve or reject borrow requests',
            ],
            'RELOCATION_MANAGEMENT' => [
                'view_relocation' => 'View relocation requests and status',
                'create_relocation' => 'Create new relocation requests',
                'update_relocation' => 'Update relocation details',
                'approve_relocation' => 'Approve or reject relocation requests',
            ],
            'ARCHIVE_DISPOSAL' => [
                'view_archive' => 'View archived documents and disposal requests',
                'create_archive' => 'Create archive records for documents',
                'update_archive' => 'Edit archive records and retention policies',
                'delete_archive' => 'Permanently remove archived records',
                'approve_disposal' => 'Approve document disposal requests',
                'manage_retention' => 'Configure retention policies and schedules',
            ],
            'SYSTEM_ADMIN' => [
                'view_users' => 'View user accounts and role assignments',
                'create_users' => 'Create new user accounts',
                'update_users' => 'Edit user details and role assignments',
                'delete_users' => 'Deactivate or remove user accounts',
                'manage_system_config' => 'Configure system settings and parameters',
                'view_audit_logs' => 'View system activity and change logs',
                'override' => 'Override locked workflows and user restrictions',
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
     * Uses new RBAC permission keys
     */
    public static function roleDefaults()
    {
        return [
            'records_officer' => [
                // Document records - basic access
                'view_documents',
                'search_documents',
                'create_documents',
                'view_shelf_map',
                
                // Borrow - can create and view
                'view_borrow',
                'create_borrow',
                'update_borrow',
                
                // Relocation - can request
                'create_relocation',
                
                // Archive - can view and create
                'view_archive',
                'create_archive',
            ],
            'admin' => [
                // Document records - full access
                'view_documents',
                'search_documents',
                'create_documents',
                'update_documents',
                'view_shelf_map',
                'manage_racks',
                'approve_create_documents',
                'approve_archive',
                
                // Borrow - can approve
                'view_borrow',
                'create_borrow',
                'update_borrow',
                'approve_borrow',
                
                // Relocation - can approve
                'view_relocation',
                'create_relocation',
                'approve_relocation',
                
                // Archive - can manage
                'view_archive',
                'create_archive',
                'update_archive',
                'manage_retention',
                'approve_disposal',
            ],
            'super_admin' => [
                // Document records - all
                'view_documents',
                'search_documents',
                'create_documents',
                'update_documents',
                'delete_documents',
                'view_shelf_map',
                'manage_racks',
                'approve_create_documents',
                'approve_archive',
                
                // Borrow - all
                'view_borrow',
                'create_borrow',
                'update_borrow',
                'approve_borrow',
                
                // Relocation - all
                'view_relocation',
                'create_relocation',
                'update_relocation',
                'approve_relocation',
                
                // Archive - all
                'view_archive',
                'create_archive',
                'update_archive',
                'delete_archive',
                'approve_disposal',
                'manage_retention',
                
                // System - all
                'view_users',
                'create_users',
                'update_users',
                'delete_users',
                'manage_system_config',
                'view_audit_logs',
                'override',
            ],
        ];
    }
}