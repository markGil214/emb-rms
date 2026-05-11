<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FolderFileModel;
use App\Models\FileDisposalRequestModel;
use CodeIgniter\Controller;

class FileUploadController extends BaseController
{
    protected $folderModel;
    protected $folderFileModel;
    protected $fileDisposalRequestModel;
    protected $uploadPath;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->folderFileModel = new FolderFileModel();
        $this->fileDisposalRequestModel = new FileDisposalRequestModel();
        $this->uploadPath = WRITEPATH . 'uploads/folders/';
        
        // Create directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload file for a folder
     */
    public function upload(int $folderId)
    {
        // Verify folder exists
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => 'Folder not found',
            ]);
        }

        if (($folder['status'] ?? null) !== 'Available') {
            return redirect()->back()->withInput()->with('errors', [
                'folder_id' => "Cannot upload files while folder status is '{$folder['status']}'. Only Available folders accept uploads.",
            ]);
        }

        $retentionType = (string) $this->request->getPost('retention_type');

        $validationRules = [
            'file' => [
                'rules' => 'uploaded[file]|max_size[file,30720]',
                'errors' => [
                    'uploaded'  => 'You must select a file to upload.',
                    'max_size'  => 'File size must not exceed 30MB.',
                ]
            ],
            'retention_type' => [
                'rules' => 'required|in_list[permanent,expiration]',
                'errors' => [
                    'required' => 'Please select a retention option.',
                    'in_list'  => 'Invalid retention option selected.',
                ],
            ],
        ];

        if ($retentionType === 'expiration') {
            $validationRules['expiration_years'] = [
                'rules' => 'required|is_natural|less_than_equal_to[30]',
                'errors' => [
                    'required' => 'Please enter the number of years.',
                    'is_natural' => 'Expiration years must be a whole number (0 or more).',
                    'less_than_equal_to' => 'Expiration years must not be greater than 30.',
                ],
            ];
            $validationRules['expiration_months'] = [
                'rules' => 'required|is_natural|less_than_equal_to[11]',
                'errors' => [
                    'required' => 'Please enter the number of months.',
                    'is_natural' => 'Expiration months must be a whole number (0 or more).',
                    'less_than_equal_to' => 'Expiration months must not be greater than 11.',
                ],
            ];
            $validationRules['expiration_days'] = [
                'rules' => 'required|is_natural|less_than_equal_to[30]',
                'errors' => [
                    'required' => 'Please enter the number of days.',
                    'is_natural' => 'Expiration days must be a whole number (0 or more).',
                    'less_than_equal_to' => 'Expiration days must not be greater than 30.',
                ],
            ];
        }

        // Validate file upload
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');
        
        // Generate unique filename with folder_id prefix
        $newName = $folderId . '_' . time() . '_' . $file->getRandomName();
        
        log_message('debug', "Uploading file: " . $file->getClientName() . " as " . $newName);
        
        // Move file to uploads directory
        try {
            $file->move($this->uploadPath, $newName);
            log_message('debug', "File moved to: " . $this->uploadPath . $newName);
        } catch (\Exception $e) {
            log_message('error', "File move failed: " . $e->getMessage());
            return redirect()->back()->withInput()->with('errors', [
                'file' => 'File upload failed. Please try again.',
            ]);
        }

        // Verify file was actually saved
        $uploadedPath = $this->uploadPath . $newName;
        if (!file_exists($uploadedPath)) {
            log_message('error', "Uploaded file not found at: " . $uploadedPath);
            return redirect()->back()->withInput()->with('errors', [
                'file' => 'File upload verification failed',
            ]);
        }

        // Save file info to database
        $filePath = 'uploads/folders/' . $newName;
        $expirationYears  = (int) $this->request->getPost('expiration_years');
        $expirationMonths = (int) $this->request->getPost('expiration_months');
        $expirationDays   = (int) $this->request->getPost('expiration_days');

        $expirationDate = null;
        if ($retentionType === 'expiration') {
            if ($expirationYears === 0 && $expirationMonths === 0 && $expirationDays === 0) {
                return redirect()->back()->withInput()->with('errors', [
                    'expiration_years' => 'At least one of years, months, or days must be greater than zero.',
                ]);
            }
            $today = new \DateTimeImmutable('today');
            $interval = new \DateInterval('P' . $expirationYears . 'Y' . $expirationMonths . 'M' . $expirationDays . 'D');
            $expirationDate = $today->add($interval)->format('Y-m-d');
        }
        $data = [
            'folder_id' => $folderId,
            'file_name' => $file->getClientName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'uploaded_by' => auth_user()['user_id'] ?? null,
            'retention_type' => $retentionType,
            'expiration_date' => $expirationDate,
        ];

        if ($this->folderFileModel->save($data)) {
            log_message('debug', "File record saved to database with path: " . $filePath);
            return redirect()->to(route_to('records.show', $folderId))->with('success', 'File uploaded successfully');
        } else {
            // Delete uploaded file if database save fails
            if (file_exists($this->uploadPath . $newName)) {
                unlink($this->uploadPath . $newName);
            }

            $modelErrors = $this->folderFileModel->errors();
            $errorText = is_array($modelErrors) ? implode(', ', $modelErrors) : (string) $modelErrors;

            log_message('error', 'Database save failed: ' . $errorText);

            return redirect()->back()->withInput()->with(
                'errors',
                is_array($modelErrors) && !empty($modelErrors)
                    ? $modelErrors
                    : ['file' => 'Failed to save file information. Please try again.']
            );
        }
    }

    /**
     * Download file
     */
    public function download(int $fileId)
    {
        $file = $this->folderFileModel->find($fileId);
        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Construct proper file path - file_path already includes 'uploads/folders/'
        $fullPath = WRITEPATH . $file['file_path'];
        
        log_message('debug', "Attempting to download file ID: " . $fileId);
        log_message('debug', "Stored path: " . $file['file_path']);
        log_message('debug', "Full path: " . $fullPath);
        log_message('debug', "File exists: " . (file_exists($fullPath) ? 'yes' : 'no'));
        
        if (!file_exists($fullPath)) {
            log_message('error', "File not found: " . $fullPath);
            return redirect()->back()->with('error', 'File not found at: ' . $fullPath);
        }

        // Check if file is readable
        if (!is_readable($fullPath)) {
            log_message('error', "File not readable: " . $fullPath);
            return redirect()->back()->with('error', 'File is not readable');
        }

        // Get file info
        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        
        log_message('debug', "File size: " . $fileSize . " bytes");
        log_message('debug', "Download as: " . $file['file_name']);

        // Stream the file for download
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $file['file_name'] . '"')
            ->setHeader('Content-Length', $fileSize)
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody(file_get_contents($fullPath));
    }

    public function requestDisposal(int $fileId)
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('file_disposal_requests')) {
            return redirect()->back()->with('error', 'File Disposal workflow table is not ready yet. Please run database migrations first.');
        }

        $file = $this->folderFileModel->find($fileId);
        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $this->isEligibleForDisposal($file)) {
            return redirect()->back()->with('error', 'File is not yet expired and is not eligible for disposal request.');
        }

        $existingRequest = $this->fileDisposalRequestModel->latestByFile($fileId);
        if ($existingRequest && in_array((string) ($existingRequest['status'] ?? ''), ['Pending', 'Approved', 'Disposed'], true)) {
            return redirect()->to(route_to('records.show', (int) $file['folder_id']))
                ->with('error', 'A disposal workflow already exists for this file.');
        }

        $requestData = [
            'file_id' => $fileId,
            'status' => 'Pending',
            'requested_by' => auth_user()['user_id'] ?? null,
            'requested_at' => date('Y-m-d H:i:s'),
            'approved_by' => null,
            'approved_at' => null,
            'disposed_at' => null,
            'notes' => null,
        ];

        if (! $this->fileDisposalRequestModel->save($requestData)) {
            return redirect()->to(route_to('records.show', (int) $file['folder_id']))
                ->with('error', 'Failed to create disposal request.');
        }

        $auditLog = service('auditLog');
        $auditLog->log(auth_user()['user_id'] ?? null, 'request_file_disposal', "file_id:{$fileId}");

        return redirect()->to(route_to('records.show', (int) $file['folder_id']))
            ->with('success', 'Disposal request submitted and is now pending approval.');
    }

    /**
     * Dispose file
     */
    public function delete(int $fileId)
    {
        $file = $this->folderFileModel->find($fileId);
        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $this->isEligibleForDisposal($file)) {
            return redirect()->back()->with('error', 'File is not yet expired and is not ready for disposal.');
        }

        $folderId = $file['folder_id'];
        $filePath = WRITEPATH . $file['file_path'];

        // Delete physical file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $this->folderFileModel->delete($fileId);

        return redirect()->to(route_to('records.show', $folderId))->with('success', 'File disposed successfully');
    }

    private function isEligibleForDisposal(array $file): bool
    {
        $retentionType = (string) ($file['retention_type'] ?? 'permanent');
        $expirationRaw = trim((string) ($file['expiration_date'] ?? ''));
        $today = new \DateTimeImmutable('today');

        if (
            $retentionType !== 'expiration' ||
            $expirationRaw === '' ||
            $expirationRaw === '0000-00-00' ||
            $expirationRaw === '0000-00-00 00:00:00'
        ) {
            return false;
        }

        try {
            $expirationDate = new \DateTimeImmutable($expirationRaw);
            return $expirationDate <= $today;
        } catch (\Exception $e) {
            return false;
        }
    }
}
