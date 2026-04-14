<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div class="max-w-5xl mx-auto">

    

    <!-- Form Card -->

    <div class="bg-white shadow rounded-lg overflow-hidden">

        <div class="bg-gray-400 px-4 py-3">

            <h2 class="text-lg font-semibold text-gray-800">Document Information</h2>

        </div>

        

        <!-- Display All Validation Errors -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded p-4 m-4">
                <h3 class="text-red-800 font-bold mb-2">⚠️ Please fix the following errors:</h3>
                <ul class="text-red-700 text-sm space-y-1">
                    <?php foreach ($errors as $field => $message): ?>
                        <li>• <?= $message ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= route_to('records.store') ?>" method="POST" class="p-4 space-y-4" id="folderForm" onsubmit="validateForm(event)">

            <?= csrf_field() ?>



            <!-- Company Name and Document Status -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Company Name -->

                <div>

                    <label for="company_name" class="block text-sm font-medium text-gray-700 pt-2">

                        Company Name <span class="text-red-500">*</span>

                    </label>

                    <input type="text" id="company_name" name="company_name" 

                        value="<?= old('company_name') ?>"

                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"

                        placeholder="Enter company name" required>

                    <?php if (isset($errors['company_name'])): ?>

                        <p class="mt-1 text-sm text-red-600"><?= $errors['company_name'] ?></p>

                    <?php endif; ?>

                </div>

                

                <!-- Folder Type -->

                <div>

                    <label for="folder_type" class="block text-sm font-medium text-gray-700 pt-2">

                        Folder Type

                    </label>

                    <select id="folder_type" name="folder_type" 

                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">

                        <option value="">-- Select Folder Type --</option>

                        <option value="permits" <?= old('folder_type') === 'permits' ? 'selected' : '' ?>>PERMIT</option>

                        <option value="ECC / CNC FILES" <?= old('folder_type') === 'ECC / CNC FILES' ? 'selected' : '' ?>>ECC / CNC FILES</option>

                        <option value="IEE / EIS FILES" <?= old('folder_type') === 'IEE / EIS FILES' ? 'selected' : '' ?>>IEE / EIS FILES</option>

                    </select>

                    <?php if (isset($errors['folder_type'])): ?>

                        <p class="mt-1 text-sm text-red-600"><?= $errors['folder_type'] ?></p>

                    <?php endif; ?>

                </div>

                

                <!-- Folder Subtype (UI only) -->

                <div>

                    <label for="folder_subtype" class="block text-sm font-medium text-gray-700 pt-2">

                        Folder Subtype

                    </label>

                    <input type="text" id="folder_subtype" name="folder_subtype"

                        value="<?= old('folder_subtype') ?>"

                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">

                </div>

                

            </div>



            <!-- Location Section -->

            <div class="border-t pt-4">

                <h3 class="text-base font-medium text-gray-900 mb-3">Location Information</h3>

                

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Rack Dropdown -->

                    <div>

                        <label for="cabinet" class="block text-sm font-medium text-gray-700 mb-1">

                            Rack <span class="text-red-500">*</span>

                        </label>

                        <select id="cabinet" name="cabinet" 

                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"

                                required>

                            <option value="">-- Select Rack --</option>

                        </select>

                        <?php if (isset($errors['location_id'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['location_id'] ?></p>

                        <?php endif; ?>

                    </div>



                    <!-- Shelf Dropdown (auto-populated) -->

                    <div>

                        <label for="shelf" class="block text-sm font-medium text-gray-700 mb-1">

                            Shelf <span class="text-red-500">*</span>

                        </label>

                        <select id="shelf" name="shelf" 

                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"

                                required disabled>

                            <option value="">-- Select Shelf --</option>

                        </select>

                    </div>

                </div>



                <!-- Hidden location_id field (auto-set by JS) -->

                <input type="hidden" id="location_id" name="location_id" value="">

            </div>



            <script>

                // Build normalized location data from PHP
                const locationsData = <?= json_encode($locations ?? []) ?>;

                const cabinetSelect = document.getElementById('cabinet');

                const shelfSelect = document.getElementById('shelf');

                const locationIdInput = document.getElementById('location_id');



                // Get unique racks and build dropdown
                const cabinets = [...new Set(locationsData.map(loc => loc.cabinet).filter(Boolean))].sort();

                cabinets.forEach(cabinet => {

                    const option = document.createElement('option');

                    option.value = cabinet;

                    option.textContent = 'Rack ' + cabinet;

                    cabinetSelect.appendChild(option);

                });



                // Handle Rack selection
                cabinetSelect.addEventListener('change', function() {

                    const selectedCabinet = this.value;

                    shelfSelect.innerHTML = '<option value="">-- Select Shelf --</option>';

                    locationIdInput.value = '';



                    if (selectedCabinet) {

                        // Filter shelves for this cabinet
                        const shelves = locationsData

                            .filter(loc => loc.cabinet === selectedCabinet)

                            .map(loc => ({

                                shelf: loc.shelf || loc.cabinet,

                                locationId: loc.location_id

                            }));



                        // Remove duplicates and sort
                        const uniqueShelves = [...new Map(shelves.map(s => [s.shelf, s])).values()].sort((a, b) => 

                            a.shelf.localeCompare(b.shelf)

                        );



                        uniqueShelves.forEach(item => {

                            const option = document.createElement('option');

                            option.value = JSON.stringify({ shelf: item.shelf, locationId: item.locationId });

                            option.textContent = 'Shelf ' + item.shelf;

                            shelfSelect.appendChild(option);

                        });



                        shelfSelect.disabled = false;

                    } else {

                        shelfSelect.disabled = true;

                    }

                });



                // Handle Shelf selection
                shelfSelect.addEventListener('change', function() {

                    locationIdInput.value = '';

                    if (this.value) {

                        const selected = JSON.parse(this.value);

                        locationIdInput.value = selected.locationId;

                    }

                });

                // Client-side validation before form submit
                function validateForm(event) {
                    const locationId = document.getElementById('location_id').value;
                    const cabinet = document.getElementById('cabinet').value;
                    const shelf = document.getElementById('shelf').value;
                    if (!cabinet || !shelf || !locationId) {
                        event.preventDefault();
                        alert('❌ Please select both Rack and Shelf before creating the record.');
                        return false;
                    }
                    
                    console.log('✅ Form validation passed. Submitting with location_id:', locationId);
                }

            </script>



            <!-- Action Buttons -->

            <div class="border-t pt-4 flex gap-3 justify-end">

                <a href="<?= route_to('records') ?>" 

                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-center">

                    Cancel

                </a>

                <button type="submit" 

                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">

                    Create Record

                </button>

            </div>

        </form>

    </div>

</div>



<script>

// Preserve form values and prevent reset

document.addEventListener('DOMContentLoaded', function() {

    // Set default status if none selected

    const statusSelect = document.getElementById('status');

    if (statusSelect && !statusSelect.value) {

        statusSelect.value = 'Available';

    }

    

    // Prevent form reset on page load

    const form = document.getElementById('folderForm');

    if (form) {

        // Store form data in sessionStorage

        const formData = {

            company_name: '<?= old('company_name') ?>',

            status: '<?= old('status') || 'Available' ?>',

            cabinet: '<?= old('cabinet') ?>',

            rack: '<?= old('rack') ?>',

            location_id: '<?= old('location_id') ?>'

        };

        

        // Restore form values after page load

        Object.keys(formData).forEach(key => {

            if (formData[key]) {

                const input = form.querySelector(`[name="${key}"]`);

                if (input) {

                    input.value = formData[key];

                }

            }

        });

    }

});

</script>



<?= $this->endSection() ?>

