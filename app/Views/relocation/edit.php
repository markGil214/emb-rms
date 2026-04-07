<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1>Edit Relocation Request</h1>

    <?php if (session()->has('error')): ?>
        <div>
            <strong>Error:</strong> <?= session('error') ?>
        </div>
    <?php endif; ?>

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

    <form method="POST" action="/relocations/<?= $relocation['relocation_id'] ?>" id="relocationForm">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="folder_id" value="<?= $relocation['folder_id'] ?>">

        <fieldset>
            <legend>1️⃣ FOLDER TO RELOCATE</legend>
            <p><strong><?= $folder['file_code'] ?></strong></p>
            <p><?= $folder['company_name'] ?></p>
        </fieldset>

        <fieldset>
            <legend>2️⃣ CURRENT LOCATION (Readonly)</legend>
            <label for="from_location">Current Location:</label>
            <input type="text" id="from_location" readonly value="<?php 
                $currentLoc = null;
                foreach ($locations as $loc) {
                    if ($loc['location_id'] == $relocation['from_location_id']) {
                        $currentLoc = $loc;
                        break;
                    }
                }
                if ($currentLoc) {
                    echo 'Cabinet ' . $currentLoc['cabinet'] . ' - Rack ' . $currentLoc['rack'];
                } else {
                    echo 'Unknown';
                }
            ?>">
        </fieldset>

        <fieldset>
            <legend>3️⃣ SELECT NEW LOCATION</legend>
        <label for="to_location_id">New Location:</label>
        <select name="to_location_id" id="to_location_id" required>
            <option value="">-- Choose Location --</option>
            <?php 
                $grouped = [];
                foreach ($locations as $loc) {
                    $cabinet = $loc['cabinet'] ?? 'Unknown';
                    if (!isset($grouped[$cabinet])) {
                        $grouped[$cabinet] = [];
                    }
                    $grouped[$cabinet][] = $loc;
                }
            ?>
            <?php foreach ($grouped as $cabinet => $cabinets): ?>
                <optgroup label="Cabinet <?= $cabinet ?>">
                    <?php foreach ($cabinets as $location): ?>
                        <option value="<?= $location['location_id'] ?>" <?= $location['location_id'] == $relocation['to_location_id'] ? 'selected' : '' ?> <?= $location['location_id'] == $relocation['from_location_id'] ? 'disabled' : '' ?>>
                            Rack <?= $location['rack'] ?><?= $location['location_id'] == $relocation['from_location_id'] ? ' (Current Location)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        </fieldset>

        <fieldset>
            <legend>4️⃣ REASON (OPTIONAL)</legend>
            <label for="reason">Why are you relocating this folder?</label>
            <textarea name="reason" id="reason" rows="4" placeholder="Enter reason..."><?= $relocation['reason'] ?></textarea>
            <p><small>Optional but recommended for audit trail and accountability</small></p>
        </fieldset>

        <div>
            <button type="submit">Update Relocation</button>
            <a href="/relocations">Cancel</a>
        </div>
    </form>
</body>
</html>
