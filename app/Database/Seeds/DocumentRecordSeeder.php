<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\FileCodeGenerator;
use App\Models\FolderModel;
use App\Models\CategoryModel;
use App\Models\LocationModel;

class DocumentRecordSeeder extends Seeder
{
    protected $db;

    public function run()
    {
        $this->db = \Config\Database::connect();
        
        // Get existing categories and locations
        $categories = $this->getCategories();
        $locations = $this->getLocations();
        
        // Company names for realistic data
        $companies = [
            'Tech Solutions Inc', 'Global Manufacturing Co', 'Digital Services Ltd',
            'Energy Corporation', 'Construction Partners', 'Finance Group',
            'Healthcare Systems', 'Education Foundation', 'Transport Logistics',
            'Retail Chain Corp', 'Food Processing Inc', 'Chemical Industries',
            'Mining Operations', 'Telecom Networks', 'Power Generation',
            'Water Management', 'Waste Management', 'Environmental Services',
            'Infrastructure Development', 'Urban Planning', 'Agricultural Co-op'
        ];
        
        // Document types
        $documentTypes = [
            'PERMITS', 'ECC / CNC FILES', 'IEE / EIS FILES'
        ];
        
        // Generate 100 document records
        for ($i = 1; $i <= 100; $i++) {
            $companyName = $companies[($i - 1) % count($companies)];
            $folderType = $documentTypes[($i - 1) % count($documentTypes)];
            $category = $categories[($i - 1) % count($categories)];
            $location = $locations[($i - 1) % count($locations)];
            
            // Generate file code with proper prefix
            $prefix = $this->getPrefixFromType($folderType);
            $fileCode = FileCodeGenerator::generate($prefix, $i);
            $locationCode = FileCodeGenerator::generateLocationCode($location['rack'], $location['shelf']);
            
            // Random status
            $statuses = ['Available', 'Borrowed', 'Archived', 'Disposed'];
            $status = $statuses[($i - 1) % count($statuses)];
            
            // Random dates
            $createdDate = date('Y-m-d H:i:s', strtotime("-" . rand(1, 365) . " days"));
            $borrowedDate = ($status === 'Borrowed') ? date('Y-m-d H:i:s', strtotime("-" . rand(1, 30) . " days")) : null;
            $dueDate = ($status === 'Borrowed') ? date('Y-m-d H:i:s', strtotime("+30 days")) : null;
            
            $folderData = [
                'file_code' => $fileCode,
                'location_code' => $locationCode,
                'company_name' => $companyName,
                'folder_type' => $folderType,
                'category_id' => $category['category_id'],
                'status' => $status,
                'location_id' => $location['location_id'],
                'borrowed_date' => $borrowedDate,
                'due_date' => $dueDate,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $createdDate,
                'updated_at' => $createdDate,
            ];
            
            $this->insertFolder($folderData);
        }
        
        echo "Successfully seeded 100 document records with rack locations and company information.\n";
    }
    
    private function getCategories(): array
    {
        $categories = [];
        $categoryNames = FolderModel::FOLDER_CATEGORIES;
        
        foreach ($categoryNames as $categoryName) {
            $existing = $this->db->table('categories')
                ->where('category_name', $categoryName)
                ->get()
                ->getRowArray();
                
            if ($existing) {
                $categories[] = $existing;
            } else {
                $this->db->table('categories')->insert([
                    'category_name' => $categoryName,
                    'created_by' => 1,
                    'updated_by' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $categoryId = $this->db->insertID();
                $categories[] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName
                ];
            }
        }
        
        return $categories;
    }
    
    private function getLocations(): array
    {
        $locations = [];
        
        // Create 20 different rack/shelf combinations
        for ($rack = 1; $rack <= 5; $rack++) {
            for ($shelf = 'A'; $shelf <= 'D'; $shelf++) {
                $existing = $this->db->table('locations')
                    ->where('rack', $rack)
                    ->where('shelf', $shelf)
                    ->get()
                    ->getRowArray();
                    
                if ($existing) {
                    $locations[] = $existing;
                } else {
                    $this->db->table('locations')->insert([
                        'rack' => $rack,
                        'shelf' => $shelf,
                        'capacity' => 50,
                        'current_count' => 0,
                        'is_archive_location' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $locationId = $this->db->insertID();
                    $locations[] = [
                        'location_id' => $locationId,
                        'rack' => $rack,
                        'shelf' => $shelf
                    ];
                }
            }
        }
        
        return $locations;
    }
    
    private function getPrefixFromType(string $folderType): string
    {
        switch ($folderType) {
            case 'PERMITS':
                return 'PM';
            case 'ECC / CNC FILES':
                return 'EC';
            case 'IEE / EIS FILES':
                return 'IE';
            default:
                return 'DR';
        }
    }
    
    private function insertFolder(array $folderData): void
    {
        $existing = $this->db->table('folders')
            ->where('file_code', $folderData['file_code'])
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('folders')
                ->where('folder_id', $existing['folder_id'])
                ->update($folderData);
            return;
        }

        $this->db->table('folders')->insert($folderData);
    }
}
