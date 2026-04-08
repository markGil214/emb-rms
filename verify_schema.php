<?php
$c = new mysqli('localhost', 'root', '', 'emb_rms');

echo "=== Borrow Transactions Schema ===\n";
$r = $c->query('DESCRIBE borrow_transactions');
$hasBorrowerEmail = false;
while($row = $r->fetch_assoc()) {
    if ($row['Field'] === 'borrower_email') {
        $hasBorrowerEmail = true;
        echo "✅ {$row['Field']} ({$row['Type']}) - FOUND\n";
    } else {
        echo "   {$row['Field']} ({$row['Type']})\n";
    }
}

if (!$hasBorrowerEmail) {
    echo "\n❌ borrower_email column NOT FOUND!\n";
} else {
    echo "\n✅ borrower_email column is present\n";
}

echo "\n=== Current Test Data ===\n";
$r = $c->query("
    SELECT transaction_id, borrower_name, borrower_email, DATEDIFF(NOW(), expected_return_date) as days_overdue, status
    FROM borrow_transactions
    WHERE purpose LIKE 'TEST%'
    ORDER BY days_overdue
");

echo "Records: " . $r->num_rows . "\n";
while($row = $r->fetch_assoc()) {
    echo "ID: {$row['transaction_id']}, Name: {$row['borrower_name']}, Email: {$row['borrower_email']}, Days: {$row['days_overdue']}, Status: {$row['status']}\n";
}
?>
