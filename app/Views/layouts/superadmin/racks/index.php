<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? esc($title) : 'Manage Racks and Shelves' ?></title>
</head>
<body>
<h1>Manage Racks and Shelves</h1>
<p>Dashboard / Storage / Rack Management</p>

<?php if (session()->getFlashdata('success')): ?>
    <p><strong>Success:</strong> <?= esc(session()->getFlashdata('success')) ?></p>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <p><strong>Error:</strong> <?= esc(session()->getFlashdata('error')) ?></p>
<?php endif; ?>

<?php $errors = session()->getFlashdata('errors') ?: []; ?>
<hr>
<h2>Rack Controls</h2>

<p>
    <button type="button" onclick="openModal('addShelfModal')">Add Shelf</button>
</p>

<div id="addShelfModal" hidden style="position:fixed; inset:0; background:rgba(0,0,0,0.4); padding:24px;">
    <div style="background:#fff; max-width:480px; margin:8% auto; padding:20px; border:1px solid #000;">
        <h3>Add Shelf</h3>
        <form method="post" action="<?= route_to('racks.store') ?>">
            <?= csrf_field() ?>
            <div>
                <label for="rack">Rack</label><br>
                <select id="rack" name="rack" required>
                    <option value="">Choose</option>
                    <?php foreach ($racks as $rack): ?>
                        <option value="<?= esc($rack) ?>" <?= old('rack') === (string) $rack ? 'selected' : '' ?>><?= esc($rack) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (! empty($errors['rack'])): ?>
                    <p><?= esc($errors['rack']) ?></p>
                <?php endif; ?>
            </div>
            <br>
            <div>
                <label for="shelf">Shelf</label><br>
                <input id="shelf" type="text" name="shelf" value="<?= esc(old('shelf')) ?>" required maxlength="50" pattern="[A-Za-z0-9 ]+" title="Use letters, numbers, and spaces only.">
                <?php if (! empty($errors['shelf'])): ?>
                    <p><?= esc($errors['shelf']) ?></p>
                <?php endif; ?>
            </div>
            <br>
            <button type="submit">Add Shelf</button>
            <button type="button" onclick="closeModal('addShelfModal')">Close</button>
        </form>
    </div>
</div>

<hr>
<h2>Shelves Overview</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Rack</th>
            <th>Shelf</th>
            <th>Used</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($locations)): ?>
            <tr>
                <td colspan="3">No rack shelves found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($locations as $location): ?>
                <tr>
                    <td><?= esc($location['rack']) ?></td>
                    <td><?= esc($location['shelf']) ?></td>
                    <td><?= esc($location['current_count']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
    function openModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.hidden = false;
        }
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.hidden = true;
        }
    }

    document.addEventListener('click', function (event) {
        if (event.target.classList && event.target.classList.contains('modal-close')) {
            closeModal(event.target.getAttribute('data-modal'));
        }
    });

    <?php if (session()->getFlashdata('modal') === 'add-shelf'): ?>
    openModal('addShelfModal');
    <?php endif; ?>
</script>
</body>
</html>
