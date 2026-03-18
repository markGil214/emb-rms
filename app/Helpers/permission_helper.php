<?php

/**
 * Permission Helper Functions
 * 
 * Provides convenient permission checking in views
 */

if (!function_exists('can')) {
    /**
     * Check if authenticated user has a specific permission
     * 
     * Usage in views: <?php if (can('approve_borrow_requests')): ?>
     * 
     * @param string $permission Permission key to check
     * @return bool
     */
    function can($permission)
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return false;
        }

        $permissionService = service('permissionService');
        return $permissionService->hasPermission($userId, $permission);
    }
}

if (!function_exists('user_role')) {
    /**
     * Get current authenticated user's role
     * 
     * @return string|null Role name or null
     */
    function user_role()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return null;
        }

        $permissionService = service('permissionService');
        return $permissionService->getUserRole($userId);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if current user is super admin
     * 
     * @return bool
     */
    function is_super_admin()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return false;
        }

        $permissionService = service('permissionService');
        return $permissionService->isSuperAdmin($userId);
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if current user is admin or super admin
     * 
     * @return bool
     */
    function is_admin()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return false;
        }

        $permissionService = service('permissionService');
        return $permissionService->isAdmin($userId);
    }
}

if (!function_exists('can_manage_users')) {
    /**
     * Check if user can manage users (super admin only)
     * 
     * @return bool
     */
    function can_manage_users()
    {
        return can('manage_users');
    }
}

if (!function_exists('can_view_audit_logs')) {
    /**
     * Check if user can view audit logs (super admin only)
     * 
     * @return bool
     */
    function can_view_audit_logs()
    {
        return can('view_audit_logs');
    }
}