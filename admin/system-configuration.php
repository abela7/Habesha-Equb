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

// Group settings by category
$grouped_settings = [];
foreach ($settings as $setting) {
    $grouped_settings[$setting['setting_category']][] = $setting;
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
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(48, 25, 67, 0.15);
        }

        .btn-save-config {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            color: white;
            font-size: 16px;
        }

        .btn-save-config:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(19, 102, 92, 0.3);
            color: white;
        }

        .btn-reset-config {
            background: linear-gradient(135deg, var(--color-coral) 0%, #D63447 100%);
            color: white;
            font-size: 16px;
            margin-left: 12px;
        }

        .btn-reset-config:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 52, 71, 0.3);
            color: white;
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
                        <input type="text" class="form-control" name="app_name" value="HabeshaEqub" data-category="general">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Application Description</div>
                        <div class="setting-description">Brief description of your equb application</div>
                    </div>
                    <div class="setting-control">
                        <textarea class="form-control" name="app_description" rows="3" data-category="general">Ethiopian traditional savings group management system</textarea>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Maintenance Mode</div>
                        <div class="setting-description">Enable to put the system in maintenance mode</div>
                    </div>
                    <div class="setting-control">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" data-category="general">
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
                            <option value="30">30 minutes</option>
                            <option value="60" selected>1 hour</option>
                            <option value="120">2 hours</option>
                            <option value="480">8 hours</option>
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
                        <input type="number" class="form-control" name="default_contribution" value="1000" min="0" step="100" data-category="defaults">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Default Currency</div>
                        <div class="setting-description">Default currency for the system</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="default_currency" data-category="defaults">
                            <option value="ETB" selected>Ethiopian Birr (ETB)</option>
                            <option value="USD">US Dollar (USD)</option>
                            <option value="EUR">Euro (EUR)</option>
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
                            <option value="en" selected>English</option>
                            <option value="am">አማርኛ (Amharic)</option>
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
                            <input class="form-check-input" type="checkbox" name="auto_activate_members" checked data-category="defaults">
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
                            <option value="Y-m-d">2024-01-15 (YYYY-MM-DD)</option>
                            <option value="m/d/Y" selected>01/15/2024 (MM/DD/YYYY)</option>
                            <option value="d/m/Y">15/01/2024 (DD/MM/YYYY)</option>
                            <option value="M j, Y">Jan 15, 2024</option>
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
                            <option value="Africa/Addis_Ababa" selected>Africa/Addis Ababa (UTC+3)</option>
                            <option value="UTC">UTC (UTC+0)</option>
                            <option value="America/New_York">America/New York (UTC-5)</option>
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
                            <option value="10">10 items</option>
                            <option value="25" selected>25 items</option>
                            <option value="50">50 items</option>
                            <option value="100">100 items</option>
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
                            <input class="form-check-input" type="checkbox" name="enable_notifications" checked data-category="preferences">
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
            </div>
            <div class="section-content">
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Host</div>
                        <div class="setting-description">SMTP server hostname</div>
                    </div>
                    <div class="setting-control">
                        <input type="text" class="form-control" name="smtp_host" placeholder="smtp.gmail.com" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">SMTP Port</div>
                        <div class="setting-description">SMTP server port (587 for TLS, 465 for SSL)</div>
                    </div>
                    <div class="setting-control">
                        <input type="number" class="form-control" name="smtp_port" value="587" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">From Email</div>
                        <div class="setting-description">Email address used as sender for system emails</div>
                    </div>
                    <div class="setting-control">
                        <input type="email" class="form-control" name="from_email" placeholder="noreply@habeshaequb.com" data-category="email">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">From Name</div>
                        <div class="setting-description">Name displayed as sender for system emails</div>
                    </div>
                    <div class="setting-control">
                        <input type="text" class="form-control" name="from_name" value="HabeshaEqub System" data-category="email">
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
                        <input type="text" class="form-control" name="currency_symbol" value="ETB" data-category="currency">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-label">Currency Position</div>
                        <div class="setting-description">Position of currency symbol relative to amount</div>
                    </div>
                    <div class="setting-control">
                        <select class="form-select" name="currency_position" data-category="currency">
                            <option value="before">Before amount (ETB 1,000)</option>
                            <option value="after" selected>After amount (1,000 ETB)</option>
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
                            <option value="0">0 (1000)</option>
                            <option value="2" selected>2 (1000.00)</option>
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
                            <option value="," selected>Comma (1,000)</option>
                            <option value=".">Period (1.000)</option>
                            <option value=" ">Space (1 000)</option>
                            <option value="">None (1000)</option>
                        </select>
                    </div>
                </div>
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
            
            document.querySelectorAll('[data-category]').forEach(element => {
                const name = element.name;
                const category = element.dataset.category;
                let value;
                
                if (element.type === 'checkbox') {
                    value = element.checked ? '1' : '0';
                } else {
                    value = element.value;
                }
                
                settings[name] = {
                    value: value,
                    category: category
                };
            });
            
            formData.append('action', 'save_all');
            formData.append('settings', JSON.stringify(settings));
            
            const saveBtn = document.querySelector('.btn-save-config');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

            fetch('api/system-configuration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hasChanges = false;
                    updateChangeIndicators();
                    showNotification('Settings saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
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