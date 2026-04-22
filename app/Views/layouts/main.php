<?php
$headerNotifications = [];
$pendingBorrowCount = 0;

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
    if ($overdueCount > 0 && (can('view_own_borrow') || can('view_all_borrow') || can('view_pending_returns'))) {
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
    <title><?= isset($title) ? esc($title) . ' - ' : '' ?>EMB Records System</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('images/EMB-Logo.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('images/EMB-Logo.png') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/loading-animation.css') ?>">
    <style>
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
    </style>
</head>
<body class="font-sans bg-gray-50" x-data="{ 
    darkMode: localStorage.getItem('darkMode') === 'true',
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
    }
}">
    
    <!-- Include Vertical Navigation -->
    <?= view('layouts/AdminNavigationBar') ?>
    
    <!-- Main Content Area -->
    <main class="transition-all duration-300" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }" x-init="$watch('sidebarOpen', (value) => localStorage.setItem('sidebarOpen', value)); window.addEventListener('storage', (e) => { if (e.key === 'sidebarOpen') this.sidebarOpen = e.newValue !== 'false'; });" :class="sidebarOpen ? 'ml-72' : 'ml-20'">
        <!-- Top Navigation Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20 max-w-100%">
            <div class="px-8 py-4 flex items-center justify-between">
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
                                    <div class="p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0" @click="markAsRead(notification.id)">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-2 h-2 rounded-full mt-2"
                                                     :class="{
                                                         'bg-red-500': notification.type === 'danger',
                                                         'bg-green-500': notification.type === 'success',
                                                         'bg-yellow-500': notification.type === 'warning',
                                                         'bg-blue-500': notification.type === 'info'
                                                     }"></div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm text-gray-900" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="notification.time"></p>
                                                <a :href="notification.link" class="inline-block text-xs text-blue-600 hover:text-blue-800 font-medium mt-2" x-text="notification.linkText || 'View'"></a>
                                            </div>
                                        </div>
                                    </div>
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
                            <span class="text-sm text-gray-700">Welcome, <strong><?= auth_user()['username'] ?? 'Admin' ?></strong></span>
                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-white"><?= substr(auth_user()['username'] ?? 'A', 0, 1) ?></span>
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
        <div class="fixed top-20 right-8 z-50 space-y-2">
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
        <div class="p-8">
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

    <div id="appConfirmModal" style="position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:16px;" aria-hidden="true">
        <div style="position:absolute; inset:0; background:rgba(15,23,42,0.18);"></div>
        <div id="appConfirmModalCard" style="position:relative; z-index:1; width:100%; max-width:480px; overflow:hidden; border-radius:12px; background:#ffffff; box-shadow:0 10px 30px rgba(15,23,42,0.18); border:1px solid rgba(15,23,42,0.08); color:#111827; min-height:280px;">
            <div style="padding:36px 36px 24px 36px;">
                <h3 id="appConfirmModalTitle" style="margin:0; font-size:17px; line-height:24px; font-weight:600; color:#111827;">Confirmation</h3>
                <p style="margin:12px 0 0 0; font-size:14px; line-height:22px; color:#4b5563;">Please review this action before continuing.</p>
                <p id="appConfirmModalMessage" style="margin:20px 0 0 0; padding:16px 18px; font-size:14px; line-height:24px; color:#374151; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px;">Are you sure?</p>
            </div>
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:12px; padding:20px 36px; background:#f8fafc; border-top:1px solid #e5e7eb;">
                <button id="appConfirmCancel" type="button" style="appearance:none; border:0; background:transparent; padding:10px 14px; border-radius:8px; font-size:14px; font-weight:500; color:#6b7280; cursor:pointer;">Cancel</button>
                <button id="appConfirmConfirm" type="button" style="appearance:none; border:0; background:#2563eb; padding:10px 18px; border-radius:8px; font-size:14px; font-weight:600; color:#ffffff; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.08);">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('appConfirmModal');
            if (!modal) {
                return;
            }

            const titleEl = document.getElementById('appConfirmModalTitle');
            const messageEl = document.getElementById('appConfirmModalMessage');
            const cancelBtn = document.getElementById('appConfirmCancel');
            const confirmBtn = document.getElementById('appConfirmConfirm');
            const modalCard = document.getElementById('appConfirmModalCard');

            let onConfirm = null;
            let onCancel = null;

            function closeModal() {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                onConfirm = null;
                onCancel = null;
            }

            function openModal(message, confirmCallback, cancelCallback, options) {
                const opts = options || {};

                titleEl.textContent = opts.title || 'Confirmation';
                messageEl.textContent = message || 'Are you sure?';
                confirmBtn.textContent = opts.confirmText || 'Confirm';
                cancelBtn.textContent = opts.cancelText || 'Cancel';

                onConfirm = typeof confirmCallback === 'function' ? confirmCallback : null;
                onCancel = typeof cancelCallback === 'function' ? cancelCallback : null;

                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }

            cancelBtn.addEventListener('click', function () {
                const callback = onCancel;
                closeModal();
                if (callback) {
                    callback();
                }
            });

            confirmBtn.addEventListener('click', function () {
                const callback = onConfirm;
                closeModal();
                if (callback) {
                    callback();
                }
            });

            modal.addEventListener('click', function (event) {
                if (modalCard && !modalCard.contains(event.target)) {
                    const callback = onCancel;
                    closeModal();
                    if (callback) {
                        callback();
                    }
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.style.display !== 'none') {
                    const callback = onCancel;
                    closeModal();
                    if (callback) {
                        callback();
                    }
                }
            });

            window.showAppConfirm = function (message, confirmCallback, cancelCallback, options) {
                openModal(message, confirmCallback, cancelCallback, options || {});
            };

            window.showAppAlert = function (message, options) {
                openModal(message, null, null, Object.assign({
                    title: 'Notice',
                    confirmText: 'Confirm',
                    cancelText: 'Cancel'
                }, options || {}));
            };

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const message = form.getAttribute('data-confirm-message');
                if (!message) {
                    return;
                }

                if (form.dataset.modalConfirmed === 'true') {
                    form.dataset.modalConfirmed = 'false';
                    return;
                }

                event.preventDefault();

                window.showAppConfirm(message, function () {
                    form.dataset.modalConfirmed = 'true';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            });
        })();
    </script>
    
    <!-- Loading Animation Script -->
    <script src="<?= base_url('js/loading-animation.js') ?>"></script>
</body>
</html>