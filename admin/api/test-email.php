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
    // Use PHPMailer-style SMTP connection with fsockopen for testing
    $success = sendViaSMTP($smtp_host, $smtp_port, $smtp_username, $smtp_password, 
                          $smtp_encryption, $from_email, $from_name, $test_email, $subject, $message);
    
    if ($success) {
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
                $notification_id, $test_email, $subject, 'Test email via SMTP', 
                $admin_id, "Email configuration test - SMTP: {$smtp_host}:{$smtp_port}"
            ]);
        } catch (Exception $e) {
            // Don't fail if logging fails
            error_log("Failed to log test email: " . $e->getMessage());
        }
        
        json_response(true, 'Test email sent successfully via SMTP!');
    } else {
        json_response(false, 'SMTP test failed. Please check your Brevo SMTP settings:
1. Verify SMTP Host: smtp-relay.brevo.com
2. Verify Port: 587
3. Verify your Brevo login email
4. Verify your Brevo SMTP Key (not account password)
5. Ensure "From Email" is verified in your Brevo account');
    }
    
} catch (Exception $e) {
    error_log("Test email error: " . $e->getMessage());
    json_response(false, 'Failed to send test email: ' . $e->getMessage());
}

/**
 * Send email via SMTP using basic PHP sockets
 * This is a simplified SMTP implementation for testing
 */
function sendViaSMTP($host, $port, $username, $password, $encryption, $from_email, $from_name, $to_email, $subject, $message) {
    $boundary = uniqid();
    
    // Create socket context
    $context = stream_context_create();
    
    if ($encryption === 'ssl') {
        $host = 'ssl://' . $host;
        $port = $port ?: 465;
    }
    
    // Connect to SMTP server
    $smtp = fsockopen($host, $port, $errno, $errstr, 30);
    if (!$smtp) {
        error_log("SMTP Connection failed: {$errstr} ({$errno})");
        return false;
    }
    
    // Read server response
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '220') {
        fclose($smtp);
        return false;
    }
    
    // Send EHLO
    fputs($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    $response = fgets($smtp, 515);
    
    // Start TLS if required
    if ($encryption === 'tls') {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return false;
        }
        
        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            return false;
        }
        
        // Send EHLO again after TLS
        fputs($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $response = fgets($smtp, 515);
    }
    
    // Authenticate
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '334') {
        fclose($smtp);
        return false;
    }
    
    // Send username
    fputs($smtp, base64_encode($username) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '334') {
        fclose($smtp);
        return false;
    }
    
    // Send password
    fputs($smtp, base64_encode($password) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '235') {
        error_log("SMTP Auth failed: " . $response);
        fclose($smtp);
        return false;
    }
    
    // Send MAIL FROM
    fputs($smtp, "MAIL FROM:<{$from_email}>\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '250') {
        fclose($smtp);
        return false;
    }
    
    // Send RCPT TO
    fputs($smtp, "RCPT TO:<{$to_email}>\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '250') {
        fclose($smtp);
        return false;
    }
    
    // Send DATA
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '354') {
        fclose($smtp);
        return false;
    }
    
    // Send email headers and body
    $email_data = "From: {$from_name} <{$from_email}>\r\n";
    $email_data .= "To: {$to_email}\r\n";
    $email_data .= "Subject: {$subject}\r\n";
    $email_data .= "MIME-Version: 1.0\r\n";
    $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email_data .= "X-Mailer: HabeshaEqub System\r\n";
    $email_data .= "\r\n";
    $email_data .= $message;
    $email_data .= "\r\n.\r\n";
    
    fputs($smtp, $email_data);
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '250') {
        fclose($smtp);
        return false;
    }
    
    // Send QUIT
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    return true;
}
?>