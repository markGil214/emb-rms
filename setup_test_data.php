<?php
// Quick test database setup script
$db = mysqli_connect('localhost', 'root', '', 'emb_rms');
if (!$db) {
    die('Cannot connect to production database');
}

// Check if locations table exists and has sample data
$result = mysqli_query($db, 'SELECT COUNT(*) as cnt FROM locations');
$row = mysqli_fetch_assoc($result);
$locationCount = $row['cnt'];

if ($locationCount > 0) {
    // Copy locations to test database
    mysqli_query($db, '
        INSERT INTO emb_rms_test.locations 
        SELECT * FROM emb_rms.locations
    ');
    echo "Copied " . mysqli_affected_rows($db) . " locations\n";
}

// Check users table
$result = mysqli_query($db, 'SELECT COUNT(*) as cnt FROM users');
$row = mysqli_fetch_assoc($result);
$userCount = $row['cnt'];

if ($userCount > 0) {
    // Copy users to test database
    mysqli_query($db, '
        INSERT IGNORE INTO emb_rms_test.users 
        SELECT * FROM emb_rms.users LIMIT 2
    ');
    echo "Copied users\n";
}

mysqli_close($db);
echo "Test data setup complete\n";
?>
