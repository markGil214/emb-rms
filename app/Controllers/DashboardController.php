<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
	/**
	 * Display dashboard for authenticated users
	 */
	public function index()
	{
		$data = [
			'title' => 'Dashboard',
			'user' => auth_user(),
		];

		return view('dashboard', $data);
	}
}
