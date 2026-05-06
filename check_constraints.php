<?php
$db = mysqli_connect('localhost', 'root', '', 'emb_rms');
if (!$db) {
    die('Connection failed: ' . mysqli_connect_error());
}

echo "=== Foreign Keys ===\n";
$result = mysqli_query($db, "
    SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE REFERENCED_TABLE_NAME IS NOT NULL 
    AND TABLE_SCHEMA = 'emb_rms'
    AND (TABLE_NAME = 'folders' OR TABLE_NAME = 'folder_movements')
");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['CONSTRAINT_NAME']}: {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

echo "\n=== Indexes on folders ===\n";
$result = mysqli_query($db, "SHOW INDEX FROM folders");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['Key_name']}: {$row['Column_name']}\n";
}

echo "\n=== Indexes on folder_movements ===\n";
$result = mysqli_query($db, "SHOW INDEX FROM folder_movements");
while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['Key_name']}: {$row['Column_name']}\n";
}

mysqli_close($db);
?>
