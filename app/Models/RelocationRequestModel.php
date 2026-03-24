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
        'from_location_id'  => 'required|integer',
        'to_location_id'    => 'required|integer',
        'reason'            => 'required|min_length[10]|max_length[500]',
        'status'            => 'in_list[Pending,Approved,In Progress,Completed,Cancelled]',
        'requested_by'      => 'required|integer',
    ];

    protected $validationMessages = [
        'folder_id' => [
            'required' => 'Folder is required',
        ],
        'from_location_id' => [
            'required' => 'Current location is required',
        ],
        'to_location_id' => [
            'required' => 'New location is required',
        ],
        'reason' => [
            'required' => 'Reason for relocation is required',
            'min_length' => 'Reason must be at least 10 characters',
            'max_length' => 'Reason cannot exceed 500 characters',
        ],
    ];

    /**
     * Get all pending relocation requests
     */
    public function getPending()
    {
        return $this->where('status', 'Pending')
                    ->orderBy('requested_date', 'ASC')
                    ->findAll();
    }

    /**
     * Get all approved but not started relocations
     */
    public function getApproved()
    {
        return $this->where('status', 'Approved')
                    ->orderBy('approved_date', 'ASC')
                    ->findAll();
    }

    /**
     * Get all in-progress relocations
     */
    public function getInProgress()
    {
        return $this->where('status', 'In Progress')
                    ->orderBy('approved_date', 'ASC')
                    ->findAll();
    }

    /**
     * Get relocation history for a folder
     */
    public function getFolderHistory($folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('requested_date', 'DESC')
                    ->findAll();
    }

    /**
     * Approve a relocation request
     */
    public function approveRelocation($relocationId, $approvedBy)
    {
        return $this->update($relocationId, [
            'status' => 'Approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $approvedBy,
        ]);
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
