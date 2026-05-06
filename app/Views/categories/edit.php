<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Popup Modal -->
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 sm:p-6 lg:p-8 z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-6 sm:p-8 lg:p-12 max-w-2xl w-full mx-auto">
        <!-- Alert Header -->
        <div class="text-center mb-6">
            <div class="mx-auto w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Edit Category</h2>
            <p class="text-gray-600 text-sm">Modify existing document category</p>
        </div>
    <?php $session = session(); $errors = $session->getFlashdata('errors') ?? []; ?>

    <!-- Flash Messages -->
    <?php if ($session->getFlashdata('error')): ?>
        <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-6">
            <?= esc($session->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Validation Errors -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <h4 class="font-semibold mb-2">Please fix the following errors:</h4>
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $message): ?>
                    <li><?= esc($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= route_to('categories.update', $category['category_id']) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-6">
            <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">
                Category Name
            </label>
            <input type="text" 
                   id="category_name" 
                   name="category_name" 
                   value="<?= esc(old('category_name', $category['category_name'])) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Enter category name"
                   required>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between sm:space-x-3">
            <a href="<?= route_to('categories.index') ?>" 
               class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 text-center w-full sm:w-auto">
                Cancel
            </a>
            <button type="submit" 
                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 w-full sm:w-auto">
                Update Category
            </button>
        </div>
    </form>
    </div>
</div>

<?= $this->endSection() ?>
