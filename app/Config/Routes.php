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
$routes->get('/shelfmap',         'DashboardController::shelfmap', ['as' => 'shelfmap', 'filter' => 'auth']);

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
