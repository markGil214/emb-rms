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

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Return</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($pending)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No pending requests</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending as $borrow): ?>
                                <tr class="bg-gray-50 hover:bg-yellow-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $borrow['transaction_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['folder_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <?= view('components/button', [
                                                'label' => 'View',
                                                'url' => "/borrows/{$borrow['transaction_id']}",
                                                'style' => 'info'
                                            ]) ?>
                                            <?= view('components/button', [
                                                'label' => 'Approve',
                                                'type' => 'submit',
                                                'style' => 'primary',
                                                'action' => "/borrows/{$borrow['transaction_id']}/approve"
                                            ]) ?>
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
