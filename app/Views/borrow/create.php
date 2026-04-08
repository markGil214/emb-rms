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
                    <label for="folder_id" class="block text-sm font-medium text-gray-700">Select Folder</label>
                    <select name="folder_id" id="folder_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">-- Choose Folder --</option>
                        <?php foreach ($folders as $folder): ?>
                            <option value="<?= $folder['folder_id'] ?>"><?= $folder['file_code'] ?> - <?= $folder['company_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
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

<?= $this->endSection() ?>
