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

<!-- Archive Details Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Archive Information</h2>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Archive ID</h3>
                    <p class="text-gray-900 font-medium"><?= esc($archive['archive_id']) ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Folder Information</h3>
                    <p class="text-gray-900">
                        <span class="font-medium"><?= esc($folder['file_code']) ?></span> - <?= esc($folder['company_name']) ?>
                    </p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Archive Location</h3>
                    <p class="text-gray-900"><?= esc($archive['archive_location'] ?? '-') ?></p>
                </div>
                
                <?php if ($archive['storage_box']): ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Storage Box</h3>
                    <p class="text-gray-900"><?= esc($archive['storage_box']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Archive Date</h3>
                    <p class="text-gray-900">
                        <?= isset($archive['archive_date']) ? date('M d, Y g:i A', strtotime($archive['archive_date'])) : '-' ?>
                    </p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Archived By</h3>
                    <p class="text-gray-900"><?= esc($archive['archived_by']) ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Archive Reason</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">
                        <?= isset($archive['archive_reason']) ? nl2br($archive['archive_reason']) : '-' ?>
                    </p>
                </div>
                
                <?php if ($archive['notes']): ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Notes</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">
                        <?= nl2br($archive['notes']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
