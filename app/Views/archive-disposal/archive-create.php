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

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 w-full">
    <?php if (session()->has('errors')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <h4 class="font-semibold mb-2">Please fix the following errors:</h4>
            <ul class="list-disc list-inside">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= route_to('archive.store') ?>">
        <?= csrf_field() ?>

        <div class="mb-6">
            <label for="folder_id" class="block text-sm font-medium text-gray-700 mb-2">
                Select Folder <span class="text-red-500">*</span>
            </label>
            <select name="folder_id" id="folder_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Choose Folder --</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['folder_id'] ?>">
                        <?= esc($folder['file_code']) ?> - <?= esc($folder['company_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="archive_location_id" class="block text-sm font-medium text-gray-700 mb-2">
                Archive Location <span class="text-red-500">*</span>
            </label>
            <select name="archive_location_id" id="archive_location_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Choose Location --</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['location_id'] ?>">
                        Cabinet <?= esc($location['cabinet'] ?? '-') ?> - Shelf <?= esc($location['shelf'] ?? ($location['rack'] ?? '-')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="storage_box" class="block text-sm font-medium text-gray-700 mb-2">
                Storage Box (Optional)
            </label>
            <input type="text" name="storage_box" id="storage_box" 
                   placeholder="e.g., BOX-001"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="archive_reason" class="block text-sm font-medium text-gray-700 mb-2">
                Archive Reason <span class="text-red-500">*</span>
            </label>
            <textarea name="archive_reason" id="archive_reason" rows="4" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter the reason for archiving this document..."></textarea>
        </div>

        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                Notes (Optional)
            </label>
            <textarea name="notes" id="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Additional notes or comments..."></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                Archive Document
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
