<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $title ?></h1>
    <p><?= $subtitle ?></p>

    <a href="/relocations/create">+ Request Relocation</a>

    <div>
        <div>
            <h3>Needs Approval</h3>
            <p><?= $needsApproval ?></p>
        </div>
        <div>
            <h3>Completed</h3>
            <p><?= $completed ?></p>
        </div>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>Folder Code</th>
                <th>Folder / Company</th>
                <th>Current Location</th>
                <th>New Location</th>
                <th>Requested On</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($relocations)): ?>
                <tr>
                    <td colspan="7">No relocation records</td>
                </tr>
            <?php else: ?>
                <?php foreach ($relocations as $rel): ?>
                    <?php
                        $statusMap = [
                            'Pending' => 'Needs Approval',
                            'Approved' => 'Approved',
                            'In Progress' => 'Moving',
                            'Completed' => 'Completed',
                            'Declined' => 'Declined'
                        ];
                        $displayStatus = $statusMap[$rel['status']] ?? $rel['status'];
                    ?>
                    <tr>
                        <td><?= $rel['file_code'] ?? 'N/A' ?></td>
                        <td><?= $rel['company_name'] ?? 'Unknown Folder' ?></td>
                        <td><?= $rel['current_location_display'] ?></td>
                        <td><?= $rel['new_location_display'] ?></td>
                        <td><?= date('M d, Y', strtotime($rel['requested_at'])) ?></td>
                        <td><?= $displayStatus ?></td>
                        <td>
                            <?php if ($rel['status'] === 'Pending'): ?>
                                <a href="/relocations/<?= $rel['relocation_id'] ?>">View</a>
                                <?php if (can('initiate_relocation')): ?>
                                    <a href="/relocations/<?= $rel['relocation_id'] ?>/edit">Edit</a>
                                <?php endif; ?>
                                <?php if (can('approve_relocation')): ?>
                                    <form action="/relocations/<?= $rel['relocation_id'] ?>/approve" method="post" style="display:inline;">
                                        <button type="submit">Complete</button>
                                    </form>
                                    <form action="/relocations/<?= $rel['relocation_id'] ?>/decline" method="post" style="display:inline;">
                                        <button type="submit">Decline</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/relocations/<?= $rel['relocation_id'] ?>">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
