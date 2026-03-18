<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PermissionFilter - Permission-based route protection
 * 
 * Checks if user has required permission before allowing access to route.
 * Used with filter syntax: ['filter' => 'permission:permission_key']
 * 
 * Example usage in routes:
 *   $routes->get(
 *       'documents/create',
 *       'Documents::create',
 *       ['filter' => 'permission:create_document_record']
 *   );
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get the permission key from route filter arguments
        if (empty($arguments)) {
            return;
        }

        $permissionKey = $arguments[0] ?? null;

        if (!$permissionKey) {
            return;
        }

        // Get authenticated user
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            // Not authenticated - redirect to login
            return redirect()->to('/');
        }

        // Check if user has permission
        $permissionService = service('permissionService');

        if (!$permissionService->hasPermission($userId, $permissionKey)) {
            // User lacks permission - return 403 Forbidden response
            $response = service('response');
            $response->setStatusCode(403, 'Forbidden');
            $response->setBody('Access denied. You do not have the required permission: ' . $permissionKey);
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after request
    }
}