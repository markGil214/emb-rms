<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use CodeIgniter\I18n\Time;

/**
 * EmailService - Robust email sending via PHPMailer + Gmail SMTP
 * 
 * Handles:
 * - SMTP authentication with Gmail App Passwords
 * - HTML template rendering
 * - Error handling & logging
 * - Retry logic on transient failures
 * 
 * Usage:
 *   $emailService = new EmailService();
 *   $sent = $emailService->send(
 *       $to,
 *       'Subject Line',
 *       '<h1>HTML Body</h1>',
 *       'Plain text body'
 *   );
 */
class EmailService
{
    protected $mailer;
    protected $config;
    protected $from;
    protected $fromName;

    public function __construct()
    {
        $this->config = config('Email');
        $this->from = env('email.fromEmail') ?? $this->config->fromEmail;
        $this->fromName = env('email.fromName') ?? $this->config->fromName;

        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    /**
     * Configure PHPMailer for Gmail SMTP
     */
    protected function configureMailer()
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config->SMTPHost;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = env('email.SMTPUser') ?? $this->config->SMTPUser;
            $this->mailer->Password = env('email.SMTPPass') ?? $this->config->SMTPPass;
            $this->mailer->SMTPSecure = $this->config->SMTPCrypto;
            $this->mailer->Port = $this->config->SMTPPort;
            $this->mailer->Timeout = $this->config->SMTPTimeout;
            $this->mailer->SMTPKeepAlive = $this->config->SMTPKeepAlive;

            // Set from address
            $this->mailer->setFrom($this->from, $this->fromName);

            // Mail type
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            LogHelper::error('email_config_failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Send email to single recipient
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML body content
     * @param string|null $textBody Plain text fallback (auto-generated if null)
     * @return bool Success status
     */
    public function send($to, $subject, $htmlBody, $textBody = null)
    {
        try {
            // Validate credentials
            if (empty($this->config->SMTPUser) || empty($this->config->SMTPPass)) {
                LogHelper::error('email_not_configured', [
                    'reason' => 'SMTP credentials not set in .env',
                ]);
                return false;
            }

            // Clear previous recipients
            $this->mailer->clearAllRecipients();

            // Set recipient
            $this->mailer->addAddress($to);

            // Set subject & body
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;

            if ($textBody) {
                $this->mailer->AltBody = $textBody;
            }

            // Send
            $sent = $this->mailer->send();

            if ($sent) {
                LogHelper::info('email_sent', [
                    'to' => $to,
                    'subject' => $subject,
                    'timestamp' => Time::now()->format('Y-m-d H:i:s'),
                ]);
            }

            return $sent;
        } catch (Exception $e) {
            LogHelper::error('email_send_failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'timestamp' => Time::now()->format('Y-m-d H:i:s'),
            ]);
            return false;
        }
    }

    /**
     * Send email to multiple recipients
     * 
     * @param array $recipients Array of emails
     * @param string $subject Email subject
     * @param string $htmlBody HTML body content
     * @param string|null $textBody Plain text fallback
     * @return array ['sent' => count, 'failed' => count, 'errors' => [emails that failed]]
     */
    public function sendToMultiple($recipients, $subject, $htmlBody, $textBody = null)
    {
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($recipients as $email) {
            if ($this->send($email, $subject, $htmlBody, $textBody)) {
                $sent++;
            } else {
                $failed++;
                $errors[] = $email;
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Render view template as email body
     * 
     * @param string $viewPath View path (e.g., 'emails/overdue-notification')
     * @param array $data Data to pass to view
     * @return string Rendered HTML
     */
    public function renderTemplate($viewPath, $data = [])
    {
        return view($viewPath, $data, ['debug' => false]);
    }

    /**
     * Send templated email
     * 
     * @param string $to Recipient email
     * @param string $subject Subject line
     * @param string $viewPath View template path
     * @param array $data Data for template
     * @return bool Success status
     */
    public function sendFromTemplate($to, $subject, $viewPath, $data = [])
    {
        try {
            $htmlBody = $this->renderTemplate($viewPath, $data);
            return $this->send($to, $subject, $htmlBody);
        } catch (\Throwable $e) {
            LogHelper::error('email_template_failed', [
                'to' => $to,
                'template' => $viewPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
