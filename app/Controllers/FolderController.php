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
            ->select('folders.*, categories.category_name AS folder_category, locations.rack AS cabinet, locations.shelf AS shelf, locations.rack AS rack, bt.borrowed_at AS borrowed_date, bt.expected_return_date AS due_date, bt.actual_return_date AS return_date, ar.archived_date AS archived_date')
            ->join('locations', 'locations.location_id = folders.location_id', 'left')
            ->join('categories', 'categories.category_id = folders.category_id', 'left')
            ->join('borrow_transactions bt', 'bt.transaction_id = folders.current_borrow_transaction_id', 'left')
            ->join('archive_records ar', 'ar.archive_id = (SELECT ar2.archive_id FROM archive_records ar2 WHERE ar2.folder_id = folders.folder_id ORDER BY ar2.archived_date DESC, ar2.archive_id DESC LIMIT 1)', 'left', false)
            ->where('folders.status !=', 'Archived')
            ->orderBy('folders.folder_id', 'DESC')
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
            'status' => 'Pending',
            'location_id' => $locationId,
            'created_by' => auth_user()['user_id'] ?? null,
        ];

        if (!$this->folderModel->save($data)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', implode(', ', $this->folderModel->errors()));
        }

        $db->transComplete();

        return redirect()->to('/document-records')->with('success', 'Folder request submitted. Waiting for approval.');
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
     * Show borrow history for a folder
     */
    public function history(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);

        $db = \Config\Database::connect();
        $history = $db->table('borrow_transactions')
            ->select('transaction_id, borrower_name, borrower_email, purpose, status, borrowed_at, expected_return_date, actual_return_date, approved_at, created_at')
            ->where('folder_id', $folderId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('layouts/superadmin/document-records/history', [
            'title' => 'Borrow History: ' . $folder['file_code'],
            'folder' => $folder,
            'history' => $history,
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

        $db = \Config\Database::connect();

        $existingPending = $db->table('document_edit_requests')
            ->where('folder_id', $folderId)
            ->where('status', 'Pending')
            ->countAllResults();

        if ($existingPending > 0) {
            return redirect()->back()->withInput()->with('error', 'A pending update request already exists for this folder.');
        }

        $requestData = [
            'folder_id' => $folderId,
            'proposed_changes' => json_encode($data),
            'current_values' => json_encode([
                'company_name' => $folder['company_name'] ?? null,
                'folder_type' => $folder['folder_type'] ?? null,
                'category_id' => $folder['category_id'] ?? null,
                'status' => $folder['status'] ?? null,
                'borrowed_date' => $folder['borrowed_date'] ?? null,
                'due_date' => $folder['due_date'] ?? null,
                'location_code' => $folder['location_code'] ?? null,
            ]),
            'reason' => 'Metadata update request',
            'status' => 'Pending',
            'requested_by' => auth_user()['user_id'] ?? null,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('document_edit_requests')->insert($requestData);

        $this->folderModel->update($folderId, [
            'status' => 'Pending Update',
            'updated_by' => auth_user()['user_id'] ?? null,
        ]);

        return redirect()->to('/document-records')->with('success', 'Folder update request submitted. Waiting for approval.');
    }

    public function approve(int $folderId)
    {
        if (!can('approve_folder_creation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folder = $this->findFolderOrFail($folderId);
        $approverId = auth_user()['user_id'] ?? null;
        $db = \Config\Database::connect();

        if (($folder['status'] ?? '') === 'Pending') {
            $this->folderModel->update($folderId, [
                'status' => 'Available',
                'updated_by' => $approverId,
            ]);

            return redirect()->to('/document-records')->with('success', 'Folder creation request approved.');
        }

        if (($folder['status'] ?? '') === 'Pending Update') {
            $request = $db->table('document_edit_requests')
                ->where('folder_id', $folderId)
                ->where('status', 'Pending')
                ->orderBy('requested_at', 'DESC')
                ->get()
                ->getRowArray();

            if (! $request) {
                return redirect()->to('/document-records')->with('error', 'No pending update request found.');
            }

            $changes = json_decode((string) ($request['proposed_changes'] ?? '{}'), true);
            if (! is_array($changes)) {
                $changes = [];
            }

            if (empty($changes['status']) || $changes['status'] === 'Pending Update') {
                $changes['status'] = 'Available';
            }

            $changes['updated_by'] = $approverId;

            if (! $this->folderModel->update($folderId, $changes)) {
                return redirect()->to('/document-records')->with('error', 'Failed to apply approved update.');
            }

            $db->table('document_edit_requests')
                ->where('edit_request_id', $request['edit_request_id'])
                ->update([
                    'status' => 'Approved',
                    'approved_by' => $approverId,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return redirect()->to('/document-records')->with('success', 'Folder update request approved.');
        }

        return redirect()->to('/document-records')->with('error', 'This record is not awaiting approval.');
    }

    public function decline(int $folderId)
    {
        if (!can('approve_folder_creation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folder = $this->findFolderOrFail($folderId);
        $approverId = auth_user()['user_id'] ?? null;
        $db = \Config\Database::connect();

        if (($folder['status'] ?? '') === 'Pending') {
            $this->folderModel->update($folderId, [
                'status' => 'Declined',
                'updated_by' => $approverId,
            ]);

            return redirect()->to('/document-records')->with('success', 'Folder creation request declined.');
        }

        if (($folder['status'] ?? '') === 'Pending Update') {
            $request = $db->table('document_edit_requests')
                ->where('folder_id', $folderId)
                ->where('status', 'Pending')
                ->orderBy('requested_at', 'DESC')
                ->get()
                ->getRowArray();

            if (! $request) {
                return redirect()->to('/document-records')->with('error', 'No pending update request found.');
            }

            $db->table('document_edit_requests')
                ->where('edit_request_id', $request['edit_request_id'])
                ->update([
                    'status' => 'Declined',
                    'approved_by' => $approverId,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'rejection_reason' => 'Declined by approver',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $this->folderModel->update($folderId, [
                'status' => 'Available',
                'updated_by' => $approverId,
            ]);

            return redirect()->to('/document-records')->with('success', 'Folder update request declined.');
        }

        return redirect()->to('/document-records')->with('error', 'This record is not awaiting approval.');
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
            $rules['status'] = 'required|in_list[Available,Borrowed,Archived,Disposed,Pending,Pending Update,Declined]';
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