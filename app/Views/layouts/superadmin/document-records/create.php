<?= $this->extend('layouts/superadmin/document-records/main') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto">
    
    <!-- Form Card -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gray-400 px-4 py-3">
            <h2 class="text-lg font-semibold text-gray-800">Document Information</h2>
        </div>
        
        <form action="<?= route_to('records.store') ?>" method="POST" class="p-4 space-y-4" id="folderForm">
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
                
                <!-- Document Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 pt-2">
                        Document Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="">Select Status</option>
                        <option value="Available" 
                                <?= old('status') === 'Available' ? 'selected' : '' ?>>
                            Available
                        </option>
                        <option value="Borrowed" 
                                <?= old('status') === 'Borrowed' ? 'selected' : '' ?>>
                            Borrowed
                        </option>
                        <option value="Archived" 
                                <?= old('status') === 'Archived' ? 'selected' : '' ?>>
                            Archived
                        </option>
                        <option value="Disposed" 
                                <?= old('status') === 'Disposed' ? 'selected' : '' ?>>
                            Disposed
                        </option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['status'] ?></p>
                    <?php elseif (session()->getFlashdata('errors.status')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= session()->getFlashdata('errors.status') ?></p>
                    <?php elseif (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['status'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= session()->getFlashdata('errors')['status'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dates Section -->
            <div class="border-t pt-4">
                <h3 class="text-base font-medium text-gray-900 mb-3">Important Dates</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="issuance_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Issuance Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="issuance_date" name="issuance_date" 
                            value="<?= old('issuance_date') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <?php if (isset($errors['issuance_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['issuance_date'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Expiry Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="expiry_date" name="expiry_date" 
                            value="<?= old('expiry_date') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <?php if (isset($errors['expiry_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['expiry_date'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="border-t pt-4">
                <h3 class="text-base font-medium text-gray-900 mb-3">Location Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="cabinet" class="block text-sm font-medium text-gray-700 mb-1">
                            Cabinet <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="cabinet" name="cabinet" 
                            value="<?= old('cabinet') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., A-01" required>
                        <?php if (isset($errors['cabinet'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['cabinet'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="rack" class="block text-sm font-medium text-gray-700 mb-1">
                            Rack <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="rack" name="rack" 
                            value="<?= old('rack') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., R-01" required>
                        <?php if (isset($errors['rack'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['rack'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    <label for="location_id" class="block text-sm font-medium text-gray-700 pt-2">
                        Location ID <span class="text-red-500">*</span>
                    </label>
                    <div class="md:col-span-2">
                        <input type="number" id="location_id" name="location_id" 
                            value="<?= old('location_id') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter location ID" required>
                        <?php if (isset($errors['location_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['location_id'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

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
            issuance_date: '<?= old('issuance_date') ?>',
            expiry_date: '<?= old('expiry_date') ?>',
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
