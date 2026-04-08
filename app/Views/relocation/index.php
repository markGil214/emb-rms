<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <p class="text-gray-600"><?= $subtitle ?></p>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <!-- Stats removed -->
            </div>
            
            <?php if (can('request_relocation')): ?>
            <a href="<?= route_to('relocations.create') ?>" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Request Relocation</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Relocations Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Relocation Requests</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Folder Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Folder / Company
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Current Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            New Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Requested On
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($relocations)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No relocation records</p>
                                    <p class="text-sm">No relocation requests have been created yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($relocations as $index => $rel): ?>
                            <?php
                                $statusMap = [
                                    'Pending' => ['Needs Approval', 'bg-yellow-100', 'text-yellow-800'],
                                    'Approved' => ['Approved', 'bg-blue-100', 'text-blue-800'],
                                    'In Progress' => ['Moving', 'bg-orange-100', 'text-orange-800'],
                                    'Completed' => ['Completed', 'bg-green-100', 'text-green-800'],
                                    'Declined' => ['Declined', 'bg-red-100', 'text-red-800']
                                ];
                                $displayStatus = $statusMap[$rel['status']] ?? [$rel['status'], 'bg-gray-100', 'text-gray-800'];
                                $isEven = $index % 2 === 0;
                            ?>
                            <tr class="<?= $isEven ? 'bg-gray-50' : 'bg-white' ?> hover:bg-gray-100">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= $rel['file_code'] ?? 'N/A' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rel['company_name'] ?? 'Unknown Folder' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rel['current_location_display'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rel['new_location_display'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('M d, Y', strtotime($rel['requested_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $displayStatus[1] ?> <?= $displayStatus[2] ?>">
                                        <?= $displayStatus[0] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="<?= route_to('relocations.show', $rel['relocation_id']) ?>" 
                                           class="text-blue-600 hover:text-blue-900 inline-flex items-center space-x-1"
                                           title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span>View</span>
                                        </a>
                                        
                                        <?php if ($rel['status'] === 'Pending'): ?>
                                            <?php if (can('initiate_relocation')): ?>
                                            <a href="<?= route_to('relocations.edit', $rel['relocation_id']) ?>" 
                                               class="text-green-600 hover:text-green-900 inline-flex items-center space-x-1"
                                               title="Edit Request">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                <span>Edit</span>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (can('approve_relocation')): ?>
                                            <form action="<?= route_to('relocations.approve', $rel['relocation_id']) ?>" method="post" class="inline">
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-900 inline-flex items-center space-x-1"
                                                        title="Approve Request">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span>Approve</span>
                                                </button>
                                            </form>
                                            
                                            <form action="<?= route_to('relocations.decline', $rel['relocation_id']) ?>" method="post" class="inline">
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 inline-flex items-center space-x-1"
                                                        title="Decline Request">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <span>Decline</span>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
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
