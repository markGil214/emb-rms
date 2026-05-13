<?php

namespace App\Controllers;

use App\Models\BorrowTransactionModel;

class DashboardController extends BaseController
{
    /**
     * Display dashboard for authenticated users
     * Role-aware: loads different stats based on user role
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $permissionService = service('permissionService');
        $user = auth_user();
        $userId = $user['user_id'];
        $userRole = str_replace('superadmin', 'super_admin', (string) $permissionService->getUserRole($userId));

        // Build role-specific data
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'userRole' => $userRole,
            'permissions' => $permissionService->userPermissions($userId),
            'recentUsers' => $db->table('users')
                ->select('username, first_name, last_name, email, role, status, created_at')
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray(),
        ];

        // Load different stats based on role
        if ($userRole === 'super_admin') {
            // Get document status counts from database
            $documentStats = $this->getDocumentStatusCounts($db);
                
            $data['dashboardTitle'] = 'System Administration Dashboard';
            $data['stats'] = [
                'totalUsers' => $db->table('users')->countAll(),
                'totalDocuments' => $db->table('folders')->countAll(),
                'totalArchived' => $db->table('archive_records')->countAll(),
                'pendingApprovals' => $this->getPendingApprovalsCount($db, 'all'),
            ];
            $data['recentActivity'] = $this->getSystemActivityFeed($db, 20);
            $data['documentStats'] = $documentStats;

        } elseif ($userRole === 'admin') {
            // Get document status counts from database
            $documentStats = $this->getDocumentStatusCounts($db);
            
            $data['dashboardTitle'] = 'Records Management Dashboard';
            $data['stats'] = [
                'totalDocuments' => $db->table('folders')->countAll(),
                'pendingRequests' => $db->table('document_requests')
                    ->where('status', 'Pending')
                    ->countAllResults(),
            ];
            $data['recentActivity'] = $this->getTeamActivityFeed($db, 20);
            $data['documentStats'] = $documentStats;

        } else { // records_officer
            // Get document status counts from database
            $documentStats = $this->getDocumentStatusCounts($db);
            
            $data['dashboardTitle'] = 'Document Management Dashboard';
            $data['stats'] = [
                'accessibleDocuments' => $db->table('folders')->countAll(),
                'myRequests' => $db->table('document_requests')
                    ->countAllResults(),
                'pendingRequests' => $db->table('document_requests')
                    ->where('status', 'Pending')
                    ->countAllResults(),
            ];
            $data['recentActivity'] = $this->getUserActivityFeed($db, $userId, 20);
            $data['documentStats'] = $documentStats;
        }

        return view('dashboard', $data);
    }

    /**
     * Get document status counts for dashboard chart
     */
    private function getDocumentStatusCounts($db)
    {
        // Get counts for each document status
        $availableCount = $db->table('folders')
            ->where('status', 'Available')
            ->countAllResults();
            
        $borrowedCount = $db->table('folders')
            ->where('status', 'Borrowed')
            ->countAllResults();
            
        $archivedCount = $db->table('folders')
            ->where('status', 'Archived')
            ->countAllResults();

        $disposedCount = $db->table('folders')
            ->where('status', 'Disposed')
            ->countAllResults();
            
        // Get other statuses if they exist
        $otherStatuses = $db->table('folders')
            ->whereNotIn('status', ['Available', 'Borrowed', 'Archived', 'Disposed'])
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get() 
            ->getResultArray();
            
        $disposedFolders = $db->table('folders')
            ->where('status', 'Disposed')
            ->countAllResults();
            
$disposedFiles = (int) $db->table('file_disposal_requests')
    ->whereIn('status', ['Approved', 'Disposed']) // Now counts both
    ->countAllResults();
        
        $disposedCount = $disposedFolders + $disposedFiles;
            
        $stats = [
            'availableCount' => $availableCount,
            'borrowedCount' => $borrowedCount,
            'archivedCount' => $archivedCount,
            'disposedCount' => $disposedCount,
        ];
        
        // Add other statuses dynamically
        foreach ($otherStatuses as $status) {
            $stats[strtolower($status['status']) . 'Count'] = $status['count'];
        }
        
        return $stats;
    }

    /**
     * Get pending approvals count (for super admin)
     */
    private function getPendingApprovalsCount($db, $scope = 'all')
    {
        $borrowModel = new \App\Models\BorrowTransactionModel();
        
        // Get all borrow transactions
        $allBorrows = $borrowModel->findAll();
        
        $pendingCount = 0;
        foreach ($allBorrows as $borrow) {
            $calculatedStatus = $borrowModel->calculateStatus($borrow);
            
            if ($calculatedStatus === 'Pending') {
                $pendingCount++;
            }
        }

        // Add pending archival requests
        $pendingArchive = $db->table('folders')
            ->where('status', 'Pending Archive')
            ->countAllResults();
        $pendingCount += $pendingArchive;

        // Add pending relocation requests if table exists
        if ($db->tableExists('relocation_requests')) {
            $pendingRelocation = $db->table('relocation_requests')
                ->where('status', 'Pending')
                ->countAllResults();
            $pendingCount += $pendingRelocation;
        }

        // Add pending disposal requests
        // if ($db->tableExists('disposal_records')) {
        //     $pendingDisposal = $db->table('disposal_records')
        //         ->where('disposal_date', null)
        //         ->where('approved_by', null)
        //         ->where('status !=', 'Rejected')
        //         ->countAllResults();
        //     $pendingCount += $pendingDisposal;
        // }

        // Add pending file-level disposal requests
        if ($db->tableExists('file_disposal_requests')) {
            $pendingFileDisposal = $db->table('file_disposal_requests')
                ->where('status', 'Pending')
                ->countAllResults();
            $pendingCount += $pendingFileDisposal;
        }

        // Add pending restoration requests
        if ($db->tableExists('restoration_requests')) {
            $pendingRestoration = $db->table('restoration_requests')
                ->where('status', 'Pending')
                ->countAllResults();
            $pendingCount += $pendingRestoration;
        }
        
        return $pendingCount;
    }

    /**
     * Get system-wide activity feed (for super admin)
     */
    private function getSystemActivityFeed($db, $limit = 20)
    {
        return $db->table('audit_logs')
            ->select('audit_logs.*, users.username')
            ->join('users', 'users.user_id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get team activity feed (for admin)
     */
    private function getTeamActivityFeed($db, $limit = 20)
    {
        return $db->table('document_requests')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get user's personal activity feed (for records officer)
     */
    private function getUserActivityFeed($db, $userId, $limit = 20)
    {
        return $db->table('document_requests')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get overdue borrowed items for dashboard widget
     * 
     * @return array Overdue items with calculated days overdue
     */
    public function getOverdueStats()
    {
        $borrowModel = new \App\Models\BorrowTransactionModel();
        $overdue = $borrowModel->getAllOverdue();

        // Calculate days overdue for each item
        $stats = [];
        foreach ($overdue as $item) {
            $daysOverdue = (int)ceil((strtotime(date('Y-m-d')) - strtotime($item['expected_return_date'])) / (60 * 60 * 24));
            $item['days_overdue'] = max(1, $daysOverdue);
            $stats[] = $item;
        }

        return [
            'total_overdue' => count($stats),
            'items' => array_slice($stats, 0, 5), // Top 5 overdue items
            'all_items' => $stats,
        ];
    }

    /**
     * Display shelf map and search page
     */
    public function shelfmap()
    {
        $data = [
            'title' => 'Shelf Map & Search',
            'user' => auth_user(),
        ];

        return view('shelfmap', $data);
    }
}