<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();
if ($db->tableExists('file_disposal_requests')) {
    echo "TABLE_EXISTS";
} else {
    echo "TABLE_NOT_FOUND";
}
