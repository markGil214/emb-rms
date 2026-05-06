<?php
/**
 * Dashboard widget showing overdue borrowed items
 * Displays count and recent overdue items
 */
?>
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">
            <span class="text-red-600">⏰</span> Overdue Items
        </h2>
        <?php if ($overdue_stats['total_overdue'] > 0): ?>
            <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                <?php echo $overdue_stats['total_overdue']; ?> items
            </span>
        <?php else: ?>
            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                ✓ All clear
            </span>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <?php if ($overdue_stats['total_overdue'] > 0): ?>
        <div class="space-y-3">
            <!-- List of recent overdue items -->
            <?php foreach ($overdue_stats['items'] as $item): ?>
                <div class="flex items-center justify-between p-3 bg-red-50 rounded border-l-4 border-red-500">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800">
                            <?php echo htmlspecialchars($item['file_code'] ?? 'Unknown'); ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <?php echo htmlspecialchars($item['borrower_name']); ?> 
                            <span class="text-gray-500">•</span>
                            Due: <?php echo date('M d, Y', strtotime($item['expected_return_date'])); ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-red-600">
                            <?php echo $item['days_overdue']; ?> days
                        </p>
                        <p class="text-xs text-gray-500">overdue</p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- View all link -->
            <div class="mt-4 pt-3 border-t border-gray-200">
                <a href="<?php echo base_url('reports/overdue'); ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                    → View all overdue items
                </a>
                <a href="<?php echo base_url('reports/overdue/export'); ?>" class="inline-block ml-4 text-green-600 hover:text-green-800 font-medium text-sm">
                    📥 Export CSV
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- No overdue items -->
        <div class="text-center py-8">
            <p class="text-lg text-green-600 font-medium">✓ No overdue items</p>
            <p class="text-gray-600 text-sm mt-1">All borrowed items have been returned on time.</p>
        </div>
    <?php endif; ?>
</div>
