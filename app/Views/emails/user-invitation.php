<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to EMB-RMS</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 8px; }
        .header { background-color: #2563eb; color: #ffffff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 30px; background-color: #ffffff; }
        .otp-box { background-color: #f3f4f6; border: 2px dashed #2563eb; color: #2563eb; font-size: 32px; font-weight: bold; text-align: center; padding: 20px; margin: 20px 0; letter-spacing: 5px; border-radius: 8px; }
        .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; padding: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to EMB-RMS</h1>
        </div>
        <div class="content">
            <p>Hello <strong><?= esc($username) ?></strong>,</p>
            <p>Your account has been created on the <strong>Environmental Management Bureau Records Management System (EMB-RMS)</strong>.</p>
            <p>To finalize your setup and activate your account as <strong><?= esc($role) ?></strong>, please use the following activation code (OTP) to set your password:</p>
            
            <div class="otp-box">
                <?= esc($otp) ?>
            </div>

            <p>Click the button below to proceed to the password setup page:</p>
            <div style="text-align: center;">
                <a href="<?= esc($url) ?>" class="btn">Set My Password</a>
            </div>

            <p>If you did not expect this email, please contact the IT Administrator.</p>
            <p>Thank you,<br>EMB-RMS Team</p>
        </div>
        <div class="footer">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?= date('Y') ?> Environmental Management Bureau. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
