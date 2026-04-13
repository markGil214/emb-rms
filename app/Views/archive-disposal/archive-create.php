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

    <?php if (session()->has('errors')): ?>
        <div>
            <strong>Validation Errors:</strong>
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/archive-disposal/archive">
        <?= csrf_field() ?>

        <div>
            <label for="folder_id">Select Folder:</label>
            <select name="folder_id" id="folder_id" required>
                <option value="">-- Choose Folder --</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['folder_id'] ?>"><?= $folder['file_code'] ?> - <?= $folder['company_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="archive_location_id">Archive Location:</label>
            <select name="archive_location_id" id="archive_location_id" required>
                <option value="">-- Choose Location --</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['location_id'] ?>">
                        Cabinet <?= esc($location['cabinet'] ?? '-') ?> - Shelf <?= esc($location['shelf'] ?? ($location['rack'] ?? '-')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="storage_box">Storage Box (Optional):</label>
            <input type="text" name="storage_box" id="storage_box" placeholder="e.g., BOX-001">
        </div>

        <div>
            <label for="archive_reason">Archive Reason:</label>
            <textarea name="archive_reason" id="archive_reason" rows="4" required></textarea>
        </div>

        <div>
            <label for="notes">Notes (Optional):</label>
            <textarea name="notes" id="notes" rows="3"></textarea>
        </div>

        <div>
            <button type="submit">Archive Document</button>
            <a href="/archive-disposal">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
