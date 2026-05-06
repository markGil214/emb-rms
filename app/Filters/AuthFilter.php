<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter - Authentication Middleware
 * 
 * Checks if user is logged in before allowing access to protected routes.
 * Used with filter alias 'auth' in routes configuration.
 * 
 * Example usage in routes:
 *   $routes->get('/dashboard', 'DashboardController::index', ['filter' => 'auth']);
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is authenticated
        $auth = service('authentication'); // Make sure Authentication service is registered
        
        if (!$auth->check()) {
            // Redirect to login if not authenticated
            return redirect()->to('/');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after request
    }
}
