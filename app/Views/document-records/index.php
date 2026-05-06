<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$search = $filters['search'] ?? '';
$statusFilter = $filters['status'] ?? '';
$folderTypeFilter = $filters['folder_type'] ?? '';
$categoryFilter = $filters['category'] ?? '';
$sortFilter = $filters['sort'] ?? 'company_asc';

$statusOptions = [
    'available' => 'Available',
    'borrowed' => 'Borrowed',
    'archived' => 'Archived',
    'disposed' => 'Disposed',
    'pending_archive' => 'Pending Archive',
];

$folderTypeOptions = [
    'PERMITS' => 'PERMIT',
    'ECC / CNC FILES' => 'ECC / CNC FILES',
    'IEE / EIS FILES' => 'IEE / EIS FILES',
];

$sortOptions = [
    'company_asc' => 'Alphabetical (A-Z)',
    'company_desc' => 'Alphabetical (Z-A)',
    'newest' => 'Newest Created',
];

$categories = $categories ?? [];
$categoryOptions = [];

foreach ($categories as $category) {
    $categoryName = trim((string) ($category['category_name'] ?? ''));
    if ($categoryName !== '') {
        $categoryOptions[$categoryName] = $categoryName;
    }
}
ksort($categoryOptions);

$formatLocation = static function (array $folder): string {
    $cabinet = trim((string) ($folder['cabinet'] ?? ''));
    $rack = trim((string) ($folder['rack'] ?? ''));
    $shelf = trim((string) ($folder['shelf'] ?? ''));

    $parts = [];
    if ($cabinet !== '') {
        $parts[] = 'Cabinets/Racks ' . $cabinet;
    }
    if ($rack !== '' && $rack !== $cabinet) {
        $parts[] = 'Rack ' . $rack;
    }
    if ($shelf !== '') {
        $parts[] = $shelf;
    }

    if (!empty($parts)) {
        return implode(' | ', $parts);
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

$folders = $folders ?? [];
$totalFolders = count($folders);
$locationOptions = [];

foreach ($folders as $folderRow) {
    $locationLabel = $formatLocation($folderRow);
    if ($locationLabel !== '' && $locationLabel !== '--') {
        $locationOptions[$locationLabel] = $locationLabel;
    }
}
ksort($locationOptions);
?>

<div class="mx-auto w-full max-w-[1600px] p-6 space-y-6">
    <section>
        <h1 class="mb-2 text-3xl font-bold text-gray-900">Document Records</h1>
        <p class="text-gray-600">Scan folders by status, category, and due date at a glance.</p>
    </section>

    <section>
        <form method="get" action="<?= base_url('document-records') ?>" id="folderFiltersForm" class="rounded-lg border border-gray-200 bg-white p-4" onsubmit="return false;">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <input type="text"
                               id="folderSearchInput"
                               name="search"
                               value="<?= esc($search) ?>"
                               placeholder="Search folders..."
                               class="w-full lg:w-72 rounded-lg border border-gray-300 py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <input type="hidden" id="sortFilterInput" name="sort" value="<?= esc($sortFilter) ?>">
                    <input type="hidden" id="statusFilterInput" name="status" value="<?= esc($statusFilter) ?>">
                    <input type="hidden" id="folderTypeFilterInput" name="folder_type" value="<?= esc($folderTypeFilter) ?>">
                    <input type="hidden" id="categoryFilterInput" name="category" value="<?= esc($categoryFilter) ?>">

                    <button type="button" id="openFiltersModal" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50 font-medium">
                        <svg class="inline-block h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filters
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <?php if (can('manage_racks')): ?>
                        <a href="<?= route_to('racks.index') ?>"
                           class="inline-flex items-center space-x-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path>
                            </svg>
                            <span class="hidden sm:inline">Manage Racks</span>
                            <span class="sm:hidden">Racks</span>
                        </a>
                    <?php endif; ?>

                    <?php if (can('manage_categories')): ?>
                        <a href="<?= route_to('categories.index') ?>"
                           class="inline-flex items-center space-x-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <span class="hidden sm:inline">Categories</span>
                            <span class="sm:hidden">Cats</span>
                        </a>
                    <?php endif; ?>

                    <?php if (can('create_document_record')): ?>
                        <?= view('components/button', [
                            'label' => '+ Create Folder',
                            'url' => route_to('records.create'),
                            'style' => 'info',
                            'class' => 'px-3 py-2'
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>

    <!-- Filters Modal -->
    <div id="filtersModal" class="fixed inset-0 hidden z-50 items-center justify-center bg-black bg-opacity-50 p-4 overflow-y-auto">
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl my-8">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Filters & Sort</h2>
                <button type="button" id="closeFiltersModal" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4 space-y-4">
                <!-- Status Filter -->
                <div>
                    <label for="modalStatusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="modalStatusFilter" class="modal-filter w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Folder Type Filter -->
                <div>
                    <label for="modalFolderTypeFilter" class="block text-sm font-medium text-gray-700 mb-2">Folder Type</label>
                    <select id="modalFolderTypeFilter" class="modal-filter w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <?php foreach ($folderTypeOptions as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $folderTypeFilter === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="modalCategoryFilter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="modalCategoryFilter" class="modal-filter w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categoryOptions as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $categoryFilter === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="sort-button modal-filter px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50" data-sort-value="company_asc" data-sort-label="Alphabetical (A-Z)">
                            A-Z (Alphabetical)
                        </button>
                        <button type="button" class="sort-button modal-filter px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50" data-sort-value="company_desc" data-sort-label="Alphabetical (Z-A)">
                            Z-A (Alphabetical)
                        </button>
                        <button type="button" class="sort-button modal-filter px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50" data-sort-value="newest" data-sort-label="Newest Created">
                            Newest Created
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4">
                <button type="button" id="resetFiltersButton" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                    Reset
                </button>
                <button type="button" id="applyFiltersButton" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors duration-200 hover:bg-blue-700">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <section>
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-4 py-3">
                <div class="text-sm text-gray-700">
                    Showing <span id="folderVisibleCount"><?= esc((string) $totalFolders) ?></span> of <span id="folderTotalCount"><?= esc((string) $totalFolders) ?></span> entries
                </div>
                <div class="text-sm text-gray-500">
                    Active sort: <span id="folderActiveSortLabel"><?= esc($sortOptions[$sortFilter] ?? 'Alphabetical (A-Z)') ?></span>
                </div>
            </div>

            <?php if (empty($folders)): ?>
                <div class="p-12 text-center">
                    <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mb-2 text-lg font-medium text-gray-900">
                        <?php if ($search !== '' || $statusFilter !== '' || $folderTypeFilter !== '' || $sortFilter !== 'company_asc' || $categoryFilter !== ''): ?>
                            No matching folders found
                        <?php else: ?>
                            No folders found
                        <?php endif; ?>
                    </h3>
                    <p class="mb-4 text-gray-600">
                        <?php if ($search !== '' || $statusFilter !== '' || $folderTypeFilter !== '' || $sortFilter !== 'company_asc' || $categoryFilter !== ''): ?>
                            Try adjusting your search or filter criteria
                        <?php else: ?>
                            Get started by creating your first folder
                        <?php endif; ?>
                    </p>
                    <?php if (can('create_document_record') && $search === '' && $statusFilter === '' && $folderTypeFilter === '' && $sortFilter === 'company_asc' && $categoryFilter === ''): ?>
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
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Company</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Borrowed / Due Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Return Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="folderTableBody" class="divide-y divide-gray-200 bg-white">
                            <tr id="folderNoResultsRow" class="hidden">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No matching folders found</td>
                            </tr>
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
                                $searchText = strtolower(trim(implode(' ', array_filter([
                                    (string) ($folder['file_code'] ?? ''),
                                    (string) ($folder['company_name'] ?? ''),
                                    (string) ($folder['location_code'] ?? ''),
                                    (string) $categoryLabel,
                                    (string) ($folder['folder_type'] ?? ''),
                                    (string) $folderStatus,
                                ]))));
                                ?>
                                <tr
                                    class="folder-row bg-white transition-colors duration-150 hover:bg-gray-50"
                                    data-company-name="<?= esc(strtolower((string) ($folder['company_name'] ?? ''))); ?>"
                                    data-file-code="<?= esc(strtolower((string) ($folder['file_code'] ?? ''))); ?>"
                                    data-location-code="<?= esc(strtolower((string) ($folder['location_code'] ?? ''))); ?>"
                                    data-category-label="<?= esc(strtolower($categoryLabel)); ?>"
                                    data-folder-type="<?= esc(strtolower((string) ($folder['folder_type'] ?? ''))); ?>"
                                    data-status="<?= esc(strtolower((string) $folderStatus)); ?>"
                                    data-created-at="<?= esc((string) ($folder['created_at'] ?? '')); ?>"
                                    data-search-text="<?= esc($searchText); ?>">
                                    <td class="whitespace-nowrap px-4 py-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?= $statusClass ?>">
                                            <?= esc($folderStatus) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold text-gray-900"><?= esc($folder['file_code'] ?? '--') ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-900"><?= esc($folder['company_name'] ?? 'Unknown Folder') ?></td>
                                    <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700"><?= esc($categoryLabel) ?></td>
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
                                                'url' => route_to('records.show', $folder['folder_id']),
                                                'style' => 'info',
                                                'title' => 'View Details'
                                            ]) ?>

                                            <?php if ($folderStatus !== 'Pending Archive'): ?>
                                                <?= view('components/button', [
                                                    'label' => 'Edit',
                                                    'url' => route_to('records.edit', $folder['folder_id']),
                                                    'style' => 'info',
                                                    'title' => 'Edit Folder'
                                                ]) ?>
                                            <?php endif; ?>

                                            <?php if (can('approve_folder_creation') && ($folderStatus === 'Pending' || $folderStatus === 'Pending Update')): ?>
                                                <?= view('components/button', [
                                                    'label' => 'Approve',
                                                    'type' => 'submit',
                                                    'style' => 'primary',
                                                    'action' => route_to('records.approve', $folder['folder_id']),
                                                    'confirm' => 'Are you sure you want to approve this folder?'
                                                ]) ?>
                                                <?= view('components/button', [
                                                    'label' => 'Decline',
                                                    'type' => 'submit',
                                                    'style' => 'danger',
                                                    'action' => route_to('records.decline', $folder['folder_id']),
                                                    'confirm' => 'Are you sure you want to decline this folder?'
                                                ]) ?>
                                            <?php endif; ?>

                                            <?php if (can('approve_archive') && $folderStatus !== 'Archived' && $folderStatus !== 'Archival' && $folderStatus !== 'Borrowed' && $folderStatus !== 'Disposed' && $folderStatus !== 'Pending' && $folderStatus !== 'Pending Update' && $folderStatus !== 'Pending Archive'): ?>
                                                <?= view('components/button', [
                                                    'label' => 'Archive',
                                                    'type' => 'submit',
                                                    'style' => 'secondary',
                                                    'class' => 'text-green-700',
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
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
(function () {
    var form = document.getElementById('folderFiltersForm');
    if (!form) {
        return;
    }

    var searchInput = document.getElementById('folderSearchInput');
    var sortFilterInput = document.getElementById('sortFilterInput');
    var statusFilterInput = document.getElementById('statusFilterInput');
    var folderTypeFilterInput = document.getElementById('folderTypeFilterInput');
    var categoryFilterInput = document.getElementById('categoryFilterInput');

    var filtersModal = document.getElementById('filtersModal');
    var openFiltersModal = document.getElementById('openFiltersModal');
    var closeFiltersModal = document.getElementById('closeFiltersModal');
    var applyFiltersButton = document.getElementById('applyFiltersButton');

    var modalStatusFilter = document.getElementById('modalStatusFilter');
    var modalFolderTypeFilter = document.getElementById('modalFolderTypeFilter');
    var modalCategoryFilter = document.getElementById('modalCategoryFilter');
    var sortButtons = Array.prototype.slice.call(document.querySelectorAll('.sort-button.modal-filter'));

    var resetButton = document.getElementById('resetFiltersButton');
    var tbody = document.getElementById('folderTableBody');
    var noResultsRow = document.getElementById('folderNoResultsRow');
    var visibleCount = document.getElementById('folderVisibleCount');
    var totalCount = document.getElementById('folderTotalCount');
    var activeSortLabel = document.getElementById('folderActiveSortLabel');

    if (!searchInput || !tbody) {
        return;
    }

    var sortLabels = {
        company_asc: 'Alphabetical (A-Z)',
        company_desc: 'Alphabetical (Z-A)',
        newest: 'Newest Created'
    };

    function normalizeValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function setActiveSort(sortValue) {
        if (sortFilterInput) {
            sortFilterInput.value = sortValue;
        }

        sortButtons.forEach(function (button) {
            var isActive = button.dataset.sortValue === sortValue;
            button.classList.toggle('bg-blue-50', isActive);
            button.classList.toggle('text-blue-700', isActive);
            button.classList.toggle('font-semibold', isActive);
        });
    }

    function rowMatches(row, filters) {
        var searchText = normalizeValue(row.dataset.searchText);
        var status = normalizeValue(row.dataset.status);
        var folderType = normalizeValue(row.dataset.folderType);
        var categoryLabel = normalizeValue(row.dataset.categoryLabel);

        if (filters.search !== '' && searchText.indexOf(filters.search) === -1) {
            return false;
        }

        if (filters.status !== '' && status !== filters.status) {
            return false;
        }

        if (filters.folderType !== '' && folderType !== filters.folderType) {
            return false;
        }

        if (filters.category !== '' && categoryLabel !== filters.category) {
            return false;
        }

        return true;
    }

    function compareRows(a, b, sortKey) {
        var companyA = normalizeValue(a.dataset.companyName);
        var companyB = normalizeValue(b.dataset.companyName);
        var createdA = Date.parse(a.dataset.createdAt || '') || 0;
        var createdB = Date.parse(b.dataset.createdAt || '') || 0;
        var fallbackA = normalizeValue(a.dataset.fileCode);
        var fallbackB = normalizeValue(b.dataset.fileCode);

        if (sortKey === 'company_desc') {
            var descResult = companyB.localeCompare(companyA);
            return descResult !== 0 ? descResult : fallbackB.localeCompare(fallbackA);
        }

        if (sortKey === 'newest') {
            if (createdB !== createdA) {
                return createdB - createdA;
            }
            return fallbackB.localeCompare(fallbackA);
        }

        var ascResult = companyA.localeCompare(companyB);
        return ascResult !== 0 ? ascResult : fallbackA.localeCompare(fallbackB);
    }

    function applyFilters() {
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.folder-row'));
        
        var filters = {
            search: normalizeValue(searchInput.value),
            status: normalizeValue(statusFilterInput ? statusFilterInput.value : ''),
            folderType: normalizeValue(folderTypeFilterInput ? folderTypeFilterInput.value : ''),
            category: normalizeValue(categoryFilterInput ? categoryFilterInput.value : ''),
            sort: normalizeValue(sortFilterInput ? sortFilterInput.value : 'company_asc') || 'company_asc'
        };

        var filteredRows = rows.filter(function (row) {
            return rowMatches(row, filters);
        });

        var sortedRows = rows.slice().sort(function (a, b) {
            return compareRows(a, b, filters.sort);
        });

        sortedRows.forEach(function (row) {
            var isVisible = rowMatches(row, filters);
            row.style.display = isVisible ? '' : 'none';
            tbody.appendChild(row);
        });

        if (noResultsRow) {
            noResultsRow.style.display = filteredRows.length === 0 ? '' : 'none';
            tbody.appendChild(noResultsRow);
        }

        if (visibleCount) {
            visibleCount.textContent = String(filteredRows.length);
        }

        if (totalCount) {
            totalCount.textContent = String(rows.length);
        }

        if (activeSortLabel) {
            activeSortLabel.textContent = sortLabels[filters.sort] || sortLabels.company_asc;
        }
    }

    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(applyFilters, 150);
    });

    [modalStatusFilter, modalFolderTypeFilter, modalCategoryFilter].forEach(function (control) {
        if (control) {
            control.addEventListener('change', function () {
                if (control === modalStatusFilter) statusFilterInput.value = control.value;
                if (control === modalFolderTypeFilter) folderTypeFilterInput.value = control.value;
                if (control === modalCategoryFilter) categoryFilterInput.value = control.value;
            });
        }
    });

    sortButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setActiveSort(button.dataset.sortValue || 'company_asc');
        });
    });

    openFiltersModal.addEventListener('click', function () {
        filtersModal.classList.remove('hidden');
        filtersModal.classList.add('flex');
    });

    closeFiltersModal.addEventListener('click', function () {
        filtersModal.classList.add('hidden');
        filtersModal.classList.remove('flex');
    });

    applyFiltersButton.addEventListener('click', function () {
        applyFilters();
        filtersModal.classList.add('hidden');
        filtersModal.classList.remove('flex');
    });

    filtersModal.addEventListener('click', function (event) {
        if (event.target === filtersModal) {
            filtersModal.classList.add('hidden');
            filtersModal.classList.remove('flex');
        }
    });

    setActiveSort(normalizeValue(sortFilterInput ? sortFilterInput.value : 'company_asc') || 'company_asc');

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            searchInput.value = '';
            if (modalStatusFilter) modalStatusFilter.value = '';
            if (modalFolderTypeFilter) modalFolderTypeFilter.value = '';
            if (modalCategoryFilter) modalCategoryFilter.value = '';
            if (statusFilterInput) statusFilterInput.value = '';
            if (folderTypeFilterInput) folderTypeFilterInput.value = '';
            if (categoryFilterInput) categoryFilterInput.value = '';
            setActiveSort('company_asc');
            applyFilters();
            filtersModal.classList.add('hidden');
            filtersModal.classList.remove('flex');
        });
    }

    applyFilters();
})();
</script>

<?= $this->endSection() ?>
