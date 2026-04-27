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
     * 
     * ENHANCEMENT: Joins with folders and locations for human-readable display
     */
    public function index()
    {
        $permissionService = service('permissionService');
        $userId = auth_user()['user_id'];

        $relocations = $this->relocationModel->getPaginatedRelocations(25, 'relocations');

        // Build formatted location strings for display
        $relocations = array_map(function($rel) {
            $rel['current_location_display'] = $this->formatLocation($rel['rack'] ?? null, $rel['shelf'] ?? null);
            $rel['new_location_display'] = $this->formatLocation($rel['to_rack'] ?? null, $rel['to_shelf'] ?? null);
            return $rel;
        }, $relocations);

        return view('relocation/index', [
            'title' => 'Folder Relocation',
            'subtitle' => 'Move folders between cabinets and shelves',
            'relocations' => $relocations,
            'pager' => $this->relocationModel->pager,
            'needsApproval' => (new RelocationRequestModel())->where('status', 'Pending')->countAllResults(),
            'completed' => (new RelocationRequestModel())->whereIn('status', ['Approved', 'Completed'])->countAllResults(),
        ]);
    }

    /**
     * Helper: Format location display string
     */
    private function formatLocation($rack, $shelf = null)
    {
        if (!$rack && !$shelf) {
            return 'Unknown Location';
        }

        if ($rack && $shelf) {
            return "Rack {$rack} - Shelf {$shelf}";
        }

        if ($rack) {
            return "Rack {$rack}";
        }

        return "Shelf {$shelf}";
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

        $rules = [
            'folder_id' => 'required|integer',
            'to_location_id' => 'required|integer',
            'reason' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $folderId = (int) $this->request->getPost('folder_id');
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => 'Selected folder was not found.',
            ]);
        }

        $toLocationId = (int) $this->request->getPost('to_location_id');
        $db = \Config\Database::connect();
        $toLocation = $db->table('locations')->where('location_id', $toLocationId)->get()->getRowArray();
        if (!$toLocation) {
            return redirect()->back()->withInput()->with('errors', [
                'to_location_id' => 'Selected target location was not found.',
            ]);
        }

        if ((int) $folder['location_id'] === $toLocationId) {
            return redirect()->back()->withInput()->with('errors', [
                'to_location_id' => 'Target location must be different from current location.',
            ]);
        }

        // ARCHITECTURAL FIX #4: Lifecycle Guard - prevent relocating unavailable folders
        if ($folder['status'] !== 'Available') {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => "Cannot relocate folder with status '{$folder['status']}'. Only Available folders can be relocated.",
            ]);
        }

        $data = [
            'folder_id' => $folderId,
            'from_location_id' => $folder['location_id'],
            'to_location_id' => $toLocationId,
            'reason' => $this->request->getPost('reason'),
            'status' => 'Pending',
            'requested_at' => date('Y-m-d H:i:s'),
            'requested_by' => auth_user()['user_id'],
        ];

        if (!$this->relocationModel->save($data)) {
            return redirect()->back()->withInput()->with('errors',
                $this->relocationModel->errors() ?: ['general' => 'Failed to create relocation request']
            );
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
        $currentLocation = $db->table('locations')->where('location_id', $relocation['from_location_id'])->get()->getRowArray();
        $newLocation = $db->table('locations')->where('location_id', $relocation['to_location_id'])->get()->getRowArray();

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

        return redirect()->back()->with('success', 'Relocation completed');
    }

    /**
     * Decline/Reject relocation
     */
    public function decline(int $relocationId)
    {
        if (!can('approve_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $relocation = $this->relocationModel->find($relocationId);
        if (!$relocation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($relocation['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending relocations can be declined');
        }

        $this->relocationModel->update($relocationId, [
            'status' => 'Declined',
        ]);

        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'decline_relocation', "relocation_id:{$relocationId}");

        return redirect()->back()->with('success', 'Relocation declined');
    }

    /**
     * Edit relocation form
     */
    public function edit(int $relocationId)
    {
        if (!can('initiate_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $relocation = $this->relocationModel->find($relocationId);
        if (!$relocation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($relocation['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending relocations can be edited');
        }

        $folder = $this->folderModel->find($relocation['folder_id']);
        $db = \Config\Database::connect();
        $locations = $db->table('locations')->get()->getResultArray();

        return view('relocation/edit', [
            'title' => 'Edit Relocation Request',
            'relocation' => $relocation,
            'folder' => $folder,
            'locations' => $locations,
        ]);
    }

    /**
     * Update relocation
     */
    public function update(int $relocationId)
    {
        if (!can('initiate_relocation')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $relocation = $this->relocationModel->find($relocationId);
        if (!$relocation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($relocation['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending relocations can be edited');
        }

        $rules = [
            'to_location_id' => 'required|integer',
            'reason' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $toLocationId = (int) $this->request->getPost('to_location_id');
        $db = \Config\Database::connect();
        $toLocation = $db->table('locations')->where('location_id', $toLocationId)->get()->getRowArray();
        if (!$toLocation) {
            return redirect()->back()->withInput()->with('errors', [
                'to_location_id' => 'Selected target location was not found.',
            ]);
        }

        if ((int) $relocation['from_location_id'] === $toLocationId) {
            return redirect()->back()->withInput()->with('errors', [
                'to_location_id' => 'Target location must be different from current location.',
            ]);
        }

        if (!$this->relocationModel->update($relocationId, [
            'to_location_id' => $toLocationId,
            'reason' => $this->request->getPost('reason'),
        ])) {
            return redirect()->back()->withInput()->with('errors',
                $this->relocationModel->errors() ?: ['general' => 'Failed to update relocation request']
            );
        }

        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'update_relocation', "relocation_id:{$relocationId}");

        return redirect()->to('/relocations')->with('success', 'Relocation updated');
    }

    /**
     * Mark relocation as in progress
     * 
    * ENTERPRISE UPGRADE:
    * Creates a movement record for the relocation workflow
    * Generates unique movement code
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

        try {
            if ($this->movementModel->getActiveMovement($relocation['folder_id'])) {
                return redirect()->back()->with('error', 'Folder already has an active relocation movement.');
            }

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

            return redirect()->back()->with('success', 'Relocation started.');

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
    * 3. Finalizes the active movement and updates the folder location
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

        // Get new location details
        $db = \Config\Database::connect();
        $toLocation = $db->table('locations')
            ->where('location_id', $relocation['to_location_id'])
            ->get()->getRowArray();

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
                    'cabinet' => $toLocation['cabinet'] ?? ($toLocation['rack'] ?? null),
                    'shelf' => $toLocation['rack'] ?? null,
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
                "from_location:{$relocation['from_location_id']},to_location:{$relocation['to_location_id']}," .
                "lifecycle:movement_finalized,race_condition_check:passed"
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

        $db = \Config\Database::connect();
        
        // Get only pending relocations with joined data
        $relocations = $db->table('relocation_requests as r')
            ->select('r.*, f.file_code, f.company_name, fl.rack, fl.shelf, tl.rack as to_rack, tl.shelf as to_shelf')
            ->join('folders as f', 'f.folder_id = r.folder_id', 'left')
            ->join('locations as fl', 'fl.location_id = r.from_location_id', 'left')
            ->join('locations as tl', 'tl.location_id = r.to_location_id', 'left')
            ->where('r.status', 'Pending')
            ->orderBy('r.requested_at', 'DESC')
            ->get()
            ->getResultArray();

        // Build formatted location strings for display
        $relocations = array_map(function($rel) {
            $rel['current_location_display'] = $this->formatLocation($rel['rack'] ?? null, $rel['shelf'] ?? null);
            $rel['new_location_display'] = $this->formatLocation($rel['to_rack'] ?? null, $rel['to_shelf'] ?? null);
            return $rel;
        }, $relocations);

        return view('relocation/pending', [
            'title' => 'Pending Relocations',
            'subtitle' => 'Relocation requests awaiting approval',
            'pending' => $relocations,
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
