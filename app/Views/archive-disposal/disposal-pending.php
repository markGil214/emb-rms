<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
</head>
<body>
<div>
    <h1><?= $title ?></h1>

    <div>
        <strong>Action Needed:</strong> The following disposal requests are pending approval.
    </div>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Archive ID</th>
                <th>Method</th>
                <th>Requested Date</th>
                <th>Requested By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="6">No pending disposal requests</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending as $disposal): ?>
                    <tr>
                        <td><?= $disposal['disposal_id'] ?></td>
                        <td><?= $disposal['archive_id'] ?? '-' ?></td>
                        <td><?= $disposal['disposal_method'] ?></td>
                        <td><?= date('M d, Y', strtotime($disposal['created_at'])) ?></td>
                        <td><?= $disposal['requested_by'] ?? '-' ?></td>
                        <td>
                            <a href="/archive-disposal/disposal/<?= $disposal['disposal_id'] ?>">View</a>
                            <form method="POST" action="/archive-disposal/disposal/<?= $disposal['disposal_id'] ?>/approve">
                                <?= csrf_field() ?>
                                <button type="submit">Approve</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
