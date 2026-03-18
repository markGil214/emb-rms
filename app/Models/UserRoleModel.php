<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'user_roles';
    protected $primaryKey = 'user_role_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'role_id', 'assigned_at', 'assigned_by_id'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'assigned_at';

    public function getUserRole($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    public function assignRole($userId, $roleId, $assignedById = null)
    {
        return $this->updateBatch([
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by_id' => $assignedById,
        ], 'user_id') ?: $this->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by_id' => $assignedById,
        ]);
    }
}