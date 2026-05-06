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
                'view_all_borrow' => 'View All Borrow Records',
                'request_borrow' => 'Request to Borrow Documents',
                'process_borrow_release' => 'Release Documents for Borrowing',
                'process_return' => 'Process Returned Documents',
                'view_pending_returns' => 'View Overdue and Pending Returns',
            ],
            'RELOCATION' => [
                'view_relocation' => 'View Relocation Requests',
                'request_relocation' => 'Request Document Relocation',
                'initiate_relocation' => 'Initiate and Perform Relocation',
                'approve_relocation' => 'Approve Relocation Requests',
            ],
            'ARCHIVE_DISPOSAL' => [
                'view_archive' => 'View Archived Documents',
                'request_archive' => 'Request Document Archival',
                'create_archive' => 'Archive Documents Directly',
                'approve_archive' => 'Approve Archival Requests',
                'request_restore' => 'Request Restoration from Archive',
                'approve_restore' => 'Approve Restoration Requests',
                'view_disposal' => 'View Disposal Records',
                'request_disposal' => 'Request Document Disposal',
                'approve_disposal' => 'Approve Disposal Requests',
                'view_audit_logs' => 'View System Audit Logs',
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
                'view_relocation',
                'request_relocation',
                'view_archive',
                'request_archive',
                'view_disposal',
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
                'view_all_borrow',
                'request_borrow',
                'view_relocation',
                'request_relocation',
                'initiate_relocation',
                'view_archive',
                'request_archive',
                'create_archive',
                'request_restore',
                'view_disposal',
                'request_disposal',
                'approve_requests',
                'approve_folder_creation',
                'approve_borrow_requests',
                'approve_relocation',
                'approve_archive',
                'approve_disposal',
                'approve_restore',
                'view_audit_logs',
                'process_borrow_release',
                'process_return',
                'view_pending_returns',
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
                'view_all_borrow',
                'request_borrow',
                'view_relocation',
                'request_relocation',
                'initiate_relocation',
                'view_archive',
                'request_archive',
                'create_archive',
                'request_restore',
                'approve_restore',
                'view_disposal',
                'request_disposal',
                'approve_requests',
                'approve_folder_creation',
                'approve_borrow_requests',
                'approve_relocation',
                'approve_archive',
                'approve_disposal',
                'view_audit_logs',
                'process_borrow_release',
                'process_return',
                'view_pending_returns',
                'manage_users', // Hidden internal safety
            ],
        ];
    }
}
