<?php

namespace App\Libraries;

use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserRoleModel;
use App\Models\UserPermissionModel;
use App\Config\Permissions;

class PermissionService
{
    private const SYSTEM_DEFAULT_PERMISSIONS = [
        'request_borrow',
        'view_all_borrow',
        'process_borrow_release',
        'view_alerts_module',
    ];

    protected $roleModel;
    protected $rolePermissionModel;
    protected $userRoleModel;
    protected $userPermissionModel;
    protected $db;

    public function __construct()
    {
        $this->roleModel = model(RoleModel::class);
        $this->rolePermissionModel = model(RolePermissionModel::class);
        $this->userRoleModel = model(UserRoleModel::class);
        $this->userPermissionModel = model(UserPermissionModel::class);
        $this->db = \Config\Database::connect();
    }

    /**
     * Get all permissions for a user (role + custom)
     * 
     * @param int $userId
     * @return array Permission keys array
     */
    public function userPermissions($userId)
    {
        $rolePermissions = $this->getRolePermissions($userId);
        $customPermissions = $this->getCustomPermissions($userId);

        return array_values(array_unique(array_merge(
            self::SYSTEM_DEFAULT_PERMISSIONS,
            $rolePermissions,
            $customPermissions
        )));
    }

    /**
     * Get permissions from user's assigned role(s)
     * 
     * @param int $userId
     * @return array
     */
    public function getRolePermissions($userId)
    {
        $userRole = $this->userRoleModel->getUserRole($userId);
        
        if (!$userRole) {
            return [];
        }

        $rolePermissions = $this->rolePermissionModel->getRolePermissions($userRole['role_id']);
        
        $permissions = [];
        foreach ($rolePermissions as $rp) {
            $permissions[] = $rp['permission_key'];
        }

        return $permissions;
    }

    /**
     * Get custom permissions explicitly assigned to user (overrides)
     * 
     * @param int $userId
     * @return array
     */
    public function getCustomPermissions($userId)
    {
        $customPerms = $this->userPermissionModel->getUserPermissions($userId);
        
        $permissions = [];
        foreach ($customPerms as $cp) {
            $permissions[] = $cp['permission_key'];
        }

        return $permissions;
    }

    /**
     * Check if user has a specific permission
     * 
     * @param int $userId
     * @param string $permissionKey
     * @return bool
     */
    public function hasPermission($userId, $permissionKey)
    {
        // Super admin has all permissions
        $userRole = $this->userRoleModel->getUserRole($userId);
        if ($userRole && isset($userRole['role_id'])) {
            // Get role name from role_id
            $role = $this->roleModel->find($userRole['role_id']);
            if ($role && isset($role['role_name']) && $role['role_name'] === 'super_admin') {
                return true;
            }
        }

        $permissions = $this->userPermissions($userId);
        return in_array($permissionKey, $permissions);
    }

    /**
     * Check if a permission is inherited from role (not custom-assigned)
     * 
     * @param int $userId
     * @param string $permissionKey
     * @return bool
     */
    public function isInheritedPermission($userId, $permissionKey)
    {
        $rolePermissions = $this->getRolePermissions($userId);
        return in_array($permissionKey, $rolePermissions);
    }

    /**
     * Check if a permission is custom-assigned (not from role)
     * 
     * @param int $userId
     * @param string $permissionKey
     * @return bool
     */
    public function isCustomPermission($userId, $permissionKey)
    {
        return $this->userPermissionModel->hasPermission($userId, $permissionKey);
    }

    /**
     * Assign permission to user (custom override)
     * 
     * @param int $userId
     * @param string $permissionKey
     * @param int $assignedById
     * @return bool
     */
    public function assignPermission($userId, $permissionKey, $assignedById = null)
    {
        // Check if already assigned
        if ($this->isCustomPermission($userId, $permissionKey)) {
            return true;
        }

        return $this->userPermissionModel->assignPermission($userId, $permissionKey, $assignedById);
    }

    /**
     * Revoke custom permission from user
     * (Only revokes custom-assigned perms, not role-based)
     * 
     * @param int $userId
     * @param string $permissionKey
     * @return bool
     */
    public function revokePermission($userId, $permissionKey)
    {
        return $this->userPermissionModel->revokePermission($userId, $permissionKey);
    }

