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



// Borrow Request Routes

$routes->group('', ['filter' => 'auth'], function($routes) {

    $routes->get('/borrows',                  'BorrowRequestController::index',     ['as' => 'borrows.index', 'filter' => 'permission:view_own_borrow,view_all_borrow']);

    $routes->get('/borrows/create',           'BorrowRequestController::create',    ['as' => 'borrows.create', 'filter' => 'permission:request_borrow']);

    $routes->post('/borrows',                 'BorrowRequestController::store',     ['as' => 'borrows.store', 'filter' => 'permission:request_borrow']);

    $routes->get('/borrows/(:num)',           'BorrowRequestController::show/$1',   ['as' => 'borrows.show', 'filter' => 'permission:view_own_borrow,view_all_borrow']);

    $routes->post('/borrows/(:num)/approve',  'BorrowRequestController::approve/$1',['as' => 'borrows.approve', 'filter' => 'permission:approve_borrow_requests']);

    $routes->post('/borrows/(:num)/return',   'BorrowRequestController::return/$1', ['as' => 'borrows.return', 'filter' => 'permission:process_return']);

    $routes->post('/borrows/(:num)/notify',   'BorrowRequestController::notify/$1', ['as' => 'borrows.notify', 'filter' => 'permission:process_return']);

    $routes->get('/borrows/pending',          'BorrowRequestController::pending',   ['as' => 'borrows.pending', 'filter' => 'permission:approve_borrow_requests']);

    $routes->get('/borrows/borrowed',         'BorrowRequestController::borrowed',  ['as' => 'borrows.borrowed', 'filter' => 'permission:view_own_borrow,view_all_borrow']);

    $routes->get('/borrows/overdue',          'BorrowRequestController::overdue',   ['as' => 'borrows.overdue', 'filter' => 'permission:view_pending_returns']);

});

// Report Routes - Overdue items, CSV export, etc.

$routes->group('reports', ['filter' => 'auth'], function($routes) {

    $routes->get('/overdue',                  'ReportController::viewOverdue',      ['as' => 'reports.overdue', 'filter' => 'permission:view_all_borrow']);

    $routes->get('/overdue/export',           'ReportController::exportOverdueCSV', ['as' => 'reports.overdue.export', 'filter' => 'permission:view_all_borrow']);

});




// Relocation Request Routes

$routes->group('', ['filter' => 'auth'], function($routes) {

    $routes->get('/relocations',                       'RelocationController::index',           ['as' => 'relocations.index', 'filter' => 'permission:initiate_relocation']);

    $routes->get('/relocations/create',                'RelocationController::create',          ['as' => 'relocations.create', 'filter' => 'permission:initiate_relocation']);

    $routes->post('/relocations',                      'RelocationController::store',           ['as' => 'relocations.store', 'filter' => 'permission:initiate_relocation']);

    $routes->get('/relocations/(:num)',                'RelocationController::show/$1',         ['as' => 'relocations.show', 'filter' => 'permission:initiate_relocation']);

    $routes->post('/relocations/(:num)/approve',       'RelocationController::approve/$1',      ['as' => 'relocations.approve', 'filter' => 'permission:approve_relocation']);

    $routes->post('/relocations/(:num)/decline',       'RelocationController::decline/$1',      ['as' => 'relocations.decline', 'filter' => 'permission:approve_relocation']);

    $routes->get('/relocations/(:num)/edit',           'RelocationController::edit/$1',         ['as' => 'relocations.edit', 'filter' => 'permission:initiate_relocation']);

    $routes->post('/relocations/(:num)',               'RelocationController::update/$1',       ['as' => 'relocations.update', 'filter' => 'permission:initiate_relocation']);

    $routes->post('/relocations/(:num)/start',         'RelocationController::startRelocation/$1', ['as' => 'relocations.start', 'filter' => 'permission:initiate_relocation']);

    $routes->post('/relocations/(:num)/complete',      'RelocationController::complete/$1',     ['as' => 'relocations.complete', 'filter' => 'permission:complete_relocation']);

    

    // Debug console for relocation testing (development only)

    $routes->get('/relocation-test',                   'RelocationController::testConsole',     ['as' => 'relocations.test']);

});



