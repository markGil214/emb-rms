<?php
require 'vendor/autoload.php';
$db = \Config\Database::connect();
$res = $db->query('SELECT location_id, cabinet, rack, shelf FROM locations ORDER BY location_id')->getResultArray();
foreach ($res as $row) {
    echo "ID: {$row['location_id']}, Cabinet: {$row['cabinet']}, Rack: {$row['rack']}, Shelf: {$row['shelf']}\n";
}
?>
