<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();
$result = $db->query('SHOW TABLES');

echo "Tables in database:\n";
foreach($result->getResultArray() as $row) {
    echo "- " . implode('', $row) . "\n";
}
