<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div class="mx-auto w-full max-w-10xl px-4 py-6 sm:px-6 lg:px-8">

    <div id="editValidationAlert" class="hidden" style="
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 50;
        width: 340px;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: rgba(15, 23, 42, 0.94);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
        border-top: 3px solid #3b82f6;
    ">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div style="width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(59, 130, 246, 0.14); color: #93c5fd;">
                <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 14px; font-weight: 700; color: #f8fafc; line-height: 1.2;">Location required</div>
                <div id="editValidationAlertMessage" style="margin-top: 4px; font-size: 12px; color: rgba(226, 232, 240, 0.78); line-height: 1.45;">Please select both Rack and Shelf.</div>
            </div>
        </div>
    </div>

    <div id="recordUpdateAlert" class="hidden" style="
        width: 340px;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: rgba(15, 23, 42, 0.94);
        backdrop-filter: blur(14px); 
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
        border-top: 3px solid #10b981;
    ">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div style="width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, 0.14); color: #6ee7b7;">
                <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 14px; font-weight: 700; color: #f8fafc; line-height: 1.2;">Update request submitted</div>
                <div class="recordUpdateAlertMessage" style="margin-top: 4px; font-size: 12px; color: rgba(226, 232, 240, 0.78); line-height: 1.45;">Saving your changes...</div>
            </div>
        </div>
    </div>

    <div id="alertsContainer" style="position: fixed; top: 20px; right: 20px; z-index: 50;"></div>

    <!-- Header -->

    <div class="text-left mb-6">

        <h1 class="text-2xl font-bold text-gray-900"><?= $title ?></h1>

        <p class="text-gray-600">Update document record folder information</p>

    </div>



    <!-- Form Card -->

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">

        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">

            <h2 class="text-xl font-semibold text-white">Edit Document Information</h2>

        </div>

        

        <form action="<?= route_to('records.update', $folder['folder_id']) ?>" method="POST" class="p-4 sm:p-6" id="editFolderForm">

            <?= csrf_field() ?>

            <input type="hidden" name="_method" value="PUT">



            <!-- Two Column Layout -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Column -->

                <div class="space-y-4">

                    <!-- File Code (Read-only) -->

                    <div>

                        <label for="file_code" class="block text-sm font-semibold text-gray-700 mb-1">

                            File Code

                        </label>

                        <input type="text" id="file_code" name="file_code" 

                            value="<?= esc($folder['file_code']) ?>"

                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed"

                            readonly>

                    </div>



                    <!-- Company Name -->

                    <div>

                        <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-1">

                            Company Name <span class="text-red-500">*</span>

                        </label>

                        <input type="text" id="company_name" name="company_name" 

                            value="<?= old('company_name', $folder['company_name']) ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            placeholder="Enter company name" required>

                        <?php if (isset($errors['company_name'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['company_name'] ?></p>

                        <?php endif; ?>

                    </div>


                    <!-- Issuance Date -->


                    <!-- Location Code (Read-only) -->

                    <div>

                        <label for="location_code" class="block text-sm font-semibold text-gray-700 mb-1">

                            Location Code

                        </label>

                        <?php
                            $editLocationCodeDisplay = '--';
                            $editRack = trim((string) ($folder['cabinet'] ?? ''));
                            $editShelf = trim((string) ($folder['shelf'] ?? ''));
                            if ($editRack !== '' && $editShelf !== '') {
                                $editLocationCodeDisplay = 'Rack ' . $editRack . ' - Shelf ' . $editShelf;
                            } elseif ($editRack !== '') {
                                $editLocationCodeDisplay = 'Rack ' . $editRack;
                            } elseif ($editShelf !== '') {
                                $editLocationCodeDisplay = 'Shelf ' . $editShelf;
                            }
                        ?>

                        <input type="text" id="location_code" name="location_code" 

                            value="<?= esc($editLocationCodeDisplay) ?>"

                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed"

                            readonly>

                    </div>



                    <!-- Rack -->

                    <div>

                        <label for="cabinet" class="block text-sm font-semibold text-gray-700 mb-1">

                            Rack

                        </label>

                        <select id="cabinet" name="cabinet"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed"
                            disabled>
                            <option value="">-- Select Rack --</option>
                        </select>

                    </div>

                </div>



                <!-- Right Column -->

                <div class="space-y-4">

                    <!-- Folder Type -->

                    <div>

                        <label for="folder_type" class="block text-sm font-semibold text-gray-700 mb-1">

                            Folder Type <span class="text-red-500">*</span>

                        </label>

                        <select id="folder_type" name="folder_type" 

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            required>

                            <option value="">-- Select Folder Type --</option>

                            <option value="PERMITS" <?= (old('folder_type') ?? $folder['folder_type'] ?? '') === 'PERMITS' ? 'selected' : '' ?>>PERMIT</option>

                            <option value="ECC / CNC FILES" <?= (old('folder_type') ?? $folder['folder_type'] ?? '') === 'ECC / CNC FILES' ? 'selected' : '' ?>>ECC / CNC FILES</option>

                            <option value="IEE / EIS FILES" <?= (old('folder_type') ?? $folder['folder_type'] ?? '') === 'IEE / EIS FILES' ? 'selected' : '' ?>>IEE / EIS FILES</option>

                        </select>

                        <?php if (isset($errors['folder_type'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['folder_type'] ?></p>

                        <?php endif; ?>

                    </div>

                    <!-- Folder Category -->

                    <div>

                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">

                            Folder Category <span class="text-red-500">*</span>

                        </label>

                        <select id="category_id" name="category_id"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            required>

                            <option value="">-- Select Category --</option>

                            <?php foreach (($categories ?? []) as $category): ?>
                                <option value="<?= esc($category['category_id']) ?>" <?= (old('category_id', $folder['category_id'] ?? '') == $category['category_id'] ? 'selected' : '') ?>><?= esc($category['category_name']) ?></option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (isset($errors['category_id'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['category_id'] ?></p>

                        <?php endif; ?>

                    </div>

                    <!-- Shelf -->

                    <div>

                        <label for="shelf" class="block text-sm font-semibold text-gray-700 mb-1">

                            Shelf

                        </label>

                        <select id="shelf" name="shelf"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed"
                            disabled>
                            <option value="">-- Select Shelf --</option>
                        </select>

                        <input type="hidden" id="location_id" name="location_id" value="<?= esc(old('location_id', $folder['location_id'] ?? '')) ?>">

                        <?php if (isset($errors['location_id'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= esc($errors['location_id']) ?></p>

                        <?php endif; ?>

                    </div>

                </div>

            </div>



            <!-- Action Buttons -->

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end mt-8 pt-6 border-t">

                     <a href="/document-records" 

                         class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium w-full sm:w-auto text-center">

                    Cancel

                </a>

                <button type="submit" 

                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-600 text-white rounded-lg hover:from-green-700 hover:to-green-700 font-medium w-full sm:w-auto">

                    Request for Update

                </button>

            </div>

        </form>

    </div>

</div>


<script>
    (function () {
        const folderTypeSelect = document.getElementById('folder_type');
        const categorySelect = document.getElementById('category_id');
        const locationsData = <?= json_encode($locations ?? []) ?>;
        const selectedRack = <?= json_encode(old('cabinet', $folder['cabinet'] ?? '')) ?>;
        const selectedShelf = <?= json_encode(old('shelf', $folder['rack'] ?? '')) ?>;
        const selectedLocationId = <?= json_encode((string) old('location_id', $folder['location_id'] ?? '')) ?>;
        const rackSelect = document.getElementById('cabinet');
        const shelfSelect = document.getElementById('shelf');
        const locationIdInput = document.getElementById('location_id');
        const validationAlert = document.getElementById('editValidationAlert');
        const validationAlertMessage = document.getElementById('editValidationAlertMessage');
        let validationAlertTimer = null;

        function showEditValidationAlert(message) {
            if (!validationAlert) {
                return;
            }

            if (validationAlertMessage) {
                validationAlertMessage.textContent = message;
            }

            validationAlert.classList.remove('hidden');
            window.clearTimeout(validationAlertTimer);
            validationAlertTimer = window.setTimeout(function () {
                validationAlert.classList.add('hidden');
            }, 3500);
        }

        function labelWithPrefix(prefix, value) {
            value = String(value || '').trim();
            if (value === '') {
                return '';
            }

            return value.toLowerCase().indexOf(prefix.toLowerCase() + ' ') === 0 ? value : prefix + ' ' + value;
        }

        function populateShelves(rackValue, shelfValue) {
            if (!shelfSelect || !locationIdInput) {
                return;
            }

            shelfSelect.innerHTML = '<option value="">-- Select Shelf --</option>';
            locationIdInput.value = '';

            if (!rackValue) {
                shelfSelect.disabled = true;
                return;
            }

            const shelves = locationsData
                .filter(function (location) {
                    return location.cabinet === rackValue;
                })
                .map(function (location) {
                    return {
                        shelf: location.shelf || location.cabinet,
                        locationId: location.location_id
                    };
                });

            const uniqueShelves = Array.from(new Map(shelves.map(function (item) {
                return [item.shelf, item];
            })).values()).sort(function (left, right) {
                return String(left.shelf).localeCompare(String(right.shelf));
            });

            uniqueShelves.forEach(function (item) {
                const option = document.createElement('option');
                option.value = JSON.stringify({ shelf: item.shelf, locationId: item.locationId });
                option.textContent = labelWithPrefix('Shelf', item.shelf);
                if (String(item.shelf) === String(shelfValue) || String(item.locationId) === String(selectedLocationId)) {
                    option.selected = true;
                    locationIdInput.value = item.locationId;
                }
                shelfSelect.appendChild(option);
            });

            shelfSelect.disabled = false;
        }

        // Rack and Shelf are now read-only and locked down
        // Display current values in the disabled fields
        if (rackSelect) {
            const currentRack = <?= json_encode($folder['cabinet'] ?? '') ?>;
            if (currentRack) {
                const option = document.createElement('option');
                option.value = currentRack;
                option.textContent = 'Rack ' + currentRack;
                option.selected = true;
                rackSelect.appendChild(option);
            }
        }
        
        if (shelfSelect) {
            const currentShelf = <?= json_encode($folder['rack'] ?? '') ?>;
            if (currentShelf) {
                const option = document.createElement('option');
                option.value = currentShelf;
                option.textContent = 'Shelf ' + currentShelf;
                option.selected = true;
                shelfSelect.appendChild(option);
            }
        }
        
        // The following code is disabled to prevent user interaction
        /*
        if (rackSelect && shelfSelect && locationIdInput) {
            const racks = Array.from(new Set(locationsData.map(function (location) {
                return location.cabinet;
            }).filter(Boolean))).sort();

            racks.forEach(function (rack) {
                const option = document.createElement('option');
                option.value = rack;
                option.textContent = labelWithPrefix('Rack', rack);
                if (String(rack) === String(selectedRack)) {
                    option.selected = true;
                }
                rackSelect.appendChild(option);
            });

            rackSelect.addEventListener('change', function () {
                populateShelves(this.value, '');
            });

            shelfSelect.addEventListener('change', function () {
                locationIdInput.value = '';
                if (this.value) {
                    const selected = JSON.parse(this.value);
                    locationIdInput.value = selected.locationId;
                }
            });

            if (selectedRack) {
                populateShelves(selectedRack, selectedShelf);
            }
        }
        */

        const editForm = document.getElementById('editFolderForm');
        const recordUpdateAlertTemplate = document.getElementById('recordUpdateAlert');
        const alertsContainer = document.getElementById('alertsContainer');
        let updateClickCount = 0;
        let activeAlerts = [];
        let isSubmitting = false;

        function showRecordUpdateAlert() {
            if (!recordUpdateAlertTemplate || !alertsContainer) {
                return;
            }

            updateClickCount++;
            const alertId = 'recordUpdateAlert_' + updateClickCount;
            
            // Clone the template alert
            const newAlert = recordUpdateAlertTemplate.cloneNode(true);
            newAlert.id = alertId;
            newAlert.classList.remove('hidden');
            newAlert.style.marginBottom = '10px';
            newAlert.style.width = '340px';
            newAlert.style.display = 'block';
            
            // Update the message with the click count
            const messageElement = newAlert.querySelector('.recordUpdateAlertMessage');
            if (messageElement) {
                messageElement.textContent = 'Update #' + updateClickCount + ' - Saving your changes...';
            }
            
            // Add to container
            alertsContainer.appendChild(newAlert);
            activeAlerts.push({
                id: alertId,
                element: newAlert,
                timer: null
            });
            
            // Auto-hide after 3.5 seconds
            const alertObj = activeAlerts[activeAlerts.length - 1];
            alertObj.timer = window.setTimeout(function () {
                newAlert.style.opacity = '0';
                newAlert.style.transition = 'opacity 0.3s ease-out';
                window.setTimeout(function () {
                    if (newAlert.parentNode) {
                        newAlert.parentNode.removeChild(newAlert);
                    }
                    activeAlerts = activeAlerts.filter(function (alert) {
                        return alert.id !== alertId;
                    });
                }, 300);
            }, 3500);
        }

        if (editForm && locationIdInput) {
            editForm.addEventListener('submit', function (event) {
                // Rack and Shelf are now locked, only check that location_id exists
                if (!locationIdInput.value) {
                    event.preventDefault();
                    showEditValidationAlert('Location information is missing.');
                } else if (!isSubmitting) {
                    event.preventDefault();
                    isSubmitting = true;
                    showRecordUpdateAlert();
                    
                    // Submit the form after 3 seconds
                    window.setTimeout(function () {
                        editForm.submit();
                    }, 1000);
                }
            });
        }

        // Category options come directly from the database and are shown as plain names.
    })();
</script>



<?= $this->endSection() ?>

