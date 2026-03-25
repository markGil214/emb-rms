<?php

namespace App\Controllers;

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
        $userRole = $permissionService->getUserRole($userId);

        // Build role-specific data
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'userRole' => $userRole,
            'permissions' => $permissionService->userPermissions($userId),
            'recentUsers' => $db->table('users')
                ->select('username, email, role, created_at')
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

        return view('layouts/superadmin/dashboard', $data);
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
            
        // Get other statuses if they exist
        $otherStatuses = $db->table('folders')
            ->whereNotIn('status', ['Available', 'Borrowed', 'Archived'])
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->getResultArray();
            
        $stats = [
            'availableCount' => $availableCount,
            'borrowedCount' => $borrowedCount,
            'archivedCount' => $archivedCount,
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
        $borrowCount = $db->table('document_requests')
            ->where('status', 'Pending')
            ->countAllResults();
        
        $relocationCount = 0;
        if ($db->tableExists('relocation_requests')) {
            $relocationCount = $db->table('relocation_requests')
                ->where('status', 'Pending')
                ->countAllResults();
        }
        
        return $borrowCount + $relocationCount;
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
     * Display shelf map and search page
     */
    public function shelfmap()
    {
        $data = [
            'title' => 'Shelf Map & Search',
            'user' => auth_user(),
        ];

        return view('layouts/superadmin/shelfmap', $data);
    }
}