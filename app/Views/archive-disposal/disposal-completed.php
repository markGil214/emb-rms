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

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Archive ID</th>
                <th>Method</th>
                <th>Requested Date</th>
                <th>Completed Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($completed)): ?>
                <tr>
                    <td colspan="6">No completed disposals</td>
                </tr>
            <?php else: ?>
                <?php foreach ($completed as $disposal): ?>
                    <tr>
                        <td><?= $disposal['disposal_id'] ?></td>
                        <td><?= $disposal['archive_id'] ?? '-' ?></td>
                        <td><?= $disposal['disposal_method'] ?></td>
                        <td><?= date('M d, Y', strtotime($disposal['created_at'])) ?></td>
                        <td><?= date('M d, Y', strtotime($disposal['disposal_date'])) ?></td>
                        <td>
                            <a href="/archive-disposal/disposal/<?= $disposal['disposal_id'] ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
