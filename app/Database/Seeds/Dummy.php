<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Dummy extends Seeder
{
	public function run()
	{
		$db = \Config\Database::connect();

		$folder = $db->table('folders')->select('folder_id')->orderBy('folder_id', 'ASC')->get(1)->getRowArray();
		$user = $db->table('users')->select('user_id')->orderBy('user_id', 'ASC')->get(1)->getRowArray();

		if (!$folder || !$user) {
			return;
		}

		$seedFileName = 'expired-disposal-seed.pdf';

		// Prevent duplicate seed rows on repeated runs.
		$db->table('folder_files')->where('file_name', $seedFileName)->delete();

		$db->table('folder_files')->insert([
			'folder_id' => (int) $folder['folder_id'],
			'file_name' => $seedFileName,
			'file_path' => 'uploads/folders/seed/expired-disposal-seed.pdf',
			'file_size' => 102400,
			'uploaded_by' => (int) $user['user_id'],
			'retention_type' => 'expiration',
			'expiration_date' => '2026-03-20',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}
}
