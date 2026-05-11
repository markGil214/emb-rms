<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * FileDisposalRequestModel
 */
class FileDisposalRequestModel extends Model
{
    protected $table            = 'file_disposal_requests';
    protected $primaryKey       = 'disposal_request_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'file_id', 'status', 'requested_by', 'approved_by', 
        'requested_at', 'approved_at', 'disposed_at', 'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get the latest disposal request for a specific file.
     */
    public function latestByFile(int $fileId)
    {
        return $this->where('file_id', $fileId)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
}
