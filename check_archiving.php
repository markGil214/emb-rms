<?php
$mysqli = new mysqli('localhost', 'root', '', 'emb_rms');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if tables exist
$tables = ['archive_records', 'disposal_records'];
foreach ($tables as $table) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='emb_rms' AND TABLE_NAME='$table'");
    $row = $result->fetch_assoc();
    if ($row['cnt'] > 0) {
        // Get row count
        $countResult = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
        $countRow = $countResult->fetch_assoc();
        echo "✓ Table '$table' exists with {$countRow['cnt']} record(s)\n";
    } else {
        echo "✗ Table '$table' does NOT exist\n";
    }
}

// Check if column 'is_archive_location' exists in locations
$result = $mysqli->query("SHOW COLUMNS FROM locations LIKE 'is_archive_location'");
if ($result->num_rows > 0) {
    echo "✓ Column 'is_archive_location' exists in locations table\n";
} else {
    echo "✗ Column 'is_archive_location' NOT found in locations table\n";
}

$mysqli->close();
?>
