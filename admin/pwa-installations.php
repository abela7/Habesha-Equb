<?php
/**
 * HabeshaEqub - PWA Installations Tracking Page
 * View which users have installed the PWA
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Installations - HabeshaEqub Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- PWA Support -->
    <?php include '../includes/pwa-head.php'; ?>
    
    <style>
        .installations-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #4D4052 0%, #301934 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #DAA520;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .table-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(48, 25, 52, 0.1);
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
        
        .device-info {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <div class="installations-container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-section">
                    <h1>
                        <div class="page-title-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        PWA Installations
                    </h1>
                    <p class="page-subtitle">Track which users have installed the Progressive Web App</p>
                </div>
                <div class="page-actions">
                    <a href="pwa-management.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to PWA Management
                    </a>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-card">
                <h2 style="color: white; margin-bottom: 20px;">
                    <i class="fas fa-chart-bar"></i> Installation Statistics
                </h2>
                <div class="stats-grid" id="statsGrid">
                    <div class="stat-box">
                        <div class="stat-value" id="totalInstalls">-</div>
                        <div class="stat-label">Total Installations</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="memberInstalls">-</div>
                        <div class="stat-label">Member Installations</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="adminInstalls">-</div>
                        <div class="stat-label">Admin Installations</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="recentInstalls">-</div>
                        <div class="stat-label">Last 30 Days</div>
                    </div>
                </div>
            </div>
            
            <!-- Installations Table -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 style="color: #4D4052; margin: 0;">
                        <i class="fas fa-list"></i> Installation List
                    </h3>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                        <button class="btn btn-outline-secondary" onclick="searchInstallations()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div id="installationsTable">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-3">Loading installations...</p>
                    </div>
                </div>
                
                <nav id="paginationNav"></nav>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentPage = 1;
        let currentSearch = '';
        
        async function loadStatistics() {
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
                console.error('Failed to load statistics:', error);
            }
        }
        
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
                const deviceInfo = inst.device_info ? JSON.parse(inst.device_info) : {};
                const browserInfo = inst.browser_info ? JSON.parse(inst.browser_info) : {};
                
                let userName = 'Guest';
                let userType = 'guest';
                
                if (inst.member_first_name) {
                    userName = `${inst.member_first_name} ${inst.member_last_name}`;
                    userType = 'member';
                } else if (inst.admin_username) {
                    userName = inst.admin_username;
                    userType = 'admin';
                }
                
                const installDate = new Date(inst.install_date).toLocaleString();
                const lastSeen = new Date(inst.last_seen).toLocaleString();
                
                html += `
                    <tr>
                        <td>
                            <strong>${userName}</strong><br>
                            <small class="text-muted">${inst.member_email || inst.admin_email || 'N/A'}</small>
                        </td>
                        <td>
                            <span class="user-badge badge-${userType}">
                                <i class="fas fa-${userType === 'member' ? 'user' : 'user-shield'}"></i>
                                ${userType === 'member' ? 'Member' : 'Admin'}
                            </span>
                        </td>
                        <td>
                            <div class="device-info">
                                ${deviceInfo.platform || 'Unknown'}<br>
                                ${deviceInfo.screen ? `${deviceInfo.screen.width}x${deviceInfo.screen.height}` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="device-info">
                                ${browserInfo.browser || 'Unknown'} ${browserInfo.version || ''}<br>
                                <small>${browserInfo.os || ''}</small>
                            </div>
                        </td>
                        <td>${installDate}</td>
                        <td>${lastSeen}</td>
                        <td>
                            <span class="badge bg-secondary">${inst.install_count || 1}</span>
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
            
            let html = '<ul class="pagination justify-content-center">';
            
            // Previous button
            html += `<li class="page-item ${page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadInstallations(${page - 1}, '${currentSearch}'); return false;">Previous</a>
            </li>`;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
                    html += `<li class="page-item ${i === page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadInstallations(${i}, '${currentSearch}'); return false;">${i}</a>
                    </li>`;
                } else if (i === page - 3 || i === page + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Next button
            html += `<li class="page-item ${page === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadInstallations(${page + 1}, '${currentSearch}'); return false;">Next</a>
            </li>`;
            
            html += '</ul>';
            nav.innerHTML = html;
        }
        
        function searchInstallations() {
            currentSearch = document.getElementById('searchInput').value;
            currentPage = 1;
            loadInstallations(currentPage, currentSearch);
        }
        
        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchInstallations();
            }
        });
        
        // Load on page load
        window.addEventListener('load', () => {
            loadStatistics();
            loadInstallations();
        });
    </script>
    
    <!-- PWA Footer -->
    <?php include '../includes/pwa-footer.php'; ?>
</body>
</html>

