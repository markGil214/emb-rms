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

        // Validate the retention fields before touching any uploads.
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Accepts a batch. The form posts files[]; the older single `file`
        // field is still honoured so nothing else that posts here breaks.
        $files = $this->request->getFileMultiple('files') ?? [];
        if (empty($files)) {
            $single = $this->request->getFile('file');
            if ($single !== null) {
                $files = [$single];
            }
        }

        $files = array_filter($files, static function ($file) {
            return $file !== null && $file->getClientName() !== '';
        });

        if (empty($files)) {
            return redirect()->back()->withInput()->with('errors', [
                'files' => 'You must select at least one file to upload.',
            ]);
        }

        $maxFiles = 20;
        if (count($files) > $maxFiles) {
            return redirect()->back()->withInput()->with('errors', [
                'files' => 'You can upload at most ' . $maxFiles . ' files at a time.',
            ]);
        }

        $expirationYears  = (int) $this->request->getPost('expiration_years');
        $expirationMonths = (int) $this->request->getPost('expiration_months');
        $expirationDays   = (int) $this->request->getPost('expiration_days');

        // Retention applies to the whole batch.
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

        $maxBytes = 30 * 1024 * 1024; // 30MB, matching the previous single-file limit
        $uploadedBy = auth_user()['user_id'] ?? null;

        $savedCount = 0;
        $failures = [];

        foreach ($files as $file) {
            $originalName = $file->getClientName();

            if (! $file->isValid()) {
                $failures[] = $originalName . ' (' . $file->getErrorString() . ')';
                continue;
            }

            if ($file->getSize() > $maxBytes) {
                $failures[] = $originalName . ' (exceeds 30MB)';
                continue;
            }

            $newName = $folderId . '_' . time() . '_' . $file->getRandomName();

            try {
                $file->move($this->uploadPath, $newName);
            } catch (\Throwable $e) {
                log_message('error', 'File move failed for ' . $originalName . ': ' . $e->getMessage());
                $failures[] = $originalName . ' (could not be saved)';
                continue;
            }

            if (! file_exists($this->uploadPath . $newName)) {
                log_message('error', 'Uploaded file not found at: ' . $this->uploadPath . $newName);
                $failures[] = $originalName . ' (upload verification failed)';
                continue;
            }

            $saved = $this->folderFileModel->save([
                'folder_id' => $folderId,
                'file_name' => $originalName,
                'file_path' => 'uploads/folders/' . $newName,
                'file_size' => $file->getSize(),
                'uploaded_by' => $uploadedBy,
                'retention_type' => $retentionType,
                'expiration_date' => $expirationDate,
            ]);

            if ($saved) {
                $savedCount++;
                continue;
            }

            // Don't leave an orphaned file on disk with no database record.
            if (file_exists($this->uploadPath . $newName)) {
                unlink($this->uploadPath . $newName);
            }

            $modelErrors = $this->folderFileModel->errors();
            log_message('error', 'Database save failed for ' . $originalName . ': '
                . (is_array($modelErrors) ? implode(', ', $modelErrors) : (string) $modelErrors));

            $failures[] = $originalName . ' (could not be recorded)';
        }

        if ($savedCount === 0) {
            return redirect()->back()->withInput()->with('errors', [
                'files' => 'No files were uploaded. ' . implode('; ', $failures),
            ]);
        }

        $message = $savedCount === 1
            ? '1 file uploaded successfully.'
            : $savedCount . ' files uploaded successfully.';

        if (! empty($failures)) {
            // Some succeeded, so this is a warning rather than an outright failure.
            return redirect()->to(route_to('records.show', $folderId))
                ->with('success', $message)
                ->with('warning', count($failures) . ' file(s) were skipped: ' . implode('; ', $failures));
        }

        return redirect()->to(route_to('records.show', $folderId))->with('success', $message);
    }

    /**
     * Download file (forces attachment)
     */
    public function download(int $fileId)
    {
        return $this->serveFile($fileId, 'attachment');
    }

    /**
     * View file in browser (inline if supported)
     */
    public function view(int $fileId)
    {
        return $this->serveFile($fileId, 'inline');
    }

    /**
     * Helper to serve files with specific disposition
     */
    private function serveFile(int $fileId, string $dispositionType)
    {
        $file = $this->folderFileModel->find($fileId);
        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $fullPath = WRITEPATH . $file['file_path'];
        
        if (!file_exists($fullPath)) {
            log_message('error', "File not found: " . $fullPath);
            return redirect()->back()->with('error', 'File not found');
        }

        if (!is_readable($fullPath)) {
            log_message('error', "File not readable: " . $fullPath);
            return redirect()->back()->with('error', 'File is not readable');
        }

        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $fileName = $file['file_name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Define which extensions can be shown inline
        $inlineExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // If requesting inline but extension not supported, fallback to attachment
        if ($dispositionType === 'inline' && !in_array($extension, $inlineExtensions)) {
            $dispositionType = 'attachment';
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $dispositionType . '; filename="' . $fileName . '"')
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
            return redirect()->back()
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
            return redirect()->back()
                ->with('error', 'Failed to create disposal request.');
        }

        $auditLog = service('auditLog');
        $auditLog->log('request_file_disposal', 'file', (int) $fileId, null, null, auth_user()['user_id'] ?? null);

        return redirect()->back()
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
