<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\FileCodeGenerator;
use App\Models\FolderModel;

class FolderSeeder extends Seeder
{
    protected $db;

    public function run()
    {
        $this->db = \Config\Database::connect();

        $categoryIds = $this->syncCategories(FolderModel::FOLDER_CATEGORIES);
        
        // 1️⃣ Seed locations dynamically
        $locations = [
            ['rack' => 1, 'shelf' => 'A'],
            ['rack' => 1, 'shelf' => 'B'],
            ['rack' => 1, 'shelf' => 'C'],
            ['rack' => 2, 'shelf' => 'A'],
            ['rack' => 2, 'shelf' => 'B'],
            ['rack' => 3, 'shelf' => 'A'],
        ];

        $locationIds = $this->syncLocations($locations);

        // 2️⃣ Prepare folder data dynamically
        $sampleFolders = [
            [
                'prefix' => 'FI',
                'company_name' => 'Five Star Inc',
                'folder_type' => 'PERMITS',
                'category_name' => 'Solid Waste Management System Files',
                'borrowed_date' => null,
                'due_date' => null,
                'status' => 'Available',
                'location_index' => 0,
            ],
            [
                'prefix' => 'FI',
                'company_name' => 'First Call Services',
                'folder_type' => 'ECC / CNC FILES',
                'category_name' => 'Mining Companies',
                'borrowed_date' => null,
                'due_date' => null,
                'status' => 'Available',
                'location_index' => 1,
            ],
            [
                'prefix' => 'HR',
                'company_name' => 'HR Records 2024',
                'folder_type' => 'PERMITS',
                'category_name' => 'Hydropower Plants',
                'borrowed_date' => null,
                'due_date' => null,
                'status' => 'Available',
                'location_index' => 2,
            ],
            [
                'prefix' => 'OP',
                'company_name' => 'Operations Archive',
                'folder_type' => 'IEE / EIS FILES',
                'category_name' => 'Telecommunications',
                'borrowed_date' => null,
                'due_date' => null,
                'status' => 'Available',
                'location_index' => 3,
            ],
            [
                'prefix' => 'AR',
                'company_name' => 'Archived Records 2020',
                'folder_type' => 'ECC / CNC FILES',
                'category_name' => 'Road Projects',
                'borrowed_date' => null,
                'due_date' => null,
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
            $locationCode = FileCodeGenerator::generateLocationCode($location['rack'], $location['shelf']);

            $folderData = [
                'file_code' => $fileCode,
                'location_code' => $locationCode,
                'company_name' => $folder['company_name'],
                'folder_type' => $folder['folder_type'],
                'category_id' => $categoryIds[$folder['category_name']],
                'borrowed_date' => $folder['borrowed_date'],
                'due_date' => $folder['due_date'],
                'status' => $folder['status'],
                'location_id' => $location['location_id'],
                'created_by' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->syncFolder($folderData);
        }
    }

    private function syncCategories(array $categoryNames): array
    {
        $results = [];

        foreach ($categoryNames as $categoryName) {
            $existing = $this->db->table('categories')
                ->where('category_name', $categoryName)
                ->get()
                ->getRowArray();

            if ($existing) {
                $results[$categoryName] = (int) $existing['category_id'];
                continue;
            }

            $this->db->table('categories')->insert([
                'category_name' => $categoryName,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $results[$categoryName] = (int) $this->db->insertID();
        }

        return $results;
    }

    private function syncLocations(array $locations): array
    {
        $results = [];

        foreach ($locations as $location) {
            $existing = $this->db->table('locations')
                ->where('rack', $location['rack'])
                ->where('shelf', $location['shelf'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $this->db->table('locations')
                    ->where('location_id', $existing['location_id'])
                    ->update([
                        'rack' => $location['rack'],
                        'shelf' => $location['shelf'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                $results[] = [
                    'location_id' => $existing['location_id'],
                    'rack' => $location['rack'],
                    'shelf' => $location['shelf'],
                ];
                continue;
            }

            $insertData = [
                'rack' => $location['rack'],
                'shelf' => $location['shelf'],
                'capacity' => 0,
                'current_count' => 0,
                'is_archive_location' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->table('locations')->insert($insertData);

            $results[] = [
                'location_id' => $this->db->insertID(),
                'rack' => $location['rack'],
                'shelf' => $location['shelf'],
            ];
        }

        return $results;
    }

    private function syncFolder(array $folderData): void
    {
        $existing = $this->db->table('folders')
            ->where('file_code', $folderData['file_code'])
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('folders')
                ->where('folder_id', $existing['folder_id'])
                ->update([
                    'location_code' => $folderData['location_code'],
                    'company_name' => $folderData['company_name'],
                    'folder_type' => $folderData['folder_type'],
                    'category_id' => $folderData['category_id'],
                    'borrowed_date' => $folderData['borrowed_date'],
                    'due_date' => $folderData['due_date'],
                    'status' => $folderData['status'],
                    'location_id' => $folderData['location_id'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return;
        }

        $folderData['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('folders')->insert($folderData);
    }
}