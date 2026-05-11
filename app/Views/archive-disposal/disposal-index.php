<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
    $statusCounts = $statusCounts ?? [];
    $disposalRows = $disposalRows ?? [];
    $fileDisposalPendingCount = (int) ($fileDisposalPendingCount ?? 0);
    $readyToDisposeCount = (int) ($readyToDisposeCount ?? 0);

    $summaryCards = [
        ['label' => 'Ready to Dispose', 'count' => $readyToDisposeCount, 'color' => 'rose'],
        ['label' => 'Archived', 'count' => (int) ($statusCounts['Archived'] ?? 0), 'color' => 'blue'],
        ['label' => 'Pending Disposal', 'count' => (int) ($statusCounts['Pending Disposal'] ?? 0), 'color' => 'orange'],
        ['label' => 'Approved for Disposal', 'count' => (int) ($statusCounts['Approved for Disposal'] ?? 0), 'color' => 'green'],
        ['label' => 'Disposed', 'count' => (int) ($statusCounts['Disposed'] ?? 0), 'color' => 'gray'],
        ['label' => 'Rejected', 'count' => (int) ($statusCounts['Rejected'] ?? 0), 'color' => 'red'],
        ['label' => 'Pending Disposal Requests', 'count' => $fileDisposalPendingCount, 'color' => 'amber'],
    ];

    $summaryCards = array_values(array_filter($summaryCards, static function (array $card): bool {
        return $card['count'] > 0;
    }));

    $statusClass = static function (string $status): string {
        $map = [
            'Archived' => 'bg-blue-100 text-blue-700',
            'Pending Disposal' => 'bg-orange-100 text-orange-700',
            'Approved for Disposal' => 'bg-green-100 text-green-700',
            'Disposed' => 'bg-gray-200 text-gray-700',
        ];

        return $map[$status] ?? 'bg-gray-100 text-gray-700';
    };

    $formatDate = static function ($value, string $format = 'M d, Y'): string {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date($format, $timestamp) : '-';
    };
?>

