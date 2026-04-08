<?php
/**
 * Overdue items report view
 * Displays searchable/sortable table of all overdue items
 */
?>

<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Overdue Items Report</h1>
        <p class="text-gray-600">Items that have not been returned by their expected return date.</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Filters</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                    Overdue Since
                </label>
                <input 
                    type="date" 
                    id="date_from" 
                    name="date_from" 
                    value="<?php echo htmlspecialchars($filter['date_from'] ?? ''); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                >
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                    Overdue Until
                </label>
                <input 
                    type="date" 
                    id="date_to" 
                    name="date_to" 
                    value="<?php echo htmlspecialchars($filter['date_to'] ?? ''); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                >
            </div>

            <!-- Borrower Name -->
            <div>
                <label for="borrower_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Borrower Name
                </label>
                <input 
                    type="text" 
                    id="borrower_name" 
                    name="borrower_name" 
                    placeholder="Search..."
                    value="<?php echo htmlspecialchars($filter['borrower_name'] ?? ''); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                >
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button 
                    type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition"
                >
                    Search
                </button>
                <a 
                    href="<?php echo base_url('reports/overdue'); ?>" 
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md text-center transition"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Button -->
    <div class="mb-6">
        <form method="GET" action="<?php echo base_url('reports/overdue/export'); ?>" class="inline">
            <?php foreach ($filter as $key => $value): ?>
                <?php if ($value): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <button 
                type="submit" 
                class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-md transition"
            >
                📥 Export as CSV
            </button>
        </form>
        <span class="text-gray-600 text-sm ml-4">
            Found: <strong><?php echo count($overdue); ?></strong> overdue item(s)
        </span>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($overdue) > 0): ?>
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-300">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Document</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Borrower</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Expected Return</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Last Notification</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($overdue as $item): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-800">
                                        <?php echo htmlspecialchars($item['file_code']); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($item['company_name']); ?>
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-800">
                                <?php echo htmlspecialchars($item['borrower_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-800">
                                <?php echo date('M d, Y', strtotime($item['expected_return_date'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    <?php echo $item['days_overdue']; ?> days
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block text-xs font-semibold">
                                    <?php echo htmlspecialchars($item['notification_status'] ?? 'None'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php if ($item['last_notification_sent_at']): ?>
                                    <?php echo date('M d, Y H:i', strtotime($item['last_notification_sent_at'])); ?>
                                    <?php if ($item['escalated_to_manager']): ?>
                                        <br><span class="text-orange-600 font-medium">Escalated ✓</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Never
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <a 
                                    href="<?php echo base_url('borrow/' . $item['transaction_id']); ?>" 
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center p-12">
                <p class="text-gray-600 text-lg">No overdue items found.</p>
                <p class="text-gray-500 text-sm mt-1">All borrowed items have been returned on time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
