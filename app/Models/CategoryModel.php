<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'categories';
    protected $primaryKey       = 'category_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_name',
        'created_by',
        'updated_by'
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
        'category_name' => 'required|max_length[100]|is_unique[categories.category_name,category_id,{category_id}]'
    ];

    protected $validationMessages = [
        'category_name' => [
            'required'   => 'Category name is required.',
            'max_length' => 'Category name must not exceed 100 characters.',
            'is_unique'  => 'This category name already exists.'
        ]
    ];


    public function getCategoryWithDetails($categoryId)
    {
        return $this->select('categories.*, users.username as created_by_name')
            ->join('users', 'users.user_id = categories.created_by', 'left')
            ->where('categories.category_id', $categoryId)
            ->first();
    }

    /**
     * Get all categories with creator details
     */
    public function getAllCategoriesWithDetails()
    {
        return $this->select('categories.*, users.username as created_by_name')
            ->join('users', 'users.user_id = categories.created_by', 'left')
            ->orderBy('categories.category_name', 'ASC')
            ->findAll();
    }
}
