<?php
/**
 * Debug Email Configuration
 * Quick test to see what's happening with email settings
 */

require_once '../includes/db.php';

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple admin check
if (!isset($_SESSION['admin_id'])) {
    die('⛔ Please login as admin first');
}

echo "<h2>🔍 Email Configuration Debug</h2>";

try {
    // Check if system_settings table exists
    $result = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($result->rowCount() > 0) {
        echo "✅ system_settings table exists<br>";
        
        // Get all email-related settings
        $stmt = $pdo->query("SELECT * FROM system_settings WHERE setting_category = 'email' ORDER BY setting_key");
        $email_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📧 Current Email Settings in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Key</th><th>Value</th><th>Type</th><th>Description</th></tr>";
        
        foreach ($email_settings as $setting) {
            $masked_value = ($setting['setting_key'] === 'smtp_password') ? 
                str_repeat('*', strlen($setting['setting_value'])) : 
                htmlspecialchars($setting['setting_value']);
            
            echo "<tr>";
            echo "<td>{$setting['id']}</td>";
            echo "<td><strong>{$setting['setting_key']}</strong></td>";
            echo "<td>{$masked_value}</td>";
            echo "<td>{$setting['setting_type']}</td>";
            echo "<td>{$setting['setting_description']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for missing settings
        $required_settings = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_auth', 'smtp_encryption', 'from_email', 'from_name'];
        $existing_keys = array_column($email_settings, 'setting_key');
        $missing = array_diff($required_settings, $existing_keys);
        
        if (empty($missing)) {
            echo "<br>✅ All required SMTP settings are present!";
        } else {
            echo "<br>⚠️ Missing settings: " . implode(', ', $missing);
        }
        
    } else {
        echo "❌ system_settings table does not exist<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<br><br><a href='system-configuration.php'>🔙 Back to System Configuration</a>";
?>