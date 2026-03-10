<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Authentication;

class LoginController extends BaseController
{
	protected $auth;

	public function __construct()
	{
		$this->auth = new Authentication();
	}
	public function index()
	{
		return view('login');
	}


	public function authenticate()
	{
		$username = $this->request->getPost('username');
		$password = $this->request->getPost('password');

		if(!$username || !$password) {
			return redirect()->back()->with('error', 'username and password ar required');
		}

		if($this->auth->login($username, $password)) {
			return redirect()->to('/dashboard');
		} else {
			return redirect()->back()->with('error', 'Invalid username or password');
		}

	}
}
