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

    <h2>Borrow Details</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th align="left">Transaction ID</th>
            <td><?= $borrow['transaction_id'] ?></td>
        </tr>
        <tr>
            <th align="left">Folder</th>
            <td><strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?></td>
        </tr>
        <tr>
            <th align="left">Borrower</th>
            <td><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
        </tr>
        <tr>
            <th align="left">Borrowed Date</th>
            <td><?= date('M d, Y g:i A', strtotime($borrow['borrowed_at'] ?? now())) ?></td>
        </tr>
        <tr>
            <th align="left">Expected Return</th>
            <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
        </tr>
        <tr>
            <th align="left">Actual Return</th>
            <td>
                <?php if ($borrow['actual_return_date']): ?>
                    <?= date('M d, Y g:i A', strtotime($borrow['actual_return_date'])) ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th align="left">Status</th>
            <td><?= $borrow['status'] ?></td>
        </tr>
        <?php if (isset($borrow['notes']) && $borrow['notes']): ?>
            <tr>
                <th align="left">Notes</th>
                <td><?= nl2br(htmlspecialchars($borrow['notes'])) ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <p>Borrower Signature: ____________________</p>
    <p>Administrator: ____________________</p>

    <?php if ($borrow['status'] === 'Pending' && can('approve_borrow_requests')): ?>
        <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/approve">
            <?= csrf_field() ?>
            <button type="submit">Approve Request</button>
        </form>
    <?php elseif (($borrow['status'] === 'Borrowed' || $borrow['status'] === 'Overdue') && can('process_return')): ?>
        <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return">
            <?= csrf_field() ?>
            <button type="submit">Process Return</button>
        </form>
    <?php elseif ($borrow['status'] === 'Returned'): ?>
        <p>Item Returned</p>
    <?php endif; ?>

    <p><a href="/borrows">Back to List</a></p>
</body>
</html>
