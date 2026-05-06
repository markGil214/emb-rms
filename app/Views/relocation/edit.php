<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Relocation Request</h1>
        <p class="text-gray-600">Modify the relocation request details</p>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->has('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <p class="text-sm text-red-700 mt-1"><?= session('error') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                    <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="<?= route_to('relocations.update', $relocation['relocation_id']) ?>" id="relocationForm" class="p-6 space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="folder_id" value="<?= $relocation['folder_id'] ?>">

            <!-- Step 1: Folder to Relocate -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">1</span>
                    Folder to Relocate
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">File Code:</span>
                        <p class="text-lg font-bold text-gray-900"><?= $folder['file_code'] ?></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Company:</span>
                        <p class="text-lg font-bold text-gray-900"><?= $folder['company_name'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Current Location -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-gray-100 text-gray-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">2</span>
                    Current Location (Readonly)
                </h3>
                <div class="space-y-3">
                    <label for="from_location" class="block text-sm font-medium text-gray-700">Current Location:</label>
                    <input type="text" id="from_location" readonly 
                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg"
                           value="<?php 
                                $currentLoc = null;
                                foreach ($locations as $loc) {
                                    if ($loc['location_id'] == $relocation['from_location_id']) {
                                        $currentLoc = $loc;
                                        break;
                                    }
                                }
                                if ($currentLoc) {
                                    echo 'Rack ' . $currentLoc['rack'] . ' - Shelf ' . $currentLoc['shelf'];
                                } else {
                                    echo 'Unknown';
                                }
                            ?>">
                </div>
            </div>

            <!-- Step 3: Select New Location -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">3</span>
                    Select New Location
                </h3>
                <div class="space-y-3">
                    <label for="to_location_id" class="block text-sm font-medium text-gray-700">New Location:</label>
                    <select name="to_location_id" id="to_location_id" required
                            class="w-full px-3 py-2 border bg-gray-100 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Choose Location --</option>
                        <?php 
                            $grouped = [];
                            foreach ($locations as $loc) {
                                $rack = $loc['rack'] ?? 'Unknown';
                                if (!isset($grouped[$rack])) {
                                    $grouped[$rack] = [];
                                }
                                $grouped[$rack][] = $loc;
                            }
                        ?>
                        <?php foreach ($grouped as $rack => $shelves): ?>
                            <optgroup label="Rack <?= $rack ?>">
                                <?php foreach ($shelves as $location): ?>
                                    <option value="<?= $location['location_id'] ?>" 
                                        <?= $location['location_id'] == $relocation['to_location_id'] ? 'selected' : '' ?> 
                                        <?= $location['location_id'] == $relocation['from_location_id'] ? 'disabled' : '' ?>>
                                        Shelf <?= $location['shelf'] ?><?= $location['location_id'] == $relocation['from_location_id'] ? ' (Current Location)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Step 4: Reason -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-gray-100 text-gray-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">4</span>
                    Reason (Optional)
                </h3>
                <div class="space-y-3">
                    <label for="reason" class="block text-sm font-medium text-gray-700">Why are you relocating this folder?</label>
                    <textarea name="reason" id="reason" rows="4" 
                              class="w-full px-3 py-2 border bg-gray-100 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Enter reason..."><?= $relocation['reason'] ?></textarea>
                    <p class="text-sm text-gray-500">Optional but recommended for audit trail and accountability</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= route_to('relocations.index') ?>" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span>Update Relocation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
