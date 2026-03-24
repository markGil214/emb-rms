<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\FileCodeGenerator;

class FolderSeeder extends Seeder
{
    public function run()
    {
        // Check if folders already exist
        $existingFolders = $this->db->table('folders')->get()->getNumRows();
        if ($existingFolders > 0) {
            echo "ℹ️  Folders already seeded. Skipping FolderSeeder.\n";
            return;
        }

        // 1️⃣ Seed locations dynamically
        $locations = [
            ['cabinet' => 1, 'rack' => 'A'],
            ['cabinet' => 1, 'rack' => 'B'],
            ['cabinet' => 1, 'rack' => 'C'],
            ['cabinet' => 2, 'rack' => 'A'],
            ['cabinet' => 2, 'rack' => 'B'],
            ['cabinet' => 3, 'rack' => 'A'],
        ];

        $this->db->table('locations')->insertBatch($locations);

        // Get inserted location IDs
        $locationIds = $this->db->table('locations')
            ->select('location_id, cabinet, rack')
            ->get()
            ->getResultArray();

        // 2️⃣ Prepare folder data dynamically
        $sampleFolders = [
            [
                'prefix' => 'FI',
                'company_name' => 'Five Star Inc',
                'issuance_date' => '2025-01-01',
                'expiry_date' => '2030-12-31',
                'status' => 'Available',
                'location_index' => 0,
            ],
            [
                'prefix' => 'FI',
                'company_name' => 'First Call Services',
                'issuance_date' => '2025-02-01',
                'expiry_date' => '2030-12-31',
                'status' => 'Available',
                'location_index' => 1,
            ],
            [
                'prefix' => 'HR',
                'company_name' => 'HR Records 2024',
                'issuance_date' => '2024-01-01',
                'expiry_date' => null,
                'status' => 'Available',
                'location_index' => 2,
            ],
            [
                'prefix' => 'OP',
                'company_name' => 'Operations Archive',
                'issuance_date' => '2025-01-15',
                'expiry_date' => null,
                'status' => 'Available',
                'location_index' => 3,
            ],
            [
                'prefix' => 'AR',
                'company_name' => 'Archived Records 2020',
                'issuance_date' => '2020-01-01',
                'expiry_date' => '2025-12-31',
                'status' => 'Archived',
                'location_index' => 5,
            ],
        ];

        $folders = [];
        $prefixCounters = []; // Track sequence per prefix

        foreach ($sampleFolders as $folder) {
            $location = $locationIds[$folder['location_index']];
            $prefix = $folder['prefix'];
            
            // Increment counter for this prefix
            if (!isset($prefixCounters[$prefix])) {
                $prefixCounters[$prefix] = 1;
            } else {
                $prefixCounters[$prefix]++;
            }
            
            // Generate file code with correct sequence
            $fileCode = FileCodeGenerator::generate($prefix, $prefixCounters[$prefix]);
            $locationCode = FileCodeGenerator::generateLocationCode($location['cabinet'], $location['rack']);

            $folders[] = [
                'file_code' => $fileCode,
                'location_code' => $locationCode,
                'company_name' => $folder['company_name'],
                'issuance_date' => $folder['issuance_date'],
                'expiry_date' => $folder['expiry_date'],
                'status' => $folder['status'],
                'location_id' => $location['location_id'],
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // 3️⃣ Insert folders
        $this->db->table('folders')->insertBatch($folders);
    }
}