<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="padding: 20px;">
    <h1><?= $title ?></h1>

    <form method="GET" action="/archive-disposal/search" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin: 20px 0;">
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label for="q" style="display: block; margin-bottom: 5px; font-weight: bold;">Search Archives:</label>
                <input type="text" name="q" id="q" value="<?= $query ?>" placeholder="File code, company name, location..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <button type="submit" style="background: #007bff; color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer;">Search</button>
        </div>
    </form>

    <?php if (!empty($query)): ?>
        <div style="margin: 20px 0; color: #666;">
            Found <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($query) ?>"
        </div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background: #f5f5f5; border-bottom: 2px solid #333;">
                <th style="padding: 10px; text-align: left;">File Code</th>
                <th style="padding: 10px; text-align: left;">Company</th>
                <th style="padding: 10px; text-align: left;">Location</th>
                <th style="padding: 10px; text-align: left;">Storage Box</th>
                <th style="padding: 10px; text-align: left;">Archive Date</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #666;">
                        <?= empty($query) ? 'Enter a search term to find archived documents' : 'No results found' ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $archive): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px;"><?= $archive['folder_id'] ?? 'N/A' ?></td>
                        <td style="padding: 10px;">Archive <?= $archive['archive_id'] ?></td>
                        <td style="padding: 10px;"><?= $archive['archive_location'] ?></td>
                        <td style="padding: 10px;"><?= $archive['storage_box'] ?? '—' ?></td>
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

<?= $this->endSection() ?>
