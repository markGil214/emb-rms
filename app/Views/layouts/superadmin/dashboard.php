<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="absolute inset-0 z-0">
    <div class="ml-20 transition-all duration-300" x-data="{ 
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
        hamburgerOpen: true,
        stats: {
            totalFolders: 25,
            activeUsers: 856,
            newToday: 42,
            pendingReview: 28
        },
        activities: [
            { id: 1, user: 'John Doe', action: 'created a new record', time: '2 minutes ago', type: 'success' },
            { id: 2, user: 'Jane Smith', action: 'updated user profile', time: '15 minutes ago', type: 'info' },
            { id: 3, user: 'Admin', action: 'scheduled system maintenance', time: '1 hour ago', type: 'warning' },
            { id: 4, user: 'Mike Johnson', action: 'generated monthly report', time: '2 hours ago', type: 'success' },
            { id: 5, user: 'Sarah Wilson', action: 'deleted expired records', time: '3 hours ago', type: 'error' }
        ],
        refreshStats() {
            // Simulate API call to refresh stats
            console.log('Refreshing stats...');
        }
    }" x-init="
        window.addEventListener('storage', (e) => { 
            if (e.key === 'sidebarOpen') {
                this.sidebarOpen = e.newValue !== 'false';
            }
        });
    " :class="sidebarOpen ? 'ml-72' : 'ml-20'">
            
            <!-- Welcome Section -->
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, <?= auth_user()['username'] ?? 'Admin' ?>!</h2>
                <p class="text-gray-600">Here's what's happening in your system today.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Folders -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600 mb-1">Total Folders</p>
                    <p class="text-3xl font-bold text-blue-900" x-text="stats.totalFolders"></p>
                    <p class="text-xs text-blue-700 mt-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        +8% from last month
                    </p>
                </div>
                <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Active Users -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 hover:shadow-lg transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600 mb-1">Active Users</p>
                    <p class="text-3xl font-bold text-green-900" x-text="stats.activeUsers"></p>
                    <p class="text-xs text-green-700 mt-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        +8% from last week
                    </p>
                </div>
                <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- New Today -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200 hover:shadow-lg transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-600 mb-1">New Today</p>
                    <p class="text-3xl font-bold text-yellow-900" x-text="stats.newToday"></p>
                    <p class="text-xs text-yellow-700 mt-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        +5% from yesterday
                    </p>
                </div>
                <div class="p-3 bg-yellow-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Pending Review -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200 hover:shadow-lg transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600 mb-1">Pending Review</p>
                    <p class="text-3xl font-bold text-purple-900" x-text="stats.pendingReview"></p>
                    <p class="text-xs text-purple-700 mt-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                        -3% from last week
                    </p>
                </div>
                <div class="p-3 bg-purple-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-4h4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity Feed -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    <button @click="refreshStats()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Refresh
                    </button>
                </div>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <template x-for="activity in activities" :key="activity.id">
                    <div class="p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                     :class="{
                                         'bg-green-100': activity.type === 'success',
                                         'bg-blue-100': activity.type === 'info',
                                         'bg-yellow-100': activity.type === 'warning',
                                         'bg-red-100': activity.type === 'error'
                                     }">
                                    <svg class="w-4 h-4"
                                         :class="{
                                             'text-green-600': activity.type === 'success',
                                             'text-blue-600': activity.type === 'info',
                                             'text-yellow-600': activity.type === 'warning',
                                             'text-red-600': activity.type === 'error'
                                         }"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium" x-text="activity.user"></span>
                                    <span x-text="' ' + activity.action"></span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1" x-text="activity.time"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="p-4 border-t border-gray-200">
                <button class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium text-center">
                    View all activity
                </button>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6 space-y-4">
                <button @click="$root.addAlert('Opening new record form...', 'info')" 
                        class="w-full p-4 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors group flex items-center justify-center space-x-3">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="font-medium">New Record</span>
                </button>
                
                <button @click="$root.addAlert('Opening user management...', 'info')" 
                        class="w-full p-4 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors group flex items-center justify-center space-x-3">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">Add User</span>
                </button>
                
                <button @click="$root.addAlert('Generating report...', 'success')" 
                        class="w-full p-4 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors group flex items-center justify-center space-x-3">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-4h4"></path>
                    </svg>
                    <span class="font-medium">Generate Report</span>
                </button>
                
                <button @click="$root.addAlert('Opening settings...', 'info')" 
                        class="w-full p-4 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors group flex items-center justify-center space-x-3">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">Settings</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>