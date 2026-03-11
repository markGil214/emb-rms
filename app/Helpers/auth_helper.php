<?php

/**
 * Authentication Helper Functions
 * 
 * Provides convenient access to authentication functionality
 */

if (!function_exists('auth')) {
    /**
     * Get the Authentication service instance
     * 
     * @return \App\Libraries\Authentication
     */
    function auth()
    {
        return service('authentication');
    }
}

if (!function_exists('auth_user')) {
    /**
     * Get the current authenticated user
     * 
     * @return array|null
     */
    function auth_user()
    {
        return service('authentication')->user();
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    function is_logged_in()
    {
        return service('authentication')->check();
    }
}

if (!function_exists('user_role')) {
    /**
     * Get current user's role
     * 
     * @return string|null
     */
    function user_role()
    {
        $user = service('authentication')->user();
        return $user['role'] ?? null;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if user is admin or super admin
     * 
     * @return bool
     */
    function is_admin()
    {
        $role = user_role();
        return in_array($role, ['Admin', 'SuperAdmin']);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if user is super admin
     * 
     * @return bool
     */
    function is_super_admin()
    {
        return user_role() === 'SuperAdmin';
    }
}
