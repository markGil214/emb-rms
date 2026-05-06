<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\EmailService;
use App\Models\UserModel;

class LoginController extends BaseController
{
	private const RESET_SESSION_KEY = 'password_reset';
	private const RESET_CODE_TTL_SECONDS = 900; // 15 minutes

	/**
	 * Display login page
	 */
	public function index()
	{
		return view('login');
	}

	/**
	 * Authenticate user credentials
	 * Validates input and attempts login via Authentication service
	 */
	public function authenticate()
	{
		$username = trim((string) $this->request->getPost('username'));
		$password = (string) $this->request->getPost('password');

		// Validate input
		if (!$username || !$password) {
			return redirect()->back()->with('error', 'Username and password are required');
		}

		// Attempt login via Authentication service
		$auth = service('authentication');
		if ($auth->login($username, $password)) {
			return redirect()->to('/dashboard');
		}

		return redirect()->back()->with('error', 'Invalid username or password');
	}

	/**
	 * Logout current user
	 */
	public function logout()
	{
		$auth = service('authentication');
		if ($auth->logout()) {
			return redirect()->to('/');
		}

		return redirect()->back()->with('error', 'Logout failed');
	}

	/**
	 * Display forgot password page.
	 */
	public function forgotPassword()
	{
		return view('auth/forgot_password');
	}

	/**
	 * Generate and email reset code to user.
	 */
	public function sendResetCode()
	{
		$email = trim((string) $this->request->getPost('email'));

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return redirect()->back()->withInput()->with('error', 'Please enter a valid email address.');
		}

		$userModel = new UserModel();
		$user = $userModel->where('email', $email)->first();

		// Always return a generic success message to avoid user enumeration.
		$genericMessage = 'If an account exists for this email, a reset code has been sent.';
		if (!$user) {
			return redirect()->to(url_to('password.reset'))->with('success', $genericMessage);
		}

		$code = strtoupper(bin2hex(random_bytes(3))); // 6 chars
		$expiresAt = time() + self::RESET_CODE_TTL_SECONDS;

		session()->set(self::RESET_SESSION_KEY, [
			'user_id' => (int) $user['user_id'],
			'email' => strtolower($email),
			'code_hash' => password_hash($code, PASSWORD_DEFAULT),
			'expires_at' => $expiresAt,
		]);

		$emailService = new EmailService();
		$emailSent = $emailService->sendFromTemplate(
			$email,
			'EMB-RMS Password Reset Code',
			'emails/password-reset-code',
			[
				'username' => $user['username'] ?? 'User',
				'code' => $code,
				'expiresInMinutes' => (int) (self::RESET_CODE_TTL_SECONDS / 60),
			]
		);

		if (!$emailSent) {
			return redirect()->back()->withInput()->with('error', 'Unable to send reset code right now. Please try again later.');
		}

		return redirect()->to(url_to('password.reset'))->with('success', $genericMessage);
	}

	/**
	 * Display reset password page.
	 */
	public function resetPassword()
	{
		return view('auth/reset_password');
	}

	/**
	 * Verify reset code and update password.
	 */
	public function updatePassword()
	{
		$email = strtolower(trim((string) $this->request->getPost('email')));
		$code = strtoupper(trim((string) $this->request->getPost('code')));
		$password = (string) $this->request->getPost('password');
		$confirmPassword = (string) $this->request->getPost('confirm_password');

		$rules = [
			'email' => 'required|valid_email',
			'code' => 'required|min_length[6]|max_length[8]',
			'password' => 'required|min_length[8]',
			'confirm_password' => 'required|matches[password]',
		];

		if (!$this->validate($rules)) {
			return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
		}

		$resetData = session()->get(self::RESET_SESSION_KEY);
		if (!$resetData || !is_array($resetData)) {
			return redirect()->back()->withInput()->with('error', 'Reset session expired. Please request a new code.');
		}

		if (($resetData['email'] ?? '') !== $email) {
			return redirect()->back()->withInput()->with('error', 'Email does not match the code request.');
		}

		if ((int) ($resetData['expires_at'] ?? 0) < time()) {
			session()->remove(self::RESET_SESSION_KEY);
			return redirect()->back()->withInput()->with('error', 'Reset code expired. Please request a new code.');
		}

		if (!password_verify($code, (string) ($resetData['code_hash'] ?? ''))) {
			return redirect()->back()->withInput()->with('error', 'Invalid reset code.');
		}

		$userModel = new UserModel();
		$userId = (int) ($resetData['user_id'] ?? 0);
		if ($userId <= 0 || !$userModel->find($userId)) {
			session()->remove(self::RESET_SESSION_KEY);
			return redirect()->back()->withInput()->with('error', 'Invalid reset request. Please try again.');
		}

		$updated = $userModel->skipValidation()->update($userId, ['password' => $password]);
		if (!$updated) {
			return redirect()->back()->withInput()->with('error', 'Failed to update password. Please try again.');
		}

		session()->remove(self::RESET_SESSION_KEY);
		return redirect()->to('/')->with('success', 'Password updated successfully. You can now log in.');
	}
}
