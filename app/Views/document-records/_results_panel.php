<?php
/**
 * Document records results panel: count/sort bar, limiter buttons,
 * table (or empty state), and pagination. Rendered both as part of the
 * full index page and, standalone, as the AJAX response for live search
 * and pagination so neither has to duplicate this markup.
 *
 * @var array $folders
 * @var int   $totalFolders
 * @var int   $currentPage
 * @var int   $totalPages
 * @var array $filters
 */

$search = $filters['search'] ?? '';
$statusFilter = $filters['status'] ?? '';
$folderTypeFilter = $filters['folder_type'] ?? '';
$categoryFilter = $filters['category'] ?? '';
$sortFilter = $filters['sort'] ?? 'company_asc';

$sortOptions = [
    'company_asc' => 'Alphabetical (A-Z)',
    'company_desc' => 'Alphabetical (Z-A)',
    'newest' => 'Newest Created',
];

$formatLocation = static function (array $folder): string {
    $rack = trim((string) ($folder['rack'] ?? ''));
    $shelf = trim((string) ($folder['shelf'] ?? ''));

    if ($rack !== '' || $shelf !== '') {
        $parts = [];

        if ($rack !== '') {
            $parts[] = 'Rack ' . $rack;
        }

        if ($shelf !== '') {
            $parts[] = 'Shelf ' . $shelf;
        }

        return implode(' - ', $parts);
    }

    return (string) ($folder['location_code'] ?? '--');
};

$formatDate = static function ($dateString, string $fallback = '--'): string {
    if (!$dateString) {
        return $fallback;
    }

    $trimmed = trim((string) $dateString);

    if ($trimmed === '' || $trimmed === '1970-01-01' || $trimmed === '1970-01-01 00:00:00') {
        return $fallback;
    }

    $timestamp = strtotime($trimmed);

    if ($timestamp === false) {
        return $fallback;
    }

    return date('M d, Y', $timestamp);
};

$pageUrl = static function (int $page) use ($filters): string {
    $params = [
        'search' => $filters['search'] ?? '',
        'status' => $filters['status'] ?? '',
        'folder_type' => $filters['folder_type'] ?? '',
        'category' => $filters['category'] ?? '',
        'sort' => $filters['sort'] ?? 'company_asc',
        'limit' => $filters['limit'] ?? 'all',
        'page' => $page,
    ];

    $params = array_filter($params, static function ($value) {
        return $value !== '' && $value !== null;
    });

    return base_url('document-records') . '?' . http_build_query($params);
};

$hasActiveFilters = $search !== '' || $statusFilter !== '' || $folderTypeFilter !== '' || $sortFilter !== 'company_asc' || $categoryFilter !== '';
?>

<div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-4 py-3">
    <div class="text-sm text-gray-700">
        Showing <span id="folderVisibleCount"><?= esc((string) count($folders)) ?></span> of <span id="folderTotalCount"><?= esc((string) $totalFolders) ?></span> entries
    </div>
    <div class="flex items-center gap-4">
        <div class="text-sm text-gray-500">
            Active sort: <span id="folderActiveSortLabel"><?= esc($sortOptions[$sortFilter] ?? 'Alphabetical (A-Z)') ?></span>
        </div>
        <!-- Table Limiter Buttons -->
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Show:</span>
            <div class="flex gap-1" id="tableLimiterButtons">
                <button type="button" class="limiter-btn px-3 py-1 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 hover:border-gray-400 transition-all duration-200" data-limit="25">25</button>
                <button type="button" class="limiter-btn px-3 py-1 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 hover:border-gray-400 transition-all duration-200" data-limit="100">100</button>
                <button type="button" class="limiter-btn px-3 py-1 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 hover:border-gray-400 transition-all duration-200" data-limit="250">250</button>
                <button type="button" class="limiter-btn px-3 py-1 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 hover:border-gray-400 transition-all duration-200" data-limit="all">All</button>
            </div>
        </div>
    </div>
</div>

