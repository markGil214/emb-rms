<?php

namespace App\Models;

use CodeIgniter\Model;

class ArchiveRecordModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'archive_records';
    protected $primaryKey       = 'archive_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folder_id', 'archived_date', 'archive_location_id',
        'retention_status', 'retention_expiry_date', 'retention_policy_reference',
        'archived_by'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = false;

    // Validation rules
    protected $validationRules = [
        'folder_id'                      => 'required|integer',
        'archived_date'                  => 'required|valid_date',
        'archive_location_id'            => 'required|integer',
        'retention_status'               => 'in_list[Active,Inactive,Expired]',
        'retention_expiry_date'          => 'valid_date|permit_empty',
        'retention_policy_reference'     => 'permit_empty',
        'archived_by'                    => 'required|integer',
    ];

    protected $validationMessages = [
        'folder_id' => [
            'required' => 'Folder is required',
        ],
        'archived_date' => [
            'required' => 'Archive date is required',
            'valid_date' => 'Please enter a valid date',
        ],
        'archive_location_id' => [
            'required' => 'Archive location is required',
        ],
        'archived_by' => [
            'required' => 'Archived by user is required',
        ],
    ];

    /**
     * Get all archived folders
     */
    public function getAllArchived()
    {
        return $this->orderBy('archive_date', 'DESC')->findAll();
    }

    /**
     * Get archive history for a folder
     */
    public function getFolderArchive($folderId)
    {
        return $this->where('folder_id', $folderId)->first();
    }

    /**
     * Search archived records
     */
    public function search($query)
    {
        return $this->join('folders', 'folders.folder_id = archive_records.folder_id')
                    ->like('folders.file_code', $query)
                    ->orLike('folders.company_name', $query)
                    ->select('archive_records.*')
                    ->findAll();
    }

    /**
     * Get records by storage box
     */
    public function getByLocation($locationId)
    {
        return $this->where('archive_location_id', $locationId)
                    ->orderBy('archived_date', 'ASC')
                    ->findAll();
    }
}
