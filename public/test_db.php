<?php
require_once __DIR__ . '/../app/Config/Constants.php';
require_once __DIR__ . '/../system/bootstrap.php';

$db = \Config\Database::connect();
$userId = 1; // Assuming user 1 is the one being tested

echo "<h1>User 1 Permissions</h1>";
$custom = $db->table('user_permissions')->where('user_id', $userId)->get()->getResultArray();
echo "<h2>Custom Permissions</h2>";
echo "<pre>";
print_r($custom);
echo "</pre>";

$roles = $db->table('user_roles')->where('user_id', $userId)->get()->getResultArray();
echo "<h2>User Roles</h2>";
echo "<pre>";
print_r($roles);
echo "</pre>";

if (!empty($roles)) {
    $roleId = $roles[0]['role_id'];
    $rolePerms = $db->table('role_permissions')->where('role_id', $roleId)->get()->getResultArray();
    echo "<h2>Role Permissions (Role ID: $roleId)</h2>";
    echo "<pre>";
    print_r($rolePerms);
    echo "</pre>";
}
