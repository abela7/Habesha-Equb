<?php
/**
 * HabeshaEqub - Email & Notifications Management
 * Configure email settings, test email delivery, and manage notification system
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get current email settings
$current_settings = [];
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'email'");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $current_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_category VARCHAR(50) DEFAULT 'general',
        setting_type VARCHAR(20) DEFAULT 'text',
        setting_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

function getSetting($key, $default = '') {
    global $current_settings;
    return $current_settings[$key] ?? $default;
}

$message = '';
$messageType = '';
$testResult = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_settings') {
        try {
            $smtp_host = trim($_POST['smtp_host'] ?? '');
            $smtp_port = trim($_POST['smtp_port'] ?? '587');
            $smtp_username = trim($_POST['smtp_username'] ?? '');
            $smtp_password = trim($_POST['smtp_password'] ?? '');
            $smtp_encryption = trim($_POST['smtp_encryption'] ?? 'tls');
            $from_email = trim($_POST['from_email'] ?? '');
            $from_name = trim($_POST['from_name'] ?? '');
            
            $settings = [
                'smtp_host' => $smtp_host,
                'smtp_port' => $smtp_port,
                'smtp_username' => $smtp_username,
                'smtp_password' => $smtp_password,
                'smtp_encryption' => $smtp_encryption,
                'smtp_auth' => '1',
                'from_email' => $from_email,
                'from_name' => $from_name
            ];
            
            $pdo->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->execute([$value, $key]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, setting_description, created_at) VALUES (?, ?, 'email', 'text', ?, NOW())");
                    $description = ucfirst(str_replace('_', ' ', $key));
                    $stmt->execute([$key, $value, $description]);
                }
            }
            
            $pdo->commit();
            
            // Refresh current settings
            $current_settings = [];
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'email'");
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $current_settings[$row['setting_key']] = $row['setting_value'];
            }
            
            $message = "Email settings saved successfully!";
            $messageType = "success";
            
        } catch (Exception $e) {
            $pdo->rollback();
            $message = "Error saving settings: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if ($action === 'test_email') {
        $test_email = trim($_POST['test_email'] ?? '');
        $from_email = trim($_POST['test_from_email'] ?? getSetting('from_email'));
        
        if (empty($test_email) || empty($from_email)) {
            $message = "Please provide both test email and from email addresses!";
            $messageType = "danger";
        } else {
            $testResult = testEmailDelivery($test_email, $from_email);
            
            // Log the test in notifications table
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (
                        notification_id, recipient_type, recipient_email, type, channel, 
                        subject, message, status, sent_at, sent_by_admin_id, notes
                    ) VALUES (?, 'admin', ?, 'general', 'email', ?, ?, ?, NOW(), ?, ?)
                ");
                $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $status = $testResult['success'] ? 'sent' : 'failed';
                $notes = $testResult['success'] ? 'Email test successful' : 'Email test failed: ' . $testResult['message'];
                
                $stmt->execute([
                    $notification_id, $test_email, 'HabeshaEqub Email Test', 
                    $testResult['message'], $status, $admin_id, $notes
                ]);
            } catch (Exception $e) {
                error_log("Failed to log email test: " . $e->getMessage());
            }
        }
    }
}

/**
 * Test email delivery with multiple connection methods and better diagnostics
 */
