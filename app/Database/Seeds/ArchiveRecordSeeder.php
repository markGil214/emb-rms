<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ArchiveRecordSeeder extends Seeder
{
    public function run()
    {
        // Check if archive records already exist
        $existing = $this->db->table('archive_records')->get()->getNumRows();
        if ($existing > 0) {
            echo "ℹ️  Archive records already seeded. Skipping ArchiveRecordSeeder.\n";
            return;
        }

        // Get sample users, folders, and locations
        $users = $this->db->table('users')->get()->getResultArray();
        $folders = $this->db->table('folders')->limit(5)->get()->getResultArray();
        $locations = $this->db->table('locations')->limit(3)->get()->getResultArray();

        if (empty($users) || empty($folders) || empty($locations)) {
            echo "⚠️  Skipping ArchiveRecordSeeder: Requires users, folders, and locations to be seeded first.\n";
            return;
        }

        $archivist = $users[2] ?? $users[0]; // Records officer
        $admin = $users[1] ?? $users[0]; // Admin

        $archives = [];
        
        // Active archive record
        $archives[] = [
            'folder_id' => $folders[0]['folder_id'],
            'archived_date' => date('Y-m-d', strtotime('-90 days')),
            'archive_location_id' => $locations[0]['location_id'],
            'retention_status' => 'Active',
            'retention_expiry_date' => date('Y-m-d', strtotime('+270 days')), // 5 years from today
            'retention_policy_reference' => 'POL-RET-2024-001',
            'archived_by' => $archivist['user_id'],
            'created_at' => date('Y-m-d H:i:s', strtotime('-90 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Active archive record with longer retention
        $archives[] = [
            'folder_id' => $folders[1]['folder_id'],
            'archived_date' => date('Y-m-d', strtotime('-60 days')),
            'archive_location_id' => $locations[1]['location_id'],
            'retention_status' => 'Active',
            'retention_expiry_date' => date('Y-m-d', strtotime('+1800 days')), // 10 years
            'retention_policy_reference' => 'POL-RET-2024-002',
            'archived_by' => $admin['user_id'],
            'created_at' => date('Y-m-d H:i:s', strtotime('-60 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Archive record eligible for disposal (retention expired)
        $archives[] = [
            'folder_id' => $folders[2]['folder_id'],
            'archived_date' => date('Y-m-d', strtotime('-2000 days')), // 5+ years old
            'archive_location_id' => $locations[2]['location_id'],
            'retention_status' => 'Eligible for Disposal',
            'retention_expiry_date' => date('Y-m-d', strtotime('-100 days')), // Already expired
            'retention_policy_reference' => 'POL-RET-2024-001',
            'archived_by' => $archivist['user_id'],
            'created_at' => date('Y-m-d H:i:s', strtotime('-2000 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Inactive archive record (disposal already approved/executed)
        $archives[] = [
            'folder_id' => $folders[3]['folder_id'],
            'archived_date' => date('Y-m-d', strtotime('-2100 days')),
            'archive_location_id' => $locations[0]['location_id'],
            'retention_status' => 'Inactive',
            'retention_expiry_date' => date('Y-m-d', strtotime('-200 days')),
            'retention_policy_reference' => 'POL-RET-2024-001',
            'archived_by' => $admin['user_id'],
            'created_at' => date('Y-m-d H:i:s', strtotime('-2100 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('archive_records')->insertBatch($archives);
    }
}
