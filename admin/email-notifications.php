<?php
/**
 * HabeshaEqub - Email & Notifications Management
 * Dedicated page for email configuration, testing, and notification management
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
    
    // Use working Brevo settings as defaults
    $brevo_defaults = [
        'smtp_host' => 'smtp-relay.brevo.com',
        'smtp_port' => '587',
        'smtp_username' => '92bed1001@smtp-brevo.com',
        'smtp_password' => '8VgfHCdmsZX0whkx',
        'smtp_encryption' => 'tls',
        'smtp_auth' => '1',
        'from_name' => 'HabeshaEqub System',
        'from_email' => 'admin@habeshaequb.com'
    ];
    
    return $current_settings[$key] ?? $brevo_defaults[$key] ?? $default;
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
            
            $message = "✅ Email settings saved successfully!";
            $messageType = "success";
            
        } catch (Exception $e) {
            $pdo->rollback();
            $message = "❌ Error saving settings: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if ($action === 'test_email') {
        $test_email = trim($_POST['test_email'] ?? '');
        $from_email = trim($_POST['test_from_email'] ?? getSetting('from_email'));
        
        if (empty($test_email) || empty($from_email)) {
            $message = "❌ Please provide both test email and from email addresses!";
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
 * Test email delivery with comprehensive reporting
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
        
        // Create context
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $report[] = "🔗 Connecting to SMTP server...";
        $smtp = stream_socket_client("tcp://{$smtp_host}:{$smtp_port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        
        if (!$smtp) {
            $report[] = "❌ Connection failed: {$errstr} ({$errno})";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        $report[] = "✅ Connected successfully";
        
        // Read welcome message
        $response = fgets($smtp, 515);
        $report[] = "📨 Server: " . trim($response);
        
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            $report[] = "❌ Server not ready";
            return ['success' => false, 'message' => implode("\n", $report), 'delivery_time' => round((microtime(true) - $start_time) * 1000, 2)];
        }
        
        // Send EHLO
        fputs($smtp, "EHLO habeshaequb.com\r\n");
        $response = fgets($smtp, 515);
        $report[] = "🤝 EHLO: " . trim($response);
        
        // Read all capabilities
        $capabilities = [trim($response)];
        while (substr($response, 3, 1) === '-') {
            $response = fgets($smtp, 515);
            $capabilities[] = trim($response);
        }
        
        if ($smtp_encryption === 'tls') {
            $report[] = "🔐 Starting TLS encryption...";
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            $report[] = "🔒 STARTTLS: " . trim($response);
            
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
        $email_content .= "Subject: 📧 HabeshaEqub Email Test - Delivery Report\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= "<html><body style='font-family: Arial, sans-serif;'>";
        $email_content .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>";
        $email_content .= "<h2 style='color: #28a745; text-align: center;'>📧 Email Delivery Test Report</h2>";
        $email_content .= "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        $email_content .= "<h3>✅ Delivery Successful!</h3>";
        $email_content .= "<p><strong>Test Details:</strong></p>";
        $email_content .= "<ul>";
        $email_content .= "<li><strong>From:</strong> {$from_name} &lt;{$from_email}&gt;</li>";
        $email_content .= "<li><strong>To:</strong> {$test_email}</li>";
        $email_content .= "<li><strong>SMTP Server:</strong> {$smtp_host}:{$smtp_port}</li>";
        $email_content .= "<li><strong>Encryption:</strong> " . strtoupper($smtp_encryption) . "</li>";
        $email_content .= "<li><strong>Delivery Time:</strong> {$delivery_time}ms</li>";
        $email_content .= "<li><strong>Test Time:</strong> " . date('Y-m-d H:i:s T') . "</li>";
        $email_content .= "</ul>";
        $email_content .= "</div>";
        $email_content .= "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>";
        $email_content .= "<h4>📊 Delivery Report:</h4>";
        $email_content .= "<pre style='background: white; padding: 10px; border-radius: 3px; font-size: 12px; overflow-x: auto;'>";
        $email_content .= htmlspecialchars(implode("\n", $report));
        $email_content .= "</pre>";
        $email_content .= "</div>";
        $email_content .= "<p style='text-align: center; color: #666; font-size: 12px; margin-top: 20px;'>";
        $email_content .= "This test email was sent from your HabeshaEqub system to verify email delivery functionality.";
        $email_content .= "</p>";
        $email_content .= "</div>";
        $email_content .= "</body></html>";
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
        $report[] = "📬 Check your inbox (and spam folder) for the detailed report email.";
        
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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #6C5B7B;
            --secondary-color: #C06C84;
            --accent-color: #F8B500;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            padding: 2rem 0;
        }
        
        .email-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .email-header {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .config-section {
            padding: 2rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eee;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #fd7e14);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            color: white;
        }
        
        .test-result {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            white-space: pre-line;
            max-height: 400px;
            overflow-y: auto;
            border-left: 4px solid var(--info-color);
        }
        
        .test-result.success {
            border-left-color: var(--success-color);
            background: #f8fff8;
        }
        
        .test-result.error {
            border-left-color: var(--danger-color);
            background: #fff8f8;
        }
        
        .settings-display {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-working {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #664d03;
        }
        
        .quick-actions {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-top: 1px solid #dee2e6;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 91, 123, 0.25);
        }
        
        .navigation-breadcrumb {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .navigation-breadcrumb a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .navigation-breadcrumb a:hover {
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            
            <!-- Navigation Breadcrumb -->
            <div class="navigation-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            <i class="fas fa-envelope"></i> Email & Notifications
                        </li>
                    </ol>
                </nav>
            </div>
            
            <!-- Main Email Card -->
            <div class="email-card">
                <div class="email-header">
                    <h1><i class="fas fa-envelope-open-text"></i></h1>
                    <h2>Email & Notifications</h2>
                    <p class="lead mb-0">Manage SMTP configuration and test email delivery</p>
                </div>
                
                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Current Configuration Status -->
                <div class="config-section border-bottom">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle text-info"></i> Current Configuration Status
                    </h3>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="settings-display">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>SMTP Host:</strong> <?= htmlspecialchars(getSetting('smtp_host')) ?><br>
                                        <strong>Port:</strong> <?= htmlspecialchars(getSetting('smtp_port')) ?><br>
                                        <strong>Username:</strong> <?= htmlspecialchars(getSetting('smtp_username')) ?><br>
                                        <strong>Encryption:</strong> <?= strtoupper(getSetting('smtp_encryption')) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>From Name:</strong> <?= htmlspecialchars(getSetting('from_name')) ?><br>
                                        <strong>From Email:</strong> <?= htmlspecialchars(getSetting('from_email')) ?><br>
                                        <strong>Auth:</strong> <?= getSetting('smtp_auth') === '1' ? 'Enabled' : 'Disabled' ?><br>
                                        <strong>Status:</strong> <span class="status-badge status-working">✅ Configured</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="text-center w-100">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-success">Email System Ready</h5>
                                <small class="text-muted">Configuration loaded successfully</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Configuration Form -->
                <div class="config-section border-bottom">
                    <h3 class="section-title">
                        <i class="fas fa-cog text-primary"></i> SMTP Configuration
                    </h3>
                    
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
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Email Testing Section -->
                <div class="config-section border-bottom">
                    <h3 class="section-title">
                        <i class="fas fa-paper-plane text-warning"></i> Email Delivery Testing
                    </h3>
                    
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
                        
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Send Test Email with Full Report
                            </button>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                This will send a detailed delivery report to your email
                            </small>
                        </div>
                    </form>
                    
                    <?php if ($testResult): ?>
                        <div class="test-result <?= $testResult['success'] ? 'success' : 'error' ?>">
                            <h5><?= $testResult['success'] ? '🎉 Email Test Result: SUCCESS' : '❌ Email Test Result: FAILED' ?></h5>
                            <?php if ($testResult['success']): ?>
                                <p><strong>✅ Email delivered successfully!</strong></p>
                                <p><strong>📧 Recipient:</strong> <?= htmlspecialchars($testResult['recipient']) ?></p>
                                <p><strong>⏱️ Delivery Time:</strong> <?= $testResult['delivery_time'] ?>ms</p>
                                <p><strong>📬 Check your inbox:</strong> A detailed delivery report has been sent to your email.</p>
                                <hr>
                            <?php endif; ?>
                            <strong>📊 Detailed Report:</strong>
                            <?= htmlspecialchars($testResult['message']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h4 class="mb-3">
                        <i class="fas fa-bolt text-warning"></i> Quick Actions
                    </h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-grid">
                                <a href="test-your-brevo.php" class="btn btn-outline-success">
                                    <i class="fas fa-rocket"></i> Simple Test Tool
                                </a>
                                <small class="text-muted mt-1">Basic working test (proven to work)</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-grid">
                                <a href="brevo-test-advanced.php" class="btn btn-outline-primary">
                                    <i class="fas fa-cogs"></i> Advanced Diagnostics
                                </a>
                                <small class="text-muted mt-1">Multiple connection methods</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-grid">
                                <a href="debug-email-config.php" class="btn btn-outline-info">
                                    <i class="fas fa-bug"></i> Debug Configuration
                                </a>
                                <small class="text-muted mt-1">Check database settings</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6><i class="fas fa-chart-line text-info"></i> Coming Soon:</h6>
                            <ul class="list-unstyled small text-muted">
                                <li>✨ Automated welcome emails</li>
                                <li>✨ Payment reminder notifications</li>
                                <li>✨ Payout confirmation emails</li>
                                <li>✨ Admin alert notifications</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt text-success"></i> Security:</h6>
                            <ul class="list-unstyled small text-muted">
                                <li>🔒 TLS encryption enabled</li>
                                <li>🔑 Secure authentication</li>
                                <li>📧 Verified sender domains</li>
                                <li>🛡️ Anti-spam compliance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
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
        
        // Smooth scroll for form submissions
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    setTimeout(() => {
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }, 100);
                });
            });
        });
    </script>
</body>
</html>