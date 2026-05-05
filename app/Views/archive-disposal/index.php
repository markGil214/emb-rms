<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
    $records = $archiveDisposalRecords ?? $archived ?? [];
    $workflowRequests = $workflowRequests ?? [];
    $statusCounts = $statusCounts ?? [
        'Archived' => $totalArchived ?? 0,
        'Pending Disposal' => $totalDisposalPending ?? 0,
        'Approved for Disposal' => $totalDisposalApproved ?? 0,
        'Disposed' => $totalDisposed ?? 0,
    ];

    $formatDate = static function ($value, string $format = 'M d, Y'): string {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date($format, $timestamp) : '-';
    };

    $statusClass = static function (string $status): string {
        $classes = [
            'Archived' => 'bg-blue-100 text-blue-700',
            'Pending Archive' => 'bg-yellow-100 text-yellow-700',
            'Pending Disposal' => 'bg-orange-100 text-orange-700',
            'Approved for Disposal' => 'bg-green-100 text-green-700',
            'Disposed' => 'bg-gray-200 text-gray-700',
        ];

        return $classes[$status] ?? 'bg-gray-100 text-gray-700';
    };

    $retentionPeriod = static function (array $record): string {
        $fileCount = (int) ($record['file_count'] ?? 0);
        $expiringCount = (int) ($record['expiring_file_count'] ?? 0);
        $permanentCount = (int) ($record['permanent_file_count'] ?? 0);

        if ($fileCount === 0) {
            return 'No files';
        }

        if ($expiringCount === 0) {
            return 'Permanent';
        }

        if ($permanentCount === 0) {
            return $expiringCount === 1 ? '1 expiring file' : $expiringCount . ' expiring files';
        }

        return $expiringCount . ' expiring, ' . $permanentCount . ' permanent';
    };

    $certificateText = static function (?string $reference): string {
        $reference = trim((string) $reference);
        if ($reference === '') {
            return '-';
        }

        if (preg_match('/^Reference:\s*(.+)$/mi', $reference, $matches)) {
            return trim($matches[1]);
        }

        return $reference;
    };

    $summaryCards = [
        ['label' => 'Archived', 'count' => $statusCounts['Archived'] ?? 0, 'icon' => 'archive', 'color' => 'blue'],
        ['label' => 'Pending Archive', 'count' => $statusCounts['Pending Archive'] ?? 0, 'icon' => 'clock', 'color' => 'orange'],
        ['label' => 'Pending', 'count' => $statusCounts['Pending Disposal'] ?? 0, 'icon' => 'clock', 'color' => 'orange'],
        ['label' => 'Approved', 'count' => $statusCounts['Approved for Disposal'] ?? 0, 'icon' => 'check', 'color' => 'green'],
        ['label' => 'Disposed', 'count' => $statusCounts['Disposed'] ?? 0, 'icon' => 'disposed', 'color' => 'gray'],
    ];
    // Filter out empty status cards (count = 0) to reduce clutter
    $summaryCards = array_filter($summaryCards, function($card) { return $card['count'] > 0; });
    $activeStatusFilter = (string) ($statusFilter ?? '');
    $searchQuery = (string) ($query ?? '');
    // Flag to track if we're showing bulk actions
    $showBulkActions = (bool) (count($records) > 0 || count($workflowRequests) > 0);
?>

