<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-8xl mx-auto">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900"><?= $title ?></h1>
        </div>
        
        <div class="p-6">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <strong>Success:</strong> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <strong>Error:</strong> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Alert</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>These items are overdue and need to be returned immediately.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($overdue)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No overdue items</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($overdue as $borrow): ?>
                                <tr class="bg-red-50 hover:bg-red-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $borrow['transaction_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['folder_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['borrowed_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                        <?= (int) ((time() - strtotime($borrow['expected_return_date'])) / 86400) ?> days
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="/borrows/<?= $borrow['transaction_id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Mark Return</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
