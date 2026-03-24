<?php

namespace App\Controllers;

use App\Models\RelocationRequestModel;
use App\Models\FolderModel;
use App\Models\LocationModel;

class RelocationController extends BaseController
{
    protected $relocationModel;
    protected $folderModel;

    public function __construct()
    {
        $this->relocationModel = new RelocationRequestModel();
        $this->folderModel = new FolderModel();
    }

    /**
     * List all relocations
     */
    public function index()
    {
        $permissionService = service('permissionService');
        $userId = auth_user()['user_id'];

        $relocations = $this->relocationModel->orderBy('requested_date', 'DESC')->findAll();

        return view('relocation/index', [
            'title' => 'Relocation Management',
            'relocations' => $relocations,
            'totalPending' => count($this->relocationModel->getPending()),
            'totalApproved' => count($this->relocationModel->getApproved()),
            'totalInProgress' => count($this->relocationModel->getInProgress()),
        ]);
    }

    /**
     * Show form to create relocation request
     */
    public function create()
    {
        if (!can('initiate_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folders = $this->folderModel->findAll();
        $db = \Config\Database::connect();
        $locations = $db->table('locations')->get()->getResultArray();

        return view('relocation/create', [
            'title' => 'Create Relocation Request',
            'folders' => $folders,
            'locations' => $locations,
        ]);
    }

    /**
     * Store relocation request
     */
    public function store()
    {
        if (!can('initiate_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        if (!$this->validate($this->relocationModel->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $folderId = $this->request->getPost('folder_id');
        $folder = $this->folderModel->find($folderId);

        $data = [
            'folder_id' => $folderId,
            'from_location_id' => $folder['location_id'],
            'to_location_id' => $this->request->getPost('new_location_id'),
            'reason' => $this->request->getPost('reason'),
            'status' => 'Pending',
            'requested_at' => date('Y-m-d H:i:s'),
            'requested_by' => auth_user()['user_id'],
        ];

        if (!$this->relocationModel->save($data)) {
            return redirect()->back()->withInput()->with('error', 'Failed to create relocation request');
        }

        log_message('info', "User " . auth_user()['user_id'] . " requested relocation for folder {$folderId}");

        return redirect()->to('/relocations')->with('success', 'Relocation request created');
    }

    /**
     * Show relocation details
     */
    public function show(int $relocationId)
    {
        $relocation = $this->relocationModel->find($relocationId);

        if (!$relocation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $folder = $this->folderModel->find($relocation['folder_id']);
        $db = \Config\Database::connect();
        $currentLocation = $db->table('locations')->find($relocation['from_location_id']);
        $newLocation = $db->table('locations')->find($relocation['to_location_id']);

        return view('relocation/show', [
            'title' => 'Relocation Details',
            'relocation' => $relocation,
            'folder' => $folder,
            'currentLocation' => $currentLocation,
            'newLocation' => $newLocation,
        ]);
    }

    /**
     * Approve relocation (Admin only)
     */
    public function approve(int $relocationId)
    {
        if (!can('approve_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $relocation = $this->relocationModel->find($relocationId);
        if (!$relocation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->relocationModel->approveRelocation($relocationId, auth_user()['user_id']);

        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'approve_relocation', "relocation_id:{$relocationId}");

        return redirect()->back()->with('success', 'Relocation approved');
    }

    /**
     * Mark relocation as in progress
     */
    public function startRelocation(int $relocationId)
    {
        if (!can('initiate_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $relocation = $this->relocationModel->find($relocationId);
        if (!$relocation || $relocation['status'] !== 'Approved') {
            return redirect()->back()->with('error', 'Invalid status for this action');
        }

        $this->relocationModel->startRelocation($relocationId);

        return redirect()->back()->with('success', 'Relocation started');
    }

    /**
     * Complete relocation
     */
    public function complete(int $relocationId)
    {
        if (!can('complete_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $relocation = $this->relocationModel->find($relocationId);
        if (!$relocation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Update relocation
        $this->relocationModel->completeRelocation($relocationId);

        // Update folder location
        $this->folderModel->update($relocation['folder_id'], [
            'location_id' => $relocation['to_location_id']
        ]);

        // Log
        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'complete_relocation', "relocation_id:{$relocationId}");

        $db->transComplete();

        return redirect()->to('/relocations')->with('success', 'Relocation completed');
    }

    /**
     * List pending relocations
     */
    public function pending()
    {
        if (!can('approve_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $pending = $this->relocationModel->getPending();

        return view('relocation/pending', [
            'title' => 'Pending Relocations',
            'pending' => $pending,
        ]);
    }
}
