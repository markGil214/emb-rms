<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FolderFileModel;
use App\Libraries\FileCodeGenerator;
use CodeIgniter\Controller;

class FolderController extends BaseController
{
    protected $folderModel;
    protected $folderFileModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->folderFileModel = new FolderFileModel();
    }

    /**
     * List all folders
     */
    public function index()
    {
        $folders = $this->folderModel->findAll();

        return view('folders/index', [
            'title' => 'Folders',
            'folders' => $folders
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('folders/create', [
            'title' => 'Create New Folder'
        ]);
    }

    /**
     * Store new folder (with validation and transaction)
     */
    public function store()
    {
        if (!$this->validate([
            'company_name' => 'required|max_length[255]',
            'issuance_date' => 'required|valid_date',
            'expiry_date' => 'required|valid_date',
            'cabinet' => 'required',
            'rack' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $prefix = $this->request->getPost('prefix') ?? 'GEN';

        // Generate file code safely
        $nextCode = FileCodeGenerator::getNext($prefix);

        // Generate location code
        $locationCode = FileCodeGenerator::generateLocationCode(
            $this->request->getPost('cabinet'),
            $this->request->getPost('rack')
        );

        $data = [
            'file_code' => $nextCode,
            'location_code' => $locationCode,
            'company_name' => $this->request->getPost('company_name'),
            'issuance_date' => $this->request->getPost('issuance_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'location_id' => $this->request->getPost('location_id'),
            'created_by' => auth_user()['user_id'] ?? null,
        ];

        if (!$this->folderModel->save($data)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', implode(', ', $this->folderModel->errors()));
        }

        $db->transComplete();

        return redirect()->to('/records')->with('success', 'Folder created successfully');
    }

    /**
     * Show folder details
     */
    public function show(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);
        $files = $this->folderFileModel->getByFolder($folderId);

        return view('folders/show', [
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

        return view('folders/edit', [
            'title' => 'Edit Folder',
            'folder' => $folder
        ]);
    }

    /**
     * Update folder (with optional location update)
     */
    public function update(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);

        if (!$this->validate([
            'company_name' => 'required|max_length[255]',
            'issuance_date' => 'required|valid_date',
            'expiry_date' => 'required|valid_date',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'issuance_date' => $this->request->getPost('issuance_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'status' => $this->request->getPost('status'),
            'updated_by' => auth_user()['user_id'] ?? null,
        ];

        // Update location code if cabinet/rack changed
        $cabinet = $this->request->getPost('cabinet');
        $rack = $this->request->getPost('rack');
        if ($cabinet && $rack) {
            $data['location_code'] = FileCodeGenerator::generateLocationCode($cabinet, $rack);
        }

        $this->folderModel->update($folderId, $data);

        return redirect()->to("/records/$folderId")->with('success', 'Folder updated successfully');
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