<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .dashboard-shell {
        padding: 1.5rem;
    }

    .dashboard-stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 1rem !important;
        margin-bottom: 2rem !important;
    }

    .dashboard-content-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 1rem !important;
    }

    .dashboard-recent-users {
        grid-column: span 2 / span 2;
    }

    @media (max-width: 1279px) {
        .dashboard-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .dashboard-content-grid {
            grid-template-columns: 1fr !important;
        }

        .dashboard-recent-users {
            grid-column: auto;
        }
    }

    @media (max-width: 639px) {
        .dashboard-shell {
            padding: 1rem;
        }

        .dashboard-stats-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<!-- Welcome Section -->
<div class="dashboard-shell p-4 sm:p-6 lg:p-8">
            <div class="mb-8">
                <p class="text-gray-600 text-sm">Here's what's happening in your system today.</p>
            </div>

            <!-- Stats Grid -->
            <div class="dashboard-stats-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-8">
                
                <!-- Total Folders -->
                <div class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 mb-2">Total Documents</p>
                            <p class="text-2xl font-bold text-blue-600 mb-1"><?= $stats['totalDocuments'] ?? 0 ?></p>
                            <p class="text-sm text-gray-500">in system</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full ml-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <?php if (is_super_admin()): ?>
                <!-- Active Users -->
                <div class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 mb-2">Active Users</p>
                            <p class="text-2xl font-bold text-green-600 mb-1"><?= $stats['totalUsers'] ?? 0 ?></p>
                            <p class="text-sm text-gray-500">registered</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full ml-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Available Documents -->
                <div class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 mb-2">Available</p>
                            <p class="text-2xl font-bold text-yellow-600 mb-1"><?= $documentStats['availableCount'] ?? 0 ?></p>
                            <p class="text-sm text-gray-500">documents</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full ml-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                 <!-- Pending Approvals -->
                <div class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 mb-2">Pending</p>
                            <p class="text-2xl font-bold text-purple-600 mb-1"><?= $stats['pendingApprovals'] ?? 0 ?></p>
                            <p class="text-sm text-gray-500">approvals</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full ml-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-4h4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Content Grid -->
            <div class="dashboard-content-grid grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
                
                <?php if (is_super_admin()): ?>
                    <!-- Recent Users Table -->
                    <div class="dashboard-recent-users xl:col-span-2 bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                                <button @click="refreshStats()" class="px-4 py-2 text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-medium transition-colors">
                                    Refresh
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Username</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <span class="text-blue-600 font-semibold text-sm">
                                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: '-') ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?= $user['role'] === 'SuperAdmin' ? 'bg-purple-100 text-purple-800' : ($user['role'] === 'Admin' ? 'bg-blue-100 text-blue-800' :
                                                    'bg-green-100 text-green-800') ?>">
                                                    <?= htmlspecialchars($user['role']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= (($user['status'] ?? 'Active') === 'Inactive') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                                    <?= htmlspecialchars($user['status'] ?? 'Active') ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-200">
                            <button class="w-full px-4 py-2 text-sm bg-gray-50 text-gray-700 hover:bg-100 rounded-lg font-medium transition-colors">
                                View all users
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Document Status Chart -->
                <div class="<?= is_super_admin() ? '' : 'xl:col-span-3' ?> bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Document Status</h3>
                    </div>
                    <div class="p-4">
                        <div class="relative w-48 h-48 mx-auto">
                            <canvas id="myChart" width="192" height="192"></canvas>
                        </div>
                        
                        <!-- Chart Legend -->
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">Available</span>
                                </div>
                                <span class="font-semibold text-gray-900"><?= $documentStats['availableCount'] ?? 0 ?></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">Borrowed</span>
                                </div>
                                <span class="font-semibold text-gray-900"><?= $documentStats['borrowedCount'] ?? 0 ?></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">Archived</span>
                                </div>
                                <span class="font-semibold text-gray-900"><?= $documentStats['archivedCount'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Chart JavaScript -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('darkMode', 'false');

                    var ctx = document.getElementById('myChart').getContext('2d');

                    var myChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Available', 'Borrowed', 'Archived'],
                            datasets: [{
                                data: [<?= $documentStats['availableCount'] ?? 0 ?>, <?= $documentStats['borrowedCount'] ?? 0 ?>, <?= $documentStats['archivedCount'] ?? 0 ?>],
                                backgroundColor: [
                                    'rgba(34, 197, 94, 0.8)',
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(220, 38, 38, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(34, 197, 94, 1)',
                                    'rgba(59, 130, 246, 1)',
                                    'rgba(220, 38, 38, 1)'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    padding: 12,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.label || '';
                                            var value = context.parsed || 0;
                                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>

        </div>

        <?= $this->endSection() ?>
        