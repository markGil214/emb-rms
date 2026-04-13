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
        <h2>Archive Details</h2>
        
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <td>Archive ID:</td>
                <td><?= $archive['archive_id'] ?></td>
            </tr>
            <tr>
                <td>Folder:</td>
                <td>
                    <strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?>
                </td>
            </tr>
            <tr>
                <td>Archive Location:</td>
                <td><?= $archive['archive_location'] ?? '-' ?></td>
            </tr>
            <?php if ($archive['storage_box']): ?>
                <tr>
                    <td>Storage Box:</td>
                    <td><?= $archive['storage_box'] ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Archive Date:</td>
                <td><?= isset($archive['archive_date']) ? date('M d, Y g:i A', strtotime($archive['archive_date'])) : '-' ?></td>
            </tr>
            <tr>
                <td>Archive Reason:</td>
                <td><?= isset($archive['archive_reason']) ? nl2br($archive['archive_reason']) : '-' ?></td>
            </tr>
            <?php if ($archive['notes']): ?>
                <tr>
                    <td>Notes:</td>
                    <td><?= nl2br($archive['notes']) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Archived By:</td>
                <td><?= $archive['archived_by'] ?></td>
            </tr>
        </table>
    </div>

    <div>
        <a href="/archive-disposal">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
