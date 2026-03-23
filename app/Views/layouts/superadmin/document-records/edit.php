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

        

        <form action="<?= route_to('records.update', $folder['folder_id']) ?>" method="POST" class="p-6">

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

                    <div>

                        <label for="issuance_date" class="block text-sm font-semibold text-gray-700 mb-1">

                            Issuance Date <span class="text-red-500">*</span>

                        </label>

                        <input type="date" id="issuance_date" name="issuance_date" 

                            value="<?= old('issuance_date', $folder['issuance_date']) ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            required>

                        <?php if (isset($errors['issuance_date'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['issuance_date'] ?></p>

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



                    <!-- Expiry Date -->

                    <div>

                        <label for="expiry_date" class="block text-sm font-semibold text-gray-700 mb-1">

                            Expiry Date <span class="text-red-500">*</span>

                        </label>

                        <input type="date" id="expiry_date" name="expiry_date" 

                            value="<?= old('expiry_date', $folder['expiry_date']) ?>"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            required>

                        <?php if (isset($errors['expiry_date'])): ?>

                            <p class="mt-1 text-sm text-red-600"><?= $errors['expiry_date'] ?></p>

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

                        <textarea id="notes" name="notes" rows="3"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"

                            placeholder="Add any additional notes..."><?= old('notes') ?></textarea>

                    </div>

                </div>

            </div>



            <!-- Action Buttons -->

            <div class="flex gap-3 justify-end mt-8 pt-6 border-t">

                <a href="/permits" 

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



<?= $this->endSection() ?>

