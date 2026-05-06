<?php
/**
 * Email template for manager escalation on overdue items
 * Sent to manager when item is 7+ days overdue
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
            background-color: #c62828;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header .critical {
            font-size: 18px;
            margin-top: 10px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .escalation-alert {
            background-color: #ffebee;
            border: 2px solid #c62828;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
        }
        .escalation-alert strong {
            color: #c62828;
            font-size: 18px;
        }
        .item-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
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
            font-weight: 500;
        }
        .action-required {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .action-required strong {
            color: #e65100;
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
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚨 ESCALATION ALERT</h1>
            <div class="critical">Overdue Document Return Required</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <p>Hello <?php echo htmlspecialchars($manager_name ?? 'Manager'); ?>,</p>
            </div>

            <!-- Escalation Alert -->
            <div class="escalation-alert">
                <strong>⚠️ CRITICAL: ITEM 7+ DAYS OVERDUE</strong><br><br>
                A document borrowed by <strong><?php echo htmlspecialchars($requested_by); ?></strong> has not been returned and is exceeding our retention policy.
            </div>

            <!-- Item Details -->
            <p><strong>Overdue Item Details:</strong></p>
            <div class="item-details">
                <div class="detail-row">
                    <span class="detail-label">Document Code:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($file_code); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Company/Title:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($company_name ?? 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Borrowed By:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($borrower_name); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Requested By (Staff):</span>
                    <span class="detail-value"><?php echo htmlspecialchars($requested_by); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Expected Return Date:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars(date('M d, Y', strtotime($expected_return_date))); ?></strong></span>
                </div>
            </div>

            <!-- Action Required -->
            <div class="action-required">
                <strong>Manager Action Required:</strong><br><br>
                Please contact <strong><?php echo htmlspecialchars($requested_by); ?></strong> to remind them about returning this document.
                If the item is critical to operations, you may need to follow up with the borrower directly.
            </div>

            <!-- Info -->
            <div class="info-box">
                <strong>ℹ️ Important Information:</strong><br><br>
                • The borrower has already been sent multiple reminders (1 day, 3 days, and 7 days overdue)<br>
                • This escalation indicates a prolonged retention of the document<br>
                • Prompt return ensures compliance with our retention policies<br>
                • If the item cannot be returned, please update the Records Management System
            </div>

            <p>
                If the item has already been returned or if there are special circumstances, please contact the Records Management Office immediately to update the system.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                <strong>Records Management System</strong><br>
                Automatic Escalation Notification
            </div>
            <div class="footer-text" style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px;">
                Sent on <?php echo date('M d, Y \a\t g:i A'); ?>
            </div>
        </div>
    </div>
</body>
</html>
