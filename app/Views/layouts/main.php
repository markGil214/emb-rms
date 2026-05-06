<?php
$headerNotifications = [];
$pendingBorrowCount = 0;
$borrowOnlyCount = 0;
$relocationOnlyCount = 0;
$pendingArchiveCount = 0;
$pendingDisposalCount = 0;
$disposalOnlyCount = 0;
$restoreOnlyCount = 0;

try {
    $borrowModel = new \App\Models\BorrowTransactionModel();
    $db = \Config\Database::connect();

    // Calculate pending borrow count for notification badge
    $allBorrows = $borrowModel->findAll();
    foreach ($allBorrows as $borrow) {
        $calculatedStatus = $borrowModel->calculateStatus($borrow);
        if ($calculatedStatus === 'Pending') {
            $pendingBorrowCount++;
        }
    }

    // Add pending relocation requests to the count
    if (can('approve_relocation') && $db->tableExists('relocation_requests')) {
        $pendingRelocationCount = $db->table('relocation_requests')
            ->where('status', 'Pending')
            ->countAllResults();
        $pendingBorrowCount += $pendingRelocationCount;
    }

    $overdueCount = count($borrowModel->getAllOverdue());
    if ($overdueCount > 0 && (can('view_borrow') || can('approve_borrow_requests') || can('view_pending_returns'))) {
        $headerNotifications[] = [
            'id' => 1,
            'type' => 'danger',
            'message' => $overdueCount . ' overdue borrower request(s) found.',
            'time' => 'Needs action',
            'read' => false,
            'link' => base_url('borrows'),
            'linkText' => 'View',
        ];
    }

    if (can('approve_borrow_requests')) {
        // Get separate counts for notifications
        $borrowOnlyCount = 0;
        $allBorrows = $borrowModel->findAll();
        foreach ($allBorrows as $borrow) {
            $calculatedStatus = $borrowModel->calculateStatus($borrow);
            if ($calculatedStatus === 'Pending') {
                $borrowOnlyCount++;
            }
        }

        if ($borrowOnlyCount > 0) {
            $headerNotifications[] = [
                'id' => 2,
                'type' => 'warning',
                'message' => $borrowOnlyCount . ' borrow request(s) need approval.',
                'time' => 'Pending approval',
                'read' => false,
                'link' => base_url('borrows/pending'),
                'linkText' => 'View',
            ];
        }
    }

    // Add separate relocation notification
    if (can('approve_relocation') && $db->tableExists('relocation_requests')) {
        $relocationOnlyCount = $db->table('relocation_requests')
            ->where('status', 'Pending')
            ->countAllResults();

        if ($relocationOnlyCount > 0) {
            $headerNotifications[] = [
                'id' => 3,
                'type' => 'info',
                'message' => $relocationOnlyCount . ' relocation request(s) pending approval.',
                'time' => 'Awaiting action',
                'read' => false,
                'link' => base_url('relocations/pending'),
                'linkText' => 'View',
            ];
        }
    }

    // Add pending archival requests to the count and notifications
    if (can('approve_archive')) {
        $pendingArchiveCount = $db->table('folders')
            ->where('status', 'Pending Archive')
            ->countAllResults();

        if ($pendingArchiveCount > 0) {
            $pendingBorrowCount += $pendingArchiveCount;
            $headerNotifications[] = [
                'id' => 4,
                'type' => 'warning',
                'message' => $pendingArchiveCount . ' folder archival request(s) pending approval.',
                'time' => 'Archival pending',
                'read' => false,
                'link' => base_url('archive'),
                'linkText' => 'View',
            ];
        }
    }

    // Add pending folder creation/update requests
    if (can('approve_folder_creation')) {
        $pendingFolderApprovalCount = $db->table('folders')
            ->whereIn('status', ['Pending', 'Pending Update'])
            ->countAllResults();

        if ($pendingFolderApprovalCount > 0) {
            $pendingBorrowCount += $pendingFolderApprovalCount;
            $headerNotifications[] = [
                'id' => 7,
                'type' => 'info',
                'message' => $pendingFolderApprovalCount . ' folder request(s) pending approval.',
                'time' => 'Awaiting action',
                'read' => false,
                'link' => base_url('document-records'),
                'linkText' => 'View',
            ];
        }
    }

    // Add pending disposal requests to the count and notifications
    if (can('approve_disposal')) {
        $disposalOnlyCount = 0;
        if ($db->tableExists('disposal_records')) {
            $disposalOnlyCount += $db->table('disposal_records')
                ->where('disposal_date', null)
                ->where('approved_by', null)
                ->where('status !=', 'Rejected')
                ->countAllResults();
        }
        
        if ($db->tableExists('file_disposal_requests')) {
            $disposalOnlyCount += $db->table('file_disposal_requests')
                ->where('status', 'Pending')
                ->countAllResults();
        }

        if ($disposalOnlyCount > 0) {
            $pendingDisposalCount = $disposalOnlyCount;
            $pendingBorrowCount += $disposalOnlyCount;
            $headerNotifications[] = [
                'id' => 5,
                'type' => 'danger',
                'message' => $disposalOnlyCount . ' disposal request(s) pending approval.',
                'time' => 'Disposal pending',
                'read' => false,
                'link' => base_url('disposal/pending'),
                'linkText' => 'View',
            ];
        }
    }

    // Add pending restoration requests to the count and notifications
    if (can('approve_restore') && $db->tableExists('restoration_requests')) {
        $restoreOnlyCount = $db->table('restoration_requests')
            ->where('status', 'Pending')
            ->countAllResults();

        if ($restoreOnlyCount > 0) {
            $pendingBorrowCount += $restoreOnlyCount;
            $headerNotifications[] = [
                'id' => 6,
                'type' => 'info',
                'message' => $restoreOnlyCount . ' restoration request(s) pending approval.',
                'time' => 'Restore pending',
                'read' => false,
                'link' => base_url('archive'),
                'linkText' => 'View',
            ];
        }
    }

} catch (\Throwable $e) {
    $headerNotifications = [];
    $pendingBorrowCount = 0;
}

