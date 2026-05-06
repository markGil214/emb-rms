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
     * Get archive records with disposal workflow and retention context.
     */
    public function getArchiveDisposalRecords(): array
    {
        $db = \Config\Database::connect();

        $disposalFields = $db->tableExists('disposal_records')
            ? $db->getFieldNames('disposal_records')
            : [];
        $hasRequestedBy = in_array('requested_by', $disposalFields, true);

        $select = [
            'f.folder_id',
            'f.file_code',
            'f.company_name',
            'f.folder_type',
            'f.status as folder_status',
            'f.created_by as folder_created_by',
            'f.updated_by as folder_updated_by',
            'c.category_name',
            'ar.archive_id',
            'ar.archived_date',
            'ar.archive_location_id',
            'ar.archived_by',
            'archiver.username as archived_by_username',
            'CASE WHEN archiver.first_name IS NOT NULL AND archiver.first_name != "" THEN CONCAT(archiver.first_name, " ", archiver.last_name) ELSE archiver.username END as archived_by_name',
            'folder_creator.username as folder_created_by_username',
            'CASE WHEN folder_creator.first_name IS NOT NULL AND folder_creator.first_name != "" THEN CONCAT(folder_creator.first_name, " ", folder_creator.last_name) ELSE folder_creator.username END as folder_created_by_name',
            'folder_updater.username as folder_updated_by_username',
            'CASE WHEN folder_updater.first_name IS NOT NULL AND folder_updater.first_name != "" THEN CONCAT(folder_updater.first_name, " ", folder_updater.last_name) ELSE folder_updater.username END as folder_updated_by_name',
            'l.rack',
            'l.shelf',
            'dr.disposal_id',
            'dr.disposal_date',
            'dr.disposal_method',
            'dr.compliance_reference',
            'dr.approved_by',
            'dr.created_at as disposal_requested_at',
            'CASE WHEN approver.first_name IS NOT NULL AND approver.first_name != "" THEN CONCAT(approver.first_name, " ", approver.last_name) ELSE approver.username END as approved_by_name',
            $hasRequestedBy ? 'dr.requested_by as disposal_requested_by' : 'NULL as disposal_requested_by',
            $hasRequestedBy ? 'CASE WHEN requester.first_name IS NOT NULL AND requester.first_name != "" THEN CONCAT(requester.first_name, " ", requester.last_name) ELSE requester.username END as requested_by_name' : 'NULL as requested_by_name',
        ];

        $builder = $db->table('folders as f')
            ->select(implode(', ', $select), false)
            ->join('categories as c', 'c.category_id = f.category_id', 'left')
            ->join('archive_records as ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = f.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false)
            ->join('locations as l', 'l.location_id = ar.archive_location_id', 'left')
            ->join('users as archiver', 'archiver.user_id = ar.archived_by', 'left')
            ->join('users as folder_creator', 'folder_creator.user_id = f.created_by', 'left')
            ->join('users as folder_updater', 'folder_updater.user_id = f.updated_by', 'left')
            ->join('disposal_records as dr', 'dr.disposal_id = (SELECT dr2.disposal_id FROM disposal_records dr2 WHERE dr2.archive_id = ar.archive_id ORDER BY dr2.created_at DESC, dr2.disposal_id DESC LIMIT 1)', 'left', false)
            ->join('users as approver', 'approver.user_id = dr.approved_by', 'left')
            ->whereIn('f.status', ['Archived', 'Disposed'])
            ->where('ar.archive_id IS NOT NULL', null, false);

        if ($hasRequestedBy) {
            $builder->join('users as requester', 'requester.user_id = dr.requested_by', 'left');
        }

        if (
            $db->tableExists('folder_files') &&
            in_array('retention_type', $db->getFieldNames('folder_files'), true) &&
            in_array('expiration_date', $db->getFieldNames('folder_files'), true)
        ) {
            $retentionSubquery = "(SELECT folder_id,
                    COUNT(*) as file_count,
                    SUM(CASE WHEN retention_type = 'expiration' THEN 1 ELSE 0 END) as expiring_file_count,
                    SUM(CASE WHEN retention_type = 'permanent' THEN 1 ELSE 0 END) as permanent_file_count,
                    MIN(CASE WHEN retention_type = 'expiration' THEN expiration_date ELSE NULL END) as earliest_expiration_date,
                    MAX(CASE WHEN retention_type = 'expiration' THEN expiration_date ELSE NULL END) as latest_expiration_date
                FROM folder_files
                GROUP BY folder_id) as retention_summary";

            $builder
                ->select('retention_summary.file_count, retention_summary.expiring_file_count, retention_summary.permanent_file_count, retention_summary.earliest_expiration_date, retention_summary.latest_expiration_date', false)
                ->join($retentionSubquery, 'retention_summary.folder_id = f.folder_id', 'left', false);
        } else {
            $builder->select('0 as file_count, 0 as expiring_file_count, 0 as permanent_file_count, NULL as earliest_expiration_date, NULL as latest_expiration_date', false);
        }

        return $builder
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
