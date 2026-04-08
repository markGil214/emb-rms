<?php
$conn = new mysqli('localhost', 'root', '', 'emb_rms');

echo "=== Users Table ===\n";
$result = $conn->query('DESCRIBE users');
$userCols = [];
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
    $userCols[] = $row['Field'];
}

echo "\n=== Borrow Transactions Table ===\n";
$result = $conn->query('DESCRIBE borrow_transactions');
$borrowCols = [];
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
    $borrowCols[] = $row['Field'];
}

echo "\n=== MISSING COLUMNS TO ADD ===\n";
$neededNotificationCols = ['notification_status', 'last_notification_sent_at', 'escalated_to_manager'];
$missingNotif = array_diff($neededNotificationCols, $borrowCols);
echo "Borrow Transaction columns to add: " . implode(', ', $missingNotif) . "\n";
?>
