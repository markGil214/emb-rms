<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
</head>
<body>
    <h1><?= $title ?></h1>

    <h2>Summary</h2>
    <ul>
        <li>Total Archived: <?= $totalArchived ?></li>
        <li>Disposal Pending: <?= $totalDisposalPending ?></li>
        <li>Disposal Approved: <?= $totalDisposalApproved ?></li>
    </ul>

    <h2>Quick Actions</h2>
    <ul>
        <li><a href="/archive-disposal/create-archive">Archive Document</a></li>
        <li><a href="/archive-disposal/create-disposal">Request Disposal</a></li>
        <li><a href="/archive-disposal/search">Search Archives</a></li>
        <li><a href="/archive-disposal/disposal/pending">View Pending Disposal</a></li>
        <li><a href="/archive-disposal/disposal/completed">View Completed Disposal</a></li>
    </ul>

    <h2>Recently Archived</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>File Code</th>
                <th>Company</th>
                <th>Archive Location</th>
                <th>Archive Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($archived)): ?>
                <tr>
                    <td colspan="5">No archived documents</td>
                </tr>
            <?php else: ?>
                <?php foreach (array_slice($archived, 0, 5) as $archive): ?>
                    <tr>
                        <td><?= $archive['folder_id'] ?></td>
                        <td>Archive <?= $archive['archive_id'] ?></td>
                        <td><?= $archive['archive_location'] ?? '-' ?></td>
                        <td><?= isset($archive['archive_date']) ? date('M d, Y', strtotime($archive['archive_date'])) : '-' ?></td>
                        <td><a href="/archive-disposal/archive/<?= $archive['archive_id'] ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
