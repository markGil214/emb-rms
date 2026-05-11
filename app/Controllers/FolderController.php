<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FolderFileModel;
use App\Models\CategoryModel;
use App\Models\RelocationRequestModel;
// use App\Models\FolderMovementModel;
use App\Libraries\FileCodeGenerator;
use CodeIgniter\Controller;

class FolderController extends BaseController
{
    protected $folderModel;
    protected $folderFileModel;
    protected $categoryModel;
    protected $relocationModel;
//    protected $movementModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->folderFileModel = new FolderFileModel();
        $this->categoryModel = new CategoryModel();
        $this->relocationModel = new RelocationRequestModel();
//        $this->movementModel = new FolderMovementModel();
    }

    /**
     * List all folders
     */
    public function index()
    {
        $filters = [
            'search' => trim((string) $this->request->getGet('search')),
            'status' => trim((string) $this->request->getGet('status')),
            'folder_type' => trim((string) $this->request->getGet('folder_type')),
            'category' => trim((string) $this->request->getGet('category')),
            'sort' => trim((string) $this->request->getGet('sort')),
        ];

        if ($filters['sort'] === '') {
            $filters['sort'] = 'company_asc';
        }

        $folders = $this->folderModel->getDocumentRecords();
        $categories = $this->categoryModel->orderBy('category_name', 'ASC')->findAll();

        return view('document-records/index', [
            'title' => 'Folders',
            'folders' => $folders,
            'categories' => $categories,
            'filters' => $filters,
            'pager' => null,
            'perPage' => count($folders),
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
        
        return view('document-records/create', [
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
        $db = \Config\Database::connect();
        $db->transStart();

        $companyName = $this->request->getPost('company_name');
        $folderType = $this->request->getPost('folder_type');
        $categoryId = (int) $this->request->getPost('category_id');
        $nextCode = FileCodeGenerator::getNextFromCompany($companyName);

        // Check for duplicate: same company name and folder type
        if ($this->folderModel->isDuplicate($companyName, $folderType)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'A folder with the same company name and folder type already exists.');
        }

        $locationId = (int) $this->request->getPost('location_id');
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
            'company_name' => $companyName,
            'folder_type' => $folderType,
            'category_id' => $categoryId,
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

        // Map location columns to folder fields with correct labels
        $folder['cabinet'] = $location['cabinet'] ?? null;
        $folder['rack']    = $location['rack'] ?? null;
        $folder['shelf']   = $location['shelf'] ?? null;

        // SELF-HEALING: If location_code is out of sync or in old format, fix it now
        $correctCode = FileCodeGenerator::generateLocationCode($location['rack'] ?? '', $location['shelf'] ?? '');
        if (($folder['location_code'] ?? '') !== $correctCode) {
            $this->folderModel->update($folderId, ['location_code' => $correctCode]);
            $folder['location_code'] = $correctCode; // Update local copy for immediate display
        }

        $files = $this->folderFileModel->getByFolder($folderId);

        return view('document-records/show', [
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
            ->select('transaction_id, borrower_name, borrower_email, purpose, notes, status, borrowed_at, expected_return_date, actual_return_date, approved_at, created_at')
            ->where('folder_id', $folderId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $requestHistory = $db->table('relocation_requests as r')
            ->select('r.relocation_id, r.folder_id, r.from_location_id, r.to_location_id, r.reason, r.reason_type, r.status, r.requested_by, r.approved_by, r.requested_at, r.approved_at, r.rejection_reason, r.created_at, r.updated_at, fl.building as from_building, fl.room as from_room, fl.rack as from_rack, fl.shelf as from_shelf, tl.building as to_building, tl.room as to_room, tl.rack as to_rack, tl.shelf as to_shelf, ru.username as requested_by_username, au.username as approved_by_username')
            ->join('folders as f', 'f.folder_id = r.folder_id', 'left')
            ->join('locations as fl', 'fl.location_id = r.from_location_id', 'left')
            ->join('locations as tl', 'tl.location_id = r.to_location_id', 'left')
            ->join('users as ru', 'ru.user_id = r.requested_by', 'left')
            ->join('users as au', 'au.user_id = r.approved_by', 'left')
            ->groupStart()
                ->where('r.folder_id', $folderId)
                ->orWhere('f.file_code', $folder['file_code'])
            ->groupEnd()
            ->groupBy('r.relocation_id')
            ->orderBy('r.requested_at', 'DESC')
            ->get()
            ->getResultArray();

        // Fallback: if joins fail to match in current runtime DB, fetch direct requests by folder_id.
        if (empty($requestHistory)) {
            $requestHistory = $db->table('relocation_requests as r')
                ->select('r.relocation_id, r.folder_id, r.from_location_id, r.to_location_id, r.reason, r.reason_type, r.status, r.requested_by, r.approved_by, r.requested_at, r.approved_at, r.rejection_reason, r.created_at, r.updated_at, fl.rack as from_rack, fl.shelf as from_shelf, tl.rack as to_rack, tl.shelf as to_shelf, ru.username as requested_by_username, au.username as approved_by_username')
                ->join('locations as fl', 'fl.location_id = r.from_location_id', 'left')
                ->join('locations as tl', 'tl.location_id = r.to_location_id', 'left')
                ->join('users as ru', 'ru.user_id = r.requested_by', 'left')
                ->join('users as au', 'au.user_id = r.approved_by', 'left')
                ->where('r.folder_id', $folderId)
                ->orderBy('r.requested_at', 'DESC')
                ->get()
                ->getResultArray();
        }
        $relocationHistory = array_map(static function (array $row): array {
            $row['history_kind'] = 'request';
            $row['history_date'] = $row['requested_at'] ?? $row['created_at'] ?? null;
            return $row;
        }, $requestHistory);

        usort($relocationHistory, static function (array $left, array $right): int {
            $leftTime = ! empty($left['history_date']) ? strtotime((string) $left['history_date']) : 0;
            $rightTime = ! empty($right['history_date']) ? strtotime((string) $right['history_date']) : 0;

            if ($leftTime === $rightTime) {
                return 0;
            }

            return ($leftTime < $rightTime) ? 1 : -1;
        });

        return view('document-records/history', [
            'title' => 'Borrow and Relocation History: ' . $folder['file_code'],
            'folder' => $folder,
            'history' => $history,
            'relocationHistory' => $relocationHistory,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(int $folderId)
    {
        $folder = $this->findFolderOrFail($folderId);
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

        $location = $db->table('locations')
            ->where('location_id', $folder['location_id'])
            ->get()
            ->getRowArray();

        $folder['cabinet'] = $location['rack'] ?? null;
        $folder['rack'] = $location['shelf'] ?? null;

        $categories = $this->categoryModel
            ->orderBy('category_name', 'ASC')
            ->findAll();

        return view('document-records/edit', [
            'title' => 'Edit Folder',
            'folder' => $folder,
            'locations' => $locations,
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

        $postedRack = trim((string) ($this->request->getPost('cabinet') ?? ''));
        $postedShelf = trim((string) ($this->request->getPost('shelf') ?? ''));

        if (($postedRack === '' && $postedShelf !== '') || ($postedRack !== '' && $postedShelf === '')) {
            return redirect()->back()->withInput()->with('errors', [
                'location_id' => 'Please select both Rack and Shelf.',
            ]);
        }

        $companyName = $this->request->getPost('company_name');
        $folderType = $this->request->getPost('folder_type');
        $categoryId = (int) $this->request->getPost('category_id');

        // Check for duplicate: same company name and folder type (exclude current folder)
        if ($this->folderModel->isDuplicate($companyName, $folderType, $folderId)) {
            return redirect()->back()->withInput()->with('error', 'A folder with the same company name and folder type already exists.');
        }

        $data = [
            'company_name' => $companyName,
            'folder_type' => $folderType,
            'category_id' => $categoryId,
            'status' => $folder['status'] ?? 'Available',
            'borrowed_date' => $this->request->getPost('borrowed_date') ?: null,
            'due_date' => $folder['due_date'] ?? null,
            'location_id' => (int) ($this->request->getPost('location_id') ?: ($folder['location_id'] ?? 0)),
            'updated_by' => auth_user()['user_id'] ?? null,
        ];

        $db = \Config\Database::connect();
        $selectedLocation = $db->table('locations')
            ->where('location_id', $data['location_id'])
            ->get()
            ->getRowArray();

        if (!$selectedLocation) {
            return redirect()->back()->withInput()->with('error', 'Selected location not found');
        }

        $data['location_code'] = FileCodeGenerator::generateLocationCode(
            $selectedLocation['rack'] ?? '',
            $selectedLocation['shelf'] ?? ''
        );

        if ($folder['status'] === 'Pending') {
            $this->folderModel->update($folderId, $data);
            return redirect()->to('/document-records')->with('success', 'Pending folder updated successfully.');
        }

        $db = \Config\Database::connect();
        $existingRequest = $db->table('document_edit_requests')
            ->where('folder_id', $folderId)
            ->where('status', 'Pending')
            ->get()
            ->getRowArray();

        if ($existingRequest) {
            $db->table('document_edit_requests')
                ->where('edit_request_id', $existingRequest['edit_request_id'])
                ->update([
                    'proposed_changes' => json_encode($data),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            
            return redirect()->to('/document-records')->with('success', 'Existing update request updated.');
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
                'location_id' => $folder['location_id'] ?? null,
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

/*
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
*/

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

/*
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
*/

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
            $rules['borrowed_date'] = 'permit_empty|valid_date[Y-m-d]';
            $rules['location_id'] = 'required|integer';
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
