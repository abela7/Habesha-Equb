<?php
session_start();
require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';
require_once '../languages/translator.php';

$t = Translator::getInstance();

// Page security-check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$page_title = $t->get('equb_management.page_title');
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['app_language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - HabeshaEqub Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --color-primary: #1e40af;
            --color-secondary: #059669;
            --color-danger: #dc2626;
            --color-warning: #d97706;
            --color-success: #059669;
            --color-info: #0284c7;
            --color-dark: #1f2937;
            --color-light: #f8fafc;
            --color-border: #e5e7eb;
            --color-muted: #6b7280;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--color-dark);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--color-border);
            padding: 1rem 2rem;
            box-shadow: var(--shadow-sm);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--color-dark);
        }

        .page-title .icon {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white;
            padding: 0.75rem;
            border-radius: var(--radius-lg);
            font-size: 1.25rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background: var(--color-success);
            color: white;
        }

        .btn-success:hover {
            background: #047857;
        }

        .btn-warning {
            background: var(--color-warning);
            color: white;
        }

        .btn-warning:hover {
            background: #b45309;
        }

        .btn-danger {
            background: var(--color-danger);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-outline {
            background: white;
            color: var(--color-dark);
            border: 1px solid var(--color-border);
        }

        .btn-outline:hover {
            background: var(--color-light);
            border-color: var(--color-primary);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            padding: 0.75rem;
            border-radius: var(--radius-md);
            font-size: 1.25rem;
        }

        .stat-icon.primary {
            background: rgba(30, 64, 175, 0.1);
            color: var(--color-primary);
        }

        .stat-icon.success {
            background: rgba(5, 150, 105, 0.1);
            color: var(--color-success);
        }

        .stat-icon.warning {
            background: rgba(217, 119, 6, 0.1);
            color: var(--color-warning);
        }

        .stat-icon.danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--color-danger);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-dark);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--color-muted);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .trend-up {
            color: var(--color-success);
        }

        .trend-down {
            color: var(--color-danger);
        }

        /* Content Panel */
        .content-panel {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
            overflow: hidden;
        }

        .panel-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-dark);
        }

        .panel-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* Filters and Search */
        .filters-section {
            padding: 1.5rem;
            border-bottom: 1px solid var(--color-border);
            background: #fafbfc;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-dark);
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-left: 3rem;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-muted);
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }

        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--color-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-table tr:hover {
            background: rgba(30, 64, 175, 0.02);
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.active {
            background: rgba(5, 150, 105, 0.1);
            color: var(--color-success);
        }

        .status-badge.planning {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .status-badge.completed {
            background: rgba(107, 114, 128, 0.1);
            color: var(--color-muted);
        }

        .status-badge.suspended {
            background: rgba(217, 119, 6, 0.1);
            color: var(--color-warning);
        }

        .status-badge.cancelled {
            background: rgba(220, 38, 38, 0.1);
            color: var(--color-danger);
        }

        /* Payment Tiers */
        .payment-tiers {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tier-badge {
            padding: 0.25rem 0.5rem;
            background: var(--color-light);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .tier-badge.full {
            background: rgba(5, 150, 105, 0.1);
            border-color: var(--color-success);
            color: var(--color-success);
        }

        .tier-badge.half {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .tier-badge.quarter {
            background: rgba(217, 119, 6, 0.1);
            border-color: var(--color-warning);
            color: var(--color-warning);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-icon.edit {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .btn-icon.edit:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-icon.delete {
            background: rgba(220, 38, 38, 0.1);
            color: var(--color-danger);
        }

        .btn-icon.delete:hover {
            background: var(--color-danger);
            color: white;
        }

        .btn-icon.view {
            background: rgba(107, 114, 128, 0.1);
            color: var(--color-muted);
        }

        .btn-icon.view:hover {
            background: var(--color-muted);
            color: white;
        }

        /* Loading States */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: var(--color-muted);
        }

        .spinner {
            width: 2rem;
            height: 2rem;
            border: 2px solid var(--color-border);
            border-top: 2px solid var(--color-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.75rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--color-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: var(--color-dark);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 0.8rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--color-muted);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: var(--color-light);
            color: var(--color-dark);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--color-border);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-help {
            font-size: 0.75rem;
            color: var(--color-muted);
            margin-top: 0.25rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.2);
            color: var(--color-success);
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.2);
            color: var(--color-danger);
        }

        .alert-warning {
            background: rgba(217, 119, 6, 0.1);
            border: 1px solid rgba(217, 119, 6, 0.2);
            color: var(--color-warning);
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="page-title">
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h1><?php echo $t->get('equb_management.title'); ?></h1>
                    <p style="font-size: 0.875rem; color: var(--color-muted); margin: 0;">
                        <?php echo $t->get('equb_management.subtitle'); ?>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    <?php echo $t->get('common.refresh'); ?>
                </button>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i>
                    <?php echo $t->get('equb_management.create_new'); ?>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title"><?php echo $t->get('equb_management.stats.total_equbs'); ?></span>
                    <div class="stat-icon primary">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalEqubs">-</div>
                <div class="stat-label"><?php echo $t->get('equb_management.stats.all_terms'); ?></div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up trend-up"></i>
                    <span id="totalEqubsTrend">-</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title"><?php echo $t->get('equb_management.stats.active_equbs'); ?></span>
                    <div class="stat-icon success">
                        <i class="fas fa-play-circle"></i>
                    </div>
                </div>
                <div class="stat-value" id="activeEqubs">-</div>
                <div class="stat-label"><?php echo $t->get('equb_management.stats.currently_running'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title"><?php echo $t->get('equb_management.stats.total_pool'); ?></span>
                    <div class="stat-icon warning">
                        <i class="fas fa-pound-sign"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalPool">-</div>
                <div class="stat-label"><?php echo $t->get('equb_management.stats.combined_value'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title"><?php echo $t->get('equb_management.stats.total_members'); ?></span>
                    <div class="stat-icon danger">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalMembers">-</div>
                <div class="stat-label"><?php echo $t->get('equb_management.stats.enrolled_members'); ?></div>
            </div>
        </div>

        <!-- Main Content Panel -->
        <div class="content-panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fas fa-list"></i>
                    <?php echo $t->get('equb_management.equb_terms'); ?>
                </div>
                <div class="panel-actions">
                    <button class="btn btn-outline btn-sm" onclick="exportData()">
                        <i class="fas fa-download"></i>
                        <?php echo $t->get('common.export'); ?>
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i>
                        <?php echo $t->get('common.filters'); ?>
                    </button>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section" id="filtersSection" style="display: none;">
                <div class="filters-grid">
                    <div class="form-group">
                        <label class="form-label"><?php echo $t->get('equb_management.filters.search'); ?></label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="<?php echo $t->get('equb_management.filters.search_placeholder'); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $t->get('equb_management.filters.status'); ?></label>
                        <select class="form-control" id="statusFilter">
                            <option value=""><?php echo $t->get('common.all'); ?></option>
                            <option value="planning"><?php echo $t->get('equb_management.status.planning'); ?></option>
                            <option value="active"><?php echo $t->get('equb_management.status.active'); ?></option>
                            <option value="completed"><?php echo $t->get('equb_management.status.completed'); ?></option>
                            <option value="suspended"><?php echo $t->get('equb_management.status.suspended'); ?></option>
                            <option value="cancelled"><?php echo $t->get('equb_management.status.cancelled'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $t->get('equb_management.filters.date_range'); ?></label>
                        <input type="date" class="form-control" id="dateFromFilter">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="opacity: 0;">-</label>
                        <input type="date" class="form-control" id="dateToFilter">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="opacity: 0;">-</label>
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-search"></i>
                            <?php echo $t->get('common.apply'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div id="tableContainer">
                <div class="loading">
                    <div class="spinner"></div>
                    <?php echo $t->get('common.loading'); ?>...
                </div>
            </div>
        </div>
    </main>

    <!-- Create/Edit Modal -->
    <div id="equbModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle"><?php echo $t->get('equb_management.create_new'); ?></h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="equbForm">
                    <input type="hidden" id="equbId" name="equb_id">
                    
                    <!-- Basic Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.equb_name'); ?> *</label>
                            <input type="text" class="form-control" id="equbName" name="equb_name" required>
                            <div class="form-help"><?php echo $t->get('equb_management.form.equb_name_help'); ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.status'); ?></label>
                            <select class="form-control" id="equbStatus" name="status">
                                <option value="planning"><?php echo $t->get('equb_management.status.planning'); ?></option>
                                <option value="active"><?php echo $t->get('equb_management.status.active'); ?></option>
                                <option value="completed"><?php echo $t->get('equb_management.status.completed'); ?></option>
                                <option value="suspended"><?php echo $t->get('equb_management.status.suspended'); ?></option>
                                <option value="cancelled"><?php echo $t->get('equb_management.status.cancelled'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label"><?php echo $t->get('equb_management.form.description'); ?></label>
                        <textarea class="form-control" id="equbDescription" name="equb_description" rows="3"></textarea>
                    </div>

                    <!-- Term Configuration -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.max_members'); ?> *</label>
                            <input type="number" class="form-control" id="maxMembers" name="max_members" min="2" max="50" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.duration_months'); ?> *</label>
                            <input type="number" class="form-control" id="durationMonths" name="duration_months" min="1" max="24" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.start_date'); ?> *</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.end_date'); ?></label>
                            <input type="date" class="form-control" id="endDate" name="end_date" readonly>
                            <div class="form-help"><?php echo $t->get('equb_management.form.end_date_help'); ?></div>
                        </div>
                    </div>

                    <!-- Payment Configuration -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.payout_day'); ?></label>
                            <input type="number" class="form-control" id="payoutDay" name="payout_day" min="1" max="31" value="5">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.admin_fee'); ?></label>
                            <input type="number" class="form-control" id="adminFee" name="admin_fee" step="0.01" min="0" value="10.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.late_fee'); ?></label>
                            <input type="number" class="form-control" id="lateFee" name="late_fee" step="0.01" min="0" value="20.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.grace_period'); ?></label>
                            <input type="number" class="form-control" id="gracePeriod" name="grace_period_days" min="0" max="10" value="2">
                        </div>
                    </div>

                    <!-- Payment Tiers -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?php echo $t->get('equb_management.form.payment_tiers'); ?> *</label>
                        <div id="paymentTiersContainer">
                            <!-- Dynamic payment tiers will be added here -->
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addPaymentTier()">
                            <i class="fas fa-plus"></i>
                            <?php echo $t->get('equb_management.form.add_tier'); ?>
                        </button>
                    </div>

                    <!-- Registration Settings -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.registration_start'); ?></label>
                            <input type="date" class="form-control" id="registrationStart" name="registration_start_date">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $t->get('equb_management.form.registration_end'); ?></label>
                            <input type="date" class="form-control" id="registrationEnd" name="registration_end_date">
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="autoAssignPositions" name="auto_assign_positions" checked>
                                <?php echo $t->get('equb_management.form.auto_assign'); ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="approvalRequired" name="approval_required" checked>
                                <?php echo $t->get('equb_management.form.approval_required'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="isPublic" name="is_public" checked>
                                <?php echo $t->get('equb_management.form.is_public'); ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="isFeatured" name="is_featured">
                                <?php echo $t->get('equb_management.form.is_featured'); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?php echo $t->get('equb_management.form.notes'); ?></label>
                        <textarea class="form-control" id="equbNotes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">
                    <?php echo $t->get('common.cancel'); ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="saveEqub()">
                    <i class="fas fa-save"></i>
                    <?php echo $t->get('common.save'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Global variables
        let currentEqubs = [];
        let currentEditId = null;
        let filtersVisible = false;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadEqubs();
            initializeEventListeners();
        });

        // Event listeners
        function initializeEventListeners() {
            // Search input
            document.getElementById('searchInput').addEventListener('input', debounce(applyFilters, 300));
            
            // Status filter
            document.getElementById('statusFilter').addEventListener('change', applyFilters);
            
            // Date calculation
            document.getElementById('startDate').addEventListener('change', calculateEndDate);
            document.getElementById('durationMonths').addEventListener('change', calculateEndDate);
        }

        // Load equbs data
        async function loadEqubs() {
            try {
                showLoading();
                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: 'load' })
                });

                const result = await response.json();
                
                if (result.success) {
                    currentEqubs = result.data.equbs;
                    updateStats(result.data.stats);
                    renderTable(currentEqubs);
                } else {
                    showAlert('error', result.message);
                }
            } catch (error) {
                showAlert('error', 'Failed to load equb data: ' + error.message);
            }
        }

        // Update statistics
        function updateStats(stats) {
            document.getElementById('totalEqubs').textContent = stats.total_equbs;
            document.getElementById('activeEqubs').textContent = stats.active_equbs;
            document.getElementById('totalPool').textContent = '£' + formatNumber(stats.total_pool);
            document.getElementById('totalMembers').textContent = stats.total_members;
            
            // Update trends (placeholder - you can implement actual trend calculation)
            document.getElementById('totalEqubsTrend').textContent = '+12% this month';
        }

        // Render data table
        function renderTable(data) {
            const container = document.getElementById('tableContainer');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h3>No Equb Terms Found</h3>
                        <p>Create your first equb term to get started with member management.</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i>
                            Create First Equb
                        </button>
                    </div>
                `;
                return;
            }

            const tableHtml = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Equb Name</th>
                            <th>Status</th>
                            <th>Members</th>
                            <th>Duration</th>
                            <th>Start Date</th>
                            <th>Payment Tiers</th>
                            <th>Pool Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(equb => `
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">${escapeHtml(equb.equb_name)}</div>
                                    <div style="font-size: 0.8rem; color: var(--color-muted);">${equb.equb_id}</div>
                                </td>
                                <td>
                                    <span class="status-badge ${equb.status}">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                        ${capitalizeFirst(equb.status)}
                                    </span>
                                </td>
                                <td>
                                    <strong>${equb.current_members}/${equb.max_members}</strong>
                                    <div style="font-size: 0.8rem; color: var(--color-muted);">
                                        ${Math.round((equb.current_members / equb.max_members) * 100)}% filled
                                    </div>
                                </td>
                                <td>${equb.duration_months} months</td>
                                <td>${formatDate(equb.start_date)}</td>
                                <td>
                                    <div class="payment-tiers">
                                        ${renderPaymentTiers(equb.payment_tiers)}
                                    </div>
                                </td>
                                <td>
                                    <strong>£${formatNumber(equb.total_pool_amount)}</strong>
                                    <div style="font-size: 0.8rem; color: var(--color-success);">
                                        £${formatNumber(equb.collected_amount)} collected
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon view" onclick="viewEqub(${equb.id})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon edit" onclick="editEqub(${equb.id})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon delete" onclick="deleteEqub(${equb.id}, '${escapeHtml(equb.equb_name)}')" title="Delete">
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

        // Render payment tiers
        function renderPaymentTiers(tiersJson) {
            try {
                const tiers = JSON.parse(tiersJson);
                return tiers.map(tier => 
                    `<span class="tier-badge ${tier.tag}">£${tier.amount} ${tier.tag}</span>`
                ).join('');
            } catch (e) {
                return '<span class="tier-badge">Invalid tiers</span>';
            }
        }

        // Show loading state
        function showLoading() {
            document.getElementById('tableContainer').innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    Loading equb data...
                </div>
            `;
        }

        // Modal functions
        function openCreateModal() {
            currentEditId = null;
            document.getElementById('modalTitle').textContent = 'Create New Equb Term';
            document.getElementById('equbForm').reset();
            clearPaymentTiers();
            addDefaultPaymentTiers();
            document.getElementById('equbModal').classList.add('show');
        }

        function editEqub(id) {
            currentEditId = id;
            const equb = currentEqubs.find(e => e.id === id);
            if (!equb) return;

            document.getElementById('modalTitle').textContent = 'Edit Equb Term';
            populateForm(equb);
            document.getElementById('equbModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('equbModal').classList.remove('show');
        }

        // Populate form with equb data
        function populateForm(equb) {
            document.getElementById('equbId').value = equb.id;
            document.getElementById('equbName').value = equb.equb_name;
            document.getElementById('equbDescription').value = equb.equb_description || '';
            document.getElementById('equbStatus').value = equb.status;
            document.getElementById('maxMembers').value = equb.max_members;
            document.getElementById('durationMonths').value = equb.duration_months;
            document.getElementById('startDate').value = equb.start_date;
            document.getElementById('endDate').value = equb.end_date;
            document.getElementById('payoutDay').value = equb.payout_day;
            document.getElementById('adminFee').value = equb.admin_fee;
            document.getElementById('lateFee').value = equb.late_fee;
            document.getElementById('gracePeriod').value = equb.grace_period_days;
            document.getElementById('registrationStart').value = equb.registration_start_date || '';
            document.getElementById('registrationEnd').value = equb.registration_end_date || '';
            document.getElementById('autoAssignPositions').checked = equb.auto_assign_positions == 1;
            document.getElementById('approvalRequired').checked = equb.approval_required == 1;
            document.getElementById('isPublic').checked = equb.is_public == 1;
            document.getElementById('isFeatured').checked = equb.is_featured == 1;
            document.getElementById('equbNotes').value = equb.notes || '';

            // Populate payment tiers
            clearPaymentTiers();
            try {
                const tiers = JSON.parse(equb.payment_tiers);
                tiers.forEach(tier => addPaymentTier(tier));
            } catch (e) {
                addDefaultPaymentTiers();
            }
        }

        // Payment tier management
        function clearPaymentTiers() {
            document.getElementById('paymentTiersContainer').innerHTML = '';
        }

        function addDefaultPaymentTiers() {
            addPaymentTier({ amount: 1000, tag: 'full', description: 'Full Member - £1000/month' });
            addPaymentTier({ amount: 500, tag: 'half', description: 'Half Member - £500/month' });
            addPaymentTier({ amount: 250, tag: 'quarter', description: 'Quarter Member - £250/month' });
        }

        function addPaymentTier(data = {}) {
            const container = document.getElementById('paymentTiersContainer');
            const index = container.children.length;
            
            const tierHtml = `
                <div class="payment-tier-row" style="display: flex; gap: 1rem; margin-bottom: 0.75rem; align-items: end;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Amount (£)</label>
                        <input type="number" class="form-control" name="tier_amount_${index}" 
                               value="${data.amount || ''}" step="0.01" min="0" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Tag</label>
                        <input type="text" class="form-control" name="tier_tag_${index}" 
                               value="${data.tag || ''}" placeholder="e.g., full, half" required>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="tier_description_${index}" 
                               value="${data.description || ''}" placeholder="Member tier description">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removePaymentTier(this)" 
                            style="height: 2.75rem;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', tierHtml);
        }

        function removePaymentTier(button) {
            button.closest('.payment-tier-row').remove();
        }

        // Calculate end date
        function calculateEndDate() {
            const startDate = document.getElementById('startDate').value;
            const duration = parseInt(document.getElementById('durationMonths').value);
            
            if (startDate && duration) {
                const start = new Date(startDate);
                start.setMonth(start.getMonth() + duration);
                document.getElementById('endDate').value = start.toISOString().split('T')[0];
            }
        }

        // Save equb
        async function saveEqub() {
            try {
                const formData = new FormData(document.getElementById('equbForm'));
                
                // Collect payment tiers
                const tiers = [];
                const container = document.getElementById('paymentTiersContainer');
                for (let i = 0; i < container.children.length; i++) {
                    const amount = formData.get(`tier_amount_${i}`);
                    const tag = formData.get(`tier_tag_${i}`);
                    const description = formData.get(`tier_description_${i}`);
                    
                    if (amount && tag) {
                        tiers.push({ amount: parseFloat(amount), tag, description });
                    }
                }

                const data = {
                    action: currentEditId ? 'update' : 'create',
                    id: currentEditId,
                    equb_name: formData.get('equb_name'),
                    equb_description: formData.get('equb_description'),
                    status: formData.get('status'),
                    max_members: parseInt(formData.get('max_members')),
                    duration_months: parseInt(formData.get('duration_months')),
                    start_date: formData.get('start_date'),
                    end_date: formData.get('end_date'),
                    payment_tiers: JSON.stringify(tiers),
                    payout_day: parseInt(formData.get('payout_day')),
                    admin_fee: parseFloat(formData.get('admin_fee')),
                    late_fee: parseFloat(formData.get('late_fee')),
                    grace_period_days: parseInt(formData.get('grace_period_days')),
                    registration_start_date: formData.get('registration_start_date') || null,
                    registration_end_date: formData.get('registration_end_date') || null,
                    auto_assign_positions: formData.get('auto_assign_positions') ? 1 : 0,
                    approval_required: formData.get('approval_required') ? 1 : 0,
                    is_public: formData.get('is_public') ? 1 : 0,
                    is_featured: formData.get('is_featured') ? 1 : 0,
                    notes: formData.get('notes')
                };

                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', result.message);
                    closeModal();
                    loadEqubs();
                } else {
                    showAlert('error', result.message);
                }
            } catch (error) {
                showAlert('error', 'Failed to save equb: ' + error.message);
            }
        }

        // Delete equb
        async function deleteEqub(id, name) {
            if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: 'delete', id: id })
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', result.message);
                    loadEqubs();
                } else {
                    showAlert('error', result.message);
                }
            } catch (error) {
                showAlert('error', 'Failed to delete equb: ' + error.message);
            }
        }

        // View equb details
        function viewEqub(id) {
            // Navigate to detailed view page
            window.location.href = `equb-details.php?id=${id}`;
        }

        // Filter functions
        function toggleFilters() {
            filtersVisible = !filtersVisible;
            const section = document.getElementById('filtersSection');
            section.style.display = filtersVisible ? 'block' : 'none';
        }

        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const dateFrom = document.getElementById('dateFromFilter').value;
            const dateTo = document.getElementById('dateToFilter').value;

            const filtered = currentEqubs.filter(equb => {
                // Search filter
                if (search && !equb.equb_name.toLowerCase().includes(search) && 
                    !equb.equb_id.toLowerCase().includes(search)) {
                    return false;
                }

                // Status filter
                if (status && equb.status !== status) {
                    return false;
                }

                // Date range filter
                if (dateFrom && equb.start_date < dateFrom) {
                    return false;
                }
                if (dateTo && equb.start_date > dateTo) {
                    return false;
                }

                return true;
            });

            renderTable(filtered);
        }

        // Export data
        function exportData() {
            const csvContent = generateCSV(currentEqubs);
            downloadCSV(csvContent, `equb-data-${new Date().toISOString().split('T')[0]}.csv`);
        }

        // Utility functions
        function refreshData() {
            loadEqubs();
        }

        function showAlert(type, message) {
            const container = document.getElementById('alertContainer');
            const alertHtml = `
                <div class="alert alert-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    ${message}
                </div>
            `;
            container.innerHTML = alertHtml;
            
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }

        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        function generateCSV(data) {
            const headers = ['ID', 'Name', 'Status', 'Members', 'Duration', 'Start Date', 'Pool Amount'];
            const rows = data.map(equb => [
                equb.equb_id,
                equb.equb_name,
                equb.status,
                `${equb.current_members}/${equb.max_members}`,
                `${equb.duration_months} months`,
                equb.start_date,
                equb.total_pool_amount
            ]);
            
            return [headers, ...rows].map(row => row.join(',')).join('\n');
        }

        function downloadCSV(content, filename) {
            const blob = new Blob([content], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>