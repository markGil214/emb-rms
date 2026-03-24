<?php

namespace App\Models;

use CodeIgniter\Model;

class DisposalRecordModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'disposal_records';
    protected $primaryKey       = 'disposal_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'archive_id', 'disposal_date', 'disposal_method',
        'compliance_reference', 'approved_by'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = false;

    // Validation rules
    protected $validationRules = [
        'archive_id'        => 'required|integer',
        'disposal_date'     => 'required|valid_date',
        'disposal_method'   => 'required|in_list[Destruction,Recycling,Transfer,Donation,Return]',
        'approved_by'       => 'required|integer',
        'compliance_reference' => 'permit_empty',
    ];

    protected $validationMessages = [
        'archive_id' => [
            'required' => 'Archive record is required',
        ],
        'disposal_date' => [
            'required' => 'Disposal date is required',
            'valid_date' => 'Please enter a valid date',
        ],
        'disposal_method' => [
            'required' => 'Disposal method is required',
            'in_list' => 'Invalid disposal method selected',
        ],
        'approved_by' => [
            'required' => 'Approver must be specified',
        ],
    ];

    /**
     * Get all disposals (most recent first)
     */
    public function getAllDisposals()
    {
        return $this->orderBy('disposal_date', 'DESC')->findAll();
    }

    /**
     * Get appr disposal records by archive
     */
    public function getByArchive($archiveId)
    {
        return $this->where('archive_id', $archiveId)->first();
    }

    /**
     * Get disposals by method
     */
    public function getByMethod($method)
    {
        return $this->where('disposal_method', $method)
                    ->orderBy('disposal_date', 'DESC')
                    ->findAll();
    }

    /**
     * Approve disposal request
     */
    public function approvDisposal($disposalId, $approvedBy)
    {
        return $this->update($disposalId, [
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Complete disposal
     */
    public function completeDisposal($disposalId)
    {
        return $this->update($disposalId, [
            'disposal_date' => date('Y-m-d'),
        ]);
    }
}
