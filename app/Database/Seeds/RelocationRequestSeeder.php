<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RelocationRequestSeeder extends Seeder
{
    public function run()
    {
        // Check if relocation requests already exist
        $existing = $this->db->table('relocation_requests')->get()->getNumRows();
        if ($existing > 0) {
            echo "ℹ️  Relocation requests already seeded. Skipping RelocationRequestSeeder.\n";
            return;
        }

        // Get sample users, folders, and locations
        $users = $this->db->table('users')->get()->getResultArray();
        $folders = $this->db->table('folders')->limit(5)->get()->getResultArray();
        $locations = $this->db->table('locations')->get()->getResultArray();

        if (empty($users) || empty($folders) || empty($locations)) {
            echo "⚠️  Skipping RelocationRequestSeeder: Requires users, folders, and locations to be seeded first.\n";
            return;
        }

        $requester = $users[2] ?? $users[0]; // Records officer
        $approver = $users[1] ?? $users[0]; // Admin

        if (count($locations) < 2) {
            echo "⚠️  Skipping RelocationRequestSeeder: Need at least 2 locations.\n";
            return;
        }

        $requests = [];
        
        // Pending relocation request
        $requests[] = [
            'folder_id' => $folders[0]['folder_id'],
            'from_location_id' => $locations[0]['location_id'],
            'to_location_id' => $locations[1]['location_id'],
            'requested_by' => $requester['user_id'],
            'requested_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'reason' => 'Consolidation of related files in secure cabinet',
            'status' => 'Pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Approved relocation request
        $requests[] = [
            'folder_id' => $folders[1]['folder_id'],
            'from_location_id' => $locations[1]['location_id'],
            'to_location_id' => $locations[2]['location_id'],
            'requested_by' => $requester['user_id'],
            'requested_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            'reason' => 'Archive consolidation - high-frequency access documents',
            'status' => 'Approved',
            'approved_by' => $approver['user_id'],
            'approved_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'rejection_reason' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
        ];

        // In-progress relocation request
        $requests[] = [
            'folder_id' => $folders[2]['folder_id'],
            'from_location_id' => $locations[2]['location_id'],
            'to_location_id' => $locations[0]['location_id'],
            'requested_by' => $requester['user_id'],
            'requested_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'reason' => 'Climate controlled storage requirement',
            'status' => 'In Progress',
            'approved_by' => $approver['user_id'],
            'approved_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            'rejection_reason' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ];

        // Completed relocation request
        $requests[] = [
            'folder_id' => $folders[3]['folder_id'],
            'from_location_id' => $locations[0]['location_id'],
            'to_location_id' => $locations[1]['location_id'],
            'requested_by' => $requester['user_id'],
            'requested_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'reason' => 'Optimization - improved access efficiency',
            'status' => 'Completed',
            'approved_by' => $approver['user_id'],
            'approved_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
            'rejection_reason' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
        ];

        $this->db->table('relocation_requests')->insertBatch($requests);
    }
}
