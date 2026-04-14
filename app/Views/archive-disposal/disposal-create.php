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

    <form method="POST" action="/archive-disposal/disposal">
        <?= csrf_field() ?>

        <div>
            <label for="archive_id">Select Archived Record:</label>
            <select name="archive_id" id="archive_id" required>
                <option value="">-- Choose Archive Record --</option>
                <?php foreach ($archived as $archive): ?>
                    <option value="<?= $archive['archive_id'] ?>">
                        #<?= $archive['archive_id'] ?> - <?= esc($archive['file_code'] ?? ('Folder ' . $archive['folder_id'])) ?> - <?= esc($archive['company_name'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="disposal_method">Disposal Method:</label>
            <select name="disposal_method" id="disposal_method" required>
                <option value="">-- Choose Method --</option>
                <?php foreach ($disposalMethods as $method): ?>
                    <option value="<?= $method ?>"><?= $method ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="compliance_reference">Authorization Reference (Optional):</label>
            <input type="text" name="compliance_reference" id="compliance_reference" placeholder="e.g., AUTH-2026-001">
        </div>

        <div>
            <label for="reason">Reason for Disposal:</label>
            <textarea name="reason" id="reason" rows="4" required></textarea>
        </div>

        <div>
            <button type="submit">Request Disposal</button>
            <a href="/archive-disposal">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
