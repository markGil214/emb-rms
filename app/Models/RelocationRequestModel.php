<?php

namespace App\Models;

use CodeIgniter\Model;

class RelocationRequestModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'relocation_requests';
    protected $primaryKey       = 'relocation_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folder_id', 'from_location_id', 'to_location_id',
        'reason', 'status', 'requested_by', 'approved_by',
        'requested_at', 'approved_at', 'rejection_reason'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = false;

    // Validation rules
    protected $validationRules = [
        'folder_id'         => 'required|integer',
        'to_location_id'    => 'required|integer',
        'reason'            => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'folder_id' => [
            'required' => 'Folder is required',
            'integer' => 'Invalid folder selection',
        ],
        'to_location_id' => [
            'required' => 'New location is required',
            'integer' => 'Invalid location selection',
        ],
        'reason' => [
            'max_length' => 'Reason cannot exceed 500 characters',
        ],
    ];

    /**
     * Get all pending relocation requests
     */
    public function getPending()
    {
        return $this->where('status', 'Pending')
                    ->orderBy('requested_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get all approved but not started relocations
     */
    public function getApproved()
    {
        return $this->where('status', 'Approved')
                    ->orderBy('approved_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get all in-progress relocations
     */
    public function getInProgress()
    {
        return $this->where('status', 'In Progress')
                    ->orderBy('approved_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get relocation history for a folder
     */
    public function getFolderHistory($folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('requested_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get paginated relocation requests with joined folder and location data.
     */
    public function getPaginatedRelocations(int $perPage = 25, string $group = 'relocations')
    {
        return $this->select('relocation_requests.*, f.file_code, f.company_name, fl.rack, fl.shelf, tl.rack as to_rack, tl.shelf as to_shelf')
            ->join('folders as f', 'f.folder_id = relocation_requests.folder_id', 'left')
            ->join('locations as fl', 'fl.location_id = relocation_requests.from_location_id', 'left')
            ->join('locations as tl', 'tl.location_id = relocation_requests.to_location_id', 'left')
            // Newest requested/completed activity should appear first.
            ->orderBy("GREATEST(IFNULL(relocation_requests.requested_at, '1000-01-01 00:00:00'), IFNULL(relocation_requests.approved_at, '1000-01-01 00:00:00'), IFNULL(relocation_requests.updated_at, '1000-01-01 00:00:00'), IFNULL(relocation_requests.created_at, '1000-01-01 00:00:00'))", 'DESC', false)
            ->orderBy('relocation_requests.relocation_id', 'DESC')
            ->paginate($perPage, $group);
    }

    /**
     * Approve a relocation request
     */
    public function approveRelocation($relocationId, $approvedBy)
    {
        // Get the relocation request to get folder_id and to_location_id
        $relocation = $this->find($relocationId);
        if (!$relocation) {
            return false;
        }

        // Update the relocation request status
        $result = $this->update($relocationId, [
            'status' => 'Completed',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $approvedBy,
        ]);

        // Update the folder's location to the new location
        if ($result) {
            $db = \Config\Database::connect();
            $db->table('folders')->where('folder_id', $relocation['folder_id'])->update([
                'location_id' => $relocation['to_location_id'],
            ]);
        }

        return $result;
    }

    /**
     * Start relocation in progress
     */
    public function startRelocation($relocationId)
    {
        return $this->update($relocationId, [
            'status' => 'In Progress',
        ]);
    }

    /**
     * Complete relocation
     */
    public function completeRelocation($relocationId)
    {
        return $this->update($relocationId, [
            'status' => 'Completed',
            'completed_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Cancel relocation
     */
    public function cancelRelocation($relocationId)
    {
        return $this->update($relocationId, [
            'status' => 'Cancelled',
        ]);
    }
}