    /**
     * Assign role to user and populate default role permissions
     * 
     * @param int $userId
     * @param int $roleId
     * @param int $assignedById
     * @return bool
     */
    public function assignRole($userId, $roleId, $assignedById = null)
    {
        // Remove any existing custom permissions for this user
        // (they'll be replaced by role defaults)
        $this->db->table('user_permissions')->where('user_id', $userId)->delete();

        // Assign role
        return $this->userRoleModel->assignRole($userId, $roleId, $assignedById);
    }

    /**
     * Get user's current role
     * 
     * @param int $userId
     * @return string|null Role name or null
     */
    public function getUserRole($userId)
    {
        $userRole = $this->userRoleModel->getUserRole($userId);
        
        if (!$userRole) {
            return null;
        }

        $role = $this->roleModel->find($userRole['role_id']);
        return $role['role_name'] ?? null;
    }

    /**
     * Get role name by ID
     * 
     * @param int $roleId
     * @return string|null
     */
    public function getRoleName($roleId)
    {
        $role = $this->roleModel->find($roleId);
        return $role['role_name'] ?? null;
    }

    /**
     * Get role ID by name
     * 
     * @param string $roleName
     * @return int|null
     */
    public function getRoleId($roleName)
    {
        $role = $this->roleModel->where('role_name', $roleName)->first();
        return $role['role_id'] ?? null;
    }

    /**
     * Get all available permissions (from Permissions config)
     * 
     * @return array
     */
    public function getAllPermissions()
    {
        return Permissions::flat();
    }

    /**
     * Get grouped permissions for UI
     * 
     * @return array
     */
    public function getGroupedPermissions()
    {
        return Permissions::grouped();
    }

    /**
     * Bulk assign permissions to user
     * 
     * @param int $userId
     * @param array $permissionKeys
     * @param int $assignedById
     * @return bool
     */
    public function assignPermissions($userId, array $permissionKeys, $assignedById = null)
    {
        foreach ($permissionKeys as $permission) {
            $this->assignPermission($userId, $permission, $assignedById);
        }
        return true;
    }

    /**
     * Revoke all custom permissions from user
     * (Keeps role-based permissions intact)
     * 
     * @param int $userId
     * @return bool
     */
    public function revokeAllCustomPermissions($userId)
    {
        return $this->db->table('user_permissions')->where('user_id', $userId)->delete();
    }

    /**
     * Get role permissions by role ID
     * 
     * @param int $roleId
     * @return array
     */
    public function getRolePermissionsByRoleId($roleId)
    {
        $perms = $this->rolePermissionModel->getRolePermissions($roleId);
        $permissions = [];
        foreach ($perms as $p) {
            $permissions[] = $p['permission_key'];
        }
        return $permissions;
    }

    /**
     * Check if user is super admin
     * 
     * @param int $userId
     * @return bool
     */
    public function isSuperAdmin($userId)
    {
        return $this->getUserRole($userId) === 'super_admin';
    }

    /**
     * Check if user is admin or super admin
     * 
     * @param int $userId
     * @return bool
     */
    public function isAdmin($userId)
    {
        $role = $this->getUserRole($userId);
        return in_array($role, ['admin', 'super_admin']);
    }

    /**
     * Sync all permissions from Permissions config to super_admin role
     * Ensures super_admin always has all permissions, including new ones
     * 
     * @param int|null $assignedById User ID performing the sync (for audit)
     * @return int Count of newly synced permissions
     */
    public function syncSuperAdminPermissions($assignedById = null)
    {
        $superAdminId = $this->getRoleId('super_admin');
        if (!$superAdminId) {
            return 0;
        }

        // Get all permissions from config
        $allPermissions = Permissions::flat();
        
        // Get currently assigned permissions to super_admin
        $currentPerms = $this->db->table('role_permissions')
            ->where('role_id', $superAdminId)
            ->get()
            ->getResultArray();
        
        $currentPermKeys = array_column($currentPerms, 'permission_key');
        
        // Find missing permissions
        $missingPerms = array_diff($allPermissions, $currentPermKeys);
        
        // Insert missing permissions
        $synced = 0;
        foreach ($missingPerms as $permKey) {
            $this->db->table('role_permissions')->insert([
                'role_id' => $superAdminId,
                'permission_key' => $permKey,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $synced++;
        }

        if ($synced > 0) {
            log_message('info', "Synced $synced new permissions to super_admin role");
            
            // Log if audit log available
            if ($assignedById) {
                $auditLog = model('AuditlogModel');
                $auditLog->log(
                    'permissions_synced',
                    'role',
                    $superAdminId,
                    null,
                    ['synced_count' => $synced, 'permissions' => $missingPerms],
                    $assignedById
                );
            }
        }

        return $synced;
    }
}