<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * GuestFilter - Guest-Only Middleware
 * 
 * Redirects authenticated users away from guest-only routes (like login/register pages).
 * Used with filter alias 'guest' in routes configuration.
 * 
 * Example usage in routes:
 *   $routes->get('/login', 'AuthController::login', ['filter' => 'guest']);
 */
class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is already authenticated
        $auth = service('authentication');
        
        if ($auth->check()) {
            // Redirect authenticated users away from guest-only routes
            return redirect()->to('/dashboard');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after request
    }
}
