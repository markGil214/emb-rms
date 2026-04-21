<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-6 text-left">
    <a href="<?= route_to('archive.index') ?>" 
       class="inline-flex items-center px-3 py-2 rounded hover:bg-gray-200 transition-colors group">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span class="ml-2 text-sm">Back to Archive Management</span>
    </a>
</div>
<!-- Disposal Details Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Disposal Information</h2>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Disposal ID</h3>
                    <p class="text-gray-900 font-medium"><?= esc($disposal['disposal_id']) ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Folder Information</h3>
                    <p class="text-gray-900">
                        <span class="font-medium"><?= esc($folder['file_code']) ?></span> - <?= esc($folder['company_name']) ?>
                    </p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Disposal Method</h3>
                    <p class="text-gray-900"><?= esc($disposal['disposal_method']) ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Reason</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">
                        <?= nl2br($disposal['reason'] ?? '-') ?>
                    </p>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Status</h3>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                              <?php 
                              $status = $disposal['status'] ?? (($disposal['disposal_date'] ?? null) ? 'Approved' : 'Pending');
                              if ($status === 'Approved') {
                                  echo 'bg-green-100 text-green-800';
                              } else {
                                  echo 'bg-yellow-100 text-yellow-800';
                              }
                              ?>">
                            <?php if ($status === 'Approved'): ?>
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-8 8 0 018 0zm3.707-9.293a1 1 0 00-1.414 1.414L8.586 7.707a1 1 0 00-1.414-1.414L7.293 9.293a1 1 0 001.414 1.414l7.293 7.293a1 1 0 001.414 1.414L10 16.414a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-8 8 0 018 0zM8 7a8 8 0 018 0v-1a1 1 0 011-1h2a1 1 0 011 1v1a1 1 0 011-1H8z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            <?= esc($status) ?>
                        </span>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Requested Date</h3>
                    <p class="text-gray-900">
                        <?= date('M d, Y g:i A', strtotime($disposal['created_at'])) ?>
                    </p>
                </div>
                
                <?php if ($disposal['disposal_date']): ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Completed Date</h3>
                    <p class="text-gray-900">
                        <?= date('M d, Y g:i A', strtotime($disposal['disposal_date'])) ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($disposal['compliance_reference'])): ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Compliance Reference</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">
                        <?= nl2br($disposal['compliance_reference']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
