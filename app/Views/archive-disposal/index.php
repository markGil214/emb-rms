<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
</head>
<body>
    <h1><?= $title ?></h1>

    <?php if (session('success')): ?>
        <p style="color: green; font-weight: 600;"><?= esc(session('success')) ?></p>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <p style="color: #b91c1c; font-weight: 600;"><?= esc(session('error')) ?></p>
    <?php endif; ?>

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
                <th>Folder Type</th>
                <th>Folder Subtype</th>
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
                        <td><?= esc($archive['file_code']) ?></td>
                        <td><?= esc($archive['company_name']) ?></td>
                        <td><?= esc($archive['folder_type']) ?></td>
                        <td><?= !empty($archive['category_name']) ? esc($archive['category_name']) : '-' ?></td>
                        <td>
                            <a href="/document-records/<?= $archive['folder_id'] ?>">View</a>
                            <form action="<?= route_to('archive.restore', $archive['folder_id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Restore this folder to Available status?');">
                                <?= csrf_field() ?>
                                <button type="submit" style="background:none; border:none; color:blue; cursor:pointer; text-decoration:underline; padding:0; margin-left:8px;">Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
