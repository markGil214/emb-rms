<?php
// Standalone SMTP test - no CI4 needed
require_once __DIR__ . '/../vendor/autoload.php';

echo "<pre>";
echo "=== SMTP Diagnostic ===\n\n";

// Read .env manually
$envFile = __DIR__ . '/../.env';
$envVars = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $envVars[trim($parts[0])] = trim($parts[1], " \t\n\r\"'");
    }
}

$smtpUser = $envVars['email.SMTPUser'] ?? '(not set)';
$smtpPass = $envVars['email.SMTPPass'] ?? '(not set)';
$fromEmail = $envVars['email.fromEmail'] ?? '(not set)';

echo "SMTPUser:  [{$smtpUser}]\n";
echo "SMTPPass:  [" . str_repeat('*', min(4, strlen($smtpPass))) . substr($smtpPass, 4) . "] (len=" . strlen($smtpPass) . ")\n";
echo "fromEmail: [{$fromEmail}]\n\n";

// Check PHPMailer
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "ERROR: PHPMailer not found!\n";
    exit;
}
echo "PHPMailer: OK\n\n";

// Test SMTP connection
echo "=== Connecting to smtp.gmail.com:587 ===\n\n";
try {
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    $mailer->SMTPDebug = 3;
    $mailer->Debugoutput = function($str, $level) {
        echo htmlspecialchars(trim($str)) . "\n";
    };
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtpUser;
    $mailer->Password = $smtpPass;
    $mailer->SMTPSecure = 'tls';
    $mailer->Port = 587;
    $mailer->Timeout = 15;
    $mailer->CharSet = 'UTF-8';
    $mailer->isHTML(true);
    
    $mailer->setFrom($fromEmail, 'EMB-RMS Test');
    $mailer->addAddress($fromEmail);
    $mailer->Subject = 'EMB-RMS SMTP Test';
    $mailer->Body = '<h1>It works!</h1><p>Sent: ' . date('Y-m-d H:i:s') . '</p>';
    
    $mailer->send();
    echo "\n*** SUCCESS - Email sent! ***\n";
} catch (PHPMailer\PHPMailer\Exception $e) {
    echo "\n*** FAILED ***\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "ErrorInfo: " . $mailer->ErrorInfo . "\n";
} catch (Throwable $e) {
    echo "\n*** ERROR ***\n";
    echo $e->getMessage() . "\n";
}
echo "</pre>";
