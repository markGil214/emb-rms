<?php

// Manually fix existing folder location codes to match their rack/shelf
require_once __DIR__ . '/public/index.php'; // Boot the app

use App\Models\FolderModel;
use App\Libraries\FileCodeGenerator;

$db = \Config\Database::connect();
$folderModel = new FolderModel();

$folders = $db->table('folders')
    ->select('folders.folder_id, locations.rack, locations.shelf')
    ->join('locations', 'locations.location_id = folders.location_id')
    ->get()
    ->getResultArray();

echo "Starting location_code fix for " . count($folders) . " folders...\n";

foreach ($folders as $folder) {
    $newCode = FileCodeGenerator::generateLocationCode($folder['rack'], $folder['shelf']);
    
    $db->table('folders')
        ->where('folder_id', $folder['folder_id'])
        ->update(['location_code' => $newCode]);
        
    echo "Folder ID {$folder['folder_id']}: Updated to {$newCode}\n";
}

echo "Done!\n";
