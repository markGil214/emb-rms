<?php
$db = mysqli_connect('localhost', 'root', '');
if (!$db) {
    die('Connection failed: ' . mysqli_connect_error());
}

if (!mysqli_query($db, 'CREATE DATABASE IF NOT EXISTS emb_rms_test')) {
    echo 'Error creating database: ' . mysqli_error($db);
} else {
    echo 'Test database created/verified successfully';
}

mysqli_close($db);
?>
