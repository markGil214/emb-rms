<?php

// Manually sync role permissions to ensure the database matches the code
require_once 'app/Config/Permissions.php';
require_once 'app/Libraries/PermissionService.php';

use App\Libraries\PermissionService;

$service = new PermissionService();
$summary = $service->syncAllRolePermissions();

echo "Sync Summary:\n";
print_r($summary);

// Also sync Super Admin just in case
$synced = $service->syncSuperAdminPermissions();
echo "Super Admin Synced: $synced\n";
