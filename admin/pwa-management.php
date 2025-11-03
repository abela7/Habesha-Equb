<?php
/**
 * HabeshaEqub - PWA Management Page
 * Visual interface to manage PWA updates without editing code
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Read current service worker version
$serviceWorkerPath = '../service-worker.js';
$currentVersion = '1.0.0';
$manifestPath = '../manifest.json';

if (file_exists($serviceWorkerPath)) {
    $swContent = file_get_contents($serviceWorkerPath);
    if (preg_match('/CACHE_VERSION\s*=\s*[\'"]([^\'"]+)[\'"]/', $swContent, $matches)) {
        $currentVersion = $matches[1];
    }
}

// Get update history from database (create table if needed)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pwa_updates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(20) NOT NULL,
            updated_by INT NOT NULL,
            update_note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_version (version),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get update history
    $historyStmt = $pdo->prepare("
        SELECT pu.*, a.username as admin_username
        FROM pwa_updates pu
        LEFT JOIN admins a ON pu.updated_by = a.id
        ORDER BY pu.created_at DESC
        LIMIT 20
    ");
    $historyStmt->execute();
    $updateHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error with PWA updates table: " . $e->getMessage());
    $updateHistory = [];
}

// Get manifest info
$manifestData = [];
if (file_exists($manifestPath)) {
    $manifestJson = file_get_contents($manifestPath);
    $manifestData = json_decode($manifestJson, true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Management - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- PWA Support -->
    <?php include '../includes/pwa-head.php'; ?>
    
    <style>
        /* PWA Management Styles */
        .pwa-management-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .pwa-status-card {
            background: linear-gradient(135deg, #4D4052 0%, #301934 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 24px rgba(48, 25, 52, 0.2);
        }
        
        .pwa-status-card h2 {
            color: white;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .version-display {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .version-number {
            font-size: 48px;
            font-weight: 700;
            color: #DAA520;
            margin: 0;
        }
        
        .version-label {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }
        
        .update-form-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 16px rgba(48, 25, 52, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #4D4052;
            margin-bottom: 8px;
            display: block;
        }
        
        .version-input-group {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        
        .version-input {
            flex: 1;
            max-width: 200px;
        }
        
        .btn-update-pwa {
            background: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
            color: #4D4052;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-update-pwa:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(218, 165, 32, 0.4);
        }
        
        .btn-update-pwa:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .history-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(48, 25, 52, 0.1);
        }
        
        .history-item {
            padding: 16px;
            border-left: 4px solid #DAA520;
            background: #F1ECE2;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .history-item:last-child {
            margin-bottom: 0;
        }
        
        .history-version {
            font-size: 20px;
            font-weight: 700;
            color: #4D4052;
            margin-bottom: 4px;
        }
        
        .history-meta {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }
        
        /* Tab Styles */
        .nav-tabs {
            border-bottom: 2px solid #F1ECE2;
        }
        
        .nav-tabs .nav-link {
            color: #4D4052;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 15px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .nav-tabs .nav-link:hover {
            border-bottom-color: #DAA520;
            color: #301934;
        }
        
        .nav-tabs .nav-link.active {
            color: #4D4052;
            background: transparent;
            border-bottom-color: #DAA520;
        }
        
        .tab-content {
            min-height: 400px;
        }
        
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-member {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-admin {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-guest {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .device-info {
            font-size: 12px;
            color: #666;
        }
        
        .info-box {
            background: #F1ECE2;
            border-left: 4px solid #DAA520;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #DAA520;
            margin-right: 8px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #DAA520;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 4px;
        }
        
        .alert-custom {
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .version-input-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .version-input {
                max-width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <div class="pwa-management-container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-section">
                    <h1>
                        <div class="page-title-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        PWA Management
                    </h1>
                    <p class="page-subtitle">Manage Progressive Web App updates and versions</p>
                </div>
            </div>
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Status Card -->
            <div class="pwa-status-card">
                <h2>
                    <i class="fas fa-info-circle"></i>
                    Current Status
                </h2>
                
                <div class="version-display">
                    <p class="version-number" id="currentVersion">v<?php echo htmlspecialchars($currentVersion); ?></p>
                    <p class="version-label">Current PWA Version</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($updateHistory); ?></div>
                        <div class="stat-label">Total Updates</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo htmlspecialchars($manifestData['name'] ?? 'HabeshaEqub'); ?></div>
                        <div class="stat-label">App Name</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $manifestData['display'] ?? 'standalone'; ?></div>
                        <div class="stat-label">Display Mode</div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="update-form-card" style="padding: 0;">
                <ul class="nav nav-tabs" id="pwaTabs" role="tablist" style="border-bottom: 2px solid #F1ECE2; padding: 0 30px; margin: 0;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="version-tab" data-bs-toggle="tab" data-bs-target="#version-panel" type="button" role="tab">
                            <i class="fas fa-code-branch me-2"></i>Version Management
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="installations-tab" data-bs-toggle="tab" data-bs-target="#installations-panel" type="button" role="tab">
                            <i class="fas fa-mobile-alt me-2"></i>Installations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-panel" type="button" role="tab">
                            <i class="fas fa-history me-2"></i>Update History
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="pwaTabContent" style="padding: 30px;">
                    <!-- Version Management Tab -->
                    <div class="tab-pane fade show active" id="version-panel" role="tabpanel">
                        <div class="info-box">
                            <i class="fas fa-lightbulb"></i>
                            <strong>How it works:</strong> When you update the version, all users who have installed the app will receive a notification to update. They can update with a single click - no need to uninstall and reinstall!
                        </div>
                        
                        <form id="updatePWAForm">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-code me-2"></i>
                                    New Version Number
                                </label>
                                <div class="version-input-group">
                                    <input 
                                        type="text" 
                                        class="form-control version-input" 
                                        id="newVersion" 
                                        name="version" 
                                        value="<?php echo htmlspecialchars($currentVersion); ?>"
                                        placeholder="e.g., 1.0.1"
                                        pattern="[0-9]+\.[0-9]+\.[0-9]+"
                                        required
                                    >
                                    <button type="submit" class="btn-update-pwa" id="updateBtn">
                                        <i class="fas fa-rocket"></i>
                                        Update Now
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Format: Major.Minor.Patch (e.g., 1.0.1, 1.1.0, 2.0.0)
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    Update Note (Optional)
                                </label>
                                <textarea 
                                    class="form-control" 
                                    id="updateNote" 
                                    name="note" 
                                    rows="3"
                                    placeholder="What's new in this update? (e.g., Bug fixes, New features, Performance improvements)"
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Installations Tab -->
                    <div class="tab-pane fade" id="installations-panel" role="tabpanel">
                        <!-- Statistics -->
                        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 30px;">
                            <div class="stat-item">
                                <div class="stat-value" id="totalInstalls">-</div>
                                <div class="stat-label">Total Installs</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="memberInstalls">-</div>
                                <div class="stat-label">Members</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="adminInstalls">-</div>
                                <div class="stat-label">Admins</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="recentInstalls">-</div>
                                <div class="stat-label">Last 30 Days</div>
                            </div>
                        </div>
                        
                        <!-- Search -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 style="color: #4D4052; margin: 0;">
                                <i class="fas fa-list"></i> Installation List
                            </h4>
                            <div class="input-group" style="max-width: 300px;">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                                <button class="btn btn-outline-secondary" onclick="searchInstallations()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Installations Table -->
                        <div id="installationsTable">
                            <div class="text-center py-5">
                                <div class="spinner-border" role="status"></div>
                                <p class="mt-3">Loading installations...</p>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <nav id="paginationNav" class="mt-3"></nav>
                    </div>
                    
                    <!-- Update History Tab -->
                    <div class="tab-pane fade" id="history-panel" role="tabpanel">
                        <?php if (empty($updateHistory)): ?>
                            <div class="alert alert-info alert-custom">
                                <i class="fas fa-info-circle me-2"></i>
                                No updates have been made yet. Update the version to create the first entry.
                            </div>
                        <?php else: ?>
                            <div id="updateHistoryList">
                                <?php foreach ($updateHistory as $update): ?>
                                    <div class="history-item">
                                        <div class="history-version">
                                            <i class="fas fa-tag me-2"></i>
                                            Version <?php echo htmlspecialchars($update['version']); ?>
                                        </div>
                                        <?php if (!empty($update['update_note'])): ?>
                                            <p style="margin: 8px 0; color: #4D4052;">
                                                <?php echo nl2br(htmlspecialchars($update['update_note'])); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="history-meta">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($update['admin_username'] ?? 'System'); ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('M j, Y g:i A', strtotime($update['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update PWA Form Handler
        document.getElementById('updatePWAForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const version = document.getElementById('newVersion').value.trim();
            const note = document.getElementById('updateNote').value.trim();
            const updateBtn = document.getElementById('updateBtn');
            
            // Validate version format
            if (!/^\d+\.\d+\.\d+$/.test(version)) {
                showAlert('Invalid version format. Please use format: Major.Minor.Patch (e.g., 1.0.1)', 'danger');
                return;
            }
            
            // Disable button
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            
            try {
                const response = await fetch('api/pwa-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_version',
                        version: version,
                        note: note
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('✅ PWA version updated successfully! Users will be notified to update.', 'success');
                    
                    // Update UI
                    document.getElementById('currentVersion').textContent = 'v' + version;
                    
                    // Clear form
                    document.getElementById('updateNote').value = '';
                    
                    // Reload page after 2 seconds to show new history
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('❌ Error: ' + (data.message || 'Failed to update version'), 'danger');
                    updateBtn.disabled = false;
                    updateBtn.innerHTML = '<i class="fas fa-rocket"></i> Update Now';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Network error. Please try again.', 'danger');
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-rocket"></i> Update Now';
            }
        });
        
        // Load installation statistics
        async function loadInstallStats() {
            // Check if stats elements exist (they're inside the installations tab)
            const totalInstallsEl = document.getElementById('totalInstalls');
            if (!totalInstallsEl) {
                // Elements don't exist yet (tab not visible), skip loading
                return;
            }
            
            try {
                const response = await fetch('api/pwa-installations.php?action=get_statistics');
                const data = await response.json();
                
                if (data.success) {
                    const memberInstallsEl = document.getElementById('memberInstalls');
                    const adminInstallsEl = document.getElementById('adminInstalls');
                    const recentInstallsEl = document.getElementById('recentInstalls');
                    
                    // Safely update elements if they exist
                    if (totalInstallsEl) totalInstallsEl.textContent = data.statistics.total || 0;
                    if (memberInstallsEl) memberInstallsEl.textContent = data.statistics.members || 0;
                    if (adminInstallsEl) adminInstallsEl.textContent = data.statistics.admins || 0;
                    if (recentInstallsEl) recentInstallsEl.textContent = data.statistics.recent_30_days || 0;
                }
            } catch (error) {
                console.error('Failed to load installation stats:', error);
            }
        }
        
        // Installation management functions
        let currentPage = 1;
        let currentSearch = '';
        
        async function loadInstallations(page = 1, search = '') {
            try {
                const url = `api/pwa-installations.php?action=get_installations&page=${page}&limit=50&search=${encodeURIComponent(search)}`;
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    displayInstallations(data.installations);
                    displayPagination(data.total, data.page, data.limit);
                } else {
                    document.getElementById('installationsTable').innerHTML = 
                        '<div class="alert alert-danger">Failed to load installations</div>';
                }
            } catch (error) {
                console.error('Failed to load installations:', error);
                document.getElementById('installationsTable').innerHTML = 
                    '<div class="alert alert-danger">Error loading installations</div>';
            }
        }
        
        // HTML escape function to prevent XSS
        function escapeHtml(text) {
            if (text == null) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function displayInstallations(installations) {
            if (installations.length === 0) {
                document.getElementById('installationsTable').innerHTML = 
                    '<div class="alert alert-info">No installations found</div>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>Install Date</th>
                                <th>Last Seen</th>
                                <th>Installs</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            installations.forEach(inst => {
                // Safely parse JSON with error handling
                let deviceInfo = {};
                let browserInfo = {};
                
                if (inst.device_info) {
                    try {
                        deviceInfo = JSON.parse(inst.device_info);
                    } catch (e) {
                        console.warn('Failed to parse device_info:', e);
                        deviceInfo = {};
                    }
                }
                
                if (inst.browser_info) {
                    try {
                        browserInfo = JSON.parse(inst.browser_info);
                    } catch (e) {
                        console.warn('Failed to parse browser_info:', e);
                        browserInfo = {};
                    }
                }
                
                let userName = 'Guest';
                let userType = 'guest';
                
                if (inst.member_first_name) {
                    // Escape user-supplied names before concatenation
                    const firstName = escapeHtml(inst.member_first_name || '');
                    const lastName = escapeHtml(inst.member_last_name || '');
                    userName = `${firstName} ${lastName}`.trim() || 'Guest';
                    userType = 'member';
                } else if (inst.admin_username) {
                    // Escape admin username
                    userName = escapeHtml(inst.admin_username) || 'Guest';
                    userType = 'admin';
                }
                
                // Escape email addresses
                const userEmail = escapeHtml(inst.member_email || inst.admin_email || '');
                const displayEmail = userEmail || 'N/A';
                
                // Determine badge icon and text based on user type
                let badgeIcon = 'user';
                let badgeText = 'Guest';
                
                if (userType === 'member') {
                    badgeIcon = 'user';
                    badgeText = 'Member';
                } else if (userType === 'admin') {
                    badgeIcon = 'user-shield';
                    badgeText = 'Admin';
                }
                
                // Escape device and browser info (could contain user-controlled data)
                const devicePlatform = escapeHtml(deviceInfo.platform || 'Unknown');
                const deviceScreen = deviceInfo.screen 
                    ? `${escapeHtml(String(deviceInfo.screen.width || ''))}x${escapeHtml(String(deviceInfo.screen.height || ''))}`
                    : '';
                const browserName = escapeHtml(browserInfo.browser || 'Unknown');
                const browserVersion = escapeHtml(browserInfo.version || '');
                const browserOS = escapeHtml(browserInfo.os || '');
                
                const installDate = new Date(inst.install_date).toLocaleString();
                const lastSeen = new Date(inst.last_seen).toLocaleString();
                const installCount = escapeHtml(String(inst.install_count || 1));
                
                html += `
                    <tr>
                        <td>
                            <strong>${userName}</strong><br>
                            <small class="text-muted">${displayEmail}</small>
                        </td>
                        <td>
                            <span class="user-badge badge-${userType}">
                                <i class="fas fa-${badgeIcon}"></i>
                                ${badgeText}
                            </span>
                        </td>
                        <td>
                            <div class="device-info">
                                ${devicePlatform}<br>
                                ${deviceScreen}
                            </div>
                        </td>
                        <td>
                            <div class="device-info">
                                ${browserName} ${browserVersion}<br>
                                <small>${browserOS}</small>
                            </div>
                        </td>
                        <td>${installDate}</td>
                        <td>${lastSeen}</td>
                        <td>
                            <span class="badge bg-secondary">${installCount}</span>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('installationsTable').innerHTML = html;
        }
        
        function displayPagination(total, page, limit) {
            const totalPages = Math.ceil(total / limit);
            const nav = document.getElementById('paginationNav');
            
            if (totalPages <= 1) {
                nav.innerHTML = '';
                return;
            }
            
            // Previous button
            const prevBtn = document.createElement('li');
            prevBtn.className = `page-item ${page === 1 ? 'disabled' : ''}`;
            const prevLink = document.createElement('a');
            prevLink.className = 'page-link';
            prevLink.href = '#';
            prevLink.textContent = 'Previous';
            if (page > 1) {
                prevLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadInstallations(page - 1, currentSearch);
                });
            }
            prevBtn.appendChild(prevLink);
            
            // Page numbers
            const pageNumbers = [];
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
                    const pageItem = document.createElement('li');
                    pageItem.className = `page-item ${i === page ? 'active' : ''}`;
                    const pageLink = document.createElement('a');
                    pageLink.className = 'page-link';
                    pageLink.href = '#';
                    pageLink.textContent = i.toString();
                    pageLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        loadInstallations(i, currentSearch);
                    });
                    pageItem.appendChild(pageLink);
                    pageNumbers.push(pageItem);
                } else if (i === page - 3 || i === page + 3) {
                    const ellipsis = document.createElement('li');
                    ellipsis.className = 'page-item disabled';
                    const ellipsisSpan = document.createElement('span');
                    ellipsisSpan.className = 'page-link';
                    ellipsisSpan.textContent = '...';
                    ellipsis.appendChild(ellipsisSpan);
                    pageNumbers.push(ellipsis);
                }
            }
            
            // Next button
            const nextBtn = document.createElement('li');
            nextBtn.className = `page-item ${page === totalPages ? 'disabled' : ''}`;
            const nextLink = document.createElement('a');
            nextLink.className = 'page-link';
            nextLink.href = '#';
            nextLink.textContent = 'Next';
            if (page < totalPages) {
                nextLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadInstallations(page + 1, currentSearch);
                });
            }
            nextBtn.appendChild(nextLink);
            
            // Build the list
            const ul = document.createElement('ul');
            ul.className = 'pagination justify-content-center';
            ul.appendChild(prevBtn);
            pageNumbers.forEach(item => ul.appendChild(item));
            ul.appendChild(nextBtn);
            
            nav.innerHTML = '';
            nav.appendChild(ul);
        }
        
        function searchInstallations() {
            currentSearch = document.getElementById('searchInput').value;
            currentPage = 1;
            loadInstallations(currentPage, currentSearch);
        }
        
        // Search on Enter key
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchInstallations();
            }
        });
        
        // Load installations when installations tab is shown
        document.getElementById('installations-tab')?.addEventListener('shown.bs.tab', () => {
            loadInstallStats();
            loadInstallations();
        });
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-custom alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        }
    </script>
    
    <!-- PWA Footer -->
    <?php include '../includes/pwa-footer.php'; ?>
</body>
</html>

