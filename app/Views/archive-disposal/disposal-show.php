<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px; max-width: 800px;">
    <h1><?= $title ?></h1>

    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin: 20px 0;">
        <h2>Disposal Request Details</h2>
        
        <table style="width: 100%;">
            <tr>
                <td style="padding: 10px 0; font-weight: bold; width: 30%;">Disposal ID:</td>
                <td style="padding: 10px 0;"><?= $disposal['disposal_id'] ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Folder:</td>
                <td style="padding: 10px 0;">
                    <strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Disposal Method:</td>
                <td style="padding: 10px 0;"><?= $disposal['disposal_method'] ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Reason:</td>
                <td style="padding: 10px 0;"><?= nl2br($disposal['reason']) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Status:</td>
                <td style="padding: 10px 0;">
                    <span style="padding: 5px 10px; border-radius: 4px;
                        <?php switch($disposal['status']) {
                            case 'Pending': echo 'background: #fff3cd; color: #856404;'; break;
                            case 'Approved': echo 'background: #cfe2ff; color: #084298;'; break;
                            case 'Completed': echo 'background: #d4edda; color: #155724;'; break;
                            case 'Cancelled': echo 'background: #e2e3e5; color: #383d41;'; break;
                        } ?>">
                        <?= $disposal['status'] ?>
                    </span>
                </td>
            </tr>
            <?php if ($disposal['authorization_ref']): ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold;">Authorization Ref:</td>
                    <td style="padding: 10px 0;"><?= $disposal['authorization_ref'] ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Requested Date:</td>
                <td style="padding: 10px 0;"><?= date('M d, Y g:i A', strtotime($disposal['created_at'])) ?></td>
            </tr>
            <?php if ($disposal['disposal_date']): ?>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold;">Completed Date:</td>
                    <td style="padding: 10px 0;"><?= date('M d, Y g:i A', strtotime($disposal['disposal_date'])) ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div style="margin: 20px 0;">
        <a href="/archive-disposal" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Back to Dashboard</a>
    </div>
</div>

<?= $this->endSection() ?>
