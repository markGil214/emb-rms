<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $title ?></h1>

    <h2>Relocation Details</h2>
    
    <table border="1" cellpadding="10">
        <tr>
            <td><strong>Relocation ID:</strong></td>
            <td><?= $relocation['relocation_id'] ?></td>
        </tr>
        <tr>
            <td><strong>Folder:</strong></td>
            <td>
                <strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?>
            </td>
        </tr>
        <tr>
            <td><strong>Current Location:</strong></td>
            <td><?= ($currentLocation && $currentLocation['cabinet']) ? 'Cabinet ' . $currentLocation['cabinet'] : 'N/A' ?></td>
        </tr>
        <tr>
            <td><strong>New Location:</strong></td>
            <td><?= ($newLocation && $newLocation['cabinet']) ? 'Cabinet ' . $newLocation['cabinet'] : 'N/A' ?></td>
        </tr>
        <tr>
            <td><strong>Requested Date:</strong></td>
            <td><?= date('M d, Y g:i A', strtotime($relocation['requested_at'])) ?></td>
        </tr>
        <tr>
            <td><strong>Reason:</strong></td>
            <td><?= nl2br($relocation['reason']) ?></td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><?= $relocation['status'] ?></td>
        </tr>
    </table>

    <p><a href="/relocations">Back to List</a></p>
</body>
</html>
