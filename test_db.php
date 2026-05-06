<?php
// Quick database test
require 'vendor/autoload.php';

$db = \Config\Database::connect();

echo "=== Database Check ===\n";

$tables = $db->listTables();
echo "Tables found: " . count($tables) . "\n";

if (in_array('locations', $tables)) {
    $locCount = $db->table('locations')->countAllResults();
    echo "✓ Locations: " . $locCount . " records\n";
    
    if ($locCount > 0) {
        $sample = $db->table('locations')->select('*')->get()->getFirstRow('array');
        echo "  Sample: ID=" . $sample['location_id'] . ", Cabinet=" . $sample['cabinet'] . ", Shelf=" . $sample['shelf'] . "\n";
    }
} else {
    echo "✗ Locations table not found\n";
}

if (in_array('folders', $tables)) {
    $folderCount = $db->table('folders')->countAllResults();
    echo "✓ Folders: " . $folderCount . " records\n";
} else {
    echo "✗ Folders table not found\n";
}
