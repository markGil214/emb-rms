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
                <th>Borrower</th>
                <th>Requested Date</th>
                <th>Expected Return</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="6">No pending requests</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending as $borrow): ?>
                    <tr>
                        <td><?= $borrow['transaction_id'] ?></td>
                        <td><?= $borrow['folder_id'] ?></td>
                        <td><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['created_at'])) ?></td>
                        <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                        <td>
                            <a href="/borrows/<?= $borrow['transaction_id'] ?>">View</a>
                            <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/approve" style="display: inline;">
                                <button type="submit">Approve</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
