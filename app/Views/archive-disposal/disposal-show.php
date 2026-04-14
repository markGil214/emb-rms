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
        <h2>Disposal Request Details</h2>
        
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <td>Disposal ID:</td>
                <td><?= $disposal['disposal_id'] ?></td>
            </tr>
            <tr>
                <td>Folder:</td>
                <td>
                    <strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?>
                </td>
            </tr>
            <tr>
                <td>Disposal Method:</td>
                <td><?= $disposal['disposal_method'] ?></td>
            </tr>
            <tr>
                <td>Reason:</td>
                <td><?= nl2br($disposal['reason'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Status:</td>
                <td>
                    <?= $disposal['status'] ?? (($disposal['disposal_date'] ?? null) ? 'Approved' : 'Pending') ?>
                </td>
            </tr>
            <?php if (!empty($disposal['compliance_reference'])): ?>
                <tr>
                    <td>Compliance Reference:</td>
                    <td><?= nl2br($disposal['compliance_reference']) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Requested Date:</td>
                <td><?= date('M d, Y g:i A', strtotime($disposal['created_at'])) ?></td>
            </tr>
            <?php if ($disposal['disposal_date']): ?>
                <tr>
                    <td>Completed Date:</td>
                    <td><?= date('M d, Y g:i A', strtotime($disposal['disposal_date'])) ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div>
        <a href="/archive-disposal">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
