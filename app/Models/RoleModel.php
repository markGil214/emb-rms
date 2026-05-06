<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['role_name', 'description', 'is_system_role'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    protected $validationRules = [
        'role_name' => 'required|min_length[3]|max_length[50]|is_unique[roles.role_name,role_id,{role_id}]',
        'description' => 'permit_empty|string',
        'is_system_role' => 'permit_empty|in_list[0,1]',
    ];
}