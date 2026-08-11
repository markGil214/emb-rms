<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;

/**
 * EmailService - Robust email sending via the Resend API
 *
 * Handles:
 * - Authenticated calls to Resend's REST API
 * - HTML template rendering
 * - Error handling & logging
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
    protected $config;
    protected $from;
    protected $fromName;
    protected $resendApiKey;

    public function __construct()
    {
        $this->config = config('Email');
        $this->from = env('email.fromEmail') ?? $this->config->fromEmail;
        $this->fromName = env('email.fromName') ?? $this->config->fromName;
        $this->resendApiKey = env('email.resendApiKey') ?? $this->config->resendApiKey;
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
        if (empty($this->resendApiKey)) {
            LogHelper::error('email_not_configured', [
                'reason' => 'Resend API key not set in .env (email.resendApiKey)',
            ]);
            return false;
        }

        try {
            $payload = [
                'from' => $this->fromName . ' <' . $this->from . '>',
                'to' => [$to],
                'subject' => $subject,
                'html' => $htmlBody,
            ];

            if ($textBody) {
                $payload['text'] = $textBody;
            }

            $client = \Config\Services::curlrequest();
            $response = $client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->resendApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $sent = $statusCode >= 200 && $statusCode < 300;

            if ($sent) {
                LogHelper::info('email_sent', [
                    'to' => $to,
                    'subject' => $subject,
                    'timestamp' => Time::now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                $responseData = json_decode((string) $response->getBody(), true);

                LogHelper::error('email_send_failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $statusCode,
                    'error' => $responseData['message'] ?? (string) $response->getBody(),
                    'timestamp' => Time::now()->format('Y-m-d H:i:s'),
                ]);
            }

            return $sent;
        } catch (\Throwable $e) {
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
