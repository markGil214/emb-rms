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

<!-- Alert Message -->
<div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-6">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">Action Needed:</span>
    </div>
    <p class="mt-2">The following file disposal requests are waiting for approval.</p>
</div>

<!-- Pending Work Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">File Disposal Approvals</h2>
    </div>
    
    <?php if (empty($pending)): ?>
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V9a2 2 0 00-2-2H9z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No pending requests</h3>
            <p class="text-gray-600">All requests have been processed</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pending as $disposal): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= esc($disposal['request_id']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold <?= strpos((string) ($disposal['request_type'] ?? ''), 'File') !== false ? 'bg-blue-100 text-blue-700' : (($disposal['request_type'] ?? '') === 'Restoration' ? 'bg-green-100 text-green-700' : (($disposal['request_type'] ?? '') === 'Archive Approval' ? 'bg-orange-100 text-orange-700' : 'bg-purple-100 text-purple-700')) ?>">
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
                                <?php
                                    $status = (string) ($disposal['status'] ?? 'Pending Disposal');
                                    $statusClass = $status === 'Approved for Disposal'
                                        ? 'bg-green-100 text-green-700'
                                        : ($status === 'Pending Archive'
                                            ? 'bg-orange-100 text-orange-700'
                                            : ($status === 'Restoration Requested'
                                            ? 'bg-yellow-100 text-yellow-700'
                                            : 'bg-orange-100 text-orange-700'));
                                ?>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold <?= esc($statusClass) ?>">
                                    <?= esc($status) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= !empty($disposal['requested_at']) ? date('M d, Y', strtotime($disposal['requested_at'])) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($disposal['requested_by'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <?php if (!empty($disposal['view_route']) && (!empty($disposal['view_id']) || !empty($disposal['route_id']))): ?>
                                        <a href="<?= route_to($disposal['view_route'], $disposal['view_id'] ?? $disposal['route_id']) ?>"
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                    <?php endif; ?>
                                    <?php if (!empty($disposal['approve_route']) && !empty($disposal['route_id'])): ?>
                                        <form method="POST" action="<?= route_to($disposal['approve_route'], $disposal['route_id']) ?>"
                                            class="inline" data-confirm-message="<?= esc($disposal['confirm_message'] ?? 'Approve this request?', 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-green-600 hover:text-green-900 font-medium"><?= esc($disposal['action_label'] ?? 'Approve') ?></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-500"><?= esc($disposal['fallback_action_label'] ?? 'Pending review') ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($disposal['decline_route']) && !empty($disposal['route_id'])): ?>
                                        <form method="POST" action="<?= route_to($disposal['decline_route'], $disposal['route_id']) ?>"
                                            class="inline" data-confirm-message="<?= esc($disposal['decline_confirm_message'] ?? 'Decline this request?', 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900 font-medium"><?= esc($disposal['decline_label'] ?? 'Decline') ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
