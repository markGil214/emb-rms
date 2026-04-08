<?php
$c = new mysqli('localhost', 'root', '', 'emb_rms');

echo "=== Updating Test Records with Borrower Email ===\n";

// Get borrower email
$r = $c->query("SELECT borrower_id, email FROM borrowers LIMIT 1");
$borrower = $r->fetch_assoc();
$borrower_id = $borrower['borrower_id'];
$borrower_email = $borrower['email'];

echo "Using borrower_id: $borrower_id, email: $borrower_email\n\n";

// Update test records with borrower_email
$result = $c->query("UPDATE borrow_transactions SET borrower_email = '$borrower_email', borrower_id = $borrower_id WHERE purpose LIKE 'TEST%'");
echo "Updated " . $c->affected_rows . " records\n\n";

// Verify
echo "=== Updated Test Data ===\n";
$r = $c->query("
    SELECT transaction_id, status, DATEDIFF(NOW(), expected_return_date) as days_overdue,
           borrower_email, notification_status, purpose
    FROM borrow_transactions
    WHERE purpose LIKE 'TEST%'
    ORDER BY days_overdue ASC
");

while($row = $r->fetch_assoc()) {
    echo "ID: {$row['transaction_id']}, Status: {$row['status']}, Days: {$row['days_overdue']}, Email: {$row['borrower_email']}, Notif: {$row['notification_status']}\n";
}

echo "\n✅ Test data ready with borrower emails\n";
?>
