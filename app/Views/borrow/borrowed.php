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

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Folder</th>
                <th>Borrower Name</th>
                <th>Borrowed Date</th>
                <th>Expected Return</th>
                <th>Days Remaining</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($borrowed)): ?>
                <tr>
                    <td colspan="7">No currently borrowed items</td>
                </tr>
            <?php else: ?>
                <?php foreach ($borrowed as $borrow): ?>
                    <?php
                        $daysRemaining = ceil((strtotime($borrow['expected_return_date']) - time()) / (24 * 60 * 60));
                        $isOverdue = $daysRemaining < 0;
                    ?>
                    <tr style="<?= $isOverdue ? 'background-color: #ffcccc;' : '' ?>">
                        <td><?= $borrow['transaction_id'] ?></td>
                        <td><?= $borrow['folder_id'] ?></td>
                        <td><?= $borrow['borrower_name'] ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['borrowed_at'])) ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                        <td style="text-align: center; font-weight: bold; <?= $isOverdue ? 'color: red;' : 'color: green;' ?>">
                            <?= abs($daysRemaining) ?> <?= $isOverdue ? 'days overdue' : 'days left' ?>
                        </td>
                        <td>
                            <a href="/borrows/<?= $borrow['transaction_id'] ?>">View</a>
                            <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit">Return</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="/borrows">Back to All Requests</a></p>
</body>
</html>