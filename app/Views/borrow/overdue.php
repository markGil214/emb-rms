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

    <p><strong>Alert:</strong> These items are overdue and need to be returned immediately.</p>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Folder</th>
                <th>Borrower</th>
                <th>Borrowed Date</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($overdue)): ?>
                <tr>
                    <td colspan="7">No overdue items</td>
                </tr>
            <?php else: ?>
                <?php foreach ($overdue as $borrow): ?>
                    <tr>
                        <td><?= $borrow['transaction_id'] ?></td>
                        <td><?= $borrow['folder_id'] ?></td>
                        <td><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['borrowed_at'])) ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                        <td><?= (int) ((time() - strtotime($borrow['expected_return_date'])) / 86400) ?> days</td>
                        <td>
                            <a href="/borrows/<?= $borrow['transaction_id'] ?>">View</a>
                            <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" style="display: inline;">
                                <button type="submit">Mark Return</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
