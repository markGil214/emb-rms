<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px; max-width: 800px;">
    <h1><?= $title ?></h1>

    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin: 20px 0;">
        <h2>Archive Details</h2>
        
        <table style="width: 100%;">
            <tr>
                <td style="padding: 10px 0; font-weight: bold; width: 30%;">Archive ID:</td>
                <td style="padding: 10px 0;"><?= $archive['archive_id'] ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Folder:</td>
                <td style="padding: 10px 0;">
                    <strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Archive Location:</td>
                <td style="padding: 10px 0;"><?= $archive['archive_location'] ?></td>
            </tr>
            <?php if ($archive['storage_box']): ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold;">Storage Box:</td>
                    <td style="padding: 10px 0;"><?= $archive['storage_box'] ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Archive Date:</td>
                <td style="padding: 10px 0;"><?= date('M d, Y g:i A', strtotime($archive['archive_date'])) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Archive Reason:</td>
                <td style="padding: 10px 0;"><?= nl2br($archive['archive_reason']) ?></td>
            </tr>
            <?php if ($archive['notes']): ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold;">Notes:</td>
                    <td style="padding: 10px 0;"><?= nl2br($archive['notes']) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Archived By:</td>
                <td style="padding: 10px 0;"><?= $archive['archived_by'] ?></td>
            </tr>
        </table>
    </div>

    <div style="margin: 20px 0;">
        <a href="/archive-disposal" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Back to Dashboard</a>
    </div>
</div>

<?= $this->endSection() ?>
