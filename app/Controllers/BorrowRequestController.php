<?php

namespace App\Controllers;

use App\Models\BorrowTransactionModel;
use App\Models\FolderModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class BorrowRequestController extends BaseController
{
    protected $borrowModel;
    protected $folderModel;
    protected $userModel;

    public function __construct()
    {
        $this->borrowModel = new BorrowTransactionModel();
        $this->folderModel = new FolderModel();
        $this->userModel = new UserModel();
    }

    /**
     * List all borrow requests
     */
    public function index()
    {
        $userId = auth_user()['user_id'];

        // Permission-based filtering: Users with approve_borrow_requests see everything
        // Users with only view_borrow see only their records
        if (!can('approve_borrow_requests')) {
            $borrows = $this->borrowModel->getUserBorrows($userId);
        } else {
            $borrows = $this->borrowModel->getAllByLatestActivity();
        }

        // Calculate dynamic status for each borrow
        foreach ($borrows as &$borrow) {
            $borrow['status'] = $this->borrowModel->calculateStatus($borrow);
        }

        // Build folder lookup for readable folder display in list view
        $folderMap = [];
        $folderIds = array_values(array_unique(array_filter(array_column($borrows, 'folder_id'))));
        if (!empty($folderIds)) {
            $folders = $this->folderModel->whereIn('folder_id', $folderIds)->findAll();
            foreach ($folders as $folder) {
                $folderMap[$folder['folder_id']] = [
                    'file_code' => $folder['file_code'] ?? '',
                    'company_name' => $folder['company_name'] ?? '',
                ];
            }
        }

        return view('borrow/index', [
            'title' => 'Borrow Management',
            'borrows' => $borrows,
            'folderMap' => $folderMap,
            'totalPending' => count(array_filter($borrows, function($b) { return $b['status'] === 'Pending'; })),
            'totalBorrowed' => count(array_filter($borrows, function($b) { return $b['status'] === 'Borrowed'; })),
            'totalOverdue' => count(array_filter($borrows, function($b) { return $this->borrowModel->isOverdue($b); })),
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Check permission
        if (!can('request_borrow')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to request borrows');
        }

        $folders = $this->folderModel->where('status', 'Available')->findAll();

        return view('borrow/create', [
            'title' => 'Request Borrow',
            'folders' => $folders,
        ]);
    }

    /**
     * Store new borrow request
     */
    public function store()
    {
        if (!can('request_borrow')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        if (!$this->validate($this->borrowModel->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ✅ SECURITY: Validate folder exists and is available (prevent double borrowing)
        $folder = $this->folderModel->find($this->request->getPost('folder_id'));
        if (!$folder) {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => 'Folder not found',
            ]);
        }
        if ($folder['status'] !== 'Available') {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => 'This folder is not available for borrowing',
            ]);
        }

        $data = [
            'folder_id' => $this->request->getPost('folder_id'),
            'borrower_name' => $this->request->getPost('borrower_name'),
            'borrower_email' => $this->request->getPost('borrower_email'),
            'purpose' => $this->request->getPost('purpose') ?? 'General request',
            'expected_return_date' => $this->request->getPost('expected_return_date'),
            'status' => 'Pending',  // ✅ Status is Pending, not Borrowed
            'notes' => $this->request->getPost('notes') ?: null,
            'created_by' => auth_user()['user_id'],
        ];

        try {
            // ✅ Model-level validation happens in beforeInsert hook
            if (!$this->borrowModel->save($data)) {
                log_message('error', "Failed to save borrow request: " . json_encode($this->borrowModel->errors) . " [User: " . auth_user()['user_id'] . "]");
                return redirect()->back()->withInput()->with('errors',
                    $this->borrowModel->errors() ?: ['general' => 'Failed to create borrow request']
                );
            }

            // Log to audit
            log_message('info', "User " . auth_user()['user_id'] . " created borrow request for folder " . $data['folder_id']);

            return redirect()->to('/borrows')->with('success', 'Borrow request created successfully');
        } catch (\InvalidArgumentException $e) {
            // Model validation failed through beforeInsert hook
            log_message('error', "Borrow request validation failed: " . $e->getMessage() . " [User: " . auth_user()['user_id'] . "]");
            return redirect()->back()->withInput()->with('errors', [
                'general' => 'Validation failed. Please review the provided values.',
            ]);
        }
    }

    /**
     * Show borrow details
     */
    public function show(int $transactionId)
    {
        $borrow = $this->borrowModel->find($transactionId);

        if (!$borrow) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Calculate dynamic status
        $borrow['status'] = $this->borrowModel->calculateStatus($borrow);

        // IDOR Check: Permission-based access control
        // Users can view own records OR if authorized to view all
        $userId = auth_user()['user_id'];
        if (
            $borrow['created_by'] !== $userId &&
            !can('approve_borrow_requests')
        ) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $folder = $this->folderModel->find($borrow['folder_id']);

        return view('borrow/show', [
            'title' => 'Borrow Details',
            'borrow' => $borrow,
            'folder' => $folder,
        ]);
    }

    /**
     * ✅ APPROVE BORROW REQUEST AND RELEASE ITEM
     * 
     * Delegates to BorrowService for atomic operation
     * Controller is now thin (HTTP concerns only)
     */
    public function approve(int $transactionId)
    {
        if (!can('approve_borrow_requests')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $adminId = auth_user()['user_id'];
        $borrowService = new \App\Libraries\BorrowService();

        try {
            // ✅ Service handles: validation, atomicity, logging, error handling
            $borrowService->approve($transactionId, $adminId);

            return redirect()->to('/borrows')->with('success', 'Borrow request approved');

        } catch (\DomainException $e) {
            // ✅ Business rule violated (already logged by service)
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            // ✅ Unexpected error (already logged by service)
            return redirect()->back()->with('error', 'An unexpected error occurred. Please contact support.');
        }
    }

    /**
     * ✅ RETURN BORROWED ITEM
     * 
     * Delegates to BorrowService for atomic operation
     * Controller is now thin (HTTP concerns only)
     */
    public function return(int $transactionId)
    {
        if (!can('approve_borrow_requests')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $userId = auth_user()['user_id'];
        $userRole = service('permissionService')->getUserRole($userId);
        $borrowService = new \App\Libraries\BorrowService();

        try {
            // ✅ Service handles: security checks, validation, atomicity, logging, error handling
            $borrowService->return($transactionId, $userId, $userRole);

            return redirect()->back()->with('success', 'Document returned successfully');

        } catch (\DomainException $e) {
            // ✅ Business rule violated or IDOR attempt (already logged by service)
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            // ✅ Unexpected error (already logged by service)
            return redirect()->back()->with('error', 'An unexpected error occurred. Please contact support.');
        }
    }

    /**
     * Manually send overdue reminder to borrower for one transaction.
     */
    public function notify(int $transactionId)
    {
        if (!can('approve_borrow_requests')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $notificationService = new \App\Libraries\OverdueNotificationService();
        $result = $notificationService->sendSingleBorrowerNotification($transactionId);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * ✅ DECLINE BORROW REQUEST
     * 
     * Updates status from Pending to Declined
     */
    public function decline(int $transactionId)
    {
        if (!can('approve_borrow_requests')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        $borrow = $this->borrowModel->find($transactionId);
        
        if (!$borrow) {
            return redirect()->back()->with('error', 'Borrow request not found');
        }

        if ($borrow['status'] !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending requests can be declined');
        }

        try {
            // Update status to Declined
            $this->borrowModel->update($transactionId, [
                'status' => 'Declined',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            log_message('info', "User " . auth_user()['user_id'] . " declined borrow request {$transactionId}");

            return redirect()->to('/borrows')->with('success', 'Borrow request declined');

        } catch (\Throwable $e) {
            log_message('error', "Failed to decline borrow request {$transactionId}: " . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while declining the request');
        }
    }

    /**
     * List pending requests for approval
     */
    public function pending()
    {
        if (!can('approve_borrow_requests')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        // Get all pending borrows that haven't been returned yet
        $pending = $this->borrowModel->where('actual_return_date IS NULL')
                                      ->where('status', 'Pending')
                                      ->orderBy('created_at', 'DESC')
                                      ->orderBy('transaction_id', 'DESC')
                                      ->findAll();

        // Add borrower names
        foreach ($pending as &$borrow) {
        }

        return view('borrow/pending', [
            'title' => 'Pending Borrow Requests',
            'pending' => $pending,
        ]);
    }

    /**
     * List overdue items
     */
    public function overdue()
    {
        if (!can('view_pending_returns')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        // Get all borrowed items that haven't been returned yet
        $allBorrows = $this->borrowModel->where('actual_return_date IS NULL')
                                         ->findAll();

        // Filter to only overdue items (due date has passed)        
        $overdue = array_filter($allBorrows, function($borrow) {
            return $this->borrowModel->isOverdue($borrow);
        });

        // Add borrower names
        foreach ($overdue as &$borrow) {
        }

        return view('borrow/overdue', [
            'title' => 'Overdue Items',
            'overdue' => $overdue,
        ]);
    }

    /**
     * List currently borrowed items (approved and in use)
     */
    public function borrowed()
    {
        // Get all currently borrowed items (status = Borrowed, not yet returned)
        $borrowed = $this->borrowModel->where('actual_return_date IS NULL')
                                      ->where('status', 'Borrowed')
                                      ->orderBy('borrowed_at', 'DESC')
                                      ->orderBy('transaction_id', 'DESC')
                                      ->findAll();

        return view('borrow/borrowed', [
            'title' => 'Currently Borrowed Items',
            'borrowed' => $borrowed,
        ]);
    }
}
