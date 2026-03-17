<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div class="absolute inset-0 z-0">

    <div class="ml-20 transition-all duration-300" :class="sidebarOpen ? 'ml-72' : 'ml-20'">



        <!-- Welcome Section -->

        <div class="p-8">

            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, <?= auth_user()['username'] ?? 'Admin' ?>!</h2>

            <p class="text-gray-600 mt-10 mb-10">Here's what's happening in your system today.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Total Folders -->

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 hover:scale-105">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-blue-600 mb-1">Total Folders</p>

                            <p class="text-3xl font-bold text-blue-900" x-text="stats.totalFolders"></p>

                            <p class="text-xs text-blue-700 mt-2">





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

                            <p class="text-xs text-green-700 mt-2"></p>

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

                <!-- Recent Users Table -->

                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">

                    <div class="p-6 border-b border-gray-200">

                        <div class="flex items-center justify-between">

                            <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>

                            <button @click="refreshStats()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">

                                Refresh

                            </button>

                        </div>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead class="bg-gray-50 border-b border-gray-200">

                                <tr>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>

                                </tr>

                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">

                                <?php foreach ($recentUsers as $user): ?>

                                    <tr class="hover:bg-gray-50 transition-colors">

                                        <td class="px-6 py-4 whitespace-nowrap">

                                            <div class="flex items-center">

                                                <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">

                                                    <span class="text-green-600 font-medium text-sm">

                                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>

                                                    </span>

                                                </div>

                                                <div class="ml-3">

                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></div>

                                                </div>

                                            </div>

                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">

                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>

                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">

                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 

                                    <?= $user['role'] === 'SuperAdmin' ? 'bg-purple-100 text-purple-800' : ($user['role'] === 'Admin' ? 'bg-blue-100 text-blue-800' :

                                        'bg-green-100 text-green-800') ?>">

                                                <?= htmlspecialchars($user['role']) ?>

                                            </span>

                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">

                                            <?= date('M d, Y', strtotime($user['created_at'])) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="p-4 border-t border-gray-200">

                        <button class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium text-center">

                            View all users

                        </button>

                    </div>

                </div>


                <!-- Document Status Distribution -->

                <div class="bg-white rounded-lg shadow border border-gray-200">

                    <div class="p-4 border-b border-gray-200">

                        <h3 class="text-base font-medium text-gray-900">Document Status</h3>

                    </div>

                    <div class="p-4">

                        <!-- Document Status Chart -->

                        <div class="p-4">

                            <div class="relative w-64 h-64 mx-auto">

                                <canvas id="myChart" width="256" height="256"></canvas>

                            </div>
                            <script>
                                var ctx = document.getElementById('myChart').getContext('2d');

                                var myChart = new Chart(ctx, {

                                    type: 'pie',

                                    data: {

                                        labels: ['Available', 'Borrowed', 'Archived'],

                                        datasets: [{

                                            data: [<?= $stats['availableCount'] ?? 40 ?>, <?= $stats['borrowedCount'] ?? 30 ?>, <?= $stats['archivedCount'] ?? 30 ?>],

                                            backgroundColor: [

                                                'rgba(34, 197, 94, 0.7)',

                                                'rgba(59, 130, 246, 0.7)',

                                                'rgba(220, 38, 38, 0.7)'

                                            ],

                                            borderColor: [

                                                'rgba(34, 197, 94, 0.7)',

                                                'rgba(59, 130, 246, 0.7)',

                                                'rgba(220, 38, 38, 0.7)'

                                            ],

                                            borderWidth: 1

                                        }]

                                    },

                                    options: {

                                        responsive: true,

                                        maintainAspectRatio: false,

                                        plugins: {

                                            legend: {

                                                position: 'bottom',

                                                labels: {

                                                    fontColor: 'black',

                                                    fontSize: 14,

                                                    padding: 20

                                                }

                                            }

                                        }

                                    }

                                });
                            </script>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <?= $this->endSection() ?>