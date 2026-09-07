<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>



<?php

$search = $filters['search'] ?? '';

$statusFilter = $filters['status'] ?? '';

$folderTypeFilter = $filters['folder_type'] ?? '';

$categoryFilter = $filters['category'] ?? '';

$noAttachmentsFilter = ($filters['no_attachments'] ?? '') === '1' ? '1' : '';

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



$categories = $categories ?? [];

$categoryOptions = [];



foreach ($categories as $category) {

    $categoryName = trim((string) ($category['category_name'] ?? ''));

    if ($categoryName !== '') {

        $categoryOptions[$categoryName] = $categoryName;
    }
}

ksort($categoryOptions);



$folders = $folders ?? [];

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

                        <button type="button" id="folderSearchButton" aria-label="Search" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>

                            </svg>
                        </button>

                    </div>



                    <input type="hidden" id="sortFilterInput" name="sort" value="<?= esc($sortFilter) ?>">

                    <input type="hidden" id="statusFilterInput" name="status" value="<?= esc($statusFilter) ?>">

                    <input type="hidden" id="folderTypeFilterInput" name="folder_type" value="<?= esc($folderTypeFilter) ?>">

                    <input type="hidden" id="categoryFilterInput" name="category" value="<?= esc($categoryFilter) ?>">

                    <input type="hidden" id="noAttachmentsFilterInput" name="no_attachments" value="<?= esc($noAttachmentsFilter) ?>">



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



                <!-- Attachment Filter -->

                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>

                    <label for="modalNoAttachmentsFilter" class="flex items-start gap-2 cursor-pointer rounded-lg border border-gray-300 px-3 py-2 hover:bg-gray-50">

                        <input type="checkbox" id="modalNoAttachmentsFilter" class="modal-filter mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" <?= $noAttachmentsFilter === '1' ? 'checked' : '' ?>>

                        <span class="text-sm text-gray-700">

                            Only records with no files attached

                            <span class="block text-xs text-gray-500">Finds folders that were created but never populated</span>

                        </span>

                    </label>

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

            <div id="folderResultsPanel">
                <?= view('document-records/_results_panel', [
                    'folders' => $folders,
                    'totalFolders' => $totalFolders,
                    'currentPage' => $currentPage,
                    'totalPages' => $totalPages,
                    'filters' => $filters,
                ]) ?>
            </div>

        </div>

    </section>

</div>



