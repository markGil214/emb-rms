<?php
// Drop and recreate test database schema from production without FK constraints initially
$db = mysqli_connect('localhost', 'root', '');
if (!$db) {
    die('Cannot connect to database server');
}

// Disable foreign key checks
mysqli_query($db, 'SET FOREIGN_KEY_CHECKS=0');

// Switch to test database
mysqli_select_db($db, 'emb_rms_test');

// Drop all tables first
$result = mysqli_query($db, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'emb_rms_test'");
while ($row = mysqli_fetch_assoc($result)) {
    mysqli_query($db, 'DROP TABLE IF EXISTS `' . $row['TABLE_NAME'] . '`');
}

echo "Dropped all tables in test database\n";

// Get all table creation statements from production
$prodConnection = mysqli_connect('localhost', 'root', '', 'emb_rms');
mysqli_query($prodConnection, 'SET FOREIGN_KEY_CHECKS=0');

$result = mysqli_query($prodConnection, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'emb_rms' ORDER BY TABLE_NAME");

$tableCount = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $tableName = $row['TABLE_NAME'];
    
    // Get CREATE TABLE statement
    $createResult = mysqli_query($prodConnection, "SHOW CREATE TABLE `$tableName`");
    if ($createResult) {
        $createRow = mysqli_fetch_assoc($createResult);
        $createStatement = $createRow['Create Table'];
        
        // Create table in test DB
        if (mysqli_query($db, $createStatement)) {
            $tableCount++;
            echo "Created table: $tableName\n";
        } else {
            echo "ERROR creating $tableName: " . mysqli_error($db) . "\n";
        }
    }
}

// Re-enable foreign key checks
mysqli_query($db, 'SET FOREIGN_KEY_CHECKS=1');
mysqli_query($prodConnection, 'SET FOREIGN_KEY_CHECKS=1');

echo "\nTotal tables created: $tableCount\n";

mysqli_close($prodConnection);
mysqli_close($db);
?>
