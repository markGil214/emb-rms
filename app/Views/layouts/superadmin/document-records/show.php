<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div class="max-w-8xl mx-auto" x-data="{ uploadModalOpen: <?= (!empty($uploadErrors['pdf_file']) || !empty($uploadErrors['retention_type'])) ? 'true' : 'false' ?> }">

    <?php $uploadErrors = session('errors') ?? []; ?>

    <?php if (!empty($uploadErrors['pdf_file'])): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= esc($uploadErrors['pdf_file']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($uploadErrors['retention_type'])): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= esc($uploadErrors['retention_type']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($uploadErrors['folder_id'])): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= esc($uploadErrors['folder_id']) ?>
        </div>
    <?php endif; ?>

    <!-- Document Details -->

      <div class="flex justify-between items-center mb-4">

        <a href="<?= route_to('records') ?>" 

           class="px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-300 font-medium inline-flex items-center">

            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>

            </svg>

            Back to Records

        </a>

        <?php if (!in_array($folder['status'] ?? '', ['Pending', 'Pending Update'], true)): ?>
        <div class="flex items-center gap-2">
            <a href="<?= route_to('records.history', $folder['folder_id']) ?>"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                History
            </a>

            <button type="button"
                    @click="uploadModalOpen = true"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Upload Documents
            </button>
        </div>
        <?php endif; ?>

    </div>



    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">

        <div class="bg-gray-200 px-4 py-3 flex items-center justify-between">

            <h2 class="text-lg font-semibold text-gray-600">Document Details</h2>

        </div>

        

        <div class="p-4">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <!-- File Code -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">File Code</label>

                    <p class="text-base font-bold text-gray-900"><?= esc($folder['file_code']) ?></p>

                </div>



                <!-- Company Name -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Company Name</label>

                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['company_name']) ?></p>

                </div>



                <!-- Location Code -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Location Code</label>

                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['location_code']) ?></p>

                </div>



                <!-- Borrowed Date -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Borrowed Date</label>

                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['borrowed_date'] ?? 'N/A') ?></p>

                </div>



                <!-- Due Date -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Due Date</label>

                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['due_date'] ?? 'N/A') ?></p>

                </div>



                <!-- Status -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>

                    <?php

                    if ($folder['status'] === 'Available') {

                        $statusColor = 'bg-green-200 text-green-800';

                    } elseif ($folder['status'] === 'Archived') {

                        $statusColor = 'bg-gray-200 text-gray-800';

                    } else {

                        $statusColor = 'bg-yellow-200 text-yellow-800';

                    }

                    ?>

                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">

                        <?= esc($folder['status']) ?>

                    </span>

                </div>



                <!-- Cabinet -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cabinet</label>

                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['cabinet'] ?? 'N/A') ?></p>

                </div>



                <!-- Rack -->

                <div class="bg-gray-50 rounded-lg p-3">

                    <label class="block text-xs font-semibold text-gray-600 mb-1">Rack</label>

                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['rack'] ?? 'N/A') ?></p>

                </div>

            </div>



            </div>

    </div>

    <?php if (!in_array($folder['status'] ?? '', ['Pending', 'Pending Update'], true)): ?>

    <!-- Uploaded Files List -->

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">

        <div class="bg-gray-200 px-4 py-3">

            <h2 class="text-lg font-semibold text-gray-600">Uploaded Documents</h2>

        </div>

        

        <div class="p-4">

            <?php if (empty($files)): ?>

                <div class="text-center py-8">

                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>

                    </svg>

                    <h3 class="text-lg font-medium text-gray-900 mb-2">No documents uploaded yet</h3>

                    <p class="text-gray-600">Upload your first PDF document using the form above.</p>

                </div>

            <?php else: ?>

                <div class="overflow-x-auto">

                    <table class="w-full border-collapse">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">File Name</th>

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Size</th>

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Retention</th>

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expiration Date</th>

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Uploaded</th>

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>

                            </tr>

                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">

                            <?php foreach ($files as $file): ?>

                                <tr class="hover:bg-gray-50">

                                    <td class="border px-4 py-2">

                                        <div class="flex items-center">

                                            <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">

                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 011.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293 1.293a1 1 0 001.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>

                                            </svg>

                                            <span class="font-medium text-gray-900 text-sm"><?= esc($file['file_name']) ?></span>

                                        </div>

                                    </td>

                                    <td class="border px-4 py-2 text-sm text-gray-600"><?= \App\Models\FolderFileModel::formatFileSize($file['file_size']) ?></td>

                                    <?php $retentionType = $file['retention_type'] ?? 'permanent'; ?>
                                    <td class="border px-4 py-2 text-sm text-gray-700">
                                        <?php if ($retentionType === 'expiration'): ?>
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Expires in 5 years</span>
                                        <?php else: ?>
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Permanent</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="border px-4 py-2 text-sm text-gray-600">
                                        <?php if ($retentionType === 'expiration' && !empty($file['expiration_date'])): ?>
                                            <?= date('M d, Y', strtotime($file['expiration_date'])) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td class="border px-4 py-2 text-sm text-gray-600"><?= date('M d, Y H:i', strtotime($file['created_at'])) ?></td>

                                    <td class="border px-4 py-2">

                                        <div class="flex space-x-2">

                                            <a href="<?= route_to('file.download', $file['file_id']) ?>" 

                                                class="text-blue-600 hover:text-blue-800 font-medium text-sm">Download</a>

                                            <form action="<?= route_to('file.delete', $file['file_id']) ?>" method="POST" style="display:inline;" data-confirm-message="Delete this file?">

                                                <?= csrf_field() ?>

                                                <input type="hidden" name="_method" value="DELETE">

                                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Delete</button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

    <?php else: ?>
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="p-6 text-center text-gray-600">
            Documents and history will be available after approval.
        </div>
    </div>
    <?php endif; ?>


    <div x-show="uploadModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4 py-8">
            <div class="fixed inset-0 bg-black/50" @click="uploadModalOpen = false"></div>

            <div class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-800">Upload Document</h2>
                    <button type="button" @click="uploadModalOpen = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <form action="<?= route_to('file.upload', $folder['folder_id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <?= csrf_field() ?>

                        <div>
                            <label for="pdf_file_modal" class="block text-sm font-semibold text-gray-700 mb-1">
                                PDF File <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-400">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0 0l3 3v12"></path>
                                </svg>

                                <input type="file"
                                       id="pdf_file_modal"
                                       name="pdf_file"
                                       accept=".pdf"
                                       class="mb-3 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                       required>

                                <label for="pdf_file_modal" class="cursor-pointer">
                                    <span class="text-blue-600 font-medium hover:text-blue-800">Choose a PDF file</span>
                                    <span class="text-gray-500"> or drag and drop</span>
                                </label>
                                <p class="text-gray-600 text-sm mt-1">Maximum file size: 10MB</p>
                            </div>
                        </div>

                        <div>
                            <label for="retention_type_modal" class="block text-sm font-semibold text-gray-700 mb-1">
                                File Retention <span class="text-red-500">*</span>
                            </label>
                            <select id="retention_type_modal"
                                    name="retention_type"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                    required>
                                <option value="permanent" <?= old('retention_type', 'permanent') === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                <option value="expiration" <?= old('retention_type') === 'expiration' ? 'selected' : '' ?>>Expires in 5 years</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-600">If you choose expiration, the system automatically sets the expiry date to 5 years from upload.</p>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button"
                                    @click="uploadModalOpen = false"
                                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 font-medium">
                                Upload PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection() ?>



