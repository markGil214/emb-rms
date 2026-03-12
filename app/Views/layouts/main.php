<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? esc($title) . ' - ' : '' ?>EMB Records System</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="font-sans bg-gray-50" x-data="{ 
    notificationsOpen: false,
    notifications: [
        { id: 1, type: 'success', message: 'New user registered successfully', time: '2 minutes ago', read: false },
        { id: 2, type: 'warning', message: 'System maintenance scheduled', time: '1 hour ago', read: false },
        { id: 3, type: 'info', message: 'Database backup completed', time: '2 hours ago', read: true }
    ],
    alerts: [],
    unreadCount() {
        return this.notifications.filter(n => !n.read).length;
    },
    init() {
        // Auto-dismiss alerts after 5 seconds
        this.$watch('alerts', () => {
            setTimeout(() => {
                if (this.alerts.length > 0) {
                    this.alerts.shift();
                }
            }, 5000);
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
                            <span x-show="unreadCount() > 0" 
                                  x-text="unreadCount() > 9 ? '9+' : unreadCount()"
                                  class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                            </span>
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
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div class="p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-2 h-2 rounded-full mt-2"
                                                     :class="{
                                                         'bg-green-500': notification.type === 'success',
                                                         'bg-yellow-500': notification.type === 'warning',
                                                         'bg-blue-500': notification.type === 'info'
                                                     }"></div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm text-gray-900" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="notification.time"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="p-3 border-t border-gray-200">
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-700">Welcome, <strong><?= auth_user()['username'] ?? 'Admin' ?></strong></span>
                        <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-white"><?= substr(auth_user()['username'] ?? 'A', 0, 1) ?></span>
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
</body>
</html>