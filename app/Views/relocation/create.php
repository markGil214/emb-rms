<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="p-6">

    <!-- Alert Messages -->
    <?php if (session()->has('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <p class="text-sm text-red-700 mt-1"><?= session('error') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                    <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Relocation Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="<?= route_to('relocations.store') ?>" id="relocationForm" class="p-6 space-y-6" data-confirm-message="Submit this relocation request?">
            <?= csrf_field() ?>

            <!-- Step 1: Select Folder -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">1</span>
                    Select Folder
                </h3>
                
                <div class="space-y-3">
                    <label for="folderSearch" class="block text-sm font-medium text-gray-700">Search by file code or company name:</label>
                    <div class="relative">
                        <input type="text" id="folderSearch" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Search..." autocomplete="off">
                        <div id="searchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-60 overflow-y-auto hidden"></div>
                    </div>
                    
                    <div id="selectedFolder" class="hidden bg-gray-50 border border-blue-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <span class="text-xs font-medium text-gray-500">File Code:</span>
                                <p id="selectedCode" class="font-medium text-gray-600"></p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500">Company:</span>
                                <p id="selectedCompany" class="font-medium text-gray-600"></p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500">Current Location:</span>
                                <p id="selectedLocation" class="font-medium text-gray-600"></p>
                            </div>
                        </div>
                        <button type="button" onclick="clearSelection()" 
                                class="mt-3 text-sm text-gray-600 hover:text-blue-800 font-medium">
                            Change Selection
                        </button>
                    </div>
                    <input type="hidden" name="folder_id" id="folder_id">
                </div>
            </div>

            <!-- Step 2: Current Location -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-gray-100 text-gray-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">2</span>
                    Current Location (Readonly)
                </h3>
                <div class="space-y-3">
                    <label for="from_location" class="block text-sm font-medium text-gray-800">Current Location:</label>
                    <input type="text" id="from_location" readonly 
                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg"
                           placeholder="Select a folder first...">
                </div>
            </div>

            <!-- Step 3: Select New Location -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">3</span>
                    Select New Location
                </h3>
                <div class="space-y-3">
                    <label for="to_location_id" class="block text-sm font-medium text-gray-700">New Location:</label>
                    <select name="to_location_id" id="to_location_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Choose Location --</option>
                        <?php 
                            $grouped = [];
                            foreach ($locations as $loc) {
                                $rack = $loc['rack'] ?? 'Unknown';
                                if (!isset($grouped[$rack])) {
                                    $grouped[$rack] = [];
                                }
                                $grouped[$rack][] = $loc;
                            }
                        ?>
                        <?php foreach ($grouped as $rack => $shelves): ?>
                            <optgroup label="Rack <?= $rack ?>">
                                <?php foreach ($shelves as $location): ?>
                                    <option value="<?= $location['location_id'] ?>">Shelf <?= $location['shelf'] ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Step 4: Reason -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-gray-100 text-gray-800 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">4</span>
                    Reason (Optional)
                </h3>
                <div class="space-y-3">
                    <label for="reason" class="block text-sm font-medium text-gray-700">Why are you relocating this folder?</label>
                    <textarea name="reason" id="reason" rows="4" 
                              maxlength="1000"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Enter reason..."><?= esc(old('reason')) ?></textarea>
                    <p class="text-sm text-gray-500">Optional but recommended for audit trail and accountability</p>
                </div>
            </div>

            <!-- Confirmation -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Relocation</h3>
                <div id="confirmationBox" class="hidden space-y-2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Folder:</span>
                            <p id="confirmCode" class="font-medium text-gray-900"></p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Current Location:</span>
                            <p id="confirmFrom" class="font-medium text-gray-900"></p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">New Location:</span>
                            <p id="confirmTo" class="font-medium text-gray-900"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= route_to('relocations.index') ?>" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Request Relocation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const folders = <?= json_encode($folders) ?>;
    const locations = <?= json_encode($locations) ?>;

    document.getElementById('folderSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const results = document.getElementById('searchResults');
        
        if (query.length === 0) {
            results.classList.add('hidden');
            return;
        }

        const filtered = folders.filter(f => 
            f.file_code.toLowerCase().includes(query) || 
            f.company_name.toLowerCase().includes(query)
        );

        if (filtered.length === 0) {
            results.innerHTML = '<div class="p-3 text-gray-500 text-sm">No folders found</div>';
            results.classList.remove('hidden');
            return;
        }

        results.innerHTML = filtered.map(f => {
            const loc = locations.find(l => l.location_id === f.location_id);
            const locationDisplay = loc ? `Rack ${loc.rack} - Shelf ${loc.shelf}` : 'Unknown Location';
            return `<div onclick="selectFolder(${f.folder_id}, '${f.file_code}', '${f.company_name}', '${locationDisplay}', ${f.location_id})" class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0">${f.file_code} - ${f.company_name} (${locationDisplay})</div>`;
        }).join('');
        
        results.classList.remove('hidden');
    });

    function selectFolder(folderId, code, company, location, currentLocationId) {
        document.getElementById('folder_id').value = folderId;
        document.getElementById('folderSearch').value = '';
        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('selectedCode').textContent = code;
        document.getElementById('selectedCompany').textContent = company;
        document.getElementById('selectedLocation').textContent = location;
        document.getElementById('selectedFolder').classList.remove('hidden');
        document.getElementById('from_location').value = location;
        
        const dropdown = document.getElementById('to_location_id');
        const options = dropdown.querySelectorAll('option');
        options.forEach(opt => {
            if (opt.value === String(currentLocationId)) {
                opt.disabled = true;
                opt.textContent = opt.textContent + ' (Current Location)';
            } else {
                opt.disabled = false;
                opt.textContent = opt.textContent.replace(' (Current Location)', '');
            }
        });
        updateConfirmation();
    }

    function clearSelection() {
        document.getElementById('folder_id').value = '';
        document.getElementById('folderSearch').value = '';
        document.getElementById('from_location').value = '';
        document.getElementById('selectedFolder').classList.add('hidden');
        document.getElementById('confirmationBox').classList.add('hidden');
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
            document.getElementById('confirmationBox').classList.add('hidden');
            return;
        }
        const folder = folders.find(f => f.folder_id == folderId);
        const fromLoc = locations.find(l => l.location_id === folder.location_id);
        const toLoc = locations.find(l => l.location_id == locationId);
        document.getElementById('confirmCode').textContent = `${folder.file_code} - ${folder.company_name}`;
        document.getElementById('confirmFrom').textContent = `Rack ${fromLoc.rack}`;
        document.getElementById('confirmTo').textContent = `Rack ${toLoc.rack}`;
        document.getElementById('confirmationBox').classList.remove('hidden');
    }

    document.addEventListener('click', function(e) {
        if (e.target.id !== 'folderSearch') {
            document.getElementById('searchResults').classList.add('hidden');
        }
    });

    document.getElementById('relocationForm').addEventListener('submit', function(e) {
        const folderId = document.getElementById('folder_id').value;
        const locationId = document.getElementById('to_location_id').value;
        if (!folderId) {
            e.preventDefault();
            window.showAppAlert('Please select a folder to relocate');
            return false;
        }
        if (!locationId) {
            e.preventDefault();
            window.showAppAlert('Please select a new location');
            return false;
        }
    });
</script>

<?= $this->endSection() ?>
