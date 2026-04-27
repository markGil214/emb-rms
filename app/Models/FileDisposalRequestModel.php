<?php

namespace App\Models;

use CodeIgniter\Model;

class FileDisposalRequestModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'file_disposal_requests';
    protected $primaryKey       = 'disposal_request_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'file_id',
        'status',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'disposed_at',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'file_id'       => 'required|integer',
        'status'        => 'required|in_list[Pending,Approved,Rejected,Disposed]',
        'requested_by'  => 'required|integer',
        'approved_by'   => 'permit_empty|integer',
        'requested_at'  => 'required|valid_date[Y-m-d H:i:s]',
        'approved_at'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'disposed_at'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'notes'         => 'permit_empty|max_length[1000]',
    ];

    public function latestByFile(int $fileId): ?array
    {
        $record = $this->where('file_id', $fileId)
            ->orderBy('disposal_request_id', 'DESC')
            ->first();

        return $record ?: null;
    }
}
