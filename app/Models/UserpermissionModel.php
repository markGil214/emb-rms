<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPermissionModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'user_permissions';
    protected $primaryKey = 'user_permission_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'permission_key', 'assigned_at', 'assigned_by_id'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'assigned_at';
    protected $updatedField = '';

    public function getUserPermissions($userId)
    {
        return $this->where('user_id', $userId)
            ->select('permission_key')
            ->orderBy('user_permission_id', 'ASC')
            ->findAll();
    }

    public function assignPermission($userId, $permissionKey, $assignedById = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'permission_key' => $permissionKey,
            'assigned_by_id' => $assignedById,
        ]);
    }

    public function revokePermission($userId, $permissionKey)
    {
        return $this->where('user_id', $userId)
            ->where('permission_key', $permissionKey)
            ->delete();
    }

    public function hasPermission($userId, $permissionKey)
    {
        return $this->where('user_id', $userId)
            ->where('permission_key', $permissionKey)
            ->countAllResults() > 0;
    }
}