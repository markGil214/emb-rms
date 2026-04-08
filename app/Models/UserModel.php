<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\UserEntity;

class UserModel extends Model
{
	protected $DBGroup = 'default';
	protected $table = 'users';
	protected $primaryKey = 'user_id';
	protected $useAutoIncrement = true;
	protected $insertID = 0;
	protected $returnType = 'array';  // Return as array for now - UserEntity has hydration issues
	protected $useSoftDelete = false;
	protected $protectFields = true;
	protected $allowedFields = ['username', 'password', 'email', 'role', 'remember_token', 'manager_id'];

	// Dates
	protected $useTimestamps = true;  // Enable automatic timestamp management
	protected $dateFormat = 'datetime';
	protected $createdField = 'created_at';
	protected $updatedField = 'updated_at';
	protected $deletedField = 'deleted_at';

	// Validation
	protected $validationRules = [
		'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,user_id,{user_id}]',
		'password' => 'required|min_length[8]|max_length[255]',
		'email'    => 'required|valid_email|is_unique[users.email,user_id,{user_id}]',
		'role'     => 'required|in_list[RecordsOfficer,Admin,SuperAdmin]',
	];
	
	protected $validationMessages = [
		'username' => [
			'required'   => 'Username is required',
			'min_length' => 'Username must be at least 3 characters',
			'max_length' => 'Username cannot exceed 50 characters',
			'is_unique'  => 'This username is already taken',
		],
		'password' => [
			'required'   => 'Password is required',
			'min_length' => 'Password must be at least 8 characters',
		],
		'email' => [
			'required'     => 'Email is required',
			'valid_email'  => 'Please provide a valid email address',
			'is_unique'    => 'This email is already registered',
		],
		'role' => [
			'required' => 'Role is required',
			'in_list'  => 'Invalid role selected',
		],
	];
	
	protected $skipValidation = false;
	protected $cleanValidationRules = true;

	// Callbacks
	protected $allowCallbacks = true;
	protected $beforeInsert = ['hashPassword'];
	protected $afterInsert = [];
	protected $beforeUpdate = ['hashPassword'];
	protected $afterUpdate = [];
	protected $beforeFind = [];
	protected $afterFind = [];
	protected $beforeDelete = [];
	protected $afterDelete = [];

	/**
	 * Hash password before insert or update
	 * Only hash if password field is present in data
	 */
	protected function hashPassword(array $data)
	{
		if (isset($data['data']['password'])) {
			$data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
		}
		return $data;
	}
}
