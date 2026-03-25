<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
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

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Return</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($borrowed)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No currently borrowed items</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($borrowed as $borrow): ?>
                                <?php
                                    $daysRemaining = ceil((strtotime($borrow['expected_return_date']) - time()) / (24 * 60 * 60));
                                    $isOverdue = $daysRemaining < 0;
                                ?>
                                <tr class="<?= $isOverdue ? 'bg-red-50' : 'hover:bg-gray-50' ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $borrow['transaction_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['folder_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['borrower_name'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['borrowed_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold <?= $isOverdue ? 'text-red-600' : 'text-green-600' ?>">
                                        <?= abs($daysRemaining) ?> <?= $isOverdue ? 'days overdue' : 'days left' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="/borrows/<?= $borrow['transaction_id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="text-green-600 hover:text-green-900">Return</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="/borrows" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    ← Back to All Requests
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>