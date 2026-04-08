<?php
/**
 * Email template for overdue borrower notifications
 * Sends at 1, 3, 7 days overdue
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #d32f2f;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .item-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #0275d8;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .action-text {
            margin: 20px 0;
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            border-radius: 4px;
        }
        .days-overdue {
            font-size: 20px;
            font-weight: bold;
            color: #d32f2f;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }
        .footer-text {
            margin: 5px 0;
        }
        a {
            color: #0275d8;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⏰ Item Return Reminder</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <p>Hello <?php echo htmlspecialchars($borrower_name ?? 'Borrower'); ?>,</p>
            </div>

            <!-- Alert -->
            <div class="alert-box">
                <strong>⚠️ OVERDUE NOTICE</strong><br>
                The item you borrowed is now <span class="days-overdue"><?php echo htmlspecialchars($days_overdue); ?> days overdue</span>.
            </div>

            <!-- Item Details -->
            <p><strong>We need you to return the following item:</strong></p>
            <div class="item-details">
                <div class="detail-row">
                    <span class="detail-label">Document Code:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars($file_code); ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Company/Title:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($company_name ?? 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Purpose of Borrow:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($purpose ?? 'Not specified'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Expected Return Date:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars(date('M d, Y', strtotime($expected_return_date))); ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Days Overdue:</span>
                    <span class="detail-value"><strong style="color: #d32f2f;"><?php echo htmlspecialchars($days_overdue); ?> days</strong></span>
                </div>
            </div>

            <!-- Action Required -->
            <div class="action-text">
                <strong>✓ What You Need To Do:</strong><br><br>
                <ol>
                    <li>Locate the item with document code <strong><?php echo htmlspecialchars($file_code); ?></strong></li>
                    <li>Bring it to the Records Management Office</li>
                    <li>Return it to the Records Officer on duty</li>
                    <li>You will receive a confirmation once the item is received</li>
                </ol>
            </div>

            <!-- Message -->
            <p>
                <strong>Why does this matter?</strong><br>
                Overdue items disrupt our inventory management and prevent other staff from accessing important documents.
                Please return this item as soon as possible to help us maintain efficient records management.
            </p>

            <p>
                <strong>Questions?</strong><br>
                Contact the Records Management Office at the extension listed below or reply to this email.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                <strong>Records Management System</strong><br>
                This is an automated notification. Please do not reply directly to this email.
            </div>
            <div class="footer-text" style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px;">
                Sent on <?php echo date('M d, Y \a\t g:i A'); ?>
            </div>
        </div>
    </div>
</body>
</html>