$headerNotificationsJson = json_encode($headerNotifications, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= base_url('images/EMB-Logo.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('images/EMB-Logo.png') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/loading-animation.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modals.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/dark-mode.css') ?>">
    <style>
        html,
        body,
        body * {
            font-size: 12px !important;
        }

        /* Dark mode styles */
        .dark {
            color-scheme: dark;
        }
        
        .dark body {
            background-color: rgb(17 24 39);
            color: rgb(243 244 246);
        }
        
        .dark .bg-white {
            background-color: rgb(31 41 55) !important;
        }

        .dark .bg-gray-50 {
            background-color: rgb(17 24 39) !important;
        }
        
        .dark .bg-gray-100 {
            background-color: rgb(31 41 55) !important;
        }
        
        .dark .bg-gray-200 {
            background-color: rgb(55 65 81) !important;
        }
        
        .dark .bg-gray-300 {
            background-color: rgb(75 85 99) !important;
        }
        
        .dark .bg-gray-700 {
            background-color: rgb(55 65 81) !important;
        }
        
        .dark .bg-gray-900 {
            background-color: rgb(31 41 55) !important;
        }
        
        .dark .text-gray-900 {
            color: rgb(243 244 246) !important;
        }
        
        .dark .text-gray-800 {
            color: rgb(229 231 235) !important;
        }
        
        .dark .text-gray-700 {
            color: rgb(209 213 219) !important;
        }
        
        .dark .text-gray-600 {
            color: rgb(156 163 175) !important;
        }
        
        .dark .text-gray-500 {
            color: rgb(156 163 175) !important;
        }
        
        .dark .border-gray-200 {
            border-color: rgb(55 65 81) !important;
        }
        
        .dark .border-gray-100 {
            border-color: rgb(55 65 81) !important;
        }
        
        .dark .border-gray-300 {
            border-color: rgb(75 85 99) !important;
        }
        
        .dark .hover\:bg-gray-50:hover {
            background-color: rgb(31 41 55) !important;
        }
        
        .dark .hover\:bg-gray-100:hover {
            background-color: rgb(55 65 81) !important;
        }
        
        .dark .hover\:bg-gray-200:hover {
            background-color: rgb(75 85 99) !important;
        }
        
        .dark .hover\:bg-gray-900:hover {
            background-color: rgb(17 24 39) !important;
        }
        
        .dark .hover\:bg-green-900:hover {
            background-color: rgb(20 83 45) !important;
        }
        
        .dark .hover\:bg-green-600:hover {
            background-color: rgb(22 101 52) !important;
        }
        
        .dark .shadow-lg {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.5), 0 4px 6px -2px rgb(0 0 0 / 0.3) !important;
        }
        
        .dark .shadow {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.5), 0 1px 2px 0 rgb(0 0 0 / 0.3) !important;
        }
        
        .dark .shadow-sm {
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.3) !important;
        }
        
        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        
        /* Dark mode for navigation bar */
        .dark .bg-green-700 {
            background-color: rgb(20 83 45) !important;
        }
        
        .dark .bg-green-600 {
            background-color: rgb(22 101 52) !important;
        }

        /* Dark mode for archive table wrapper and header section */
        .dark .archive-table-wrap {
            background-color: rgb(31 41 55) !important;
            border-color: rgb(55 65 81) !important;
        }

        .dark .archive-table-wrap .bg-white {
            background-color: rgb(31 41 55) !important;
        }

        .dark .archive-table-wrap > div {
            background-color: rgb(31 41 55) !important;
            border-color: rgb(55 65 81) !important;
        }

        .dark .archive-table-wrap .px-6.py-4 {
            background-color: rgb(31 41 55) !important;
        }

        /* Dark mode for archive/disposal toolbars */

        .dark .archive-toolbar {
            background-color: transparent;
        }

        /* Dark mode for disposal summary cards and panel */
        .dark .disposal-panel {
            background-color: rgb(31 41 55) !important;
        }

        .dark .disposal-summary-card {
            background-color: rgb(55 65 81) !important;
            border-color: rgb(75 85 99) !important;
        }

        .dark .disposal-summary-card p.text-sm {
            color: rgb(156 163 175) !important;
        }

        .dark .disposal-summary-card .text-3xl {
            color: rgb(243 244 246) !important;
        }

        .dark .disposal-summary-card--blue {
            background-color: rgb(7 89 133 / 0.3) !important;
            border-left: 4px solid rgb(96 165 250) !important;
        }

        .dark .disposal-summary-card--amber {
            background-color: rgb(92 51 23 / 0.3) !important;
            border-left: 4px solid rgb(250 204 21) !important;
        }

        /* Dark mode for disposal records header section */
        .dark .disposal-toolbar {
            background-color: transparent;
        }

        .dark .disposal-search-wrap {
            background-color: rgb(55 65 81) !important;
            border-color: rgb(75 85 99) !important;
        }

        .dark .disposal-search-input {
            background-color: rgb(55 65 81) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(75 85 99) !important;
        }

        .dark .disposal-search-input::placeholder {
            color: rgb(107 114 128) !important;
        }

        .dark .disposal-search-input:focus {
            background-color: rgb(55 65 81) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(59 130 246) !important;
            box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1) !important;
        }

        .dark .disposal-search-icon {
            color: rgb(107 114 128) !important;
        }

        .dark .disposal-quick-filter {
            background-color: rgb(55 65 81) !important;
            color: rgb(209 213 219) !important;
            border-color: rgb(75 85 99) !important;
        }

        .dark .disposal-quick-filter:hover {
            background-color: rgb(75 85 99) !important;
            color: rgb(243 244 246) !important;
        }

        .dark .disposal-quick-filter.is-active {
            background-color: rgb(59 130 246) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(59 130 246) !important;
        }

        /* Dark mode for disposal records section text */
        .dark .disposal-panel .px-6.py-4 h2 {
            color: rgb(243 244 246) !important;
        }

        .dark .disposal-panel .px-6.py-4 p {
            color: rgb(156 163 175) !important;
        }

        .dark .archive-search-input {
            background-color: rgb(31 41 55) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(55 65 81) !important;
        }

        .dark .archive-search-input::placeholder {
            color: rgb(107 114 128) !important;
        }

        .dark .archive-search-input:focus {
            background-color: rgb(31 41 55) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(59 130 246) !important;
            box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1) !important;
        }

        .dark .archive-search-icon {
            color: rgb(107 114 128) !important;
        }

        .dark .archive-quick-filter {
            background-color: rgb(31 41 55) !important;
            color: rgb(209 213 219) !important;
            border-color: rgb(55 65 81) !important;
        }

        .dark .archive-quick-filter:hover {
            background-color: rgb(55 65 81) !important;
            color: rgb(243 244 246) !important;
        }

        .dark .archive-quick-filter.is-active {
            background-color: rgb(59 130 246) !important;
            color: rgb(243 244 246) !important;
            border-color: rgb(59 130 246) !important;
        }

        .dark .archive-filter-trigger {
            background-color: rgb(31 41 55) !important;
            color: rgb(209 213 219) !important;
            border-color: rgb(55 65 81) !important;
        }

        .dark .archive-filter-trigger:hover {
            background-color: rgb(55 65 81) !important;
            color: rgb(243 244 246) !important;
        }

        /* Dark mode for archive table */
        .dark table.archive-table {
            background-color: rgb(31 41 55) !important;
            color: rgb(226 232 240) !important;
        }

        .dark table.archive-table thead {
            background-color: rgb(55 65 81) !important;
        }

        .dark table.archive-table thead th {
            background-color: rgb(55 65 81) !important;
            color: rgb(156 163 175) !important;
            border-color: rgb(75 85 99) !important;
        }

        .dark table.archive-table tbody tr {
            border-color: rgb(55 65 81) !important;
            background-color: rgb(31 41 55) !important;
        }

        .dark table.archive-table tbody tr:hover {
            background-color: rgb(55 65 81) !important;
        }

        .dark table.archive-table tbody td {
            color: rgb(226 232 240) !important;
            border-color: rgb(55 65 81) !important;
        }

        .dark table.archive-table a {
            color: rgb(96 165 250) !important;
        }

        .dark table.archive-table a:hover {
            color: rgb(147 197 253) !important;
            text-decoration: underline;
        }

        /* Dark mode for archive status badges */
        .dark .archive-status-badge {
            border-color: transparent;
        }

        .dark .archive-status-badge.bg-yellow-100 {
            background-color: rgb(78 36 0 / 0.6) !important;
            color: rgb(253 224 71) !important;
        }

        .dark .archive-status-badge.bg-blue-100 {
            background-color: rgb(7 89 133 / 0.6) !important;
            color: rgb(147 197 253) !important;
        }

        .dark .archive-status-badge.bg-green-100 {
            background-color: rgb(20 83 45 / 0.6) !important;
            color: rgb(134 239 172) !important;
        }

        .dark .archive-status-badge.bg-gray-200 {
            background-color: rgb(75 85 99 / 0.6) !important;
            color: rgb(203 213 225) !important;
        }
        
        /* Dark mode for buttons */
        .dark .bg-blue-600 {
            background-color: rgb(37 99 235) !important;
        }
        
        .dark .bg-blue-700 {
            background-color: rgb(29 78 216) !important;
        }
        
        .dark .hover\:bg-blue-700:hover {
            background-color: rgb(29 78 216) !important;
        }
        
        .dark .bg-red-50 {
            background-color: rgb(127 29 29) !important;
        }
        
        .dark .bg-green-50 {
            background-color: rgb(20 83 45) !important;
        }
        
        .dark .text-red-700 {
            color: rgb(248 113 113) !important;
        }
        
        .dark .text-green-700 {
            color: rgb(134 239 172) !important;
        }
        
        .dark .border-red-200 {
            border-color: rgb(127 29 29) !important;
        }
        
        .dark .border-green-200 {
            border-color: rgb(20 83 45) !important;
        }

        /* Reusable Disposal Action Button Styles */
        .disposal-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
            text-decoration: none !important;
            cursor: pointer;
        }

        .disposal-action--primary {
            background: #16a34a !important;
            color: #ffffff !important;
            border: 1px solid #15803d !important;
        }

        .disposal-action--primary:hover {
            background: #15803d !important;
        }

        .disposal-action--secondary {
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            color: #374151 !important;
        }

        .disposal-action--secondary:hover {
            background: #f3f4f6 !important;
            color: #111827 !important;
        }

        .disposal-action--danger {
            background: #ef4444 !important;
            color: #ffffff !important;
            border: 1px solid #b91c1c !important;
        }

        .disposal-action--danger:hover {
            background: #dc2626 !important;
        }

        .disposal-action--warning {
            background: #f59e0b !important;
            color: #ffffff !important;
            border: 1px solid #d97706 !important;
        }

        .disposal-action--warning:hover {
            background: #d97706 !important;
        }

        .disposal-action--info {
            background: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #1d4ed8 !important;
        }

        .disposal-action--info:hover {
            background: #1d4ed8 !important;
        }

        /* Dark Mode Overrides */
        .dark .disposal-action--primary {
            background: #059669 !important;
            border-color: #065f46 !important;
        }

        .dark .disposal-action--secondary {
            background: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
        }

        .dark .disposal-action--secondary:hover {
            background: #4b5563 !important;
        }

        .dark .disposal-action--danger {
            background: #dc2626 !important;
            border-color: #991b1b !important;
        }

        .dark .disposal-action--warning {
            background: #d97706 !important;
            border-color: #92400e !important;
        }

        .dark .disposal-action--info {
            background: #1d4ed8 !important;
            border-color: #1e40af !important;
        }

        .dark .disposal-action {
            color: #ffffff !important;
        }
    </style>
