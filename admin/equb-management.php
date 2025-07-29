<?php
/**
 * HabeshaEqub - Equb Management System
 * Comprehensive equb term management and financial administration
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once '../languages/user_language_handler.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Set admin's language preference from database
setAdminLanguageFromDatabase($admin_id);

// Get equb statistics for dashboard
try {
    // Get equb settings with proper calculations
    $stmt = $pdo->query("
        SELECT 
            es.*,
            COUNT(DISTINCT m.id) as current_members,
            COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) as collected_amount,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as distributed_amount
        FROM equb_settings es
        LEFT JOIN members m ON m.equb_settings_id = es.id AND m.is_active = 1
        LEFT JOIN payments p ON p.member_id = m.id
        LEFT JOIN payouts po ON po.member_id = m.id
        GROUP BY es.id
        ORDER BY es.created_at DESC
    ");
    $equbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate overall statistics
    $total_equbs = count($equbs);
    $active_equbs = count(array_filter($equbs, fn($e) => $e['status'] === 'active'));
    $total_pool = array_sum(array_column($equbs, 'total_pool_amount'));
    $total_members = array_sum(array_column($equbs, 'current_members'));
    
} catch (PDOException $e) {
    error_log("Error fetching equb data: " . $e->getMessage());
    $equbs = [];
    $total_equbs = 0;
    $active_equbs = 0;
    $total_pool = 0;
    $total_members = 0;
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('equb_management.page_title'); ?> - HabeshaEqub Admin</title>
    
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
        /* === EQUB MANAGEMENT PAGE STYLES === */
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
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
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
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

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--color-teal);
        }

        .stat-card.primary::before { background: var(--color-teal); }
        .stat-card.success::before { background: var(--color-gold); }
        .stat-card.warning::before { background: var(--color-light-gold); }
        .stat-card.danger::before { background: var(--color-coral); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.primary { background: linear-gradient(135deg, var(--color-teal), #0F5147); }
        .stat-icon.success { background: linear-gradient(135deg, var(--color-gold), #D4A72C); }
        .stat-icon.warning { background: linear-gradient(135deg, var(--color-light-gold), #B8962F); }
        .stat-icon.danger { background: linear-gradient(135deg, var(--color-coral), #D44638); }

        .stat-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--color-purple);
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Content Panel */
        .content-panel {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .panel-header {
            padding: 32px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-purple);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel-actions {
            display: flex;
            gap: 12px;
        }

        /* Filters */
        .filters-section {
            padding: 24px 32px;
            border-bottom: 1px solid var(--border-color);
            background: #FEFFFE;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        /* Table */
        .table-container {
            padding: 32px;
        }

        .equb-table {
            width: 100%;
            border-collapse: collapse;
        }

        .equb-table th {
            background: #F8F9FA;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--color-purple);
            border-bottom: 2px solid var(--border-color);
        }

        .equb-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .equb-table tbody tr:hover {
            background: #FEFFFE;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.planning { background: #E3F2FD; color: #1976D2; }
        .status-badge.active { background: #E8F5E8; color: #2E7D32; }
        .status-badge.completed { background: #F3E5F5; color: #7B1FA2; }
        .status-badge.suspended { background: #FFF3E0; color: #F57C00; }
        .status-badge.cancelled { background: #FFEBEE; color: #D32F2F; }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-view { background: var(--color-teal); color: white; }
        .btn-edit { background: var(--color-gold); color: white; }
        .btn-delete { background: var(--color-coral); color: white; }

        .btn-action:hover {
            transform: scale(1.1);
        }

        /* Alert Messages */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 12px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        .alert-success { background: #E8F5E8; color: #2E7D32; border: 1px solid #4CAF50; }
        .alert-error { background: #FFEBEE; color: #D32F2F; border: 1px solid #F44336; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Loading */
        .loading {
            text-align: center;
            padding: 60px;
            color: var(--text-secondary);
        }

        .loading i {
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <!-- Include Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Alert Container -->
        <div id="alertContainer" class="alert-container"></div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    <div class="page-title-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <?php echo t('equb_management.title'); ?>
                </h1>
                <p class="page-subtitle"><?php echo t('equb_management.subtitle'); ?></p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline-secondary me-2" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-2"></i>
                    <?php echo t('common.refresh'); ?>
                </button>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo t('equb_management.create_new'); ?>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-title"><?php echo t('equb_management.stats.total_equbs'); ?></div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalEqubs"><?php echo $total_equbs; ?></div>
                <div class="stat-label"><?php echo t('equb_management.stats.all_terms'); ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-title"><?php echo t('equb_management.stats.active_equbs'); ?></div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-play-circle"></i>
                    </div>
                </div>
                <div class="stat-value" id="activeEqubs"><?php echo $active_equbs; ?></div>
                <div class="stat-label"><?php echo t('equb_management.stats.currently_running'); ?></div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-title"><?php echo t('equb_management.stats.total_pool'); ?></div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-pound-sign"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalPool">£<?php echo number_format($total_pool, 2); ?></div>
                <div class="stat-label"><?php echo t('equb_management.stats.combined_value'); ?></div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-title"><?php echo t('equb_management.stats.total_members'); ?></div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalMembers"><?php echo $total_members; ?></div>
                <div class="stat-label"><?php echo t('equb_management.stats.enrolled_members'); ?></div>
            </div>
        </div>

        <!-- Main Content Panel -->
        <div class="content-panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fas fa-list"></i>
                    <?php echo t('equb_management.equb_terms'); ?>
                </div>
                <div class="panel-actions">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportData()">
                        <i class="fas fa-download me-1"></i>
                        <?php echo t('common.export'); ?>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleFilters()">
                        <i class="fas fa-filter me-1"></i>
                        <?php echo t('common.filters'); ?>
                    </button>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section" id="filtersSection" style="display: none;">
                <div class="filters-grid">
                    <div class="form-group">
                        <label class="form-label"><?php echo t('equb_management.filters.search'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchInput"
                                   placeholder="<?php echo t('equb_management.filters.search_placeholder'); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo t('equb_management.filters.status'); ?></label>
                        <select class="form-control" id="statusFilter">
                            <option value=""><?php echo t('common.all'); ?></option>
                            <option value="planning"><?php echo t('equb_management.status.planning'); ?></option>
                            <option value="active"><?php echo t('equb_management.status.active'); ?></option>
                            <option value="completed"><?php echo t('equb_management.status.completed'); ?></option>
                            <option value="suspended"><?php echo t('equb_management.status.suspended'); ?></option>
                            <option value="cancelled"><?php echo t('equb_management.status.cancelled'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo t('equb_management.filters.date_range'); ?></label>
                        <input type="date" class="form-control" id="dateFromFilter">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="opacity: 0;">-</label>
                        <input type="date" class="form-control" id="dateToFilter">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="opacity: 0;">-</label>
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-search me-1"></i>
                            <?php echo t('common.apply'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-container">
                <div id="tableContainer">
                    <div class="loading">
                        <i class="fas fa-spinner"></i>
                        <?php echo t('common.loading'); ?>...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global variables
        let equbsData = [];
        let filteredData = [];
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadEqubData();
        });

        // Load equb data from API
        async function loadEqubData() {
            try {
                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'load' })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    equbsData = data.data.equbs || [];
                    filteredData = [...equbsData];
                    updateStatistics(data.data.stats);
                    renderTable();
                } else {
                    showAlert('error', data.message || 'Failed to load data');
                }
            } catch (error) {
                console.error('Error loading data:', error);
                showAlert('error', 'Network error. Please check your connection.');
            }
        }

        // Update statistics
        function updateStatistics(stats) {
            if (stats) {
                document.getElementById('totalEqubs').textContent = stats.total_equbs || 0;
                document.getElementById('activeEqubs').textContent = stats.active_equbs || 0;
                document.getElementById('totalPool').textContent = '£' + (stats.total_pool || 0).toLocaleString('en-GB', {minimumFractionDigits: 2});
                document.getElementById('totalMembers').textContent = stats.total_members || 0;
            }
        }

        // Render table
        function renderTable() {
            const container = document.getElementById('tableContainer');
            
            if (filteredData.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No equb terms found</h5>
                        <p class="text-muted">Create your first equb term to get started.</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus me-2"></i>Create New Equb Term
                        </button>
                    </div>
                `;
                return;
            }

            const tableHtml = `
                <table class="equb-table">
                    <thead>
                        <tr>
                            <th>Equb Name</th>
                            <th>Status</th>
                            <th>Members</th>
                            <th>Duration</th>
                            <th>Pool Value</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filteredData.map(equb => `
                            <tr>
                                <td>
                                    <div>
                                        <strong>${escapeHtml(equb.equb_name)}</strong>
                                        <br>
                                        <small class="text-muted">${equb.equb_id}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge ${equb.status}">${formatStatus(equb.status)}</span>
                                </td>
                                <td>
                                    <strong>${equb.current_members}/${equb.max_members}</strong>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: ${(equb.current_members/equb.max_members)*100}%"></div>
                                    </div>
                                </td>
                                <td>${equb.duration_months} months</td>
                                <td>£${parseFloat(equb.total_pool_amount || 0).toLocaleString('en-GB', {minimumFractionDigits: 2})}</td>
                                <td>${formatDate(equb.start_date)}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewEqub(${equb.id})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editEqub(${equb.id})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteEqub(${equb.id})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            container.innerHTML = tableHtml;
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatStatus(status) {
            const statusMap = {
                'planning': '<?php echo t("equb_management.status.planning"); ?>',
                'active': '<?php echo t("equb_management.status.active"); ?>',
                'completed': '<?php echo t("equb_management.status.completed"); ?>',
                'suspended': '<?php echo t("equb_management.status.suspended"); ?>',
                'cancelled': '<?php echo t("equb_management.status.cancelled"); ?>'
            };
            return statusMap[status] || status;
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        // Action functions
        function viewEqub(id) {
            const equb = equbsData.find(e => e.id == id);
            if (equb) {
                showAlert('info', `Viewing details for: ${equb.equb_name}`);
                // TODO: Implement view modal
            }
        }

        function editEqub(id) {
            const equb = equbsData.find(e => e.id == id);
            if (equb) {
                showAlert('info', `Editing: ${equb.equb_name}`);
                // TODO: Implement edit modal
            }
        }

        function deleteEqub(id) {
            const equb = equbsData.find(e => e.id == id);
            if (equb && confirm(`Are you sure you want to delete "${equb.equb_name}"?`)) {
                showAlert('info', `Deleting: ${equb.equb_name}`);
                // TODO: Implement delete functionality
            }
        }

        function openCreateModal() {
            showAlert('info', 'Create new equb functionality coming soon!');
            // TODO: Implement create modal
        }

        // Filter functions
        function toggleFilters() {
            const filtersSection = document.getElementById('filtersSection');
            filtersSection.style.display = filtersSection.style.display === 'none' ? 'block' : 'none';
        }

        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const dateFrom = document.getElementById('dateFromFilter').value;
            const dateTo = document.getElementById('dateToFilter').value;

            filteredData = equbsData.filter(equb => {
                let matches = true;

                if (search) {
                    matches = matches && (
                        equb.equb_name.toLowerCase().includes(search) ||
                        equb.equb_id.toLowerCase().includes(search)
                    );
                }

                if (status) {
                    matches = matches && equb.status === status;
                }

                if (dateFrom) {
                    matches = matches && new Date(equb.start_date) >= new Date(dateFrom);
                }

                if (dateTo) {
                    matches = matches && new Date(equb.start_date) <= new Date(dateTo);
                }

                return matches;
            });

            renderTable();
        }

        // Utility functions
        function refreshData() {
            loadEqubData();
        }

        function exportData() {
            showAlert('info', 'Export functionality coming soon!');
        }

        function showAlert(type, message) {
            const container = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'error' ? 'error' : 'success'}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
                ${message}
            `;
            
            container.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>