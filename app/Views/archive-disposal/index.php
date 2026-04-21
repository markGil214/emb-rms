<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        <?= esc(session('success')) ?>
    </div>
<?php endif; ?>

<?php if (session('error')): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <?= esc(session('error')) ?>
    </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Archived</p>
                <p class="text-2xl font-bold text-gray-900"><?= $totalArchived ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg mr-4">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Disposal Pending</p>
                <p class="text-2xl font-bold text-gray-900"><?= $totalDisposalPending ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg mr-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Disposal Approved</p>
                <p class="text-2xl font-bold text-gray-900"><?= $totalDisposalApproved ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Recently Archived with Quick Actions -->
<div class="flex flex-col lg:flex-row gap-6 mb-8">
    <!-- Recently Archived Section -->
    <div class="flex-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Recently Archived</h2>
            </div>
            
            <?php if (empty($archived)): ?>
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No archived documents</h3>
                    <p class="text-gray-600">Start by archiving your first document</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder Subtype</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach (array_slice($archived, 0, 5) as $archive): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= esc($archive['file_code']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= esc($archive['company_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= esc($archive['folder_type']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= !empty($archive['category_name']) ? esc($archive['category_name']) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?= route_to('records.show', $archive['folder_id']) ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-4">View</a>
                                        <form action="<?= route_to('archive.restore', $archive['folder_id']) ?>" method="POST" 
                                              class="inline" onsubmit="return confirm('Restore this folder to Available status?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900 font-medium">Restore</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions Sidebar -->
    <div class="lg:w-80">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="<?= route_to('archive.create') ?>" 
                   class="flex items-center w-full p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Archive Document</span>
                </a>

                <a href="<?= route_to('disposal.create') ?>" 
                   class="flex items-center w-full p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Request Disposal</span>
                </a>

                <a href="<?= route_to('archive.search') ?>" 
                   class="flex items-center w-full p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Search Archives</span>
                </a>

                <a href="<?= route_to('disposal.pending') ?>" 
                   class="flex items-center w-full p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">View Pending Disposal</span>
                </a>

                <a href="<?= route_to('disposal.completed') ?>" 
                   class="flex items-center w-full p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">View Completed Disposal</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
