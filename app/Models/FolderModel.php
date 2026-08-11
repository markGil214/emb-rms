<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\FileCodeGenerator;

class FolderModel extends Model
{
    //Folder Type Constants
    const FOLDER_TYPES = [
        'PERMITS',
        'ECC / CNC FILES',
        'IEE / EIS FILES'
    ];

    // Folder Category Constants
    const FOLDER_CATEGORIES = [
        'Solid Waste Management System Files',
        'Mining Companies',
        'Hydropower Plants',
        'Telecommunications',
        'CSAG / ISAG / Batching Plants Files',
        'Road Projects'
    ];

    protected $DBGroup          = 'default';
    protected $table            = 'folders';
    protected $primaryKey       = 'folder_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'file_code', 'company_name', 'folder_type', 'category_id',
        'location_code', 'status', 'location_id', 'created_by', 'updated_by',
        'borrowed_date', 'due_date'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useSoftDeletes = false;
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'file_code'      => 'required|max_length[50]|is_unique[folders.file_code,folder_id,{folder_id}]',
        'location_code'  => 'required|max_length[50]',
        'location_id'    => 'required|integer',
        'company_name'   => 'required|max_length[100]',
        'folder_type'    => 'required|in_list[PERMITS,ECC / CNC FILES,IEE / EIS FILES]|max_length[50]',
        'category_id'    => 'required|integer|is_not_unique[categories.category_id]',
        'status'         => 'in_list[Available,Borrowed,Archived,Disposed,Pending,Pending Update,Pending Archive,Declined]',
        'borrowed_date'  => 'permit_empty|valid_date',
        'due_date'       => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'file_code' => [
            'required'  => 'File code is required',
            'is_unique' => 'This file code already exists',
            'max_length'=> 'File code cannot exceed 50 characters',
        ],
        'location_code' => [
            'required'  => 'Location code is required',
            'max_length'=> 'Location code cannot exceed 50 characters',
        ],
        'company_name' => [
            'required' => 'Company name is required',
        ],
        'folder_type' => [
            'required'       => 'Folder type is required',
            'in_list'        => 'Please select a valid folder type.',
            'max_length'     => 'Folder type cannot exceed 50 characters.',
        ],
        'category_id' => [
            'required' => 'Folder category is required',
            'is_not_unique' => 'Please select a valid category.',
        ],
        'location_id' => [
            'required' => 'Location is required',
        ],
        'borrowed_date' => [
            'valid_date' => 'Please provide a valid borrowed date.',
        ],
        'due_date' => [
            'valid_date' => 'Please provide a valid due date.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['setDefaults', 'generateFileCode'];
    protected $beforeUpdate         = ['setDefaults'];

    /**
     * Set default values before insert/update
     */
    protected function setDefaults(array $data)
    {
        if (empty($data['data']['status'])) {
            $data['data']['status'] = 'Available';
        }

        return $data;
    }

    /**
     * Automatically generate file code if not provided
     */
    protected function generateFileCode(array $data)
    {
        if (empty($data['data']['file_code']) && !empty($data['data']['company_name'])) {
            $data['data']['file_code'] = FileCodeGenerator::getNextFromCompany($data['data']['company_name']);
        }

        return $data;
    }

    /**
     * Join with locations table
     */
    public function withLocation($folderId)
    {
        return $this->select('folders.*, locations.cabinet, locations.shelf, locations.rack')
                    ->join('locations', 'folders.location_id = locations.location_id')
                    ->where('folder_id', $folderId)
                    ->first();
    }

    /**
     * Get folders by location code (chainable)
     */
    public function byLocationCode($locationCode)
    {
        return $this->where('location_code', $locationCode);
    }

    /**
     * Get folders by file code prefix (chainable)
     */
    public function byPrefix($prefix)
    {
        return $this->like('file_code', strtoupper($prefix) . '-%');
    }

    /**
     * Get folders by status (chainable)
     */
    public function byStatus($status)
    {
        return $this->where('status', $status);
    }

    /**
     * Get folders by folder type (chainable)
     */
    public function byFolderType($folderType)
    {
        return $this->where('folder_type', $folderType);
    }

    /**
     * Get paginated document records with optional filters.
     */
    public function getPaginatedDocumentRecords(array $filters = [], int $perPage = 25, string $group = 'folders')
    {
        $builder = $this->select('folders.*, categories.category_name AS folder_category, locations.rack AS cabinet, locations.shelf AS shelf, locations.rack AS rack, bt.borrowed_at AS borrowed_date, bt.expected_return_date AS due_date, bt.actual_return_date AS return_date, ar.archived_date')
            ->join('locations', 'locations.location_id = folders.location_id', 'left')
            ->join('categories', 'categories.category_id = folders.category_id', 'left')
            ->join('borrow_transactions bt', 'bt.transaction_id = folders.current_borrow_transaction_id', 'left')
            ->join('archive_records ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = folders.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false);

        $statusMap = [
            'available' => 'Available',
            'borrowed' => 'Borrowed',
            'archived' => 'Archived',
            'disposed' => 'Disposed',
            'pending_archive' => 'Pending Archive',
        ];

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($status === 'archived') {
            $builder->where('folders.status', 'Archived');
        } elseif ($status !== '' && isset($statusMap[$status])) {
            $builder->where('folders.status', $statusMap[$status]);
            $builder->where('folders.status !=', 'Archived'); // Secondary safety
        } else {
            $builder->where('folders.status !=', 'Archived');
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('folders.file_code', $search)
                ->orLike('folders.company_name', $search)
                ->orLike('folders.location_code', $search)
                ->orLike('locations.rack', $search)
                ->orLike('locations.shelf', $search)
                ->orLike('categories.category_name', $search)
                ->groupEnd();
        }


        $folderType = trim((string) ($filters['folder_type'] ?? ''));
        if ($folderType !== '') {
            $builder->where('folders.folder_type', $folderType);
        }

        $sort = trim((string) ($filters['sort'] ?? 'company_asc'));
        switch ($sort) {
            case 'company_desc':
                $builder->orderBy('folders.company_name', 'DESC');
                $builder->orderBy('folders.folder_id', 'DESC');
                break;
            case 'newest':
                $builder->orderBy('folders.created_at', 'DESC');
                $builder->orderBy('folders.folder_id', 'DESC');
                break;
            case 'company_asc':
            default:
                $builder->orderBy('folders.company_name', 'ASC');
                $builder->orderBy('folders.folder_id', 'ASC');
                break;
        }

        return $builder->paginate($perPage, $group);
    }

    /**
     * Build the base query for the document records listing, applying the
     * same search/status/type/category filters used by both the paginated
     * fetch and the matching total count.
     */
    protected function buildDocumentRecordsQuery(array $filters = [])
    {
        $builder = $this->select('folders.*, categories.category_name AS folder_category, locations.rack AS cabinet, locations.shelf AS shelf, locations.rack AS rack, bt.borrowed_at AS borrowed_date, bt.expected_return_date AS due_date, bt.actual_return_date AS return_date, ar.archived_date')
            ->join('locations', 'locations.location_id = folders.location_id', 'left')
            ->join('categories', 'categories.category_id = folders.category_id', 'left')
            ->join('borrow_transactions bt', 'bt.transaction_id = folders.current_borrow_transaction_id', 'left')
            ->join('archive_records ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = folders.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false);

        $statusMap = [
            'available' => 'Available',
            'borrowed' => 'Borrowed',
            'archived' => 'Archived',
            'disposed' => 'Disposed',
            'pending_archive' => 'Pending Archive',
        ];

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && isset($statusMap[$status])) {
            $builder->where('folders.status', $statusMap[$status]);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('folders.file_code', $search)
                ->orLike('folders.company_name', $search)
                ->orLike('folders.location_code', $search)
                ->orLike('locations.rack', $search)
                ->orLike('locations.shelf', $search)
                ->orLike('categories.category_name', $search)
                ->groupEnd();
        }

        $folderType = trim((string) ($filters['folder_type'] ?? ''));
        if ($folderType !== '') {
            $builder->where('folders.folder_type', $folderType);
        }

        $category = trim((string) ($filters['category'] ?? ''));
        if ($category !== '') {
            $builder->where('categories.category_name', $category);
        }

        return $builder;
    }

    /**
     * Apply the listing's sort options to a document records query builder.
     */
    protected function applyDocumentRecordsSort($builder, string $sort): void
    {
        switch ($sort) {
            case 'company_desc':
                $builder->orderBy('folders.company_name', 'DESC');
                $builder->orderBy('folders.folder_id', 'DESC');
                break;
            case 'newest':
                $builder->orderBy('folders.created_at', 'DESC');
                $builder->orderBy('folders.folder_id', 'DESC');
                break;
            case 'company_asc':
            default:
                $builder->orderBy('folders.company_name', 'ASC');
                $builder->orderBy('folders.folder_id', 'ASC');
                break;
        }
    }

    /**
     * Get document records for the listing page, filtered/sorted/paginated
     * entirely at the database level.
     */
    public function getDocumentRecords(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $builder = $this->buildDocumentRecordsQuery($filters);
        $this->applyDocumentRecordsSort($builder, trim((string) ($filters['sort'] ?? 'company_asc')));

        if ($limit !== null) {
            $builder->limit($limit, (int) $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get total count of document records matching the given filters.
     */
    public function getTotalDocumentRecords(array $filters = []): int
    {
        return $this->buildDocumentRecordsQuery($filters)->countAllResults();
    }

    /**
     * Check if a folder with the same company_name and folder_type already exists.
     *
     * @param string   $companyName
     * @param string   $folderType
     * @param int|null $excludeFolderId Folder ID to exclude (for updates)
     * @return bool True if a duplicate exists
     */
    public function isDuplicate(string $companyName, string $folderType, ?int $excludeFolderId = null): bool
    {
        $builder = $this->where('company_name', $companyName)
            ->where('folder_type', $folderType);

        if ($excludeFolderId !== null) {
            $builder->where('folder_id !=', $excludeFolderId);
        }

        return $builder->countAllResults() > 0;
    }
}
