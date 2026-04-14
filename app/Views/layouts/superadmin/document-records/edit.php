<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div class="max-w-10xl mx-auto">

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

        

        <form action="<?= route_to('records.update', $folder['folder_id']) ?>" method="POST" class="p-6" id="editFolderForm">

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

                    <!-- Borrowed Date -->

                    <div>

                        <label for="borrowed_date" class="block text-sm font-semibold text-gray-700 mb-1">

                            Borrowed Date

                        </label>

                        <input type="date" id="borrowed_date" name="borrowed_date" 

                            value="<?= old('borrowed_date', $folder['borrowed_date'] ?? '') ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                        <?php if (isset($errors['borrowed_date'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['borrowed_date'] ?></p>

                        <?php endif; ?>

                    </div>



                    <!-- Location Code (Read-only) -->

                    <div>

                        <label for="location_code" class="block text-sm font-semibold text-gray-700 mb-1">

                            Location Code

                        </label>

                        <input type="text" id="location_code" name="location_code" 

                            value="<?= esc($folder['location_code']) ?>"

                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed"

                            readonly>

                    </div>



                    <!-- Cabinet -->

                    <div>

                        <label for="cabinet" class="block text-sm font-semibold text-gray-700 mb-1">

                            Cabinet

                        </label>

                        <input type="text" id="cabinet" name="cabinet" 

                            value="<?= old('cabinet') ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            placeholder="Enter cabinet number">

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

                    <!-- Status -->

                    <div>

                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">

                            Status

                        </label>

                        <select id="status" name="status" 

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                            <option value="Available" <?= $folder['status'] === 'Available' ? 'selected' : '' ?>>Available</option>

                            <option value="Borrowed" <?= $folder['status'] === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>

                            <option value="Archived" <?= $folder['status'] === 'Archived' ? 'selected' : '' ?>>Archived</option>

                            <option value="Disposed" <?= $folder['status'] === 'Disposed' ? 'selected' : '' ?>>Disposed</option>

                        </select>

                    </div>



                    <!-- Due Date -->

                    <div>

                        <label for="due_date" class="block text-sm font-semibold text-gray-700 mb-1">

                            Due Date

                        </label>

                        <input type="date" id="due_date" name="due_date" 

                            value="<?= old('due_date', $folder['due_date'] ?? '') ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                        <?php if (isset($errors['due_date'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['due_date'] ?></p>

                        <?php endif; ?>

                    </div>



                    <!-- Rack -->

                    <div>

                        <label for="rack" class="block text-sm font-semibold text-gray-700 mb-1">

                            Rack

                        </label>

                        <input type="text" id="rack" name="rack" 

                            value="<?= old('rack') ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            placeholder="Enter rack number">

                    </div>



                    <!-- Notes/Additional Info (Optional) -->

                    <div>


                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">

                            Notes

                        </label>

                        <textarea id="notes" rows="3"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            placeholder="Add any additional notes..."></textarea>

                    </div>

                </div>

            </div>



            <!-- Action Buttons -->

            <div class="flex gap-3 justify-end mt-8 pt-6 border-t">

                <a href="/document-records" 

                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">

                    Cancel

                </a>

                <button type="submit" 

                        class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-600 text-white rounded-lg hover:from-green-700 hover:to-green-700 font-medium">

                    Update Record

                </button>

            </div>

        </form>

    </div>

</div>


<script>
    (function () {
        const folderTypeSelect = document.getElementById('folder_type');
        const categorySelect = document.getElementById('category_id');
        if (!folderTypeSelect || !categorySelect) {
            return;
        }

        // Category options come directly from the database and are shown as plain names.
    })();
</script>



<?= $this->endSection() ?>

