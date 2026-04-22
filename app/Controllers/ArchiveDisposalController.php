<?php

namespace App\Controllers;

use App\Models\ArchiveRecordModel;
use App\Models\DisposalRecordModel;
use App\Models\FolderModel;

class ArchiveDisposalController extends BaseController
{
    protected $archiveModel;
    protected $disposalModel;
    protected $folderModel;

    public function __construct()
    {
        $this->archiveModel = new ArchiveRecordModel();
        $this->disposalModel = new DisposalRecordModel();
        $this->folderModel = new FolderModel();
    }

    /**
     * Main archive/disposal dashboard
     */
    public function index()
    {
        $archived = $this->archiveModel->getAllArchived();
        $disposalPending = $this->disposalModel->getPending();
        $disposalApproved = $this->disposalModel->getApproved();

        return view('archive-disposal/index', [
            'title' => 'Archive & Disposal Management',
            'archived' => $archived,
            'disposalPending' => $disposalPending,
            'disposalApproved' => $disposalApproved,
            'totalArchived' => count($archived),
            'totalDisposalPending' => count($disposalPending),
            'totalDisposalApproved' => count($disposalApproved),
        ]);
    }

    // ===== ARCHIVE OPERATIONS =====

    /**
     * Show form to archive a folder
     */
    public function createArchive()
    {
        if (!can('request_archive')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folders = $this->folderModel->where('status !=', 'Archived')
                                     ->where('status !=', 'Disposed')
                                     ->findAll();
        $db = \Config\Database::connect();
        $locations = $db->table('locations')->get()->getResultArray();

        return view('archive-disposal/archive-create', [
            'title' => 'Archive Document',
            'folders' => $folders,
            'locations' => $locations,
        ]);
    }

    /**
     * Store archive record
     */
    public function storeArchive()
    {
        if (!can('request_archive')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $rules = [
            'folder_id' => 'required|integer',
            'archive_location_id' => 'required|integer',
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

        $archiveLocationId = (int) $this->request->getPost('archive_location_id');
        $db = \Config\Database::connect();
        $archiveLocation = $db->table('locations')->where('location_id', $archiveLocationId)->get()->getRowArray();
        if (!$archiveLocation) {
            return redirect()->back()->withInput()->with('errors', [
                'archive_location_id' => 'Selected archive location was not found.',
            ]);
        }

        $db->transStart();

        $data = [
            'folder_id' => $folderId,
            'archived_date' => date('Y-m-d'),
            'archive_location_id' => $archiveLocationId,
            'archived_by' => auth_user()['user_id'],
        ];

        if (!$this->archiveModel->save($data)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors',
                $this->archiveModel->errors() ?: ['general' => 'Failed to create archive record']
            );
        }

        // Update folder status
        $this->folderModel->update($data['folder_id'], ['status' => 'Archived']);

        // Log audit
        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'create_archive', "folder_id:{$data['folder_id']}");

        $db->transComplete();

        return redirect()->to('/archive-disposal')->with('success', 'Document archived successfully');
    }

    /**
     * View archived document details
     */
    public function showArchive(int $archiveId)
    {
        $archive = $this->archiveModel->find($archiveId);

        if (!$archive) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $folder = $this->folderModel->find($archive['folder_id']);

        return view('archive-disposal/archive-show', [
            'title' => 'Archive Details',
            'archive' => $archive,
            'folder' => $folder,
        ]);
    }

    /**
     * Search archived records
     */
    public function searchArchive()
    {
        $query = $this->request->getGet('q') ?? '';

        if (empty($query)) {
            $results = [];
        } else {
            $results = $this->archiveModel->search($query);
        }

        return view('archive-disposal/archive-search', [
            'title' => 'Search Archives',
            'results' => $results,
            'query' => $query,
        ]);
    }

    // ===== DISPOSAL OPERATIONS =====

    /**
     * Show form to request disposal
     */
    public function createDisposal()
    {
        if (!can('request_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        // Get archived records that don't have disposals yet
        $db = \Config\Database::connect();
        $archived = $db->table('archive_records as ar')
                  ->select('ar.archive_id, ar.folder_id, ar.archived_date, f.file_code, f.company_name')
                  ->join('folders as f', 'f.folder_id = ar.folder_id', 'left')
                  ->join('disposal_records as dr', 'dr.archive_id = ar.archive_id', 'left')
                  ->where('dr.disposal_id IS NULL')
                  ->get()
                  ->getResultArray();

        return view('archive-disposal/disposal-create', [
            'title' => 'Request Disposal',
            'archived' => $archived,
            'disposalMethods' => ['Destruction', 'Recycling', 'Transfer', 'Donation', 'Return'],
        ]);
    }

    /**
     * Store disposal request
     */
    public function storeDisposal()
    {
        if (!can('request_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $rules = [
            'archive_id'           => 'required|integer',
            'disposal_method'      => 'required|in_list[Destruction,Recycling,Transfer,Donation,Return]',
            'reason'               => 'required|max_length[1000]',
            'compliance_reference' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $archiveId = (int) $this->request->getPost('archive_id');
        $archive = $this->archiveModel->find($archiveId);
        if (!$archive) {
            return redirect()->back()->withInput()->with('errors', [
                'archive_id' => 'Selected archive record was not found.',
            ]);
        }

        $lifecycleContext = [
            'status' => 'Pending',
            'disposal_date' => null,
            'approved_by' => null,
        ];

        if (!$this->disposalModel->validateLifecycleState($lifecycleContext)) {
            return redirect()->back()->withInput()->with('errors', $this->disposalModel->getLifecycleErrors());
        }

        $reason = trim((string) $this->request->getPost('reason'));
        $reference = trim((string) ($this->request->getPost('compliance_reference') ?? ''));

        $complianceReference = "Reason: {$reason}";
        if ($reference !== '') {
            $complianceReference .= "\nReference: {$reference}";
        }

        $data = [
            'archive_id' => $archiveId,
            'disposal_method' => $this->request->getPost('disposal_method'),
            'disposal_date' => null,
            'compliance_reference' => $complianceReference,
            'approved_by' => null,
        ];

        if (!$this->disposalModel->save($data)) {
            return redirect()->back()->withInput()->with('errors',
                $this->disposalModel->errors() ?: ['general' => 'Failed to create disposal record']
            );
        }

        // Log audit
        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'request_disposal', "archive_id:{$data['archive_id']}");

        return redirect()->to('/archive-disposal')->with('success', 'Disposal request created');
    }

    /**
     * View disposal request details
     */
    public function showDisposal(int $disposalId)
    {
        $disposal = $this->disposalModel->find($disposalId);

        if (!$disposal) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();
        $archive = $db->table('archive_records')
            ->where('archive_id', $disposal['archive_id'])
            ->get()
            ->getRowArray();
        $folder = null;
        if ($archive) {
            $folder = $this->folderModel->find($archive['folder_id']);
        }

        return view('archive-disposal/disposal-show', [
            'title' => 'Disposal Request Details',
            'disposal' => $disposal,
            'archive' => $archive,
            'folder' => $folder,
        ]);
    }

    /**
     * Approve and execute disposal
     */
    public function approveDisposal(int $disposalId)
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $disposal = $this->disposalModel->find($disposalId);
        if (!$disposal) {
            return redirect()->back()->withInput()->with('errors', [
                'disposal_id' => 'Disposal request not found.',
            ]);
        }

        if ($this->disposalModel->inferStatus($disposal) !== 'Pending') {
            return redirect()->back()->withInput()->with('errors', [
                'status' => 'Only Pending disposal requests can be approved.',
            ]);
        }

        $archive = $this->archiveModel->find((int) $disposal['archive_id']);
        if (!$archive) {
            return redirect()->back()->withInput()->with('errors', [
                'archive_id' => 'Related archive record not found.',
            ]);
        }

        $approvalData = [
            'status' => 'Approved',
            'disposal_date' => date('Y-m-d'),
            'approved_by' => auth_user()['user_id'] ?? null,
        ];

        if (!$this->disposalModel->validateLifecycleState($approvalData)) {
            return redirect()->back()->withInput()->with('errors', $this->disposalModel->getLifecycleErrors());
        }

        if (!$this->disposalModel->update($disposalId, [
            'disposal_date' => $approvalData['disposal_date'],
            'approved_by' => $approvalData['approved_by'],
        ])) {
            return redirect()->back()->withInput()->with('errors',
                $this->disposalModel->errors() ?: ['general' => 'Failed to approve disposal request']
            );
        }

        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'], 'approve_disposal', "disposal_id:{$disposalId}");

        return redirect()->back()->with('success', 'Disposal request approved');
    }

    /**
     * List pending disposal requests
     */
    public function pendingDisposal()
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        // Get disposal records where disposal_date is NULL (not yet executed)
        $db = \Config\Database::connect();
        $pending = $db->table('disposal_records')
                     ->where('disposal_date IS NULL')
                     ->get()
                     ->getResultArray();

        return view('archive-disposal/disposal-pending', [
            'title' => 'Pending Disposal Requests',
            'pending' => $pending,
        ]);
    }

    /**
     * List completed disposals
     */
    public function completedDisposal()
    {
        $db = \Config\Database::connect();
        $completed = $db->table('disposal_records')
                       ->where('disposal_date IS NOT NULL')
                       ->get()
                       ->getResultArray();

        return view('archive-disposal/disposal-completed', [
            'title' => 'Completed Disposals',
            'completed' => $completed,
        ]);
    }

    /**
     * Archive a folder directly from Document Records list.
     */
    public function archiveFolderFromRecords(int $folderId)
    {
        if (!can('request_archive') && !can('archive_document')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Folder not found');
        }

        if ($folder['status'] === 'Archived') {
            return redirect()->back()->with('error', 'Folder is already archived');
        }

        if ($folder['status'] === 'Disposed') {
            return redirect()->back()->with('error', 'Disposed folders cannot be archived');
        }

        $archiveLocationId = (int) ($folder['location_id'] ?? 0);
        $db = \Config\Database::connect();
        $archiveLocation = $db->table('locations')->where('location_id', $archiveLocationId)->get()->getRowArray();
        if (!$archiveLocation) {
            return redirect()->back()->with('error', 'Folder location is invalid for archive record');
        }

        $db->transStart();

        $saved = $this->archiveModel->insert([
            'folder_id' => $folderId,
            'archived_date' => date('Y-m-d'),
            'archive_location_id' => $archiveLocationId,
            'archived_by' => auth_user()['user_id'] ?? null,
        ]);

        if (!$saved) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to create archive record');
        }

        $updated = $db->table('folders')
            ->where('folder_id', $folderId)
            ->update([
                'status' => 'Archived',
                'updated_by' => auth_user()['user_id'] ?? null,
            ]);

        if (!$updated) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to update folder status to Archived');
        }

        $db->transComplete();

        try {
            $auditLog = service('auditLog');
            $auditLog->log(auth_user()['user_id'], 'create_archive', "folder_id:{$folderId}");
        } catch (\Throwable $e) {
            log_message('error', 'Archive audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->to('/document-records')->with('success', 'Folder archived successfully');
    }

    /**
     * Restore archived folder back to Available status
     */
    public function restoreFolder(int $folderId)
    {
        if (!can('request_archive') && !can('archive_document')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Folder not found');
        }

        if ($folder['status'] !== 'Archived') {
            return redirect()->back()->with('error', 'Only archived folders can be restored');
        }

        $db = \Config\Database::connect();

        // Use direct table update to avoid full model validation for required create fields.
        $updated = $db->table('folders')
            ->where('folder_id', $folderId)
            ->update([
                'status' => 'Available',
                'updated_by' => auth_user()['user_id'] ?? null,
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to restore folder status');
        }

        // Do not block restore if audit table/service is unavailable.
        try {
            $auditLog = service('auditLog');
            $auditLog->log(auth_user()['user_id'], 'restore_folder', "folder_id:{$folderId}");
        } catch (\Throwable $e) {
            log_message('error', 'Restore audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->to('/archive')->with('success', 'Folder restored to Available status');
    }
}
