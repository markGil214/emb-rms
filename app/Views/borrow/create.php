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

    <form method="POST" action="/borrows">
        <?= csrf_field() ?>

        <div>
            <label for="borrower_name">Borrower Name:</label>
            <input type="text" name="borrower_name" id="borrower_name" placeholder="Enter borrower's name" required>
        </div>

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
            <label for="expected_return_date">Expected Return Date:</label>
            <input type="date" name="expected_return_date" id="expected_return_date" required>
        </div>

        <div>
            <label for="notes">Notes (Optional):</label>
            <textarea name="notes" id="notes" rows="4"></textarea>
        </div>

        <div>
            <button type="submit">Request Borrow</button>
            <a href="/borrows">Cancel</a>
        </div>
    </form>
</body>
</html>
