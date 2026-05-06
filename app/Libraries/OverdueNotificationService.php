<?php

namespace App\Libraries;

use App\Models\BorrowTransactionModel;
use App\Models\UserModel;
use App\Models\FolderModel;
use CodeIgniter\I18n\Time;

/**
 * OverdueNotificationService - Orchestrates overdue item notifications
 * 
 * Responsibilities:
 * - Find items overdue 1, 3, 7 days
 * - Send borrower reminder emails
 * - Escalate to managers at 7+ days
 * - Track notification state to prevent duplicates
 * - Log all operations
 * 
 * Daily cron: php spark notification:check-overdue
 */
class OverdueNotificationService
{
    protected $borrowModel;
    protected $userModel;
    protected $folderModel;
    protected $emailService;

    public function __construct()
    {
        $this->borrowModel = new BorrowTransactionModel();
        $this->userModel = new UserModel();
        $this->folderModel = new FolderModel();
        $this->emailService = new EmailService();
    }

    /**
     * Main orchestrator - Run daily via cron
     * 
     * Sends notifications at 1, 3, 7 day intervals
     * Escalates to managers at 7+ days
     * 
     * @return array Results summary
     */
    public function sendNotifications(bool $dryRun = false)
    {
        $results = [
            'borrower_emails_sent' => 0,
            'manager_emails_sent' => 0,
            'borrower_emails_would_send' => 0,
            'manager_emails_would_send' => 0,
            'failed_sends' => 0,
            'errors' => [],
            'timestamp' => Time::now()->format('Y-m-d H:i:s'),
        ];

        // Check each threshold
        $thresholds = [1, 3, 7];

        foreach ($thresholds as $days) {
            try {
                $overdue = $this->findOverdueBorrows($days);

                foreach ($overdue as $borrow) {
                    if ($dryRun) {
                        $results['borrower_emails_would_send']++;
                        if ($days >= 7) {
                            $results['manager_emails_would_send']++;
                        }
                        continue;
                    }

                    // Send borrower notification
                    $sent = $this->notifyBorrower($borrow, $days);
                    if ($sent) {
                        $results['borrower_emails_sent']++;
                    } else {
                        $results['failed_sends']++;
                        $results['errors'][] = "Failed to notify borrower for transaction {$borrow['transaction_id']}";
                    }

                    // At 7+ days, escalate to manager
                    if ($days >= 7) {
                        $managerSent = $this->escalateToManager($borrow);
                        if ($managerSent) {
                            $results['manager_emails_sent']++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $results['errors'][] = "Error processing {$days}-day overdue: {$e->getMessage()}";
                LogHelper::error('overdue_notification_error', [
                    'days' => $days,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log summary
        LogHelper::info('overdue_notification_run', $results);

        return $results;
    }

    /**
     * Find all items overdue exactly X days
     * AND haven't had notification sent for that threshold yet
     * 
     * @param int $days Days overdue (1, 3, 7)
     * @return array Array of borrow transactions
     */
    public function findOverdueBorrows($days)
    {
        $notificationKey = "{$days}_day_sent";

        $query = $this->borrowModel
            ->where('status', 'Borrowed')
            ->where('actual_return_date', null)
            ->where('DATEDIFF(NOW(), expected_return_date) >=', (int) $days, false);

        // Check if this notification hasn't been sent yet
        // This prevents duplicate emails on re-runs
        $results = $query->findAll();

        $filtered = [];
        foreach ($results as $borrow) {
            $notificationStatus = $borrow['notification_status'] ?? 'None';
            
            // Only include if this notification threshold hasn't been sent
            if (strpos($notificationStatus, $notificationKey) === false) {
                $filtered[] = $borrow;
            }
        }

        return $filtered;
    }

    /**
     * Send reminder email to borrower
     * 
     * @param array $borrow The borrow transaction record
     * @param int $daysOverdue How many days overdue
     * @return bool Success
     */
    protected function notifyBorrower($borrow, $daysOverdue)
    {
        try {
            // Get folder details
            $folder = $this->folderModel->find($borrow['folder_id']);
            if (!$folder) {
                LogHelper::warning('folder_not_found', ['folder_id' => $borrow['folder_id']]);
                return false;
            }

            // Get borrower email from transaction (primary source)
            $borrowerEmail = $borrow['borrower_email'] ?? '';
            
            // Last resort: Get from created_by user (legacy fallback)
            if (empty($borrowerEmail) && !empty($borrow['created_by'])) {
                $user = $this->userModel->find($borrow['created_by']);
                $borrowerEmail = $user['email'] ?? '';
            }

            if (empty($borrowerEmail)) {
                LogHelper::warning('borrower_email_missing', ['transaction_id' => $borrow['transaction_id']]);
                return false;
            }

            // Render email template
            $sent = $this->emailService->sendFromTemplate(
                $borrowerEmail,
                "⏰ Item {$folder['file_code']} is {$daysOverdue} days overdue",
                'emails/overdue-borrower',
                [
                    'borrower_name' => $borrow['borrower_name'],
                    'file_code' => $folder['file_code'] ?? 'Unknown',
                    'company_name' => $folder['company_name'] ?? '',
                    'expected_return_date' => $borrow['expected_return_date'],
                    'days_overdue' => $daysOverdue,
                    'purpose' => $borrow['purpose'],
                ]
            );

            if ($sent) {
                // Update notification status
                $this->updateNotificationStatus($borrow['transaction_id'], $daysOverdue);
            }

            return $sent;
        } catch (\Throwable $e) {
            LogHelper::error('borrower_notification_error', [
                'transaction_id' => $borrow['transaction_id'],
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Escalate to manager at 7+ days overdue
     * 
     * @param array $borrow The borrow transaction
     * @return bool Success
     */
    protected function escalateToManager($borrow)
    {
        try {
            // Get the user who created the borrow
            $user = $this->userModel->find($borrow['created_by']);
            if (!$user) {
                return false;
            }

            // Get manager email (if assigned)
            $managerEmail = null;
            if ($user['manager_id']) {
                $manager = $this->userModel->find($user['manager_id']);
                $managerEmail = $manager['email'] ?? null;
            }

            // If no manager, escalate to system admin (role = SuperAdmin or first Admin)
            if (!$managerEmail) {
                $admin = $this->userModel
                    ->whereIn('role', ['SuperAdmin'])
                    ->first();
                if ($admin) {
                    $managerEmail = $admin['email'];
                }
            }

            if (empty($managerEmail)) {
                LogHelper::warning('no_manager_for_escalation', ['user_id' => $borrow['created_by']]);
                return false;
            }

            // Get folder details
            $folder = $this->folderModel->find($borrow['folder_id']);
            $fileCode = $folder['file_code'] ?? 'Unknown';

            // Send manager escalation email
            $sent = $this->emailService->sendFromTemplate(
                $managerEmail,
                "🚨 ESCALATION: Overdue item {$fileCode}",
                'emails/overdue-manager',
                [
                    'manager_name' => $user['manager_id'] ? $this->userModel->find($user['manager_id'])['username'] : 'Administrator',
                    'borrower_name' => $borrow['borrower_name'],
                    'file_code' => $fileCode,
                    'company_name' => $folder['company_name'] ?? '',
                    'expected_return_date' => $borrow['expected_return_date'],
                    'requested_by' => $user['username'],
                ]
            );

            if ($sent) {
                $this->borrowModel->update($borrow['transaction_id'], [
                    'escalated_to_manager' => true,
                ]);
            }

            return $sent;
        } catch (\Throwable $e) {
            LogHelper::error('manager_escalation_error', [
                'transaction_id' => $borrow['transaction_id'],
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update notification status to mark that X-day notification was sent
     * 
     * @param int $transactionId Transaction to update
     * @param int $days Days threshold (1, 3, 7)
     */
    protected function updateNotificationStatus($transactionId, $days)
    {
        try {
            $borrow = $this->borrowModel->find($transactionId);
            $currentStatus = $borrow['notification_status'] ?? 'None';

            // Append the new sent flag
            $notificationKey = "{$days}_day_sent";
            if (strpos($currentStatus, $notificationKey) === false) {
                $newStatus = $currentStatus === 'None' 
                    ? $notificationKey 
                    : "{$currentStatus}|{$notificationKey}";

                $this->borrowModel->update($transactionId, [
                    'notification_status' => $newStatus,
                    'last_notification_sent_at' => Time::now()->format('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            LogHelper::error('update_notification_status_failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send manual overdue reminder for a single transaction from UI action.
     *
     * Uses threshold buckets 1/3/7 based on current days overdue.
     * Prevents duplicate sends for the same threshold.
     *
     * @param int $transactionId
     * @return array{success:bool,message:string}
     */
    public function sendSingleBorrowerNotification(int $transactionId): array
    {
        $borrow = $this->borrowModel->find($transactionId);
        if (!$borrow) {
            return ['success' => false, 'message' => 'The selected borrow record could not be found.'];
        }

        if (($borrow['status'] ?? '') !== 'Borrowed' || !empty($borrow['actual_return_date'])) {
            return ['success' => false, 'message' => 'Only active borrowed items can receive overdue reminders.'];
        }

        $overdueSeconds = time() - strtotime((string) $borrow['expected_return_date']);
        if ($overdueSeconds <= 0) {
            return ['success' => false, 'message' => 'This item is not yet overdue.'];
        }

        $threshold = 1;
        if ($overdueSeconds >= 7 * 86400) {
            $threshold = 7;
        } elseif ($overdueSeconds >= 3 * 86400) {
            $threshold = 3;
        }

        $notificationKey = "{$threshold}_day_sent";
        $notificationStatus = $borrow['notification_status'] ?? 'None';
        if (strpos($notificationStatus, $notificationKey) !== false) {
            return ['success' => false, 'message' => "A {$threshold}-day reminder has already been sent for this item."];
        }

        $sent = $this->notifyBorrower($borrow, $threshold);
        if (!$sent) {
            return ['success' => false, 'message' => 'Unable to send the reminder email at this time. Please try again later.'];
        }

        if ($threshold >= 7) {
            $this->escalateToManager($borrow);
        }

        return ['success' => true, 'message' => "Reminder sent successfully ({$threshold}-day stage)."];
    }
}
