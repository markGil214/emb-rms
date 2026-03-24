<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px;">
    <h1><?= $title ?></h1>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background: #f5f5f5; border-bottom: 2px solid #333;">
                <th style="padding: 10px; text-align: left;">ID</th>
                <th style="padding: 10px; text-align: left;">Folder</th>
                <th style="padding: 10px; text-align: left;">Method</th>
                <th style="padding: 10px; text-align: left;">Requested Date</th>
                <th style="padding: 10px; text-align: left;">Completed Date</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($completed)): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #666;">No completed disposals</td>
                </tr>
            <?php else: ?>
                <?php foreach ($completed as $disposal): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px;"><?= $disposal['disposal_id'] ?></td>
                        <td style="padding: 10px;"><?= $disposal['folder_id'] ?></td>
                        <td style="padding: 10px;"><?= $disposal['disposal_method'] ?></td>
                        <td style="padding: 10px;"><?= date('M d, Y', strtotime($disposal['created_at'])) ?></td>
                        <td style="padding: 10px;">
                            <span style="padding: 3px 8px; background: #d4edda; color: #155724; border-radius: 3px;">
                                <?= date('M d, Y', strtotime($disposal['disposal_date'])) ?>
                            </span>
                        </td>
                        <td style="padding: 10px;">
                            <a href="/archive-disposal/disposal/<?= $disposal['disposal_id'] ?>" style="color: #007bff; text-decoration: none;">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
