<?php
    /**
     * Get user's effective permissions (role + custom)
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        $permissionService = service('permissionService');
        return $permissionService->userPermissions($this->attributes['user_id']);
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $permission
     * @return bool
     */
    public function can(string $permission): bool
    {
        $permissionService = service('permissionService');
        return $permissionService->hasPermission($this->attributes['user_id'], $permission);
    }

    /**
     * Get user's assigned role name
     * 
     * @return string|null
     */
    public function getRole(): ?string
    {
        $permissionService = service('permissionService');
        return $permissionService->getUserRole($this->attributes['user_id']);
    }

    /**
     * Check if user is super admin
     * 
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        $permissionService = service('permissionService');
        return $permissionService->isSuperAdmin($this->attributes['user_id']);
    }

    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        $permissionService = service('permissionService');
        return $permissionService->isAdmin($this->attributes['user_id']);
    }