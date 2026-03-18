<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);  // DISABLED for security - use explicit routes only

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * Named routes follow RESTful conventions (resource.action)
 * Apply filters at route level for security
 * --------------------------------------------------------------------
 */

// Public routes - Guest only (login/register)
$routes->get('/',                   'LoginController::index',      ['as' => 'login', 'filter' => 'guest']);
$routes->post('/authenticate',      'LoginController::authenticate',['as' => 'login.authenticate', 'filter' => 'guest']);
$routes->get('/register',           'RegisterController::index',    ['as' => 'register', 'filter' => 'guest']);
$routes->post('/register/store',    'RegisterController::store',    ['as' => 'register.store', 'filter' => 'guest']);
$routes->get('/logout',             'LoginController::logout',     ['as' => 'logout', 'filter' => 'auth']);
$routes->post('/logout',            'LoginController::logout',     ['as' => 'logout', 'filter' => 'auth']);
// Protected routes - Authenticated users only
$routes->get('/dashboard',        'DashboardController::index',  ['as' => 'dashboard', 'filter' => 'auth']);

// Authenticated routes with specific permissions
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/shelfmap',     'DashboardController::shelfmap', ['as' => 'shelfmap', 'filter' => 'permission:view_shelf_map']);
});

// Folder routes (authenticated users only)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/permits',                'FolderController::index',    ['as' => 'records', 'filter' => 'permission:search_documents']);
    $routes->get('/permits/create',         'FolderController::create',   ['as' => 'records.create', 'filter' => 'permission:create_document_record']);
    $routes->post('/permits',               'FolderController::store',    ['as' => 'records.store', 'filter' => 'permission:create_document_record']);
    $routes->get('/permits/(:num)',         'FolderController::show/$1',  ['as' => 'records.show', 'filter' => 'permission:search_documents']);
    $routes->get('/permits/(:num)/edit',    'FolderController::edit/$1',  ['as' => 'records.edit', 'filter' => 'permission:edit_document_metadata']);
    $routes->put('/permits/(:num)',         'FolderController::update/$1',['as' => 'records.update', 'filter' => 'permission:edit_document_metadata']);
    
    // File upload routes
    $routes->post('/permits/(:num)/upload', 'FileUploadController::upload/$1', ['as' => 'file.upload', 'filter' => 'permission:create_document_record']);
    $routes->get('/files/(:num)/download',  'FileUploadController::download/$1', ['as' => 'file.download', 'filter' => 'permission:search_documents']);
    $routes->delete('/files/(:num)',        'FileUploadController::delete/$1', ['as' => 'file.delete', 'filter' => 'permission:create_document_record']);
});

// Admin routes - Permission Management (Super Admin only)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/admin/permissions',                  'Admin\PermissionController::index',       ['as' => 'admin.permissions', 'filter' => 'permission:manage_users']);
    $routes->post('/admin/permissions/load/(:num)',     'Admin\PermissionController::loadUser/$1', ['as' => 'admin.permissions.load', 'filter' => 'permission:manage_users']);
    $routes->post('/admin/permissions/save/(:num)',     'Admin\PermissionController::savePermissions/$1', ['as' => 'admin.permissions.save', 'filter' => 'permission:manage_users']);
    $routes->get('/admin/permissions/search',           'Admin\PermissionController::searchUsers', ['as' => 'admin.permissions.search', 'filter' => 'permission:manage_users']);
    $routes->get('/admin/permissions/history/(:num)',   'Admin\PermissionController::getHistory/$1', ['as' => 'admin.permissions.history', 'filter' => 'permission:manage_users']);
    $routes->get('/admin/permissions/load-role/(:num)', 'Admin\PermissionController::loadRole/$1', ['as' => 'admin.permissions.load-role', 'filter' => 'permission:manage_users']);
    $routes->post('/admin/permissions/save-role-perms', 'Admin\PermissionController::saveRolePerms', ['as' => 'admin.permissions.save-role-perms', 'filter' => 'permission:manage_users']);
});

$routes->get('/test-helpers', function() {
    echo "=== Helper Test ===<br>";
    
    // Set fake session for testing
    session()->set([
        'user_id' => 1,
        'logged_in' => true,
    ]);
    
    // Test helpers
    echo "user_role(): " . user_role() . "<br>";
    echo "is_super_admin(): " . (is_super_admin() ? 'true' : 'false') . "<br>";
    echo "is_admin(): " . (is_admin() ? 'true' : 'false') . "<br>";
    echo "can('create_document_record'): " . (can('create_document_record') ? 'true' : 'false') . "<br>";
    echo "can('approve_disposal'): " . (can('approve_disposal') ? 'true' : 'false') . "<br>";
});

$routes->get('/test-permission-filter', function() {
    return "If you see this, your permission filter works!";
}, ['filter' => 'permission:approve_disposal']);
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
