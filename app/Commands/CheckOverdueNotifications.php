<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\OverdueNotificationService;

/**
 * CheckOverdueNotifications Command
 * 
 * Runs the overdue notification system manually or via cron
 * Checks for items overdue 1, 3, 7 days and sends notifications
 * 
 * Usage:
 *   php spark notification:check-overdue
 *   php spark notification:check-overdue --verbose
 * 
 * For automated daily runs, add to crontab:
 *   0 9 * * * cd /path/to/app && php spark notification:check-overdue
 */
class CheckOverdueNotifications extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Notifications';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'notification:check-overdue';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Check for overdue borrowed items and send notifications to borrowers and managers.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'notification:check-overdue [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--verbose' => 'Show detailed output for each notification sent',
        '--dry-run' => 'Show what would be sent without actually sending emails',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        try {
            CLI::write('🔍 Checking for overdue borrowed items...', 'cyan');
            CLI::newLine();

            $verbose = (bool) CLI::getOption('verbose');
            $dryRun = (bool) CLI::getOption('dry-run');

            // Initialize service
            $notificationService = new OverdueNotificationService();

            if ($dryRun) {
                CLI::write('DRY RUN MODE - No emails will be sent', 'yellow');
                CLI::newLine();
            }

            // Run the service
            $results = $notificationService->sendNotifications($dryRun);

            // Display results
            $this->displayResults($results, $verbose, $dryRun);

            // Return success
            return 0;
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
            if ((bool) CLI::getOption('verbose')) {
                CLI::write($e->getTraceAsString(), 'red');
            }
            return 1;
        }
    }

    /**
     * Display results in a formatted way
     *
     * @param array $results Results from OverdueNotificationService
     * @param bool $verbose Show detailed output
     * @param bool $dryRun Whether this was a dry run
     */
    protected function displayResults(array $results, bool $verbose = false, bool $dryRun = false)
    {
        CLI::newLine();
        CLI::write('═══════════════════════════════════════════════════════════════', 'cyan');
        CLI::write('OVERDUE NOTIFICATION SUMMARY', 'cyan');
        CLI::write('═══════════════════════════════════════════════════════════════', 'cyan');
        CLI::newLine();

        // Summary stats
        CLI::write('Results:', 'green');
        CLI::write('  ✓ Borrower Emails Sent: ' . $results['borrower_emails_sent'], 'cyan');
        CLI::write('  ✓ Manager Escalations Sent: ' . $results['manager_emails_sent'], 'cyan');
        CLI::write('  ✗ Failed Sends: ' . $results['failed_sends'], 'red');
        if ($dryRun) {
            CLI::write('  → Borrower Emails Would Send: ' . ($results['borrower_emails_would_send'] ?? 0), 'yellow');
            CLI::write('  → Manager Escalations Would Send: ' . ($results['manager_emails_would_send'] ?? 0), 'yellow');
        }
        CLI::newLine();

        // Timestamp
        CLI::write('Timestamp: ' . $results['timestamp'], 'yellow');
        CLI::newLine();

        // Errors (if any)
        if (!empty($results['errors'])) {
            CLI::write('Errors Encountered:', 'red');
            foreach ($results['errors'] as $error) {
                CLI::write('  ✗ ' . $error, 'red');
            }
            CLI::newLine();
        }

        // Summary
        if ($dryRun) {
            $total = ($results['borrower_emails_would_send'] ?? 0) + ($results['manager_emails_would_send'] ?? 0);
            if ($total > 0) {
                CLI::write("Dry run only: {$total} notification(s) would be sent.", 'yellow');
            } else {
                CLI::write('No overdue items found or no notifications to send.', 'yellow');
            }
        } else {
            $total = $results['borrower_emails_sent'] + $results['manager_emails_sent'];
            if ($total > 0) {
                CLI::write("✓ Successfully sent {$total} notification(s)", 'green');
            } else {
                CLI::write('No overdue items found or no notifications to send.', 'yellow');
            }
        }

        CLI::newLine();
        CLI::write('═══════════════════════════════════════════════════════════════', 'cyan');
        CLI::newLine();

        // Verbose output
        if ($verbose && !empty($results['errors'])) {
            CLI::write('Detailed Error Log:', 'yellow');
            foreach ($results['errors'] as $error) {
                CLI::write('  • ' . $error, 'yellow');
            }
            CLI::newLine();
        }
    }
}
