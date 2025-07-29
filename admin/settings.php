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
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M19 22s-3-3-3-6 3-6 3-6"/>
                        <path d="M16 12s3 3 3 6-3 6-3 6"/>
                    </svg>
                </div>
                <h3>Translation Management</h3>
                <p>Manage English and Amharic translations for the entire system</p>
                <div class="category-features">
                    <span class="feature-tag">Live Editor</span>
                    <span class="feature-tag">JSON Management</span>
                    <span class="feature-tag">Bi-lingual</span>
                </div>
                <a href="translation.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Manage Translations
                </a>
            </div>

            <!-- Equb Management -->
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1m17-4a4 4 0 0 0-8 0m8 8a4 4 0 0 0-8 0"/>
                    </svg>
                </div>
                <h3>Equb Management</h3>
                <p>Manage and monitor equb transactions, reports, and financial data</p>
                <div class="category-features">
                    <span class="feature-tag">Transaction History</span>
                    <span class="feature-tag">Reports</span>
                    <span class="feature-tag">Financial Analytics</span>
                </div>
                <a href="equb-management.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Manage Equbs
                </a>
            </div>

            <!-- System Configuration -->
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1m17-4a4 4 0 0 0-8 0m8 8a4 4 0 0 0-8 0"/>
                    </svg>
                </div>
                <h3>System Configuration</h3>
                <p>Configure general system settings, defaults, and preferences</p>
                <div class="category-features">
                    <span class="feature-tag">General Settings</span>
                    <span class="feature-tag">Defaults</span>
                    <span class="feature-tag">Preferences</span>
                </div>
                <a href="#" class="btn btn-secondary" onclick="showComingSoon('System Configuration')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 15h-2v-6h2zm0-8h-2V7h2z"/>
                    </svg>
                    Configure System
                </a>
            </div>

            <!-- User Management -->
            <div class="settings-category">
                <div class="category-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Admin Management</h3>
                <p>Manage admin accounts, permissions, and access controls</p>
                <div class="category-features">
                    <span class="feature-tag">Admin Accounts</span>
                    <span class="feature-tag">Permissions</span>
                    <span class="feature-tag">Security</span>
                </div>
                <a href="#" class="btn btn-secondary" onclick="showComingSoon('Admin Management')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 15l2 2 4-4"/>
                        <path d="M21 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"/>
                        <path d="M3 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"/>
                    </svg>
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
        /* Professional Color Palette - Elegant & Consistent */
        :root {
            --color-cream: #F1ECE2;
            --color-dark-purple: #4D4052;
            --color-deep-purple: #301934;
            --color-gold: #DAA520;
            --color-light-gold: #CDAF56;
            --color-brown: #5D4225;
            --color-white: #FFFFFF;
            --color-light-bg: #F1ECE2;
            --color-border: rgba(77, 64, 82, 0.15);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin: 2rem 0;
        }

        .settings-category {
            background: linear-gradient(135deg, var(--color-white) 0%, var(--color-cream) 100%);
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--color-border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(77, 64, 82, 0.05);
        }

        .settings-category::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-gold), var(--color-light-gold));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .settings-category:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(77, 64, 82, 0.15);
            border-color: var(--color-gold);
        }

        .settings-category:hover::before {
            opacity: 1;
        }

        /* Special styling for Translation Management */
        .settings-category:nth-child(1) {
            background: linear-gradient(135deg, var(--color-white) 0%, var(--color-cream) 100%);
            border-color: var(--color-gold);
        }

        .settings-category:nth-child(1) .category-icon {
            background: linear-gradient(135deg, var(--color-gold), var(--color-light-gold));
        }

        /* Special styling for Equb Management */
        .settings-category:nth-child(2) {
            background: linear-gradient(135deg, #FAF7F0 0%, var(--color-cream) 100%);
            border-color: var(--color-deep-purple);
        }

        .settings-category:nth-child(2) .category-icon {
            background: linear-gradient(135deg, var(--color-deep-purple), var(--color-dark-purple));
        }

        .settings-category:nth-child(2):hover {
            border-color: var(--color-deep-purple);
            box-shadow: 0 20px 40px rgba(48, 25, 52, 0.2);
        }

        .settings-category:nth-child(2) .btn-primary {
            background: linear-gradient(135deg, var(--color-deep-purple), var(--color-dark-purple));
            color: var(--color-white);
        }

        .settings-category:nth-child(2) .btn-primary:hover {
            background: linear-gradient(135deg, var(--color-dark-purple), var(--color-deep-purple));
            transform: translateY(-2px);
        }

        .category-icon {
            background: linear-gradient(135deg, var(--color-dark-purple), var(--color-deep-purple));
            color: var(--color-white);
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 8px rgba(77, 64, 82, 0.2);
        }

        .settings-category h3 {
            margin: 0 0 8px 0;
            color: var(--color-deep-purple);
            font-size: 1.25rem;
            font-weight: 600;
        }

        .settings-category p {
            color: var(--color-dark-purple);
            margin: 0 0 16px 0;
            line-height: 1.5;
        }

        .category-features {
            margin-bottom: 20px;
        }

        .feature-tag {
            display: inline-block;
            background: var(--color-cream);
            color: var(--color-brown);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 8px;
            margin-bottom: 4px;
            border: 1px solid var(--color-border);
        }

        .settings-category .btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-gold), var(--color-light-gold));
            color: var(--color-deep-purple);
            box-shadow: 0 4px 8px rgba(218, 165, 32, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--color-light-gold), var(--color-gold));
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(218, 165, 32, 0.3);
        }

        .btn-secondary {
            background: var(--color-cream);
            color: var(--color-dark-purple);
            border: 1px solid var(--color-border);
        }

        .btn-secondary:hover {
            background: var(--color-white);
            border-color: var(--color-gold);
            color: var(--color-deep-purple);
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
        }
    </style>
</body>
</html>