<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/Database.php';

$db = \Config\Database::connect();

$query = $db->query("
    SELECT location_id, cabinet, rack, shelf
    FROM locations
    ORDER BY cabinet, rack
");

$results = $query->getResultArray();

echo "\n=== All Locations ===\n";
foreach ($results as $row) {
    echo "ID: {$row['location_id']}, Cabinet: {$row['cabinet']}, Rack: {$row['rack']}, Shelf: {$row['shelf']}\n";
}
?>
