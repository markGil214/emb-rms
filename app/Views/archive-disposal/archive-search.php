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

    <form method="GET" action="/archive-disposal/search">
        <div>
            <label for="q">Search Archives:</label>
            <input type="text" name="q" id="q" value="<?= esc($query) ?>" placeholder="File code, company name, location...">
            <button type="submit">Search</button>
        </div>
    </form>

    <?php if (!empty($query)): ?>
        <div>
            Found <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($query) ?>"
        </div>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>File Code</th>
                <th>Company</th>
                <th>Location</th>
                <th>Storage Box</th>
                <th>Archive Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="6">
                        <?= empty($query) ? 'Enter a search term to find archived documents' : 'No results found' ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $archive): ?>
                    <tr>
                        <td><?= $archive['folder_id'] ?? 'N/A' ?></td>
                        <td>Archive <?= $archive['archive_id'] ?></td>
                        <td><?= $archive['archive_location'] ?? '-' ?></td>
                        <td><?= $archive['storage_box'] ?? '-' ?></td>
                        <td><?= isset($archive['archive_date']) ? date('M d, Y', strtotime($archive['archive_date'])) : '-' ?></td>
                        <td>
                            <a href="/archive-disposal/archive/<?= $archive['archive_id'] ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
