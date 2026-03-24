<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $title ?></h1>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Folder</th>
                <th>From → To</th>
                <th>Requested By</th>
                <th>Requested Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="6">No pending relocations</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending as $rel): ?>
                    <tr>
                        <td><?= $rel['relocation_id'] ?></td>
                        <td><?= $rel['folder_id'] ?></td>
                        <td><?= $rel['current_location_id'] ?> → <?= $rel['new_location_id'] ?></td>
                        <td><?= $rel['requested_by'] ?></td>
                        <td><?= date('M d, Y', strtotime($rel['requested_date'])) ?></td>
                        <td>
                            <a href="/relocations/<?= $rel['relocation_id'] ?>">View</a>
                            <form method="POST" action="/relocations/<?= $rel['relocation_id'] ?>/approve" style="display: inline;">
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
