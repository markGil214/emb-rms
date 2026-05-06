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

<!-- Search Form -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 w-full">
    <form method="GET" action="<?= route_to('archive.search') ?>">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="q" class="block text-sm font-medium text-gray-700 mb-2">
                    Search Archives
                </label>
                <div class="relative">
                    <input type="text" name="q" id="q" 
                           value="<?= esc($query) ?>" 
                           placeholder="File code, company name, location..."
                           class="w-full md:w-96 px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    Search
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Search Results -->
<?php if (!empty($query)): ?>
    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
        Found <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($query) ?>"
    </div>
<?php endif; ?>

<!-- Results Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Search Results</h2>
    </div>
    
    <?php if (empty($results)): ?>
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                <?= empty($query) ? 'Start Your Search' : 'No Results Found' ?>
            </h3>
            <p class="text-gray-600">
                <?= empty($query) ? 'Enter a search term to find archived documents' : 'Try adjusting your search criteria' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archive ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Storage Box</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archive Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($results as $archive): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Archive <?= esc($archive['archive_id']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($archive['folder_id'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($archive['archive_location'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($archive['storage_box'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= isset($archive['archive_date']) ? date('M d, Y', strtotime($archive['archive_date'])) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="<?= route_to('archive.show', $archive['archive_id']) ?>" 
                                   class="text-blue-600 hover:text-blue-900">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
