<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class ShelfMapApiController extends BaseController
{
    /**
     * Get all shelf locations with folder occupancy data
     * Calculates occupancy by location and returns structured area data
     */
    public function getShelfData()
    {
        try {
            $db = \Config\Database::connect();   
            
            // Get all folders with direct query
            $folderResult = $db->query("SELECT folder_id, file_code, company_name, status, location_id FROM folders");
            $folders = $folderResult->getResultArray();

            // Get all locations - select only columns that exist
            $locResult = $db->query("SELECT location_id, rack, shelf, capacity, current_count, is_archive_location, coordinates_3d FROM locations");
            $locations = $locResult->getResultArray();

            if (empty($locations)) {
                return $this->response->setJSON([
                    'status' => 'warning',
                    'message' => 'No locations found in database',
                    'data' => [
                        'areas' => [],
                        'shelves' => [],
                        'locations' => []
                    ]
                ]);
            }

            // Group folders by location to calculate occupancy. Folders still
            // awaiting creation approval (or declined outright) have not been
            // accepted onto a shelf, so they don't occupy space.
            $locationOccupancy = [];
            foreach ($folders as $folder) {
                $locId = (int)($folder['location_id'] ?? 0);
                if ($locId === 0) continue;

                if (in_array((string) ($folder['status'] ?? ''), \App\Models\RackShelfModel::NON_OCCUPYING_STATUSES, true)) {
                    continue;
                }

                if (!isset($locationOccupancy[$locId])) {
                    $locationOccupancy[$locId] = [
                        'occupied' => 0,
                        'documents' => []
                    ];
                }
                $locationOccupancy[$locId]['occupied']++;
                if (!empty($folder['file_code'])) {
                    $locationOccupancy[$locId]['documents'][] = [
                        'folder_id' => (int) ($folder['folder_id'] ?? 0),
                        'file_code' => (string) $folder['file_code'],
                        'company_name' => (string) ($folder['company_name'] ?? ''),
                    ];
                }
            }

            // Build areas and shelves arrays
            $areas = [];
            $shelves = [];

            foreach ($locations as $loc) {
                $locId = (int)$loc['location_id'];
                $occupancy = $locationOccupancy[$locId] ?? ['occupied' => 0, 'documents' => []];
                $occupied = (int)$occupancy['occupied'];
                $capacity = (int)($loc['capacity'] ?? 100);

                // Determine status based on occupancy percentage. A shelf with
                // no capacity set cannot take folders at all, so it is treated
                // as full rather than reported as healthy.
                $percentage = ($capacity > 0) ? ($occupied / $capacity) * 100 : 100;
                if ($percentage >= 90) {
                    $status = 'Critical';
                    $color = 'red';
                } elseif ($percentage >= 75) {
                    $status = 'High';
                    $color = 'yellow';
                } else {
                    $status = 'Good';
                    $color = 'green';
                }

                // Use hardcoded coordinates for click detection
                $coordinateMap = [
                    1 => ['x1' => 44, 'y1' => 44, 'x2' => 65, 'y2' => 90],
                    2 => ['x1' => 44, 'y1' => 44, 'x2' => 65, 'y2' => 90],
                    3 => ['x1' => 44, 'y1' => 44, 'x2' => 65, 'y2' => 90],
                    4 => ['x1' => 52, 'y1' => 52, 'x2' => 65, 'y2' => 90],
                    5 => ['x1' => 52, 'y1' => 93, 'x2' => 65, 'y2' => 131],
                    6 => ['x1' => 60, 'y1' => 60, 'x2' => 65, 'y2' => 90],
                    7 => ['x1' => 52, 'y1' => 134, 'x2' => 65, 'y2' => 172],
                ];
                $bounds = $coordinateMap[$locId] ?? ['x1' => 0, 'y1' => 0, 'x2' => 100, 'y2' => 100];

                $locationName = $this->buildLocationName($loc);

                // Build area object
                $area = [
                    'id' => 'shelf-' . $locId,
                    'name' => $locationName,
                    'description' => sprintf(
                        '%s - Contains %d documents - %s occupancy',
                        $locationName,
                        $occupied,
                        $status
                    ),
                    'bounds' => $bounds,
                    'data' => [
                        'capacity' => $capacity,
                        'occupied' => $occupied,
                        'documents' => array_slice($occupancy['documents'], 0, 10),
                        'status' => $status,
                        'color' => $color,
                        'location_id' => $locId,
                        'cabinet' => $loc['rack'] ?? '',
                        'rack' => $loc['shelf'] ?? ''
                    ]
                ];

                $areas[] = $area;

                // Build shelf object
                $shelves[] = [
                    'id' => 'loc-' . $locId,
                    'name' => $locationName,
                    'capacity' => $capacity,
                    'occupied' => $occupied,
                    'documents' => $occupancy['documents']
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'areas' => $areas,
                    'shelves' => $shelves,
                    'locations' => $locations,
                    'totalFolders' => count($folders),
                    'totalLocations' => count($locations)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'ShelfMapApi::getShelfData Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to fetch shelf data',
                'error' => ENVIRONMENT !== 'production' ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Get folders by location ID
     */
    public function getFoldersByLocation($locationId)
    {
        try {
            $db = \Config\Database::connect();
            
            $result = $db->query(
                "SELECT folder_id, file_code, company_name, status, location_id FROM folders WHERE location_id = ?",
                [(int)$locationId]
            );
            $folders = $result->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $folders
            ]);

        } catch (\Exception $e) {
            log_message('error', 'getFoldersByLocation Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to fetch folders'
            ]);
        }
    }

    /**
     * Search folders by file code, company name, or status
     */
    public function searchFolders()
    {
        try {
            $query = $this->request->getGet('q');
            
            if (empty($query)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => []
                ]);
            }

            $db = \Config\Database::connect();
            $searchTerm = '%' . $db->escapeLikeString($query) . '%';
            
            $result = $db->query(
                "SELECT folder_id, file_code, company_name, status, location_id FROM folders 
                 WHERE file_code LIKE ? OR company_name LIKE ? OR status LIKE ?
                 LIMIT 50",
                [$searchTerm, $searchTerm, $searchTerm]
            );
            $folders = $result->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $folders
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to search folders'
            ]);
        }
    }

    /**
     * Helper: Build location name from cabinet and rack
     */
    private function buildLocationName($location)
    {
        $parts = [];
        if (!empty($location['rack'])) $parts[] = 'Rack ' . $location['rack'];
        if (!empty($location['shelf'])) $parts[] = 'Shelf ' . $location['shelf'];

        return !empty($parts) ? implode(' - ', $parts) : 'Location #' . $location['location_id'];
    }
}
