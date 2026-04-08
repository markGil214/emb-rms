<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-8xl mx-auto">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900"><?= $title ?></h1>
        </div>
        
        <div class="p-6">
            <?php if (session()->has('errors')): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <strong>Validation Errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="/borrows" class="space-y-6">
                <?= csrf_field() ?>

                <div>
                    <label for="borrower_name" class="block text-sm font-medium text-gray-700">Borrower Name</label>
                    <input type="text" name="borrower_name" id="borrower_name" 
                           placeholder="Enter borrower's name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="borrower_email" class="block text-sm font-medium text-gray-700">Borrower Email</label>
                    <input type="email" name="borrower_email" id="borrower_email" 
                           placeholder="Enter borrower's email address" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="folderSearch" class="block text-sm font-medium text-gray-700">Select Folder</label>
                    <div class="mt-1 relative">
                        <input type="text" id="folderSearch" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="Search by file code or company name..." autocomplete="off">
                        <div id="searchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-60 overflow-y-auto hidden shadow-lg"></div>
                    </div>
                    
                    <div id="selectedFolder" class="hidden mt-3 bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <span class="text-xs font-medium text-gray-500">Selected Folder:</span>
                                <p id="selectedFolderInfo" class="text-sm font-medium text-gray-900"></p>
                            </div>
                            <button type="button" onclick="clearFolderSelection()" 
                                    class="ml-3 text-sm text-red-600 hover:text-red-800 font-medium">
                                Clear
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="folder_id" id="folder_id">
                </div>

                <div>
                    <label for="expected_return_date" class="block text-sm font-medium text-gray-700">Expected Return Date</label>
                    <input type="date" name="expected_return_date" id="expected_return_date" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" 
                            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Request Borrow
                    </button>
                    <a href="/borrows" 
                       class="inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const folders = <?= json_encode($folders ?? []) ?>;

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

        results.innerHTML = filtered.map(f => 
            `<div onclick="selectFolder(${f.folder_id}, '${f.file_code}', '${f.company_name}')" 
                  class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0">
                <div class="font-medium text-gray-900">${f.file_code}</div>
                <div class="text-sm text-gray-600">${f.company_name}</div>
            </div>`
        ).join('');
        
        results.classList.remove('hidden');
    });

    function selectFolder(folderId, fileCode, companyName) {
        document.getElementById('folder_id').value = folderId;
        document.getElementById('selectedFolderInfo').textContent = `${fileCode} - ${companyName}`;
        document.getElementById('selectedFolder').classList.remove('hidden');
        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('folderSearch').value = '';
    }

    function clearFolderSelection() {
        document.getElementById('folder_id').value = '';
        document.getElementById('selectedFolder').classList.add('hidden');
        document.getElementById('folderSearch').value = '';
        document.getElementById('folderSearch').focus();
    }

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#folderSearch') && !e.target.closest('#searchResults')) {
            document.getElementById('searchResults').classList.add('hidden');
        }
    });
</script>

<?= $this->endSection() ?>
