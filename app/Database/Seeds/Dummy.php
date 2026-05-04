<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Dummy extends Seeder
{
	public function run()
	{
		$db = \Config\Database::connect();

		$user = $db->table('users')->select('user_id')->orderBy('user_id', 'ASC')->get(1)->getRowArray();
		$category = $db->table('categories')->select('category_id')->orderBy('category_id', 'ASC')->get(1)->getRowArray();
		$location = $db->table('locations')->select('location_id, rack, shelf')->orderBy('location_id', 'ASC')->get(1)->getRowArray();

		if (!$user || !$category || !$location) {
			return;
		}

		$seedFileCode = 'DM-ARCHIVE-001';
		$seedFileName = 'expired-disposal-seed.pdf';
		$seedCompanyName = 'Dummy Archive Disposal Demo';
		$now = date('Y-m-d H:i:s');
		$archiveDate = date('Y-m-d', strtotime('-90 days'));
		$locationCode = sprintf('R%s-S%s', (string) ($location['rack'] ?? '1'), (string) ($location['shelf'] ?? 'A'));

		$seedFolder = $db->table('folders')
			->where('file_code', $seedFileCode)
			->get()
			->getRowArray();

		$folderData = [
			'file_code' => $seedFileCode,
			'location_code' => $locationCode,
			'company_name' => $seedCompanyName,
			'folder_type' => 'PERMITS',
			'category_id' => (int) $category['category_id'],
			'borrowed_date' => null,
			'due_date' => null,
			'status' => 'Archived',
			'location_id' => (int) $location['location_id'],
			'created_by' => (int) $user['user_id'],
			'updated_at' => $now,
		];

		if ($seedFolder) {
			$db->table('folders')
				->where('folder_id', (int) $seedFolder['folder_id'])
				->update($folderData);
			$seedFolderId = (int) $seedFolder['folder_id'];
		} else {
			$folderData['created_at'] = $now;
			$db->table('folders')->insert($folderData);
			$seedFolderId = (int) $db->insertID();
		}

		// Prevent duplicate seed rows on repeated runs.
		$db->table('folder_files')
			->where('folder_id', $seedFolderId)
			->where('file_name', $seedFileName)
			->delete();

		$db->table('folder_files')->insert([
			'folder_id' => $seedFolderId,
			'file_name' => $seedFileName,
			'file_path' => 'uploads/folders/seed/expired-disposal-seed.pdf',
			'file_size' => 102400,
			'uploaded_by' => (int) $user['user_id'],
			'retention_type' => 'expiration',
			'expiration_date' => '2026-03-20',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);

		$db->table('folders')
			->where('folder_id', $seedFolderId)
			->update([
				'status' => 'Archived',
				'updated_at' => $now,
			]);

		$archive = $db->table('archive_records')
			->where('folder_id', $seedFolderId)
			->get()
			->getRowArray();

		$archiveData = [
			'folder_id' => $seedFolderId,
			'archived_date' => $archiveDate,
			'archive_location_id' => (int) $location['location_id'],
			'archived_by' => (int) $user['user_id'],
			'updated_at' => $now,
		];

		if ($archive) {
			$db->table('archive_records')
				->where('archive_id', (int) $archive['archive_id'])
				->update($archiveData);
			$archiveId = (int) $archive['archive_id'];
		} else {
			$archiveData['created_at'] = $now;
			$db->table('archive_records')->insert($archiveData);
			$archiveId = (int) $db->insertID();
		}

		$disposalFields = $db->getFieldNames('disposal_records');
		$hasRequestedBy = in_array('requested_by', $disposalFields, true);
		$disposalData = [
			'archive_id' => $archiveId,
			'disposal_date' => null,
			'disposal_method' => 'Destruction',
			'approved_by' => null,
			'compliance_reference' => 'COMP-2026-DUMMY',
			'created_at' => $now,
		];

		if ($hasRequestedBy) {
			$disposalData['requested_by'] = (int) $user['user_id'];
		}

		$existingDisposal = $db->table('disposal_records')
			->where('archive_id', $archiveId)
			->get()
			->getRowArray();

		if ($existingDisposal) {
			$db->table('disposal_records')
				->where('disposal_id', (int) $existingDisposal['disposal_id'])
				->update($disposalData);
		} else {
			$db->table('disposal_records')->insert($disposalData);
		}
	}
}
