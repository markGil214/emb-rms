<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class=" max-w-8xl mx-auto ">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Search borrow requests..." 
                               class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <div class="flex items-center">
                        <select id="limitSelector" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                </div>
                
                <a href="/borrows/create" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    + Request Borrow
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
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($borrows)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No borrow records found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($borrows as $index => $borrow): ?>
                                <?php if ($borrow['status'] !== 'Returned'): ?>
                                <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-100' ?> hover:bg-gray-200 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $borrow['transaction_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if (isset($folderMap[$borrow['folder_id']])): ?>
                                            <strong><?= esc($folderMap[$borrow['folder_id']]['file_code']) ?></strong> - <?= esc($folderMap[$borrow['folder_id']]['company_name']) ?>
                                        <?php else: ?>
                                            <?= esc($borrow['folder_id']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $borrow['borrower_name'] ?? 'N/A' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['borrowed_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $borrow['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($borrow['status'] === 'Borrowed' || $borrow['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= $borrow['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="/borrows/<?= $borrow['transaction_id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <?php if ($borrow['status'] === 'Pending' && can('approve_borrow_requests')): ?>
                                                <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/approve" class="inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                                </form>
                                            <?php elseif ($borrow['status'] === 'Borrowed' || $borrow['status'] === 'Overdue'): ?>
                                                <form method="POST" action="/borrows/<?= $borrow['transaction_id'] ?>/return" class="inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Return</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
          
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const limitSelector = document.getElementById('limitSelector');
    const tableRows = document.querySelectorAll('tbody tr');
    
    // Function to apply search and limit
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const limit = limitSelector.value;
        let visibleCount = 0;
        let matchedRows = [];
        
        // First, collect all data rows and apply search filter
        tableRows.forEach(row => {
            // Skip the "No records found" row
            if (row.querySelector('td[colspan="7"]')) {
                return;
            }
            
            const rowText = row.textContent.toLowerCase();
            const matchesSearch = rowText.includes(searchTerm);
            
            if (matchesSearch) {
                matchedRows.push(row);
                row.style.display = 'none'; // Hide all initially, then show based on limit
            } else {
                row.style.display = 'none';
            }
        });
        
        // Apply limit to matching rows
        const limitNumber = limit === 'all' ? matchedRows.length : parseInt(limit);
        const rowsToShow = matchedRows.slice(0, limitNumber);
        
        rowsToShow.forEach(row => {
            row.style.display = '';
            visibleCount++;
        });
        
        // Show/hide "No records found" message
        const noRecordsRow = document.querySelector('td[colspan="7"]');
        
        if (visibleCount === 0 && noRecordsRow) {
            noRecordsRow.parentElement.style.display = '';
            noRecordsRow.textContent = 'No matching borrow records found';
        } else if (noRecordsRow) {
            noRecordsRow.parentElement.style.display = 'none';
        }
    }
    
    // Apply initial filters
    applyFilters();
    
    // Event listeners
    searchInput.addEventListener('input', applyFilters);
    limitSelector.addEventListener('change', applyFilters);
});
</script>

<?= $this->endSection() ?>
