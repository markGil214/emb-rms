<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$occupancyMap = $occupancyMap ?? [];
$locationsList = array_map(static function (array $location) use ($occupancyMap): array {
    // `current_count` on the locations table is never maintained, so the
    // real occupancy is the live folder count passed in by the controller.
    $location['used'] = (int) ($occupancyMap[(int) $location['location_id']] ?? 0);
    $location['capacity'] = (int) ($location['capacity'] ?? 0);

    return $location;
}, $locations ?? []);
$racksList = $racks ?? [];
$totalShelves = is_array($locationsList) ? count($locationsList) : 0;
$totalRacks = is_array($racksList) ? count($racksList) : 0;
$editLocation = $editLocation ?? null;
?>

<style>
    .rack-page { min-width: 0; color: #0f172a; }
    .page-hero,
    .controls-card,
    .table-card,
    .pagination-card { border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); }
    .page-hero { margin-bottom: 16px; padding: 20px; background: linear-gradient(135deg, #ffffff 0%, #ffffff 60%, #f8fafc 100%); }
    .page-hero__inner { display: flex; flex-direction: column; gap: 12px; }
    .page-kicker, .controls-kicker { margin: 0; color: #64748b; font-size: 12px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; }
    .page-title { margin: 4px 0 0; color: #0f172a; font-size: 28px; font-weight: 700; line-height: 1.15; }
    .page-description { margin: 8px 0 0; max-width: 720px; color: #475569; font-size: 14px; line-height: 1.6; }
    .page-stats { display: flex; flex-wrap: wrap; gap: 10px; }
    .stat-pill, .summary-pill, .action-pill, .pagination-page { display: inline-flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 999px; background: #fff; color: #475569; font-size: 12px; font-weight: 700; line-height: 1; }
    .stat-pill { padding: 8px 12px; }
    .stat-dot { width: 8px; height: 8px; margin-right: 8px; border-radius: 999px; background: #3b82f6; }
    .stat-dot--green { background: #10b981; }
    .controls-card { margin-bottom: 12px; padding: 18px 20px; }
    .controls-layout { display: grid; gap: 14px; align-items: end; }
    .controls-copy { min-width: 0; }
    .controls-grid { display: grid; gap: 12px; margin-top: 12px; }
    .search-wrap { position: relative; }
    .search-input, .select-input, .form-control { width: 100%; border: 1px solid #cbd5e1; border-radius: 14px; background: #fff; color: #0f172a; font-size: 14px; outline: none; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
    .search-input, .select-input { min-height: 44px; padding: 10px 16px; }
    .search-input { padding-left: 42px; }
    .search-input:focus, .select-input:focus, .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.14); }
    .search-icon { position: absolute; left: 14px; top: 50%; width: 18px; height: 18px; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
    .primary-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 16px; border: 1px solid #0f172a; border-radius: 14px; background: #0f172a; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: background-color 0.15s ease, box-shadow 0.15s ease; }
    .button-icon { width: 20px; height: 20px; margin-right: 8px; }
    .primary-button:hover { background: #1e293b; }
    .primary-button:focus { box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.12); }
    .table-card { overflow: hidden; }
    .table-wrap { overflow-x: auto; }
    .rack-table { width: 100%; min-width: 720px; border-collapse: collapse; }
    .rack-table thead { background: #f8fafc; }
    .rack-table th, .rack-table td { padding: 12px 24px; border-bottom: 1px solid #e2e8f0; }
    .rack-table th { color: #64748b; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
    .cell-left { text-align: left; }
    .cell-center { text-align: center; }
    .rack-group-row { background: #f8fafc; }
    .rack-row { display: flex; align-items: center; }
    .shelf-row__content { display: flex; align-items: center; padding-left: 12px; }
    .rack-toggle { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; margin-right: 8px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; color: #475569; cursor: pointer; }
    .rack-toggle-icon { width: 16px; height: 16px; transition: transform 0.2s ease; }
    .is-rotated { transform: rotate(90deg); }
    .rack-icon { width: 16px; height: 16px; margin-right: 8px; color: #64748b; }
    .rack-title { color: #0f172a; font-size: 14px; font-weight: 700; }
    .rack-count { margin-left: 12px; color: #64748b; font-size: 12px; }
    .used-pill { display: inline-flex; align-items: center; border-radius: 999px; background: #dbeafe; color: #1e40af; padding: 4px 10px; font-size: 12px; font-weight: 700; }
    .summary-pill { padding: 5px 12px; color: #475569; }
    .shelf-row:nth-child(odd) { background: #fff; }
    .shelf-row:nth-child(even) { background: #f8fafc; }
    .shelf-marker { width: 8px; height: 8px; margin-right: 12px; border-radius: 999px; background: #cbd5e1; }
    .shelf-title { color: #0f172a; font-size: 14px; }
    .action-pill { padding: 5px 12px; border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; cursor: pointer; }
    .action-pill:hover { border-color: #93c5fd; background: #dbeafe; color: #1e3a8a; }
    .pagination-card { margin-top: 12px; padding: 16px 18px; }
    .pagination-layout { display: flex; flex-direction: column; gap: 12px; }
    .pagination-text { color: #475569; font-size: 14px; }
    .pagination-controls { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .pagination-button { padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #475569; font-size: 14px; cursor: pointer; transition: background-color 0.15s ease, color 0.15s ease, opacity 0.15s ease; }
    .pagination-button:hover { background: #f8fafc; }
    .pagination-button.is-disabled { opacity: 0.5; cursor: not-allowed; }
    .pagination-page { padding: 6px 12px; background: #f8fafc; color: #475569; }
    .empty-state { padding: 48px 20px; text-align: center; }
    .empty-state__icon { width: 64px; height: 64px; margin: 0 auto 16px; color: #94a3b8; }
    .empty-state__title { margin: 0 0 8px; color: #0f172a; font-size: 18px; font-weight: 600; }
    .empty-state__text { margin: 0 0 16px; color: #475569; font-size: 14px; }
    .modal-overlay { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, 0.45); z-index: 50; }
    .modal-panel { width: 100%; max-width: 28rem; border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.16); padding: 24px; }
    .modal-title { margin: 0 0 16px; color: #0f172a; font-size: 18px; font-weight: 700; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; color: #334155; font-size: 14px; font-weight: 600; }
    .form-control { min-height: 42px; padding: 10px 12px; }
    .form-error { margin-top: 6px; color: #dc2626; font-size: 13px; }
    .form-hint { margin-top: 6px; color: #64748b; font-size: 12px; }
    .used-pill--full { background: #fee2e2; color: #991b1b; }
    .capacity-unset { color: #b45309; font-style: italic; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
    .secondary-button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #334155; font-size: 14px; font-weight: 600; cursor: pointer; }
    .secondary-button:hover { background: #e2e8f0; }
    .submit-button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 14px; border: 1px solid #0f172a; border-radius: 12px; background: #0f172a; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
    .submit-button:hover { background: #1e293b; }
    @media (min-width: 640px) { .page-hero__inner, .pagination-layout { flex-direction: row; justify-content: space-between; align-items: center; } }
    @media (min-width: 1024px) { .controls-grid { grid-template-columns: minmax(0, 22rem) 11rem 10rem; } .controls-layout { grid-template-columns: minmax(0, 1fr) auto; } }

    /* Dark mode: this page uses its own hand-rolled CSS instead of Tailwind
       utility classes, so the global dark-mode.css overrides (which only
       target Tailwind class names) never match anything here. */
    .dark .rack-page { color: var(--color-text); }
    .dark .page-hero,
    .dark .controls-card,
    .dark .table-card,
    .dark .pagination-card { border-color: var(--color-border); background: var(--color-bg-secondary); }
    .dark .page-hero { background: linear-gradient(135deg, var(--color-bg-secondary) 0%, var(--color-bg-secondary) 60%, var(--color-bg-tertiary) 100%); }
    .dark .page-kicker, .dark .controls-kicker { color: var(--color-text-muted); }
    .dark .page-title { color: var(--color-text); }
    .dark .page-description { color: var(--color-text-secondary); }
    .dark .stat-pill, .dark .summary-pill, .dark .pagination-page { border-color: var(--color-border); background: var(--color-bg-secondary); color: var(--color-text-secondary); }
    .dark .search-input, .dark .select-input, .dark .form-control { border-color: var(--color-border-light); background: var(--color-bg-secondary); color: var(--color-text); }
    .dark .search-icon { color: var(--color-text-muted); }
    .dark .primary-button { border-color: var(--color-text); }
    .dark .rack-table thead { background: var(--color-bg-tertiary); }
    .dark .rack-table th, .dark .rack-table td { border-color: var(--color-border); }
    .dark .rack-table th { color: var(--color-text-muted); }
    .dark .rack-group-row { background: var(--color-bg-tertiary); }
    .dark .rack-toggle { border-color: var(--color-border); background: var(--color-bg-secondary); color: var(--color-text-secondary); }
    .dark .rack-icon { color: var(--color-text-muted); }
    .dark .rack-title { color: var(--color-text); }
    .dark .rack-count { color: var(--color-text-muted); }
    .dark .used-pill { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
    .dark .summary-pill { color: var(--color-text-secondary); }
    .dark .shelf-row:nth-child(odd) { background: var(--color-bg-secondary); }
    .dark .shelf-row:nth-child(even) { background: var(--color-bg-tertiary); }
    .dark .shelf-marker { background: var(--color-border-light); }
    .dark .shelf-title { color: var(--color-text); }
    .dark .action-pill { border-color: rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.15); color: #93c5fd; }
    .dark .action-pill:hover { border-color: rgba(59, 130, 246, 0.6); background: rgba(59, 130, 246, 0.25); color: #bfdbfe; }
    .dark .pagination-text { color: var(--color-text-secondary); }
    .dark .pagination-button { border-color: var(--color-border-light); background: var(--color-bg-secondary); color: var(--color-text-secondary); }
    .dark .pagination-button:hover { background: var(--color-bg-tertiary); }
    .dark .pagination-page { background: var(--color-bg-tertiary); color: var(--color-text-secondary); }
    .dark .form-hint { color: var(--color-text-muted); }
    .dark .used-pill--full { background: rgba(220, 38, 38, 0.2); color: #fca5a5; }
    .dark .capacity-unset { color: #fcd34d; }
    .dark .empty-state__icon { color: var(--color-text-muted); }
    .dark .empty-state__title { color: var(--color-text); }
    .dark .empty-state__text { color: var(--color-text-secondary); }
    .dark .modal-panel { border-color: var(--color-border); background: var(--color-bg-secondary); }
    .dark .modal-title { color: var(--color-text); }
    .dark .form-label { color: var(--color-text-secondary); }
    .dark .secondary-button { border-color: var(--color-border); background: var(--color-bg-tertiary); color: var(--color-text-secondary); }
    .dark .secondary-button:hover { background: var(--color-border); }
    .dark .submit-button { border-color: var(--color-text); }
</style>

<div x-data="racksManager()" class="rack-page">
    <div class="page-hero">
        <div class="page-hero__inner">
            <div>
                <p class="page-kicker">Location management</p>
                <h1 class="page-title">Manage Racks</h1>
                <p class="page-description">Search, filter, and expand rack groups from one place.</p>
                <div class="page-stats">
                    <span class="stat-pill"><span class="stat-dot"></span><?= esc((string) $totalRacks) ?> racks</span>
                    <span class="stat-pill"><span class="stat-dot stat-dot--green"></span><?= esc((string) $totalShelves) ?> shelves</span>
                </div>
            </div>
        </div>
    </div>

    <div class="controls-card">
        <div class="controls-layout">
            <div class="controls-copy">
                <p class="controls-kicker">Search and filters</p>
                <div class="controls-grid">
                    <div class="search-wrap">
                        <input type="text" x-model="searchQuery" @input="filterLocations()" placeholder="Search racks or shelves..." class="search-input">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <select x-model="rackFilter" @change="filterLocations()" class="select-input">
                        <option value="">All Racks</option>
                        <?php foreach ($racksList as $rack): ?>
                            <option value="<?= esc($rack) ?>">Rack <?= esc($rack) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select x-model="entriesPerPage" @change="updatePagination()" class="select-input">
                        <option value="5">5 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                        <option value="">All</option>
                    </select>

                    <div>
                        <button type="button" @click="toggleShowAll()" class="secondary-button" x-text="entriesPerPage === '' ? 'Showing all' : 'Show all'"></button>
                    </div>
                </div>
            </div>

            <div>
                <button type="button" onclick="openModal('addRackModal')" class="primary-button">
                    <svg class="button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Shelf
                </button>
            </div>
        </div>
    </div>

    <div id="addRackModal" class="modal-overlay" x-data="{ open: false }">
        <div class="modal-panel">
            <h3 class="modal-title">Add New Shelf</h3>
            <form method="post" action="<?= route_to('racks.store') ?>" data-confirm-message="Add this shelf to the selected rack?">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="rack" class="form-label">Rack</label>
                    <select id="rack" name="rack" required class="form-control">
                        <option value="">Choose a rack</option>
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?= $i ?>" <?= old('rack') === (string) $i ? 'selected' : '' ?>>Rack <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (! empty($errors['rack'])): ?>
                        <p class="form-error"><?= esc($errors['rack']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="shelf" class="form-label">Shelf</label>
                    <input id="shelf" type="text" name="shelf" value="<?= esc(old('shelf')) ?>" required maxlength="50" pattern="[A-Za-z0-9 ]+" title="Use letters, numbers, and spaces only." class="form-control">
                    <?php if (! empty($errors['shelf'])): ?>
                        <p class="form-error"><?= esc($errors['shelf']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input id="capacity" type="number" name="capacity" value="<?= esc(old('capacity', '0')) ?>" min="0" step="1" class="form-control">
                    <p class="form-hint">Maximum folders this shelf can hold. A shelf set to 0 cannot store any folders.</p>
                    <?php if (! empty($errors['capacity'])): ?>
                        <p class="form-error"><?= esc($errors['capacity']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeModal('addRackModal')" class="secondary-button">Cancel</button>
                    <button type="submit" class="submit-button">Add Shelf</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editRackModal" class="modal-overlay">
        <div class="modal-panel">
            <h3 class="modal-title">Edit Shelf</h3>
            <form method="post" id="editRackForm" action="<?= $editLocation ? route_to('racks.update', $editLocation['location_id']) : '#' ?>" data-confirm-message="Save changes to this shelf?">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="edit-rack" class="form-label">Rack</label>
                    <select id="edit-rack" name="rack" required class="form-control">
                        <option value="">Choose a rack</option>
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?= $i ?>" <?= old('rack', (string) ($editLocation['rack'] ?? '')) === (string) $i ? 'selected' : '' ?>>Rack <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (! empty($errors['rack'])): ?>
                        <p class="form-error"><?= esc($errors['rack']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="edit-shelf" class="form-label">Shelf</label>
                    <input id="edit-shelf" type="text" name="shelf" value="<?= esc(old('shelf', (string) ($editLocation['shelf'] ?? ''))) ?>" required maxlength="50" pattern="[A-Za-z0-9 ]+" title="Use letters, numbers, and spaces only." class="form-control">
                    <?php if (! empty($errors['shelf'])): ?>
                        <p class="form-error"><?= esc($errors['shelf']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="edit-capacity" class="form-label">Capacity</label>
                    <input id="edit-capacity" type="number" name="capacity" value="<?= esc(old('capacity', (string) ($editLocation['capacity'] ?? '0'))) ?>" min="0" step="1" class="form-control">
                    <p class="form-hint">Maximum folders this shelf can hold. A shelf set to 0 cannot store any folders.</p>
                    <?php if (! empty($errors['capacity'])): ?>
                        <p class="form-error"><?= esc($errors['capacity']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeModal('editRackModal')" class="secondary-button">Cancel</button>
                    <button type="submit" class="submit-button">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-card">
        <template x-if="filteredLocations.length === 0">
            <div class="empty-state">
                <svg class="empty-state__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="empty-state__title">
                    <span x-show="searchQuery || rackFilter">No matching locations found</span>
                    <span x-show="!searchQuery && !rackFilter">No rack shelves found</span>
                </h3>
                <p class="empty-state__text">
                    <span x-show="searchQuery || rackFilter">Try adjusting your search or filter criteria</span>
                    <span x-show="!searchQuery && !rackFilter">Get started by adding your first shelf</span>
                </p>
            </div>
        </template>

        <template x-if="filteredLocations.length > 0">
            <div class="table-wrap">
                <table class="rack-table">
                    <thead>
                        <tr>
                            <th>Rack</th>
                            <th>Shelf</th>
                            <th class="cell-center">Used</th>
                            <th class="cell-center">Capacity</th>
                            <th class="cell-center">Actions</th>
                        </tr>
                    </thead>
                    <template x-for="rackGroup in paginatedRacks" :key="'rack-' + rackGroup.rack">
                        <tbody>
                            <tr class="rack-group-row">
                                <td class="cell-left" colspan="2">
                                    <div class="rack-row">
                                        <button type="button" @click="toggleRack(rackGroup.rack)" class="rack-toggle">
                                            <svg class="rack-toggle-icon" :class="isRackExpanded(rackGroup.rack) ? 'is-rotated' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                        <svg class="rack-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span class="rack-title" x-text="'Rack ' + rackGroup.rack"></span>
                                        <span class="rack-count" x-text="rackGroup.shelves.length + ' shelves'"></span>
                                    </div>
                                </td>
                                <td class="cell-center">
                                    <span class="used-pill" x-text="rackGroup.totalUsed"></span>
                                </td>
                                <td class="cell-center"></td>
                                <td class="cell-center">
                                    <button type="button" class="summary-pill">Rack summary</button>
                                </td>
                            </tr>

                            <template x-if="isRackExpanded(rackGroup.rack)">
                                <template x-for="(location, shelfIndex) in rackGroup.shelves" :key="location.rack + '-' + location.shelf">
                                    <tr class="shelf-row" :class="shelfIndex % 2 === 0 ? 'shelf-row--odd' : 'shelf-row--even'">
                                        <td></td>
                                        <td class="cell-left">
                                            <div class="shelf-row__content">
                                                <span class="shelf-marker"></span>
                                                <span class="shelf-title" x-text="'Shelf ' + location.shelf"></span>
                                            </div>
                                        </td>
                                        <td class="cell-center">
                                            <span class="used-pill"
                                                :class="Number(location.used) >= Number(location.capacity) ? 'used-pill--full' : ''"
                                                x-text="location.used"></span>
                                        </td>
                                        <td class="cell-center">
                                            <span class="shelf-title"
                                                :class="Number(location.capacity) === 0 ? 'capacity-unset' : ''"
                                                x-text="Number(location.capacity) > 0 ? location.capacity : 'Not set'"></span>
                                        </td>
                                        <td class="cell-center">
                                            <button type="button" class="action-pill" @click="openEditModal(location)">Edit</button>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </template>
                </table>
            </div>
        </template>
    </div>

    <div class="pagination-card" x-show="filteredLocations.length > 0 && entriesPerPage !== ''">
        <div class="pagination-layout">
            <div class="pagination-text">
                Showing <span x-text="startIndex + 1"></span> to <span x-text="endIndex"></span> of <span x-text="filteredRacks.length"></span> racks
            </div>
            <div class="pagination-controls">
                <button @click="previousPage()" :disabled="currentPage === 1" :class="currentPage === 1 ? 'is-disabled' : ''" class="pagination-button">Previous</button>
                <span class="pagination-page">Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span></span>
                <button @click="nextPage()" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'is-disabled' : ''" class="pagination-button">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
                modal.style.display = 'flex';
        }
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
                modal.style.display = 'none';
        }
    }

    document.addEventListener('click', function (event) {
        if (event.target.classList && event.target.classList.contains('modal-overlay')) {
            closeModal(event.target.id);
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList && event.target.classList.contains('modal-close')) {
            closeModal(event.target.getAttribute('data-modal'));
        }
    });

    <?php if (session()->getFlashdata('modal') === 'add-rack'): ?>
    openModal('addRackModal');
    <?php endif; ?>

    <?php if ($editLocation): ?>
    openModal('editRackModal');
    <?php endif; ?>
</script>

<script>
function racksManager() {
    return {
        locations: <?= json_encode($locationsList) ?>,
        updateUrlBase: <?= json_encode(rtrim(base_url('manage-racks'), '/')) ?>,
        filteredLocations: [],
        filteredRacks: [],
        rackExpansionState: {},
        searchQuery: '',
        rackFilter: '',
        entriesPerPage: '25',
        previousEntriesPerPage: '25',
        currentPage: 1,

        init() {
            this.filteredLocations = [...this.locations];
            this.groupFilteredLocations();
            this.updatePagination();
        },

        openEditModal(location) {
            var form = document.getElementById('editRackForm');
            if (form) {
                form.action = this.updateUrlBase + '/' + location.location_id + '/update';
            }

            var rackSelect = document.getElementById('edit-rack');
            if (rackSelect) {
                rackSelect.value = String(location.rack);
            }

            var shelfInput = document.getElementById('edit-shelf');
            if (shelfInput) {
                shelfInput.value = location.shelf;
            }

            var capacityInput = document.getElementById('edit-capacity');
            if (capacityInput) {
                capacityInput.value = Number(location.capacity || 0);
            }

            openModal('editRackModal');
        },

        groupFilteredLocations() {
            const groups = {};

            this.filteredLocations.forEach(location => {
                const rackKey = String(location.rack || '');
                if (!groups[rackKey]) {
                    groups[rackKey] = {
                        rack: rackKey,
                        shelves: [],
                        totalUsed: 0
                    };
                }

                groups[rackKey].shelves.push(location);
                groups[rackKey].totalUsed += parseInt(location.used || 0, 10);

                if (this.rackExpansionState[rackKey] === undefined) {
                    this.rackExpansionState[rackKey] = false;
                }
            });

            this.filteredRacks = Object.values(groups);

            Object.keys(this.rackExpansionState).forEach(rackKey => {
                if (!groups[rackKey]) {
                    delete this.rackExpansionState[rackKey];
                }
            });
        },
        
        filterLocations() {
            this.filteredLocations = this.locations.filter(location => {
                const searchTerm = (this.searchQuery || '').toLowerCase().trim();
                const rackValue = String(location.rack || '').toLowerCase();
                const shelfValue = String(location.shelf || '').toLowerCase();
                const searchableText = `${rackValue} shelf ${shelfValue} rack ${rackValue} shelf ${shelfValue}`;
                const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
                
                const matchesRack = !this.rackFilter || String(location.rack) === this.rackFilter;
                
                return matchesSearch && matchesRack;
            });

            this.groupFilteredLocations();
            
            this.currentPage = 1;
            this.updatePagination();
        },

        isRackExpanded(rackKey) {
            return this.rackExpansionState[rackKey] !== false;
        },

        toggleRack(rackKey) {
            this.rackExpansionState[rackKey] = !this.isRackExpanded(rackKey);
        },

        expandAllRacks() {
            this.filteredRacks.forEach(group => {
                this.rackExpansionState[group.rack] = true;
            });
        },

        collapseAllRacks() {
            this.filteredRacks.forEach(group => {
                this.rackExpansionState[group.rack] = false;
            });
        },

        toggleShowAll() {
            if (this.entriesPerPage === '') {
                // restore previous value
                this.entriesPerPage = this.previousEntriesPerPage || '25';
            } else {
                this.previousEntriesPerPage = this.entriesPerPage || '25';
                this.entriesPerPage = '';
            }

            this.currentPage = 1;
            this.updatePagination();
        },

        get paginatedRacks() {
            if (this.entriesPerPage === '') {
                return this.filteredRacks;
            }

            const start = (this.currentPage - 1) * parseInt(this.entriesPerPage, 10);
            const end = start + parseInt(this.entriesPerPage, 10);
            return this.filteredRacks.slice(start, end);
        },
        
        get paginatedLocations() {
            if (this.entriesPerPage === '') {
                return this.filteredLocations;
            }
            
            const start = (this.currentPage - 1) * parseInt(this.entriesPerPage, 10);
            const end = start + parseInt(this.entriesPerPage, 10);
            return this.filteredLocations.slice(start, end);
        },
        
        updatePagination() {
            // Trigger reactivity
            this.$nextTick(() => {
                if (this.currentPage > this.totalPages) {
                    this.currentPage = this.totalPages;
                }
                if (this.currentPage < 1) {
                    this.currentPage = 1;
                }
            });
        },
        
        get totalPages() {
            if (this.entriesPerPage === '') {
                return 1;
            }
            return Math.max(1, Math.ceil(this.filteredRacks.length / parseInt(this.entriesPerPage, 10)));
        },
        
        get startIndex() {
            if (this.entriesPerPage === '') {
                return 0;
            }
            return (this.currentPage - 1) * parseInt(this.entriesPerPage, 10);
        },
        
        get endIndex() {
            if (this.entriesPerPage === '') {
                return this.filteredRacks.length;
            }
            const end = this.startIndex + parseInt(this.entriesPerPage, 10);
            return Math.min(end, this.filteredRacks.length);
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        }
    }
}
</script>

<?= $this->endSection() ?>
