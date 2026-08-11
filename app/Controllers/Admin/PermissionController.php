<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Config\Permissions;

class PermissionController extends BaseController
{
	/**
	 * Display permission management interface
	 * Super Admin only
	 */
	public function index()
	{
		// Check permission
		if (!can('manage_users')) {
			return $this->response->setStatusCode(403, 'Forbidden');
		}

		$db = \Config\Database::connect();
		$permissionService = service('permissionService');

		// Ensure audit_logs table exists to prevent crash
		if (!$db->tableExists('audit_logs')) {
			$db->query("CREATE TABLE IF NOT EXISTS `audit_logs` (
				`log_id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) DEFAULT NULL,
				`entity_type` varchar(50) DEFAULT NULL,
				`entity_id` int(11) DEFAULT NULL,
				`action` varchar(100) DEFAULT NULL,
				`old_data` text DEFAULT NULL,
				`new_data` text DEFAULT NULL,
				`created_at` datetime DEFAULT NULL,
				PRIMARY KEY (`log_id`),
				KEY `idx_user` (`user_id`),
				KEY `idx_entity` (`entity_type`, `entity_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
		}

		$usersByRole = [];
		$usersWithRoles = $db->table('users u')
			->select("u.user_id, u.username, u.email, u.first_name, u.last_name, TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) AS full_name, COALESCE(u.status, 'Active') AS status, COALESCE(r.role_name, u.role) AS role_name")
			->join('user_roles ur', 'ur.user_id = u.user_id', 'left')
			->join('roles r', 'r.role_id = ur.role_id', 'left')
			->orderBy('u.username', 'ASC')
			->get()
			->getResultArray();

		foreach ($usersWithRoles as $user) {
			$roleName = trim((string) ($user['role_name'] ?? ''));
			if ($roleName === '') {
				continue;
			}

			$usersByRole[$roleName][] = $user;
		}

		// Get all roles
		$roles = $db->table('roles')
			->orderBy('role_id', 'ASC')
			->get()
			->getResultArray();

		// Get all permissions (grouped) for the matrix columns
		$allPermissions = $permissionService->getGroupedPermissions();

		// Get all permissions for all users to pre-populate the matrix
		$userPermissionsMap = [];
		foreach ($usersWithRoles as $user) {
			$userPermissionsMap[$user['user_id']] = $permissionService->userPermissions($user['user_id']);
		}

		$data = [
			'title' => 'User Permissions',
			'users' => $usersWithRoles,
			'usersByRole' => $usersByRole,
			'roles' => $roles,
			'permissions' => $allPermissions,
			'userPermissionsMap' => $userPermissionsMap,
		];

		return view('permissions/manage', $data);
	}

	/**
	 * Load user's current permissions via AJAX
	 */
	public function loadUser($userId)
	{
		if (!can('manage_users')) {
			return $this->response->setJSON(['error' => 'Forbidden'], 403);
		}

		$db = \Config\Database::connect();
		$permissionService = service('permissionService');

		// Get user
		$user = $db->table('users')->where('user_id', $userId)->get()->getRowArray();
		if (!$user) {
			return $this->response->setJSON(['error' => 'User not found'], 404);
		}

		$user['status'] = trim((string) ($user['status'] ?? '')) ?: 'Active';
		$user['full_name'] = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));

		// Get user's role
		$userRole = $permissionService->getUserRole($userId);
		$roleId = null;
		if ($userRole) {
			$role = $db->table('roles')
				->where('role_name', $userRole)
				->get()->getRowArray();
			$roleId = $role['role_id'] ?? null;
		}

		// Get user's permissions
		$userPermissions = $permissionService->userPermissions($userId);
		$rolePermissions = $permissionService->getRolePermissions($userId);
		$customPermissions = $permissionService->getCustomPermissions($userId);

