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
        $folders = $this->folderModel
            ->select('folders.*, locations.cabinet, locations.rack')
            ->join('locations', 'locations.location_id = folders.location_id', 'left')
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
        $locations = $db->table('locations')->get()->getResultArray();
        
        return view('layouts/superadmin/document-records/create', [
            'title' => 'Create New Folder',
            'locations' => $locations
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
            'location_id' => 'required|integer',
            'status' => 'required|in_list[Available,Borrowed,Archived,Disposed]',
            'folder_type' => 'in_list[Commercial sand and gravel,Telecommunication,Local Government Unit,Mining Company,Hydro Power Plants]|max_length[50]',
        ])) {
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
            $location->cabinet ?? '',
            $location->shelf ?? ''
        );

        $data = [
            'file_code' => $nextCode,
            'location_code' => $locationCode,
            'company_name' => $this->request->getPost('company_name'),
            'folder_type' => $this->request->getPost('folder_type'),
            'issuance_date' => $this->request->getPost('issuance_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'status' => $this->request->getPost('status'),
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
        $folder['cabinet'] = $location['cabinet'] ?? null;
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

        return view('layouts/superadmin/document-records/edit', [
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
            'folder_type' => 'in_list[Commercial sand and gravel,Telecommunication,Local Government Unit,Mining Company,Hydro Power Plants]|max_length[50]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'company_name' => $this->request->getPost('company_name'),
            'folder_type' => $this->request->getPost('folder_type'),
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

        return redirect()->to('/document-records')->with('success', 'Folder updated successfully');
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