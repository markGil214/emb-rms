<?php

namespace App\Controllers;

use App\Models\ArchiveRecordModel;
use App\Models\DisposalRecordModel;
use App\Models\FileDisposalRequestModel;
use App\Models\FolderModel;
use App\Models\RestorationRequestModel;

class ArchiveDisposalController extends BaseController
{
    protected $archiveModel;
    protected $disposalModel;
    protected $fileDisposalRequestModel;
    protected $folderModel;
    protected $restorationRequestModel;

    public function __construct()
    {
        $this->archiveModel = new ArchiveRecordModel();
        $this->disposalModel = new DisposalRecordModel();
        $this->fileDisposalRequestModel = new FileDisposalRequestModel();
        $this->folderModel = new FolderModel();
        $this->restorationRequestModel = new RestorationRequestModel();
    }

    /**
     * Disposal management dashboard.
     */
    public function disposalIndex()
    {
        if (!can('request_disposal') && !can('approve_disposal')) {
            return redirect()->to('/dashboard')->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();

        // This dashboard is file-level disposal only (disposal_records/
        // folder-level counts live on the separate Archive Folders page).
        // Both the summary cards and $disposalRows below must describe the
        // same file_disposal_requests dataset.
        $fileDisposalPendingCount = 0;
        $fileDisposalApprovedCount = 0;
        $fileDisposalRejectedCount = 0;
        $fileDisposalDisposedCount = 0;
        if ($db->tableExists('file_disposal_requests')) {
            $fileDisposalPendingCount = (int) $db->table('file_disposal_requests')
                ->where('status', 'Pending')
                ->countAllResults();
            $fileDisposalApprovedCount = (int) $db->table('file_disposal_requests')
                ->where('status', 'Approved')
                ->countAllResults();
            $fileDisposalRejectedCount = (int) $db->table('file_disposal_requests')
                ->where('status', 'Rejected')
                ->countAllResults();
            $fileDisposalDisposedCount = (int) $db->table('file_disposal_requests')
                ->where('status', 'Disposed')
                ->countAllResults();
        }

        // Count files that are Ready to Dispose (expired but not yet submitted for disposal)
        $readyToDisposeCount = $this->countReadyToDisposeFiles();

        $disposalRows = $this->getDisposalDashboardRows();

        return view('archive-disposal/disposal-index', [
            'title' => 'Disposal Management',
            'fileDisposalPendingCount' => $fileDisposalPendingCount,
            'fileDisposalApprovedCount' => $fileDisposalApprovedCount,
            'fileDisposalRejectedCount' => $fileDisposalRejectedCount,
            'fileDisposalDisposedCount' => $fileDisposalDisposedCount,
            'readyToDisposeCount' => $readyToDisposeCount,
            'disposalRows' => $disposalRows,
        ]);
    }

    /**
     * Main archive/disposal dashboard
     */
    public function index()
    {
        $archiveDisposalRecords = $this->archiveModel->getArchiveDisposalRecords();
        $pendingRestorationsByFolder = $this->getPendingRestorationsByFolder();
        $workflowRequests = $this->getArchiveWorkflowRequests();
        $statusFilter = trim((string) ($this->request->getGet('status') ?? ''));
        $validStatusFilters = ['Archived', 'Pending Archive', 'Disposed'];

        foreach ($archiveDisposalRecords as &$record) {
            $record['current_status'] = $this->inferArchiveDisposalStatus($record);
        }
        unset($record);

        // Counts must reflect the full dataset, not whatever subset is left
        // after the status filter below narrows the display arrays --
        // otherwise every status other than the one being filtered on
        // collapses to zero and the summary cards vanish.
        $statusCounts = $this->getArchiveDisposalStatusCounts($archiveDisposalRecords);
        foreach ($workflowRequests as $request) {
            $requestStatus = (string) ($request['status'] ?? '');
            if (isset($statusCounts[$requestStatus])) {
                $statusCounts[$requestStatus]++;
            }
        }

        if ($statusFilter !== '') {
            if ($statusFilter === 'Archived' || $statusFilter === 'Disposed') {
                $archiveDisposalRecords = array_values(array_filter(
                    $archiveDisposalRecords,
                    static function (array $record) use ($statusFilter): bool {
                        return ($record['current_status'] ?? '') === $statusFilter;
                    }
                ));
                $workflowRequests = [];
            } elseif ($statusFilter === 'Pending Archive') {
                $workflowRequests = array_values(array_filter(
                    $workflowRequests,
                    static function (array $request) use ($statusFilter): bool {
                        return ($request['status'] ?? '') === $statusFilter;
                    }
                ));
                $archiveDisposalRecords = [];
            } else {
                $statusFilter = '';
            }
        }

        return view('archive-disposal/index', [
            'title' => 'Archive & Disposal Management',
            'archiveDisposalRecords' => $archiveDisposalRecords,
            'archived' => $archiveDisposalRecords,
            'pendingRestorationsByFolder' => $pendingRestorationsByFolder,
            'workflowRequests' => $workflowRequests,
            'statusCounts' => $statusCounts,
            'statusFilter' => $statusFilter,
            'totalArchived' => $statusCounts['Archived'],
            'totalDisposalPending' => $statusCounts['Pending Disposal'],
            'totalDisposalApproved' => $statusCounts['Approved for Disposal'],
            'totalDisposed' => $statusCounts['Disposed'],
        ]);
    }

    // ===== ARCHIVE OPERATIONS =====

    /**
     * Show form to archive a folder
     */
    public function createArchive()
    {
        if (!can('approve_archive')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folders = $this->folderModel->where('status !=', 'Archived')
                         ->where('status !=', 'Archival')
                         ->where('status !=', 'Disposed')
                         ->where('status !=', 'Borrowed')
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
        if (!can('approve_archive')) {
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

        // Prevent archival of borrowed records
        if (in_array(($folder['status'] ?? ''), ['Borrowed', 'Archival'], true)) {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => 'Cannot archive this record. Please change its status before archiving.',
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
        $auditLog->log('approve_archive', 'folder', (int) $data['folder_id'], null, null, auth_user()['user_id']);

        $db->transComplete();

        return redirect()->to(route_to('archive.index'))->with('success', 'Document archived successfully');
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
     * View disposal request details
     */
    public function showDisposal(int $disposalId)
    {
        $disposal = $this->getDisposalDetails($disposalId);

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
     * Approve a disposal request without executing disposal.
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

        if ($this->disposalModel->inferStatus($disposal) !== 'Pending Disposal') {
            return redirect()->back()->withInput()->with('errors', [
                'status' => 'Only pending disposal requests can be approved.',
            ]);
        }

        $archive = $this->archiveModel->find((int) $disposal['archive_id']);
        if (!$archive) {
            return redirect()->back()->withInput()->with('errors', [
                'archive_id' => 'Related archive record not found.',
            ]);
        }

        $approvalData = [
            'status' => 'Approved for Disposal',
            'disposal_date' => null,
            'approved_by' => auth_user()['user_id'] ?? null,
        ];

        if (!$this->disposalModel->validateLifecycleState($approvalData)) {
            return redirect()->back()->withInput()->with('errors', $this->disposalModel->getLifecycleErrors());
        }

        if (!$this->disposalModel->update($disposalId, [
            'approved_by' => $approvalData['approved_by'],
        ])) {
            return redirect()->back()->withInput()->with('errors',
                $this->disposalModel->errors() ?: ['general' => 'Failed to approve disposal request']
            );
        }

        $auditLog = service('auditLog');
        $auditLog->log('approve_disposal', 'disposal', (int) $disposalId, null, null, auth_user()['user_id']);

        return redirect()->back()->with('success', 'Disposal request approved');
    }

    /**
     * Complete an approved disposal and mark the folder disposed.
     */
    public function completeDisposal(int $disposalId)
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $disposal = $this->disposalModel->find($disposalId);
        if (!$disposal) {
            return redirect()->back()->with('error', 'Disposal request not found.');
        }

        if ($this->disposalModel->inferStatus($disposal) !== 'Approved for Disposal') {
            return redirect()->back()->with('error', 'Only approved disposal requests can be marked disposed.');
        }

        $archive = $this->archiveModel->find((int) $disposal['archive_id']);
        if (!$archive) {
            return redirect()->back()->with('error', 'Related archive record not found.');
        }

        $completionData = [
            'status' => 'Disposed',
            'disposal_date' => date('Y-m-d'),
            'approved_by' => $disposal['approved_by'] ?? null,
        ];

        if (!$this->disposalModel->validateLifecycleState($completionData)) {
            return redirect()->back()->withInput()->with('errors', $this->disposalModel->getLifecycleErrors());
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        $disposalUpdated = $this->disposalModel->update($disposalId, [
            'disposal_date' => $completionData['disposal_date'],
        ]);

        $folderUpdated = $this->folderModel->update((int) $archive['folder_id'], [
            'status' => 'Disposed',
            'updated_by' => auth_user()['user_id'] ?? null,
        ]);

        if (!$disposalUpdated || !$folderUpdated) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to complete disposal.');
        }

        $db->transCommit();

        try {
            $auditLog = service('auditLog');
            $auditLog->log('complete_disposal', 'disposal', (int) $disposalId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'Disposal completion audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Disposal marked as disposed');
    }

    /**
     * Reject a pending disposal request.
     */
    public function rejectDisposal(int $disposalId)
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $disposal = $this->disposalModel->find($disposalId);
        if (!$disposal) {
            return redirect()->back()->with('error', 'Disposal request not found.');
        }

        if ($this->disposalModel->inferStatus($disposal) !== 'Pending Disposal') {
            return redirect()->back()->with('error', 'Only pending disposal requests can be rejected.');
        }

        // Mark the disposal request as Rejected instead of deleting
        $updated = $this->disposalModel->update($disposalId, [
            'status' => 'Rejected',
        ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to reject disposal request.');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('reject_disposal', 'disposal', (int) $disposalId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'Disposal rejection audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Disposal request rejected');
    }

    /**
     * List pending disposal and restoration requests.
     */
    public function pendingDisposal()
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        $pending = $db->table('file_disposal_requests fdr')
            ->select('fdr.disposal_request_id, fdr.file_id, fdr.status, fdr.requested_at, fdr.approved_at, fdr.disposed_at, fdr.requested_by, fdr.approved_by, ff.file_name, ff.folder_id, f.file_code, f.company_name, requester.username as requested_by_name, approver.username as approved_by_name', false)
            ->join('folder_files ff', 'ff.file_id = fdr.file_id', 'left')
            ->join('folders f', 'f.folder_id = ff.folder_id', 'left')
            ->join('users requester', 'requester.user_id = fdr.requested_by', 'left')
            ->join('users approver', 'approver.user_id = fdr.approved_by', 'left')
            ->whereIn('fdr.status', ['Pending', 'Approved'])
            ->orderBy('fdr.requested_at', 'DESC')
            ->get()
            ->getResultArray();

        $rows = [];
        foreach ($pending as $request) {
            $fileLabel = $request['file_name'] ?: 'File #' . $request['file_id'];
            $folderLabel = trim(($request['file_code'] ?? '') . ' ' . ($request['company_name'] ?? ''));
            $status = ($request['status'] ?? '') === 'Approved' ? 'Approved for Disposal' : 'Pending Disposal';

            $rows[] = [
                'request_type' => 'File Disposal',
                'request_id' => $request['disposal_request_id'],
                'subject' => $folderLabel ? $fileLabel . ' (' . $folderLabel . ')' : $fileLabel,
                'method' => 'File Disposed',
                'status' => $status,
                'requested_at' => $request['requested_at'] ?? null,
                'requested_by' => $request['requested_by_name'] ?? $request['requested_by'] ?? '-',
                'approved_by' => $request['approved_by_name'] ?? '-',
                'view_route' => !empty($request['folder_id']) ? 'records.show' : null,
                'view_id' => $request['folder_id'] ?? null,
                'approve_route' => ($request['status'] ?? '') === 'Pending' ? 'file-disposal.approve' : null,
                'decline_route' => ($request['status'] ?? '') === 'Pending' ? 'file-disposal.reject' : null,
                'complete_route' => ($request['status'] ?? '') === 'Approved' ? 'file-disposal.complete' : null,
                'route_id' => $request['disposal_request_id'],
                'action_label' => ($request['status'] ?? '') === 'Pending' ? 'Approve' : (($request['status'] ?? '') === 'Approved' ? 'Complete' : null),
                'decline_label' => ($request['status'] ?? '') === 'Pending' ? 'Decline' : null,
                'confirm_message' => ($request['status'] ?? '') === 'Approved' ? 'Mark this file as disposed?' : 'Approve this disposal request?',
                'decline_confirm_message' => 'Reject this disposal request?',
                'fallback_action_label' => null,
            ];
        }

        return view('archive-disposal/disposal-pending', [
            'title' => 'File Disposal Approvals',
            'pending' => $rows,
        ]);
    }

    /**
     * Approve a pending file disposal request.
     */
    public function approveFileDisposal(int $requestId)
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('file_disposal_requests')) {
            return redirect()->back()->with('error', 'File Disposal workflow table is not ready yet. Please run database migrations first.');
        }

        $request = $this->fileDisposalRequestModel->find($requestId);
        if (!$request) {
            return redirect()->back()->with('error', 'File Disposal request not found.');
        }

        if (($request['status'] ?? '') !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending file disposal requests can be approved.');
        }

        $updated = $db->table('file_disposal_requests')
            ->where('disposal_request_id', $requestId)
            ->where('status', 'Pending')
            ->update([
                'status' => 'Approved',
                'approved_by' => auth_user()['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to approve file disposal request.');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('approve_file_disposal', 'file_disposal_request', (int) $requestId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'File Disposal approval audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'File Disposal request approved');
    }

    /**
     * Reject a pending file disposal request.
     */
    public function rejectFileDisposal(int $requestId)
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('file_disposal_requests')) {
            return redirect()->back()->with('error', 'File Disposal workflow table is not ready yet.');
        }

        $request = $this->fileDisposalRequestModel->find($requestId);
        if (!$request) {
            return redirect()->back()->with('error', 'File Disposal request not found.');
        }

        if (($request['status'] ?? '') !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending file disposal requests can be rejected.');
        }

        $updated = $db->table('file_disposal_requests')
            ->where('disposal_request_id', $requestId)
            ->where('status', 'Pending')
            ->update([
                'status' => 'Rejected',
                'approved_by' => auth_user()['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to reject file disposal request.');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('reject_file_disposal', 'file_disposal_request', (int) $requestId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'File Disposal rejection audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'File Disposal request rejected');
    }

    /**
     * Complete an approved file disposal request.
     */
    public function completeFileDisposal(int $requestId)
    {
        if (!can('approve_disposal')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('file_disposal_requests')) {
            return redirect()->back()->with('error', 'File Disposal workflow table is not ready yet.');
        }

        $request = $this->fileDisposalRequestModel->find($requestId);
        if (!$request) {
            return redirect()->back()->with('error', 'File Disposal request not found.');
        }

        if (($request['status'] ?? '') !== 'Approved') {
            return redirect()->back()->with('error', 'Only approved file disposal requests can be completed.');
        }

        $updated = $db->table('file_disposal_requests')
            ->where('disposal_request_id', $requestId)
            ->where('status', 'Approved')
            ->update([
                'status' => 'Disposed',
                'disposed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to complete file disposal.');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('complete_file_disposal', 'file_disposal_request', (int) $requestId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'File Disposal completion audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'File Disposal marked as disposed');
    }

    /**
     * List completed disposals
     */
    public function completedDisposal()
    {
        $db = \Config\Database::connect();
        $completed = [];

        $archiveCompleted = $db->table('disposal_records')
            ->where('disposal_date IS NOT NULL')
            ->get()
            ->getResultArray();

        foreach ($archiveCompleted as $disposal) {
            $completed[] = [
                'request_type' => 'Archive',
                'request_id' => $disposal['disposal_id'],
                'subject' => 'Archive #' . ($disposal['archive_id'] ?? '-'),
                'method' => $disposal['disposal_method'] ?? '-',
                'status' => 'Disposed',
                'requested_at' => $disposal['created_at'] ?? null,
                'completed_at' => $disposal['disposal_date'] ?? null,
                'view_route' => 'disposal.show',
                'route_id' => $disposal['disposal_id'],
            ];
        }

        if ($db->tableExists('file_disposal_requests')) {
            $fileCompleted = $db->table('file_disposal_requests fdr')
                    ->select('fdr.disposal_request_id, fdr.file_id, fdr.status, fdr.requested_at, fdr.created_at, fdr.approved_at, fdr.disposed_at, ff.file_name, ff.folder_id, f.file_code, f.company_name')
                    ->join('folder_files ff', 'ff.file_id = fdr.file_id', 'left')
                    ->join('folders f', 'f.folder_id = ff.folder_id', 'left')
                    ->where('fdr.status', 'Disposed')
                    ->orderBy('COALESCE(fdr.disposed_at, fdr.approved_at, fdr.updated_at)', 'DESC', false)
                    ->get()
                ->getResultArray();

            foreach ($fileCompleted as $disposal) {
                $fileLabel = $disposal['file_name'] ?: 'File #' . $disposal['file_id'];
                $folderLabel = trim(($disposal['file_code'] ?? '') . ' ' . ($disposal['company_name'] ?? ''));

                $completed[] = [
                    'request_type' => 'File',
                    'request_id' => $disposal['disposal_request_id'],
                    'subject' => $folderLabel ? $fileLabel . ' (' . $folderLabel . ')' : $fileLabel,
                    'method' => 'File Disposed',
                    'status' => $disposal['status'] ?? 'Disposed',
                    'requested_at' => $disposal['requested_at'] ?? $disposal['created_at'] ?? null,
                    'completed_at' => $disposal['disposed_at'] ?? $disposal['approved_at'] ?? null,
                    'view_route' => !empty($disposal['folder_id']) ? 'records.show' : null,
                    'route_id' => $disposal['folder_id'] ?? null,
                ];
            }
        }

        usort($completed, static function (array $left, array $right): int {
            return strtotime((string) ($right['completed_at'] ?? '')) <=> strtotime((string) ($left['completed_at'] ?? ''));
        });

        return view('archive-disposal/disposal-completed', [
            'title' => 'Disposed Records',
            'completed' => $completed,
        ]);
    }

    /**
     * Submit a folder archive request from Document Records.
     */
    public function archiveFolderFromRecords(int $folderId)
    {
        if (!can('approve_archive')) {
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

        if (in_array($folder['status'] ?? '', ['Pending', 'Pending Update', 'Pending Archive'], true)) {
            return redirect()->back()->with('error', 'This folder already has a pending request');
        }

        if (!$this->folderModel->update($folderId, [
            'status' => 'Pending Archive',
            'updated_by' => auth_user()['user_id'] ?? null,
        ])) {
            return redirect()->back()->with('error', 'Failed to submit archive request');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('approve_archive', 'folder', (int) $folderId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'Archive request audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->to('/document-records')->with('success', 'Archive request submitted for approval');
    }

    /**
     * Approve a pending folder archive request.
     */
    public function approveArchiveRequest(int $folderId)
    {
        if (!can('approve_archive')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Folder not found');
        }

        if (($folder['status'] ?? '') !== 'Pending Archive') {
            return redirect()->back()->with('error', 'Only pending archive requests can be approved');
        }

        $archiveLocationId = (int) ($folder['location_id'] ?? 0);
        $db = \Config\Database::connect();
        $archiveLocation = $db->table('locations')->where('location_id', $archiveLocationId)->get()->getRowArray();
        if (!$archiveLocation) {
            return redirect()->back()->with('error', 'Folder location is invalid for archive record');
        }

        $db->transBegin();

        $saved = $this->archiveModel->insert([
            'folder_id' => $folderId,
            'archived_date' => date('Y-m-d'),
            'archive_location_id' => $archiveLocationId,
            'archived_by' => auth_user()['user_id'] ?? null,
        ]);

        $updated = $this->folderModel->update($folderId, [
            'status' => 'Archived',
            'updated_by' => auth_user()['user_id'] ?? null,
        ]);

        if (!$saved || !$updated) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to approve archive request');
        }

        $db->transCommit();

        try {
            $auditLog = service('auditLog');
            $auditLog->log('approve_archive', 'folder', (int) $folderId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'Archive approval audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Archive request approved');
    }

    /**
     * Decline a pending folder archive request.
     */
    public function declineArchiveRequest(int $folderId)
    {
        if (!can('approve_archive')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Folder not found');
        }

        if (($folder['status'] ?? '') !== 'Pending Archive') {
            return redirect()->back()->with('error', 'Only pending archive requests can be declined');
        }

        $restoredStatus = !empty($folder['current_borrow_transaction_id']) ? 'Borrowed' : 'Available';
        if (!$this->folderModel->update($folderId, [
            'status' => $restoredStatus,
            'updated_by' => auth_user()['user_id'] ?? null,
        ])) {
            return redirect()->back()->with('error', 'Failed to decline archive request');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('decline_archive', 'folder', (int) $folderId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'Archive decline audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Archive request declined');
    }

    /**
     * Request restoration for an archived folder.
     */
    public function requestRestoration(int $folderId)
    {
        if (!can('request_restore')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('restoration_requests')) {
            return redirect()->back()->with('error', 'Restoration workflow table is not ready yet. Please run database migrations first.');
        }

        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Folder not found');
        }

        if ($folder['status'] !== 'Archived') {
            return redirect()->back()->with('error', 'Only archived folders can be requested for restoration');
        }

        if ($this->restorationRequestModel->pendingForFolder($folderId)) {
            return redirect()->back()->with('error', 'A restoration request is already pending for this folder');
        }

        $archive = $db->table('archive_records')
            ->where('folder_id', $folderId)
            ->orderBy('archive_id', 'DESC')
            ->get()
            ->getRowArray();

        $saved = $this->restorationRequestModel->insert([
            'folder_id' => $folderId,
            'archive_id' => $archive['archive_id'] ?? null,
            'status' => 'Pending',
            'requested_by' => auth_user()['user_id'] ?? null,
            'requested_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            return redirect()->back()->with('error', 'Failed to create restoration request');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('request_restore', 'folder', (int) $folderId, null, null, auth_user()['user_id']);
        } catch (\Throwable $e) {
            log_message('error', 'Restoration request audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->to(route_to('archive.index'))->with('success', 'Restoration request submitted for approval');
    }

    /**
     * Backward-compatible route target for older restore links.
     */
    public function restoreFolder(int $folderId)
    {
        return $this->requestRestoration($folderId);
    }

    /**
     * Approve a pending restoration request and restore the folder.
     */
    public function approveRestoration(int $requestId)
    {
        if (!can('approve_restore')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('restoration_requests')) {
            return redirect()->back()->with('error', 'Restoration workflow table is not ready yet. Please run database migrations first.');
        }

        $request = $this->restorationRequestModel->find($requestId);
        if (!$request) {
            return redirect()->back()->with('error', 'Restoration request not found');
        }

        if (($request['status'] ?? '') !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending restoration requests can be approved');
        }

        $folderId = (int) $request['folder_id'];
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Folder not found');
        }

        if ($folder['status'] !== 'Archived') {
            return redirect()->back()->with('error', 'Only archived folders can be restored');
        }

        $db->transBegin();

        $folderUpdated = $db->table('folders')
            ->where('folder_id', $folderId)
            ->update([
                'status' => 'Available',
                'updated_by' => auth_user()['user_id'] ?? null,
            ]);

        $requestUpdated = $db->table('restoration_requests')
            ->where('restoration_request_id', $requestId)
            ->where('status', 'Pending')
            ->update([
                'status' => 'Approved',
                'approved_by' => auth_user()['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

        if (!$folderUpdated || !$requestUpdated) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to approve restoration request');
        }

        $db->transCommit();

        try {
            $auditLog = service('auditLog');
            $auditLog->log('approve_restore', 'restoration_request', (int) $requestId, null, null, auth_user()['user_id']);
        } catch (\Throwable $e) {
            log_message('error', 'Restoration approval audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Restoration request approved');
    }

    /**
     * Reject a pending restoration request and keep the folder archived.
     */
    public function rejectRestoration(int $requestId)
    {
        if (!can('approve_restore')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('restoration_requests')) {
            return redirect()->back()->with('error', 'Restoration workflow table is not ready yet. Please run database migrations first.');
        }

        $request = $this->restorationRequestModel->find($requestId);
        if (!$request) {
            return redirect()->back()->with('error', 'Restoration request not found');
        }

        if (($request['status'] ?? '') !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending restoration requests can be rejected');
        }

        $requestUpdated = $db->table('restoration_requests')
            ->where('restoration_request_id', $requestId)
            ->where('status', 'Pending')
            ->update([
                'status' => 'Rejected',
                'approved_by' => auth_user()['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

        if (!$requestUpdated) {
            return redirect()->back()->with('error', 'Failed to reject restoration request');
        }

        try {
            $auditLog = service('auditLog');
            $auditLog->log('reject_restore', 'restoration_request', (int) $requestId, null, null, auth_user()['user_id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'Restoration rejection audit logging failed: {message}', ['message' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Restoration request rejected');
    }

    private function getArchiveDisposalStatusCounts(array $records): array
    {
        $counts = [
            'Archived' => 0,
            'Pending Archive' => 0,
            'Pending Disposal' => 0,
            'Approved for Disposal' => 0,
            'Disposed' => 0,
            'Rejected' => 0,
        ];

        foreach ($records as $record) {
            $status = $record['current_status'] ?? $this->inferArchiveDisposalStatus($record);
            if (!array_key_exists($status, $counts)) {
                continue;
            }

            $counts[$status]++;
        }

        return $counts;
    }

    private function inferArchiveDisposalStatus(array $record): string
    {
        if (($record['folder_status'] ?? '') === 'Pending Archive') {
            return 'Pending Archive';
        }

        if (($record['folder_status'] ?? '') === 'Disposed') {
            return 'Disposed';
        }

        // Check for disposal record
        if (!empty($record['disposal_id'])) {
            $status = (string) ($record['status'] ?? '');
            if ($status === 'Rejected') {
                return 'Rejected';
            }
            if (!empty($record['disposal_date'])) {
                return 'Disposed';
            }
            if (!empty($record['approved_by'])) {
                return 'Approved for Disposal';
            }
            return 'Pending Disposal';
        }

        return 'Archived';
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists($table)) {
            return false;
        }

        return in_array($column, $db->getFieldNames($table), true);
    }

    private function getDisposalDetails(int $disposalId): ?array
    {
        $db = \Config\Database::connect();
        $hasDisposalRequester = $this->tableHasColumn('disposal_records', 'requested_by');

        $builder = $db->table('disposal_records dr')
            ->select('dr.*, approver.username as approved_by_name', false)
            ->join('users approver', 'approver.user_id = dr.approved_by', 'left')
            ->where('dr.disposal_id', $disposalId);

        if ($hasDisposalRequester) {
            $builder
                ->select('requester.username as requested_by_name', false)
                ->join('users requester', 'requester.user_id = dr.requested_by', 'left');
        } else {
            $builder->select('NULL as requested_by, NULL as requested_by_name', false);
        }

        $record = $builder->get()->getRowArray();
        if (!$record) {
            return null;
        }

        $record['status'] = $this->disposalModel->inferStatus($record);

        return $record;
    }

    private function getPendingRestorationsByFolder(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('restoration_requests')) {
            return [];
        }

        $requests = $db->table('restoration_requests rr')
            ->select('rr.restoration_request_id, rr.folder_id, 
                CASE WHEN u.first_name IS NOT NULL AND u.first_name != "" THEN CONCAT(u.first_name, " ", u.last_name) ELSE u.username END as requested_by_name')
            ->join('users u', 'u.user_id = rr.requested_by', 'left')
            ->where('rr.status', 'Pending')
            ->orderBy('rr.restoration_request_id', 'DESC')
            ->get()
            ->getResultArray();

        $byFolder = [];
        foreach ($requests as $request) {
            $folderId = (int) $request['folder_id'];
            if (!isset($byFolder[$folderId])) {
                $byFolder[$folderId] = $request;
            }
        }

        return $byFolder;
    }

    private function getArchiveWorkflowRequests(): array
    {
        $db = \Config\Database::connect();
        $requests = [];
        $canReviewArchive = can('approve_archive') || is_super_admin();

        // Restoration requests are shown on the archive row itself via getPendingRestorationsByFolder().
        // Keep them out of the standalone workflow list to avoid duplicate pending entries.

        if ($canReviewArchive) {
            $pendingArchiveFolders = $db->table('folders f')
                ->select('f.folder_id, f.file_code, f.company_name, f.status, f.updated_by, f.updated_at, 
                    CASE WHEN u.first_name IS NOT NULL AND u.first_name != "" THEN CONCAT(u.first_name, " ", u.last_name) ELSE u.username END as full_name')
                ->join('users u', 'u.user_id = f.updated_by', 'left')
                ->where('f.status', 'Pending Archive')
                ->orderBy('f.updated_at', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($pendingArchiveFolders as $folder) {
                $folderLabel = trim(($folder['file_code'] ?? '') . ' ' . ($folder['company_name'] ?? ''));

                $requests[] = [
                    'request_type' => 'Archive',
                    'request_id' => $folder['folder_id'],
                    'subject' => $folderLabel ?: 'Folder #' . $folder['folder_id'],
                    'method' => 'Archive folder',
                    'status' => 'Pending Archive',
                    'requested_at' => $folder['updated_at'] ?? null,
                    'requested_by' => $folder['full_name'] ?? $folder['updated_by'] ?? '-',
                    'view_route' => 'records.show',
                    'view_id' => $folder['folder_id'],
                    'approve_route' => 'archive-request.approve',
                    'decline_route' => 'archive-request.decline',
                    'route_id' => $folder['folder_id'],
                    'action_label' => 'Approve Archive',
                    'decline_label' => 'Reject Archive',
                    'confirm_message' => 'Approve this archive request?',
                    'decline_confirm_message' => 'Reject this archive request?',
                    'workflow_stage' => 'pending-archive',
                    'archive_status' => 'Pending Archive',
                    'approved_by' => '-',
                ];
            }
        }

        usort($requests, static function (array $left, array $right): int {
            return strtotime((string) ($right['requested_at'] ?? '')) <=> strtotime((string) ($left['requested_at'] ?? ''));
        });

        return $requests;
    }

    private function getFileDisposalCount($statuses): int
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('file_disposal_requests')) {
            return 0;
        }

        $builder = $db->table('file_disposal_requests');
        if (is_array($statuses)) {
            $builder->whereIn('status', $statuses);
        } else {
            $builder->where('status', $statuses);
        }

        return $builder->countAllResults();
    }

    private function getDisposalDashboardRows(): array
    {
        $db = \Config\Database::connect();
        $rows = [];

        // ---- 1. Ready to Dispose: expired files not yet submitted for disposal ----
        $expiredFiles = $this->getReadyToDisposeFiles();
        foreach ($expiredFiles as $file) {
            $fileLabel = $file['file_name'] ?: 'File #' . $file['file_id'];
            $folderLabel = trim((string) (($file['file_code'] ?? '') . ' ' . ($file['company_name'] ?? '')));
            $subject = $folderLabel !== '' ? $fileLabel . ' (' . $folderLabel . ')' : $fileLabel;
            $location = 'Rack ' . ($file['rack'] ?? '-') . ' - Shelf ' . ($file['shelf'] ?? '-');

            $rows[] = [
                'subject' => $subject,
                'request_type' => 'File Expiration',
                'folder_type' => $file['folder_type'] ?? '-',
                'location' => $location,
                'status' => 'Ready to Dispose',
                'method' => 'Expired (' . ($file['expiration_date'] ?? '-') . ')',
                'requested_at' => $file['expiration_date'] ?? null,
                'disposed_at' => null,
                'requested_by' => $file['uploaded_by_name'] ?? '-',
                'approved_by' => '-',
                'view_route' => !empty($file['folder_id']) ? 'records.show' : null,
                'view_id' => $file['folder_id'] ?? null,
                'approve_route' => null,
                'approve_id' => null,
                'decline_route' => null,
                'decline_id' => null,
                'confirm_message' => null,
                'decline_confirm_message' => null,
                'file_id' => $file['file_id'] ?? null,
                'expiration_date' => $file['expiration_date'] ?? null,
            ];
        }

        // ---- 2. Archive disposal records (REMOVED: FILE DISPOSAL ONLY) ----

        // ---- 3. File disposal requests ----
        if ($db->tableExists('file_disposal_requests')) {
            $fileRequests = $db->table('file_disposal_requests fdr')
                ->select('fdr.disposal_request_id, fdr.file_id, fdr.status, fdr.requested_at, fdr.created_at, fdr.approved_at, fdr.disposed_at, fdr.requested_by, fdr.approved_by, ff.file_name, ff.folder_id, f.file_code, f.company_name, f.folder_type, l.rack, l.shelf, requester.username as requested_by_name, approver.username as approved_by_name', false)
                ->join('folder_files ff', 'ff.file_id = fdr.file_id', 'left')
                ->join('folders f', 'f.folder_id = ff.folder_id', 'left')
                ->join('locations l', 'l.location_id = f.location_id', 'left')
                ->join('users requester', 'requester.user_id = fdr.requested_by', 'left')
                ->join('users approver', 'approver.user_id = fdr.approved_by', 'left')
                ->orderBy('COALESCE(fdr.requested_at, fdr.created_at)', 'DESC', false)
                ->get()
                ->getResultArray();

            foreach ($fileRequests as $request) {
                $fileLabel = $request['file_name'] ?: 'File #' . $request['file_id'];
                $folderLabel = trim((string) (($request['file_code'] ?? '') . ' ' . ($request['company_name'] ?? '')));
                $subject = $folderLabel !== '' ? $fileLabel . ' (' . $folderLabel . ')' : $fileLabel;
                $location = 'Rack ' . ($request['rack'] ?? '-') . ' - Shelf ' . ($request['shelf'] ?? '-');

                $status = 'Pending Disposal';
                if (($request['status'] ?? '') === 'Approved') {
                    $status = 'Approved for Disposal';
                } elseif (($request['status'] ?? '') === 'Disposed') {
                    $status = 'Disposed';
                } elseif (($request['status'] ?? '') === 'Rejected') {
                    $status = 'Rejected';
                }

                $rows[] = [
                    'subject' => $subject,
                    'request_type' => 'File Disposal',
                    'folder_type' => $request['folder_type'] ?? '-',
                    'location' => $location,
                    'status' => $status,
                    'method' => 'File Disposed',
                    'requested_at' => $request['requested_at'] ?? $request['created_at'] ?? null,
                    'disposed_at' => $request['disposed_at'] ?? null,
                    'requested_by' => $request['requested_by_name'] ?? $request['requested_by'] ?? '-',
                    'approved_by' => $request['approved_by_name'] ?? $request['approved_by'] ?? '-',
                    'view_route' => !empty($request['folder_id']) ? 'records.show' : null,
                    'view_id' => $request['folder_id'] ?? null,
                    'approve_route' => ($request['status'] ?? '') === 'Pending' ? 'file-disposal.approve' : null,
                    'approve_id' => ($request['status'] ?? '') === 'Pending' ? ($request['disposal_request_id'] ?? null) : null,
                    'decline_route' => ($request['status'] ?? '') === 'Pending' ? 'file-disposal.reject' : null,
                    'decline_id' => ($request['status'] ?? '') === 'Pending' ? ($request['disposal_request_id'] ?? null) : null,
                    'complete_route' => ($request['status'] ?? '') === 'Approved' ? 'file-disposal.complete' : null,
                    'complete_id' => ($request['status'] ?? '') === 'Approved' ? ($request['disposal_request_id'] ?? null) : null,
                    'confirm_message' => ($request['status'] ?? '') === 'Approved' ? 'Mark this file as disposed?' : 'Approve this disposal request?',
                    'decline_confirm_message' => 'Reject this disposal request?',
                    'file_id' => $request['file_id'] ?? null,
                ];
            }
        }

        // Sort: "Ready to Dispose" first, then by date
        usort($rows, static function (array $left, array $right): int {
            $leftReady = ($left['status'] ?? '') === 'Ready to Dispose' ? 1 : 0;
            $rightReady = ($right['status'] ?? '') === 'Ready to Dispose' ? 1 : 0;
            if ($leftReady !== $rightReady) {
                return $rightReady <=> $leftReady;
            }

            $leftRequested = strtotime((string) ($left['requested_at'] ?? '')) ?: 0;
            $rightRequested = strtotime((string) ($right['requested_at'] ?? '')) ?: 0;

            if ($rightRequested === $leftRequested) {
                $leftDisposed = strtotime((string) ($left['disposed_at'] ?? '')) ?: 0;
                $rightDisposed = strtotime((string) ($right['disposed_at'] ?? '')) ?: 0;
                return $rightDisposed <=> $leftDisposed;
            }

            return $rightRequested <=> $leftRequested;
        });

        return $rows;
    }

    /**
     * Get files from folder_files that have expired (expiration_date <= today)
     * and have NOT yet been submitted for disposal via file_disposal_requests.
     */
    private function getReadyToDisposeFiles(): array
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        $hasExpirationColumn = in_array('expiration_date', $db->getFieldNames('folder_files'), true);
        if (!$hasExpirationColumn) {
            return [];
        }

        $builder = $db->table('folder_files ff')
            ->select('ff.file_id, ff.folder_id, ff.file_name, ff.file_path, ff.file_size, ff.uploaded_by, ff.retention_type, ff.expiration_date, ff.created_at, f.file_code, f.company_name, f.folder_type, l.rack, l.shelf, u.username as uploaded_by_name', false)
            ->join('folders f', 'f.folder_id = ff.folder_id', 'left')
            ->join('locations l', 'l.location_id = f.location_id', 'left')
            ->join('users u', 'u.user_id = ff.uploaded_by', 'left')
            ->where('ff.retention_type', 'expiration')
            ->where('ff.expiration_date IS NOT NULL')
            ->where('ff.expiration_date <=', $today);

        // Exclude files that already have an active/completed disposal request
        if ($db->tableExists('file_disposal_requests')) {
            $builder->where('NOT EXISTS (
                SELECT 1 FROM file_disposal_requests fdr
                WHERE fdr.file_id = ff.file_id
                AND fdr.status IN ("Pending", "Approved", "Disposed")
            )', null, false);
        }

        return $builder
            ->orderBy('ff.expiration_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Count files ready to dispose (expired, not yet submitted for disposal).
     */
    private function countReadyToDisposeFiles(): int
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        $hasExpirationColumn = in_array('expiration_date', $db->getFieldNames('folder_files'), true);
        if (!$hasExpirationColumn) {
            return 0;
        }

        $builder = $db->table('folder_files ff')
            ->where('ff.retention_type', 'expiration')
            ->where('ff.expiration_date IS NOT NULL')
            ->where('ff.expiration_date <=', $today);

        if ($db->tableExists('file_disposal_requests')) {
            $builder->where('NOT EXISTS (
                SELECT 1 FROM file_disposal_requests fdr
                WHERE fdr.file_id = ff.file_id
                AND fdr.status IN ("Pending", "Approved", "Disposed")
            )', null, false);
        }

        return (int) $builder->countAllResults();
    }
}
