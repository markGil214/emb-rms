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

        if (!$this->validate($this->archiveModel->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $data = [
            'folder_id' => $this->request->getPost('folder_id'),
            'archived_date' => date('Y-m-d'),
            'archive_location_id' => $this->request->getPost('archive_location_id'),
            'retention_status' => 'Active',
            'archived_by' => auth_user()['user_id'],
        ];

        if (!$this->archiveModel->save($data)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create archive record');
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
        $archived = $db->table('archive_records')
                      ->where('retention_status', 'Active')
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

        if (!$this->validate($this->disposalModel->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'archive_id' => $this->request->getPost('archive_id'),
            'disposal_method' => $this->request->getPost('disposal_method'),
            'disposal_date' => null,  // Set when completed
            'compliance_reference' => $this->request->getPost('compliance_reference') ?? null,
            'approved_by' => auth_user()['user_id'],  // Current user approving
        ];

        if (!$this->disposalModel->save($data)) {
            return redirect()->back()->withInput()->with('error', 'Failed to create disposal record');
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
        $archive = $db->table('archive_records')->find($disposal['archive_id']);
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Set disposal date to today, marking it as completed/executed
        $this->disposalModel->update($disposalId, [
            'disposal_date' => date('Y-m-d'),
        ]);

        // Update archive retention status
        $db = \Config\Database::connect();
        $db->table('archive_records')->update($disposal['archive_id'], [
            'retention_status' => 'Inactive',
        ]);

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
}
