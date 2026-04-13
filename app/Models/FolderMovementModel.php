<?php

namespace App\Models;

use CodeIgniter\Model;

class FolderMovementModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'folder_movements';
    protected $primaryKey = 'movement_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'folder_id', 'from_location_id', 'to_location_id',
        'from_building', 'to_building',
        'from_room', 'to_room',
        'from_cabinet', 'to_cabinet',
        'from_shelf', 'to_shelf',
        'relocation_request_id', 'moved_by', 'moved_at', 'reason',
        // Enterprise enhancements
        'from_location_label', 'to_location_label',
        'confirmed_from_building', 'confirmed_to_building',
        'approved_by', 'approved_at', 'completed_by', 'completed_at',
        'movement_code',
        'folder_status_at_start', 'folder_status_at_completion', 'status_conflict_detected'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'folder_id' => 'required|integer',
        'moved_by' => 'required|integer',
        'moved_at' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'folder_id' => [
            'required' => 'Folder is required',
        ],
        'moved_by' => [
            'required' => 'User who moved is required',
        ],
        'moved_at' => [
            'required' => 'Movement date is required',
            'valid_date' => 'Please enter a valid date',
        ],
    ];

    /**
     * Get all movements for a folder
     */
    public function getFolderMovements(int $folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('moved_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get latest movement for a folder
     */
    public function getLatestMovement(int $folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('moved_at', 'DESC')
                    ->first();
    }

    /**
     * Get movements between two dates
     */
    public function getMovementsBetweenDates(int $folderId, string $startDate, string $endDate)
    {
        return $this->where('folder_id', $folderId)
                    ->where('moved_at >=', $startDate)
                    ->where('moved_at <=', $endDate)
                    ->orderBy('moved_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get all movements by a specific user
     */
    public function getMovementsByUser(int $userId)
    {
        return $this->where('moved_by', $userId)
                    ->orderBy('moved_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get movements for a relocation request
     */
    public function getByRelocationRequest(int $relocationRequestId)
    {
        return $this->where('relocation_request_id', $relocationRequestId)
                    ->findAll();
    }

    /**
     * Record a movement
     */
    public function recordMovement(array $data)
    {
        return $this->save($data);
    }

    /**
     * ENTERPRISE: Start movement (transition to In-Transit state)
     * 
     * Sets folder to in-transit, generates movement code, captures status
     * Called when relocation approval happens
     */
    public function startMovement(int $folderId, int $relocationRequestId, array $locationData)
    {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Generate unique movement code
            $movementCode = $this->generateMovementCode();
            
            // Get folder current state
            $folderModel = new FolderModel();
            $folder = $folderModel->find($folderId);
            $currentLocation = $db->table('locations')
                ->where('location_id', $folder['location_id'])
                ->get()
                ->getRowArray();

            // Create movement record with location snapshots
            $movementData = [
                'folder_id' => $folderId,
                'relocation_request_id' => $relocationRequestId,
                'from_location_id' => $folder['location_id'],
                'to_location_id' => $locationData['to_location_id'] ?? null,
                'from_building' => $currentLocation['building'] ?? null,
                'from_room' => $currentLocation['room'] ?? null,
                'from_cabinet' => $currentLocation['rack'] ?? null,
                'from_shelf' => $currentLocation['shelf'] ?? null,
                'from_location_label' => $this->buildLocationLabel([
                    'building' => $currentLocation['building'] ?? null,
                    'room' => $currentLocation['room'] ?? null,
                    'cabinet' => $currentLocation['rack'] ?? null,
                    'shelf' => $currentLocation['shelf'] ?? null,
                ]),
                'movement_code' => $movementCode,
                'folder_status_at_start' => $folder['status'],
                'approved_by' => auth_user()['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s'),
                'status_conflict_detected' => false,
            ];
            
            // Save movement record
            if (!$this->save($movementData)) {
                throw new \Exception('Failed to create movement record');
            }
            
            $movementId = $this->insertID();
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed during movement start');
            }
            
            return $movementId;
            
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * ENTERPRISE: Complete movement (transition from In-Transit)
     * 
     * Finalizes movement, updates folder location, captures completion user
     * Called when relocation completes
     */
    public function completeMovement(
        int $folderId,
        int $movementId,
        int $toLocationId,
        array $updatedLocationFields = []
    ) {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Get movement and folder
            $movement = $this->find($movementId);
            $folderModel = new FolderModel();
            $folder = $folderModel->find($folderId);
            
            // Check for race conditions (status changed)
            $statusConflict = ($folder['status'] !== $movement['folder_status_at_start']);
            
            // Update location label snapshot
            $toLabel = $this->buildLocationLabel($updatedLocationFields);
            
            // Update movement record
            $this->update($movementId, [
                'to_building' => $updatedLocationFields['building'] ?? null,
                'to_room' => $updatedLocationFields['room'] ?? null,
                'to_cabinet' => $updatedLocationFields['cabinet'] ?? null,
                'to_shelf' => $updatedLocationFields['shelf'] ?? null,
                'to_location_label' => $toLabel,
                'confirmed_to_building' => $updatedLocationFields['building'] ?? null,
                'completed_by' => auth_user()['user_id'] ?? null,
                'completed_at' => date('Y-m-d H:i:s'),
                'folder_status_at_completion' => $folder['status'],
                'status_conflict_detected' => $statusConflict,
            ]);
            
            // Update folder: new location only
            $folderModel->update($folderId, [
                'location_id' => $toLocationId,
            ]);
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed during movement completion');
            }
            
            return true;
            
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Get active (in-progress) movement for a folder
     */
    public function getActiveMovement(int $folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->where('completed_at', null)
                    ->where('completed_by', null)
                    ->first();
    }

    /**
     * Generate unique movement code (MOV-YYYY-XXXXXX)
     */
    public function generateMovementCode(): string
    {
        $year = date('Y');
        $count = $this->where('YEAR(created_at)', $year)->countAllResults() + 1;
        return sprintf('MOV-%s-%06d', $year, $count);
    }

    /**
     * Build location label from location fields
     * Example: "Building A > Room 201 > Cabinet 4 > Shelf B"
     */
    private function buildLocationLabel(array $locationFields): string
    {
        $parts = [];
        
        if (!empty($locationFields['building'])) {
            $parts[] = $locationFields['building'];
        }
        if (!empty($locationFields['room'])) {
            $parts[] = $locationFields['room'];
        }
        if (!empty($locationFields['cabinet'])) {
            $parts[] = $locationFields['cabinet'];
        }
        if (!empty($locationFields['shelf'])) {
            $parts[] = $locationFields['shelf'];
        }
        if (!empty($locationFields['box'])) {
            $parts[] = $locationFields['box'];
        }
        
        return implode(' > ', $parts) ?: 'Unknown Location';
    }

    /**
     * Get all movements with conflict markers
     */
    public function getConflictedMovements()
    {
        return $this->where('status_conflict_detected', true)
                    ->orderBy('completed_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get audit trail for a folder
     */
    public function getAuditTrail(int $folderId)
    {
        return $this->select(
            'movement_id, movement_code, approved_by, approved_at, 
             completed_by, completed_at, status_conflict_detected,
             from_location_label, to_location_label'
        )
        ->where('folder_id', $folderId)
        ->orderBy('movement_id', 'DESC')
        ->findAll();
    }
}
