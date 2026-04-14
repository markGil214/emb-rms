<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FolderFileModel;
use App\Models\CategoryModel;
use App\Libraries\FileCodeGenerator;
use CodeIgniter\Controller;

class FolderController extends BaseController
{
    protected $folderModel;
    protected $folderFileModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->folderFileModel = new FolderFileModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * List all folders
     */
    public function index()
    {
        $folders = $this->folderModel
            // Support schemas where locations has rack/shelf (without cabinet).
            ->select('folders.*, categories.category_name AS folder_category, locations.rack AS cabinet, locations.shelf AS shelf, locations.rack AS rack, bt.borrowed_at AS borrowed_date, bt.expected_return_date AS due_date, bt.actual_return_date AS return_date, ar.archived_date AS archived_date')
            ->join('locations', 'locations.location_id = folders.location_id', 'left')
            ->join('categories', 'categories.category_id = folders.category_id', 'left')
            ->join('borrow_transactions bt', 'bt.transaction_id = folders.current_borrow_transaction_id', 'left')
            ->join('archive_records ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = folders.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false)
            ->where('folders.status !=', 'Archived')
            ->orderBy('file_code', 'ASC')
            ->findAll();

        return view('layouts/superadmin/document-records/document-records', [
            'title' => 'Folders',
            'folders' => $folders
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $db = \Config\Database::connect();
        $locations = $db->table('locations')
            ->select('location_id, rack, shelf')
            ->orderBy('rack', 'ASC')
            ->orderBy('shelf', 'ASC')
            ->get()
            ->getResultArray();

        $locations = array_map(static function (array $location) {
            return [
                'location_id' => $location['location_id'],
                'cabinet' => $location['rack'] ?? '',
                'shelf' => $location['shelf'] ?? ($location['rack'] ?? ''),
            ];
        }, $locations);

        $categories = $this->categoryModel
            ->orderBy('category_name', 'ASC')
            ->findAll();
        
        return view('layouts/superadmin/document-records/create', [
            'title' => 'Create New Folder',
            'locations' => $locations,
            'categories' => $categories,
            'selectedCategoryId' => old('category_id'),
        ]);
    }

    /**
     * Store new folder (with validation and transaction)
     */
    public function store()
    {
        $rules = $this->getFolderValidationRules(true);
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Generate file code based on company name
        $companyName = $this->request->getPost('company_name');
        $nextCode = FileCodeGenerator::getNextFromCompany($companyName);

        // Get location details to generate location code
        $locationId = (int)$this->request->getPost('location_id');
        $location = $db->table('locations')->where('location_id', $locationId)->get()->getRow();
        
        if (!$location) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Selected location not found');
        }

        // Generate location code from location details
        $locationCode = FileCodeGenerator::generateLocationCode(
            $location->rack ?? '',
            $location->shelf ?? ''
        );

        $data = [
            'file_code' => $nextCode,
            'location_code' => $locationCode,
            'company_name' => $this->request->getPost('company_name'),
            'folder_type' => $this->request->getPost('folder_type'),
            'category_id' => $this->request->getPost('category_id'),
            'status' => 'Available',
            'location_id' => $locationId,
            'created_by' => auth_user()['user_id'] ?? null,
        ];

        if (!$this->folderModel->save($data)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', implode(', ', $this->folderModel->errors()));
        }

        $db->transComplete();

        return redirect()->to('/document-records')->with('success', 'Folder created successfully');
    }

    /**
     * Show folder details
     */
    public function show(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);
        $db = \Config\Database::connect();
        $location = $db->table('locations')
            ->where('location_id', $folder['location_id'])
            ->get()
            ->getRowArray();

        // Keep the current view contract: expose cabinet and rack in $folder.
        $folder['cabinet'] = $location['cabinet'] ?? ($location['rack'] ?? null);
        $folder['rack'] = $location['rack'] ?? ($location['shelf'] ?? null);
        $files = $this->folderFileModel->getByFolder($folderId);

        return view('layouts/superadmin/document-records/show', [
            'title' => 'Folder: ' . $folder['file_code'],
            'folder' => $folder,
            'files' => $files
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);
        $categories = $this->categoryModel
            ->orderBy('category_name', 'ASC')
            ->findAll();

        return view('layouts/superadmin/document-records/edit', [
            'title' => 'Edit Folder',
            'folder' => $folder,
            'categories' => $categories,
            'selectedCategoryId' => old('category_id', $folder['category_id'] ?? ''),
        ]);
    }

    /**
     * Update folder (with optional location update)
     */
    public function update(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);

        $rules = $this->getFolderValidationRules(false);
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'folder_type' => $this->request->getPost('folder_type'),
            'category_id' => $this->request->getPost('category_id'),
            'status' => $this->request->getPost('status'),
            'borrowed_date' => $this->request->getPost('borrowed_date') ?: null,
            'due_date' => $this->request->getPost('due_date') ?: null,
            'updated_by' => auth_user()['user_id'] ?? null,
        ];

        // Update location code if cabinet/rack changed
        $cabinet = $this->request->getPost('cabinet');
        $rack = $this->request->getPost('rack');
        if ($cabinet && $rack) {
            $data['location_code'] = FileCodeGenerator::generateLocationCode($cabinet, $rack);
        }

        if (!$this->folderModel->update($folderId, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->folderModel->errors());
        }

        return redirect()->to('/document-records')->with('success', 'Folder updated successfully');
    }

    /**
     * Shared validation rules for folder create/update.
     */
    protected function getFolderValidationRules(bool $isCreate): array
    {
        $rules = [
            'company_name'  => 'required|max_length[255]',
            'folder_type'   => 'required|in_list[PERMITS,ECC / CNC FILES,IEE / EIS FILES]|max_length[50]',
            'category_id'   => 'required|integer|is_not_unique[categories.category_id]',
        ];

        if ($isCreate) {
            $rules['location_id'] = 'required|integer';
        } else {
            $rules['status'] = 'required|in_list[Available,Borrowed,Archived,Disposed]';
            $rules['borrowed_date'] = 'permit_empty|valid_date[Y-m-d]';
            $rules['due_date'] = 'permit_empty|valid_date[Y-m-d]';
        }

        return $rules;
    }

    /**
     * Helper: Find folder or throw 404
     */
    protected function findFolderOrFail(int $folderId)
    {
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $folder;
    }
}