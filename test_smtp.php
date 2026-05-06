<?php
require_once __DIR__ . '/vendor/autoload.php';

$envVars = [];
foreach (file(__DIR__ . '/.env') as $line) {
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
echo "SMTPPass:  [" . str_repeat('*', 4) . substr($smtpPass, 4) . "] (len=" . strlen($smtpPass) . ")\n";
echo "fromEmail: [{$fromEmail}]\n\n";

echo "Connecting to smtp.gmail.com:587...\n";

try {
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtpUser;
    $mailer->Password = $smtpPass;
    $mailer->SMTPSecure = 'tls';
    $mailer->Port = 587;
    $mailer->Timeout = 15;
    $mailer->isHTML(true);
    $mailer->CharSet = 'UTF-8';

    $mailer->setFrom($fromEmail, 'EMB-RMS Test');
    $mailer->addAddress($fromEmail);
    $mailer->Subject = 'EMB-RMS SMTP Test';
    $mailer->Body = '<h1>It works!</h1>';

    $mailer->send();
    echo "SUCCESS - Email sent!\n";
} catch (PHPMailer\PHPMailer\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "ErrorInfo: " . $mailer->ErrorInfo . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
