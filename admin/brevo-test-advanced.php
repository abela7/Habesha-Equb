<?php
/**
 * Advanced Brevo SMTP Test with Multiple Connection Methods
 * Tests both STARTTLS and direct SSL connections
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
    <title>Advanced Brevo Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>🔧 Advanced Brevo SMTP Test</h2>
    <p>This will try multiple connection methods to find what works with your Brevo account.</p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $test_email = $_POST['test_email'] ?? '';
        $smtp_user = $_POST['smtp_user'] ?? '';
        $smtp_pass = $_POST['smtp_pass'] ?? '';
        $from_email = $_POST['from_email'] ?? '';
        
        if (empty($test_email) || empty($smtp_user) || empty($smtp_pass) || empty($from_email)) {
            echo '<div class="alert alert-danger">❌ Please fill all fields!</div>';
        } else {
            echo '<div class="alert alert-info">🚀 Testing multiple connection methods...</div>';
            
            // Method 1: STARTTLS on port 587
            echo '<h5>Method 1: STARTTLS (Port 587)</h5>';
            $result1 = testBrevoAdvanced('smtp-relay.brevo.com', 587, $smtp_user, $smtp_pass, $from_email, $test_email, 'starttls');
            echo '<div class="alert alert-' . ($result1['success'] ? 'success' : 'warning') . '">' . $result1['message'] . '</div>';
            
            // Method 2: Direct SSL on port 465
            if (!$result1['success']) {
                echo '<h5>Method 2: Direct SSL (Port 465)</h5>';
                $result2 = testBrevoAdvanced('smtp-relay.brevo.com', 465, $smtp_user, $smtp_pass, $from_email, $test_email, 'ssl');
                echo '<div class="alert alert-' . ($result2['success'] ? 'success' : 'warning') . '">' . $result2['message'] . '</div>';
            }
            
            // Method 3: No encryption (testing only)
            if (!$result1['success'] && (!isset($result2) || !$result2['success'])) {
                echo '<h5>Method 3: No Encryption (Testing Only)</h5>';
                $result3 = testBrevoAdvanced('smtp-relay.brevo.com', 587, $smtp_user, $smtp_pass, $from_email, $test_email, 'none');
                echo '<div class="alert alert-' . ($result3['success'] ? 'success' : 'danger') . '">' . $result3['message'] . '</div>';
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
            <label class="form-label">Brevo Username (Your Login Email)</label>
            <input type="email" name="smtp_user" class="form-control" placeholder="your-email@domain.com" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Brevo SMTP Key</label>
            <input type="password" name="smtp_pass" class="form-control" placeholder="xkeysib-..." required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">From Email (Verified in Brevo)</label>
            <input type="email" name="from_email" class="form-control" placeholder="noreply@yourdomain.com" required>
        </div>
        
        <div class="col-12">
            <button type="submit" class="btn btn-primary">🔧 Test All Methods</button>
            <a href="email-test-simple.php" class="btn btn-secondary">🔙 Simple Test</a>
        </div>
    </form>
</div>
</body>
</html>

<?php
/**
 * Advanced Brevo SMTP Test Function with Multiple Methods
 */
function testBrevoAdvanced($host, $port, $username, $password, $from_email, $to_email, $method) {
    try {
        // Create context
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        // Connect based on method
        if ($method === 'ssl') {
            $smtp = stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        } else {
            $smtp = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        }
        
        if (!$smtp) {
            return ['success' => false, 'message' => "Connection failed: {$errstr} ({$errno})"];
        }
        
        // Read welcome message
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return ['success' => false, 'message' => "Server not ready: {$response}"];
        }
        
        // Send EHLO and read all responses
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "EHLO failed: {$response}"];
        }
        
        // Read all EHLO responses (multi-line)
        $capabilities = [$response];
        while (substr($response, 3, 1) === '-') {
            $response = fgets($smtp, 515);
            $capabilities[] = $response;
        }
        
        // Handle STARTTLS if needed
        if ($method === 'starttls') {
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) !== '220') {
                fclose($smtp);
                return ['success' => false, 'message' => "STARTTLS failed: {$response}. Capabilities: " . implode(', ', array_map('trim', $capabilities))];
            }
            
            // Enable TLS encryption
            if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($smtp);
                return ['success' => false, 'message' => "TLS encryption failed"];
            }
            
            // Send EHLO again after TLS
            fputs($smtp, "EHLO habeshaequb.com\r\n");
            $response = fgets($smtp, 515);
            while (substr($response, 3, 1) === '-') {
                $response = fgets($smtp, 515);
            }
        }
        
        // Authenticate (only if not testing without encryption)
        if ($method !== 'none') {
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
                return ['success' => false, 'message' => "Authentication failed: {$response}. Check your Brevo SMTP Key!"];
            }
        }
        
        // Send MAIL FROM
        fputs($smtp, "MAIL FROM:<{$from_email}>\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "MAIL FROM failed: {$response}"];
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
        $email_content .= "Subject: Brevo Test SUCCESS - Method: {$method}\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= "<h2>🎉 SUCCESS with {$method}!</h2>";
        $email_content .= "<p>Connection method: <strong>{$method}</strong></p>";
        $email_content .= "<p>Port: <strong>{$port}</strong></p>";
        $email_content .= "<p>Host: <strong>{$host}</strong></p>";
        $email_content .= "<p>Time: " . date('Y-m-d H:i:s T') . "</p>";
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
        
        return ['success' => true, 'message' => "✅ SUCCESS with {$method} method! Email sent to {$to_email}"];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error: " . $e->getMessage()];
    }
}
?>