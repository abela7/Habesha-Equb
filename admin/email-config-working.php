<?php
/**
 * WORKING EMAIL CONFIGURATION - Fresh Start
 * Simple, functional email configuration without complexity
 */

require_once '../includes/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple admin check
if (!isset($_SESSION['admin_id'])) {
    die('⛔ Please login as admin first - <a href="login.php">Login Here</a>');
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $smtp_host = trim($_POST['smtp_host'] ?? '');
        $smtp_port = trim($_POST['smtp_port'] ?? '587');
        $smtp_username = trim($_POST['smtp_username'] ?? '');
        $smtp_password = trim($_POST['smtp_password'] ?? '');
        $smtp_encryption = trim($_POST['smtp_encryption'] ?? 'tls');
        $from_email = trim($_POST['from_email'] ?? '');
        $from_name = trim($_POST['from_name'] ?? '');
        
        // Create/update settings directly
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
        
        $pdo->commit();
        $message = "✅ Email settings saved successfully!";
        $messageType = "success";
        
    } catch (Exception $e) {
        $pdo->rollback();
        $message = "❌ Error saving settings: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get current settings
$current_settings = [];
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'email'");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $current_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // If table doesn't exist, create it
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
    
    // Use user's known working Brevo settings as defaults
    $brevo_defaults = [
        'smtp_host' => 'smtp-relay.brevo.com',
        'smtp_port' => '587',
        'smtp_username' => '92bed1001@smtp-brevo.com',
        'smtp_password' => '8VgfHCdmsZX0whkx',
        'smtp_encryption' => 'tls',
        'from_name' => 'HabeshaEqub System'
    ];
    
    return $current_settings[$key] ?? $brevo_defaults[$key] ?? $default;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none; border-radius: 15px; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); border: none; }
        .brevo-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-envelope"></i> Email Configuration</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Brevo Quick Setup -->
                    <div class="brevo-info p-3 mb-4">
                        <h6><i class="fas fa-rocket"></i> Brevo SMTP Quick Setup</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>Host:</strong> smtp-relay.brevo.com<br>
                                    <strong>Port:</strong> 587<br>
                                    <strong>Encryption:</strong> TLS
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Username:</strong> Your Brevo login email<br>
                                    <strong>Password:</strong> Your Brevo SMTP Key<br>
                                    <strong>From:</strong> Verified sender in Brevo
                                </small>
                            </div>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="smtp_host" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_host')) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" name="smtp_port" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_port')) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Username (Brevo Login Email)</label>
                                <input type="email" name="smtp_username" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_username')) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Password (Brevo SMTP Key)</label>
                                <input type="password" name="smtp_password" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_password')) ?>" required>
                                <small class="text-muted">Find in Brevo: SMTP & API → SMTP → SMTP Key</small>
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
                                <label class="form-label">From Email (Must be verified in Brevo)</label>
                                <input type="email" name="from_email" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('from_email')) ?>" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">From Name</label>
                                <input type="text" name="from_name" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('from_name')) ?>" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                            <a href="email-test-simple.php" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Test Email
                            </a>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📋 Current Settings:</h6>
                            <small class="text-muted">
                                Host: <?= getSetting('smtp_host', 'Not set') ?><br>
                                Port: <?= getSetting('smtp_port', 'Not set') ?><br>
                                Username: <?= getSetting('smtp_username', 'Not set') ?><br>
                                From: <?= getSetting('from_email', 'Not set') ?>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <h6>🔗 Quick Links:</h6>
                            <a href="system-configuration.php" class="btn btn-sm btn-outline-secondary">Original Config</a>
                            <a href="debug-email-config.php" class="btn btn-sm btn-outline-info">Debug Tool</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>