function testEmailDelivery($test_email, $from_email) {
    $smtp_host = getSetting('smtp_host');
    $smtp_port = (int)getSetting('smtp_port');
    $smtp_username = getSetting('smtp_username');
    $smtp_password = getSetting('smtp_password');
    $smtp_encryption = getSetting('smtp_encryption');
    $from_name = getSetting('from_name');
    
    $start_time = microtime(true);
    $report = [];
    
    try {
        $report[] = "🔍 Starting email delivery test...";
        $report[] = "📧 Test email: {$test_email}";
        $report[] = "📤 From: {$from_name} <{$from_email}>";
        $report[] = "🌐 SMTP: {$smtp_host}:{$smtp_port}";
        $report[] = "";
        
        // First try to resolve hostname using multiple methods
        $report[] = "🔍 Checking DNS resolution...";
        
        // Method 1: Use gethostbyname
        $ip = gethostbyname($smtp_host);
        if ($ip !== $smtp_host) {
            $report[] = "✅ DNS resolved: {$smtp_host} → {$ip}";
        } else {
            $report[] = "❌ DNS resolution failed for {$smtp_host}";
            
            // Method 2: Try using curl to test connectivity
            $report[] = "🔧 Trying alternative connection method...";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "smtp://{$smtp_host}:{$smtp_port}");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($curl_error) {
                $report[] = "❌ CURL connection failed: {$curl_error}";
                
                // Method 3: Try alternative Brevo SMTP servers
                $report[] = "🔧 Trying alternative Brevo servers...";
                $alternative_hosts = [
                    'smtp-relay.sendinblue.com',  // Old Brevo hostname
                    '178.33.242.93',              // Brevo IP (may change)
                ];
                
                foreach ($alternative_hosts as $alt_host) {
                    $alt_ip = gethostbyname($alt_host);
                    if ($alt_ip !== $alt_host || filter_var($alt_host, FILTER_VALIDATE_IP)) {
                        $report[] = "✅ Alternative found: {$alt_host} → {$alt_ip}";
                        $smtp_host = $alt_host;
                        break;
                    } else {
                        $report[] = "❌ Alternative failed: {$alt_host}";
                    }
                }
            } else {
                $report[] = "✅ CURL connection successful";
            }
        }
        
        // Create socket connection
        $report[] = "";
        $report[] = "🔗 Attempting socket connection...";
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $smtp = stream_socket_client("tcp://{$smtp_host}:{$smtp_port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        
        if (!$smtp) {
            $report[] = "❌ Socket connection failed: {$errstr} ({$errno})";
            
            // Try direct IP connection if hostname failed
            if ($ip && $ip !== $smtp_host) {
                $report[] = "🔧 Trying direct IP connection: {$ip}";
                $smtp = stream_socket_client("tcp://{$ip}:{$smtp_port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
                if (!$smtp) {
                    $report[] = "❌ Direct IP connection also failed: {$errstr} ({$errno})";
                    return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
                } else {
                    $report[] = "✅ Direct IP connection successful!";
                }
            } else {
                return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
            }
        } else {
            $report[] = "✅ Socket connection successful";
        }
        
        // Read welcome message
        $response = fgets($smtp, 515);
        $report[] = "📨 Server welcome: " . trim($response);
        
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            $report[] = "❌ Server not ready";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // Send EHLO
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        $report[] = "🤝 EHLO response: " . trim($response);
        
        // Read all capabilities
        while (substr($response, 3, 1) === '-') {
            $response = fgets($smtp, 515);
            $report[] = "   Extension: " . trim($response);
        }
        
        if ($smtp_encryption === 'tls') {
            $report[] = "🔐 Starting TLS encryption...";
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            $report[] = "🔒 STARTTLS response: " . trim($response);
            
            if (substr($response, 0, 3) !== '220') {
                fclose($smtp);
                $report[] = "❌ STARTTLS failed";
                return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
            }
            
            if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($smtp);
                $report[] = "❌ TLS encryption failed";
                return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
            }
            
            $report[] = "✅ TLS encryption enabled";
            
            // EHLO again after TLS
            fputs($smtp, "EHLO habeshaequb.com\r\n");
            $response = fgets($smtp, 515);
            while (substr($response, 3, 1) === '-') {
                $response = fgets($smtp, 515);
            }
        }
        
        // Authentication
        $report[] = "🔑 Authenticating...";
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        
        if (substr($response, 0, 3) !== '334') {
            fclose($smtp);
            $report[] = "❌ AUTH LOGIN failed: " . trim($response);
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // Send username
        fputs($smtp, base64_encode($smtp_username) . "\r\n");
        $response = fgets($smtp, 515);
        
        if (substr($response, 0, 3) !== '334') {
            fclose($smtp);
            $report[] = "❌ Username failed: " . trim($response);
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // Send password
        fputs($smtp, base64_encode($smtp_password) . "\r\n");
        $response = fgets($smtp, 515);
        
        if (substr($response, 0, 3) !== '235') {
            fclose($smtp);
            $report[] = "❌ Authentication failed: " . trim($response);
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        $report[] = "✅ Authentication successful";
        
        // MAIL FROM
        fputs($smtp, "MAIL FROM:<{$from_email}>\r\n");
        $response = fgets($smtp, 515);
        $report[] = "📤 MAIL FROM: " . trim($response);
        
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            $report[] = "❌ MAIL FROM failed - check if '{$from_email}' is verified in Brevo";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // RCPT TO
        fputs($smtp, "RCPT TO:<{$test_email}>\r\n");
        $response = fgets($smtp, 515);
        $report[] = "📨 RCPT TO: " . trim($response);
        
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            $report[] = "❌ RCPT TO failed";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        $report[] = "📝 DATA: " . trim($response);
        
        if (substr($response, 0, 3) !== '354') {
            fclose($smtp);
            $report[] = "❌ DATA command failed";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // Send email content
        $delivery_time = round((microtime(true) - $start_time) * 1000, 2);
        $email_content = "From: {$from_name} <{$from_email}>\r\n";
        $email_content .= "To: {$test_email}\r\n";
        $email_content .= "Subject: HabeshaEqub Email Test - Success Report\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= "✅ EMAIL DELIVERY TEST SUCCESSFUL!\r\n\r\n";
        $email_content .= "Your HabeshaEqub email system is working correctly.\r\n\r\n";
        $email_content .= "Test Details:\r\n";
        $email_content .= "- From: {$from_name} <{$from_email}>\r\n";
        $email_content .= "- To: {$test_email}\r\n";
        $email_content .= "- SMTP Server: {$smtp_host}:{$smtp_port}\r\n";
        $email_content .= "- Encryption: " . strtoupper($smtp_encryption) . "\r\n";
        $email_content .= "- Delivery Time: {$delivery_time}ms\r\n";
        $email_content .= "- Test Time: " . date('Y-m-d H:i:s T') . "\r\n\r\n";
        $email_content .= "This email confirms that your SMTP configuration is working properly.\r\n";
        $email_content .= "\r\n.\r\n";
        
        fputs($smtp, $email_content);
        $response = fgets($smtp, 515);
        $report[] = "📮 Email sent: " . trim($response);
        
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            $report[] = "❌ Email delivery failed";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        $final_delivery_time = round((microtime(true) - $start_time) * 1000, 2);
        $report[] = "";
        $report[] = "🎉 EMAIL DELIVERED SUCCESSFULLY!";
        $report[] = "⏱️ Total delivery time: {$final_delivery_time}ms";
        $report[] = "📬 Check your inbox for the test email.";
        
        return [
            'success' => true, 
            'message' => implode("\n", $report), 
            'delivery_time' => $final_delivery_time,
            'recipient' => $test_email
        ];
        
    } catch (Exception $e) {
        $report[] = "❌ Exception: " . $e->getMessage();
        return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email & Notifications - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* === EMAIL CONFIGURATION PAGE DESIGN === */
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-title-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 400;
        }

        .page-actions .btn {
            padding: 16px 32px;
            font-weight: 700;
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
            border: none;
            min-width: 180px;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Configuration Sections */
        .config-section {
            background: white;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.06);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-light);
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .section-icon.primary {
            background: linear-gradient(135deg, var(--color-purple) 0%, var(--color-coral) 100%);
        }

        .section-icon.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .section-icon.warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0;
        }

        .section-description {
            color: var(--text-secondary);
            margin: 0;
            font-size: 16px;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-light);
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-purple);
            box-shadow: 0 0 0 0.2rem rgba(108, 91, 123, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-purple) 0%, var(--color-coral) 100%);
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Test Result Display */
        .test-result {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin-top: 24px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-line;
            max-height: 400px;
            overflow-y: auto;
            border-left: 4px solid #17a2b8;
        }

        .test-result.success {
            border-left-color: #28a745;
            background: #f8fff8;
        }

        .test-result.error {
            border-left-color: #dc3545;
            background: #fff8f8;
        }

        /* Status Display */
        .settings-status {
            background: var(--color-cream);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .status-item {
            text-align: center;
        }

        .status-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .status-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-purple);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .config-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1>
                <div class="page-title-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                Email & Notifications
            </h1>
            <p class="page-subtitle">Configure SMTP settings, test email delivery, and manage notification system</p>
        </div>
        <div class="page-actions">
            <a href="settings.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Settings
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Current Configuration Status -->
    <div class="config-section">
        <div class="section-header">
            <div class="section-icon primary">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <h2 class="section-title">Current Configuration</h2>
                <p class="section-description">Current SMTP settings and status</p>
            </div>
        </div>

        <div class="settings-status">
            <div class="status-grid">
                <div class="status-item">
                    <div class="status-label">SMTP Host</div>
                    <div class="status-value"><?= htmlspecialchars(getSetting('smtp_host')) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-label">Port</div>
                    <div class="status-value"><?= htmlspecialchars(getSetting('smtp_port')) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-label">Username</div>
                    <div class="status-value"><?= htmlspecialchars(getSetting('smtp_username')) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-label">Encryption</div>
                    <div class="status-value"><?= strtoupper(getSetting('smtp_encryption')) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-label">From Name</div>
                    <div class="status-value"><?= htmlspecialchars(getSetting('from_name')) ?></div>
                </div>
                <div class="status-item">
                    <div class="status-label">From Email</div>
                    <div class="status-value"><?= htmlspecialchars(getSetting('from_email')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMTP Configuration -->
    <div class="config-section">
        <div class="section-header">
            <div class="section-icon primary">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h2 class="section-title">SMTP Configuration</h2>
                <p class="section-description">Configure your email server settings</p>
            </div>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="action" value="save_settings">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('smtp_host')) ?>" required>
                    <small class="text-muted">e.g., smtp-relay.brevo.com</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="smtp_port" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('smtp_port')) ?>" required>
                    <small class="text-muted">587 (TLS) or 465 (SSL)</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" name="smtp_username" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('smtp_username')) ?>" required>
                    <small class="text-muted">Your SMTP login</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" name="smtp_password" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('smtp_password')) ?>" required>
                    <small class="text-muted">SMTP key or app password</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Encryption</label>
                    <select name="smtp_encryption" class="form-select">
                        <option value="tls" <?= getSetting('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                        <option value="ssl" <?= getSetting('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= getSetting('smtp_encryption') === 'none' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Email</label>
                    <input type="email" name="from_email" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('from_email')) ?>" required>
                    <small class="text-muted">Must be verified in your email provider</small>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">From Name</label>
                    <input type="text" name="from_name" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('from_name')) ?>" required>
                    <small class="text-muted">Display name for outgoing emails</small>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    Save Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Email Testing -->
    <div class="config-section">
        <div class="section-header">
            <div class="section-icon success">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div>
                <h2 class="section-title">Email Delivery Testing</h2>
                <p class="section-description">Test your email configuration with comprehensive diagnostics</p>
            </div>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="action" value="test_email">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Test Email Address</label>
                    <input type="email" name="test_email" class="form-control" 
                           placeholder="Enter email to receive test" required>
                    <small class="text-muted">Where should we send the test email?</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Email</label>
                    <input type="email" name="test_from_email" class="form-control" 
                           value="<?= htmlspecialchars(getSetting('from_email')) ?>" required>
                    <small class="text-muted">Must be verified in your email provider</small>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane me-2"></i>
                    Send Test Email
                </button>
                <a href="test-your-brevo.php" class="btn btn-outline-primary">
                    <i class="fas fa-tools me-2"></i>
                    Simple Test Tool
                </a>
            </div>
        </form>

        <?php if ($testResult): ?>
            <div class="test-result <?= $testResult['success'] ? 'success' : 'error' ?>">
                <h5><?= $testResult['success'] ? '✅ Email Test Result: SUCCESS' : '❌ Email Test Result: FAILED' ?></h5>
                <?php if ($testResult['success']): ?>
                    <p><strong>✅ Email delivered successfully!</strong></p>
                    <p><strong>📧 Recipient:</strong> <?= htmlspecialchars($testResult['recipient']) ?></p>
                    <p><strong>⏱️ Delivery Time:</strong> <?= $testResult['delivery_time'] ?>ms</p>
                    <hr>
                <?php endif; ?>
                <strong>📊 Detailed Report:</strong>
                <?= htmlspecialchars($testResult['message']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            });
        }, 5000);
    </script>
</body>
</html>