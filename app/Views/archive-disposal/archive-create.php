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

    <form method="POST" action="/archive-disposal/archive" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px;">
        <?= csrf_field() ?>

        <div style="margin-bottom: 20px;">
            <label for="folder_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Folder:</label>
            <select name="folder_id" id="folder_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">-- Choose Folder --</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['folder_id'] ?>"><?= $folder['file_code'] ?> - <?= $folder['company_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="archive_location" style="display: block; margin-bottom: 5px; font-weight: bold;">Archive Location:</label>
            <input type="text" name="archive_location" id="archive_location" required placeholder="e.g., Warehouse A, Section 5" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="storage_box" style="display: block; margin-bottom: 5px; font-weight: bold;">Storage Box (Optional):</label>
            <input type="text" name="storage_box" id="storage_box" placeholder="e.g., BOX-001" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="archive_reason" style="display: block; margin-bottom: 5px; font-weight: bold;">Archive Reason:</label>
            <textarea name="archive_reason" id="archive_reason" rows="4" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="notes" style="display: block; margin-bottom: 5px; font-weight: bold;">Notes (Optional):</label>
            <textarea name="notes" id="notes" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" style="background: #0066cc; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Archive Document</button>
            <a href="/archive-disposal" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Cancel</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