		return $this->response->setJSON([
			'user' => $user,
			'roleId' => $roleId,
			'rolePermissions' => $rolePermissions,
			'customPermissions' => array_column($customPermissions, 'permission_key'),
			'allPermissions' => $userPermissions,
		]);
	}

	/**
	 * Save user permissions via AJAX
	 */
	public function savePermissions($userId)
	{
		if (!can('manage_users')) {
			return $this->response->setJSON(['error' => 'Forbidden'], 403);
		}

		$db = \Config\Database::connect();
		$permissionService = service('permissionService');

		$roleId = $this->request->getPost('role_id');
		$selectedPermissions = $this->request->getPost('permissions') ?? [];

		// ✅ Get current role info
		$userRoleName = $permissionService->getUserRole($userId);
		$dbRole = $db->table('roles')->where('role_name', $userRoleName)->get()->getRowArray();
		$currentRoleId = $dbRole['role_id'] ?? null;

		// ✅ If roleId not provided, use current one
		if (!$roleId) {
			$roleId = $currentRoleId;
		}

		// ✅ Only assign new role if it actually changed and was provided
		if ($roleId && $roleId != $currentRoleId && $this->request->getPost('role_id')) {
			$adminId = session()->get('user_id');
			$permissionService->assignRole($userId, $roleId, $adminId);

			// Keep the legacy users.role column in sync so session role
			// (set at login from this column) doesn't go stale after an
			// RBAC-only role change.
			$newRole = $db->table('roles')->where('role_id', $roleId)->get()->getRowArray();
			if ($newRole) {
				$roleNameMap = [
					'super_admin' => 'SuperAdmin',
					'admin' => 'Admin',
					'records_officer' => 'RecordsOfficer',
				];
				$legacyRoleName = $roleNameMap[strtolower($newRole['role_name'])] ?? $newRole['role_name'];
				$db->table('users')->where('user_id', $userId)->update(['role' => $legacyRoleName]);
			}
		}

		// Get permissions from the role (these cannot be unchecked per-user in current architecture)
		$rolePermissions = [];
		if ($roleId) {
			$rolePerms = $db->table('role_permissions')
				->where('role_id', $roleId)
				->get()
				->getResultArray();
			$rolePermissions = array_column($rolePerms, 'permission_key');
		}

		// ✅ Get current custom permissions before making changes
		$currentPermsArray = $permissionService->getCustomPermissions($userId);
		
		// ✅ Only manage custom permissions (not from role)
		$customSelectedPerms = array_diff($selectedPermissions, $rolePermissions);
		
		// ✅ Find permissions to add
		$permsToAdd = array_diff($customSelectedPerms, $currentPermsArray);
		
		// ✅ Find permissions to remove (no longer selected and not from role)
		$permsToRemove = array_diff($currentPermsArray, $customSelectedPerms);
		
		// Add new permissions
		foreach ($permsToAdd as $perm) {
			$adminId = session()->get('user_id');
			$permissionService->assignPermission($userId, $perm, $adminId);
		}
		
		// Remove unchecked permissions
		foreach ($permsToRemove as $perm) {
			$permissionService->revokePermission($userId, $perm);
		}

		// Log the change
		$auditLog = model('AuditLogModel');
		$auditLog->log(
			'permissions_updated',
			'user',
			$userId,
			null,
			[
				'role_id' => $roleId,
				'permissions' => $selectedPermissions,
			]
		);

		return $this->response->setJSON(['success' => true, 'message' => 'Permissions updated']);
	}

	/**
	 * Sync permissions from Config file to Database
	 */
	public function sync()
	{
		if (!can('manage_users')) {
			return $this->response->setJSON(['error' => 'Forbidden'], 403);
		}

		$permissionService = service('permissionService');
		$adminId = session()->get('user_id');

		// 1. Sync all role defaults
		$summary = $permissionService->syncAllRolePermissions();

		// 2. Force Super Admin sync
		$syncedCount = $permissionService->syncSuperAdminPermissions($adminId);

		return $this->response->setJSON([
			'success' => true, 
			'message' => 'System-wide synchronization complete. Role defaults updated and Super Admin verified.',
			'summary' => $summary,
			'syncedCount' => $syncedCount
		]);
	}

	/**
	 * Search users via AJAX
	 */
	public function searchUsers()
	{
		if (!can('manage_users')) {
			return $this->response->setJSON(['error' => 'Forbidden'], 403);
		}

		$query = $this->request->getGet('q', FILTER_SANITIZE_STRING);
		$db = \Config\Database::connect();

		$users = $db->table('users')
			->select("user_id, username, email, first_name, last_name, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS full_name, COALESCE(status, 'Active') AS status")
			->where("username LIKE '%{$query}%' OR email LIKE '%{$query}%' OR first_name LIKE '%{$query}%' OR last_name LIKE '%{$query}%'")
			->limit(10)
			->get()
			->getResultArray();

		return $this->response->setJSON($users);
	}

	/**
	 * Get permission change history for a user
	 */
	public function getHistory($userId)
	{
		if (!can('manage_users')) {
			return $this->response->setJSON(['error' => 'Forbidden'], 403);
		}

		$db = \Config\Database::connect();

		$history = $db->table('audit_logs')
			->select('audit_logs.*, users.username as admin_name')
			->join('users', 'users.user_id = audit_logs.user_id', 'left')
			->where('audit_logs.target_user_id', $userId)
			->where('audit_logs.action_type IN ("permission_changed", "role_assigned")')
			->orderBy('audit_logs.created_at', 'DESC')
			->limit(20)
			->get()
			->getResultArray();

		return $this->response->setJSON($history);
	}

	/**
	 * Load permissions for a specific role (for role-based matrix view)
	 */
	public function loadRole($roleId)
	{
		try {
			if (!can('manage_users')) {
				return $this->response->setJSON(['error' => 'Forbidden'], 403);
			}

			$roleId = (int) $roleId;
			if ($roleId <= 0) {
				return $this->response->setJSON(['error' => 'Invalid role ID'], 400);
			}

			$db = \Config\Database::connect();
			
			// Get role to check if super_admin
			$role = $db->table('roles')->where('role_id', $roleId)->get()->getRowArray();
			if (!$role) {
				return $this->response->setJSON(['error' => 'Role not found'], 404);
			}

			// If super_admin, return all permissions from config
			if ($role['role_name'] === 'super_admin') {
				$allPermissions = Permissions::grouped();
				$permissions = [];
				
				// Flatten all permissions
				foreach ($allPermissions as $group) {
					foreach ($group as $key => $label) {
						$permissions[$key] = true;
					}
				}
				
				log_message('info', "Loaded " . count($permissions) . " ALL permissions for super_admin");
				return $this->response->setJSON($permissions);
			}

			// For normal roles, load only assigned permissions
			$rolePerms = $db->table('role_permissions')
				->where('role_id', $roleId)
				->get()
				->getResultArray();

			if (empty($rolePerms)) {
				log_message('info', "No permissions found for role $roleId");
				return $this->response->setJSON([]);
			}

			$permissions = [];
			foreach ($rolePerms as $perm) {
				if (isset($perm['permission_key'])) {
					$permissions[$perm['permission_key']] = true;
				}
			}

			log_message('info', "Loaded " . count($permissions) . " permissions for role $roleId");
			return $this->response->setJSON($permissions);
		} catch (\Exception $e) {
			log_message('error', 'loadRole error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
			return $this->response->setJSON(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Save permissions for roles (role matrix bulk update)
	 */
	public function saveRolePerms()
	{
		if (!can('manage_users')) {
			return $this->response->setJSON(['error' => 'Forbidden'], 403);
		}

		try {
			$db = \Config\Database::connect();
			$permissionService = service('permissionService');
			
			// Step 1: Sync super_admin with latest permissions before making changes
			$adminId = session()->get('user_id');
			$synced = $permissionService->syncSuperAdminPermissions($adminId);
			if ($synced > 0) {
				log_message('info', "Auto-synced $synced new permissions to super_admin");
			}
			
			// Try multiple ways to get JSON input
			$input = null;
			
			// Method 1: Parse raw body directly (most reliable)
			$rawBody = file_get_contents('php://input');
			if ($rawBody) {
				$input = json_decode($rawBody, true);
				log_message('info', 'Parsed JSON from php://input');
			}
			
			// Method 2: getJSON() as fallback
			if (!$input) {
				$jsonObj = $this->request->getJSON();
				if ($jsonObj) {
					$input = json_decode(json_encode($jsonObj), true);
					log_message('info', 'Parsed JSON from getJSON()');
				}
			}

			// Debug logging
			log_message('info', 'saveRolePerms called');
			log_message('info', 'Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
			log_message('info', 'Raw body length: ' . strlen($rawBody ?? ''));
			log_message('info', 'Input after parsing: ' . json_encode($input));
			log_message('info', 'Input type: ' . gettype($input));

			if (empty($input) || !is_array($input)) {
				log_message('error', 'Invalid input - empty or not array. input: ' . json_encode($input) . ', is_array: ' . var_export(is_array($input), true));
				return $this->response->setJSON(['success' => false, 'error' => 'No valid data received'], 400);
			}

			// Process each role
			foreach ($input as $roleId => $permissions) {
				$roleId = (int) $roleId;

				if ($roleId <= 0) {
					continue;
				}

				// Get role to check if super_admin
				$role = $db->table('roles')->where('role_id', $roleId)->get()->getRowArray();
				if (!$role) {
					continue;
				}

				// SKIP SUPER_ADMIN - it's managed by syncSuperAdminPermissions
				if ($role['role_name'] === 'super_admin') {
					log_message('info', 'Skipping super_admin role from edit (managed automatically)');
					continue;
				}

				// Ensure permissions is array
				if (!is_array($permissions)) {
					$permissions = [];
				}

				// Remove all existing permissions for this role
				$db->table('role_permissions')->where('role_id', $roleId)->delete();

				// Add new permissions
				foreach ($permissions as $permKey) {
					$db->table('role_permissions')->insert([
						'role_id' => $roleId,
						'permission_key' => $permKey,
						'created_at' => date('Y-m-d H:i:s'),
					]);
				}

				// Log the change
				$auditLog = model('AuditLogModel');
				$auditLog->log(
					'permissions_updated',
					'role',
					$roleId,
					null,
					['permissions' => $permissions],
					$adminId
				);
			}

			$message = 'Role permissions updated';
			if ($synced > 0) {
				$message .= " (synced $synced new permissions to super_admin)";
			}

			return $this->response->setJSON(['success' => true, 'message' => $message], 200);
		} catch (\Exception $e) {
			log_message('error', 'saveRolePerms error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
			return $this->response->setJSON(['success' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
		}
	}
}
