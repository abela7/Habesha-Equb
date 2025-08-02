<?php
/**
 * HabeshaEqub - System Configuration Page
 * Configure general system settings, defaults, and preferences
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get system settings
try {
    // First, create table if it doesn't exist
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
    
    // Check if we have any settings, if not, populate defaults
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM system_settings");
    $settings_count = $count_stmt->fetchColumn();
    
    if ($settings_count == 0) {
        // Insert default settings
        $default_settings = [
            ['app_name', 'HabeshaEqub', 'general', 'text', 'The name of your application shown throughout the system'],
            ['app_description', 'Ethiopian traditional savings group management system', 'general', 'text', 'Brief description of your equb application'],
            ['maintenance_mode', '0', 'general', 'boolean', 'Enable to put the system in maintenance mode'],
            ['session_timeout', '60', 'general', 'select', 'User session timeout in minutes'],
            ['default_contribution', '1000', 'defaults', 'number', 'Default monthly contribution amount for new members'],
            ['default_currency', 'GBP', 'defaults', 'select', 'Default currency for the system'],
            ['default_language', 'en', 'defaults', 'select', 'Default language for new users'],
            ['auto_activate_members', '1', 'defaults', 'boolean', 'Automatically activate new member registrations'],
            ['date_format', 'm/d/Y', 'preferences', 'select', 'How dates are displayed throughout the system'],
            ['timezone', 'Africa/Addis_Ababa', 'preferences', 'select', 'System timezone for all date/time operations'],
            ['items_per_page', '25', 'preferences', 'select', 'Number of items to show per page in lists'],
            ['enable_notifications', '1', 'preferences', 'boolean', 'Enable system notifications for users'],
            ['smtp_host', '', 'email', 'text', 'SMTP server hostname'],
            ['smtp_port', '587', 'email', 'number', 'SMTP server port (587 for TLS, 465 for SSL)'],
            ['from_email', '', 'email', 'text', 'Email address used as sender for system emails'],
            ['from_name', 'HabeshaEqub System', 'email', 'text', 'Name displayed as sender for system emails'],
            ['smtp_auth', '1', 'email', 'boolean', 'Enable SMTP authentication'],
            ['smtp_username', '', 'email', 'text', 'SMTP authentication username'],
            ['smtp_password', '', 'email', 'password', 'SMTP authentication password'],
            ['smtp_encryption', 'tls', 'email', 'select', 'SMTP encryption method (tls, ssl, none)'],
            ['currency_symbol', '£', 'currency', 'text', 'Symbol to display for currency amounts'],
            ['currency_position', 'before', 'currency', 'select', 'Position of currency symbol relative to amount'],
            ['decimal_places', '2', 'currency', 'select', 'Number of decimal places to show for currency'],
            ['thousands_separator', ',', 'currency', 'select', 'Character used to separate thousands']
        ];
        
        $insert_stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_category, setting_type, setting_description, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        foreach ($default_settings as $setting) {
            $insert_stmt->execute($setting);
        }
    }
    
    $stmt = $pdo->query("
        SELECT setting_key, setting_value, setting_category, setting_type, setting_description 
        FROM system_settings 
        ORDER BY setting_category, setting_key
    ");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching system settings: " . $e->getMessage());
    $settings = [];
}

// Group settings by category and create lookup array
$grouped_settings = [];
$settings_values = [];
foreach ($settings as $setting) {
    $grouped_settings[$setting['setting_category']][] = $setting;
    $settings_values[$setting['setting_key']] = $setting['setting_value'];
}

// Helper function to get setting value
function getSetting($key, $default = '') {
    global $settings_values;
    return isset($settings_values[$key]) ? $settings_values[$key] : $default;
}

// Helper function to check if setting is checked
function isSettingChecked($key) {
    global $settings_values;
    return isset($settings_values[$key]) && $settings_values[$key] == '1';
}

// Helper function to get selected option
function isSettingSelected($key, $value) {
    global $settings_values;
    return isset($settings_values[$key]) && $settings_values[$key] == $value;
}

// Default categories if no settings exist
$default_categories = [
    'general' => 'General Settings',
    'defaults' => 'Default Values', 
    'preferences' => 'System Preferences',
    'email' => 'Email Configuration',
    'currency' => 'Currency Settings'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration - HabeshaEqub Admin</title>
    
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
        /* === SYSTEM CONFIGURATION PAGE DESIGN === */
        
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
            background: linear-gradient(135deg, var(--color-light-gold) 0%, #B8941C 100%);
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

        .btn-save-config {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            border: none;
            box-shadow: 
                0 4px 15px rgba(16, 185, 129, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-save-config::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-save-config:hover::before {
            left: 100%;
        }

        .btn-save-config:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 8px 30px rgba(16, 185, 129, 0.4),
                0 4px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            color: white;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .btn-save-config:active {
            transform: translateY(-1px);
            box-shadow: 
                0 4px 15px rgba(16, 185, 129, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-reset-config {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            margin-left: 16px;
            border: none;
            box-shadow: 
                0 4px 15px rgba(239, 68, 68, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-reset-config::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-reset-config:hover::before {
            left: 100%;
        }

        .btn-reset-config:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 8px 30px rgba(239, 68, 68, 0.4),
                0 4px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            color: white;
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
        }

        .btn-reset-config:active {
            transform: translateY(-1px);
            box-shadow: 
                0 4px 15px rgba(239, 68, 68, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Configuration Categories */
        .config-categories {
            margin-bottom: 40px;
        }

        .category-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .category-tab {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 12px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            white-space: nowrap;
            font-weight: 500;
            cursor: pointer;
        }

        .category-tab:hover {
            background: var(--color-cream);
            color: var(--color-teal);
            text-decoration: none;
            transform: translateY(-2px);
        }

        .category-tab.active {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            color: white;
            border-color: var(--color-teal);
        }

        /* Configuration Sections */
        .config-section {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            overflow: hidden;
            margin-bottom: 24px;
            display: none;
        }

        .config-section.active {
            display: block;
        }

        .section-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--color-purple);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-description {
            color: var(--text-secondary);
            margin: 8px 0 0 0;
            font-size: 14px;
        }

        .section-content {
            padding: 24px;
        }

        /* Setting Items */
        .setting-item {
            border-bottom: 1px solid var(--border-light);
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-info {
            flex: 1;
            padding-right: 20px;
        }

        .setting-label {
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 4px;
            font-size: 16px;
        }

        .setting-description {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.4;
        }

        .setting-control {
            min-width: 250px;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-teal);
            box-shadow: 0 0 0 3px rgba(19, 102, 92, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
        }

        .form-check-input:checked {
            background-color: var(--color-teal);
            border-color: var(--color-teal);
        }

        /* Status Indicators */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-indicator.saved {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            color: #065F46;
        }

        .status-indicator.changed {
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
            color: #92400E;
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--color-cream);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quick-actions-text {
            color: var(--color-purple);
            font-weight: 600;
        }

        .quick-actions-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-quick {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-export {
            background: var(--color-gold);
            color: white;
        }

        .btn-import {
            background: var(--color-teal);
            color: white;
        }

        .btn-quick:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Add Setting Modal */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-bottom: 1px solid var(--border-light);
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }

        .modal-title {
            color: var(--color-purple);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 8px;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 24px;
            }

            .page-title-section h1 {
                font-size: 24px;
                justify-content: center;
            }

            .setting-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .setting-control {
                min-width: 100%;
                width: 100%;
            }

            .category-tabs {
                flex-wrap: wrap;
            }

            .quick-actions {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    <div class="page-title-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    System Configuration
                </h1>
                <p class="page-subtitle">Configure general system settings, defaults, and preferences</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-save-config" onclick="saveAllSettings()">
                    <i class="fas fa-save"></i>
                    Save All Changes
                </button>
                <button class="btn btn-reset-config" onclick="resetToDefaults()">
                    <i class="fas fa-undo"></i>
                    Reset to Defaults
                </button>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-actions-text">
                <i class="fas fa-bolt me-2"></i>
                Quick Actions
            </div>
            <div class="quick-actions-buttons">
                <button class="btn-quick btn-export" onclick="exportSettings()">
                    <i class="fas fa-download me-2"></i>
                    Export Settings
                </button>
                <button class="btn-quick btn-import" onclick="importSettings()">
                    <i class="fas fa-upload me-2"></i>
                    Import Settings
                </button>
                <button class="btn-quick" style="background: var(--color-gold); color: white;" onclick="addNewSetting()">
                    <i class="fas fa-plus me-2"></i>
                    Add Setting
                </button>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="category-tab active" onclick="showCategory('general')">
                <i class="fas fa-cog me-2"></i>
                General Settings
            </button>
            <button class="category-tab" onclick="showCategory('defaults')">
                <i class="fas fa-sliders-h me-2"></i>
                Default Values
            </button>
            <button class="category-tab" onclick="showCategory('preferences')">
                <i class="fas fa-user-cog me-2"></i>
                Preferences
            </button>
            <button class="category-tab" onclick="showCategory('email')">
                <i class="fas fa-envelope me-2"></i>
                Email Config
            </button>
            <button class="category-tab" onclick="showCategory('currency')">
                <i class="fas fa-dollar-sign me-2"></i>
                Currency
            </button>
        </div>

        <!-- Configuration Sections -->
        
        <!-- General Settings -->
        <div class="config-section active" id="general-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-cog"></i>
                    General Settings
                </h3>
                <p class="section-description">Basic system configuration and application settings</p>
            </div>
            <div class="section-content">
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Application Name</div>
                        <div class="setting-description">The name of your application shown throughout the system</div>
                    </div>
                    <div class="setting-control">
                        <input type="text" class="form-control" name="app_name" value="<?php echo htmlspecialchars(getSetting('app_name', 'HabeshaEqub')); ?>" data-category="general">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Application Description</div>
                        <div class="setting-description">Brief description of your equb application</div>
                    </div>
                    <div class="setting-control">
                        <textarea class="form-control" name="app_description" rows="3" data-category="general"><?php echo htmlspecialchars(getSetting('app_description', 'Ethiopian traditional savings group management system')); ?></textarea>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Maintenance Mode</div>
                        <div class="setting-description">Enable to put the system in maintenance mode</div>
                    </div>
                    <div class="setting-control">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" data-category="general" <?php echo isSettingChecked('maintenance_mode') ? 'checked' : ''; ?>>
                            <label class="form-check-label">Enable Maintenance Mode</label>
                        </div>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Session Timeout</div>
                        <div class="setting-description">User session timeout in minutes</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="session_timeout" data-category="general">
                            <option value="30" <?php echo isSettingSelected('session_timeout', '30') ? 'selected' : ''; ?>>30 minutes</option>
                            <option value="60" <?php echo isSettingSelected('session_timeout', '60') || getSetting('session_timeout') == '' ? 'selected' : ''; ?>>1 hour</option>
                            <option value="120" <?php echo isSettingSelected('session_timeout', '120') ? 'selected' : ''; ?>>2 hours</option>
                            <option value="480" <?php echo isSettingSelected('session_timeout', '480') ? 'selected' : ''; ?>>8 hours</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Default Values -->
        <div class="config-section" id="defaults-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-sliders-h"></i>
                    Default Values
                </h3>
                <p class="section-description">Default values for new members and system operations</p>
            </div>
            <div class="section-content">
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Default Contribution Amount</div>
                        <div class="setting-description">Default monthly contribution amount for new members</div>
                    </div>
                    <div class="setting-control">
                        <input type="number" class="form-control" name="default_contribution" value="<?php echo htmlspecialchars(getSetting('default_contribution', '1000')); ?>" min="0" step="100" data-category="defaults">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Default Currency</div>
                        <div class="setting-description">Default currency for the system</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="default_currency" data-category="defaults">
                            <option value="GBP" <?php echo isSettingSelected('default_currency', 'GBP') || getSetting('default_currency') == '' ? 'selected' : ''; ?>>British Pound (£)</option>
                            <option value="ETB" <?php echo isSettingSelected('default_currency', 'ETB') ? 'selected' : ''; ?>>Ethiopian Birr (ETB)</option>
                            <option value="USD" <?php echo isSettingSelected('default_currency', 'USD') ? 'selected' : ''; ?>>US Dollar (USD)</option>
                            <option value="EUR" <?php echo isSettingSelected('default_currency', 'EUR') ? 'selected' : ''; ?>>Euro (EUR)</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Default Language</div>
                        <div class="setting-description">Default language for new users</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="default_language" data-category="defaults">
                            <option value="en" <?php echo isSettingSelected('default_language', 'en') || getSetting('default_language') == '' ? 'selected' : ''; ?>>English</option>
                            <option value="am" <?php echo isSettingSelected('default_language', 'am') ? 'selected' : ''; ?>>አማርኛ (Amharic)</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Auto-activate Members</div>
                        <div class="setting-description">Automatically activate new member registrations</div>
                    </div>
                    <div class="setting-control">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_activate_members" data-category="defaults" <?php echo isSettingChecked('auto_activate_members') || getSetting('auto_activate_members') == '' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Auto-activate new members</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Preferences -->
        <div class="config-section" id="preferences-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-user-cog"></i>
                    System Preferences
                </h3>
                <p class="section-description">System behavior and user experience preferences</p>
            </div>
            <div class="section-content">
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Date Format</div>
                        <div class="setting-description">How dates are displayed throughout the system</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="date_format" data-category="preferences">
                            <option value="Y-m-d" <?php echo isSettingSelected('date_format', 'Y-m-d') ? 'selected' : ''; ?>>2024-01-15 (YYYY-MM-DD)</option>
                            <option value="m/d/Y" <?php echo isSettingSelected('date_format', 'm/d/Y') || getSetting('date_format') == '' ? 'selected' : ''; ?>>01/15/2024 (MM/DD/YYYY)</option>
                            <option value="d/m/Y" <?php echo isSettingSelected('date_format', 'd/m/Y') ? 'selected' : ''; ?>>15/01/2024 (DD/MM/YYYY)</option>
                            <option value="M j, Y" <?php echo isSettingSelected('date_format', 'M j, Y') ? 'selected' : ''; ?>>Jan 15, 2024</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Time Zone</div>
                        <div class="setting-description">System timezone for all date/time operations</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="timezone" data-category="preferences">
                            <option value="Africa/Addis_Ababa" <?php echo isSettingSelected('timezone', 'Africa/Addis_Ababa') || getSetting('timezone') == '' ? 'selected' : ''; ?>>Africa/Addis Ababa (UTC+3)</option>
                            <option value="UTC" <?php echo isSettingSelected('timezone', 'UTC') ? 'selected' : ''; ?>>UTC (UTC+0)</option>
                            <option value="America/New_York" <?php echo isSettingSelected('timezone', 'America/New_York') ? 'selected' : ''; ?>>America/New York (UTC-5)</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Items Per Page</div>
                        <div class="setting-description">Number of items to show per page in lists</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="items_per_page" data-category="preferences">
                            <option value="10" <?php echo isSettingSelected('items_per_page', '10') ? 'selected' : ''; ?>>10 items</option>
                            <option value="25" <?php echo isSettingSelected('items_per_page', '25') || getSetting('items_per_page') == '' ? 'selected' : ''; ?>>25 items</option>
                            <option value="50" <?php echo isSettingSelected('items_per_page', '50') ? 'selected' : ''; ?>>50 items</option>
                            <option value="100" <?php echo isSettingSelected('items_per_page', '100') ? 'selected' : ''; ?>>100 items</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Enable Notifications</div>
                        <div class="setting-description">Enable system notifications for users</div>
                    </div>
                    <div class="setting-control">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_notifications" data-category="preferences" <?php echo isSettingChecked('enable_notifications') || getSetting('enable_notifications') == '' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Enable notifications</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Configuration -->
        <div class="config-section" id="email-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i>
                    Email Configuration
                </h3>
                <p class="section-description">Email server settings and notification preferences</p>
                <!-- Brevo Quick Setup Guide -->
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-rocket"></i>
                                Brevo SMTP Setup (Recommended)
                            </h6>
                            <p class="mb-2"><strong>Quick Configuration for Brevo:</strong></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>SMTP Host:</strong> <code>smtp-relay.brevo.com</code></li>
                                        <li><strong>SMTP Port:</strong> <code>587</code></li>
                                        <li><strong>Encryption:</strong> <code>TLS</code></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Username:</strong> Your Brevo login email</li>
                                        <li><strong>Password:</strong> Your Brevo SMTP Key</li>
                                        <li><strong>From Email:</strong> Verified sender in Brevo</li>
                                    </ul>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-external-link-alt"></i>
                                Get your SMTP Key: Brevo Dashboard → SMTP & API → SMTP → SMTP Key
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-content">
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Host</div>
                        <div class="setting-description">SMTP server hostname</div>
                    </div>
                    <div class="setting-control">
                        <input type="text" class="form-control" name="smtp_host" value="<?php echo htmlspecialchars(getSetting('smtp_host')); ?>" placeholder="smtp-relay.brevo.com" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Port</div>
                        <div class="setting-description">SMTP server port (587 for TLS, 465 for SSL)</div>
                    </div>
                    <div class="setting-control">
                        <input type="number" class="form-control" name="smtp_port" value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">From Email</div>
                        <div class="setting-description">Email address used as sender for system emails</div>
                    </div>
                    <div class="setting-control">
                        <input type="email" class="form-control" name="from_email" value="<?php echo htmlspecialchars(getSetting('from_email')); ?>" placeholder="noreply@habeshaequb.com" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">From Name</div>
                        <div class="setting-description">Name displayed as sender for system emails</div>
                    </div>
                    <div class="setting-control">
                        <input type="text" class="form-control" name="from_name" value="<?php echo htmlspecialchars(getSetting('from_name', 'HabeshaEqub System')); ?>" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Authentication</div>
                        <div class="setting-description">Enable SMTP authentication (recommended)</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="smtp_auth" data-category="email">
                            <option value="1" <?php echo getSetting('smtp_auth', '1') === '1' ? 'selected' : ''; ?>>Enabled (Recommended)</option>
                            <option value="0" <?php echo getSetting('smtp_auth', '1') === '0' ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Username</div>
                        <div class="setting-description">SMTP authentication username (usually your email)</div>
                    </div>
                    <div class="setting-control">
                        <input type="email" class="form-control" name="smtp_username" value="<?php echo htmlspecialchars(getSetting('smtp_username')); ?>" placeholder="your-brevo-login-email@domain.com" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Password</div>
                        <div class="setting-description">SMTP authentication password (use app-specific password for Gmail)</div>
                    </div>
                    <div class="setting-control">
                        <input type="password" class="form-control" name="smtp_password" value="<?php echo htmlspecialchars(getSetting('smtp_password')); ?>" placeholder="Enter your Brevo SMTP Key" data-category="email">
                        <small class="form-text text-muted">
                            <i class="fas fa-key text-primary"></i>
                            <strong>Use your Brevo SMTP Key</strong> (not your account password) - Find it in Brevo Dashboard → SMTP & API → SMTP
                        </small>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Encryption</div>
                        <div class="setting-description">SMTP encryption method (TLS recommended for port 587)</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="smtp_encryption" data-category="email">
                            <option value="tls" <?php echo getSetting('smtp_encryption', 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (Port 587 - Recommended)</option>
                            <option value="ssl" <?php echo getSetting('smtp_encryption', 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL (Port 465)</option>
                            <option value="none" <?php echo getSetting('smtp_encryption', 'tls') === 'none' ? 'selected' : ''; ?>>None (Not recommended)</option>
                        </select>
                    </div>
                </div>

                <!-- Email Test Section -->
                <div class="setting-item border-top pt-3 mt-3">
                    <div class="setting-info">
                        <div class="setting-label">
                            <i class="fas fa-paper-plane text-success"></i>
                            Test Email Configuration
                        </div>
                        <div class="setting-description">Send a test email to verify your SMTP settings</div>
                    </div>
                    <div class="setting-control">
                        <div class="input-group">
                            <input type="email" class="form-control" id="test-email" placeholder="Enter test email address" value="<?php echo htmlspecialchars(getSetting('from_email')); ?>">
                            <button class="btn btn-outline-success" type="button" id="send-test-email">
                                <i class="fas fa-paper-plane"></i>
                                Send Test Email
                            </button>
                        </div>
                        <div id="test-email-result" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency Settings -->
        <div class="config-section" id="currency-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-dollar-sign"></i>
                    Currency Settings
                </h3>
                <p class="section-description">Currency display and formatting preferences</p>
            </div>
            <div class="section-content">
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Currency Symbol</div>
                        <div class="setting-description">Symbol to display for currency amounts</div>
                    </div>
                    <div class="setting-control">
                        <input type="text" class="form-control" name="currency_symbol" value="<?php echo htmlspecialchars(getSetting('currency_symbol', '£')); ?>" data-category="currency">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Currency Position</div>
                        <div class="setting-description">Position of currency symbol relative to amount</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="currency_position" data-category="currency">
                            <option value="before" <?php echo isSettingSelected('currency_position', 'before') || getSetting('currency_position') == '' ? 'selected' : ''; ?>>Before amount (£1,000)</option>
                            <option value="after" <?php echo isSettingSelected('currency_position', 'after') ? 'selected' : ''; ?>>After amount (1,000 £)</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Decimal Places</div>
                        <div class="setting-description">Number of decimal places to show for currency</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="decimal_places" data-category="currency">
                            <option value="0" <?php echo isSettingSelected('decimal_places', '0') ? 'selected' : ''; ?>>0 (1000)</option>
                            <option value="2" <?php echo isSettingSelected('decimal_places', '2') || getSetting('decimal_places') == '' ? 'selected' : ''; ?>>2 (1000.00)</option>
                        </select>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Thousands Separator</div>
                        <div class="setting-description">Character used to separate thousands</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="thousands_separator" data-category="currency">
                            <option value="," <?php echo isSettingSelected('thousands_separator', ',') || getSetting('thousands_separator') == '' ? 'selected' : ''; ?>>Comma (1,000)</option>
                            <option value="." <?php echo isSettingSelected('thousands_separator', '.') ? 'selected' : ''; ?>>Period (1.000)</option>
                            <option value=" " <?php echo isSettingSelected('thousands_separator', ' ') ? 'selected' : ''; ?>>Space (1 000)</option>
                            <option value="" <?php echo getSetting('thousands_separator') === '' && isset($settings_values['thousands_separator']) ? 'selected' : ''; ?>>None (1000)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Information (for testing) -->
        <div class="config-section" style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 12px; border: 1px solid #dee2e6;">
            <h4 style="color: var(--color-purple); margin-bottom: 16px;">
                <i class="fas fa-bug me-2"></i>
                Debug Information
            </h4>
            <div style="font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; background: white; padding: 12px; border-radius: 8px;">
                <strong>Settings Count:</strong> <?php echo count($settings); ?><br>
                <strong>Settings Loaded:</strong><br>
                <?php foreach ($settings_values as $key => $value): ?>
                    <?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?><br>
                <?php endforeach; ?>
                
                <?php if (empty($settings_values)): ?>
                    <span style="color: red;">No settings found in database. Check if table exists and is populated.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Setting Modal -->
    <div class="modal fade" id="addSettingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i>
                        Add New Setting
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSettingForm">
                        <div class="form-group">
                            <label class="form-label">Setting Key *</label>
                            <input type="text" class="form-control" name="setting_key" required>
                            <small class="text-muted">Unique identifier (e.g., max_upload_size)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Setting Value *</label>
                            <input type="text" class="form-control" name="setting_value" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="setting_category">
                                <option value="general">General Settings</option>
                                <option value="defaults">Default Values</option>
                                <option value="preferences">System Preferences</option>
                                <option value="email">Email Configuration</option>
                                <option value="currency">Currency Settings</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Setting Type</label>
                            <select class="form-select" name="setting_type">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                                <option value="select">Select</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="setting_description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSetting()">
                        <i class="fas fa-save me-2"></i>
                        Add Setting
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let addSettingModal;
        let hasChanges = false;

        document.addEventListener('DOMContentLoaded', function() {
            addSettingModal = new bootstrap.Modal(document.getElementById('addSettingModal'));
            
            // Track changes
            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('change', function() {
                    hasChanges = true;
                    updateChangeIndicators();
                });
            });
            
            // Add test email button event listener
            const testEmailBtn = document.getElementById('send-test-email');
            if (testEmailBtn) {
                testEmailBtn.addEventListener('click', sendTestEmail);
            }
        });

        function showCategory(category) {
            // Update tabs
            document.querySelectorAll('.category-tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            // Update sections
            document.querySelectorAll('.config-section').forEach(section => section.classList.remove('active'));
            document.getElementById(category + '-section').classList.add('active');
        }

        function updateChangeIndicators() {
            const saveBtn = document.querySelector('.btn-save-config');
            if (hasChanges) {
                saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Save Changes';
                saveBtn.style.background = 'linear-gradient(135deg, var(--color-coral), #D63447)';
            } else {
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save All Changes';
                saveBtn.style.background = 'linear-gradient(135deg, var(--color-teal), #0F5147)';
            }
        }

        function saveAllSettings() {
            const formData = new FormData();
            const settings = {};
            
            console.log('Starting save process...');
            
            document.querySelectorAll('[data-category]').forEach(element => {
                const name = element.name;
                const category = element.dataset.category;
                let value;
                
                if (element.type === 'checkbox') {
                    value = element.checked ? '1' : '0';
                    console.log(`Checkbox ${name}: ${element.checked} -> ${value}`);
                } else {
                    value = element.value;
                    console.log(`Field ${name}: ${value}`);
                }
                
                settings[name] = {
                    value: value,
                    category: category
                };
            });
            
            console.log('Settings to save:', settings);
            
            formData.append('action', 'save_all');
            formData.append('settings', JSON.stringify(settings));
            
            const saveBtn = document.querySelector('.btn-save-config');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

            fetch('api/system-configuration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    
                    if (data.success) {
                        hasChanges = false;
                        updateChangeIndicators();
                        showNotification('Settings saved successfully!', 'success');
                        console.log('Settings saved successfully!');
                    } else {
                        console.error('Save failed:', data.message);
                        showNotification('Error: ' + data.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Raw response was:', text);
                    showNotification('Invalid response from server', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('Failed to save settings', 'error');
            })
            .finally(() => {
                saveBtn.disabled = false;
                updateChangeIndicators();
            });
        }

        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                fetch('api/system-configuration.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=reset_defaults'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to reset settings', 'error');
                });
            }
        }

        function exportSettings() {
            window.open('api/system-configuration.php?action=export', '_blank');
        }

        function importSettings() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('action', 'import');
                    formData.append('settings_file', file);
                    
                    fetch('api/system-configuration.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            showNotification('Error: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Failed to import settings', 'error');
                    });
                }
            };
            input.click();
        }

        function addNewSetting() {
            document.getElementById('addSettingForm').reset();
            addSettingModal.show();
        }

        function saveSetting() {
            const form = document.getElementById('addSettingForm');
            const formData = new FormData(form);
            formData.append('action', 'add_setting');

            fetch('api/system-configuration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addSettingModal.hide();
                    showNotification('Setting added successfully!', 'success');
                    location.reload();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to add setting', 'error');
            });
        }

        function sendTestEmail() {
            const testEmailInput = document.getElementById('test-email');
            const testButton = document.getElementById('send-test-email');
            const resultDiv = document.getElementById('test-email-result');
            
            const testEmail = testEmailInput.value.trim();
            if (!testEmail) {
                showNotification('Please enter a test email address', 'error');
                return;
            }
            
            // Disable button and show loading
            testButton.disabled = true;
            testButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Testing email configuration...</div>';
            
            // Get current SMTP settings from form
            const formData = new FormData();
            formData.append('action', 'test_email');
            formData.append('test_email', testEmail);
            
            // Add current form values
            const inputs = document.querySelectorAll('[data-category="email"]');
            inputs.forEach(input => {
                formData.append(input.name, input.value);
            });
            
            fetch('api/test-email.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Test email sent successfully!</strong><br>
                            <small class="text-muted">Check ${testEmail} for the test message.</small>
                        </div>
                    `;
                    showNotification('Test email sent successfully!', 'success');
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Test email failed:</strong><br>
                            <small>${data.message}</small>
                        </div>
                    `;
                    showNotification('Test email failed: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error sending test email</strong><br>
                        <small>Please check your settings and try again.</small>
                    </div>
                `;
                showNotification('Error sending test email', 'error');
            })
            .finally(() => {
                // Re-enable button
                testButton.disabled = false;
                testButton.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test Email';
            });
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Warn about unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html> 