</head>
<body class="font-sans bg-gray-50" x-data="{ 
    darkMode: localStorage.getItem('darkMode') === 'true',
    sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
    notificationsOpen: false,
    userDropdownOpen: false,
    notifications: <?= esc($headerNotificationsJson ?: '[]', 'attr') ?>,
    alerts: [],
    unreadCount() {
        return this.notifications.filter(n => !n.read).length;
    },
    init() {
        // Apply dark mode on load
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        }

        // Keep sidebar state persisted and synced across tabs/views.
        this.$watch('sidebarOpen', (value) => {
            localStorage.setItem('sidebarOpen', value);
        });
        window.addEventListener('storage', (e) => {
            if (e.key === 'sidebarOpen') {
                this.sidebarOpen = e.newValue !== 'false';
            }
        });
        
        // Auto-dismiss alerts after 5 seconds
        this.$watch('alerts', () => {
            setTimeout(() => {
                if (this.alerts.length > 0) {
                    this.alerts.shift();
                }
            }, 5000);
        });
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-dropdown')) {
                this.userDropdownOpen = false;
            }
        });
    },
    addAlert(message, type = 'info') {
        this.alerts.push({ id: Date.now(), message, type });
    },
    markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
            notification.read = true;
        }
    },
    markAllAsRead() {
        this.notifications.forEach(n => n.read = true);
    },
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    }
}">
    
    <div style="display:flex; min-height:100vh; width:100%;">
    <!-- Vertical Navigation Bar for Admin Dashboard -->
    <nav
    class="bg-green-700 text-white shadow-lg transition-all duration-300 z-30"
    :style="sidebarOpen
        ? 'width:16rem; height:100vh; position:fixed; top:0; left:0; overflow-y:auto; overflow-x:hidden;'
        : 'width:5.5rem; height:100vh; position:fixed; top:0; left:0; overflow-y:auto; overflow-x:hidden;'">

        <!-- Navigation Menu -->
        <ul class="p-4 space-y-2">
            <!-- Header Item -->
            <li class="bg-green-700 text-white mb-4">
                <div class="flex items-center relative" :class="sidebarOpen ? 'ml-3' : 'justify-center'">
                    <img x-show="sidebarOpen" x-transition src="/images/EMB-Logo.png" alt="EMB Records Logo" class="w-10 h-10" :class="sidebarOpen ? 'ml-2' : ''">
                    <h2 x-show="sidebarOpen" x-transition class="text-xl font-bold ml-2">RMS</h2>
                    <button type="button"
                            @click="toggleSidebar()"
                            :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'"
                            aria-label="Toggle sidebar"
                            :style="sidebarOpen
                                ? 'position:absolute; top:-6px; right:-4px; z-index:80; width:34px; height:34px; border-radius:10px; border:1px solid rgba(255,255,255,0.22); background:linear-gradient(180deg,#166534 0%,#14532d 100%); box-shadow:0 8px 20px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.16); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:transform .2s ease, box-shadow .2s ease, background .2s ease;'
                                : 'position:absolute; top:-6px; left:50%; transform:translateX(-50%); z-index:80; width:34px; height:34px; border-radius:10px; border:1px solid rgba(255,255,255,0.22); background:linear-gradient(180deg,#166534 0%,#14532d 100%); box-shadow:0 8px 20px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.16); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:transform .2s ease, box-shadow .2s ease, background .2s ease;'"
                            @mouseenter="if (sidebarOpen) { $el.style.transform='translateY(-1px) scale(1.02)'; } else { $el.style.transform='translateX(-50%) translateY(-1px) scale(1.02)'; } $el.style.boxShadow='0 10px 22px rgba(0,0,0,0.28), inset 0 1px 0 rgba(255,255,255,0.2)'"
                            @mouseleave="if (sidebarOpen) { $el.style.transform='translateY(0) scale(1)'; } else { $el.style.transform='translateX(-50%) translateY(0) scale(1)'; } $el.style.boxShadow='0 8px 20px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.16)'"
                            @mousedown="if (sidebarOpen) { $el.style.transform='translateY(0) scale(.97)'; } else { $el.style.transform='translateX(-50%) translateY(0) scale(.97)'; }"
                            @mouseup="if (sidebarOpen) { $el.style.transform='translateY(-1px) scale(1.02)'; } else { $el.style.transform='translateX(-50%) translateY(-1px) scale(1.02)'; }">
                        <span style="position:relative; width:16px; height:12px; display:block;">
                            <span
                                :style="sidebarOpen
                                    ? 'position:absolute; left:0; top:0; width:16px; height:2px; border-radius:2px; background:#e8fff1; transform:translateY(5px) rotate(45deg); transition:transform .22s ease, opacity .22s ease;'
                                    : 'position:absolute; left:0; top:0; width:16px; height:2px; border-radius:2px; background:#e8fff1; transform:translateY(0) rotate(0deg); transition:transform .22s ease, opacity .22s ease;'">
                            </span>
                            <span
                                :style="sidebarOpen
                                    ? 'position:absolute; left:0; top:5px; width:16px; height:2px; border-radius:2px; background:#e8fff1; opacity:0; transform:scaleX(.5); transition:transform .22s ease, opacity .22s ease;'
                                    : 'position:absolute; left:0; top:5px; width:16px; height:2px; border-radius:2px; background:#e8fff1; opacity:1; transform:scaleX(1); transition:transform .22s ease, opacity .22s ease;'">
                            </span>
                            <span
                                :style="sidebarOpen
                                    ? 'position:absolute; left:0; top:10px; width:16px; height:2px; border-radius:2px; background:#e8fff1; transform:translateY(-5px) rotate(-45deg); transition:transform .22s ease, opacity .22s ease;'
                                    : 'position:absolute; left:0; top:10px; width:16px; height:2px; border-radius:2px; background:#e8fff1; transform:translateY(0) rotate(0deg); transition:transform .22s ease, opacity .22s ease;'">
                            </span>
                        </span>
                    </button>
                </div>
                <hr class ="mt-4">
            </li>
            <li>
                <a href="<?= route_to('dashboard') ?>" 
                class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('dashboard') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Dashboard</span>
                </a>
            </li>
            <li>
                <?php if (can('view_shelf_map')): ?>
                <a href="<?= route_to('shelfmap') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('shelfmap') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Shelf Map And Search</span>
                </a>
                <?php endif; ?>
            </li>

            <li>
                    <?php if (can('view_documents') || can('create_document_record') || can('edit_document_metadata') || can('search_documents') || can('manage_racks') || can('manage_categories') || can('approve_folder_creation') || can('approve_archive')): ?>
                <a href="<?= route_to('records') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('records') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Document Records</span>
                </a>
                <?php endif; ?>
            </li>

            <li>
                <?php if (can('request_borrow') || can('approve_borrow_requests') || can('view_borrow')): ?>
                <a href="<?= route_to('borrows.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('borrows.index') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Borrow Management</span>
                    <?php if ($borrowOnlyCount > 0): ?>
                        <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">
                            <?= $borrowOnlyCount ?>
                        </span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            </li>

            <li>
                <?php if (can('initiate_relocation') || can('approve_relocation')): ?>
                <a href="<?= route_to('relocations.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('relocations.index') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Relocation</span>
                </a>
                <?php endif; ?>
            </li>

            <li>
                <?php if (can('approve_archive') || can('request_restore') || can('approve_restore')): ?>
                <a href="<?= route_to('archive.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('archive.index') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Archive Folders</span>
                </a>
                <?php endif; ?>
            </li>

            <li>
                <?php if (can('request_disposal') || can('approve_disposal')): ?>
                <a href="<?= route_to('disposal.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('disposal.index') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Disposal Management</span>
                    <?php if ($pendingDisposalCount > 0): ?>
                        <span x-show="sidebarOpen" class="ml-auto bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">
                            <?= $pendingDisposalCount ?>
                        </span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            </li>

            <li>
                <?php if (can('manage_users')): ?>
                <a href="<?= route_to('users.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
                :class="window.location.pathname === '<?= route_to('users.index') ?>' ? 'bg-green-900' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a4 4 0 00-4-4h-2m-4 5H2v-1a4 4 0 014-4h2m8-5a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 100-6 3 3 0 000 6zM6 12a3 3 0 100-6 0 4 4 0 000 6z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">User Management</span>
                </a>
                <?php endif; ?>
            </li>

            <li>
                <?php if (can('manage_users')): ?>
                <a href="<?= route_to('permissions.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="ml-3">Permissions</span>
                </a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content Area -->
    <main
        class="transition-all duration-300 min-h-screen flex flex-col"
        :style="sidebarOpen
            ? 'flex:1; min-width:0; margin-left:16rem;'
            : 'flex:1; min-width:0; margin-left:5.5rem;'">
        <!-- Top Navigation Bar -->
        <header class="bg-white shadow-sm border border-gray-200 z-20 mx-4 sm:mx-6 lg:mx-8 rounded-lg mt-4">
            <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img src="<?= base_url('images/EMB-Logo.png') ?>" alt="EMB Logo" class="w-10 h-10 mr-2">
                    <h1 class="text-2xl font-bold text-gray-900"><?= isset($title) ? esc($title) : 'Dashboard' ?></h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notifications Dropdown -->
                    <div class="relative notifications-dropdown z-10">
                        <button @click="notificationsOpen = !notificationsOpen" 
                                @click.away="notificationsOpen = false"
                                class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <!-- Notification Badge -->
                            <?php if ($pendingBorrowCount > 0): ?>
                            <span class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                                <?= $pendingBorrowCount > 9 ? '9+' : $pendingBorrowCount ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        
                        <div x-show="notificationsOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-40">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="p-4 text-sm text-gray-500">No new notifications</div>
                                </template>
                                <template x-for="notification in notifications" :key="notification.id">
                                    <a :href="notification.link" class="block p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0" @click="markAsRead(notification.id)">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="notification.time"></p>
                                            </div>
                                            <div class="w-2 h-2 rounded-full ml-2 mt-1 flex-shrink-0"
                                                 :class="{
                                                     'bg-red-500': notification.type === 'danger',
                                                     'bg-green-500': notification.type === 'success',
                                                     'bg-yellow-500': notification.type === 'warning',
                                                     'bg-blue-500': notification.type === 'info'
                                                 }"></div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDarkMode()"
                            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                            :class="darkMode ? 'text-yellow-500' : 'text-gray-600'"
                            :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                        <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- User Info Dropdown -->
                    <div class="relative user-dropdown">
                        <button @click="userDropdownOpen = !userDropdownOpen" 
                                class="flex items-center space-x-3 hover:bg-gray-100 rounded-lg p-2 transition-colors">
                            <span class="text-sm text-gray-700"><strong><?= auth_user()['username'] ?? 'Admin' ?></strong></span>
                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="userDropdownOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-40">
                            <div class="p-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?= auth_user()['username'] ?? 'Admin' ?></p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>         
                             
                                <a href="<?= url_to('logout') ?>" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </a>
                            
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Alert Messages -->
        <div style="position:fixed; top:80px; right:32px; z-index:50;">
            <template x-for="alert in alerts" :key="alert.id"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="transform translate-x-full opacity-0"
                     x-transition:enter-end="transform translate-x-0 opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="transform translate-x-0 opacity-100"
                     x-transition:leave-end="transform translate-x-full opacity-0">
                <div class="max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 p-4"
                     :class="{
                         'border-green-500': alert.type === 'success',
                         'border-red-500': alert.type === 'error',
                         'border-yellow-500': alert.type === 'warning',
                         'border-blue-500': alert.type === 'info'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5"
                                 :class="{
                                     'text-green-500': alert.type === 'success',
                                     'text-red-500': alert.type === 'error',
                                     'text-yellow-500': alert.type === 'warning',
                                     'text-blue-500': alert.type === 'info'
                                 }"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-gray-900" x-text="alert.message"></p>
                        </div>
                        <button @click="alerts = alerts.filter(a => a.id !== alert.id)" 
                                class="ml-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Page Content -->
        <div class="p-8 flex-1" style="margin-top:0;">
            <!-- Flash Messages (PHP Session) -->
            <?php if(session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if(session()->getFlashdata('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
        
                <?= $this->renderSection('content') ?>
         
        </div>
    </main>
    </div>

    <!-- Professional Modal System -->
    <script src="<?= base_url('assets/js/modal-system.js') ?>"></script>
    
    <!-- Request Context Helpers -->
    <script src="<?= base_url('assets/js/request-context-helpers.js') ?>"></script>
    
    <!-- Loading Animation Script -->
    <script src="<?= base_url('js/loading-animation.js') ?>"></script>
</body>
</html>
