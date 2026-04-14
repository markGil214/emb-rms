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
        'archive_id'           => 'required|integer',
        'disposal_method'      => 'required|in_list[Destruction,Recycling,Transfer,Donation,Return]',
        'reason'               => 'required|max_length[1000]',
        'compliance_reference' => 'permit_empty|max_length[1000]',
        'disposal_date'        => 'permit_empty|valid_date[Y-m-d]',
        'approved_by'          => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'archive_id' => [
            'required' => 'Archive record is required',
            'integer' => 'Invalid archive record selected',
        ],
        'disposal_method' => [
            'required' => 'Disposal method is required',
            'in_list' => 'Invalid disposal method selected',
        ],
        'reason' => [
            'required' => 'Reason for disposal is required',
            'max_length' => 'Reason cannot exceed 1000 characters',
        ],
        'disposal_date' => [
            'valid_date' => 'Please enter a valid date',
        ],
        'approved_by' => [
            'integer' => 'Invalid approver selected',
        ],
    ];

    protected $lifecycleErrors = [];

    /**
     * Lifecycle validation guard for disposal records.
     */
    public function validateLifecycleState(array $data): bool
    {
        $this->lifecycleErrors = [];

        $status = $data['status'] ?? 'Pending';
        $disposalDate = $data['disposal_date'] ?? null;
        $approvedBy = $data['approved_by'] ?? null;

        if ($status === 'Pending') {
            if (!empty($disposalDate)) {
                $this->lifecycleErrors['disposal_date'] = 'Disposal date must be empty while request is pending.';
            }

            if (!empty($approvedBy)) {
                $this->lifecycleErrors['approved_by'] = 'Approver must be empty while request is pending.';
            }
        }

        if ($status === 'Approved') {
            if (empty($disposalDate)) {
                $this->lifecycleErrors['disposal_date'] = 'Disposal date is required when approving disposal.';
            }

            if (empty($approvedBy)) {
                $this->lifecycleErrors['approved_by'] = 'Approver is required when approving disposal.';
            }
        }

        return empty($this->lifecycleErrors);
    }

    public function getLifecycleErrors(): array
    {
        return $this->lifecycleErrors;
    }

    public function inferStatus(array $record): string
    {
        return (empty($record['disposal_date']) && empty($record['approved_by'])) ? 'Pending' : 'Approved';
    }

    /**
     * Get all disposals (most recent first)
     */
    public function getAllDisposals()
    {
        return $this->orderBy('disposal_date', 'DESC')->findAll();
    }

    /**
     * Get pending disposal requests.
     */
    public function getPending()
    {
        return $this->where('disposal_date', null)
                    ->where('approved_by', null)
                    ->findAll();
    }

    /**
     * Get approved/completed disposals.
     */
    public function getApproved()
    {
        return $this->where('disposal_date IS NOT NULL', null, false)
                    ->where('approved_by IS NOT NULL', null, false)
                    ->findAll();
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