<?php if (empty($folders)): ?>

    <div class="p-12 text-center">
        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>

        <h3 class="mb-2 text-lg font-medium text-gray-900">
            <?= $hasActiveFilters ? 'No matching folders found' : 'No folders found' ?>
        </h3>

        <p class="mb-4 text-gray-600">
            <?= $hasActiveFilters ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first folder' ?>
        </p>

        <?php if (can('create_document_record') && !$hasActiveFilters): ?>
            <a href="<?= route_to('records.create') ?>"
                class="inline-flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors duration-200 hover:bg-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Folder</span>
            </a>
        <?php endif; ?>
    </div>

<?php else: ?>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">File Code</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Location</th>
                    <th class="w-80 px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Company</th>
                    <th class="w-28 px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Borrowed / Due Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date Returned</th>
                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody id="folderTableBody" class="divide-y divide-gray-200 bg-white">
                <?php foreach ($folders as $folder): ?>
                    <?php
                    $folderStatus = $folder['status'] ?? 'Unknown';

                    switch ($folderStatus) {
                        case 'Available':
                            $statusClass = 'bg-green-100 text-green-800';
                            break;
                        case 'Borrowed':
                            $statusClass = 'bg-red-100 text-red-800';
                            break;
                        case 'Archived':
                            $statusClass = 'bg-gray-100 text-gray-800';
                            break;
                        case 'Archival':
                            $statusClass = 'bg-orange-100 text-orange-800';
                            break;
                        case 'Pending':
                            $statusClass = 'bg-blue-100 text-blue-800';
                            break;
                        case 'Pending Update':
                            $statusClass = 'bg-indigo-100 text-indigo-800';
                            break;
                        case 'Pending Archive':
                            $statusClass = 'bg-orange-100 text-orange-800';
                            break;
                        case 'Declined':
                            $statusClass = 'bg-red-100 text-red-800';
                            break;
                        default:
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                            break;
                    }

                    $categoryLabel = trim((string) ($folder['folder_category'] ?? '--'));
                    ?>
                    <tr class="folder-row bg-white transition-colors duration-150 hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?= $statusClass ?>">
                                <?= esc($folderStatus) ?>
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold text-gray-900"><?= esc($folder['file_code'] ?? '--') ?></td>
                        <td class="whitespace-nowrap px-4 py-2 text-sm font-medium text-blue-600"><?= esc($formatLocation($folder)) ?></td>
                        <td class="max-w-[20rem] truncate px-4 py-2 text-sm text-gray-900" title="<?= esc($folder['company_name'] ?? 'Unknown Folder', 'attr') ?>"><?= esc($folder['company_name'] ?? 'Unknown Folder') ?></td>
                        <td class="max-w-[7rem] truncate px-4 py-2 text-sm text-gray-700" title="<?= esc($categoryLabel, 'attr') ?>"><?= esc($categoryLabel) ?></td>
                        <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900"><?= esc($folder['folder_type'] ?? '--') ?></td>
                        <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">
                            <div>Borrowed Date: <?= esc($formatDate($folder['borrowed_date'] ?? null, 'Not yet borrowed')) ?></div>
                            <div>Due Date: <?= esc($formatDate($folder['due_date'] ?? null, 'Not yet borrowed')) ?></div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">
                            <?php if (! empty($folder['return_date'])): ?>
                                <?= esc($formatDate($folder['return_date'], '--')) ?>
                            <?php elseif ($folderStatus === 'Borrowed'): ?>
                                Not yet returned
                            <?php else: ?>
                                No return record
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-sm font-medium">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <?= view('components/button', [
                                    'label' => 'View',
                                    'type' => 'link',
                                    'url' => route_to('records.show', $folder['folder_id']),
                                    'style' => 'info',
                                    'title' => 'View Details',
                                    'confirm' => null,
                                    'class' => 'px-3 py-2'
                                ]) ?>

                                <?php if ($folderStatus !== 'Pending Archive'): ?>
                                    <?= view('components/button', [
                                        'label' => 'Edit',
                                        'type' => 'link',
                                        'url' => route_to('records.edit', $folder['folder_id']),
                                        'style' => 'info',
                                        'title' => 'Edit Folder',
                                        'confirm' => null,
                                        'class' => 'px-3 py-2'
                                    ]) ?>
                                <?php endif; ?>

                                <?php if (can('approve_folder_creation') && ($folderStatus === 'Pending' || $folderStatus === 'Pending Update')): ?>
                                    <?php $isPendingUpdate = $folderStatus === 'Pending Update'; ?>
                                    <?php if ($isPendingUpdate): ?>
                                        <form action="<?= route_to('records.approve', $folder['folder_id']) ?>" method="POST" style="display:inline;" class="update-diff-form" data-diff-url="<?= route_to('records.editRequestDiff', $folder['folder_id']) ?>" data-diff-action="approve">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="disposal-action disposal-action--primary px-3 py-2">Approve Update</button>
                                        </form>

                                        <form action="<?= route_to('records.decline', $folder['folder_id']) ?>" method="POST" style="display:inline;" class="update-diff-form" data-diff-url="<?= route_to('records.editRequestDiff', $folder['folder_id']) ?>" data-diff-action="decline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="disposal-action disposal-action--danger px-3 py-2">Decline Update</button>
                                        </form>
                                    <?php else: ?>
                                        <?= view('components/button', [
                                            'label' => 'Approve Creation',
                                            'type' => 'submit',
                                            'style' => 'primary',
                                            'action' => route_to('records.approve', $folder['folder_id']),
                                            'confirm' => 'Are you sure you want to approve this folder creation?',
                                            'class' => 'px-3 py-2'
                                        ]) ?>
                                        <?= view('components/button', [
                                            'label' => 'Decline Creation',
                                            'type' => 'submit',
                                            'style' => 'danger',
                                            'action' => route_to('records.decline', $folder['folder_id']),
                                            'confirm' => 'Are you sure you want to decline this folder creation?',
                                            'class' => 'px-3 py-2'
                                        ]) ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (can('approve_archive') && $folderStatus !== 'Archived' && $folderStatus !== 'Archival' && $folderStatus !== 'Borrowed' && $folderStatus !== 'Disposed' && $folderStatus !== 'Pending' && $folderStatus !== 'Pending Update' && $folderStatus !== 'Pending Archive'): ?>
                                    <?= view('components/button', [
                                        'label' => 'Archive',
                                        'type' => 'submit',
                                        'style' => 'secondary',
                                        'class' => 'text-green-700 px-3 py-2',
                                        'action' => route_to('records.archive', $folder['folder_id']),
                                        'confirm' => 'Submit this folder for archive approval?'
                                    ]) ?>
                                <?php elseif ($folderStatus === 'Archived'): ?>
                                    <span class="text-gray-400 font-medium px-2">Archived</span>
                                <?php elseif ($folderStatus === 'Borrowed'): ?>
                                    <span class="text-red-600 font-medium px-2">Borrowed</span>
                                <?php elseif ($folderStatus === 'Archival'): ?>
                                    <span class="text-orange-600 font-medium px-2">Archival</span>
                                <?php elseif ($folderStatus === 'Pending Archive'): ?>
                                    <span class="text-orange-600 font-medium px-2">Archive pending</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="flex flex-col items-center justify-end gap-4 border-t border-gray-200 px-4 py-3 sm:flex-row">
            <div class="flex items-center gap-1" id="paginationNumbers">
                <?php
                $windowSize = 5;
                $startPage = max(1, min($currentPage - 2, $totalPages - $windowSize + 1));
                $endPage = min($totalPages, $startPage + $windowSize - 1);
                ?>

                <?php if ($currentPage > 1): ?>
                    <a href="<?= esc($pageUrl($currentPage - 1), 'attr') ?>" data-page-link class="px-3 py-1 text-sm border border-gray-300 rounded-l hover:bg-gray-50 transition-colors">←prev</a>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?= esc($pageUrl($i), 'attr') ?>" data-page-link class="px-3 py-1 text-sm border transition-colors <?= $i === $currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50' ?>"><?= $i ?></a>
                    <?php if ($i < $endPage): ?>
                        <span class="text-gray-500">|</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= esc($pageUrl($currentPage + 1), 'attr') ?>" data-page-link class="px-3 py-1 text-sm border border-gray-300 rounded-r hover:bg-gray-50 transition-colors">next→</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

<?php endif; ?>
