<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/Database.php';

$db = \Config\Database::connect();

// Get recent relocations with location details
$query = $db->query("
    SELECT r.relocation_id, r.folder_id, r.to_location_id, r.status, 
           l.cabinet, l.rack, l.shelf
    FROM relocation_requests r
    LEFT JOIN locations l ON r.to_location_id = l.location_id
    ORDER BY r.relocation_id DESC
    LIMIT 10
");

$results = $query->getResultArray();

echo "\n=== Recent Relocation Requests ===\n";
foreach ($results as $row) {
    echo "ID: {$row['relocation_id']}, Folder: {$row['folder_id']}, ";
    echo "To Location ID: {$row['to_location_id']}, ";
    echo "Cabinet: {$row['cabinet']}, Rack: {$row['rack']}, Shelf: {$row['shelf']}, ";
    echo "Status: {$row['status']}\n";
}
?>
