<?php

namespace Tests;

use CodeIgniter\Test\FeatureTestCase;

class TestPhase4 extends FeatureTestCase
{
    protected $appConfig = \Config\App::class;

    public function testDatabaseHasRoles()
    {
        $db = \Config\Database::connect();
        $roles = $db->table('roles')->countAllResults();
        $this->assertGreaterThan(0, $roles, 'No roles found in database');
        echo "\n✓ Database has $roles roles\n";
    }

    public function testDatabaseHasUsers()
    {
        $db = \Config\Database::connect();
        $users = $db->table('users')->countAllResults();
        $this->assertGreaterThan(0,  $users, 'No users found in database');
        echo "\n✓ Database has $users users\n";
    }

    public function testPermissionsConfigExists()
    {
        $perms = \App\Config\Permissions::all();
        $this->assertIsArray($perms);
        $this->assertArrayHasKey('DOCUMENT_MANAGEMENT', $perms);
        echo "\n✓ Permissions config loaded with " . count($perms) . " groups\n";
    }

    public function testPermissionServiceExists()
    {
        $permissionService = service('permissionService');
        $this->assertNotNull($permissionService);
        echo "\n✓ PermissionService is available\n";
    }

    public function testRolePermissionTableHasData()
    {
        $db = \Config\Database::connect();
        $perms = $db->table('role_permissions')->countAllResults();
        $this->assertGreaterThan(0, $perms, 'No role permissions found');
        echo "\n✓ role_permissions table has $perms entries\n";
    }

    public function testControllerMethodsExist()
    {
        $controller = new \App\Controllers\Admin\PermissionController();
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'loadRole'));
        $this->assertTrue(method_exists($controller, 'saveRolePerms'));
        echo "\n✓ All controller methods exist\n";
    }
}

