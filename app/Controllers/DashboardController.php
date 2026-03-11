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

		return view('layouts/superadmin/dashboard', $data);
	}

	/**
	 * Display shelf map and search page
	 */
	public function shelfmap()
	{
		$data = [
			'title' => 'Shelf Map & Search',
			'user' => auth_user(),
		];

		return view('layouts/superadmin/shelfmap', $data);
	}
}
