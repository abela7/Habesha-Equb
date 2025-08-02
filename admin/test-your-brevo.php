<?php
/**
 * Test Your Exact Brevo Settings
 * Using the provided SMTP configuration
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
    <title>Test Your Brevo Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none; border-radius: 15px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-rocket"></i> Test Your Brevo Settings</h4>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Your Brevo Configuration:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>SMTP Server:</strong> smtp-relay.brevo.com<br>
                                <strong>Port:</strong> 587<br>
                                <strong>Login:</strong> 92bed1001@smtp-brevo.com
                            </div>
                            <div class="col-md-6">
                                <strong>Password:</strong> 8VgfHCdmsZX0whkx<br>
                                <strong>Encryption:</strong> TLS (STARTTLS)<br>
                                <strong>Status:</strong> Ready to test!
                            </div>
                        </div>
                    </div>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $test_email = $_POST['test_email'] ?? '';
                        $from_email = $_POST['from_email'] ?? '';
                        
                        if (empty($test_email) || empty($from_email)) {
                            echo '<div class="alert alert-danger">❌ Please fill all fields!</div>';
                        } else {
                            echo '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Testing your Brevo configuration...</div>';
                            
                            // Test with your exact settings
                            $result = testYourBrevo($test_email, $from_email);
                            
                            if ($result['success']) {
                                echo '<div class="alert alert-success">' . $result['message'] . '</div>';
                                
                                // If successful, save settings to database
                                saveToDatabase();
                                echo '<div class="alert alert-info"><i class="fas fa-database"></i> Settings saved to database for future use!</div>';
                                
                            } else {
                                echo '<div class="alert alert-danger">' . $result['message'] . '</div>';
                            }
                        }
                    }
                    ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Test Email Address</label>
                            <input type="email" name="test_email" class="form-control" placeholder="Enter your email to receive test" required>
                            <small class="text-muted">Where should we send the test email?</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">From Email (Must be verified in your Brevo account)</label>
                            <input type="email" name="from_email" class="form-control" placeholder="noreply@yourdomain.com" required>
                            <small class="text-muted">This must be a verified sender in your Brevo account</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane"></i> Test Your Brevo Settings Now
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-tools"></i> Other Tools:</h6>
                            <a href="brevo-test-advanced.php" class="btn btn-sm btn-outline-primary">Advanced Test</a>
                            <a href="email-config-working.php" class="btn btn-sm btn-outline-secondary">Configuration</a>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-lightbulb"></i> Remember:</h6>
                            <small class="text-muted">
                                Make sure your "From Email" is verified in your Brevo account under Senders & IP.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
/**
 * Test function with your exact Brevo settings
 */
