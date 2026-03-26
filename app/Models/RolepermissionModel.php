<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'role_permissions';
    protected $primaryKey = 'role_permission_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['role_id', 'permission_key'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    public function getRolePermissions($roleId)
    {
        return $this->where('role_id', $roleId)
            ->select('permission_key')
            ->orderBy('role_permission_id', 'ASC')
            ->findAll();
    }
}