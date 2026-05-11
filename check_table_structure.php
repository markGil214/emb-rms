<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();
if ($db->tableExists('file_disposal_requests')) {
    $fields = $db->getFieldData('file_disposal_requests');
    foreach ($fields as $field) {
        echo $field->name . " (" . $field->type . ")\n";
    }
} else {
    echo "TABLE_NOT_FOUND";
}
