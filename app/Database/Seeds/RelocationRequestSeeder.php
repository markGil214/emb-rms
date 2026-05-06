<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RelocationRequestSeeder extends Seeder
{
    public function run()
    {
        // Get sample users, folders, and locations
        $users = $this->db->table('users')->get()->getResultArray();
        $folders = $this->db->table('folders')->orderBy('folder_id', 'ASC')->limit(5)->get()->getResultArray();
        $locations = $this->db->table('locations')->orderBy('location_id', 'ASC')->get()->getResultArray();

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
            'reason' => 'Consolidation of related files in secure storage area',
            'status' => 'Pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Completed relocation request
        $requests[] = [
            'folder_id' => $folders[1]['folder_id'],
            'from_location_id' => $locations[1]['location_id'],
            'to_location_id' => $locations[2]['location_id'],
            'requested_by' => $requester['user_id'],
            'requested_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            'reason' => 'Archive consolidation - high-frequency access documents',
            'status' => 'Completed',
            'approved_by' => $approver['user_id'],
            'approved_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'rejection_reason' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
        ];

        // Completed relocation request
        $requests[] = [
            'folder_id' => $folders[2]['folder_id'],
            'from_location_id' => $locations[2]['location_id'],
            'to_location_id' => $locations[0]['location_id'],
            'requested_by' => $requester['user_id'],
            'requested_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'reason' => 'Climate controlled storage requirement',
            'status' => 'Completed',
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

        // Declined relocation request
        if (count($folders) > 4) {
            $requests[] = [
                'folder_id' => $folders[4]['folder_id'],
                'from_location_id' => $locations[0]['location_id'],
                'to_location_id' => $locations[2]['location_id'],
                'requested_by' => $requester['user_id'],
                'requested_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'reason' => 'Insufficient storage space in target location',
                'status' => 'Declined',
                'approved_by' => $approver['user_id'],
                'approved_at' => null,
                'rejection_reason' => 'Target location does not have sufficient capacity',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ];
        }

        foreach ($requests as $request) {
            $existing = $this->db->table('relocation_requests')
                ->where('folder_id', $request['folder_id'])
                ->where('requested_at', $request['requested_at'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $this->db->table('relocation_requests')
                    ->where('relocation_id', $existing['relocation_id'])
                    ->update($request);
                continue;
            }

            $this->db->table('relocation_requests')->insert($request);
        }
    }
}
