<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mx-auto w-full max-w-[1600px] p-4 sm:p-6 space-y-6 min-w-0">
    <!-- Page Header -->
    <div class="mb-6">
        <p class="text-gray-600"><?= $subtitle ?></p>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2 min-w-0">
                <div class="relative w-full sm:w-80">
                    <input type="text"
                           id="relocationSearchInput"
                           placeholder="Search relocations..."
                           class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <button type="button" id="openRelocationFilters" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                </button>
            </div>

            <?php if (can('initiate_relocation')): ?>
                <a href="<?= route_to('relocations.create') ?>"
                   class="inline-flex w-auto flex-none items-center justify-center space-x-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Request Relocation</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="relocationFiltersModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4 overflow-y-auto">
        <div class="relative w-full max-w-2xl rounded-lg bg-white shadow-xl my-8">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Filters & Sort</h2>
                <button type="button" id="closeRelocationFilters" class="text-gray-400 hover:text-gray-600" aria-label="Close filters">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4 px-6 py-4">
                <div>
                    <label for="statusFilter" class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                    <select id="statusFilter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="in progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="declined">Declined</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="dateFromFilter" class="mb-2 block text-sm font-medium text-gray-700">Requested From</label>
                        <input type="date" id="dateFromFilter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="dateToFilter" class="mb-2 block text-sm font-medium text-gray-700">Requested To</label>
                        <input type="date" id="dateToFilter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="sortFilter" class="mb-2 block text-sm font-medium text-gray-700">Sort Order</label>
                    <select id="sortFilter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <option value="newest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4">
                <button type="button" id="resetRelocationFilters" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</button>
                <button type="button" id="applyRelocationFilters" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Apply</button>
            </div>
        </div>
    </div>

    <!-- Relocations Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Relocation Requests</h2>
            <div class="text-sm text-gray-600">
                Showing <span id="relocationVisibleCount"><?= esc((string) count($relocations ?? [])) ?></span> entries
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-[960px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Folder Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Folder / Company
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Current Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            New Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Requested On
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($relocations)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No relocation records</p>
                                    <p class="text-sm">No relocation requests have been created yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($relocations as $index => $rel): ?>
                            <?php
                                $statusMap = [
                                    'Pending' => ['Needs Approval', 'bg-yellow-100', 'text-yellow-800'],
                                    'Approved' => ['Approved', 'bg-blue-100', 'text-blue-800'],
                                    'In Progress' => ['Moving', 'bg-orange-100', 'text-orange-800'],
                                    'Completed' => ['Completed', 'bg-green-100', 'text-green-800'],
                                    'Declined' => ['Declined', 'bg-red-100', 'text-red-800']
                                ];
                                $displayStatus = $statusMap[$rel['status']] ?? [$rel['status'], 'bg-gray-100', 'text-gray-800'];
                                $isEven = $index % 2 === 0;
                                $requestedTimestamp = !empty($rel['requested_at']) ? strtotime($rel['requested_at']) : 0;
                            ?>
                            <tr class="relocation-row <?= $isEven ? 'bg-gray-50' : 'bg-white' ?> hover:bg-gray-100"
                                data-status="<?= esc(strtolower((string) $rel['status']), 'attr') ?>"
                                data-requested-at="<?= esc((string) ($rel['requested_at'] ?? ''), 'attr') ?>"
                                data-requested-ts="<?= esc((string) $requestedTimestamp, 'attr') ?>"
                                data-search="<?= esc(strtolower(trim(implode(' ', array_filter([
                                    (string) ($rel['file_code'] ?? ''),
                                    (string) ($rel['company_name'] ?? ''),
                                    (string) ($rel['current_location_display'] ?? ''),
                                    (string) ($rel['new_location_display'] ?? ''),
                                    (string) ($rel['status'] ?? ''),
                                ])))), 'attr') ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= $rel['file_code'] ?? 'N/A' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rel['company_name'] ?? 'Unknown Folder' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rel['current_location_display'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $rel['new_location_display'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('M d, Y g:i A', strtotime($rel['requested_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $displayStatus[1] ?> <?= $displayStatus[2] ?>">
                                        <?= $displayStatus[0] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <?= view('components/button', [
                                            'label' => 'View',
                                            'url' => route_to('relocations.show', $rel['relocation_id']),
                                            'style' => 'info',
                                            'title' => 'View Details'
                                        ]) ?>
                                        
                                        <?php if ($rel['status'] === 'Pending'): ?>
                                            <?php if (can('initiate_relocation')): ?>
                                                <?= view('components/button', [
                                                    'label' => 'Edit',
                                                    'url' => route_to('relocations.edit', $rel['relocation_id']),
                                                    'style' => 'info',
                                                    'title' => 'Edit Request'
                                                ]) ?>
                                            <?php endif; ?>
                                            
                                            <?php if (can('approve_relocation')): ?>
                                                <?= view('components/button', [
                                                    'label' => 'Approve',
                                                    'type' => 'submit',
                                                    'style' => 'primary',
                                                    'action' => route_to('relocations.approve', $rel['relocation_id']),
                                                    'confirm' => 'Approve this relocation request?'
                                                ]) ?>
                                                <?= view('components/button', [
                                                    'label' => 'Decline',
                                                    'type' => 'submit',
                                                    'style' => 'danger',
                                                    'action' => route_to('relocations.decline', $rel['relocation_id']),
                                                    'confirm' => 'Decline this relocation request?'
                                                ]) ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="relocationNoResultsRow" class="hidden">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No matching relocation records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pager && $pager->getPageCount('relocations') > 1): ?>
        <?php
            $totalRelocations = $pager->getTotal('relocations');
            $currentPage = $pager->getCurrentPage('relocations');
            $perPage = 25;
            $relocationCount = count($relocations ?? []);
            $startIndex = $totalRelocations > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
            $endIndex = $totalRelocations > 0 ? min($startIndex + $relocationCount - 1, $totalRelocations) : 0;
        ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 flex-wrap">
                <div class="text-sm text-gray-700">
                    Showing <?= esc((string) $startIndex) ?> to <?= esc((string) $endIndex) ?> of <?= esc((string) $totalRelocations) ?> entries
                </div>
                <div class="flex items-center space-x-2">
                    <?= $pager->links('relocations') ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const searchInput = document.getElementById('relocationSearchInput');
    const openFilters = document.getElementById('openRelocationFilters');
    const closeFilters = document.getElementById('closeRelocationFilters');
    const filtersModal = document.getElementById('relocationFiltersModal');
    const applyButton = document.getElementById('applyRelocationFilters');
    const resetButton = document.getElementById('resetRelocationFilters');
    const statusFilter = document.getElementById('statusFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');
    const sortFilter = document.getElementById('sortFilter');
    const visibleCount = document.getElementById('relocationVisibleCount');
    const noResultsRow = document.getElementById('relocationNoResultsRow');

    if (!searchInput) {
        return;
    }

    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function getRows() {
        return Array.prototype.slice.call(document.querySelectorAll('.relocation-row'));
    }

    function applyFilters() {
        const rows = getRows();
        const query = normalize(searchInput.value);
        const statusValue = normalize(statusFilter && statusFilter.value);
        const fromValue = dateFromFilter && dateFromFilter.value ? dateFromFilter.value : '';
        const toValue = dateToFilter && dateToFilter.value ? dateToFilter.value : '';
        const sortValue = normalize(sortFilter && sortFilter.value) || 'newest';
        const visibleRows = [];

        rows.forEach(row => {
            const rowStatus = normalize(row.dataset.status);
            const rowSearch = normalize(row.dataset.search);
            const rowRequestedAt = row.dataset.requestedAt || '';

            const matchesSearch = !query || rowSearch.includes(query);
            const matchesStatus = !statusValue || rowStatus === statusValue;
            const matchesFrom = !fromValue || (rowRequestedAt && rowRequestedAt >= fromValue);
            const matchesTo = !toValue || (rowRequestedAt && rowRequestedAt <= toValue);
            const shouldShow = matchesSearch && matchesStatus && matchesFrom && matchesTo;

            row.style.display = shouldShow ? '' : 'none';
            if (shouldShow) {
                visibleRows.push(row);
            }
        });

        visibleRows.sort((a, b) => {
            const aTs = parseInt(a.dataset.requestedTs || '0', 10);
            const bTs = parseInt(b.dataset.requestedTs || '0', 10);
            return sortValue === 'oldest' ? aTs - bTs : bTs - aTs;
        });

        visibleRows.forEach(row => row.parentNode.appendChild(row));

        if (visibleCount) {
            visibleCount.textContent = String(visibleRows.length);
        }

        if (noResultsRow) {
            noResultsRow.style.display = visibleRows.length === 0 ? '' : 'none';
            noResultsRow.parentNode.appendChild(noResultsRow);
        }
    }

    let searchTimer = null;
    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(applyFilters, 150);
    });

    [statusFilter, dateFromFilter, dateToFilter, sortFilter].forEach(control => {
        if (!control) return;
        control.addEventListener('change', applyFilters);
        control.addEventListener('input', applyFilters);
    });

    if (openFilters && filtersModal) {
        openFilters.addEventListener('click', () => {
            filtersModal.classList.remove('hidden');
            filtersModal.classList.add('flex');
        });
    }

    if (closeFilters && filtersModal) {
        closeFilters.addEventListener('click', () => {
            filtersModal.classList.add('hidden');
            filtersModal.classList.remove('flex');
        });
    }

    if (applyButton && filtersModal) {
        applyButton.addEventListener('click', () => {
            applyFilters();
            filtersModal.classList.add('hidden');
            filtersModal.classList.remove('flex');
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (dateFromFilter) dateFromFilter.value = '';
            if (dateToFilter) dateToFilter.value = '';
            if (sortFilter) sortFilter.value = 'newest';
            applyFilters();
            if (filtersModal) {
                filtersModal.classList.add('hidden');
                filtersModal.classList.remove('flex');
            }
        });
    }

    if (filtersModal) {
        filtersModal.addEventListener('click', event => {
            if (event.target === filtersModal) {
                filtersModal.classList.add('hidden');
                filtersModal.classList.remove('flex');
            }
        });
    }

    applyFilters();
})();
</script>

<?= $this->endSection() ?>
