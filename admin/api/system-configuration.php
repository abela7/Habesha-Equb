<?php
/**
 * HabeshaEqub - System Configuration API
 * Handle system settings CRUD operations
 */

// Start output buffering to catch any errors
ob_start();

require_once '../../includes/db.php';

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Clean any previous output
if (ob_get_length()) {
    ob_clean();
}

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

// Get current admin info
$current_admin_id = $_SESSION['admin_id'];

// Create system_settings table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_category VARCHAR(50) DEFAULT 'general',
            setting_type VARCHAR(20) DEFAULT 'text',
            setting_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (setting_category),
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {
    error_log("Failed to create system_settings table: " . $e->getMessage());
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'export') {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value, setting_category, setting_type, setting_description FROM system_settings ORDER BY setting_category, setting_key");
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $export_data = [
                'exported_at' => date('c'),
                'exported_by' => $current_admin_id,
                'version' => '1.0',
                'settings' => $settings
            ];
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="habeshaequb_settings_' . date('Y-m-d_H-i-s') . '.json"');
            echo json_encode($export_data, JSON_PRETTY_PRINT);
            exit;
            
        } catch (Exception $e) {
            error_log("Settings export error: " . $e->getMessage());
            json_response(false, 'Failed to export settings');
        }
    }
    
    json_response(false, 'Invalid GET request');
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'save_all':
                $settings_json = $_POST['settings'] ?? '';
                $settings = json_decode($settings_json, true);
                
                if (!$settings) {
                    json_response(false, 'Invalid settings data');
                }
                
                // Begin transaction
                $pdo->beginTransaction();
                
                foreach ($settings as $key => $setting_data) {
                    $value = $setting_data['value'] ?? '';
                    $category = $setting_data['category'] ?? 'general';
                    
                    // Check if setting exists
                    $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
                    $stmt->execute([$key]);
                    
                    if ($stmt->fetch()) {
                        // Update existing setting
                        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, setting_category = ?, updated_at = NOW() WHERE setting_key = ?");
                        $stmt->execute([$value, $category, $key]);
                    } else {
                        // Insert new setting
                        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, created_at) VALUES (?, ?, ?, 'text', NOW())");
                        $stmt->execute([$key, $value, $category]);
                    }
                }
                
                $pdo->commit();
                json_response(true, 'All settings saved successfully');
                break;
                
            case 'add_setting':
                $setting_key = trim($_POST['setting_key'] ?? '');
                $setting_value = trim($_POST['setting_value'] ?? '');
                $setting_category = $_POST['setting_category'] ?? 'general';
                $setting_type = $_POST['setting_type'] ?? 'text';
                $setting_description = trim($_POST['setting_description'] ?? '');
                
                if (empty($setting_key)) {
                    json_response(false, 'Setting key is required');
                }
                
                // Check if setting key already exists
                $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
                $stmt->execute([$setting_key]);
                if ($stmt->fetch()) {
                    json_response(false, 'Setting key already exists');
                }
                
                // Insert new setting
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, setting_description, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$setting_key, $setting_value, $setting_category, $setting_type, $setting_description]);
                
                json_response(true, 'Setting added successfully');
                break;
                
            case 'reset_defaults':
                // Clear all existing settings
                $pdo->exec("DELETE FROM system_settings");
                
                // Insert default settings
                $default_settings = [
                    // General Settings
                    ['app_name', 'HabeshaEqub', 'general', 'text', 'The name of your application shown throughout the system'],
                    ['app_description', 'Ethiopian traditional savings group management system', 'general', 'text', 'Brief description of your equb application'],
                    ['maintenance_mode', '0', 'general', 'boolean', 'Enable to put the system in maintenance mode'],
                    ['session_timeout', '60', 'general', 'select', 'User session timeout in minutes'],
                    
                    // Default Values
                    ['default_contribution', '1000', 'defaults', 'number', 'Default monthly contribution amount for new members'],
                    ['default_currency', 'GBP', 'defaults', 'select', 'Default currency for the system'],
                    ['default_language', 'en', 'defaults', 'select', 'Default language for new users'],
                    ['auto_activate_members', '1', 'defaults', 'boolean', 'Automatically activate new member registrations'],
                    
                    // System Preferences
                    ['date_format', 'm/d/Y', 'preferences', 'select', 'How dates are displayed throughout the system'],
                    ['timezone', 'Africa/Addis_Ababa', 'preferences', 'select', 'System timezone for all date/time operations'],
                    ['items_per_page', '25', 'preferences', 'select', 'Number of items to show per page in lists'],
                    ['enable_notifications', '1', 'preferences', 'boolean', 'Enable system notifications for users'],
                    
                    // Email Configuration
                    ['smtp_host', '', 'email', 'text', 'SMTP server hostname'],
                    ['smtp_port', '587', 'email', 'number', 'SMTP server port (587 for TLS, 465 for SSL)'],
                    ['from_email', '', 'email', 'text', 'Email address used as sender for system emails'],
                    ['from_name', 'HabeshaEqub System', 'email', 'text', 'Name displayed as sender for system emails'],
                    
                    // Currency Settings
                    ['currency_symbol', '£', 'currency', 'text', 'Symbol to display for currency amounts'],
                    ['currency_position', 'before', 'currency', 'select', 'Position of currency symbol relative to amount'],
                    ['decimal_places', '2', 'currency', 'select', 'Number of decimal places to show for currency'],
                    ['thousands_separator', ',', 'currency', 'select', 'Character used to separate thousands']
                ];
                
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, setting_description, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                foreach ($default_settings as $setting) {
                    $stmt->execute($setting);
                }
                
                json_response(true, 'Settings reset to defaults successfully');
                break;
                
            case 'import':
                if (!isset($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
                    json_response(false, 'No valid file uploaded');
                }
                
                $file_content = file_get_contents($_FILES['settings_file']['tmp_name']);
                $import_data = json_decode($file_content, true);
                
                if (!$import_data || !isset($import_data['settings'])) {
                    json_response(false, 'Invalid settings file format');
                }
                
                // Begin transaction
                $pdo->beginTransaction();
                
                // Clear existing settings
                $pdo->exec("DELETE FROM system_settings");
                
                // Import settings
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, setting_description, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                foreach ($import_data['settings'] as $setting) {
                    $stmt->execute([
                        $setting['setting_key'],
                        $setting['setting_value'],
                        $setting['setting_category'] ?? 'general',
                        $setting['setting_type'] ?? 'text',
                        $setting['setting_description'] ?? ''
                    ]);
                }
                
                $pdo->commit();
                json_response(true, 'Settings imported successfully');
                break;
                
            case 'delete_setting':
                $setting_id = (int)($_POST['setting_id'] ?? 0);
                
                if (!$setting_id) {
                    json_response(false, 'Setting ID is required');
                }
                
                $stmt = $pdo->prepare("DELETE FROM system_settings WHERE id = ?");
                $stmt->execute([$setting_id]);
                
                json_response(true, 'Setting deleted successfully');
                break;
                
            default:
                json_response(false, 'Invalid action');
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("System Configuration API error: " . $e->getMessage());
        json_response(false, 'An error occurred while processing your request: ' . $e->getMessage());
    }
}

json_response(false, 'Invalid request method');
?> 