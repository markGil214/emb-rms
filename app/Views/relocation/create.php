<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1>Create Folder Relocation Request</h1>

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

    <form method="POST" action="/relocations" id="relocationForm">
        <?= csrf_field() ?>

        <fieldset>
            <legend>1️⃣ SELECT FOLDER</legend>
            <label for="folderSearch">Search by file code or company name:</label>
            <input type="text" id="folderSearch" placeholder="Search..." autocomplete="off">
            <div id="searchResults"></div>
            <div id="selectedFolder" style="display:none;">
                <div id="selectedCode"></div>
                <div id="selectedCompany"></div>
                <div id="selectedLocation"></div>
                <button type="button" onclick="clearSelection()">Change Selection</button>
            </div>
            <input type="hidden" name="folder_id" id="folder_id">
        </fieldset>

        <fieldset>
            <legend>2️⃣ CURRENT LOCATION (Readonly)</legend>
            <label for="from_location">Current Location:</label>
            <input type="text" id="from_location" readonly placeholder="Select a folder first...">
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
                            <option value="<?= $location['location_id'] ?>">Rack <?= $location['rack'] ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </fieldset>

        <fieldset>
            <legend>4️⃣ REASON (OPTIONAL)</legend>
            <label for="reason">Why are you relocating this folder?</label>
            <textarea name="reason" id="reason" rows="4" placeholder="Enter reason..."></textarea>
            <p><small>Optional but recommended for audit trail and accountability</small></p>
        </fieldset>

        <fieldset>
            <legend>Confirm Relocation</legend>
            <div id="confirmationBox" style="display:none;">
                <p>Folder: <span id="confirmCode"></span></p>
                <p>Current Location: <span id="confirmFrom"></span></p>
                <p>New Location: <span id="confirmTo"></span></p>
            </div>
        </fieldset>

        <div>
            <button type="submit">Request Relocation</button>
            <a href="/relocations">Cancel</a>
        </div>
    </form>

    <script>
        const folders = <?= json_encode($folders) ?>;
        const locations = <?= json_encode($locations) ?>;

        document.getElementById('folderSearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const results = document.getElementById('searchResults');
            
            if (query.length === 0) {
                results.style.display = 'none';
                return;
            }

            const filtered = folders.filter(f => 
                f.file_code.toLowerCase().includes(query) || 
                f.company_name.toLowerCase().includes(query)
            );

            if (filtered.length === 0) {
                results.innerHTML = '<div>No folders found</div>';
                results.style.display = 'block';
                return;
            }

            results.innerHTML = filtered.map(f => {
                const loc = locations.find(l => l.location_id === f.location_id);
                const locationDisplay = loc ? `Cabinet ${loc.cabinet} - Rack ${loc.rack}` : 'Unknown Location';
                return `<div onclick="selectFolder(${f.folder_id}, '${f.file_code}', '${f.company_name}', '${locationDisplay}', ${f.location_id})" style="cursor:pointer; padding:5px; border-bottom:1px solid #ddd;">${f.file_code} - ${f.company_name} (${locationDisplay})</div>`;
            }).join('');
            
            results.style.display = 'block';
        });

        function selectFolder(folderId, code, company, location, currentLocationId) {
            document.getElementById('folder_id').value = folderId;
            document.getElementById('folderSearch').value = '';
            document.getElementById('searchResults').style.display = 'none';
            document.getElementById('selectedCode').textContent = code;
            document.getElementById('selectedCompany').textContent = company;
            document.getElementById('selectedLocation').textContent = `Current Location: ${location}`;
            document.getElementById('selectedFolder').style.display = 'block';
            document.getElementById('from_location').value = location;
            const dropdown = document.getElementById('to_location_id');
            const options = dropdown.querySelectorAll('option');
            options.forEach(opt => {
                if (opt.value === String(currentLocationId)) {
                    opt.disabled = true;
                    opt.textContent = opt.textContent + ' (Current Location)';
                } else {
                    opt.disabled = false;
                }
            });
            updateConfirmation();
        }

        function clearSelection() {
            document.getElementById('folder_id').value = '';
            document.getElementById('folderSearch').value = '';
            document.getElementById('from_location').value = '';
            document.getElementById('selectedFolder').style.display = 'none';
            document.getElementById('confirmationBox').style.display = 'none';
            const dropdown = document.getElementById('to_location_id');
            const options = dropdown.querySelectorAll('option');
            options.forEach(opt => {
                opt.disabled = false;
                opt.textContent = opt.textContent.replace(' (Current Location)', '');
            });
        }

        document.getElementById('to_location_id').addEventListener('change', updateConfirmation);
        document.getElementById('reason').addEventListener('input', updateConfirmation);

        function updateConfirmation() {
            const folderId = document.getElementById('folder_id').value;
            const locationId = document.getElementById('to_location_id').value;
            if (!folderId || !locationId) {
                document.getElementById('confirmationBox').style.display = 'none';
                return;
            }
            const folder = folders.find(f => f.folder_id == folderId);
            const fromLoc = locations.find(l => l.location_id === folder.location_id);
            const toLoc = locations.find(l => l.location_id == locationId);
            document.getElementById('confirmCode').textContent = `${folder.file_code} - ${folder.company_name}`;
            document.getElementById('confirmFrom').textContent = `Cabinet ${fromLoc.cabinet}`;
            document.getElementById('confirmTo').textContent = `Cabinet ${toLoc.cabinet}`;
            document.getElementById('confirmationBox').style.display = 'block';
        }

        document.addEventListener('click', function(e) {
            if (e.target.id !== 'folderSearch') {
                document.getElementById('searchResults').style.display = 'none';
            }
        });

        document.getElementById('relocationForm').addEventListener('submit', function(e) {
            const folderId = document.getElementById('folder_id').value;
            const locationId = document.getElementById('to_location_id').value;
            if (!folderId) {
                e.preventDefault();
                alert('Please select a folder to relocate');
                return false;
            }
            if (!locationId) {
                e.preventDefault();
                alert('Please select a new location');
                return false;
            }
        });
    </script>
</body>
</html>
</html>
