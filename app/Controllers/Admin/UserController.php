<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PermissionService;
use App\Libraries\EmailService;
use App\Models\RoleModel;
use App\Models\UserModel;

class UserController extends BaseController
{
	protected $userModel;
	protected $roleModel;
	protected $permissionService;

	public function __construct()
	{
		$this->userModel = model(UserModel::class);
		$this->roleModel = model(RoleModel::class);
		$this->permissionService = service('permissionService');
	}

	public function index()
	{
		if (!can('manage_users')) {
			return redirect()->to('/dashboard')->with('error', 'Permission denied');
		}

        // Ensure role permissions are synced with config defaults
        $this->permissionService->syncAllRolePermissions();

		$db = \Config\Database::connect();
		$search = trim((string) $this->request->getGet('q'));
		$statusFilter = trim((string) $this->request->getGet('status'));
		$limit = trim((string) $this->request->getGet('limit'));

		$builder = $db->table('users u')
			->select("u.user_id, u.username, u.first_name, u.last_name, u.email, u.role, COALESCE(r.role_name, u.role) AS role_name, COALESCE(u.status, 'Active') AS status, u.inactive_at, u.created_at, u.updated_at")
			->join('user_roles ur', 'ur.user_id = u.user_id', 'left')
			->join('roles r', 'r.role_id = ur.role_id', 'left');

		if ($search !== '') {
			$builder->groupStart()
				->like('u.username', $search)
				->orLike('u.email', $search)
				->orLike('u.first_name', $search)
				->orLike('u.last_name', $search)
				->orLike('u.role', $search)
				->orLike('r.role_name', $search)
				->groupEnd();
		}

		if (in_array($statusFilter, ['Active', 'Inactive', 'Pending'], true)) {
			$builder->where('u.status', $statusFilter);
		}

		$users = $builder
			->orderBy('u.created_at', 'DESC')
			->orderBy('u.user_id', 'DESC')
			->get()
			->getResultArray();

		if ($limit !== '' && $limit !== 'all') {
			$users = array_slice($users, 0, max(1, (int) $limit));
		}

		$roles = $this->roleModel->orderBy('role_id', 'ASC')->findAll();

		$stats = [
			'total' => $db->table('users')->countAllResults(),
			'active' => $db->table('users')->where('status', 'Active')->countAllResults(),
			'inactive' => $db->table('users')->where('status', 'Inactive')->countAllResults(),
			'pending' => $db->table('users')->where('status', 'Pending')->countAllResults(),
		];

		return view('users/index', [
			'title' => 'User Management',
			'users' => $users,
			'roles' => $roles,
			'filters' => [
				'q' => $search,
				'status' => $statusFilter,
				'limit' => $limit === '' ? '25' : $limit,
			],
			'stats' => $stats,
		]);
	}

	public function create()
	{
		if (!can('manage_users')) {
			return redirect()->to('/dashboard')->with('error', 'Permission denied');
		}

		return view('users/create', [
			'title' => 'Add User',
			'roles' => $this->roleModel->orderBy('role_id', 'ASC')->findAll(),
		]);
	}

	public function store()
	{
		if (!can('manage_users')) {
			return redirect()->back()->with('error', 'Permission denied');
		}

		$rules = [
			'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
			'first_name' => 'permit_empty|max_length[100]',
			'last_name' => 'permit_empty|max_length[100]',
			'email' => 'required|valid_email|is_unique[users.email]',
			'role_id' => 'required|integer',
		];

		if (! $this->validate($rules, [
			'username' => [
				'is_unique' => 'The username you entered is already registered in the system.',
			],
			'email' => [
				'is_unique' => 'The email address you entered already exists in our records. Please use a different one.',
			]
		])) {
			return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
		}

		$roleId = (int) $this->request->getPost('role_id');
		$role = $this->roleModel->find($roleId);
		if (! $role) {
			return redirect()->back()->withInput()->with('errors', ['role_id' => 'Role not found']);
		}

		$rawRoleName = strtolower($role['role_name']);
		$roleNameMap = [
			'super_admin' => 'SuperAdmin',
			'admin' => 'Admin',
			'records_officer' => 'RecordsOfficer',
		];
        
        $systemRole = $roleNameMap[$rawRoleName] ?? $role['role_name'];

        // Generate random initial password (OTP)
        $otp = bin2hex(random_bytes(4)); // 8 character code

		$data = [
			'username' => trim((string) $this->request->getPost('username')),
			'first_name' => trim((string) $this->request->getPost('first_name')) ?: null,
			'last_name' => trim((string) $this->request->getPost('last_name')) ?: null,
			'email' => trim((string) $this->request->getPost('email')),
			'password' => $otp,
			'role' => $systemRole,
			'status' => 'Pending',
		];

		if (! $this->userModel->save($data)) {
			return redirect()->back()->withInput()->with('errors', $this->userModel->errors() ?: ['general' => 'Failed to save user record.']);
		}

		$userId = $this->userModel->getInsertID();
        $assignedBy = session()->get('user_id');
		$this->permissionService->assignRole($userId, $roleId, $assignedBy);

        // Send Invitation Email
        $emailService = new EmailService();
        $emailData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'otp' => $otp,
            'role' => $systemRole,
            'url' => base_url('setup-password/' . $userId)
        ];

