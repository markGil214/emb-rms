<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
</head>
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
        <h2 style="margin:0 0 12px 0;color:#065f46;">Password Reset Request</h2>
        <p style="margin:0 0 16px 0;">Hi <?= esc($username ?? 'User') ?>,</p>
        <p style="margin:0 0 16px 0;">Use this code to reset your EMB-RMS password:</p>
        <div style="margin:0 0 20px 0;padding:16px;text-align:center;border:2px dashed #16a34a;border-radius:8px;background:#f0fdf4;font-size:28px;letter-spacing:6px;font-weight:700;color:#166534;">
            <?= esc($code ?? '') ?>
        </div>
        <p style="margin:0 0 8px 0;">This code expires in <?= esc($expiresInMinutes ?? 15) ?> minutes.</p>
        <p style="margin:0;color:#6b7280;font-size:13px;">If you did not request a password reset, you can ignore this email.</p>
    </div>
</body>
</html>
