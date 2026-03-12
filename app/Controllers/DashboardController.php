<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
	/**
	 * Display dashboard for authenticated users
	 */
	public function index()
	{
		$db = \Config\Database::connect();
		
		$data = [
			'title' => 'Dashboard',
			'user' => auth_user(),
			'stats' => [
				'totalUsers' => $db->table('users')->countAll(),
				'totalFolders' => $db->table('folders')->countAll(),
				'totalRecords' => $db->table('archive_records')->countAll(),
				'pendingRequests' => $db->table('document_requests')->where('status', 'Pending')->countAllResults(),
			],
			'recentUsers' => $db->table('users')
				->select('username, email, role, created_at')
				->orderBy('created_at', 'DESC')
				->limit(10)
				->get()
				->getResultArray(),
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
