<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
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

    <form method="POST" action="/relocations">
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
            <label for="new_location_id">New Location:</label>
            <select name="new_location_id" id="new_location_id" required>
                <option value="">-- Choose Location --</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['location_id'] ?>"><?= $location['location_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="reason">Reason for Relocation:</label>
            <textarea name="reason" id="reason" rows="4" required></textarea>
        </div>

        <div>
            <button type="submit">Request Relocation</button>
            <a href="/relocations">Cancel</a>
        </div>
    </form>
</body>
</html>
