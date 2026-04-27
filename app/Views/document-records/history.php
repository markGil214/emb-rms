<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Borrow & Relocation History') ?></title>
    <style>
        :root {
            --bg: #f4f6f8;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --header: #f9fafb;
            --brand: #1d4ed8;
            --brand-hover: #1e40af;
            --ok-bg: #dcfce7;
            --ok-text: #166534;
            --warn-bg: #fef3c7;
            --warn-text: #92400e;
            --bad-bg: #fee2e2;
            --bad-text: #991b1b;
            --neutral-bg: #f3f4f6;
            --neutral-text: #374151;
            font-size: 12px !important;
        }

        * {
            box-sizing: border-box;
            font-size: 12px !important;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 1200px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .back-link {
            display: inline-block;
            text-decoration: none;
            background: var(--brand);
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link:hover {
            background: var(--brand-hover);
        }

        .folder-meta {
            text-align: right;
            font-size: 14px;
            color: var(--muted);
        }

        .folder-meta strong {
            display: block;
            color: var(--text);
            margin-top: 2px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            background: var(--header);
            border-bottom: 1px solid var(--line);
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h1 {
            margin: 0;
            font-size: 20px;
        }

        .card-body {
            padding: 16px;
        }

        .empty {
            padding: 32px 10px;
            text-align: center;
            color: var(--muted);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th,
        td {
            border: 1px solid var(--line);
            padding: 10px 12px;
            font-size: 14px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: var(--header);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--muted);
        }

        tr:nth-child(even) {
            background: #fcfcfd;
        }

        .status-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-overdue {
            background: var(--bad-bg);
            color: var(--bad-text);
        }

        .status-returned {
            background: var(--ok-bg);
            color: var(--ok-text);
        }

        .status-borrowed {
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        .status-other {
            background: var(--neutral-bg);
            color: var(--neutral-text);
        }

        .history-tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--line);
            margin-bottom: 16px;
            overflow-x: auto;
        }

        .history-tab-button {
            appearance: none;
            border: 0;
            background: transparent;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
            border-bottom: 2px solid transparent;
            cursor: pointer;
            white-space: nowrap;
        }

        .history-tab-button.active {
            color: var(--brand);
            border-bottom-color: var(--brand);
        }

        .history-panel {
            display: none;
        }

        .history-panel.active {
            display: block;
        }

        .info-banner {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 13px;
        }

        .history-tools {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 14px;
        }

        .history-tools select,
        .history-tools input {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            background: #fff;
            color: var(--text);
        }

        .history-tools input[type="search"] {
            min-width: 230px;
            flex: 1;
        }

        .location-change {
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
        }

        .location-arrow {
            color: var(--brand);
            font-weight: 700;
            margin: 0 6px;
        }

        .date-primary {
            font-weight: 600;
            color: var(--text);
        }

        .date-kind {
            margin-top: 2px;
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .reason-cell {
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .filtered-empty {
            display: none;
            padding: 16px 10px;
            text-align: center;
            color: var(--muted);
            border: 1px dashed var(--line);
            border-radius: 8px;
            margin-top: 10px;
        }

        .borrow-overdue-row td {
            background: #fff7f7;
        }

        .borrow-purpose-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .folder-meta {
                text-align: left;
            }

            .history-tools input[type="search"] {
                min-width: 100%;
                flex: none;
            }

            .location-change {
                white-space: normal;
            }
        }
    </style>
</head>
<body>
    <?php
        $relocationHistory = $relocationHistory ?? [];

        $formatLocationLabel = static function (array $row, string $prefix): string {
            if (($row['history_kind'] ?? '') === 'movement') {
                if ($prefix === 'from_' && ! empty($row['from_location_label'])) {
                    return (string) $row['from_location_label'];
                }

                if ($prefix === 'to_' && ! empty($row['to_location_label'])) {
                    return (string) $row['to_location_label'];
                }
            }

            $rackKey = $prefix . 'rack';
            $shelfKey = $prefix . 'shelf';
            $rack = trim((string) ($row[$rackKey] ?? ''));
            $shelf = trim((string) ($row[$shelfKey] ?? ''));

            if ($rack !== '' && $shelf !== '') {
                return 'RACK ' . $rack . ' - SHELF ' . $shelf;
            }

            if ($rack !== '') {
                return 'RACK ' . $rack;
            }

            if ($shelf !== '') {
                return 'SHELF ' . $shelf;
            }

            $parts = [];

            foreach (['building', 'room', 'rack', 'shelf'] as $field) {
                $key = $prefix . $field;
                if (! empty($row[$key])) {
                    $label = ucfirst($field);
                    $parts[] = $label . ': ' . $row[$key];
                }
            }

            if (! empty($parts)) {
                return implode(', ', $parts);
            }

            return 'N/A';
        };

        $formatRelocationStatus = static function (string $status = null, string $historyKind = 'request'): array {
            $normalized = strtolower(trim((string) $status));

            if ($historyKind === 'movement') {
                if ($normalized === 'completed') {
                    return ['Completed', 'status-returned'];
                }

                if ($normalized === 'in progress') {
                    return ['In Progress', 'status-borrowed'];
                }
            }

            if ($normalized === 'pending') {
                return ['Pending', 'status-borrowed'];
            }

            if ($normalized === 'approved' || $normalized === 'completed') {
                return [ucfirst($normalized), 'status-returned'];
            }

            if ($normalized === 'declined' || $normalized === 'rejected') {
                return ['Declined', 'status-overdue'];
            }

            if ($normalized === 'in progress') {
                return ['In Progress', 'status-other'];
            }

            return [$status ?: 'N/A', 'status-other'];
        };
    ?>
    <div class="container">
        <div class="top-bar">
            <a class="back-link" href="<?= route_to('records.show', $folder['folder_id']) ?>">Back to Document Details</a>

            <div class="folder-meta">
                Folder
                <strong><?= esc($folder['file_code']) ?> - <?= esc($folder['company_name']) ?></strong>
            </div>
        </div>

        <div class="history-tabs" role="tablist" aria-label="Folder history tabs">
            <button type="button" class="history-tab-button active" data-target="borrowHistoryPanel">Borrow History</button>
            <button type="button" class="history-tab-button" data-target="relocationHistoryPanel">Relocation History</button>
        </div>

        <section id="borrowHistoryPanel" class="card history-panel active" role="tabpanel">
            <div class="card-header">
                <h1>Borrow History (<span id="borrowVisibleCount"><?= count($history) ?></span> records)</h1>
            </div>

            <div class="card-body">
                <?php if (empty($history)): ?>
                    <div class="empty">
                        <h3>No borrow history found</h3>
                        <p>This folder has no borrow transactions yet.</p>
                    </div>
                <?php else: ?>
                    <div class="info-banner">This shows all borrow and return activity for this folder.</div>

                    <div class="history-tools" aria-label="Borrow history filters">
                        <select id="borrowStatusFilter" aria-label="Filter by borrow status">
                            <option value="all">All Status</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="returned">Returned</option>
                            <option value="overdue">Overdue</option>
                            <option value="declined">Declined</option>
                        </select>
                        <input type="date" id="borrowDateFrom" aria-label="Filter from borrowed date">
                        <input type="date" id="borrowDateTo" aria-label="Filter to borrowed date">
                        <input type="search" id="borrowSearch" placeholder="Search borrower" aria-label="Search borrower">
                        <select id="borrowSort" aria-label="Sort borrow history by borrowed date">
                            <option value="newest">Newest first</option>
                            <option value="oldest">Oldest first</option>
                        </select>
                    </div>

                    <div class="table-wrap">
                        <table id="borrowHistoryTable">
                            <thead>
                                <tr>
                                    <th>Borrowed</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Borrower</th>
                                    <th>Purpose</th>
                                    <th>Returned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $row): ?>
                                    <?php
                                        $isOverdue = (
                                            $row['status'] === 'Borrowed' &&
                                            empty($row['actual_return_date']) &&
                                            !empty($row['expected_return_date']) &&
                                            strtotime($row['expected_return_date']) < time()
                                        );

                                        if ($isOverdue) {
                                            $statusLabel = 'Overdue';
                                            $statusClass = 'status-overdue';
                                        } elseif ($row['status'] === 'Returned') {
                                            $statusLabel = 'Returned';
                                            $statusClass = 'status-returned';
                                        } elseif ($row['status'] === 'Borrowed') {
                                            $statusLabel = 'Borrowed';
                                            $statusClass = 'status-borrowed';
                                        } else {
                                            $statusLabel = $row['status'] ?: 'Unknown';
                                            $statusClass = 'status-other';
                                        }

                                        $borrowedDate = !empty($row['borrowed_at']) ? date('M d, Y H:i', strtotime($row['borrowed_at'])) : '--';
                                        $dueDate = !empty($row['expected_return_date']) ? date('M d, Y', strtotime($row['expected_return_date'])) : '--';
                                        $returnedDate = !empty($row['actual_return_date']) ? date('M d, Y H:i', strtotime($row['actual_return_date'])) : 'Not returned';
                                        $purposeText = (string) ($row['purpose'] ?? '--');
                                        $borrowerValue = (string) ($row['borrower_name'] ?? 'Unknown');

                                        if (strlen($purposeText) > 80) {
                                            $purposeShort = substr($purposeText, 0, 77) . '...';
                                        } else {
                                            $purposeShort = $purposeText;
                                        }

                                        $borrowDateStamp = ! empty($row['borrowed_at']) ? date('Y-m-d', strtotime($row['borrowed_at'])) : '';
                                        $borrowDateTs = ! empty($row['borrowed_at']) ? (string) strtotime($row['borrowed_at']) : '0';
                                    ?>
                                    <tr class="borrow-row <?= $isOverdue ? 'borrow-overdue-row' : '' ?>"
                                        data-status="<?= esc(strtolower($statusLabel), 'attr') ?>"
                                        data-date="<?= esc($borrowDateStamp, 'attr') ?>"
                                        data-date-ts="<?= esc($borrowDateTs, 'attr') ?>"
                                        data-search="<?= esc(strtolower($borrowerValue), 'attr') ?>">
                                        <td><?= esc($borrowedDate) ?></td>
                                        <td><?= esc($dueDate) ?></td>
                                        <td><span class="status-pill <?= esc($statusClass) ?>"><?= esc($statusLabel) ?></span></td>
                                        <td><?= esc($borrowerValue) ?></td>
                                        <td class="borrow-purpose-cell" title="<?= esc($purposeText, 'attr') ?>"><?= esc($purposeShort) ?></td>
                                        <td><?= esc($returnedDate) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="borrowFilteredEmpty" class="filtered-empty">No records match the selected filters.</div>
                <?php endif; ?>
            </div>
        </section>

        <section id="relocationHistoryPanel" class="card history-panel" role="tabpanel">
            <div class="card-header">
                <h1>Relocation History (<span id="relocationVisibleCount"><?= count($relocationHistory) ?></span> records)</h1>
            </div>

            <div class="card-body">
                <?php if (empty($relocationHistory)): ?>
                    <div class="empty">
                        <h3>No relocation history found</h3>
                        <p>This folder has no relocation requests or movement records yet.</p>
                    </div>
                <?php else: ?>
                    <div class="info-banner">This shows all relocation requests and movement events for this folder.</div>

                    <div class="history-tools" aria-label="Relocation history filters">
                        <select id="relocationStatusFilter" aria-label="Filter by relocation status">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="declined">Declined</option>
                            <option value="completed">Completed</option>
                            <option value="in progress">In Progress</option>
                        </select>
                        <input type="date" id="relocationDateFrom" aria-label="Filter from date">
                        <input type="date" id="relocationDateTo" aria-label="Filter to date">
                        <input type="search" id="relocationSearch" placeholder="Search rack, shelf, user, or reason" aria-label="Search relocation history">
                        <select id="relocationSort" aria-label="Sort relocation history by date">
                            <option value="newest">Newest first</option>
                            <option value="oldest">Oldest first</option>
                        </select>
                    </div>

                    <div class="table-wrap">
                        <table id="relocationHistoryTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Location Change</th>
                                    <th>Status</th>
                                    <th>Reviewed By</th>
                                    <th>Reviewed At</th>
                                    <th>Requested By</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relocationHistory as $row): ?>
                                    <?php
                                        $historyKind = $row['history_kind'] ?? 'request';
                                        $statusSource = $historyKind === 'movement'
                                            ? ($row['folder_status_at_completion'] ?? $row['relocation_status'] ?? $row['status'] ?? null)
                                            : ($row['status'] ?? null);
                                        [$statusLabel, $statusClass] = $formatRelocationStatus($statusSource, (string) $historyKind);
                                        $dateValue = $row['history_date'] ?? ($row['requested_at'] ?? $row['completed_at'] ?? $row['approved_at'] ?? $row['moved_at'] ?? null);
                                        $reviewedByValue = $historyKind === 'movement'
                                            ? ($row['completed_by_username'] ?? $row['approved_by_username'] ?? $row['requested_by_username'] ?? 'N/A')
                                            : ($row['approved_by_username'] ?? $row['requested_by_username'] ?? 'N/A');
                                        $requestedByValue = $row['requested_by_username'] ?? 'N/A';
                                        $fromLocation = $formatLocationLabel($row, 'from_');
                                        $toLocation = $formatLocationLabel($row, 'to_');
                                        $reasonText = (string) ($row['reason'] ?? ($row['request_reason'] ?? 'N/A'));

                                        if (strlen($reasonText) > 70) {
                                            $reasonShort = substr($reasonText, 0, 67) . '...';
                                        } else {
                                            $reasonShort = $reasonText;
                                        }

                                        if ($historyKind === 'movement' && ! empty($row['completed_at'])) {
                                            $reviewedAtValue = date('M d, Y H:i', strtotime($row['completed_at']));
                                        } elseif (! empty($row['approved_at'])) {
                                            $reviewedAtValue = date('M d, Y H:i', strtotime($row['approved_at']));
                                        } elseif (in_array(strtolower((string) ($row['status'] ?? '')), ['declined', 'rejected'], true) && ! empty($row['updated_at'])) {
                                            $reviewedAtValue = date('M d, Y H:i', strtotime($row['updated_at']));
                                        } else {
                                            $reviewedAtValue = 'N/A';
                                        }

                                        $dateStamp = ! empty($dateValue) ? date('Y-m-d', strtotime($dateValue)) : '';
                                        $dateTs = ! empty($dateValue) ? (string) strtotime($dateValue) : '0';
                                        $searchText = strtolower(trim($fromLocation . ' ' . $toLocation . ' ' . $reasonText . ' ' . $requestedByValue . ' ' . $reviewedByValue));
                                    ?>
                                    <tr class="relocation-row"
                                        data-status="<?= esc(strtolower($statusLabel), 'attr') ?>"
                                        data-date="<?= esc($dateStamp, 'attr') ?>"
                                        data-date-ts="<?= esc($dateTs, 'attr') ?>"
                                        data-search="<?= esc($searchText, 'attr') ?>">
                                        <td>
                                            <div class="date-primary"><?= !empty($dateValue) ? esc(date('M d, Y H:i', strtotime($dateValue))) : 'N/A' ?></div>
                                            <div class="date-kind"><?= esc($historyKind === 'movement' ? 'Movement' : 'Request') ?></div>
                                        </td>
                                        <td>
                                            <span class="location-change">
                                                <?= esc($fromLocation) ?><span class="location-arrow">&#8594;</span><?= esc($toLocation) ?>
                                            </span>
                                        </td>
                                        <td><span class="status-pill <?= esc($statusClass) ?>"><?= esc($statusLabel) ?></span></td>
                                        <td><?= esc($reviewedByValue) ?></td>
                                        <td><?= esc($reviewedAtValue) ?></td>
                                        <td><?= esc($requestedByValue) ?></td>
                                        <td class="reason-cell" title="<?= esc($reasonText, 'attr') ?>"><?= esc($reasonShort) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="relocationFilteredEmpty" class="filtered-empty">No records match the selected filters.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <script>
        (function () {
            var buttons = document.querySelectorAll('.history-tab-button');
            var panels = document.querySelectorAll('.history-panel');

            function activatePanel(targetId) {
                Array.prototype.forEach.call(buttons, function (button) {
                    button.classList.toggle('active', button.getAttribute('data-target') === targetId);
                });

                Array.prototype.forEach.call(panels, function (panel) {
                    panel.classList.toggle('active', panel.id === targetId);
                });
            }

            Array.prototype.forEach.call(buttons, function (button) {
                button.addEventListener('click', function () {
                    activatePanel(button.getAttribute('data-target'));
                });
            });

            function initBorrowFilters() {
                var table = document.getElementById('borrowHistoryTable');
                if (!table) {
                    return;
                }

                var body = table.tBodies[0];
                if (!body) {
                    return;
                }

                var statusFilter = document.getElementById('borrowStatusFilter');
                var dateFrom = document.getElementById('borrowDateFrom');
                var dateTo = document.getElementById('borrowDateTo');
                var searchInput = document.getElementById('borrowSearch');
                var sortSelect = document.getElementById('borrowSort');
                var visibleCount = document.getElementById('borrowVisibleCount');
                var filteredEmpty = document.getElementById('borrowFilteredEmpty');
                var rows = Array.prototype.slice.call(body.querySelectorAll('.borrow-row'));

                function applyBorrowFilters() {
                    var statusValue = statusFilter ? statusFilter.value.toLowerCase() : 'all';
                    var fromValue = dateFrom ? dateFrom.value : '';
                    var toValue = dateTo ? dateTo.value : '';
                    var searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
                    var sortValue = sortSelect ? sortSelect.value : 'newest';
                    var visibleRows = [];

                    rows.forEach(function (row) {
                        var rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                        var rowDate = row.getAttribute('data-date') || '';
                        var rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

                        var statusMatch = statusValue === 'all' || rowStatus === statusValue;
                        var fromMatch = !fromValue || (rowDate && rowDate >= fromValue);
                        var toMatch = !toValue || (rowDate && rowDate <= toValue);
                        var searchMatch = !searchValue || rowSearch.indexOf(searchValue) !== -1;
                        var shouldShow = statusMatch && fromMatch && toMatch && searchMatch;

                        row.style.display = shouldShow ? '' : 'none';
                        if (shouldShow) {
                            visibleRows.push(row);
                        }
                    });

                    visibleRows.sort(function (a, b) {
                        var aTs = parseInt(a.getAttribute('data-date-ts') || '0', 10);
                        var bTs = parseInt(b.getAttribute('data-date-ts') || '0', 10);
                        if (sortValue === 'oldest') {
                            return aTs - bTs;
                        }
                        return bTs - aTs;
                    });

                    visibleRows.forEach(function (row) {
                        body.appendChild(row);
                    });

                    if (visibleCount) {
                        visibleCount.textContent = String(visibleRows.length);
                    }

                    if (filteredEmpty) {
                        filteredEmpty.style.display = visibleRows.length === 0 ? 'block' : 'none';
                    }
                }

                [statusFilter, dateFrom, dateTo, searchInput, sortSelect].forEach(function (control) {
                    if (control) {
                        control.addEventListener('input', applyBorrowFilters);
                        control.addEventListener('change', applyBorrowFilters);
                    }
                });

                applyBorrowFilters();
            }

            function initRelocationFilters() {
                var table = document.getElementById('relocationHistoryTable');
                if (!table) {
                    return;
                }

                var body = table.tBodies[0];
                if (!body) {
                    return;
                }

                var statusFilter = document.getElementById('relocationStatusFilter');
                var dateFrom = document.getElementById('relocationDateFrom');
                var dateTo = document.getElementById('relocationDateTo');
                var searchInput = document.getElementById('relocationSearch');
                var sortSelect = document.getElementById('relocationSort');
                var visibleCount = document.getElementById('relocationVisibleCount');
                var filteredEmpty = document.getElementById('relocationFilteredEmpty');
                var rows = Array.prototype.slice.call(body.querySelectorAll('.relocation-row'));

                function applyRelocationFilters() {
                    var statusValue = statusFilter ? statusFilter.value.toLowerCase() : 'all';
                    var fromValue = dateFrom ? dateFrom.value : '';
                    var toValue = dateTo ? dateTo.value : '';
                    var searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
                    var sortValue = sortSelect ? sortSelect.value : 'newest';
                    var visibleRows = [];

                    rows.forEach(function (row) {
                        var rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                        var rowDate = row.getAttribute('data-date') || '';
                        var rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

                        var statusMatch = statusValue === 'all' || rowStatus === statusValue;
                        var fromMatch = !fromValue || (rowDate && rowDate >= fromValue);
                        var toMatch = !toValue || (rowDate && rowDate <= toValue);
                        var searchMatch = !searchValue || rowSearch.indexOf(searchValue) !== -1;
                        var shouldShow = statusMatch && fromMatch && toMatch && searchMatch;

                        row.style.display = shouldShow ? '' : 'none';
                        if (shouldShow) {
                            visibleRows.push(row);
                        }
                    });

                    visibleRows.sort(function (a, b) {
                        var aTs = parseInt(a.getAttribute('data-date-ts') || '0', 10);
                        var bTs = parseInt(b.getAttribute('data-date-ts') || '0', 10);
                        if (sortValue === 'oldest') {
                            return aTs - bTs;
                        }
                        return bTs - aTs;
                    });

                    visibleRows.forEach(function (row) {
                        body.appendChild(row);
                    });

                    if (visibleCount) {
                        visibleCount.textContent = String(visibleRows.length);
                    }

                    if (filteredEmpty) {
                        filteredEmpty.style.display = visibleRows.length === 0 ? 'block' : 'none';
                    }
                }

                [statusFilter, dateFrom, dateTo, searchInput, sortSelect].forEach(function (control) {
                    if (control) {
                        control.addEventListener('input', applyRelocationFilters);
                        control.addEventListener('change', applyRelocationFilters);
                    }
                });

                applyRelocationFilters();
            }

            initBorrowFilters();
            initRelocationFilters();

            activatePanel('borrowHistoryPanel');
        })();
    </script>
</body>
</html>
