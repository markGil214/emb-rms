<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $title ?></h1>

    <p><a href="/relocations/create">+ Request Relocation</a></p>

    <h3>Pending: <?= $totalPending ?></h3>
    <h3>Approved: <?= $totalApproved ?></h3>
    <h3>In Progress: <?= $totalInProgress ?></h3>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Folder</th>
                <th>From → To</th>
                <th>Requested</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($relocations)): ?>
                <tr>
                    <td colspan="6">No relocation records</td>
                </tr>
            <?php else: ?>
                <?php foreach ($relocations as $rel): ?>
                    <tr>
                        <td><?= $rel['relocation_id'] ?></td>
                        <td><?= $rel['folder_id'] ?></td>
                        <td><?= $rel['current_location_id'] ?> → <?= $rel['new_location_id'] ?></td>
                        <td><?= date('M d, Y', strtotime($rel['requested_date'])) ?></td>
                        <td><?= $rel['status'] ?></td>
                        <td><a href="/relocations/<?= $rel['relocation_id'] ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