        $subject = 'Welcome to EMB-RMS - Account Activation';
        $emailSent = $emailService->sendFromTemplate($data['email'], $subject, 'emails/user-invitation', $emailData);

		$message = $emailSent 
            ? 'User created successfully. Invitation email sent with activation code.' 
            : 'User created successfully, but invitation email failed to send. Please check SMTP settings.';

		return redirect()->to('/users')->with($emailSent ? 'success' : 'warning', $message);
	}

    /**
     * Display the password setup form for new users (Pending status)
     */
    public function setupPassword(int $userId)
    {
        $user = $this->userModel->find($userId);
        
        if (!$user || $user['status'] !== 'Pending') {
            return redirect()->to('/')->with('error', 'Invalid or expired activation link.');
        }

        return view('users/setup_password', [
            'title' => 'Activate Your Account',
            'user' => $user
        ]);
    }

    /**
     * Handle the final account setup and password creation
     */
    public function completeSetup(int $userId)
    {
        $user = $this->userModel->find($userId);
        
        if (!$user || $user['status'] !== 'Pending') {
            return redirect()->to('/')->with('error', 'Invalid or expired activation link.');
        }

        $rules = [
            'otp' => 'required',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Verify the temporary password (OTP) using password_verify since it's hashed in the DB
        if (!password_verify((string)$this->request->getPost('otp'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'The activation code (OTP) you entered is incorrect.');
        }

        // Update user: Set new password and change status to Active
        $updateData = [
            'password' => (string) $this->request->getPost('password'),
            'status' => 'Active'
        ];

        // We use skipValidation(false) but since we already validated in controller, 
        // the model will hash the password via its callback.
        if (!$this->userModel->update($userId, $updateData)) {
            return view('users/activation_result', [
                'title' => 'Activation Failed',
                'success' => false,
                'message' => 'We encountered a technical issue while activating your account. Please contact your administrator.'
            ]);
        }

        return view('users/activation_result', [
            'title' => 'Account Activated',
            'success' => true,
            'message' => 'Congratulations! Your account has been successfully activated. You can now log in to the system.'
        ]);
    }

    public function confirmStatus(int $userId)
    {
        if (!can('manage_users')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $user = $this->userModel->find($userId);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $newStatus = $this->request->getGet('status');
        if (!in_array($newStatus, ['Active', 'Inactive', 'Pending'], true)) {
            return redirect()->back()->with('error', 'Invalid status');
        }

        return view('users/confirm_status', [
            'title' => 'Verify Identity',
            'user' => $user,
            'newStatus' => $newStatus
        ]);
    }

	public function updateStatus(int $userId)
	{
		if (!can('manage_users')) {
			return redirect()->back()->with('error', 'Permission denied');
		}

        $adminPassword = (string) $this->request->getPost('admin_password');
        $adminId = session()->get('user_id');
        $adminUser = $this->userModel->find($adminId);

        if (!$adminUser || !password_verify($adminPassword, $adminUser['password'])) {
            return redirect()->back()->with('error', 'Identity verification failed. Invalid password.');
        }

		$user = $this->userModel->find($userId);
		if (! $user) {
			return redirect()->back()->with('error', 'User not found');
		}

		$status = $this->request->getPost('status');
		if (!in_array($status, ['Active', 'Inactive', 'Pending'], true)) {
			return redirect()->back()->with('error', 'Invalid status');
		}

		$updateData = [
			'status' => $status,
		];

		if ($status === 'Inactive') {
			$updateData['inactive_at'] = trim((string) ($user['inactive_at'] ?? '')) ?: date('Y-m-d H:i:s');
		} else {
			$updateData['inactive_at'] = null;
		}

		if (! $this->userModel->skipValidation()->update($userId, $updateData)) {
			return redirect()->back()->with('error', 'Failed to update user status');
		}

		if ((int) ($user['user_id'] ?? 0) === (int) (auth_user()['user_id'] ?? 0) && $status === 'Inactive') {
			service('authentication')->logout();
			return redirect()->to('/')->with('success', 'Your account was set to inactive');
		}

		return redirect()->to('/users')->with('success', 'User status updated successfully');
	}
	/**
	 * User detail page: account information plus what this user has done
	 * over the last 30 days.
	 *
	 * Note this is activity the user *performed*. audit_logs.user_id records
	 * the actor, and there is no column recording who an action was performed
	 * on, so "changes made to this account" cannot be shown yet.
	 */
	public function show(int $userId)
	{
		if (!can('manage_users')) {
			return redirect()->to('/dashboard')->with('error', 'Permission denied');
		}

		$db = \Config\Database::connect();

		$user = $db->table('users u')
			->select("u.user_id, u.username, u.first_name, u.last_name, u.email, u.role,
				COALESCE(r.role_name, u.role) AS role_name, COALESCE(u.status, 'Active') AS status,
				u.inactive_at, u.created_at, u.updated_at", false)
			->join('user_roles ur', 'ur.user_id = u.user_id', 'left')
			->join('roles r', 'r.role_id = ur.role_id', 'left')
			->where('u.user_id', $userId)
			->get()
			->getRowArray();

		if (! $user) {
			return redirect()->to('/users')->with('error', 'User not found.');
		}

		$since = date('Y-m-d H:i:s', strtotime('-30 days'));
		$activity = [];
		$activityByDay = [];
		$actionMix = [];

		if ($db->tableExists('audit_logs')) {
			$activity = $db->table('audit_logs')
				->where('user_id', $userId)
				->where('created_at >=', $since)
				->orderBy('created_at', 'DESC')
				->limit(100)
				->get()
				->getResultArray();

			$actionMix = $db->table('audit_logs')
				->select('action, entity_type, COUNT(*) AS total', false)
				->where('user_id', $userId)
				->where('created_at >=', $since)
				->groupBy('action, entity_type')
				->orderBy('total', 'DESC')
				->limit(8)
				->get()
				->getResultArray();

			$dayRows = $db->table('audit_logs')
				->select('DATE(created_at) AS day, COUNT(*) AS total', false)
				->where('user_id', $userId)
				->where('created_at >=', $since)
				->groupBy('day')
				->get()
				->getResultArray();

			$counts = [];
			foreach ($dayRows as $row) {
				$counts[$row['day']] = (int) $row['total'];
			}

			// Fill the gaps so the sparkline covers all 30 days.
			for ($i = 29; $i >= 0; $i--) {
				$day = date('Y-m-d', strtotime("-{$i} days"));
				$activityByDay[] = [
					'day' => $day,
					'label' => date('M j', strtotime($day)),
					'total' => $counts[$day] ?? 0,
				];
			}
		}

		return view('users/show', [
			'title' => 'User Details',
			'user' => $user,
			'permissions' => $this->permissionService->userPermissions($userId),
			'activity' => $activity,
			'activityByDay' => $activityByDay,
			'actionMix' => $actionMix,
			'activityTotal' => count($activity),
			'since' => $since,
		]);
	}

	public function updateInfo(int $userId)
	{
		if (!can('manage_users')) {
			return redirect()->back()->with('error', 'Permission denied');
		}

		$user = $this->userModel->find($userId);
		if (!$user) {
			return redirect()->back()->with('error', 'User not found');
		}

		$firstName = trim((string) $this->request->getPost('first_name'));
		$lastName  = trim((string) $this->request->getPost('last_name'));
		$email     = trim((string) $this->request->getPost('email'));
		$status    = trim((string) $this->request->getPost('status'));

		// Validate email
		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return redirect()->back()->with('error', 'Please provide a valid email address.');
		}

		// Check email uniqueness (exclude current user)
		$existing = $this->userModel->where('email', $email)->where('user_id !=', $userId)->first();
		if ($existing) {
			return redirect()->back()->with('error', 'This email address is already used by another account.');
		}

		if (!in_array($status, ['Active', 'Inactive'], true)) {
			return redirect()->back()->with('error', 'Invalid status value.');
		}

		$updateData = [
			'first_name' => $firstName ?: null,
			'last_name'  => $lastName ?: null,
			'email'      => $email,
			'status'     => $status,
		];

		if ($status === 'Inactive') {
			$updateData['inactive_at'] = trim((string) ($user['inactive_at'] ?? '')) ?: date('Y-m-d H:i:s');
		} else {
			$updateData['inactive_at'] = null;
		}

		if (!$this->userModel->skipValidation()->update($userId, $updateData)) {
			return redirect()->back()->with('error', 'Failed to update user information.');
		}

		// If admin deactivated themselves
		if ((int) $user['user_id'] === (int) (auth_user()['user_id'] ?? 0) && $status === 'Inactive') {
			service('authentication')->logout();
			return redirect()->to('/')->with('success', 'Your account was set to inactive.');
		}

		return redirect()->to('/users')->with('success', 'User information updated successfully.');
	}

}