<style>
    .archive-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .archive-summary-card {
        display: block;
        min-height: 92px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .archive-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        border-color: #d1d5db;
    }

    .archive-summary-card--active {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .archive-summary-card::after {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        border-radius: 9999px 0 0 9999px;
    }

    .archive-summary-card--blue::after {
        background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
    }

    .archive-summary-card--orange::after {
        background: linear-gradient(180deg, #fdba74 0%, #f97316 100%);
    }

    .archive-summary-card--green::after {
        background: linear-gradient(180deg, #86efac 0%, #16a34a 100%);
    }

    .archive-summary-card--gray::after {
        background: linear-gradient(180deg, #cbd5e1 0%, #64748b 100%);
    }

    .archive-toolbar {
        display: grid;
        grid-template-columns: minmax(260px, 420px) 1fr auto;
        gap: 12px;
        align-items: center;
    }

    .archive-action-link {
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 600;
        transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease, box-shadow 150ms ease;
    }

    .archive-action-link--primary {
        background: #16a34a;
        border: 1px solid #15803d;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(22, 163, 74, 0.18);
    }

    .archive-action-link--primary:hover {
        background: #15803d;
        color: #ffffff;
    }

    .archive-action-link--secondary {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        color: #374151;
    }

    .archive-action-link--secondary:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .archive-table-wrap {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .archive-table thead th {
        background: #eef6f1;
        color: #315242;
        letter-spacing: 0.06em;
    }

    .archive-table tbody tr {
        transition: background-color 150ms ease;
        border-left: 4px solid transparent;
    }

    .archive-table tbody tr:hover {
        background: #f8fafc;
    }

    /* Row status indicators by archive status */
    .archive-table tbody tr[data-archive-status="archived"] {
        border-left-color: #2563eb;
    }

    .archive-table tbody tr[data-archive-status="pending disposal"] {
        border-left-color: #f97316;
    }

    .archive-table tbody tr[data-archive-status="approved for disposal"] {
        border-left-color: #16a34a;
    }

    .archive-table tbody tr[data-archive-status="disposed"] {
        border-left-color: #64748b;
    }

    .archive-table th {
        white-space: nowrap;
    }

    .archive-table td {
        vertical-align: top;
    }

    .archive-table thead th {
        text-align: center;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .archive-status-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .archive-status-badge {
        display: inline-flex;
        width: max-content;
        max-width: 100%;
        border-radius: 9999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.15;
        white-space: nowrap;
    }

    .archive-helper-text {
        margin-top: 4px;
        color: #64748b;
        font-size: 13px;
    }

    .archive-quick-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .archive-quick-filter {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #374151;
        border-radius: 9999px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
    }

    .archive-quick-filter:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .archive-quick-filter.is-active {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .archive-actions {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 6px;
        align-items: center;
        min-width: 0;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 2px;
    }

    .archive-action-text {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
        padding: 6px 12px;
        min-height: 32px;
        background: #ffffff;
        border: 1px solid transparent;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        white-space: nowrap;
        transition: all 150ms ease;
    }

    .archive-action-text:hover {
        background-color: rgba(0, 0, 0, 0.04);
        border-color: currentColor;
    }

    .archive-action-text svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .archive-action-text.action-primary {
        color: #16a34a;
        background: rgba(22, 163, 74, 0.08);
    }

    .archive-action-text.action-primary:hover {
        background: rgba(22, 163, 74, 0.15);
        border-color: #16a34a;
    }

    .archive-action-text.action-secondary {
        color: #2563eb;
    }

    .archive-action-text.action-secondary:hover {
        background: rgba(37, 99, 235, 0.08);
        border-color: #2563eb;
    }

    .archive-action-text.action-danger {
        color: #dc2626;
    }

    .archive-action-text.action-danger:hover {
        background: rgba(220, 38, 38, 0.08);
        border-color: #dc2626;
    }

    .archive-action-text.action-muted {
        color: #64748b;
    }

    .archive-action-text.action-muted:hover {
        background: rgba(100, 116, 139, 0.08);
        border-color: #64748b;
    }

    .archive-action-row {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        align-items: center;
    }

    .archive-actions .inline {
        display: inline-flex;
    }

    .dark .archive-actions {
        scrollbar-color: #475569 #1f2937;
    }

    .dark .archive-action-text {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
    }

    .dark .archive-action-text:hover {
        background-color: #374151;
        border-color: #6b7280;
    }

    .dark .archive-action-text.action-primary {
        color: #86efac;
        background: rgba(22, 163, 74, 0.2);
        border-color: rgba(22, 163, 74, 0.35);
    }

    .dark .archive-action-text.action-primary:hover {
        background: rgba(22, 163, 74, 0.3);
        border-color: #4ade80;
    }

    .dark .archive-action-text.action-secondary {
        color: #93c5fd;
        background: rgba(37, 99, 235, 0.2);
        border-color: rgba(37, 99, 235, 0.35);
    }

    .dark .archive-action-text.action-secondary:hover {
        background: rgba(37, 99, 235, 0.3);
        border-color: #60a5fa;
    }

    .dark .archive-action-text.action-danger {
        color: #fca5a5;
        background: rgba(220, 38, 38, 0.2);
        border-color: rgba(220, 38, 38, 0.35);
    }

    .dark .archive-action-text.action-danger:hover {
        background: rgba(220, 38, 38, 0.3);
        border-color: #f87171;
    }

    .dark .archive-action-text.action-muted {
        color: #cbd5e1;
        background: rgba(100, 116, 139, 0.2);
        border-color: rgba(100, 116, 139, 0.35);
    }

    .dark .archive-action-text.action-muted:hover {
        background: rgba(100, 116, 139, 0.3);
        border-color: #94a3b8;
    }

    /* Bulk actions toolbar */
    .archive-bulk-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #f0f9ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .archive-bulk-actions.hidden {
        display: none;
    }

    .archive-bulk-actions-count {
        font-weight: 600;
        color: #1d4ed8;
        margin-right: 12px;
    }

    .archive-bulk-actions-buttons {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    .archive-bulk-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: #ffffff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #1d4ed8;
        cursor: pointer;
        transition: all 150ms ease;
    }

    .archive-bulk-action-btn:hover {
        background: #eff6ff;
        border-color: #93c5fd;
    }

    .archive-bulk-action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #94a3b8;
    }

    /* Checkbox styling */
    .archive-checkbox-cell {
        width: 40px;
        text-align: center;
    }

    .archive-checkbox-cell input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #2563eb;
    }

    .archive-table thead th.archive-checkbox-cell {
        padding: 8px 12px;
    }

    .archive-table tbody td.archive-checkbox-cell {
        padding: 8px 12px;
    }

    .archive-muted-action {
        color: #9ca3af;
        font-weight: 600;
    }

    .archive-placeholder {
        color: #64748b;
        font-size: 13px;
        white-space: nowrap;
    }

    .archive-toolbar {
        display: grid;
        grid-template-columns: minmax(260px, 420px) auto auto;
        gap: 12px;
        align-items: center;
    }

    .archive-search-wrap {
        position: relative;
    }

    .archive-search-input {
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.625rem 1rem 0.625rem 2.5rem;
        background: #ffffff;
        color: #111827;
    }

    .archive-search-input:focus {
        outline: none;
        border-color: transparent;
        box-shadow: 0 0 0 2px #3b82f6;
    }

    .archive-search-icon {
        position: absolute;
        left: 0.75rem;
        top: 0.7rem;
        width: 1.25rem;
        height: 1.25rem;
        color: #9ca3af;
        pointer-events: none;
    }

    .archive-filter-trigger {
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background: #ffffff;
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
    }

    .archive-filter-trigger:hover {
        background: #f9fafb;
    }

    .archive-filter-option.is-active,
    .archive-filter-button.is-active {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
        font-weight: 600;
    }

    .archive-row-hidden {
        display: none !important;
    }

    @media (max-width: 1180px) {
        .archive-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .archive-toolbar {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .archive-action-strip {
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .archive-summary-grid {
            grid-template-columns: 1fr;
        }

        .archive-toolbar {
            grid-template-columns: 1fr;
        }

        .archive-quick-filters {
            width: 100%;
        }

        .archive-quick-filter {
            flex: 1;
            justify-content: center;
            font-size: 12px;
            padding: 5px 8px;
        }

        .archive-table {
            font-size: 13px;
        }

        .archive-table th,
        .archive-table td {
            padding: 0.5rem;
        }

        /* Hide less important columns on mobile */
        .archive-table thead th:nth-child(n+3),
        .archive-table tbody td:nth-child(n+3):not(:last-child) {
            display: none;
        }

        .archive-table thead th:last-child,
        .archive-table tbody td:last-child {
            width: 100%;
        }
    }

    @media (max-width: 640px) {
        .archive-summary-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .archive-summary-card {
            min-height: 80px;
            padding: 1rem;
        }

        .archive-toolbar {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .archive-search-input {
            font-size: 16px; /* Prevents zoom on iOS */
        }

        .archive-quick-filters {
            gap: 4px;
        }

        .archive-quick-filter {
            flex: 1;
            padding: 6px 8px;
            font-size: 12px;
        }

        .archive-filter-trigger {
            width: 100%;
        }

        /* Stack table better on small screens */
        .archive-table {
            font-size: 12px;
        }

        .archive-table thead {
            display: none;
        }

        .archive-table tbody,
        .archive-table tbody tr,
        .archive-table tbody td {
            display: block;
            width: 100%;
        }

        .archive-table tbody tr {
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .archive-table tbody td {
            text-align: right;
            padding-left: 0;
            padding-right: 0.5rem;
            border: none;
            position: relative;
            padding-left: 50%;
        }

        .archive-table tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 50%;
            text-align: left;
            font-weight: 600;
            background: #f9fafb;
            padding-left: 0.5rem;
            border-right: 1px solid #e5e7eb;
        }
    }
</style>

<div class="archive-summary-grid">
    <?php foreach ($summaryCards as $card): ?>
        <?php
            $color = $card['color'];
            $iconBg = [
                'blue' => 'bg-blue-100 text-blue-600',
                'orange' => 'bg-orange-100 text-orange-600',
                'green' => 'bg-green-100 text-green-600',
                'gray' => 'bg-gray-100 text-gray-600',
            ][$color] ?? 'bg-gray-100 text-gray-600';
        ?>
        <?php $isActive = $activeStatusFilter === $card['label']; ?>
        <a
            href="<?= route_to('archive.index') ?>?status=<?= urlencode($card['label']) ?>"
            class="archive-summary-card archive-summary-card--<?= esc($color) ?> <?= $isActive ? 'archive-summary-card--active' : '' ?> bg-white rounded-lg border border-gray-200 p-5"
            title="Click to filter by <?= esc($card['label']) ?>"
            aria-label="Filter records by <?= esc($card['label']) ?>: <?= esc((string) $card['count']) ?> records"
        >
            <div class="flex items-center">
                <div class="p-3 rounded-lg mr-4 <?= esc($iconBg) ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php if ($card['icon'] === 'clock'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <?php elseif ($card['icon'] === 'check'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <?php elseif ($card['icon'] === 'disposed'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3m-8 0h10"></path>
                        <?php else: ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        <?php endif; ?>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700"><?= esc($card['label']) ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?= esc((string) $card['count']) ?></p>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

    <!-- Bulk Actions Toolbar -->
    <div id="archiveBulkActionsToolbar" class="archive-bulk-actions hidden">
        <span class="archive-bulk-actions-count"><span id="archiveBulkSelectedCount">0</span> selected</span>
        <div class="archive-bulk-actions-buttons">
            <button type="button" class="archive-bulk-action-btn" data-action="approve" title="Approve selected records">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Approve
            </button>
            <button type="button" class="archive-bulk-action-btn" data-action="reject" title="Reject selected records">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Reject
            </button>
            <button type="button" class="archive-bulk-action-btn" data-action="restore" title="Restore selected records">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Restore
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8 archive-table-wrap">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Archive & Disposal Records</h2>
                <p class="archive-helper-text">Track and manage archival records through their lifecycle: archival, retention review, and disposal approval.</p>
                <?php if ($activeStatusFilter !== ''): ?>
                    <a href="<?= route_to('archive.index') ?>" class="inline-flex mt-2 text-sm font-semibold text-blue-700 hover:text-blue-900">
                        Clear <?= esc($activeStatusFilter) ?> filter
                    </a>
                <?php endif; ?>
            </div>

            <div class="archive-toolbar">
                <div class="archive-search-wrap">
                    <input
                        type="text"
                        id="archiveSearchInput"
                        placeholder="Search by folder, company, category..."
                        value="<?= esc($searchQuery) ?>"
                        class="archive-search-input"
                        aria-label="Search archives"
                    />
                    <svg class="archive-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="archive-quick-filters" aria-label="Quick filters">
                    <button type="button" class="archive-quick-filter is-active" data-activity-filter="all" title="Show all records">All</button>
                    <button type="button" class="archive-quick-filter" data-activity-filter="pending-archive" title="Pending approval">Pending</button>
                    <button type="button" class="archive-quick-filter" data-activity-filter="approved-disposal" title="Ready for next step">Approved</button>
                </div>

                <button type="button" id="openArchiveFiltersModal" class="archive-filter-trigger" title="Open advanced filters">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    More Filters
                </button>
            </div>
        </div>
    </div>

    <div id="archiveFiltersModal" class="fixed inset-0 hidden z-50 items-center justify-center bg-black bg-opacity-50 p-4 overflow-y-auto">
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl my-8">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Advanced Filters</h2>
                <button type="button" id="closeArchiveFiltersModal" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="modalArchiveStatusFilter" class="block text-sm font-semibold text-gray-900 mb-3">Archive Status</label>
                        <select id="modalArchiveStatusFilter" class="archive-filter-option w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                            <option value="">All Archive Status</option>
                            <option value="Archived" <?= $activeStatusFilter === 'Archived' ? 'selected' : '' ?>>Archived (Active records)</option>
                            <option value="Pending Archive" <?= $activeStatusFilter === 'Pending Archive' ? 'selected' : '' ?>>Pending (Awaiting approval)</option>
                            <option value="Disposed" <?= $activeStatusFilter === 'Disposed' ? 'selected' : '' ?>>Disposed</option>
                        </select>
                    </div>

                    <div>
                        <label for="modalWorkflowStatusFilter" class="block text-sm font-semibold text-gray-900 mb-3">Active Workflow</label>
                        <select id="modalWorkflowStatusFilter" class="archive-filter-option w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">
                            <option value="">All Workflows</option>
                            <option value="Pending Archive">Pending Archive (Awaiting archival approval)</option>
                            <option value="Restoration Requested">Restoration Requested (Waiting to restore)</option>
                            <option value="No active workflow">No active workflow (Stable state)</option>
                        </select>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Tip:</span> Use Archive Status to track the physical state of records. Use Active Workflow to see what action is pending.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4">
                <button type="button" id="resetArchiveFiltersButton" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                    Reset
                </button>
                <button type="button" id="applyArchiveFiltersButton" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors duration-200 hover:bg-blue-700">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($records) && empty($workflowRequests)): ?>
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No records to display</h3>
            <p class="text-gray-600 mb-4">No archive or disposal records match your current filter. Records are created when documents reach their retention expiration date.</p>
            <p class="text-sm text-gray-500">Try adjusting your filters or check back later as records age through the system.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="archive-table min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="archive-checkbox-cell">
                            <input type="checkbox" id="archiveSelectAll" title="Select all records" />
                        </th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Archive Date</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Retention</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Archive Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr id="archiveNoResultsRow" class="hidden">
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">No matching archive records found</td>
                    </tr>
                    <?php foreach ($workflowRequests as $request): ?>
                        <?php
                            $workflowStage = (string) ($request['workflow_stage'] ?? 'restoration');
                            $requestedAt = $request['requested_at'] ?? null;
                            $archiveStatus = (string) ($request['archive_status'] ?? 'Archived');
                            $workflowStatus = (string) ($request['status'] ?? 'Restoration Requested');
                            $searchText = strtolower(trim(implode(' ', array_filter([
                                (string) ($request['subject'] ?? ''),
                                (string) ($request['method'] ?? ''),
                                $archiveStatus,
                                $workflowStatus,
                                (string) ($request['requested_by'] ?? ''),
                            ]))));
                        ?>
                        <tr
                            class="archive-row hover:bg-gray-50"
                            data-search-text="<?= esc($searchText) ?>"
                            data-archive-status="<?= esc(strtolower($archiveStatus)) ?>"
                            data-workflow-status="<?= esc(strtolower($workflowStatus)) ?>"
                            data-activity-stage="<?= esc($workflowStage) ?>"
                        >
                            <td class="archive-checkbox-cell">
                                <input type="checkbox" class="archive-row-checkbox" data-row-id="request-<?= esc($request['subject'] ?? '') ?>" />
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="font-medium text-gray-900"><?= esc($request['subject'] ?? '-') ?></div>
                                <div class="text-xs text-gray-500"><?= esc($request['method'] ?? '-') ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= !empty($requestedAt) ? date('M d, Y', strtotime($requestedAt)) : '-' ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                -
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="archive-status-badge <?= esc($statusClass($archiveStatus)) ?>">
                                    <?= esc($archiveStatus) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($workflowStatus) ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($request['requested_by'] ?? '-') ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($request['approved_by'] ?? '-') ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="archive-actions">
                                    <?php if (!empty($request['view_route']) && !empty($request['view_id'])): ?>
                                        <a href="<?= route_to($request['view_route'], $request['view_id']) ?>" class="archive-action-text action-secondary">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            View
                                        </a>
                                    <?php endif; ?>

                                    <div class="archive-action-row">
                                    <?php if (($request['workflow_stage'] ?? '') === 'restoration' && can('approve_restore') && !empty($request['approve_route']) && !empty($request['route_id'])): ?>
                                        <form action="<?= route_to($request['approve_route'], $request['route_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="<?= esc($request['confirm_message'] ?? 'Approve this restoration request?', 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="archive-action-text action-primary">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approve
                                            </button>
                                        </form>
                                    <?php elseif (($request['workflow_stage'] ?? '') === 'pending-archive' && can('approve_archive') && !empty($request['approve_route']) && !empty($request['route_id'])): ?>
                                        <form action="<?= route_to($request['approve_route'], $request['route_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="<?= esc($request['confirm_message'] ?? 'Approve this archive request?', 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="archive-action-text action-primary">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approve
                                            </button>
                                        </form>
                                        <?php if (!empty($request['decline_route'])): ?>
                                            <form action="<?= route_to($request['decline_route'], $request['route_id']) ?>" method="POST" class="inline" data-bulk-action="reject" data-confirm-message="<?= esc($request['decline_confirm_message'] ?? 'Reject this archive request?', 'attr') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text action-danger">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php elseif (($request['workflow_stage'] ?? '') === 'pending-disposal' && can('approve_disposal') && !empty($request['approve_route']) && !empty($request['route_id'])): ?>
                                        <form action="<?= route_to($request['approve_route'], $request['route_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="<?= esc($request['confirm_message'] ?? 'Approve this disposal request?', 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="archive-action-text action-primary">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approve
                                            </button>
                                        </form>
                                        <?php if (!empty($request['decline_route'])): ?>
                                            <form action="<?= route_to($request['decline_route'], $request['route_id']) ?>" method="POST" class="inline" data-bulk-action="reject" data-confirm-message="<?= esc($request['decline_confirm_message'] ?? 'Reject this disposal request?', 'attr') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text action-danger">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php elseif (!empty($request['fallback_action_label'])): ?>
                                        <span class="archive-action-text action-muted"><?= esc($request['fallback_action_label']) ?></span>
                                    <?php elseif (($request['workflow_stage'] ?? '') === 'approved-disposal' && can('approve_disposal') && !empty($request['approve_route']) && !empty($request['route_id'])): ?>
                                        <form action="<?= route_to($request['approve_route'], $request['route_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="<?= esc($request['confirm_message'] ?? 'Mark this archive disposal as completed?', 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="archive-action-text action-muted">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Mark Complete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($records as $record): ?>
                        <?php
                            $folderId = (int) ($record['folder_id'] ?? 0);
                            $status = (string) ($record['current_status'] ?? 'Archived');
                            $pendingRestoration = ($pendingRestorationsByFolder ?? [])[$folderId] ?? null;
                            $isRestorationPending = !empty($pendingRestoration);
                            $archiveStatus = (($record['folder_status'] ?? '') === 'Disposed' || $status === 'Disposed')
                                ? 'Disposed'
                                : 'Archived';
                            $workflowStatus = $isRestorationPending
                                ? 'Restoration Requested'
                                : (($status === 'Pending Disposal' || $status === 'Approved for Disposal')
                                    ? $status
                                    : (($status === 'Disposed') ? 'Disposal Completed' : 'No active workflow'));
                            $requestedBy = $record['requested_by_name']
                                ?? $record['folder_created_by_name']
                                ?? $record['folder_updated_by_name']
                                ?? $record['folder_created_by']
                                ?? '-';
                            $approvedBy = $record['archived_by_name']
                                ?? $record['archived_by']
                                ?? $record['approved_by_name']
                                ?? $record['approved_by']
                                ?? '-';
                            $searchText = strtolower(trim(implode(' ', array_filter([
                                (string) ($record['file_code'] ?? ''),
                                (string) ($record['company_name'] ?? ''),
                                (string) ($record['category_name'] ?? ''),
                                (string) ($record['folder_type'] ?? ''),
                                (string) ($record['archived_date'] ?? ''),
                                (string) $archiveStatus,
                                (string) $workflowStatus,
                                (string) $requestedBy,
                                (string) $approvedBy,
                            ]))));
                        ?>
                        <tr
                            class="archive-row hover:bg-gray-50"
                            data-search-text="<?= esc($searchText) ?>"
                            data-archive-status="<?= esc(strtolower($archiveStatus)) ?>"
                            data-workflow-status="<?= esc(strtolower($workflowStatus)) ?>"
                            data-activity-stage="<?= esc($isRestorationPending ? 'restoration' : ($status === 'Disposed' ? 'disposed' : 'archive')) ?>"
                        >
                            <td class="archive-checkbox-cell">
                                <input type="checkbox" class="archive-row-checkbox" data-row-id="folder-<?= esc((string) $folderId) ?>" />
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="font-medium text-gray-900"><?= esc($record['file_code'] ?? '-') ?></div>
                                <div class="text-gray-600"><?= esc($record['company_name'] ?? '-') ?></div>
                                <div class="text-xs text-gray-500"><?= esc($record['category_name'] ?? $record['folder_type'] ?? '-') ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($formatDate($record['archived_date'] ?? null)) ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($retentionPeriod($record)) ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="archive-status-badge <?= esc($statusClass($archiveStatus)) ?>">
                                    <?= esc($archiveStatus) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($isRestorationPending): ?>
                                    <span class="archive-status-badge bg-yellow-100 text-yellow-700">
                                        Restoration Requested
                                    </span>
                                <?php elseif ($workflowStatus === 'Pending Archive'): ?>
                                    <span class="archive-status-badge <?= esc($statusClass('Pending Archive')) ?>">
                                        Pending Archive
                                    </span>
                                <?php elseif ($status === 'Pending Disposal' || $status === 'Approved for Disposal'): ?>
                                    <span class="archive-status-badge <?= esc($statusClass($status)) ?>">
                                        <?= esc($status) ?>
                                    </span>
                                <?php elseif ($status === 'Disposed'): ?>
                                    <span class="archive-status-badge bg-gray-100 text-gray-700">
                                        Disposal Completed
                                    </span>
                                <?php else: ?>
                                    <span class="archive-placeholder">No active workflow</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($requestedBy ?: '-') ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($approvedBy ?: '-') ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="archive-actions">
                                    <a href="<?= route_to('records.show', $folderId) ?>" class="archive-action-text action-secondary">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        View
                                    </a>

                                    <?php if ($isRestorationPending): ?>
                                        <?php if (can('approve_restore')): ?>
                                            <form action="<?= route_to('restoration.approve', $pendingRestoration['restoration_request_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="Approve this restoration request?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text action-primary">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="<?= route_to('restoration.reject', $pendingRestoration['restoration_request_id']) ?>" method="POST" class="inline" data-bulk-action="reject" data-confirm-message="Reject this restoration request and keep folder archived?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text action-danger">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="archive-muted-action">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Pending
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($status === 'Pending Disposal' && can('approve_disposal') && !empty($record['disposal_id'])): ?>
                                            <form action="<?= route_to('disposal.approve', $record['disposal_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="Approve this disposal request?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text action-primary">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status === 'Approved for Disposal' && can('approve_disposal') && !empty($record['disposal_id'])): ?>
                                            <form action="<?= route_to('disposal.complete', $record['disposal_id']) ?>" method="POST" class="inline" data-bulk-action="approve" data-confirm-message="Mark this archive disposal as completed?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text" style="color: #64748b;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Complete
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status !== 'Disposed' && can('request_restore')): ?>
                                            <form action="<?= route_to('archive.restore', $folderId) ?>" method="POST" class="inline" data-bulk-action="restore" data-confirm-message="Submit a restoration request for this folder?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="archive-action-text action-primary">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    Restore
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

<script>
(function () {
    // Bulk actions functionality
    var selectAllCheckbox = document.getElementById('archiveSelectAll');
    var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.archive-row-checkbox'));
    var bulkActionsToolbar = document.getElementById('archiveBulkActionsToolbar');
    var bulkSelectedCount = document.getElementById('archiveBulkSelectedCount');
    var bulkActionButtons = Array.prototype.slice.call(document.querySelectorAll('.archive-bulk-action-btn'));

    bulkActionButtons.forEach(function (btn) {
        btn.dataset.baseLabel = btn.textContent.trim();
    });

    function getSelectedRows() {
        return rowCheckboxes
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.closest('tr'); })
            .filter(function (row) { return !!row; });
    }

    function getSelectedActionForms(action) {
        return getSelectedRows()
            .map(function (row) { return row.querySelector('form[data-bulk-action="' + action + '"]'); })
            .filter(function (form) { return !!form; });
    }

    function updateBulkActionButtons() {
        var selectedCount = rowCheckboxes.filter(function (cb) { return cb.checked; }).length;

        bulkActionButtons.forEach(function (btn) {
            var action = btn.dataset.action;
            var supportedCount = selectedCount > 0 ? getSelectedActionForms(action).length : 0;
            var baseLabel = btn.dataset.baseLabel || btn.textContent.trim();

            btn.textContent = baseLabel + (selectedCount > 0 ? ' (' + supportedCount + ')' : '');
            btn.disabled = selectedCount === 0 || supportedCount === 0;
        });
    }

    function updateBulkActionsDisplay() {
        var selectedCount = rowCheckboxes.filter(function (cb) { return cb.checked; }).length;
        bulkSelectedCount.textContent = selectedCount;
        
        if (selectedCount > 0) {
            bulkActionsToolbar.classList.remove('hidden');
        } else {
            bulkActionsToolbar.classList.add('hidden');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
        }

        updateBulkActionButtons();
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            var isChecked = this.checked;
            rowCheckboxes.forEach(function (cb) {
                if (!cb.parentElement.closest('tr').classList.contains('archive-row-hidden')) {
                    cb.checked = isChecked;
                }
            });
            updateBulkActionsDisplay();
        });
    }

    rowCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', updateBulkActionsDisplay);
    });

    async function executeBulkAction(action) {
        var selectedCheckboxes = rowCheckboxes.filter(function (cb) { return cb.checked; });
        if (selectedCheckboxes.length === 0) {
            alert('Please select at least one record.');
            return;
        }

        var forms = getSelectedActionForms(action);
        if (forms.length === 0) {
            alert('No selected records support this action.');
            return;
        }

        var actionLabel = action.charAt(0).toUpperCase() + action.slice(1);
        if (!window.confirm(actionLabel + ' ' + forms.length + ' selected record(s)?')) {
            return;
        }

        bulkActionButtons.forEach(function (btn) { btn.disabled = true; });

        var successCount = 0;
        var failureCount = 0;

        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            try {
                var response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    successCount++;
                } else {
                    failureCount++;
                }
            } catch (error) {
                failureCount++;
            }
        }

        updateBulkActionButtons();

        if (failureCount > 0) {
            alert('Bulk ' + action + ' completed with partial success. Success: ' + successCount + ', Failed: ' + failureCount + '.');
        }

        window.location.reload();
    }

    // Bulk action buttons
    bulkActionButtons.forEach(function (btn) {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            if (btn.disabled) {
                return;
            }
            var action = this.dataset.action;
            await executeBulkAction(action);
        });
    });

    updateBulkActionButtons();

    var searchInput = document.getElementById('archiveSearchInput');
    var modal = document.getElementById('archiveFiltersModal');
    var openModalButton = document.getElementById('openArchiveFiltersModal');
    var closeModalButton = document.getElementById('closeArchiveFiltersModal');
    var applyButton = document.getElementById('applyArchiveFiltersButton');
    var resetButton = document.getElementById('resetArchiveFiltersButton');
    var archiveStatusFilter = document.getElementById('modalArchiveStatusFilter');
    var workflowStatusFilter = document.getElementById('modalWorkflowStatusFilter');
    var quickFilterButtons = Array.prototype.slice.call(document.querySelectorAll('.archive-quick-filter'));
    var tableBody = document.querySelector('.archive-table tbody');
    var noResultsRow = document.getElementById('archiveNoResultsRow');
    var currentActivityFilter = 'all';

    if (!searchInput || !modal || !tableBody) {
        return;
    }

    var rows = Array.prototype.slice.call(tableBody.querySelectorAll('tr.archive-row'));

    function normalizeValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function rowMatches(row, filters) {
        var searchText = normalizeValue(row.dataset.searchText);
        var archiveStatus = normalizeValue(row.dataset.archiveStatus);
        var workflowStatus = normalizeValue(row.dataset.workflowStatus);
        var activityStage = normalizeValue(row.dataset.activityStage);

        if (filters.search !== '' && searchText.indexOf(filters.search) === -1) {
            return false;
        }

        if (filters.activity !== 'all' && activityStage !== filters.activity) {
            return false;
        }

        if (filters.archiveStatus !== '' && archiveStatus !== filters.archiveStatus) {
            return false;
        }

        if (filters.workflowStatus !== '' && workflowStatus !== filters.workflowStatus) {
            return false;
        }

        return true;
    }

    function applyFilters() {
        var filters = {
            search: normalizeValue(searchInput.value),
            activity: normalizeValue(currentActivityFilter || 'all') || 'all',
            archiveStatus: normalizeValue(archiveStatusFilter ? archiveStatusFilter.value : ''),
            workflowStatus: normalizeValue(workflowStatusFilter ? workflowStatusFilter.value : '')
        };

        var visibleCount = 0;

        rows.forEach(function (row) {
            var isVisible = rowMatches(row, filters);
            row.classList.toggle('archive-row-hidden', !isVisible);
            if (isVisible) {
                visibleCount++;
            }
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visibleCount !== 0);
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function setActiveQuickFilter(activityFilter) {
        currentActivityFilter = activityFilter || 'all';

        quickFilterButtons.forEach(function (button) {
            var isActive = (button.dataset.activityFilter || 'all') === currentActivityFilter;
            button.classList.toggle('is-active', isActive);
        });
    }

    openModalButton.addEventListener('click', openModal);
    closeModalButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    applyButton.addEventListener('click', function () {
        applyFilters();
        closeModal();
    });

    resetButton.addEventListener('click', function () {
        searchInput.value = '';
        if (archiveStatusFilter) {
            archiveStatusFilter.value = '';
        }
        if (workflowStatusFilter) {
            workflowStatusFilter.value = '';
        }
        setActiveQuickFilter('all');
        applyFilters();
        closeModal();
    });

    quickFilterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setActiveQuickFilter(button.dataset.activityFilter || 'all');
            applyFilters();
        });
    });

    var searchTimer = null;
    searchInput.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(applyFilters, 150);
    });

    if (archiveStatusFilter) {
        archiveStatusFilter.addEventListener('change', applyFilters);
    }

    if (workflowStatusFilter) {
        workflowStatusFilter.addEventListener('change', applyFilters);
    }

    setActiveQuickFilter('all');
    applyFilters();
})();
</script>

<?= $this->endSection() ?>
