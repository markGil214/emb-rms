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
        'status'         => 'in_list[Available,Borrowed,Archived,Disposed,Pending,Pending Update,Declined]',
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
}