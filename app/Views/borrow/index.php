<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $title ?></h1>

    <?php if (session()->getFlashdata('success')): ?>
        <p><strong>Success:</strong> <?= esc(session()->getFlashdata('success')) ?></p>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <p><strong>Error:</strong> <?= esc(session()->getFlashdata('error')) ?></p>
    <?php endif; ?>
    
    <p><a href="/borrows/create">+ Request Borrow</a></p>

    <h3>Pending Requests: <?= $totalPending ?></h3>
    <h3>Active Borrows: <?= $totalBorrowed ?></h3>
    <h3>Overdue Items: <?= $totalOverdue ?></h3>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Folder</th>
                <th>Borrower</th>
                <th>Borrowed Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($borrows)): ?>
                <tr>
                    <td colspan="7">No borrow records found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($borrows as $borrow): ?>
                    <?php if ($borrow['status'] !== 'Returned'): ?>
                    <tr style="<?= $borrow['status'] === 'Overdue' ? 'background-color: #ffcccc;' : '' ?>">
                        <td><?= $borrow['transaction_id'] ?></td>
                        <td>
                            <?php if (isset($folderMap[$borrow['folder_id']])): ?>
                                <strong><?= esc($folderMap[$borrow['folder_id']]['file_code']) ?></strong> - <?= esc($folderMap[$borrow['folder_id']]['company_name']) ?>
                            <?php else: ?>
                                <?= esc($borrow['folder_id']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['borrowed_at'])) ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                        <td><?= $borrow['status'] ?></td>
                        <td>
                            <a href="/borrows/<?= $borrow['transaction_id'] ?>">View</a>
                            <?php if ($borrow['status'] === 'Pending' && can('approve_borrow_requests')): ?>
                                <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/approve" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit">Approve</button>
                                </form>
                            <?php elseif ($borrow['status'] === 'Borrowed' || $borrow['status'] === 'Overdue'): ?>
                                <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit">Return</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
