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
                'view_documents' => 'View Document Records',
                'create_document_record' => 'Create New Document Records',
                'edit_document_metadata' => 'Edit Document Metadata',
                'search_documents' => 'Search and Filter Documents',
                'view_shelf_map' => 'View Shelf Map and Locations',
                'manage_racks' => 'Manage Racks and Shelves',
                'manage_categories' => 'Manage Document Categories',
            ],
            'BORROW_MANAGEMENT' => [
                'view_borrow' => 'View Own Borrow Requests',
                'request_borrow' => 'Request to Borrow Documents',
                'view_pending_returns' => 'View Overdue and Pending Returns',
            ],
            'RELOCATION' => [
                'initiate_relocation' => 'Initiate and Perform Relocation',
                'approve_relocation' => 'Approve Relocation Requests',
            ],
            'ARCHIVE_DISPOSAL' => [
                'approve_archive' => 'Archive Management',
                'request_restore' => 'Request Restoration',
                'approve_restore' => 'Approve Restoration',
                'request_disposal' => 'Request Disposal',
                'approve_disposal' => 'Approve Disposal',
                'view_audit_logs' => 'View Audit Logs',
            ],
            'APPROVALS' => [
                'approve_requests' => 'General Approval Authority',
                'approve_folder_creation' => 'Approve Folder Creation Requests',
                'approve_borrow_requests' => 'Approve Borrow Requests',
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
                'request_borrow',
                'initiate_relocation',
                'request_disposal',
            ],
            'admin' => [
                'view_documents',
                'create_document_record',
                'edit_document_metadata',
                'view_shelf_map',
                'search_documents',
                'manage_racks',
                'manage_categories',
                'view_borrow',
                'request_borrow',
                'view_pending_returns',
                'initiate_relocation',
                'approve_relocation',
                'request_restore',
                'approve_restore',
                'request_disposal',
                'approve_disposal',
                'approve_requests',
                'approve_folder_creation',
                'approve_borrow_requests',
                'approve_archive',
                'view_audit_logs',
            ],
            'super_admin' => [
                'view_documents',
                'create_document_record',
                'edit_document_metadata',
                'view_shelf_map',
                'search_documents',
                'manage_racks',
                'manage_categories',
                'view_borrow',
                'request_borrow',
                'view_pending_returns',
                'initiate_relocation',
                'approve_relocation',
                'request_restore',
                'approve_restore',
                'request_disposal',
                'approve_disposal',
                'approve_requests',
                'approve_folder_creation',
                'approve_borrow_requests',
                'approve_archive',
                'view_audit_logs',
                'manage_users',
            ],
        ];
    }
}