// Archive & Disposal Routes

$routes->group('', ['filter' => 'auth'], function($routes) {

    $routes->get('/archive-disposal',                   'ArchiveDisposalController::index',              ['as' => 'archive.index']);

    

    // Archive operations

    $routes->get('/archive-disposal/create-archive',   'ArchiveDisposalController::createArchive',      ['as' => 'archive.create', 'filter' => 'permission:request_archive']);

    $routes->post('/archive-disposal/archive',          'ArchiveDisposalController::storeArchive',       ['as' => 'archive.store', 'filter' => 'permission:request_archive']);

    $routes->get('/archive-disposal/archive/(:num)',    'ArchiveDisposalController::showArchive/$1',    ['as' => 'archive.show']);

    $routes->get('/archive-disposal/search',            'ArchiveDisposalController::searchArchive',      ['as' => 'archive.search']);

    

    // Disposal operations

    $routes->get('/archive-disposal/create-disposal',   'ArchiveDisposalController::createDisposal',    ['as' => 'disposal.create', 'filter' => 'permission:request_disposal']);

    $routes->post('/archive-disposal/disposal',         'ArchiveDisposalController::storeDisposal',     ['as' => 'disposal.store', 'filter' => 'permission:request_disposal']);

    $routes->get('/archive-disposal/disposal/(:num)',   'ArchiveDisposalController::showDisposal/$1',   ['as' => 'disposal.show']);

    $routes->post('/archive-disposal/disposal/(:num)/approve', 'ArchiveDisposalController::approveDisposal/$1', ['as' => 'disposal.approve', 'filter' => 'permission:approve_disposal']);

    $routes->post('/archive-disposal/disposal/(:num)/complete', 'ArchiveDisposalController::completeDisposal/$1', ['as' => 'disposal.complete', 'filter' => 'permission:approve_disposal']);

    $routes->get('/archive-disposal/disposal/pending',  'ArchiveDisposalController::pendingDisposal',   ['as' => 'disposal.pending', 'filter' => 'permission:approve_disposal']);

    $routes->get('/archive-disposal/disposal/completed', 'ArchiveDisposalController::completedDisposal', ['as' => 'disposal.completed']);

});



