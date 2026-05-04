<?php

namespace App\Models;

use CodeIgniter\Model;

class RestorationRequestModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'restoration_requests';
    protected $primaryKey       = 'restoration_request_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folder_id',
        'archive_id',
        'status',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'folder_id'      => 'required|integer',
        'archive_id'     => 'permit_empty|integer',
        'status'         => 'required|in_list[Pending,Approved,Rejected]',
        'requested_by'   => 'required|integer',
        'approved_by'    => 'permit_empty|integer',
        'requested_at'   => 'required|valid_date[Y-m-d H:i:s]',
        'approved_at'    => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'notes'          => 'permit_empty|max_length[1000]',
    ];

    public function pendingForFolder(int $folderId): ?array
    {
        $request = $this->where('folder_id', $folderId)
            ->where('status', 'Pending')
            ->orderBy('restoration_request_id', 'DESC')
            ->first();

        return $request ?: null;
    }
}
