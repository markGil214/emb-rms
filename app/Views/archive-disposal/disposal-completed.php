<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Back Button -->
<div class="mb-6 text-left">
    <a href="<?= route_to('archive.index') ?>" 
       class="inline-flex items-center px-3 py-2 rounded hover:bg-gray-200 transition-colors group">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span class="ml-2 text-sm">Back to Archive Management</span>
    </a>
</div>

<!-- Disposed Records Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">File Disposal History</h2>
    </div>
    
    <?php if (empty($completed)): ?>
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No disposed records</h3>
            <p class="text-gray-600">No file disposal records have been completed yet</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disposed Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($completed as $disposal): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= esc($disposal['request_id']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold <?= ($disposal['request_type'] ?? '') === 'File' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                    <?= esc($disposal['request_type'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?= esc($disposal['subject'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($disposal['method'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($disposal['status'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= !empty($disposal['requested_at']) ? date('M d, Y', strtotime($disposal['requested_at'])) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= !empty($disposal['completed_at']) ? date('M d, Y', strtotime($disposal['completed_at'])) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (!empty($disposal['view_route']) && !empty($disposal['route_id'])): ?>
                                    <a href="<?= route_to($disposal['view_route'], $disposal['route_id']) ?>"
                                       class="text-blue-600 hover:text-blue-900">View Details</a>
                                <?php else: ?>
                                    <span class="text-gray-500">No details</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
