<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Borrow History') ?></title>
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
        }

        * {
            box-sizing: border-box;
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
    <div class="container">
        <div class="top-bar">
            <a class="back-link" href="<?= route_to('records.show', $folder['folder_id']) ?>">Back to Document Details</a>

            <div class="folder-meta">
                Folder
                <strong><?= esc($folder['file_code']) ?> - <?= esc($folder['company_name']) ?></strong>
            </div>
        </div>

        <section class="card">
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
    </div>
</body>
</html>
