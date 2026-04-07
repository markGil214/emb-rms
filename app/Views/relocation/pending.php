<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f5f5f5; border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: bold; font-size: 13px; }
        td { border: 1px solid #ddd; padding: 12px; }
        tr:hover { background: #fafafa; }
        a, button { color: #007bff; text-decoration: none; padding: 5px 10px; cursor: pointer; border: 1px solid #007bff; background: white; border-radius: 3px; margin-right: 5px; }
        a:hover, button:hover { background: #007bff; color: white; }
        .empty { text-align: center; color: #999; padding: 40px; }
    </style>
</head>
<body>
    <h1><?= $title ?></h1>

    <table>
        <thead>
            <tr>
                <th>Folder Code</th>
                <th>Folder / Company</th>
                <th>Current Location</th>
                <th>New Location</th>
                <th>Requested On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="6" class="empty">No pending relocations</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending as $rel): ?>
                    <tr>
                        <td><?= $rel['file_code'] ?? 'N/A' ?></td>
                        <td><?= $rel['company_name'] ?? 'Unknown Folder' ?></td>
                        <td><?= $rel['current_location_display'] ?></td>
                        <td><?= $rel['new_location_display'] ?></td>
                        <td><?= date('M d, Y', strtotime($rel['requested_at'])) ?></td>
                        <td>
                            <a href="/relocations/<?= $rel['relocation_id'] ?>">View</a>
                            <form method="POST" action="/relocations/<?= $rel['relocation_id'] ?>/approve" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit">Approve</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="/relocations">Back to List</a></p>
</body>
</html>