function testYourBrevo($test_email, $from_email) {
    // Your exact Brevo settings
    $smtp_host = 'smtp-relay.brevo.com';
    $smtp_port = 587;
    $smtp_username = '92bed1001@smtp-brevo.com';
    $smtp_password = '8VgfHCdmsZX0whkx';
    
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
        $smtp = stream_socket_client("tcp://{$smtp_host}:{$smtp_port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        
        if (!$smtp) {
            return ['success' => false, 'message' => "❌ Connection failed: {$errstr} ({$errno})"];
        }
        
        // Read welcome message
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ Server not ready: {$response}"];
        }
        
        // Send EHLO and read all responses
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ EHLO failed: {$response}"];
        }
        
        // Read all EHLO responses (multi-line)
        $capabilities = [trim($response)];
        while (substr($response, 3, 1) === '-') {
            $response = fgets($smtp, 515);
            $capabilities[] = trim($response);
        }
        
        // Start TLS
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ STARTTLS failed: " . trim($response) . ". Server capabilities: " . implode(', ', $capabilities)];
        }
        
        // Enable TLS encryption
        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ TLS encryption failed"];
        }
        
        // Send EHLO again after TLS
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        while (substr($response, 3, 1) === '-') {
            $response = fgets($smtp, 515);
        }
        
        // Authenticate
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '334') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ AUTH LOGIN failed: " . trim($response)];
        }
        
        // Send username
        fputs($smtp, base64_encode($smtp_username) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '334') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ Username authentication failed: " . trim($response)];
        }
        
        // Send password
        fputs($smtp, base64_encode($smtp_password) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '235') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ Password authentication failed: " . trim($response) . ". Check your Brevo master password!"];
        }
        
        // Send MAIL FROM
        fputs($smtp, "MAIL FROM:<{$from_email}>\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ MAIL FROM failed: " . trim($response) . ". Make sure '{$from_email}' is verified in your Brevo account!"];
        }
        
        // Send RCPT TO
        fputs($smtp, "RCPT TO:<{$test_email}>\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ RCPT TO failed: " . trim($response)];
        }
        
        // Send DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '354') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ DATA failed: " . trim($response)];
        }
        
        // Send email content
        $email_content = "From: HabeshaEqub System <{$from_email}>\r\n";
        $email_content .= "To: {$test_email}\r\n";
        $email_content .= "Subject: 🎉 HabeshaEqub - Brevo SMTP Success!\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= "<html><body>";
        $email_content .= "<h2 style='color: #28a745;'>🎉 SUCCESS!</h2>";
        $email_content .= "<p><strong>Your HabeshaEqub email system is now working!</strong></p>";
        $email_content .= "<h3>✅ Verified Configuration:</h3>";
        $email_content .= "<ul>";
        $email_content .= "<li><strong>SMTP Server:</strong> {$smtp_host}</li>";
        $email_content .= "<li><strong>Port:</strong> {$smtp_port}</li>";
        $email_content .= "<li><strong>Login:</strong> {$smtp_username}</li>";
        $email_content .= "<li><strong>Encryption:</strong> TLS (STARTTLS)</li>";
        $email_content .= "<li><strong>From Email:</strong> {$from_email}</li>";
        $email_content .= "<li><strong>Test Time:</strong> " . date('Y-m-d H:i:s T') . "</li>";
        $email_content .= "</ul>";
        $email_content .= "<p><strong>Next Steps:</strong></p>";
        $email_content .= "<ol>";
        $email_content .= "<li>✅ Email configuration is working</li>";
        $email_content .= "<li>🔧 Settings will be saved to your database</li>";
        $email_content .= "<li>📧 You can now send automated notifications</li>";
        $email_content .= "<li>🚀 Ready for member welcome emails, payment reminders, etc.</li>";
        $email_content .= "</ol>";
        $email_content .= "<p style='color: #666; font-size: 12px;'>This email was sent from your HabeshaEqub system using Brevo SMTP.</p>";
        $email_content .= "</body></html>";
        $email_content .= "\r\n.\r\n";
        
        fputs($smtp, $email_content);
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return ['success' => false, 'message' => "❌ Email send failed: " . trim($response)];
        }
        
        // Send QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        return ['success' => true, 'message' => "🎉 SUCCESS! Email sent to {$test_email}. Check your inbox (and spam folder)!"];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "❌ Error: " . $e->getMessage()];
    }
}

/**
 * Save working settings to database
 */
function saveToDatabase() {
    global $pdo;
    
    try {
        $settings = [
            'smtp_host' => 'smtp-relay.brevo.com',
            'smtp_port' => '587',
            'smtp_username' => '92bed1001@smtp-brevo.com',
            'smtp_password' => '8VgfHCdmsZX0whkx',
            'smtp_encryption' => 'tls',
            'smtp_auth' => '1',
            'from_name' => 'HabeshaEqub System'
        ];
        
        foreach ($settings as $key => $value) {
            // Check if setting exists
            $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            
            if ($stmt->fetch()) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, setting_description, created_at) VALUES (?, ?, 'email', 'text', ?, NOW())");
                $description = ucfirst(str_replace('_', ' ', $key));
                $stmt->execute([$key, $value, $description]);
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to save settings: " . $e->getMessage());
        return false;
    }
}
?>