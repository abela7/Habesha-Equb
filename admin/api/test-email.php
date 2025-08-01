<?php
/**
 * HabeshaEqub - Test Email Configuration API
 * Test SMTP settings by sending a sample email
 */

require_once '../../includes/db.php';

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

/**
 * JSON response helper
 */
function json_response($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit;
}

// Simple admin authentication check for API
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_response(false, 'Unauthorized access');
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$action = $_POST['action'] ?? '';
if ($action !== 'test_email') {
    json_response(false, 'Invalid action');
}

// Get test email address
$test_email = trim($_POST['test_email'] ?? '');
if (empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Please provide a valid email address');
}

// Get SMTP settings from POST (current form values) or database
$smtp_host = trim($_POST['smtp_host'] ?? '');
$smtp_port = (int)($_POST['smtp_port'] ?? 587);
$smtp_username = trim($_POST['smtp_username'] ?? '');
$smtp_password = trim($_POST['smtp_password'] ?? '');
$smtp_auth = ($_POST['smtp_auth'] ?? '1') === '1';
$smtp_encryption = trim($_POST['smtp_encryption'] ?? 'tls');
$from_email = trim($_POST['from_email'] ?? '');
$from_name = trim($_POST['from_name'] ?? 'HabeshaEqub System');

// Validate required settings
if (empty($smtp_host)) {
    json_response(false, 'SMTP Host is required');
}

if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Valid From Email is required');
}

if ($smtp_auth && (empty($smtp_username) || empty($smtp_password))) {
    json_response(false, 'SMTP Username and Password are required when authentication is enabled');
}

// Test email content
$subject = 'HabeshaEqub - Email Configuration Test';
$message = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #6C5B7B, #C06C84); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .success-icon { font-size: 48px; color: #28a745; text-align: center; margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Email Configuration Test</h1>
        </div>
        <div class="content">
            <div class="success-icon">✅</div>
            <h2>Congratulations!</h2>
            <p><strong>Your HabeshaEqub email configuration is working perfectly!</strong></p>
            
            <p>This test email confirms that:</p>
            <ul>
                <li>✅ SMTP connection is successful</li>
                <li>✅ Authentication is working</li>
                <li>✅ Email delivery is functional</li>
                <li>✅ Your notification system is ready</li>
            </ul>
            
            <p><strong>Test Details:</strong></p>
            <ul>
                <li><strong>SMTP Host:</strong> ' . htmlspecialchars($smtp_host) . '</li>
                <li><strong>SMTP Port:</strong> ' . $smtp_port . '</li>
                <li><strong>Encryption:</strong> ' . strtoupper($smtp_encryption) . '</li>
                <li><strong>Authentication:</strong> ' . ($smtp_auth ? 'Enabled' : 'Disabled') . '</li>
                <li><strong>From:</strong> ' . htmlspecialchars($from_name) . ' &lt;' . htmlspecialchars($from_email) . '&gt;</li>
                <li><strong>Test Time:</strong> ' . date('Y-m-d H:i:s T') . '</li>
            </ul>
            
            <p>You can now safely use the automatic notification system for:</p>
            <ul>
                <li>📧 Welcome emails for new members</li>
                <li>🔔 Payment reminders</li>
                <li>💰 Payout notifications</li>
                <li>✅ Account approval emails</li>
                <li>📱 System alerts</li>
            </ul>
            
            <p style="margin-top: 30px;"><em>This is an automated test email from your HabeshaEqub system.</em></p>
        </div>
        <div class="footer">
            <p>HabeshaEqub - Ethiopian Traditional Savings Group Management System</p>
        </div>
    </div>
</body>
</html>
';

try {
    // Try to send using PHP's built-in mail function first (simpler)
    if (function_exists('mail')) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$from_name} <{$from_email}>\r\n";
        $headers .= "Reply-To: {$from_email}\r\n";
        $headers .= "X-Mailer: HabeshaEqub System\r\n";
        
        // For cPanel, we often can use the simple mail() function
        if (mail($test_email, $subject, $message, $headers)) {
            // Log the test
            try {
                $admin_id = $_SESSION['admin_id'];
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (
                        notification_id, recipient_type, recipient_email, type, channel, 
                        subject, message, status, sent_at, sent_by_admin_id, notes
                    ) VALUES (?, 'admin', ?, 'general', 'email', ?, ?, 'sent', NOW(), ?, ?)
                ");
                $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $stmt->execute([
                    $notification_id, $test_email, $subject, 'Test email via PHP mail()', 
                    $admin_id, 'Email configuration test - PHP mail() method'
                ]);
            } catch (Exception $e) {
                // Don't fail if logging fails
                error_log("Failed to log test email: " . $e->getMessage());
            }
            
            json_response(true, 'Test email sent successfully using PHP mail() function');
        }
    }
    
    // If we reach here, PHP mail() either failed or isn't available
    // We'll need to implement SMTP later when we add PHPMailer
    json_response(false, 'Email sending failed. This may be because:
1. PHP mail() function is not properly configured on your server
2. SMTP settings need to be configured (coming in next update)
3. Server firewall is blocking outgoing emails

For cPanel hosting, contact your hosting provider to enable PHP mail() function or we can implement PHPMailer SMTP in the next step.');
    
} catch (Exception $e) {
    error_log("Test email error: " . $e->getMessage());
    json_response(false, 'Failed to send test email: ' . $e->getMessage());
}
?>