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

    <form method="POST" action="<?= route_to('disposal.store') ?>">
        <?= csrf_field() ?>

        <div class="mb-6">
            <label for="archive_id" class="block text-sm font-medium text-gray-700 mb-2">
                Select Archived Record <span class="text-red-500">*</span>
            </label>
            <select name="archive_id" id="archive_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Choose Archive Record --</option>
                <?php foreach ($archived as $archive): ?>
                    <option value="<?= $archive['archive_id'] ?>">
                        #<?= esc($archive['archive_id']) ?> - <?= esc($archive['file_code'] ?? ('Folder ' . $archive['folder_id'])) ?> - <?= esc($archive['company_name'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="disposal_method" class="block text-sm font-medium text-gray-700 mb-2">
                Disposal Method <span class="text-red-500">*</span>
            </label>
            <select name="disposal_method" id="disposal_method" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Choose Method --</option>
                <?php foreach ($disposalMethods as $method): ?>
                    <option value="<?= $method ?>"><?= esc($method) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="compliance_reference" class="block text-sm font-medium text-gray-700 mb-2">
                Authorization Reference (Optional)
            </label>
            <input type="text" name="compliance_reference" id="compliance_reference" 
                   placeholder="e.g., AUTH-2026-001"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                Reason for Disposal <span class="text-red-500">*</span>
            </label>
            <textarea name="reason" id="reason" rows="4" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter the reason for disposal request..."></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" 
                    class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                Request Disposal
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
