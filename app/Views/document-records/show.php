<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div class="mx-auto w-full max-w-8xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ uploadModalOpen: <?= (!empty($uploadErrors['file']) || !empty($uploadErrors['retention_type'])) ? 'true' : 'false' ?> }">

    <?php $uploadErrors = session('errors') ?? []; ?>

    <?php if (!empty($uploadErrors['file'])): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= esc($uploadErrors['file']) ?>
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

    <?php
        $statusValue = (string) ($folder['status'] ?? 'Unknown');

        if ($statusValue === 'Available') {
            $statusColor = 'bg-green-200 text-green-800';
        } elseif ($statusValue === 'Archived') {
            $statusColor = 'bg-gray-200 text-gray-800';
        } else {
            $statusColor = 'bg-yellow-200 text-yellow-800';
        }

        $locationParts = [];
        if (!empty($folder['cabinet'])) {
            $locationParts[] = 'Cabinet ' . $folder['cabinet'];
        }
        if (!empty($folder['rack'])) {
            $locationParts[] = 'Rack ' . $folder['rack'];
        }
        if (!empty($folder['shelf'])) {
            $locationParts[] = 'Shelf ' . $folder['shelf'];
        }
        $locationSummary = !empty($locationParts) ? implode(' • ', $locationParts) : '--';

        if (in_array($statusValue, ['Borrowed', 'Overdue', 'Returned'], true)) {
            $borrowStatusValue = $statusValue;
        } elseif (in_array($statusValue, ['Pending', 'Pending Update'], true)) {
            $borrowStatusValue = 'Pending';
        } elseif ($statusValue === 'Archived') {
            $borrowStatusValue = 'Unavailable';
        } else {
            $borrowStatusValue = 'Available';
        }

        if ($borrowStatusValue === 'Overdue') {
            $borrowStatusColor = 'bg-red-200 text-red-800';
        } elseif ($borrowStatusValue === 'Borrowed') {
            $borrowStatusColor = 'bg-yellow-200 text-yellow-800';
        } elseif ($borrowStatusValue === 'Returned' || $borrowStatusValue === 'Available') {
            $borrowStatusColor = 'bg-green-200 text-green-800';
        } else {
            $borrowStatusColor = 'bg-gray-200 text-gray-800';
        }

        $borrowedDateValue = !empty($folder['borrowed_date']) ? date('M d, Y', strtotime($folder['borrowed_date'])) : '--';
        $dueDateValue = !empty($folder['due_date']) ? date('M d, Y', strtotime($folder['due_date'])) : '--';
    ?>

    <div class="mb-6">
        <a href="<?= route_to('records') ?>" class="mb-3 inline-flex items-center rounded-lg px-3 py-2 font-medium text-gray-700 hover:bg-gray-200">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Records
        </a>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= esc($folder['file_code']) ?> - <?= esc($folder['company_name']) ?></h1>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= $statusColor ?>">Status: <?= esc($statusValue) ?></span>

            <?php // JS: compute expiration date preview for retention_type === 'expiration' ?>
            <script>
                (function(){
                    function initExpirationFields() {
                        const retentionSelect = document.getElementById('retention_type_modal');
                        const yearsWrapper = document.getElementById('expirationYearsWrapper_modal');
                        const yearsInput = document.getElementById('expiration_years_modal');
                        const preview = document.getElementById('expirationPreview_modal');
                        const hidden = document.getElementById('expiration_date_modal');

                        function formatDateYmd(date){
                            const y = date.getFullYear();
                            const m = String(date.getMonth()+1).padStart(2,'0');
                            const d = String(date.getDate()).padStart(2,'0');
                            return `${y}-${m}-${d}`;
                        }

                        function updateExpirationPreview(){
                            if (!retentionSelect || !yearsWrapper || !yearsInput || !preview || !hidden) return;
                            if (retentionSelect.value === 'expiration'){
                                yearsWrapper.style.display = 'block';
                                yearsInput.disabled = false;
                                yearsInput.required = true;
                                const years = parseInt(yearsInput.value, 10);
                                if (!Number.isInteger(years) || years < 1) {
                                    hidden.value = '';
                                    preview.textContent = 'Enter a valid number of years to preview expiration date.';
                                    preview.style.display = 'block';
                                    return;
                                }

                                const now = new Date();
                                const expires = new Date(now.getFullYear() + years, now.getMonth(), now.getDate());
                                hidden.value = formatDateYmd(expires);
                                preview.textContent = `Expires on: ${expires.toLocaleDateString()} (${years} year${years === 1 ? '' : 's'})`;
                                preview.style.display = 'block';
                            } else {
                                yearsWrapper.style.display = 'none';
                                yearsInput.required = false;
                                yearsInput.disabled = true;
                                hidden.value = '';
                                preview.textContent = '';
                                preview.style.display = 'none';
                            }
                        }

                        if (!retentionSelect || !yearsInput) {
                            return;
                        }

                        retentionSelect.addEventListener('change', updateExpirationPreview);
                        yearsInput.addEventListener('input', updateExpirationPreview);
                        updateExpirationPreview();
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initExpirationFields);
                    } else {
                        initExpirationFields();
                    }
                })();
            </script>
                    <span class="text-sm text-gray-600">Location: <?= esc($locationSummary) ?></span>
                </div>
            </div>

            <?php if (!in_array($folder['status'] ?? '', ['Pending', 'Pending Update', 'Archived'], true)): ?>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        @click="uploadModalOpen = true"
                        class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Upload Document
                </button>

                <a href="<?= route_to('records.history', $folder['folder_id']) ?>"
                   class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 font-medium text-blue-700 hover:bg-blue-100">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    View History
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg bg-white shadow-lg">
            <div class="bg-gray-200 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-700">Document Information</h2>
            </div>
            <div class="space-y-4 p-4">
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">File Code</label>
                    <p class="text-base font-bold text-gray-900"><?= esc($folder['file_code']) ?></p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Company Name</label>
                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['company_name']) ?></p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Location Code</label>
                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['location_code'] ?? '--') ?></p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Location</label>
                    <p class="text-base font-semibold text-gray-900"><?= esc($locationSummary) ?></p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Status</label>
                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold <?= $statusColor ?>"><?= esc($statusValue) ?></span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-lg">
            <div class="bg-gray-200 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-700">Borrow Information</h2>
            </div>
            <div class="space-y-4 p-4">
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Borrow Status</label>
                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold <?= $borrowStatusColor ?>"><?= esc($borrowStatusValue) ?></span>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Borrowed Date</label>
                    <p class="text-base font-semibold text-gray-900"><?= esc($borrowedDateValue) ?></p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Due Date</label>
                    <p class="text-base font-semibold text-gray-900"><?= esc($dueDateValue) ?></p>
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

                    <p class="text-gray-600">Upload a file to attach it to this record.</p>

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

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>

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
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">With expiration</span>
                                        <?php else: ?>
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Permanent</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="border px-4 py-2 text-sm text-gray-600">
                                        <?php
                                            $expirationDisplay = '-';
                                            $expirationRaw = trim((string) ($file['expiration_date'] ?? ''));
                                            $isReadyForDisposal = false;
                                            $explicitDisposalStatus = strtolower(trim((string) ($file['disposal_status'] ?? '')));
                                            if (
                                                $retentionType === 'expiration' &&
                                                $expirationRaw !== '' &&
                                                $expirationRaw !== '0000-00-00' &&
                                                $expirationRaw !== '0000-00-00 00:00:00'
                                            ) {
                                                try {
                                                    $expirationDate = new \DateTimeImmutable($expirationRaw);
                                                    $expirationDisplay = $expirationDate->format('M d, Y');
                                                    $isReadyForDisposal = $expirationDate <= new \DateTimeImmutable('today');
                                                } catch (\Exception $e) {
                                                    $expirationDisplay = '-';
                                                    $isReadyForDisposal = false;
                                                }
                                            }

                                            $disposalStatusLabel = 'Not Yet Eligible';
                                            $disposalStatusClass = 'bg-gray-100 text-gray-700';

                                            if (in_array($explicitDisposalStatus, ['requested', 'pending'], true)) {
                                                $disposalStatusLabel = 'Disposal Requested';
                                                $disposalStatusClass = 'bg-blue-100 text-blue-700';
                                            } elseif (in_array($explicitDisposalStatus, ['disposed', 'approved', 'completed'], true)) {
                                                $disposalStatusLabel = 'Disposed';
                                                $disposalStatusClass = 'bg-red-100 text-red-700';
                                            } elseif ($isReadyForDisposal) {
                                                $disposalStatusLabel = 'Eligible for Disposal';
                                                $disposalStatusClass = 'bg-orange-100 text-orange-700';
                                            }

                                            $canRequestDisposal = $isReadyForDisposal && $explicitDisposalStatus === '';
                                        ?>
                                        <div class="flex flex-col gap-1">
                                            <span><?= esc($expirationDisplay) ?></span>
                                        </div>
                                    </td>

                                    <td class="border px-4 py-2 text-sm text-gray-600"><?= date('M d, Y H:i', strtotime($file['created_at'])) ?></td>

                                    <td class="border px-4 py-2 text-sm text-gray-600">
                                        <span class="inline-flex w-fit rounded-full px-2 py-1 text-xs font-semibold <?= esc($disposalStatusClass) ?>"><?= esc($disposalStatusLabel) ?></span>
                                    </td>

                                    <td class="border px-4 py-2">

                                        <div class="flex space-x-2">

                                            <a href="<?= route_to('file.download', $file['file_id']) ?>" 

                                                class="text-blue-600 hover:text-blue-800 font-medium text-sm">Download</a>

                                            <?php if ($canRequestDisposal): ?>
                                                <form action="<?= route_to('file.request-disposal', $file['file_id']) ?>" method="POST" style="display:inline;" data-confirm-message="Submit disposal request for this file?">

                                                    <?= csrf_field() ?>

                                                    <button type="submit" class="text-orange-600 hover:text-orange-800 font-medium text-sm">Request Disposal</button>

                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray-400 font-medium text-sm">-</span>
                                            <?php endif; ?>

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
                            <label for="file_modal" class="block text-sm font-semibold text-gray-700 mb-1">
                                File <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-400">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0 0l3 3v12"></path>
                                </svg>

                                <input type="file"
                                       id="file_modal"
                                       name="file"
                                       class="mb-3 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                       required>

                                <label for="file_modal" class="cursor-pointer">
                                    <span class="text-blue-600 font-medium hover:text-blue-800">Choose a file</span>
                                    <span class="text-gray-500"> or drag and drop</span>
                                </label>
                                <p class="text-gray-600 text-sm mt-1">Maximum file size: 30MB</p>
                            </div>
                            <?php if (!empty($uploadErrors['file'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['file']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="retention_type_modal" class="block text-sm font-semibold text-gray-700 mb-1">
                                File Retention <span class="text-red-500">*</span>
                            </label>
                            <select id="retention_type_modal"
                                    name="retention_type"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                    required>
                                <option value="" <?= old('retention_type') === '' ? 'selected' : '' ?>>-- Select Retention --</option>
                                <option value="permanent" <?= old('retention_type') === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                <option value="expiration" <?= old('retention_type') === 'expiration' ? 'selected' : '' ?>>Expiration</option>
                            </select>
                            <?php if (!empty($uploadErrors['retention_type'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['retention_type']) ?></p>
                            <?php endif; ?>
                            <div id="expirationYearsWrapper_modal" class="mt-3" style="display: <?= old('retention_type') === 'expiration' ? 'block' : 'none' ?>;">
                                <label for="expiration_years_modal" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Expiration Years <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       id="expiration_years_modal"
                                       name="expiration_years"
                                       min="1"
                                        max="30"
                                       step="1"
                                       value="<?= esc(old('expiration_years', '5')) ?>"
                                       class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                       <?= old('retention_type') === 'expiration' ? 'required' : 'disabled' ?>>
                                <?php if (!empty($uploadErrors['expiration_years'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['expiration_years']) ?></p>
                                <?php endif; ?>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">If you choose expiration, enter 1 to 30 years from today for expiry.</p>
                            <p id="expirationPreview_modal" class="mt-2 text-sm text-gray-700" style="display:none;"></p>
                            <input type="hidden" name="expiration_date" id="expiration_date_modal" value="<?= esc(old('expiration_date')) ?>">
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button"
                                    @click="uploadModalOpen = false"
                                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 font-medium">
                                Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection() ?>



