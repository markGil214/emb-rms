<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px;">
    <h1><?= $title ?></h1>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
        <div style="border: 1px solid #ccc; padding: 15px; border-radius: 4px;">
            <h3>Total Archived</h3>
            <p style="font-size: 24px; font-weight: bold;"><?= $totalArchived ?></p>
            <a href="/archive-disposal/search" style="color: #007bff; text-decoration: none;">View Archived →</a>
        </div>
        <div style="border: 1px solid #ccc; padding: 15px; border-radius: 4px;">
            <h3>Disposal Pending</h3>
            <p style="font-size: 24px; font-weight: bold; color: #ff6b6b;"><?= $totalDisposalPending ?></p>
            <a href="/archive-disposal/disposal/pending" style="color: #007bff; text-decoration: none;">Approval Needed →</a>
        </div>
        <div style="border: 1px solid #ccc; padding: 15px; border-radius: 4px;">
            <h3>Disposal Approved</h3>
            <p style="font-size: 24px; font-weight: bold;"><?= $totalDisposalApproved ?></p>
            <a href="/archive-disposal/disposal/completed" style="color: #007bff; text-decoration: none;">View Completed →</a>
        </div>
    </div>

    <div style="margin: 30px 0;">
        <h2>Quick Actions</h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/archive-disposal/create-archive" style="padding: 12px 20px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px;">+ Archive Document</a>
            <a href="/archive-disposal/create-disposal" style="padding: 12px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;">+ Request Disposal</a>
            <a href="/archive-disposal/search" style="padding: 12px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">🔍 Search Archives</a>
        </div>
    </div>

    <div style="margin-top: 30px;">
        <h2>Recently Archived</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background: #f5f5f5; border-bottom: 2px solid #333;">
                    <th style="padding: 10px; text-align: left;">File Code</th>
                    <th style="padding: 10px; text-align: left;">Company</th>
                    <th style="padding: 10px; text-align: left;">Archive Location</th>
                    <th style="padding: 10px; text-align: left;">Archive Date</th>
                    <th style="padding: 10px; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($archived)): ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #666;">No archived documents</td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_slice($archived, 0, 5) as $archive): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;"><?= $archive['folder_id'] ?></td>
                            <td style="padding: 10px;">Archive <?= $archive['archive_id'] ?></td>
                            <td style="padding: 10px;"><?= $archive['archive_location'] ?></td>
                            <td style="padding: 10px;"><?= date('M d, Y', strtotime($archive['archive_date'])) ?></td>
                            <td style="padding: 10px;">
                                <a href="/archive-disposal/archive/<?= $archive['archive_id'] ?>" style="color: #007bff; text-decoration: none;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
