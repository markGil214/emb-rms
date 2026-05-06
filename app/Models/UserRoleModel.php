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
    protected $updatedField = '';

    public function getUserRole($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    public function assignRole($userId, $roleId, $assignedById = null)
    {
        $exists = $this->where('user_id', $userId)->first();

        $data = [
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by_id' => $assignedById,
        ];

        if ($exists) {
            return $this->update($exists['user_role_id'], $data);
        }

        return $this->insert($data);
    }
}