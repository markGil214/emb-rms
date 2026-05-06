<?php
// Comprehensive Archiving Status Check
ini_set('display_errors', 0);
error_reporting(E_ALL);

$status = [
    'database' => [],
    'models' => [],
    'controllers' => [],
    'permissions' => [],
    'routes' => [],
    'seedData' => [],
    'overall' => 'OPERATIONAL'
];

$mysqli = new mysqli('localhost', 'root', '', 'emb_rms');
if ($mysqli->connect_error) {
    $status['overall'] = 'ERROR';
    $status['database']['error'] = 'Connection failed: ' . $mysqli->connect_error;
    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// 1. Check Database Tables
$tables = ['archive_records', 'disposal_records'];
foreach ($tables as $table) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='emb_rms' AND TABLE_NAME='$table'");
    $row = $result->fetch_assoc();
    if ($row['cnt'] > 0) {
        $countResult = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
        $countRow = $countResult->fetch_assoc();
        $status['database'][$table] = [
            'exists' => true,
            'record_count' => $countRow['cnt']
        ];
    } else {
        $status['database'][$table] = ['exists' => false];
        $status['overall'] = 'ERROR';
    }
}

// 2. Check locations table has archive column
$result = $mysqli->query("SHOW COLUMNS FROM locations LIKE 'is_archive_location'");
if ($result->num_rows > 0) {
    $status['database']['locations.is_archive_location'] = ['exists' => true];
} else {
    $status['database']['locations.is_archive_location'] = ['exists' => false];
}

// 3. Check for archive location entries
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM locations WHERE is_archive_location = 1");
$row = $result->fetch_assoc();
$status['database']['archive_locations_count'] = $row['cnt'];

// 4. Check seeded data samples
$result = $mysqli->query("SELECT * FROM archive_records LIMIT 3");
$status['seedData']['archive_records_samples'] = [];
while ($row = $result->fetch_assoc()) {
    $status['seedData']['archive_records_samples'][] = [
        'archive_id' => $row['archive_id'],
        'folder_id' => $row['folder_id'],
        'retention_status' => $row['retention_status']
    ];
}

$result = $mysqli->query("SELECT * FROM disposal_records LIMIT 3");
$status['seedData']['disposal_records_samples'] = [];
while ($row = $result->fetch_assoc()) {
    $status['seedData']['disposal_records_samples'][] = [
        'disposal_id' => $row['disposal_id'],
        'archive_id' => $row['archive_id'],
        'disposal_method' => $row['disposal_method'],
        'disposal_date' => $row['disposal_date']
    ];
}

// 5. Check if folders are properly linked
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM folders WHERE status = 'Archived'");
$row = $result->fetch_assoc();
$status['database']['archived_folders_count'] = $row['cnt'];

$mysqli->close();

// 6. Check file existence
$controllers = ['ArchiveDisposalController', 'ArchiveRecordModel', 'DisposalRecordModel'];
$basePath = __DIR__;
foreach ($controllers as $file) {
    $paths = [
        'controller' => "$basePath/app/Controllers/$file.php",
        'model' => "$basePath/app/Models/$file.php",
    ];
    foreach ($paths as $type => $path) {
        if (file_exists($path)) {
            $status['controllers']["$file ($type)"] = ['exists' => true];
        }
    }
}

// 7. Summary
$status['summary'] = [
    'tables_exist' => count($status['database']) > 0,
    'has_archive_data' => ($status['database']['archive_records']['record_count'] ?? 0) > 0,
    'has_disposal_data' => ($status['database']['disposal_records']['record_count'] ?? 0) > 0,
    'archive_locations_configured' => ($status['database']['archive_locations_count'] ?? 0) > 0,
    'folders_archived' => ($status['database']['archived_folders_count'] ?? 0) > 0,
];

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
