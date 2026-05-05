<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();

$permissionService = service('permissionService');
echo "Syncing all role permissions...\n";
$summary = $permissionService->syncAllRolePermissions();
print_r($summary);

echo "\nSyncing Super Admin...\n";
$count = $permissionService->syncSuperAdminPermissions(1); // Assuming ID 1 is an admin
echo "Synced $count permissions to Super Admin.\n";
