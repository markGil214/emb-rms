<?php

namespace App\Models;

use CodeIgniter\Model;

class FolderFileModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'folder_files';
    protected $primaryKey       = 'file_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folder_id', 'file_name', 'file_path', 'file_size', 'uploaded_by', 'retention_type', 'expiration_date'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'folder_id'  => 'required',
        'file_name'  => 'required|max_length[255]',
        'file_path'  => 'required|max_length[500]',
        'file_size'  => 'required|is_natural_no_zero',
        'uploaded_by'=> 'required',
        'retention_type' => 'required|in_list[permanent,expiration]',
        'expiration_date' => 'permit_empty|valid_date[Y-m-d]',
    ];

    protected $validationMessages = [
        'folder_id' => [
            'required' => 'Folder ID is required',
        ],
        'file_name' => [
            'required'   => 'File name is required',
            'max_length' => 'File name cannot exceed 255 characters',
        ],
        'file_path' => [
            'required'   => 'File path is required',
            'max_length' => 'File path cannot exceed 500 characters',
        ],
        'file_size' => [
            'required'          => 'File size is required',
            'is_natural_no_zero' => 'File size must be greater than 0',
        ],
        'uploaded_by' => [
            'required' => 'Uploaded by user ID is required',
        ],
        'retention_type' => [
            'required' => 'Retention type is required',
            'in_list'  => 'Retention type must be Permanent or Expiration',
        ],
        'expiration_date' => [
            'valid_date' => 'Expiration date must be a valid date',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks       = false;

    /**
     * Get all files for a specific folder
     */
    public function getByFolder(int $folderId)
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get file with uploader information
     */
    public function withUploader(int $fileId)
    {
        return $this->select('folder_files.*, users.username')
                    ->join('users', 'users.user_id = folder_files.uploaded_by', 'left')
                    ->where('folder_files.file_id', $fileId)
                    ->first();
    }

    /**
     * Delete file by ID
     */
    public function deleteFile(int $fileId)
    {
        return $this->delete($fileId);
    }

    /**
     * Format file size to human readable format
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