<script>
    (function() {
        var form = document.getElementById('folderFiltersForm');
        var resultsPanel = document.getElementById('folderResultsPanel');
        var searchInput = document.getElementById('folderSearchInput');

        if (!form || !resultsPanel || !searchInput) {
            return;
        }

        var sortFilterInput = document.getElementById('sortFilterInput');
        var statusFilterInput = document.getElementById('statusFilterInput');
        var folderTypeFilterInput = document.getElementById('folderTypeFilterInput');
        var categoryFilterInput = document.getElementById('categoryFilterInput');
        var noAttachmentsFilterInput = document.getElementById('noAttachmentsFilterInput');

        var filtersModal = document.getElementById('filtersModal');
        var openFiltersModal = document.getElementById('openFiltersModal');
        var closeFiltersModal = document.getElementById('closeFiltersModal');
        var applyFiltersButton = document.getElementById('applyFiltersButton');

        var modalStatusFilter = document.getElementById('modalStatusFilter');
        var modalFolderTypeFilter = document.getElementById('modalFolderTypeFilter');
        var modalCategoryFilter = document.getElementById('modalCategoryFilter');
        var modalNoAttachmentsFilter = document.getElementById('modalNoAttachmentsFilter');
        var sortButtons = Array.prototype.slice.call(document.querySelectorAll('.sort-button.modal-filter'));

        var resetButton = document.getElementById('resetFiltersButton');
        var searchButton = document.getElementById('folderSearchButton');

        function normalizeValue(value) {
            return String(value || '').trim().toLowerCase();
        }

        function setActiveSort(sortValue) {
            if (sortFilterInput) {
                sortFilterInput.value = sortValue;
            }

            sortButtons.forEach(function(button) {
                var isActive = button.dataset.sortValue === sortValue;
                button.classList.toggle('bg-blue-50', isActive);
                button.classList.toggle('text-blue-700', isActive);
                button.classList.toggle('font-semibold', isActive);
            });
        }

        function applyActiveLimiterButton(limit) {
            var limiterButtons = resultsPanel.querySelectorAll('.limiter-btn');
            limiterButtons.forEach(function(button) {
                button.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                button.classList.add('border-gray-300');
            });

            var activeButton = resultsPanel.querySelector('.limiter-btn[data-limit="' + limit + '"]');
            if (activeButton) {
                activeButton.classList.remove('border-gray-300');
                activeButton.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            }
        }

        function buildFilterUrl() {
            var url = new URL(window.location.href);

            var params = {
                search: searchInput.value,
                status: statusFilterInput ? statusFilterInput.value : '',
                folder_type: folderTypeFilterInput ? folderTypeFilterInput.value : '',
                category: categoryFilterInput ? categoryFilterInput.value : '',
                no_attachments: noAttachmentsFilterInput ? noAttachmentsFilterInput.value : '',
                sort: sortFilterInput ? sortFilterInput.value : 'company_asc',
            };

            Object.keys(params).forEach(function(key) {
                var value = params[key];
                if (!value) {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
            });

            url.searchParams.set('page', '1');

            return url.toString();
        }

        // Results (count, table/empty-state, pagination) are fetched and
        // swapped in place so typing a keyword — or changing a filter, sort,
        // page size, or page — feels instant instead of reloading the page.
        var requestToken = 0;

        function loadResults(url, options) {
            options = options || {};
            var thisToken = ++requestToken;

            resultsPanel.classList.add('opacity-60');

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Failed to load results');
                    }
                    return response.text();
                })
                .then(function(html) {
                    if (thisToken !== requestToken) {
                        return; // a newer request already landed
                    }

                    resultsPanel.innerHTML = html;

                    var limit = new URL(url, window.location.origin).searchParams.get('limit') || '25';
                    applyActiveLimiterButton(limit);

                    if (options.replace) {
                        window.history.replaceState({}, '', url);
                    } else {
                        window.history.pushState({}, '', url);
                    }
                })
                .catch(function() {
                    window.location.href = url;
                })
                .finally(function() {
                    if (thisToken === requestToken) {
                        resultsPanel.classList.remove('opacity-60');
                    }
                });
        }

        function navigateWithCurrentFilters() {
            loadResults(buildFilterUrl());
        }

        // Live search: debounce while typing so it feels real-time without
        // firing a request on every single keystroke; Enter/the search icon
        // trigger immediately.
        var searchTimer = null;

        searchInput.addEventListener('input', function() {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(navigateWithCurrentFilters, 300);
        });

        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                window.clearTimeout(searchTimer);
                navigateWithCurrentFilters();
            }
        });

        if (searchButton) {
            searchButton.addEventListener('click', function() {
                window.clearTimeout(searchTimer);
                navigateWithCurrentFilters();
            });
        }

        [modalStatusFilter, modalFolderTypeFilter, modalCategoryFilter].forEach(function(control) {
            if (control) {
                control.addEventListener('change', function() {
                    if (control === modalStatusFilter) statusFilterInput.value = control.value;
                    if (control === modalFolderTypeFilter) folderTypeFilterInput.value = control.value;
                    if (control === modalCategoryFilter) categoryFilterInput.value = control.value;
                });
            }
        });

        // Checkbox rather than a select, so it maps to '1' / '' by checked state.
        if (modalNoAttachmentsFilter && noAttachmentsFilterInput) {
            modalNoAttachmentsFilter.addEventListener('change', function() {
                noAttachmentsFilterInput.value = this.checked ? '1' : '';
            });
        }

        sortButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                setActiveSort(button.dataset.sortValue || 'company_asc');
            });
        });

        openFiltersModal.addEventListener('click', function() {
            filtersModal.classList.remove('hidden');
            filtersModal.classList.add('flex');
        });

        closeFiltersModal.addEventListener('click', function() {
            filtersModal.classList.add('hidden');
            filtersModal.classList.remove('flex');
        });

        applyFiltersButton.addEventListener('click', function() {
            navigateWithCurrentFilters();
        });

        filtersModal.addEventListener('click', function(event) {
            if (event.target === filtersModal) {
                filtersModal.classList.add('hidden');
                filtersModal.classList.remove('flex');
            }
        });

        setActiveSort(normalizeValue(sortFilterInput ? sortFilterInput.value : 'company_asc') || 'company_asc');

        if (resetButton) {
            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                if (modalStatusFilter) modalStatusFilter.value = '';
                if (modalFolderTypeFilter) modalFolderTypeFilter.value = '';
                if (modalCategoryFilter) modalCategoryFilter.value = '';
                if (modalNoAttachmentsFilter) modalNoAttachmentsFilter.checked = false;
                if (statusFilterInput) statusFilterInput.value = '';
                if (folderTypeFilterInput) folderTypeFilterInput.value = '';
                if (categoryFilterInput) categoryFilterInput.value = '';
                if (noAttachmentsFilterInput) noAttachmentsFilterInput.value = '';
                setActiveSort('company_asc');
                navigateWithCurrentFilters();
            });
        }

        // The limiter buttons, pagination links, and per-row approve/decline
        // forms all live inside resultsPanel and get replaced on every
        // reload, so they're handled via delegation instead of being bound
        // directly (direct bindings would go stale after the first swap).
        resultsPanel.addEventListener('click', function(event) {
            var limiterBtn = event.target.closest('.limiter-btn');
            if (limiterBtn) {
                event.preventDefault();
                var url = new URL(window.location.href);
                url.searchParams.set('limit', limiterBtn.getAttribute('data-limit'));
                url.searchParams.set('page', '1');
                loadResults(url.toString());
                return;
            }

            var pageLink = event.target.closest('a[data-page-link]');
            if (pageLink) {
                event.preventDefault();
                loadResults(pageLink.href);
            }
        });

        function formatDiffDetails(diff) {
            var details = {};
            var hasChanges = false;

            (diff || []).forEach(function(item) {
                if (item.changed) {
                    hasChanges = true;
                    details[item.label] = item.current + '  →  ' + item.proposed;
                }
            });

            if (!hasChanges) {
                details['Changes'] = 'No field changes detected.';
            }

            return details;
        }

        resultsPanel.addEventListener('submit', function(event) {
            var updateForm = event.target;
            if (!updateForm.classList.contains('update-diff-form')) {
                return;
            }

            if (updateForm.dataset.diffConfirmed === 'true') {
                updateForm.dataset.diffConfirmed = 'false';
                event.stopPropagation();
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var isApprove = updateForm.getAttribute('data-diff-action') === 'approve';
            var diffUrl = updateForm.getAttribute('data-diff-url');

            fetch(diffUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        return response.json().then(function(body) {
                            throw new Error(body.error || 'Unable to load the proposed update.');
                        });
                    }
                    return response.json();
                })
                .then(function(data) {
                    window.showConfirmation({
                        title: isApprove ? 'Approve Metadata Update' : 'Decline Metadata Update',
                        message: isApprove ?
                            'Review the proposed changes below before approving this update.' : 'Review the proposed changes below before declining this update.',
                        confirmText: isApprove ? 'Approve' : 'Decline',
                        cancelText: 'Cancel',
                        details: formatDiffDetails(data.diff),
                        onConfirm: function() {
                            updateForm.dataset.diffConfirmed = 'true';
                            if (typeof updateForm.requestSubmit === 'function') {
                                updateForm.requestSubmit();
                            } else {
                                updateForm.submit();
                            }
                        },
                    });
                })
                .catch(function(err) {
                    window.showError(err.message || 'Unable to load the proposed update.');
                });
        });

        // Keep the results panel in sync with browser back/forward.
        window.addEventListener('popstate', function() {
            loadResults(window.location.href, {
                replace: true
            });
        });

        applyActiveLimiterButton(<?= json_encode((string) ($filters['limit'] ?? 'all')) ?>);
    })();
</script>

<?= $this->endSection() ?>