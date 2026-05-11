<?php

$db = \Config\Database::connect();
$query = $db->query("SELECT * FROM migrations");
$results = $query->getResultArray();

if (empty($results)) {
    echo "Migrations table is empty.\n";
} else {
    echo "Found " . count($results) . " records in migrations table:\n";
    foreach ($results as $row) {
        echo " - " . $row['version'] . " (Batch: " . $row['batch'] . ")\n";
    }
}
