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
     * Get all archived folders (joined with folder metadata)
     */
    public function getAllArchived()
    {
        $db = \Config\Database::connect();
        return $db->table('folders as f')
                  ->select('f.folder_id, f.file_code, f.company_name, f.folder_type, f.category_id, c.category_name, ar.archive_id, ar.archived_date, ar.archive_location_id, ar.archived_by, l.rack, l.shelf')
                  ->join('categories as c', 'c.category_id = f.category_id', 'left')
                  ->join('archive_records as ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = f.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false)
                  ->join('locations as l', 'l.location_id = ar.archive_location_id', 'left')
                  ->where('f.status', 'Archived')
                  ->orderBy('ar.archived_date', 'DESC')
                  ->orderBy('f.folder_id', 'ASC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get archive history for a folder
     */
    public function getFolderArchive($folderId)
    {
        $db = \Config\Database::connect();
        return $db->table('archive_records')
                  ->where('folder_id', $folderId)
                  ->orderBy('archive_id', 'DESC')
                  ->get()
                  ->getRowArray();
    }

    /**
     * Search archived records/folders by file code or company name
     */
    public function search($query)
    {
        $db = \Config\Database::connect();
        return $db->table('folders as f')
                  ->select('f.folder_id, f.file_code, f.company_name, f.folder_type, f.category_id, c.category_name, ar.archive_id, ar.archived_date, ar.archive_location_id, ar.archived_by, l.rack, l.shelf')
                  ->join('categories as c', 'c.category_id = f.category_id', 'left')
                                    ->join('archive_records as ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = f.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false)
                  ->join('locations as l', 'l.location_id = ar.archive_location_id', 'left')
                  ->where('f.status', 'Archived')
                  ->groupStart()
                    ->like('f.file_code', $query)
                    ->orLike('f.company_name', $query)
                  ->groupEnd()
                  ->orderBy('ar.archived_date', 'DESC')
                  ->orderBy('f.folder_id', 'ASC')
                  ->get()
                  ->getResultArray();
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
