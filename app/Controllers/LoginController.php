<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
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
		$username = $this->request->getPost('username');
		$password = $this->request->getPost('password');

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
}
