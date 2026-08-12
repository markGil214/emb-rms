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
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold text-gray-900"><?= esc($folder['file_code']) ?> - <?= esc($folder['company_name']) ?></h1>
                
                <?php if (in_array($statusValue, ['Pending', 'Pending Update'], true)): ?>
                    <div class="flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 border border-blue-200 text-blue-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium">This folder is currently awaiting approval. Some actions may be restricted.</span>
                    </div>
                <?php endif; ?>

                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= $statusColor ?>">Status: <?= esc($statusValue) ?></span>

            <?php // JS: compute expiration date preview for retention_type === 'expiration' ?>
            <script>
                (function(){
                    function initExpirationFields() {
                        const retentionSelect = document.getElementById('retention_type_modal');
                        const fieldsWrapper = document.getElementById('expirationFieldsWrapper_modal');
                        const yearsInput = document.getElementById('expiration_years_modal');
                        const monthsInput = document.getElementById('expiration_months_modal');
                        const daysInput = document.getElementById('expiration_days_modal');
                        const preview = document.getElementById('expirationPreview_modal');
                        const hidden = document.getElementById('expiration_date_modal');

                        function formatDateYmd(date){
                            const y = date.getFullYear();
                            const m = String(date.getMonth()+1).padStart(2,'0');
                            const d = String(date.getDate()).padStart(2,'0');
                            return `${y}-${m}-${d}`;
                        }

                        function updateExpirationPreview(){
                            if (!retentionSelect || !fieldsWrapper || !yearsInput || !monthsInput || !daysInput || !preview || !hidden) return;
                            if (retentionSelect.value === 'expiration'){
                                fieldsWrapper.style.display = 'block';
                                yearsInput.disabled = false;
                                monthsInput.disabled = false;
                                daysInput.disabled = false;
                                const years = parseInt(yearsInput.value, 10) || 0;
                                const months = parseInt(monthsInput.value, 10) || 0;
                                const days = parseInt(daysInput.value, 10) || 0;
                                if (years === 0 && months === 0 && days === 0) {
                                    hidden.value = '';
                                    preview.textContent = 'Enter at least one value (years, months, or days) to preview expiration date.';
                                    preview.style.display = 'block';
                                    return;
                                }

                                const now = new Date();
                                const expires = new Date(now.getFullYear() + years, now.getMonth() + months, now.getDate() + days);
                                hidden.value = formatDateYmd(expires);
                                const parts = [];
                                if (years > 0) parts.push(years + ' year' + (years === 1 ? '' : 's'));
                                if (months > 0) parts.push(months + ' month' + (months === 1 ? '' : 's'));
                                if (days > 0) parts.push(days + ' day' + (days === 1 ? '' : 's'));
                                preview.textContent = `Expires on: ${expires.toLocaleDateString()} (${parts.join(', ')})`;
                                preview.style.display = 'block';
                            } else {
                                fieldsWrapper.style.display = 'none';
                                yearsInput.disabled = true;
                                monthsInput.disabled = true;
                                daysInput.disabled = true;
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
                        monthsInput.addEventListener('input', updateExpirationPreview);
                        daysInput.addEventListener('input', updateExpirationPreview);
                        updateExpirationPreview();
                    }

                    // Preview the chosen batch so the user can confirm what is
                    // about to be uploaded, and catch oversized files before
                    // the request is sent rather than after.
                    function initSelectedFiles() {
                        const input = document.getElementById('file_modal');
                        const wrapper = document.getElementById('selectedFilesList_modal');
                        const items = document.getElementById('selectedFilesItems_modal');
                        const countEl = document.getElementById('selectedFilesCount_modal');
                        const sizeEl = document.getElementById('selectedFilesSize_modal');
                        const warningEl = document.getElementById('selectedFilesWarning_modal');

                        if (!input || !wrapper || !items) {
                            return;
                        }

                        const MAX_BYTES = 30 * 1024 * 1024;
                        const MAX_FILES = 20;

                        function formatBytes(bytes) {
                            const units = ['B', 'KB', 'MB', 'GB'];
                            let i = 0;
                            let value = bytes;
                            while (value >= 1024 && i < units.length - 1) {
                                value /= 1024;
                                i++;
                            }
                            return (i === 0 ? value : value.toFixed(1)) + ' ' + units[i];
                        }

                        input.addEventListener('change', function () {
                            const files = Array.from(this.files || []);
                            items.innerHTML = '';

                            if (!files.length) {
                                wrapper.classList.add('hidden');
                                return;
                            }

                            let totalBytes = 0;
                            const problems = [];

                            files.forEach(function (file) {
                                totalBytes += file.size;
                                const tooBig = file.size > MAX_BYTES;
                                if (tooBig) {
                                    problems.push(file.name + ' exceeds 30MB');
                                }

                                const li = document.createElement('li');
                                li.className = 'flex items-center justify-between gap-2 px-3 py-2';

                                const name = document.createElement('span');
                                name.className = 'truncate ' + (tooBig ? 'text-red-600' : 'text-gray-700');
                                name.textContent = file.name;
                                name.title = file.name;

                                const size = document.createElement('span');
                                size.className = 'whitespace-nowrap text-xs ' + (tooBig ? 'text-red-600 font-semibold' : 'text-gray-500');
                                size.textContent = formatBytes(file.size);

                                li.appendChild(name);
                                li.appendChild(size);
                                items.appendChild(li);
                            });

                            if (files.length > MAX_FILES) {
                                problems.push('Only ' + MAX_FILES + ' files can be uploaded at a time (' + files.length + ' selected)');
                            }

                            countEl.textContent = files.length;
                            sizeEl.textContent = formatBytes(totalBytes) + ' total';

                            if (problems.length) {
                                warningEl.textContent = problems.join('. ') + '.';
                                warningEl.classList.remove('hidden');
                            } else {
                                warningEl.textContent = '';
                                warningEl.classList.add('hidden');
                            }

                            wrapper.classList.remove('hidden');
                        });
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function () {
                            initExpirationFields();
                            initSelectedFiles();
                        });
                    } else {
                        initExpirationFields();
                        initSelectedFiles();
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
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Company Location</label>
                    <p class="text-base font-semibold text-gray-900"><?= esc($folder['company_location'] ?? '') !== '' ? esc($folder['company_location']) : '--' ?></p>
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

    <?php if (true): // Always show documents section if there are files or if we want to show the 'No documents' message ?>

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

                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Disposal Status</th>

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

                                            <a href="<?= route_to('file.view', $file['file_id']) ?>" target="_blank" class="font-medium text-blue-600 hover:text-blue-800 text-sm hover:underline"><?= esc($file['file_name']) ?></a>

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
                                                $disposalStatusLabel = 'Pending Disposal';
                                                $disposalStatusClass = 'bg-blue-100 text-blue-700';
                                            } elseif ($explicitDisposalStatus === 'approved') {
                                                $disposalStatusLabel = 'Approved for Disposal';
                                                $disposalStatusClass = 'bg-green-100 text-green-700';
                                            } elseif (in_array($explicitDisposalStatus, ['disposed', 'completed'], true)) {
                                                $disposalStatusLabel = 'Disposed';
                                                $disposalStatusClass = 'bg-red-100 text-red-700';
                                            } elseif ($explicitDisposalStatus === 'rejected') {
                                                $disposalStatusLabel = 'Disposal Rejected';
                                                $disposalStatusClass = 'bg-red-100 text-red-800 border border-red-200';
                                            } elseif ($isReadyForDisposal) {
                                                $disposalStatusLabel = 'Eligible for Disposal';
                                                $disposalStatusClass = 'bg-orange-100 text-orange-700';
                                            }

                                            $canRequestDisposal = $isReadyForDisposal && ($explicitDisposalStatus === '' || $explicitDisposalStatus === 'rejected');
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

                                        <div class="flex items-center gap-2">

                                            <?= view('components/button', [
                                                'label' => 'Download',
                                                'url' => route_to('file.download', $file['file_id']),
                                                'style' => 'info',
                                            ]) ?>

                                            <?php if ($canRequestDisposal): ?>
                                                <?= view('components/button', [
                                                    'label' => 'Request Disposal',
                                                    'type' => 'submit',
                                                    'style' => 'warning',
                                                    'action' => route_to('file.request-disposal', $file['file_id']),
                                                    'confirm' => 'Submit disposal request for this file?',
                                                ]) ?>
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
                                Files <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-400">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m0 0l3 3v12"></path>
                                </svg>

                                <input type="file"
                                       id="file_modal"
                                       name="files[]"
                                       multiple
                                       class="mb-3 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                       required>

                                <label for="file_modal" class="cursor-pointer">
                                    <span class="text-blue-600 font-medium hover:text-blue-800">Choose files</span>
                                    <span class="text-gray-500"> or drag and drop</span>
                                </label>
                                <p class="text-gray-600 text-sm mt-1">Up to 20 files, 30MB each</p>
                            </div>

                            <!-- Populated by JS so the user can confirm the batch before submitting. -->
                            <div id="selectedFilesList_modal" class="mt-3 hidden">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-semibold text-gray-700">
                                        Selected: <span id="selectedFilesCount_modal">0</span>
                                    </p>
                                    <p id="selectedFilesSize_modal" class="text-xs text-gray-500"></p>
                                </div>
                                <ul id="selectedFilesItems_modal" class="max-h-40 overflow-y-auto rounded-md border border-gray-200 divide-y divide-gray-100 text-sm"></ul>
                                <p id="selectedFilesWarning_modal" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            <?php if (!empty($uploadErrors['files'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['files']) ?></p>
                            <?php endif; ?>
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
                            <div id="expirationFieldsWrapper_modal" class="mt-3" style="display: <?= old('retention_type') === 'expiration' ? 'block' : 'none' ?>;">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Expiration Duration <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label for="expiration_years_modal" class="block text-xs font-medium text-gray-500 mb-1">Years</label>
                                        <input type="number"
                                               id="expiration_years_modal"
                                               name="expiration_years"
                                               min="0"
                                               max="30"
                                               step="1"
                                               value="<?= esc(old('expiration_years', '0')) ?>"
                                               class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                               <?= old('retention_type') === 'expiration' ? '' : 'disabled' ?>>
                                    </div>
                                    <div>
                                        <label for="expiration_months_modal" class="block text-xs font-medium text-gray-500 mb-1">Months</label>
                                        <input type="number"
                                               id="expiration_months_modal"
                                               name="expiration_months"
                                               min="0"
                                               max="11"
                                               step="1"
                                               value="<?= esc(old('expiration_months', '0')) ?>"
                                               class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                               <?= old('retention_type') === 'expiration' ? '' : 'disabled' ?>>
                                    </div>
                                    <div>
                                        <label for="expiration_days_modal" class="block text-xs font-medium text-gray-500 mb-1">Days</label>
                                        <input type="number"
                                               id="expiration_days_modal"
                                               name="expiration_days"
                                               min="0"
                                               max="30"
                                               step="1"
                                               value="<?= esc(old('expiration_days', '0')) ?>"
                                               class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700"
                                               <?= old('retention_type') === 'expiration' ? '' : 'disabled' ?>>
                                    </div>
                                </div>
                                <?php if (!empty($uploadErrors['expiration_years'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['expiration_years']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($uploadErrors['expiration_months'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['expiration_months']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($uploadErrors['expiration_days'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= esc($uploadErrors['expiration_days']) ?></p>
                                <?php endif; ?>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">If you choose expiration, set how long from today the document should expire (at least one field must be greater than 0).</p>
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



