<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px;">
    <h1><?= $title ?></h1>

    <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #ffeaa7;">
        <strong>⚠️ Action Needed:</strong> The following disposal requests are pending approval.
    </div>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background: #f5f5f5; border-bottom: 2px solid #333;">
                <th style="padding: 10px; text-align: left;">ID</th>
                <th style="padding: 10px; text-align: left;">Folder</th>
                <th style="padding: 10px; text-align: left;">Method</th>
                <th style="padding: 10px; text-align: left;">Requested Date</th>
                <th style="padding: 10px; text-align: left;">Requested By</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #666;">No pending disposal requests</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending as $disposal): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px;"><?= $disposal['disposal_id'] ?></td>
                        <td style="padding: 10px;"><?= $disposal['folder_id'] ?></td>
                        <td style="padding: 10px;"><?= $disposal['disposal_method'] ?></td>
                        <td style="padding: 10px;"><?= date('M d, Y', strtotime($disposal['created_at'])) ?></td>
                        <td style="padding: 10px;"><?= $disposal['requested_by'] ?></td>
                        <td style="padding: 10px;">
                            <a href="/archive-disposal/disposal/<?= $disposal['disposal_id'] ?>" style="color: #007bff; text-decoration: none; margin-right: 10px;">View</a>
                            <form method="POST" action="/archive-disposal/disposal/<?= $disposal['disposal_id'] ?>/approve" style="display: inline;">
                                <button type="submit" style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Approve</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
