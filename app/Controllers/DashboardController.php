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
            $data['monthlyTrend'] = $this->getMonthlyActivityTrend($db);
            $data['typeBreakdown'] = $this->getDocumentsByTypeBreakdown($db);
            $data['capacityInsights'] = $this->getStorageCapacityInsights($db);
            $data['retentionInsights'] = $this->getRetentionInsights($db);
            $data['approvalQueue'] = $this->getApprovalQueueBreakdown($db);
            $data['overdueBorrowings'] = $this->getOverdueBorrowings($db);
            $data['userInsights'] = $this->getUserAndAuditInsights($db);
            $data['storageInsights'] = $this->getStorageAndIntegrityInsights($db);

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
     * Records created / archived / disposed per month for the last 12 months.
     *
     * Everything else on the dashboard is a point-in-time count, so this is
     * the only place trends are visible.
     */
    private function getMonthlyActivityTrend($db, int $months = 12): array
    {
        $buckets = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-{$i} months"));
            $buckets[$key] = [
                'label' => date('M Y', strtotime("-{$i} months")),
                'created' => 0,
                'archived' => 0,
                'disposed' => 0,
            ];
        }

        $since = date('Y-m-01 00:00:00', strtotime('-' . ($months - 1) . ' months'));

        $created = $db->table('folders')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total", false)
            ->where('created_at >=', $since)
            ->groupBy('ym')
            ->get()
            ->getResultArray();
        foreach ($created as $row) {
            if (isset($buckets[$row['ym']])) {
                $buckets[$row['ym']]['created'] = (int) $row['total'];
            }
        }

        if ($db->tableExists('archive_records')) {
            $archived = $db->table('archive_records')
                ->select("DATE_FORMAT(archived_date, '%Y-%m') AS ym, COUNT(*) AS total", false)
                ->where('archived_date >=', $since)
                ->groupBy('ym')
                ->get()
                ->getResultArray();
            foreach ($archived as $row) {
                if (isset($buckets[$row['ym']])) {
                    $buckets[$row['ym']]['archived'] = (int) $row['total'];
                }
            }
        }

        if ($db->tableExists('disposal_records')) {
            $disposed = $db->table('disposal_records')
                ->select("DATE_FORMAT(disposal_date, '%Y-%m') AS ym, COUNT(*) AS total", false)
                ->where('disposal_date >=', $since)
                ->groupBy('ym')
                ->get()
                ->getResultArray();
            foreach ($disposed as $row) {
                if (isset($buckets[$row['ym']])) {
                    $buckets[$row['ym']]['disposed'] = (int) $row['total'];
                }
            }
        }

        return [
            'labels' => array_column($buckets, 'label'),
            'created' => array_column($buckets, 'created'),
            'archived' => array_column($buckets, 'archived'),
            'disposed' => array_column($buckets, 'disposed'),
        ];
    }

    /**
     * Document counts per folder type, ranked highest first so the busiest
     * type is obvious without reading every bar.
     */
    private function getDocumentsByTypeBreakdown($db): array
    {
        $rows = $db->table('folders')
            ->select('folder_type, COUNT(*) AS total', false)
            ->groupBy('folder_type')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $types = [];
        $total = 0;

        foreach ($rows as $row) {
            $label = trim((string) ($row['folder_type'] ?? ''));
            $count = (int) $row['total'];
            $total += $count;

            $types[] = [
                'label' => $label === '' ? 'Unspecified' : $label,
                'count' => $count,
            ];
        }

        // Share is only meaningful once the totals are known.
        foreach ($types as $index => $type) {
            $types[$index]['percent'] = $total > 0
                ? (int) round(($type['count'] / $total) * 100)
                : 0;
        }

        return [
            'types' => $types,
            'total' => $total,
            'top' => $types[0] ?? null,
        ];
    }

    /**
     * Shelf capacity utilisation across the whole system.
     */
    private function getStorageCapacityInsights($db): array
    {
        $rackShelfModel = new \App\Models\RackShelfModel();
        $occupancy = $rackShelfModel->getOccupancyMap();

        $locations = $db->table('locations')
            ->select('location_id, rack, shelf, capacity')
            ->get()
            ->getResultArray();

        $totalCapacity = 0;
        $totalOccupied = 0;
        $fullShelves = 0;
        $unsetShelves = 0;
        $shelfRows = [];

        foreach ($locations as $location) {
            $locationId = (int) $location['location_id'];
            $capacity = max(0, (int) ($location['capacity'] ?? 0));
            $used = (int) ($occupancy[$locationId] ?? 0);

            if ($capacity === 0) {
                $unsetShelves++;
            } else {
                $totalCapacity += $capacity;
                $totalOccupied += min($used, $capacity);

                if ($used >= $capacity) {
                    $fullShelves++;
                }

                $shelfRows[] = [
                    'location_id' => $locationId,
                    'label' => 'Rack ' . ($location['rack'] ?? '?') . ' - Shelf ' . ($location['shelf'] ?? '?'),
                    'capacity' => $capacity,
                    'used' => $used,
                    'percent' => (int) round(min(100, ($used / $capacity) * 100)),
                ];
            }
        }

        usort($shelfRows, static function (array $a, array $b): int {
            return $b['percent'] <=> $a['percent'];
        });

        return [
            'totalCapacity' => $totalCapacity,
            'totalOccupied' => $totalOccupied,
            'utilisation' => $totalCapacity > 0 ? (int) round(($totalOccupied / $totalCapacity) * 100) : 0,
            'fullShelves' => $fullShelves,
            'unsetShelves' => $unsetShelves,
            'totalShelves' => count($locations),
            'topShelves' => array_slice($shelfRows, 0, 6),
        ];
    }

    /**
     * Retention posture for uploaded files.
     */
    private function getRetentionInsights($db): array
    {
        $empty = [
            'permanent' => 0,
            'expiring' => 0,
            'due30' => 0,
            'due60' => 0,
            'due90' => 0,
            'expired' => 0,
        ];

        if (! $db->tableExists('folder_files')) {
            return $empty;
        }

        $fields = $db->getFieldNames('folder_files');
        if (! in_array('expiration_date', $fields, true) || ! in_array('retention_type', $fields, true)) {
            return $empty;
        }

        $today = date('Y-m-d');
        $window = static function ($db, int $days) use ($today) {
            return (int) $db->table('folder_files')
                ->where('retention_type', 'expiration')
                ->where('expiration_date IS NOT NULL')
                ->where('expiration_date >=', $today)
                ->where('expiration_date <=', date('Y-m-d', strtotime("+{$days} days")))
                ->countAllResults();
        };

        return [
            'permanent' => (int) $db->table('folder_files')->where('retention_type', 'permanent')->countAllResults(),
            'expiring' => (int) $db->table('folder_files')->where('retention_type', 'expiration')->countAllResults(),
            'due30' => $window($db, 30),
            'due60' => $window($db, 60),
            'due90' => $window($db, 90),
            'expired' => (int) $db->table('folder_files')
                ->where('retention_type', 'expiration')
                ->where('expiration_date IS NOT NULL')
                ->where('expiration_date <', $today)
                ->countAllResults(),
        ];
    }

    /**
     * Outstanding approvals split by workflow, with the age of the oldest
     * item -- a bare count hides a request that has been waiting weeks.
     */
    private function getApprovalQueueBreakdown($db): array
    {
        $queue = [];

        $folderQueues = [
            'Pending' => 'Folder creation',
            'Pending Update' => 'Metadata update',
            'Pending Archive' => 'Archive request',
        ];

        foreach ($folderQueues as $status => $label) {
            $rows = $db->table('folders')
                ->select('MIN(updated_at) AS oldest, COUNT(*) AS total', false)
                ->where('status', $status)
                ->get()
                ->getRowArray();

            $queue[] = [
                'label' => $label,
                'count' => (int) ($rows['total'] ?? 0),
                'oldest' => $rows['oldest'] ?? null,
            ];
        }

        $requestTables = [
            'relocation_requests' => ['label' => 'Relocation', 'date' => 'requested_at'],
            'restoration_requests' => ['label' => 'Restoration', 'date' => 'requested_at'],
            'file_disposal_requests' => ['label' => 'File disposal', 'date' => 'created_at'],
        ];

        foreach ($requestTables as $table => $meta) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $dateColumn = in_array($meta['date'], $db->getFieldNames($table), true) ? $meta['date'] : null;
            $select = $dateColumn ? "MIN({$dateColumn}) AS oldest, COUNT(*) AS total" : 'NULL AS oldest, COUNT(*) AS total';

            $rows = $db->table($table)
                ->select($select, false)
                ->where('status', 'Pending')
                ->get()
                ->getRowArray();

            $queue[] = [
                'label' => $meta['label'],
                'count' => (int) ($rows['total'] ?? 0),
                'oldest' => $rows['oldest'] ?? null,
            ];
        }

        return $queue;
    }

    /**
     * Borrowings that are past their expected return date.
     */
    private function getOverdueBorrowings($db, int $limit = 5): array
    {
        if (! $db->tableExists('borrow_transactions')) {
            return ['count' => 0, 'rows' => []];
        }

        $base = static function ($db) {
            return $db->table('borrow_transactions bt')
                ->join('folders f', 'f.folder_id = bt.folder_id', 'left')
                ->where('bt.actual_return_date IS NULL')
                ->where('bt.expected_return_date <', date('Y-m-d H:i:s'))
                ->whereNotIn('bt.status', ['Returned', 'Declined', 'Cancelled']);
        };

        $count = (int) $base($db)->countAllResults();

        $rows = $base($db)
            ->select('bt.transaction_id, bt.borrower_name, bt.expected_return_date, f.file_code, f.company_name', false)
            ->orderBy('bt.expected_return_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return ['count' => $count, 'rows' => $rows];
    }

    /**
     * User population and audit activity, for system-health oversight.
     */
    private function getUserAndAuditInsights($db): array
    {
        $roleRows = $db->table('users')
            ->select('role, COUNT(*) AS total', false)
            ->groupBy('role')
            ->get()
            ->getResultArray();

        $statusRows = $db->table('users')
            ->select('status, COUNT(*) AS total', false)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $auditRecent = [];
        $topActors = [];
        $actionMix = [];

        if ($db->tableExists('audit_logs')) {
            $since = date('Y-m-d H:i:s', strtotime('-30 days'));

            $auditRecent = [
                'last7' => (int) $db->table('audit_logs')->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))->countAllResults(),
                'last30' => (int) $db->table('audit_logs')->where('created_at >=', $since)->countAllResults(),
                'total' => (int) $db->table('audit_logs')->countAllResults(),
            ];

            $topActors = $db->table('audit_logs al')
                ->select('u.username, COUNT(*) AS total', false)
                ->join('users u', 'u.user_id = al.user_id', 'left')
                ->where('al.created_at >=', $since)
                ->groupBy('al.user_id, u.username')
                ->orderBy('total', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Rows written before the call-site argument order was corrected
            // hold the user id in `action` and the real action name in
            // `entity_type`. Read through to `entity_type` for those so the
            // historical entries stay meaningful next to new, correct ones.
            $actionExpr = "CASE WHEN action REGEXP '^[0-9]+$' THEN entity_type ELSE action END";

            $actionMix = $db->table('audit_logs')
                ->select("{$actionExpr} AS action, COUNT(*) AS total", false)
                ->where('created_at >=', $since)
                ->groupBy($actionExpr, false)
                ->orderBy('total', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();
        }

        return [
            'byRole' => $roleRows,
            'byStatus' => $statusRows,
            'audit' => $auditRecent,
            'topActors' => $topActors,
            'actionMix' => $actionMix,
        ];
    }

    /**
     * Stored file volume, plus records that need someone's attention.
     */
    private function getStorageAndIntegrityInsights($db): array
    {
        $fileCount = 0;
        $fileBytes = 0;

        if ($db->tableExists('folder_files')) {
            $row = $db->table('folder_files')
                ->select('COUNT(*) AS total, COALESCE(SUM(file_size), 0) AS bytes', false)
                ->get()
                ->getRowArray();

            $fileCount = (int) ($row['total'] ?? 0);
            $fileBytes = (int) ($row['bytes'] ?? 0);
        }

        // Folders parked on a shelf that cannot legally hold them.
        $onUnsetShelves = (int) $db->table('folders f')
            ->join('locations l', 'l.location_id = f.location_id', 'inner')
            ->where('l.capacity', 0)
            ->whereNotIn('f.status', \App\Models\RackShelfModel::NON_OCCUPYING_STATUSES)
            ->countAllResults();

        $withoutFiles = 0;
        if ($db->tableExists('folder_files')) {
            $withoutFiles = (int) $db->table('folders f')
                ->where('NOT EXISTS (SELECT 1 FROM folder_files ff WHERE ff.folder_id = f.folder_id)', null, false)
                ->countAllResults();
        }

        return [
            'fileCount' => $fileCount,
            'fileBytes' => $fileBytes,
            'onUnsetShelves' => $onUnsetShelves,
            'foldersWithoutFiles' => $withoutFiles,
        ];
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