<style>
    .disposal-dashboard {
        display: grid;
        gap: 16px;
    }

    .disposal-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .disposal-summary-card {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .disposal-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        border-color: #d1d5db;
    }

    .disposal-summary-card::after {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        border-radius: 9999px 0 0 9999px;
    }

    .disposal-summary-card--blue::after { background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%); }
    .disposal-summary-card--orange::after { background: linear-gradient(180deg, #fdba74 0%, #f97316 100%); }
    .disposal-summary-card--green::after { background: linear-gradient(180deg, #86efac 0%, #16a34a 100%); }
    .disposal-summary-card--gray::after { background: linear-gradient(180deg, #cbd5e1 0%, #64748b 100%); }
    .disposal-summary-card--red::after { background: linear-gradient(180deg, #fca5a5 0%, #dc2626 100%); }
    .disposal-summary-card--amber::after { background: linear-gradient(180deg, #fde68a 0%, #d97706 100%); }
    .disposal-summary-card--rose::after { background: linear-gradient(180deg, #fb923c 0%, #dc2626 100%); }

    .disposal-panel {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 18px;
    }

    .disposal-toolbar {
        display: grid;
        grid-template-columns: minmax(260px, 420px) 1fr;
        gap: 12px;
        align-items: center;
    }

    .disposal-search-wrap {
        position: relative;
    }

    .disposal-search-input {
        width: 100%;
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        padding: 10px 14px 10px 42px;
        font-size: 14px;
        color: #111827;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .disposal-search-input:focus {
        outline: none;
        border-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.14);
    }

    .disposal-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        width: 18px;
        height: 18px;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    .disposal-quick-filters {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .disposal-quick-filter {
        min-height: 40px;
        border-radius: 9999px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease, box-shadow 150ms ease;
    }

    .disposal-quick-filter:hover {
        background: #f9fafb;
        color: #111827;
    }

    .disposal-quick-filter.is-active {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
    }

    .disposal-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
        letter-spacing: 0.01em;
    }

    .disposal-status-badge--pending {
        background: #fef3c7;
        color: #b45309;
    }

    .disposal-status-badge--approved {
        background: #dcfce7;
        color: #15803d;
    }

    .disposal-status-badge--disposed {
        background: #e5e7eb;
        color: #4b5563;
    }

    .disposal-status-badge--rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .disposal-status-badge--ready {
        background: linear-gradient(135deg, #fef3c7 0%, #fee2e2 100%);
        color: #c2410c;
        border: 1px solid #fdba74;
        animation: readyPulse 2s ease-in-out infinite;
    }

    @keyframes readyPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.3); }
        50% { box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.08); }
    }

    .disposal-status-badge--other {
        background: #f3f4f6;
        color: #374151;
    }

    .disposal-empty-state {
        padding: 64px 24px;
        text-align: center;
    }

    .dark .disposal-dashboard .disposal-panel {
        background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
        border-color: #374151;
    }

    .dark .disposal-dashboard .disposal-summary-card {
        background: #374151;
        border-color: #4b5563;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }

    .dark .disposal-dashboard .disposal-summary-card:hover {
        border-color: #6b7280;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
    }

    .dark .disposal-dashboard .disposal-summary-card p.text-sm {
        color: #9ca3af !important;
    }

    .dark .disposal-dashboard .disposal-summary-card p.text-3xl {
        color: #f3f4f6 !important;
    }
</style>

<div class="disposal-dashboard">
    <div class="disposal-panel archive-table-wrap p-6 sm:p-8">
        <div class="disposal-summary-grid">
            <?php foreach ($summaryCards as $card): ?>
                <div class="disposal-summary-card disposal-summary-card--<?= esc($card['color']) ?>">
                    <p class="text-sm font-semibold text-gray-700"><?= esc($card['label']) ?></p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= esc((string) $card['count']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="disposal-panel archive-table-wrap overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">File Disposal</h2>
                    <p class="text-sm text-gray-600">Track disposal requests, approvals, and completed outcomes in one workspace.</p>
                </div>

                <div class="disposal-toolbar">
                    <div class="disposal-search-wrap">
                        <input
                            type="text"
                            id="disposalSearchInput"
                            placeholder="Search by folder, company, location, type, status..."
                            class="disposal-search-input"
                            aria-label="Search disposal records"
                        />
                        <svg class="disposal-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="disposal-quick-filters" aria-label="Quick filters">
                        <button type="button" class="disposal-quick-filter is-active" data-status-filter="all">All</button>
                        <button type="button" class="disposal-quick-filter" data-status-filter="ready">Ready to Dispose</button>
                        <button type="button" class="disposal-quick-filter" data-status-filter="pending">Pending</button>
                        <button type="button" class="disposal-quick-filter" data-status-filter="approved">Approved</button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($disposalRows)): ?>
            <div class="disposal-empty-state">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No records to display</h3>
                <p class="text-gray-600 mb-4 max-w-2xl mx-auto">No disposal activity matches your current view. Requests appear here once archived records move into the disposal workflow.</p>
                <p class="text-sm text-gray-500">Use the pending approvals panel above or check back later as records enter the workflow.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table id="disposalRecordsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disposal Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disposition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewed By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="disposalRecordsBody" class="bg-white divide-y divide-gray-200">
                        <tr id="disposalNoResultsRow" class="hidden">
                            <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">No records match your current filters.</td>
                        </tr>
                        <?php foreach ($disposalRows as $record): ?>
                            <?php
                                $statusText = (string) ($record['status'] ?? 'Pending Disposal');
                                $statusKey = 'other';
                                if ($statusText === 'Ready to Dispose') {
                                    $statusKey = 'ready';
                                } elseif ($statusText === 'Pending Disposal') {
                                    $statusKey = 'pending';
                                } elseif ($statusText === 'Approved for Disposal') {
                                    $statusKey = 'approved';
                                }

                                $searchText = strtolower(trim(implode(' ', array_filter([
                                    (string) ($record['subject'] ?? ''),
                                    (string) ($record['location'] ?? ''),
                                    (string) ($record['folder_type'] ?? ''),
                                    (string) ($record['status'] ?? ''),
                                    (string) ($record['method'] ?? ''),
                                    (string) ($record['requested_by'] ?? ''),
                                    (string) ($record['approved_by'] ?? ''),
                                ]))));
                            ?>
                            <tr class="hover:bg-gray-50" data-disposal-row="1" data-status-key="<?= esc($statusKey, 'attr') ?>" data-search-text="<?= esc($searchText, 'attr') ?>">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium"><?= esc($record['subject'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= esc($record['location'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= esc($record['folder_type'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                        $statusText = (string) ($record['status'] ?? 'Pending Disposal');
                                        $statusBadgeClass = 'disposal-status-badge--other';
                                        if ($statusText === 'Ready to Dispose') {
                                            $statusBadgeClass = 'disposal-status-badge--ready';
                                        } elseif ($statusText === 'Pending Disposal') {
                                            $statusBadgeClass = 'disposal-status-badge--pending';
                                        } elseif ($statusText === 'Approved for Disposal') {
                                            $statusBadgeClass = 'disposal-status-badge--approved';
                                        } elseif ($statusText === 'Disposed') {
                                            $statusBadgeClass = 'disposal-status-badge--disposed';
                                        } elseif ($statusText === 'Rejected') {
                                            $statusBadgeClass = 'disposal-status-badge--rejected';
                                        }

                                        $shortStatusText = [
                                            'Ready to Dispose' => 'Ready to Dispose',
                                            'Pending Disposal' => 'Pending',
                                            'Approved for Disposal' => 'Approved',
                                            'Disposed' => 'Disposed',
                                            'Rejected' => 'Rejected',
                                        ][$statusText] ?? $statusText;
                                    ?>
                                    <span class="disposal-status-badge <?= esc($statusBadgeClass) ?>"><?= esc($shortStatusText) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= esc($record['method'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= esc($formatDate($record['requested_at'] ?? null)) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= esc($record['requested_by'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= esc($record['approved_by'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($record['view_route']) && !empty($record['view_id'])): ?>
                                            <?= view('components/button', [
                                                'label' => 'View',
                                                'url' => route_to($record['view_route'], (int) $record['view_id']),
                                                'style' => 'info'
                                            ]) ?>
                                        <?php endif; ?>

                                         <?php if (can('approve_disposal') && !empty($record['approve_route']) && !empty($record['approve_id'])): ?>
                                            <?= view('components/button', [
                                                'label' => 'Approve',
                                                'type' => 'submit',
                                                'style' => 'primary',
                                                'action' => route_to($record['approve_route'], (int) $record['approve_id']),
                                                'confirm' => $record['confirm_message'] ?? 'Approve this disposal request?'
                                            ]) ?>
                                        <?php endif; ?>

                                        <?php if (can('approve_disposal') && !empty($record['decline_route']) && !empty($record['decline_id'])): ?>
                                            <?= view('components/button', [
                                                'label' => 'Decline',
                                                'type' => 'submit',
                                                'style' => 'danger',
                                                'action' => route_to($record['decline_route'], (int) $record['decline_id']),
                                                'confirm' => $record['decline_confirm_message'] ?? 'Reject this disposal request?'
                                            ]) ?>
                                        <?php endif; ?>

                                        <?php if (($record['status'] ?? '') === 'Ready to Dispose' && !empty($record['file_id']) && can('request_disposal')): ?>
                                            <?= view('components/button', [
                                                'label' => 'Request Disposal',
                                                'type' => 'submit',
                                                'style' => 'warning',
                                                'action' => route_to('file.request-disposal', (int) $record['file_id']),
                                                'confirm' => 'Submit disposal request for this expired file?',
                                            ]) ?>
                                        <?php endif; ?>

                                        <?php if (
                                            ($record['status'] ?? '') !== 'Ready to Dispose' &&
                                            (empty($record['view_route']) || empty($record['view_id'])) && 
                                            (empty($record['approve_route']) || empty($record['approve_id'])) &&
                                            (empty($record['decline_route']) || empty($record['decline_id']))
                                        ): ?>
                                            <span class="text-gray-400">-</span>
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
</div>

<?php if (!empty($disposalRows)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('disposalSearchInput');
    var filterButtons = Array.prototype.slice.call(document.querySelectorAll('.disposal-quick-filter[data-status-filter]'));
    var rows = Array.prototype.slice.call(document.querySelectorAll('tr[data-disposal-row="1"]'));
    var noResultsRow = document.getElementById('disposalNoResultsRow');
    var activeFilter = 'all';

    if (!searchInput || rows.length === 0) {
        return;
    }

    var normalize = function (value) {
        return String(value || '').toLowerCase().trim();
    };

    var applyFilters = function () {
        var query = normalize(searchInput.value);
        var visibleCount = 0;

        rows.forEach(function (row) {
            var rowStatus = normalize(row.getAttribute('data-status-key'));
            var rowSearch = normalize(row.getAttribute('data-search-text'));

            var statusMatch = activeFilter === 'all' || rowStatus === activeFilter;
            var searchMatch = query === '' || rowSearch.indexOf(query) !== -1;
            var isVisible = statusMatch && searchMatch;

            row.classList.toggle('hidden', !isVisible);
            if (isVisible) {
                visibleCount++;
            }
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visibleCount !== 0);
        }
    };

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeFilter = normalize(button.getAttribute('data-status-filter')) || 'all';
            filterButtons.forEach(function (item) {
                item.classList.remove('is-active');
            });
            button.classList.add('is-active');
            applyFilters();
        });
    });

    searchInput.addEventListener('input', applyFilters);
    applyFilters();
});
</script>
<?php endif; ?>

<!-- ENHANCED DISPOSAL APPROVAL WITH FULL CONTEXT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Find all disposal approval forms (those with data-confirm-message)
    const disposalForms = document.querySelectorAll('form[data-confirm-message][action*="disposal.approve"], form[data-confirm-message][action*="disposal.complete"]');
    
    disposalForms.forEach(function (form) {
        // Find the submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        
        // Change to regular button to prevent default form submission
        const newBtn = document.createElement('button');
        newBtn.type = 'button';
        newBtn.className = submitBtn.className;
        newBtn.textContent = submitBtn.textContent;
        submitBtn.replaceWith(newBtn);
        
        // Get data from the form's data attributes (if available)
        const row = form.closest('tr');
        if (!row) return;
        
        newBtn.addEventListener('click', function (e) {
            e.preventDefault();
            
            // Extract record data from the table row
            const cells = row.querySelectorAll('td');
            const subject = cells[0]?.textContent?.trim() || 'Unknown';
            const status = cells[2]?.textContent?.trim() || 'Pending';
            const method = cells[3]?.textContent?.trim() || '-';
            const requestDate = cells[4]?.textContent?.trim() || '-';
            const requestedBy = cells[5]?.textContent?.trim() || 'Unknown';
            
            window.showDisposalApproval({
                disposalId: form.action.split('/').pop() || 'Unknown',
                folderCode: subject.split(' ')[0] || 'N/A',
                companyName: subject || 'N/A',
                disposalMethod: method,
                requestedBy: requestedBy,
                requestedDate: requestDate,
                currentStatus: status,
                message: form.getAttribute('data-confirm-message') || 'Approve this disposal request?',
                approveCallback: function () {
                    form.dataset.modalConfirmed = 'true';
                    form.submit();
                }
            });
        });
    });
});
</script>

<?= $this->endSection() ?>