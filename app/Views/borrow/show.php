<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-8xl mx-auto">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
               
                <a href="/borrows" 
                   class="inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    ← Back to List
                </a>
            </div>
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

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Borrow Details</h2>
                
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 w-1/3">Transaction ID</th>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $borrow['transaction_id'] ?></td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Folder</th>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <strong><?= $folder['file_code'] ?></strong> - <?= $folder['company_name'] ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Borrower</th>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Borrowed Date</th>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= date('M d, Y g:i A', strtotime($borrow['borrowed_at'] ?? now())) ?></td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Expected Return</th>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actual Return</th>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php if ($borrow['actual_return_date']): ?>
                                        <?= date('M d, Y g:i A', strtotime($borrow['actual_return_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $borrow['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($borrow['status'] === 'Borrowed' ? 'bg-blue-100 text-blue-800' : 
                                           ($borrow['status'] === 'Overdue' ? 'bg-red-100 text-red-800' :
                                           ($borrow['status'] === 'Declined' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'))) ?>">
                                        <?= $borrow['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if (isset($borrow['notes']) && $borrow['notes']): ?>
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Notes / Purpose</th>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= nl2br(htmlspecialchars($borrow['notes'])) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Borrower Signature:</p>
                        <div class="border-b-2 border-gray-300 h-10"></div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Administrator:</p>
                        <div class="border-b-2 border-gray-300 h-10"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-4">
                <?php if ($borrow['status'] === 'Pending' && can('approve_borrow_requests')): ?>
                    <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/approve" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Approve Request
                        </button>
                    </form>
                    <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/decline" class="inline" data-confirm-message="Decline this borrow request?">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Decline Request
                        </button>
                    </form>
                <?php elseif (($borrow['status'] === 'Borrowed' || $borrow['status'] === 'Overdue') && can('approve_borrow_requests')): ?>
                    <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Process Return
                        </button>
                    </form>
                <?php elseif ($borrow['status'] === 'Returned'): ?>
                    <div class="inline-flex items-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-50">
                        ✓ Item Returned
                    </div>
                <?php elseif ($borrow['status'] === 'Declined'): ?>
                    <div class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50">
                        ✕ Request Declined
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
