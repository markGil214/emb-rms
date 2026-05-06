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
	protected $allowedFields = ['username', 'first_name', 'last_name', 'password', 'email', 'role', 'status', 'inactive_at', 'remember_token', 'manager_id'];

	// Dates
	protected $useTimestamps = true;  // Enable automatic timestamp management
	protected $dateFormat = 'datetime';
	protected $createdField = 'created_at';
	protected $updatedField = 'updated_at';
	protected $deletedField = 'deleted_at';

	// Validation
	protected $validationRules = [
		'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
		'first_name' => 'permit_empty|max_length[100]',
		'last_name' => 'permit_empty|max_length[100]',
		'password' => 'permit_empty|min_length[8]|max_length[255]',
		'email'    => 'required|valid_email|is_unique[users.email]',
		'role'     => 'required|in_list[RecordsOfficer,Admin,SuperAdmin]',
		'status'   => 'permit_empty|in_list[Active,Inactive,Pending]',
	];
	
	protected $validationMessages = [
		'username' => [
			'required'   => 'Username is required',
			'min_length' => 'Username must be at least 3 characters',
			'max_length' => 'Username cannot exceed 50 characters',
			'is_unique'  => 'This username is already taken',
		],
		'first_name' => [
			'max_length' => 'First name cannot exceed 100 characters',
		],
		'last_name' => [
			'max_length' => 'Last name cannot exceed 100 characters',
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
		'status' => [
			'in_list' => 'Status must be Active or Inactive',
		],
	];
	
	protected $skipValidation = false;
	protected $cleanValidationRules = true;

	// Callbacks
	protected $allowCallbacks = true;
	protected $beforeInsert = ['setDefaultStatus', 'syncInactiveTimestamp', 'hashPassword'];
	protected $afterInsert = [];
	protected $beforeUpdate = ['syncInactiveTimestamp', 'hashPassword'];
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

	protected function setDefaultStatus(array $data)
	{
		if (!isset($data['data']['status']) || trim((string) $data['data']['status']) === '') {
			$data['data']['status'] = 'Active';
		}

		return $data;
	}

	protected function syncInactiveTimestamp(array $data)
	{
		if (!isset($data['data']['status'])) {
			return $data;
		}

		$status = trim((string) $data['data']['status']);
		if ($status === 'Inactive') {
			if (!isset($data['data']['inactive_at']) || trim((string) $data['data']['inactive_at']) === '') {
				$data['data']['inactive_at'] = date('Y-m-d H:i:s');
			}
		} elseif ($status === 'Active') {
			$data['data']['inactive_at'] = null;
		}

		return $data;
	}
}
