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
            
            <!-- Update Form Card -->
            <div class="update-form-card">
                <h3 style="color: #4D4052; margin-bottom: 20px;">
                    <i class="fas fa-sync-alt me-2"></i>
                    Update PWA Version
                </h3>
                
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
            
            <!-- Installation Statistics Card -->
            <div class="update-form-card">
                <h3 style="color: #4D4052; margin-bottom: 20px;">
                    <i class="fas fa-mobile-alt me-2"></i>
                    Installation Statistics
                </h3>
                
                <div id="installStats" class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
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
                
                <div class="mt-3">
                    <a href="pwa-installations.php" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>
                        View All Installations
                    </a>
                </div>
            </div>
            
            <!-- Update History Card -->
            <div class="history-card">
                <h3 style="color: #4D4052; margin-bottom: 20px;">
                    <i class="fas fa-history me-2"></i>
                    Update History
                </h3>
                
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
            try {
                const response = await fetch('api/pwa-installations.php?action=get_statistics');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalInstalls').textContent = data.statistics.total || 0;
                    document.getElementById('memberInstalls').textContent = data.statistics.members || 0;
                    document.getElementById('adminInstalls').textContent = data.statistics.admins || 0;
                    document.getElementById('recentInstalls').textContent = data.statistics.recent_30_days || 0;
                }
            } catch (error) {
                console.error('Failed to load installation stats:', error);
            }
        }
        
        // Load stats on page load
        window.addEventListener('load', () => {
            loadInstallStats();
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

