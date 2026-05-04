<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
    $status = (string) ($disposal['status'] ?? 'Pending Disposal');
    $statusClasses = [
        'Pending Disposal' => 'bg-orange-100 text-orange-700',
        'Approved for Disposal' => 'bg-green-100 text-green-700',
        'Disposed' => 'bg-gray-200 text-gray-700',
    ];
    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';

    $formatDate = static function ($value, string $format = 'M d, Y g:i A'): string {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date($format, $timestamp) : '-';
    };
?>

<div class="mb-6 text-left">
    <a href="<?= route_to('archive.index') ?>" class="inline-flex items-center px-3 py-2 rounded hover:bg-gray-200 transition-colors group">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span class="ml-2 text-sm">Back to Archive Management</span>
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-900">Disposal Information</h2>
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= esc($statusClass) ?>">
            <?= esc($status) ?>
        </span>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Disposal ID</h3>
                    <p class="text-gray-900 font-medium"><?= esc($disposal['disposal_id']) ?></p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Folder Information</h3>
                    <p class="text-gray-900">
                        <span class="font-medium"><?= esc($folder['file_code'] ?? '-') ?></span> - <?= esc($folder['company_name'] ?? '-') ?>
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Disposal Method</h3>
                    <p class="text-gray-900"><?= esc($disposal['disposal_method'] ?? '-') ?></p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Certificate / Reference</h3>
                    <p class="text-gray-900 whitespace-pre-wrap"><?= !empty($disposal['compliance_reference']) ? nl2br(esc($disposal['compliance_reference'])) : '-' ?></p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Requested Date</h3>
                    <p class="text-gray-900"><?= esc($formatDate($disposal['created_at'] ?? null)) ?></p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Requested By</h3>
                    <p class="text-gray-900"><?= esc($disposal['requested_by_name'] ?? $disposal['requested_by'] ?? '-') ?></p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Approved By</h3>
                    <p class="text-gray-900"><?= esc($disposal['approved_by_name'] ?? $disposal['approved_by'] ?? '-') ?></p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Disposal Date</h3>
                    <p class="text-gray-900"><?= esc($formatDate($disposal['disposal_date'] ?? null, 'M d, Y')) ?></p>
                </div>

                <?php if ($status === 'Approved for Disposal' && can('approve_disposal')): ?>
                    <form action="<?= route_to('disposal.complete', $disposal['disposal_id']) ?>" method="POST" data-confirm-message="Mark this archive disposal as completed?">
                        <?= csrf_field() ?>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            Mark Disposed
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
