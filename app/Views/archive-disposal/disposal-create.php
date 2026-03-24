<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px; max-width: 600px;">
    <h1><?= $title ?></h1>

    <?php if (session()->has('errors')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #f5c6cb;">
            <strong>Validation Errors:</strong>
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/archive-disposal/disposal" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px;">
        <?= csrf_field() ?>

        <div style="margin-bottom: 20px;">
            <label for="folder_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Archived Folder:</label>
            <select name="folder_id" id="folder_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">-- Choose Folder --</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['folder_id'] ?>"><?= $folder['file_code'] ?> - <?= $folder['company_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="disposal_method" style="display: block; margin-bottom: 5px; font-weight: bold;">Disposal Method:</label>
            <select name="disposal_method" id="disposal_method" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">-- Choose Method --</option>
                <?php foreach ($disposalMethods as $method): ?>
                    <option value="<?= $method ?>"><?= $method ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="authorization_ref" style="display: block; margin-bottom: 5px; font-weight: bold;">Authorization Reference (Optional):</label>
            <input type="text" name="authorization_ref" id="authorization_ref" placeholder="e.g., AUTH-2026-001" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="reason" style="display: block; margin-bottom: 5px; font-weight: bold;">Reason for Disposal:</label>
            <textarea name="reason" id="reason" rows="4" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Request Disposal</button>
            <a href="/archive-disposal" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Cancel</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
