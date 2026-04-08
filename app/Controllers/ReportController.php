<?php

namespace App\Controllers;

use App\Models\BorrowTransactionModel;
use App\Models\FolderModel;

/**
 * ReportController - Generate reports and exports
 * 
 * Handles:
 * - Overdue items CSV export
 * - Borrow history reports
 * - Archive/disposal reports
 */
class ReportController extends BaseController
{
    protected $borrowModel;
    protected $folderModel;

    public function __construct()
    {
        $this->borrowModel = new BorrowTransactionModel();
        $this->folderModel = new FolderModel();
    }

    /**
     * Export overdue items to CSV
     * 
     * Query params:
     *   - date_from: Filter items overdue since date (Y-m-d)
     *   - date_to: Filter items overdue until date (Y-m-d)
     *   - borrower_name: Filter by borrower name pattern
     *   - status: Filter by status (optional)
     * 
     * @return Response CSV file download
     */
    public function exportOverdueCSV()
    {
        // Permission check
        if (!can('view_all_borrow')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        try {
            // Get filter parameters
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            $borrowerName = $this->request->getGet('borrower_name');

            // Build query
            $query = $this->borrowModel
                ->where('status', 'Borrowed')
                ->where('actual_return_date IS NULL')
                ->where('expected_return_date <', date('Y-m-d H:i:s'))
                ->orderBy('expected_return_date', 'ASC');

            // Apply filters
            if ($dateFrom) {
                $query = $query->where('expected_return_date >=', $dateFrom . ' 00:00:00');
            }
            if ($dateTo) {
                $query = $query->where('expected_return_date <=', $dateTo . ' 23:59:59');
            }
            if ($borrowerName) {
                $query = $query->like('borrower_name', $borrowerName);
            }

            $overdue = $query->findAll();

            // Build CSV data
            $csvData = $this->buildOverdueCSV($overdue);

            // Generate filename with timestamp
            $filename = 'overdue-items-' . date('Y-m-d-His') . '.csv';

            // Return as downloadable file
            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($csvData);
        } catch (\Throwable $e) {
            \App\Libraries\LogHelper::error('csv_export_failed', [
                'error' => $e->getMessage(),
                'type' => 'overdue',
            ]);
            return redirect()->back()->with('error', 'Failed to generate CSV export');
        }
    }

    /**
     * Build CSV content for overdue items
     * 
     * @param array $overdue Array of overdue transactions
     * @return string CSV content
     */
    protected function buildOverdueCSV(array $overdue): string
    {
        // Prepare headers
        $headers = [
            'Transaction ID',
            'Document Code',
            'Company Name',
            'Borrower Name',
            'Purpose',
            'Expected Return Date',
            'Days Overdue',
            'Status',
            'Notification Status',
            'Last Notification Sent',
            'Escalated to Manager',
            'Request Date',
        ];

        // Open output buffer as CSV file
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        // Add data rows
        foreach ($overdue as $item) {
            $folder = $this->folderModel->find($item['folder_id']);
            $daysOverdue = ceil((strtotime(date('Y-m-d')) - strtotime($item['expected_return_date'])) / (60 * 60 * 24));

            $row = [
                $item['transaction_id'],
                $folder['file_code'] ?? 'N/A',
                $folder['company_name'] ?? 'N/A',
                $item['borrower_name'],
                substr($item['purpose'] ?? '', 0, 50), // Truncate long purposes
                date('Y-m-d', strtotime($item['expected_return_date'])),
                max(1, $daysOverdue),
                $item['status'],
                $item['notification_status'] ?? 'None',
                $item['last_notification_sent_at'] ? date('Y-m-d H:i', strtotime($item['last_notification_sent_at'])) : 'Never',
                $item['escalated_to_manager'] ? 'Yes' : 'No',
                date('Y-m-d', strtotime($item['created_at'])),
            ];

            fputcsv($output, $row);
        }

        // Get CSV content
        $csv = ob_get_clean();
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Alternative: build string manually
        $csv = implode(',', array_map('csvEscape', $headers)) . "\n";
        foreach ($overdue as $item) {
            $folder = $this->folderModel->find($item['folder_id']);
            $daysOverdue = ceil((strtotime(date('Y-m-d')) - strtotime($item['expected_return_date'])) / (60 * 60 * 24));

            $row = [
                $item['transaction_id'],
                $folder['file_code'] ?? 'N/A',
                $folder['company_name'] ?? 'N/A',
                $item['borrower_name'],
                substr($item['purpose'] ?? '', 0, 50),
                date('Y-m-d', strtotime($item['expected_return_date'])),
                max(1, $daysOverdue),
                $item['status'],
                $item['notification_status'] ?? 'None',
                $item['last_notification_sent_at'] ? date('Y-m-d H:i', strtotime($item['last_notification_sent_at'])) : 'Never',
                $item['escalated_to_manager'] ? 'Yes' : 'No',
                date('Y-m-d', strtotime($item['created_at'])),
            ];
            $csv .= implode(',', array_map('csvEscape', $row)) . "\n";
        }

        return $csv;
    }

    /**
     * Display overdue items report view
     * 
     * Query params:
     *   - date_from, date_to, borrower_name (filters)
     * 
     * @return Response HTML report view
     */
    public function viewOverdue()
    {
        // Permission check
        if (!can('view_all_borrow')) {
            return redirect()->back()->with('error', 'Permission denied');
        }

        try {
            // Get filter parameters
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            $borrowerName = $this->request->getGet('borrower_name');

            // Build query
            $query = $this->borrowModel
                ->where('status', 'Borrowed')
                ->where('actual_return_date IS NULL')
                ->where('expected_return_date <', date('Y-m-d H:i:s'))
                ->orderBy('expected_return_date', 'ASC');

            // Apply filters
            if ($dateFrom) {
                $query = $query->where('expected_return_date >=', $dateFrom . ' 00:00:00');
            }
            if ($dateTo) {
                $query = $query->where('expected_return_date <=', $dateTo . ' 23:59:59');
            }
            if ($borrowerName) {
                $query = $query->like('borrower_name', $borrowerName);
            }

            $overdue = $query->findAll();

            // Enrich with folder data and calculate days overdue
            $enriched = [];
            foreach ($overdue as $item) {
                $folder = $this->folderModel->find($item['folder_id']);
                $item['file_code'] = $folder['file_code'] ?? 'N/A';
                $item['company_name'] = $folder['company_name'] ?? 'N/A';
                $item['days_overdue'] = ceil((strtotime(date('Y-m-d')) - strtotime($item['expected_return_date'])) / (60 * 60 * 24));
                $enriched[] = $item;
            }

            return view('reports/overdue', [
                'title' => 'Overdue Items Report',
                'overdue' => $enriched,
                'filter' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'borrower_name' => $borrowerName,
                ],
            ]);
        } catch (\Throwable $e) {
            \App\Libraries\LogHelper::error('report_view_failed', [
                'error' => $e->getMessage(),
                'type' => 'overdue',
            ]);
            return redirect()->back()->with('error', 'Failed to load report');
        }
    }
}

/**
 * Helper function to escape CSV fields
 */
function csvEscape($value)
{
    if (preg_match('/[,"\n\r]/', $value)) {
        return '"' . str_replace('"', '""', $value) . '"';
    }
    return $value;
}
