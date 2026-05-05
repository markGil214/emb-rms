<?php
// Set up CodeIgniter 4 environment
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__ . '/..');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Config/Constants.php';

$app = \Config\Services::codeigniter();
$app->initialize();

$permissionService = service('permissionService');

echo "Syncing all role permissions...\n";
$summary = $permissionService->syncAllRolePermissions();
print_r($summary);

echo "Sync complete.\n";