$routes->get('/debug-borrow', function() {

    echo "<h1>Borrow Debug Information</h1>";

    

    try {

        $db = \Config\Database::connect();

        echo "<h2>✓ Database Connected</h2>";

        echo "<p>Driver: " . $db->DBDriver . "</p>";

        

        // Check authentication

        echo "<h3>Authentication Check</h3>";

        $session = session();

        if ($session->has('user_id')) {

            echo "<strong style='color: green;'>✓ User logged in (ID: " . $session->get('user_id') . ")</strong>";

        } else {

            echo "<strong style='color: red;'>✗ User NOT logged in</strong>";

            echo "<p>Visit <a href='/'>Login Page</a> first</p>";

        }

        

        // Check permissions

        if ($session->has('user_id')) {

            echo "<h3>Permission Check</h3>";

            $permissionService = service('permissionService');

            $userId = $session->get('user_id');

            $userRole = $permissionService->getUserRole($userId);

            echo "<p>User Role: <strong>$userRole</strong></p>";

            

            $hasRequestBorrow = $permissionService->hasPermission($userId, 'request_borrow');

            $hasApproveBorrow = $permissionService->hasPermission($userId, 'approve_borrow');

            

            echo "<p>request_borrow: " . ($hasRequestBorrow ? "<span style='color: green;'>✓</span>" : "<span style='color: red;'>✗</span>") . "</p>";

            echo "<p>approve_borrow: " . ($hasApproveBorrow ? "<span style='color: green;'>✓</span>" : "<span style='color: red;'>✗</span>") . "</p>";

        }

        

        // Check tables

        $tables = $db->listTables();

        echo "<h3>Tables (" . count($tables) . " total)</h3>";

        if (count($tables) > 0) {

            echo "<strong style='color: green;'>✓ Database has tables</strong>";

        }

        

        // Check folders

        if (in_array('folders', $tables)) {

            echo "<h3>Folders Table</h3>";

            $total = $db->table('folders')->countAllResults();

            $available = $db->table('folders')->where('status', 'Available')->countAllResults();

            echo "<p>Total: $total | Available: $available</p>";

            

            if ($available > 0) {

                echo "<strong style='color: green;'>✓ Available folders exist</strong>";

            } else {

                echo "<strong style='color: red;'>✗ No available folders</strong>";

                echo "<p>Try updating folder status to 'Available' in database.</p>";

            }

        }

        

        // Check borrow_transactions

        if (in_array('borrow_transactions', $tables)) {

            echo "<h3>Borrow Transactions Table</h3>";

            $fields = $db->getFieldData('borrow_transactions');

            

            $hasColumn = false;

            foreach ($fields as $field) {

                if ($field->name === 'borrower_name') {

                    $hasColumn = true;

                    break;

                }

            }

            

            if ($hasColumn) {

                echo "<strong style='color: green;'>✓ borrower_name column exists</strong>";

            } else {

                echo "<strong style='color: red;'>✗ borrower_name column MISSING - Run migrations</strong>";

                echo "<p>Run: <code>php spark migrate</code></p>";

            }

        }

        

        // Test validation

        echo "<h3>Validation Test</h3>";

        $folder = $db->table('folders')->where('status', 'Available')->get()->getFirstRow('array');

        if ($folder) {

            $testData = [

                'borrower_name' => 'Test Person',

                'folder_id' => $folder['folder_id'],

                'expected_return_date' => date('Y-m-d', strtotime('+7 days')),

            ];

            

            $model = new \App\Models\BorrowTransactionModel();

            if ($model->validate($testData)) {

                echo "<strong style='color: green;'>✓ Validation PASSED</strong>";

            } else {

                echo "<strong style='color: red;'>✗ Validation FAILED</strong>";

                foreach ($model->errors() as $field => $error) {

                    echo "<p>$field: $error</p>";

                }

            }

        } else {

            echo "<strong style='color: red;'>✗ No available folders to test with</strong>";

        }

        

        // Check borrow records

        echo "<h3>Recent Borrow Records</h3>";

        $borrows = $db->table('borrow_transactions')->limit(5)->orderBy('created_at', 'DESC')->get()->getResult('array');

        if (!empty($borrows)) {

            echo "<table border='1' cellpadding='5'>";

            echo "<tr><th>ID</th><th>Borrower</th><th>Folder</th><th>Status</th><th>Created</th></tr>";

            foreach ($borrows as $b) {

                echo "<tr>";

                echo "<td>{$b['transaction_id']}</td>";

                echo "<td>{$b['borrower_name']}</td>";

                echo "<td>{$b['folder_id']}</td>";

                echo "<td>{$b['status']}</td>";

                echo "<td>{$b['created_at']}</td>";

                echo "</tr>";

            }

            echo "</table>";

        } else {

            echo "<p>No borrow records yet</p>";

        }

        

    } catch (\Exception $e) {

        echo "<h2>✗ Error: " . $e->getMessage() . "</h2>";

        echo "<p>" . $e->getFile() . ":" . $e->getLine() . "</p>";

        echo "<pre>" . $e->getTraceAsString() . "</pre>";

    }

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



// TEST API ENDPOINT
$routes->get('api/test', function() {
    return json_encode(['status' => 'ok', 'message' => 'API routing works!']);
});

// API Routes - Shelf Map Data
$routes->get('api/shelfmap/data',          'Api\ShelfMapApiController::getShelfData',     ['as' => 'api.shelfmap.data']);
$routes->get('api/shelfmap/location/(:num)', 'Api\ShelfMapApiController::getFoldersByLocation/$1', ['as' => 'api.shelfmap.location']);
$routes->get('api/shelfmap/search',        'Api\ShelfMapApiController::searchFolders',     ['as' => 'api.shelfmap.search']);

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

