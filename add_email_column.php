<?php
$c = new mysqli('localhost', 'root', '', 'emb_rms');

echo "=== Adding borrower_email column manually ===\n";

// Check if column exists first
$r = $c->query("SHOW COLUMNS FROM borrow_transactions LIKE 'borrower_email'");
if ($r->num_rows > 0) {
    echo "Column already exists\n";
} else {
    echo "Column does not exist. Creating...\n";
    
    $sql = "ALTER TABLE borrow_transactions ADD COLUMN borrower_email VARCHAR(100) NULL AFTER borrower_name";
    if ($c->query($sql)) {
        echo "✅ borrower_email column added successfully\n";
    } else {
        echo "❌ Error: " . $c->error . "\n";
        exit(1);
    }
}

// Add index
$r = $c->query("SHOW INDEX FROM borrow_transactions WHERE Column_name = 'borrower_email'");
if ($r->num_rows === 0) {
    echo "Adding index...\n";
    $c->query("CREATE INDEX idx_borrower_email ON borrow_transactions(borrower_email)");
}

// Verify
echo "\n=== Verifying Schema ===\n";
$r = $c->query('SHOW COLUMNS FROM borrow_transactions');
while($row = $r->fetch_assoc()) {
    if ($row['Field'] === 'borrower_email') {
        echo "✅ borrower_email ({$row['Type']}) - CONFIRMED\n";
        break;
    }
}

echo "\n✅ Schema updated successfully\n";
?>
