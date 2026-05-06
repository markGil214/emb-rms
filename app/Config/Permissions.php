<?php

namespace App\Config;

class Permissions
{
    /**
    * Comprehensive but streamlined permissions.
    * Includes View/Create for all modules but excludes User/Permission management.
     */
    public static function all()
    {
        return [
            'DOCUMENT_RECORDS' => [
                'manage_racks' => 'Manage Racks and Shelves',
                'manage_categories' => 'Manage Document Categories',
            ],
            'ARCHIVE_DISPOSAL' => [
                'view_audit_logs' => 'View System Audit Logs',
            ],
            'APPROVALS' => [
                'approve_requests' => 'General Approval Authority',
                'approve_borrow_requests' => 'Approve Borrow Requests',
                'approve_relocation' => 'Approve Relocation Requests',
                'approve_archive' => 'Approve Archival & Disposal',
                'approve_disposal' => 'Approve Disposal Requests',
            ],
            'SYSTEM_ADMINISTRATION' => [
                'manage_users' => 'Manage Users and System Permissions',
            ],
        ];
    }

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

    public static function grouped()
    {
        return self::all();
    }

    /**
     * Default permissions for each role
     */
    public static function roleDefaults()
    {
        return [
            'records_officer' => [
                'view_documents',
                'create_document_record',
                'view_shelf_map',
                'search_documents',
                'view_borrow',
                'view_all_borrow',
                'request_borrow',
                'view_relocation',
                'request_relocation',
                'initiate_relocation',
                'view_archive',
                'view_disposal',
                'request_disposal',
            ],
            'admin' => [
                'view_documents',
                'create_document_record',
                'view_shelf_map',
                'search_documents',
                'manage_racks',
                'manage_categories',
                'view_borrow',
                'view_all_borrow',
                'request_borrow',
                'view_relocation',
                'request_relocation',
                'initiate_relocation',
                'view_archive',
                'request_archive',
                'view_disposal',
                'request_disposal',
                'approve_requests',
                'approve_borrow_requests',
                'approve_relocation',
                'approve_archive',
                'approve_disposal',
                'view_audit_logs',
                'process_borrow_release',
                'process_return',
            ],
            'super_admin' => [
                'view_documents',
                'create_document_record',
                'view_shelf_map',
                'search_documents',
                'manage_racks',
                'manage_categories',
                'view_borrow',
                'view_all_borrow',
                'request_borrow',
                'view_relocation',
                'request_relocation',
                'initiate_relocation',
                'view_archive',
                'request_archive',
                'view_disposal',
                'request_disposal',
                'approve_requests',
                'approve_borrow_requests',
                'approve_relocation',
                'approve_archive',
                'approve_disposal',
                'view_audit_logs',
                'process_borrow_release',
                'process_return',
                'manage_users', // Hidden internal safety
            ],
        ];
    }
}
