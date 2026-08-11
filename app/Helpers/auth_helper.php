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

// user_role() / is_admin() / is_super_admin() used to be duplicated here
// against raw session role (legacy `users.role`), which silently shadowed
// the RBAC-reconciled versions in permission_helper.php due to helper load
// order. Those are now the only definitions — see app/Helpers/permission_helper.php.
