<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="<?= route_to('relocations.index') ?>" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Relocation Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Relocation Details</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Relocation ID -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Relocation ID</h3>
                        <p class="text-lg font-semibold text-gray-900 bg-gray-50 border border-blue-200 rounded-lg p-4">#<?= $relocation['relocation_id'] ?></p>
                    </div>

                    <!-- Folder Information -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Folder Information</h3>
                        <div class="bg-gray-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-lg font-bold text-gray-900"><?= $folder['file_code'] ?></p>
                            <p class="text-sm text-gray-900"><?= $folder['company_name'] ?></p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Status</h3>
                        <?php
                            $statusMap = [
                                'Pending' => ['Needs Approval', 'bg-yellow-100', 'text-yellow-800'],
                                'Approved' => ['Approved', 'bg-blue-100', 'text-blue-800'],
                                'In Progress' => ['Moving', 'bg-orange-100', 'text-orange-800'],
                                'Completed' => ['Completed', 'bg-green-100', 'text-green-800'],
                                'Declined' => ['Declined', 'bg-red-100', 'text-red-800']
                            ];
                            $displayStatus = $statusMap[$relocation['status']] ?? [$relocation['status'], 'bg-gray-100', 'text-gray-800'];
                        ?>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?= $displayStatus[1] ?> <?= $displayStatus[2] ?>">
                            <?= $displayStatus[0] ?>
                        </span>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Current Location -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Current Location</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-lg font-semibold text-gray-900">
                                <?= ($currentLocation && $currentLocation['cabinet']) ? 'Cabinet ' . $currentLocation['cabinet'] . ' - Rack ' . $currentLocation['rack'] : 'N/A' ?>
                            </p>
                        </div>
                    </div>

                    <!-- New Location -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">New Location</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-lg font-semibold text-gray-900">
                                <?= ($newLocation && $newLocation['cabinet']) ? 'Cabinet ' . $newLocation['cabinet'] . ' - Rack ' . $newLocation['rack'] : 'N/A' ?>
                            </p>
                        </div>
                    </div>

                    <!-- Requested Date -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Requested Date</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-lg font-semibold text-gray-900">
                                <?= date('M d, Y g:i A', strtotime($relocation['requested_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason Section -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Reason for Relocation</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <p class="text-gray-900 leading-relaxed">
                        <?= $relocation['reason'] ? nl2br(htmlspecialchars($relocation['reason'])) : '<span class="text-gray-500 italic">No reason provided</span>' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    </div>

<?= $this->endSection() ?>
