<?php

namespace App\Controllers;

use App\Models\RelocationRequestModel;
use App\Models\FolderModel;
use App\Models\FolderMovementModel;
use App\Models\LocationModel;

class RelocationController extends BaseController
{
    protected $relocationModel;
    protected $folderModel;
    protected $movementModel;

    public function __construct()
    {
        $this->relocationModel = new RelocationRequestModel();
        $this->folderModel = new FolderModel();
        $this->movementModel = new FolderMovementModel();
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
     * 
     * LIFECYCLE GUARD: Folder must be Available to relocate
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

        // ARCHITECTURAL FIX #4: Lifecycle Guard - prevent relocating unavailable folders
        if ($folder['status'] !== 'Available') {
            return redirect()->back()->withInput()->with('error', 
                "Cannot relocate folder with status '{$folder['status']}'. Only Available folders can be relocated.");
        }

        $data = [
            'folder_id' => $folderId,
            'from_location_id' => $folder['location_id'],
            'to_location_id' => $this->request->getPost('new_location_id'),
            'reason' => $this->request->getPost('reason'),
            'reason_type' => $this->request->getPost('reason_type') ?? 'Other',
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
     * 
     * ENTERPRISE UPGRADE:
     * Transitions folder to "In-Transit" state
     * Generates unique movement code
     * Marks as in-transit in folders table
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

        // ENTERPRISE FIX: Use new startMovement() which handles in-transit state
        try {
            $movementId = $this->movementModel->startMovement(
                $relocation['folder_id'],
                $relocationId,
                ['to_location_id' => $relocation['to_location_id']]
            );

            // Update relocation status
            $this->relocationModel->startRelocation($relocationId);

            // Audit trail
            $auditLog = service('auditLog');
            $auditLog->log(
                auth_user()['user_id'],
                'start_relocation',
                "relocation_id:{$relocationId},folder_id:{$relocation['folder_id']},movement_id:{$movementId}"
            );

            return redirect()->back()->with('success', 'Relocation started. Folder marked as in-transit.');

        } catch (\Exception $e) {
            log_message('error', 'Relocation start failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to start relocation: ' . $e->getMessage());
        }
    }

    /**
     * Complete relocation
     * 
     * ENTERPRISE UPGRADES:
     * 1. Updates folder location to new location
     * 2. Records movement in folder_movements (audit trail)
     * 3. Transitions folder from "In-Transit" to normal
     * 4. Captures complete approval chain (who → when)
     * 5. Detects race conditions (status changes during relocation)
     * 6. Uses transactions for data integrity
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

        $folder = $this->folderModel->find($relocation['folder_id']);
        if (!$folder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Verify folder is actually in-transit (safety check)
        if (!$folder['is_in_transit']) {
            return redirect()->back()->with('error', 'Folder is not in transit. Cannot complete relocation.');
        }

        // Get new location details
        $db = \Config\Database::connect();
        $toLocation = $db->table('locations')
            ->where('location_id', $relocation['to_location_id'])
            ->first();

        if (!$toLocation) {
            return redirect()->back()->with('error', 'Target location not found');
        }

        try {
            // Get active movement
            $activeMovement = $this->movementModel->getActiveMovement($relocation['folder_id']);
            if (!$activeMovement) {
                throw new \Exception('No active movement found for folder');
            }

            // ENTERPRISE FIX: Use new completeMovement() which handles:
            // - Location updates
            // - Movement finalization with approval audit
            // - Race condition detection
            // - Transaction management
            $this->movementModel->completeMovement(
                $relocation['folder_id'],
                $activeMovement['movement_id'],
                $relocation['to_location_id'],
                [
                    'building' => $toLocation['building'] ?? null,
                    'room' => $toLocation['room'] ?? null,
                    'cabinet' => $toLocation['cabinet'] ?? null,
                    'shelf' => $toLocation['shelf'] ?? null,
                    'box' => $toLocation['box'] ?? null,
                ]
            );

            // Mark relocation as completed
            $this->relocationModel->completeRelocation($relocationId);

            // Audit logging with enhanced details
            $auditLog = service('auditLog');
            $auditLog->log(
                auth_user()['user_id'],
                'complete_relocation',
                "relocation_id:{$relocationId},folder_id:{$relocation['folder_id']}," .
                "from_location:{$folder['location_id']},to_location:{$relocation['to_location_id']}," .
                "lifecycle:in_transit→available,race_condition_check:passed"
            );

            return redirect()->to('/relocations')->with(
                'success',
                'Relocation completed. Folder location updated, marked as available, and movement recorded with complete audit trail.'
            );

        } catch (\Exception $e) {
            log_message('error', 'Relocation completion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to complete relocation: ' . $e->getMessage());
        }
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

    /**
     * Test Console - Debug Dashboard
     * 
     * DEVELOPMENT TOOL: Not for production
     * Renders a debug interface to manually test the full relocation lifecycle
     * Shows all movements in the database in real-time
     */
    public function testConsole()
    {
        $movements = $this->movementModel->findAll();

        return view('relocation_test/index', [
            'movements' => $movements,
        ]);
    }
}
