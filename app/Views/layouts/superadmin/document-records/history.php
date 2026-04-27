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

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .folder-meta {
                text-align: left;
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
                <h1>Borrow History</h1>
                <span>Total: <?= count($history) ?></span>
            </div>

            <div class="card-body">
                <?php if (empty($history)): ?>
                    <div class="empty">
                        <h3>No borrow history found</h3>
                        <p>This folder has no borrow transactions yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Borrower</th>
                                    <th>Email</th>
                                    <th>Purpose</th>
                                    <th>Borrowed At</th>
                                    <th>Due Date</th>
                                    <th>Returned At</th>
                                    <th>Status</th>
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
                                            $statusLabel = $row['status'] ?: 'N/A';
                                            $statusClass = 'status-other';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= esc($row['borrower_name'] ?? 'N/A') ?></td>
                                        <td><?= esc($row['borrower_email'] ?? 'N/A') ?></td>
                                        <td><?= esc($row['purpose'] ?? 'N/A') ?></td>
                                        <td><?= !empty($row['borrowed_at']) ? esc(date('M d, Y H:i', strtotime($row['borrowed_at']))) : 'N/A' ?></td>
                                        <td><?= !empty($row['expected_return_date']) ? esc(date('M d, Y', strtotime($row['expected_return_date']))) : 'N/A' ?></td>
                                        <td><?= !empty($row['actual_return_date']) ? esc(date('M d, Y H:i', strtotime($row['actual_return_date']))) : 'N/A' ?></td>
                                        <td><span class="status-pill <?= esc($statusClass) ?>"><?= esc($statusLabel) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="relocationHistoryPanel" class="card history-panel" role="tabpanel">
            <div class="card-header">
                <h1>Relocation History</h1>
                <span>Total: <?= count($relocationHistory) ?></span>
            </div>

            <div class="card-body">
                <?php if (empty($relocationHistory)): ?>
                    <div class="empty">
                        <h3>No relocation history found</h3>
                        <p>This folder has no relocation requests or movement records yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Requested At</th>
                                    <th>From Location</th>
                                    <th>To Location</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Approved At</th>
                                    <th>Requested By</th>
                                    <th>Approved By</th>
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
                                        $approvedByValue = $historyKind === 'movement'
                                            ? ($row['completed_by_username'] ?? $row['approved_by_username'] ?? $row['requested_by_username'] ?? 'N/A')
                                            : ($row['approved_by_username'] ?? 'N/A');
                                        $requestedByValue = $row['requested_by_username'] ?? 'N/A';
                                    ?>
                                    <tr>
                                        <td><?= !empty($dateValue) ? esc(date('M d, Y H:i', strtotime($dateValue))) : 'N/A' ?></td>
                                        <td><?= esc($formatLocationLabel($row, 'from_')) ?></td>
                                        <td><?= esc($formatLocationLabel($row, 'to_')) ?></td>
                                        <td><?= esc($row['reason'] ?? ($row['request_reason'] ?? 'N/A')) ?></td>
                                        <td><span class="status-pill <?= esc($statusClass) ?>"><?= esc($statusLabel) ?></span></td>
                                        <td>
                                            <?php if ($historyKind === 'movement' && ! empty($row['completed_at'])): ?>
                                                <?= esc(date('M d, Y H:i', strtotime($row['completed_at']))) ?>
                                            <?php elseif (!empty($row['approved_at'])): ?>
                                                <?= esc(date('M d, Y H:i', strtotime($row['approved_at']))) ?>
                                            <?php elseif (!empty($row['rejection_reason']) && in_array(strtolower((string) ($row['status'] ?? '')), ['declined', 'rejected'], true)): ?>
                                                Rejected
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($requestedByValue) ?></td>
                                        <td><?= esc($approvedByValue) ?></td>
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

            activatePanel('borrowHistoryPanel');
        })();
    </script>
</body>
</html>
