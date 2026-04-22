<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Display all categories
     */
    public function index()
    {
        // Check permission
        if (!can('manage_categories')) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $categories = $this->categoryModel->getAllCategoriesWithDetails();

        $data = [
            'title'      => 'Category Management',
            'categories' => $categories,
        ];

        return view('admin/categories/index', $data);
    }

    /**
     * Show create category form
     */
    public function create()
    {
        // Check permission
        if (!can('manage_categories')) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $data = [
            'title' => 'Create Category',
        ];

        return view('admin/categories/create', $data);
    }

    /**
     * Store new category
     */
    public function store()
    {
        // Check permission
        if (!can('manage_categories')) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $categoryData = [
            'category_name' => $this->request->getPost('category_name'),
            'created_by'    => auth_user()['user_id'] ?? null,
        ];

        if (!$this->categoryModel->validate($categoryData)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->categoryModel->errors());
        }

        if ($this->categoryModel->insert($categoryData)) {
            return redirect()->to(base_url('admin/categories'))
                ->with('success', 'Category created successfully.');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create category.');
    }

    /**
     * Show edit category form
     */
    public function edit($categoryId)
    {
        // Check permission
        if (!can('manage_categories')) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $category = $this->categoryModel->getCategoryWithDetails($categoryId);

        if (!$category) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Category not found');
        }

        $data = [
            'title'    => 'Edit Category',
            'category' => $category,
        ];

        return view('admin/categories/edit', $data);
    }

    /**
     * Update category
     */
    public function update($categoryId)
    {
        // Check permission
        if (!can('manage_categories')) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $category = $this->categoryModel->find($categoryId);
        if (!$category) {
            return redirect()->to(base_url('admin/categories'))->with('error', 'Category not found.');
        }

        $categoryData = [
            'category_id' => $categoryId,
            'category_name' => $this->request->getPost('category_name'),
            'updated_by'    => auth_user()['user_id'] ?? null,
        ];

        if (!$this->categoryModel->validate($categoryData)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->categoryModel->errors());
        }

        if ($this->categoryModel->update($categoryId, $categoryData)) {
            return redirect()->to(base_url('admin/categories'))
                ->with('success', 'Category updated successfully.');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update category.');
    }

    /**
     * Delete category
     */
    public function delete($categoryId)
    {
        // Check permission
        if (!can('manage_categories')) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $category = $this->categoryModel->find($categoryId);
        if (!$category) {
            return redirect()->to(base_url('admin/categories'))->with('error', 'Category not found.');
        }

        if ($this->categoryModel->delete($categoryId)) {
            return redirect()->to(base_url('admin/categories'))->with('success', 'Category deleted successfully.');
        }

        return redirect()->to(base_url('admin/categories'))->with('error', 'Failed to delete category.');
    }
}
