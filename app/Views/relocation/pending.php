<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pending Relocations</h1>
    </div>

    <!-- Pending Relocations Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($pending)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No pending relocations found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending as $rel): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?= $rel['file_code'] ?? 'N/A' ?></div>
                                    <div class="text-sm text-gray-500"><?= $rel['company_name'] ?? 'Unknown' ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= $rel['current_location_display'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= $rel['new_location_display'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($rel['requested_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= route_to('relocations.show', $rel['relocation_id']) ?>" 
                                       class="text-blue-600 hover:text-blue-900 text-sm mr-3">View</a>
                                    <form action="<?= route_to('relocations.approve', $rel['relocation_id']) ?>" method="post" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
