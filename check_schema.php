<?php
$db = mysqli_connect('localhost', 'root', '', 'emb_rms');
if (!$db) {
    die('Connection failed: ' . mysqli_connect_error());
}

echo "=== Columns in folders table ===\n";
$result = mysqli_query($db, 'SHOW COLUMNS FROM folders');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== Columns in folder_movements table ===\n";
$result = mysqli_query($db, 'SHOW COLUMNS FROM folder_movements');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

mysqli_close($db);
?>
