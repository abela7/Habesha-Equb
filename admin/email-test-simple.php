<?php
/**
 * SIMPLE EMAIL TEST - No dependencies, just pure PHP
 * Let's test if basic email sending works first
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple admin check
if (!isset($_SESSION['admin_id'])) {
    die('⛔ Please login as admin first - <a href="login.php">Login Here</a>');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Email Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>🧪 Simple Email Test</h2>
    <p>Let's test email sending step by step</p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $test_email = $_POST['test_email'] ?? '';
        $smtp_host = $_POST['smtp_host'] ?? '';
        $smtp_port = (int)($_POST['smtp_port'] ?? 587);
        $smtp_user = $_POST['smtp_user'] ?? '';
        $smtp_pass = $_POST['smtp_pass'] ?? '';
        $from_email = $_POST['from_email'] ?? '';
        
        if (empty($test_email) || empty($smtp_host) || empty($smtp_user) || empty($smtp_pass) || empty($from_email)) {
            echo '<div class="alert alert-danger">❌ Please fill all fields!</div>';
        } else {
            echo '<div class="alert alert-info">🚀 Testing email with your Brevo settings...</div>';
            
            // Test basic SMTP connection
            $result = testBrevoSMTP($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $from_email, $test_email);
            
            if ($result['success']) {
                echo '<div class="alert alert-success">✅ ' . $result['message'] . '</div>';
            } else {
                echo '<div class="alert alert-danger">❌ ' . $result['message'] . '</div>';
            }
        }
    }
    ?>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Test Email Address</label>
            <input type="email" name="test_email" class="form-control" placeholder="test@example.com" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control" value="smtp-relay.brevo.com" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">SMTP Port</label>
            <input type="number" name="smtp_port" class="form-control" value="587" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">SMTP Username (Your Brevo Login Email)</label>
            <input type="email" name="smtp_user" class="form-control" placeholder="your-email@domain.com" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">SMTP Password (Your Brevo SMTP Key)</label>
            <input type="password" name="smtp_pass" class="form-control" placeholder="xkeysib-..." required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">From Email (Verified in Brevo)</label>
            <input type="email" name="from_email" class="form-control" placeholder="noreply@yourdomain.com" required>
        </div>
        
        <div class="col-12">
            <button type="submit" class="btn btn-primary">🚀 Test Email Now</button>
            <a href="system-configuration.php" class="btn btn-secondary">🔙 Back to Config</a>
        </div>
    </form>
</div>
</body>
</html>

<?php
/**
 * Simple Brevo SMTP Test Function
 */
function testBrevoSMTP($host, $port, $username, $password, $from_email, $to_email) {
    try {
        // Create context for TLS
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        // Connect to SMTP server
        $smtp = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        
        if (!$smtp) {
            return ['success' => false, 'message' => "Connection failed: {$errstr} ({$errno})"];
        }
        
        // Read welcome message
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return ['success' => false, 'message' => "Server not ready: {$response}"];
        }
        
        // Send EHLO
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "EHLO failed: {$response}"];
        }
        
        // Start TLS
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return ['success' => false, 'message' => "STARTTLS failed: {$response}"];
        }
        
        // Enable TLS encryption
        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            return ['success' => false, 'message' => "TLS encryption failed"];
        }
        
        // Send EHLO again after TLS
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        
        // Authenticate
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '334') {
            fclose($smtp);
            return ['success' => false, 'message' => "AUTH LOGIN failed: {$response}"];
        }
        
        // Send username
        fputs($smtp, base64_encode($username) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '334') {
            fclose($smtp);
            return ['success' => false, 'message' => "Username failed: {$response}"];
        }
        
        // Send password
        fputs($smtp, base64_encode($password) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '235') {
            fclose($smtp);
            return ['success' => false, 'message' => "Password/SMTP Key failed: {$response}. Check your Brevo SMTP Key!"];
        }
        
        // Send MAIL FROM
        fputs($smtp, "MAIL FROM:<{$from_email}>\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "MAIL FROM failed: {$response}. Check if '{$from_email}' is verified in Brevo!"];
        }
        
        // Send RCPT TO
        fputs($smtp, "RCPT TO:<{$to_email}>\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "RCPT TO failed: {$response}"];
        }
        
        // Send DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '354') {
            fclose($smtp);
            return ['success' => false, 'message' => "DATA failed: {$response}"];
        }
        
        // Send email content
        $email_content = "From: HabeshaEqub <{$from_email}>\r\n";
        $email_content .= "To: {$to_email}\r\n";
        $email_content .= "Subject: Brevo SMTP Test - SUCCESS!\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= "<h2>🎉 SUCCESS!</h2>";
        $email_content .= "<p>Your Brevo SMTP configuration is working perfectly!</p>";
        $email_content .= "<p><strong>Test Details:</strong></p>";
        $email_content .= "<ul>";
        $email_content .= "<li>SMTP Host: {$host}</li>";
        $email_content .= "<li>SMTP Port: {$port}</li>";
        $email_content .= "<li>Username: {$username}</li>";
        $email_content .= "<li>From Email: {$from_email}</li>";
        $email_content .= "<li>Test Time: " . date('Y-m-d H:i:s T') . "</li>";
        $email_content .= "</ul>";
        $email_content .= "<p>Now you can integrate this into your notification system!</p>";
        $email_content .= "\r\n.\r\n";
        
        fputs($smtp, $email_content);
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "Email send failed: {$response}"];
        }
        
        // Send QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        return ['success' => true, 'message' => "Email sent successfully! Check {$to_email} for the test message."];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error: " . $e->getMessage()];
    }
}
?>