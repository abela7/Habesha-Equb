<?php
/**
 * HabeshaEqub Admin Settings Page
 * System configuration and management center
 */

require_once 'includes/admin_auth_guard.php';

// Get current admin info
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Include language handler
require_once '../languages/translator.php';
$t = Translator::getInstance();
?>
<!DOCTYPE html>
<html lang="<?php echo $t->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - HabeshaEqub Admin</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Meta tags -->
    <meta name="description" content="HabeshaEqub Admin System Settings">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1 style="color: var(--color-deep-purple); margin-bottom: 8px;">System Settings</h1>
            <p style="color: var(--color-dark-purple); margin: 0; font-size: 1.1rem;">Manage system configuration, translations, and administrative settings</p>
        </div>

        <!-- Settings Categories -->
        <div class="settings-grid">
            
            <!-- Translation Management -->
            <div class="settings-category active-module">
                <div class="module-header">
                    <div class="module-icon primary">
                        <i class="fas fa-language" style="font-size: 24px;"></i>
                    </div>
                    <div class="module-status">
                        <span class="status-badge status-active">Active</span>
                    </div>
                </div>
                <h3>Translation Management</h3>
                <p>Manage English and Amharic translations for the entire system</p>
                <div class="category-features">
                    <span class="feature-tag">Live Editor</span>
                    <span class="feature-tag">JSON Management</span>
                    <span class="feature-tag">Bi-lingual</span>
                </div>
                <a href="translation.php" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    Manage Translations
                </a>
            </div>

            <!-- Equb Management -->
            <div class="settings-category active-module">
                <div class="module-header">
                    <div class="module-icon secondary">
                        <i class="fas fa-chart-pie" style="font-size: 24px;"></i>
                    </div>
                    <div class="module-status">
                        <span class="status-badge status-active">Active</span>
                    </div>
                </div>
                <h3>Equb Management</h3>
                <p>Manage and monitor equb transactions, reports, and financial data</p>
                <div class="category-features">
                    <span class="feature-tag">Transaction History</span>
                    <span class="feature-tag">Reports</span>
                    <span class="feature-tag">Financial Analytics</span>
                </div>
                <a href="equb-management.php" class="btn btn-primary">
                    <i class="fas fa-cogs me-2"></i>
                    Manage Equbs
                </a>
            </div>

            <!-- System Configuration -->
            <div class="settings-category active-module">
                <div class="module-header">
                    <div class="module-icon accent">
                        <i class="fas fa-cog" style="font-size: 24px;"></i>
                    </div>
                    <div class="module-status">
                        <span class="status-badge status-active">Active</span>
                    </div>
                </div>
                <h3>System Configuration</h3>
                <p>Configure general system settings, defaults, and preferences</p>
                <div class="category-features">
                    <span class="feature-tag">General Settings</span>
                    <span class="feature-tag">Defaults</span>
                    <span class="feature-tag">Preferences</span>
                </div>
                <a href="system-configuration.php" class="btn btn-primary">
                    <i class="fas fa-wrench me-2"></i>
                    Configure System
                </a>
            </div>

            <!-- Admin Management -->
            <div class="settings-category active-module">
                <div class="module-header">
                    <div class="module-icon warning">
                        <i class="fas fa-users-cog" style="font-size: 24px;"></i>
                    </div>
                    <div class="module-status">
                        <span class="status-badge status-active">Active</span>
                    </div>
                </div>
                <h3>Admin Management</h3>
                <p>Manage admin accounts, permissions, and access controls</p>
                <div class="category-features">
                    <span class="feature-tag">Admin Accounts</span>
                    <span class="feature-tag">Permissions</span>
                    <span class="feature-tag">Security</span>
                </div>
                <a href="admin-management.php" class="btn btn-primary">
                    <i class="fas fa-user-shield me-2"></i>
                    Manage Admins
                </a>
            </div>

            <!-- Backup & Maintenance -->
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                </div>
                <h3>Backup & Maintenance</h3>
                <p>System backups, maintenance tools, and data management</p>
                <div class="category-features">
                    <span class="feature-tag">Database Backup</span>
                    <span class="feature-tag">File Backup</span>
                    <span class="feature-tag">Maintenance</span>
                </div>
                <a href="#" class="btn btn-secondary" onclick="showComingSoon('Backup & Maintenance')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
                    </svg>
                    Backup & Maintain
                </a>
            </div>

            <!-- Email & Notifications -->
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
                <h3>Email & Notifications</h3>
                <p>Configure email settings, notification templates, and communication</p>
                <div class="category-features">
                    <span class="feature-tag">Email Config</span>
                    <span class="feature-tag">Templates</span>
                    <span class="feature-tag">Notifications</span>
                </div>
                <a href="#" class="btn btn-secondary" onclick="showComingSoon('Email & Notifications')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Configure Email
                </a>
            </div>

            <!-- Security Settings -->
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <circle cx="12" cy="16" r="1"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h3>Security Settings</h3>
                <p>Manage security policies, password requirements, and access controls</p>
                <div class="category-features">
                    <span class="feature-tag">Password Policy</span>
                    <span class="feature-tag">Access Control</span>
                    <span class="feature-tag">Session Security</span>
                </div>
                <a href="#" class="btn btn-secondary" onclick="showComingSoon('Security Settings')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Security Config
                </a>
            </div>

        </div>

        <!-- System Information -->
        <div class="system-info-section">
            <h2>System Information</h2>
            <div class="info-grid">
                <div class="info-card">
                    <h4>Version</h4>
                    <p>HabeshaEqub v1.0.0</p>
                </div>
                <div class="info-card">
                    <h4>PHP Version</h4>
                    <p><?php echo PHP_VERSION; ?></p>
                </div>
                <div class="info-card">
                    <h4>Database</h4>
                    <p>MySQL Connected</p>
                </div>
                <div class="info-card">
                    <h4>Current Language</h4>
                    <p><?php echo ucfirst($t->getCurrentLanguage()); ?></p>
                </div>
                <div class="info-card">
                    <h4>Last Update</h4>
                    <p><?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                <div class="info-card">
                    <h4>Server Time</h4>
                    <p><?php echo date('H:i:s T'); ?></p>
                </div>
            </div>
        </div>

        <!-- DANGEROUS ZONE - System Reset -->
        <div class="danger-zone-section">
            <h2 style="color: #dc3545; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle"></i>
                Danger Zone
            </h2>
            <div class="danger-card">
                <div class="danger-content">
                    <h3 style="color: #dc3545; margin-bottom: 15px;">
                        <i class="fas fa-database"></i>
                        Reset Entire System
                    </h3>
                    <p style="color: #6c757d; margin-bottom: 20px; line-height: 1.6;">
                        <strong>⚠️ WARNING:</strong> This action will permanently delete ALL data from the system including:
                    </p>
                    <ul style="color: #6c757d; margin-bottom: 25px; line-height: 1.8;">
                        <li><strong>All Members</strong> - Complete user accounts and profiles</li>
                        <li><strong>All Payments</strong> - Payment history and transactions</li>
                        <li><strong>All Payouts</strong> - Payout records and distributions</li>
                        <li><strong>All Notifications</strong> - System notifications and messages</li>
                        <li><strong>All Device Tracking</strong> - Registration device data</li>
                    </ul>
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
                        <p style="margin: 0; color: #856404;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Admin accounts will be preserved. Only member data will be removed.
                        </p>
                    </div>
                    <button id="systemResetBtn" class="btn btn-danger" style="background: linear-gradient(135deg, #dc3545, #c82333); border: none; padding: 15px 30px; font-weight: 600;">
                        <i class="fas fa-trash-alt me-2"></i>
                        Reset Entire System
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- System Reset Confirmation Modal -->
    <div id="resetConfirmModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px; background: white; border-radius: 16px; border: 2px solid #dc3545;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 25px; border-radius: 14px 14px 0 0;">
                <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirm System Reset
                </h2>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <i class="fas fa-bomb" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="color: #dc3545; margin-bottom: 15px;">This action cannot be undone!</h3>
                    <p style="color: #6c757d; margin-bottom: 25px;">
                        You are about to permanently delete ALL member data from the system. 
                        This will make the system completely fresh for new equb setup.
                    </p>
                </div>
                
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                    <h4 style="color: #721c24; margin-bottom: 10px;">What will be deleted:</h4>
                    <ul style="color: #721c24; margin: 0; padding-left: 20px;">
                        <li>All member accounts and profiles</li>
                        <li>Complete payment history</li>
                        <li>All payout records</li>
                        <li>System notifications</li>
                        <li>Device tracking data</li>
                    </ul>
                </div>

                <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
                    <p style="margin: 0; color: #0c5460;">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Protected:</strong> Admin accounts will NOT be deleted.
                    </p>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; color: #dc3545; font-weight: 600;">
                        Type "DELETE ALL DATA" to confirm:
                    </label>
                    <input type="text" id="confirmationText" class="form-control" placeholder="Type exactly: DELETE ALL DATA" style="border: 2px solid #dc3545; padding: 12px; border-radius: 8px; width: 100%;">
                </div>
            </div>
            <div class="modal-footer" style="padding: 20px; border-top: 1px solid #dee2e6; display: flex; gap: 15px; justify-content: end;">
                <button onclick="closeResetModal()" class="btn btn-secondary" style="padding: 12px 25px;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button id="confirmResetBtn" onclick="executeSystemReset()" class="btn btn-danger" disabled style="padding: 12px 25px; background: linear-gradient(135deg, #dc3545, #c82333); border: none;">
                    <i class="fas fa-bomb me-2"></i>DELETE ALL DATA
                </button>
            </div>
        </div>
    </div>

    <!-- Coming Soon Modal -->
    <div id="comingSoonModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🚧 Coming Soon</h3>
                <span class="close" onclick="closeComingSoon()">&times;</span>
            </div>
            <div class="modal-body">
                <p id="comingSoonMessage">This feature is currently under development and will be available in a future update.</p>
                <div class="coming-soon-features">
                    <h4>Planned Features:</h4>
                    <ul id="plannedFeatures">
                        <li>Advanced configuration options</li>
                        <li>User-friendly interface</li>
                        <li>Real-time updates</li>
                        <li>Import/Export functionality</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeComingSoon()">Got it!</button>
            </div>
        </div>
    </div>

    <script>
        // Coming Soon Modal Functions
        function showComingSoon(featureName) {
            document.getElementById('comingSoonMessage').textContent = 
                `${featureName} is currently under development and will be available in a future update.`;
            document.getElementById('comingSoonModal').style.display = 'block';
        }

        function closeComingSoon() {
            document.getElementById('comingSoonModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('comingSoonModal');
            if (event.target === modal) {
                closeComingSoon();
            }
        }
    </script>

    <style>
        /* Import dashboard styling for consistency */
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin: 2rem 0;
        }

        /* Use the exact same module card styling as dashboard */
        .settings-category {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid var(--border-light);
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.4s ease;
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .settings-category:hover {
            text-decoration: none;
            color: inherit;
        }

        .active-module:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.15);
            border-color: var(--color-teal);
        }

        .coming-soon-module {
            opacity: 0.7;
            cursor: default;
        }

        .coming-soon-module:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(48, 25, 67, 0.08);
        }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .module-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .module-icon.primary { background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%); }
        .module-icon.secondary { background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%); }
        .module-icon.accent { background: linear-gradient(135deg, var(--color-light-gold) 0%, #B8941C 100%); }
        .module-icon.warning { background: linear-gradient(135deg, var(--color-coral) 0%, #D63447 100%); }
        .module-icon.info { background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%); }
        .module-icon.neutral { background: linear-gradient(135deg, var(--color-light-gold) 0%, #B8941C 100%); }

        .module-status {
            text-align: right;
        }

        .status-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.status-active {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            color: #065F46;
        }

        .status-badge.status-coming-soon {
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
            color: #92400E;
        }

        .settings-category h3 {
            margin: 0 0 12px 0;
            color: var(--color-purple);
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .settings-category p {
            color: var(--text-secondary);
            margin: 0 0 20px 0;
            line-height: 1.6;
            font-size: 15px;
        }

        .category-features {
            margin-bottom: 24px;
        }

        .feature-tag {
            display: inline-block;
            background: var(--color-cream);
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 8px;
            margin-bottom: 8px;
            border: 1px solid var(--border-color);
        }

        .settings-category .btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 24px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-teal), #0F5147);
            color: white;
            box-shadow: 0 4px 12px rgba(19, 102, 92, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(19, 102, 92, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--color-cream);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--color-teal);
            color: var(--color-teal);
            text-decoration: none;
        }

        .system-info-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--color-border);
        }

        .system-info-section h2 {
            margin-bottom: 1.5rem;
            color: var(--color-deep-purple);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-card {
            background: var(--color-cream);
            padding: 16px;
            border-radius: 8px;
            border: 1px solid var(--color-border);
            transition: all 0.3s ease;
        }

        .info-card:hover {
            background: var(--color-white);
            border-color: var(--color-gold);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(77, 64, 82, 0.1);
        }

        .info-card h4 {
            margin: 0 0 8px 0;
            color: var(--color-brown);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-card p {
            margin: 0;
            color: var(--color-deep-purple);
            font-weight: 500;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(77, 64, 82, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: linear-gradient(135deg, var(--color-white) 0%, var(--color-cream) 100%);
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(77, 64, 82, 0.2);
            border: 1px solid var(--color-border);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, var(--color-gold), var(--color-light-gold));
            border-radius: 12px 12px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--color-deep-purple);
            font-weight: 600;
        }

        .close {
            color: var(--color-dark-purple);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: var(--color-deep-purple);
        }

        .modal-body {
            padding: 20px;
            color: var(--color-dark-purple);
        }

        .coming-soon-features {
            margin-top: 16px;
            padding: 16px;
            background: var(--color-white);
            border-radius: 8px;
            border: 1px solid var(--color-border);
        }

        .coming-soon-features h4 {
            margin: 0 0 8px 0;
            color: var(--color-brown);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .coming-soon-features ul {
            margin: 0;
            padding-left: 20px;
            color: var(--color-dark-purple);
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid var(--color-border);
            text-align: right;
            background: var(--color-cream);
            border-radius: 0 0 12px 12px;
        }

        /* Danger Zone Styling */
        .danger-zone-section {
            margin-top: 40px;
            padding: 30px;
            border-radius: 16px;
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border: 2px solid #fc8181;
            box-shadow: 0 8px 32px rgba(220, 53, 69, 0.15);
        }

        .danger-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #f56565;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.1);
        }

        .danger-content h3 {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Reset Modal Styling */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            margin: auto;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            animation: modalSlideIn 0.3s ease-out;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            outline: none;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .settings-category {
                padding: 20px;
            }

            .info-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 12px;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .danger-zone-section {
                margin-top: 30px;
                padding: 20px;
            }

            .danger-card {
                padding: 20px;
            }
        }
    </style>

    <script>
        // System Reset Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // System reset button event
            document.getElementById('systemResetBtn').addEventListener('click', function() {
                document.getElementById('resetConfirmModal').style.display = 'flex';
                document.getElementById('confirmationText').value = '';
                document.getElementById('confirmResetBtn').disabled = true;
            });

            // Confirmation text validation
            document.getElementById('confirmationText').addEventListener('input', function() {
                const confirmBtn = document.getElementById('confirmResetBtn');
                if (this.value === 'DELETE ALL DATA') {
                    confirmBtn.disabled = false;
                    confirmBtn.style.opacity = '1';
                } else {
                    confirmBtn.disabled = true;
                    confirmBtn.style.opacity = '0.5';
                }
            });

            // Close modal when clicking outside
            document.getElementById('resetConfirmModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeResetModal();
                }
            });
        });

        function closeResetModal() {
            document.getElementById('resetConfirmModal').style.display = 'none';
            document.getElementById('confirmationText').value = '';
            document.getElementById('confirmResetBtn').disabled = true;
        }

        async function executeSystemReset() {
            const confirmBtn = document.getElementById('confirmResetBtn');
            const originalText = confirmBtn.innerHTML;
            
            // Show loading state
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting System...';

            try {
                const response = await fetch('api/system-reset.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'reset_system',
                        confirmation: 'DELETE ALL DATA'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Show success message
                    alert('✅ System has been successfully reset!\n\nAll member data has been deleted. The system is now fresh and ready for new equb setup.');
                    
                    // Close modal and refresh page
                    closeResetModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('❌ Reset failed: ' + result.message);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Reset error:', error);
                alert('❌ Network error occurred during reset. Please try again.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        }

        // Existing coming soon functionality
        function showComingSoon(featureName) {
            document.getElementById('comingSoonModal').style.display = 'block';
            document.querySelector('#comingSoonModal h3').textContent = '🚧 ' + featureName + ' - Coming Soon';
        }

        function closeComingSoon() {
            document.getElementById('comingSoonModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('comingSoonModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>