<?php
/**
 * HabeshaEqub - Equb Management System
 * Comprehensive equb term management and financial administration
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
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
    // Get basic equb settings
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
    $equbs_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate correct pool amounts using enhanced calculator
    $calculator = getEnhancedEqubCalculator();
    $equbs = [];

    foreach ($equbs_raw as $equb) {
        $equb_calculation = $calculator->calculateEqubPositions($equb['id']);

        if ($equb_calculation['success']) {
            // CORRECT: Total pool value = total monthly contributions × duration
            // This represents the total value contributed over the entire EQUB term
            $monthly_pool = $equb_calculation['total_monthly_pool'];
            $duration = $equb['duration_months']; // FROM DATABASE
            $equb['calculated_pool_amount'] = $monthly_pool * $duration;
        } else {
            $equb['calculated_pool_amount'] = 0;
        }

        $equbs[] = $equb;
    }
    
    // Calculate overall statistics
    $total_equbs = count($equbs);
    $active_equbs = count(array_filter($equbs, fn($e) => $e['status'] === 'active'));
    $total_pool = array_sum(array_column($equbs, 'calculated_pool_amount'));
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

        /* Enhanced Equb Management Card */
        .equb-management-card {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .equb-management-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .equb-management-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .equb-management-title {
            flex: 1;
        }

        .equb-management-title h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--color-purple);
        }

        .equb-management-title p {
            margin: 8px 0 0 0;
            color: var(--text-secondary);
            font-size: 16px;
        }

        .equb-management-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .equb-stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .equb-stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--color-purple);
            margin-bottom: 8px;
        }

        .equb-stat-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .equb-management-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-equb-primary {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-equb-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 81, 71, 0.3);
        }

        .btn-equb-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: var(--color-purple);
            border: 2px solid var(--color-teal);
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-equb-secondary:hover {
            background: var(--color-teal);
            color: white;
            transform: translateY(-2px);
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

        /* Enhanced Equb Management Hero Card */
        .equb-management-hero-card {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 24px;
            padding: 48px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 8px 16px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .hero-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 48px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-icon-section {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-icon-wrapper {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow:
                0 16px 32px rgba(15, 81, 71, 0.3),
                0 8px 16px rgba(15, 81, 71, 0.2);
        }

        .hero-icon-wrapper i {
            font-size: 48px;
            color: white;
            z-index: 1;
        }

        .icon-glow {
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            background: linear-gradient(135deg, var(--color-teal), #0F5147);
            border-radius: 28px;
            opacity: 0.3;
            filter: blur(8px);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }

        .hero-text-section {
            text-align: center;
        }

        .hero-title {
            margin-bottom: 16px;
        }

        .hero-title-main {
            display: block;
            font-size: 42px;
            font-weight: 800;
            color: var(--color-purple);
            letter-spacing: -1px;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .hero-title-subtitle {
            display: block;
            font-size: 18px;
            font-weight: 500;
            color: var(--color-teal);
            letter-spacing: 0.5px;
        }

        .hero-description {
            font-size: 16px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 24px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-features {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-purple);
            background: rgba(255, 255, 255, 0.8);
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .feature-item i {
            color: var(--color-teal);
        }

        .hero-actions-section {
            display: flex;
            flex-direction: column;
            gap: 32px;
            align-items: center;
        }

        .action-buttons-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(15, 81, 71, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 81, 71, 0.4);
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: var(--color-purple);
            border: 2px solid var(--color-teal);
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-hero-secondary:hover {
            background: var(--color-teal);
            color: white;
            transform: translateY(-2px);
        }

        .quick-stats {
            display: flex;
            gap: 24px;
            justify-content: center;
        }

        .quick-stat-item {
            text-align: center;
            padding: 16px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            min-width: 80px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 800;
            color: var(--color-purple);
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .hero-background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .pattern-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .pattern-circle-1 {
            width: 200px;
            height: 200px;
            top: -100px;
            right: -100px;
            animation: float 6s ease-in-out infinite;
        }

        .pattern-circle-2 {
            width: 150px;
            height: 150px;
            bottom: -75px;
            left: -75px;
            animation: float 8s ease-in-out infinite reverse;
        }

        .pattern-circle-3 {
            width: 100px;
            height: 100px;
            top: 50%;
            left: 20%;
            animation: float 10s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Enhanced Feature Cards */
        .feature-card.enhanced {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow:
                0 8px 24px rgba(0, 0, 0, 0.08),
                0 4px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card.enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent 0%, currentColor 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card.enhanced:hover {
            transform: translateY(-8px);
            box-shadow:
                0 16px 40px rgba(0, 0, 0, 0.12),
                0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .feature-card.enhanced:hover::before {
            opacity: 1;
        }

        .feature-card .card-body {
            padding: 32px;
            position: relative;
            z-index: 1;
        }

        .feature-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            position: relative;
            color: currentColor;
        }

        .feature-icon .icon-accent {
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: currentColor;
            border-radius: 18px;
            opacity: 0.1;
            z-index: -1;
        }

        .feature-badge {
            background: linear-gradient(135deg, currentColor 0%, rgba(255, 255, 255, 0.2) 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .feature-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--color-purple);
            margin-bottom: 12px;
            margin-top: 0;
        }

        .feature-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .btn-feature {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, currentColor 0%, rgba(255, 255, 255, 0.1) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .btn-feature:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-feature span {
            transition: transform 0.3s ease;
        }

        .btn-feature:hover span {
            transform: translateX(-2px);
        }

        .btn-feature i {
            transition: transform 0.3s ease;
        }

        .btn-feature:hover i {
            transform: translateX(4px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .equb-management-hero-card {
                padding: 32px 24px;
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 32px;
                text-align: center;
            }

            .hero-icon-section {
                order: -1;
            }

            .hero-actions-section {
                order: 1;
            }

            .hero-features {
                justify-content: center;
            }

            .action-buttons-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
                justify-content: center;
            }

            .quick-stats {
                flex-wrap: wrap;
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

        @media (max-width: 576px) {
            .hero-title-main {
                font-size: 32px;
            }

            .hero-features {
                gap: 12px;
            }

            .feature-item {
                padding: 6px 12px;
                font-size: 12px;
            }

            .quick-stats {
                gap: 12px;
            }

            .quick-stat-item {
                min-width: 60px;
                padding: 12px;
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
                <button class="btn btn-warning me-2" onclick="recalculateAllValues()" id="recalculateBtn">
                    <i class="fas fa-calculator me-2"></i>
                    Recalculate All Values
                </button>
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

        <!-- Enhanced Financial Features Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info border-0" style="background: linear-gradient(135deg, var(--light-purple) 0%, #F8F6FF 100%); border-left: 4px solid var(--purple) !important;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2">
                                <i class="fas fa-star text-gold me-2"></i>
                                <?php echo t('financial_audit.title'); ?> & <?php echo t('joint_membership.title'); ?> Features
                            </h5>
                            <p class="mb-2">Access advanced financial analytics, real-time payout calculations, and joint membership management.</p>
                        </div>
                        <div class="col-md-4 text-end">
                                                    <div class="btn-group" role="group">
                            <a href="financial-analytics.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-chart-line me-1"></i>
                                Financial Analytics
                            </a>
                            <a href="joint-groups.php" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-users me-1"></i>
                                Joint Groups
                            </a>
                            <a href="payment-tiers.php" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-coins me-1"></i>
                                Payment Tiers
                            </a>
                            <a href="payout-positions.php" class="btn btn-outline-purple btn-sm">
                                <i class="fas fa-sort-numeric-down me-1"></i>
                                Payout Positions
                            </a>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Equb Management System Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="equb-management-hero-card">
                    <div class="hero-content">
                        <div class="hero-icon-section">
                            <div class="hero-icon-wrapper">
                                <i class="fas fa-crown"></i>
                                <div class="icon-glow"></div>
                            </div>
                        </div>
                        <div class="hero-text-section">
                            <h2 class="hero-title">
                                <span class="hero-title-main">Equb Management System</span>
                                <span class="hero-title-subtitle">Advanced Financial Administration</span>
                            </h2>
                            <p class="hero-description">
                                Comprehensive equb term management with real-time calculations, automated payouts,
                                and advanced financial analytics. Manage traditional Ethiopian savings groups with
                                modern precision and security.
                            </p>
                            <div class="hero-features">
                                <div class="feature-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Enterprise Security</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-calculator"></i>
                                    <span>Real-time Calculations</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-users"></i>
                                    <span>Joint Membership Support</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Financial Analytics</span>
                                </div>
                            </div>
                        </div>
                        <div class="hero-actions-section">
                            <div class="action-buttons-group">
                                <button class="btn-hero-primary" onclick="openCreateModal()">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Create New Equb
                                </button>
                                <button class="btn-hero-secondary" onclick="recalculateAllValues()">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Recalculate Values
                                </button>
                            </div>
                            <div class="quick-stats">
                                <div class="quick-stat-item">
                                    <div class="stat-number" id="hero-total-equbs"><?php echo $total_equbs; ?></div>
                                    <div class="stat-label">Active Terms</div>
                                </div>
                                <div class="quick-stat-item">
                                    <div class="stat-number" id="hero-total-pool">£<?php echo number_format($total_pool, 0); ?>K</div>
                                    <div class="stat-label">Total Pool</div>
                                </div>
                                <div class="quick-stat-item">
                                    <div class="stat-number" id="hero-total-members"><?php echo $total_members; ?></div>
                                    <div class="stat-label">Members</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-background-pattern">
                        <div class="pattern-circle pattern-circle-1"></div>
                        <div class="pattern-circle pattern-circle-2"></div>
                        <div class="pattern-circle pattern-circle-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Feature Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="feature-card enhanced" style="border-left: 4px solid var(--color-teal) !important;">
                    <div class="card-body">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-calculator"></i>
                                <div class="icon-accent"></div>
                            </div>
                            <div class="feature-badge">Core</div>
                        </div>
                        <h6 class="feature-title">Real-time Calculations</h6>
                        <p class="feature-description">Accurate payout calculations using authentic traditional EQUB principles with position-based coefficients.</p>
                        <a href="financial-analytics.php" class="btn-feature">
                            <span>View Analytics</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="feature-card enhanced" style="border-left: 4px solid var(--color-gold) !important;">
                    <div class="card-body">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                                <div class="icon-accent"></div>
                            </div>
                            <div class="feature-badge">Advanced</div>
                        </div>
                        <h6 class="feature-title">Joint Groups</h6>
                        <p class="feature-description">Manage joint memberships where multiple people share one position with proportional payouts.</p>
                        <a href="joint-groups.php" class="btn-feature">
                            <span>Manage Groups</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="feature-card enhanced" style="border-left: 4px solid var(--color-light-gold) !important;">
                    <div class="card-body">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-coins"></i>
                                <div class="icon-accent"></div>
                            </div>
                            <div class="feature-badge">Config</div>
                        </div>
                        <h6 class="feature-title">Payment Tiers</h6>
                        <p class="feature-description">Set up and customize payment tiers for each EQUB term with flexible contribution options.</p>
                        <a href="payment-tiers.php" class="btn-feature">
                            <span>Manage Tiers</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="feature-card enhanced" style="border-left: 4px solid var(--color-purple) !important;">
                    <div class="card-body">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-sort-numeric-down"></i>
                                <div class="icon-accent"></div>
                            </div>
                            <div class="feature-badge">Premium</div>
                        </div>
                        <h6 class="feature-title">Payout Positions</h6>
                        <p class="feature-description">Drag and drop to customize member payout positions and timing with visual management.</p>
                        <a href="payout-positions.php" class="btn-feature">
                            <span>Manage Positions</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Management Tools -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--gold) !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle" style="background: rgba(218, 165, 32, 0.1); padding: 15px; margin-right: 15px;">
                                <i class="fas fa-heartbeat" style="color: var(--gold);"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Financial Health Monitoring</h6>
                                <small class="text-muted">Real-time financial insights</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-3">Monitor collection rates, distribution percentages, and financial integrity with comprehensive dashboards.</p>
                        <a href="financial-analytics.php#health" class="btn btn-sm" style="background: var(--gold); color: white; border: none;">
                            <i class="fas fa-chart-line me-1"></i>
                            View Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--gold) !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle" style="background: rgba(218, 165, 32, 0.1); padding: 15px; margin-right: 15px;">
                                <i class="fas fa-clipboard-check" style="color: var(--gold);"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Comprehensive Financial Audit</h6>
                                <small class="text-muted">Detailed reporting system</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-3">Generate detailed financial audit reports with member calculations and verification status.</p>
                        <a href="financial-analytics.php#audit" class="btn btn-sm" style="background: var(--gold); color: white; border: none;">
                            <i class="fas fa-file-invoice me-1"></i>
                            Run Audit
                        </a>
                    </div>
                </div>
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

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        Equb Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="equbModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus me-2"></i>
                        Create New Equb Term
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="equbForm">
                        <input type="hidden" id="equbId" name="equb_id">
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Equb Name *</label>
                                <input type="text" class="form-control" id="equbName" name="equb_name" required>
                                <div class="form-text">Choose a unique, descriptive name for this equb term</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-control" id="equbStatus" name="status">
                                    <option value="planning">Planning</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="equbDescription" name="equb_description" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Term Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Maximum Members *</label>
                                <input type="number" class="form-control" id="maxMembers" name="max_members" min="2" max="50" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration (Months) *</label>
                                <input type="number" class="form-control" id="durationMonths" name="duration_months" min="1" max="24" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="startDate" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="end_date" readonly>
                                <div class="form-text">Automatically calculated based on duration</div>
                            </div>
                        </div>

                        <!-- Payment Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Payout Day of Month</label>
                                <input type="number" class="form-control" id="payoutDay" name="payout_day" min="1" max="31" value="5">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Admin Fee (£)</label>
                                <input type="number" class="form-control" id="adminFee" name="admin_fee" step="0.01" min="0" value="10.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Late Fee (£)</label>
                                <input type="number" class="form-control" id="lateFee" name="late_fee" step="0.01" min="0" value="20.00">
                            </div>
                        </div>

                        <!-- Payment Tiers -->
                        <div class="mb-4">
                            <label class="form-label">Payment Tiers *</label>
                            <div id="paymentTiersContainer">
                                <!-- Dynamic payment tiers will be added here -->
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addPaymentTier()">
                                <i class="fas fa-plus me-1"></i>
                                Add Payment Tier
                            </button>
                        </div>

                        <!-- Settings -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoAssignPositions" name="auto_assign_positions" checked>
                                    <label class="form-check-label" for="autoAssignPositions">
                                        Auto-assign Payout Positions
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="approvalRequired" name="approval_required" checked>
                                    <label class="form-check-label" for="approvalRequired">
                                        Require Admin Approval
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isPublic" name="is_public" checked>
                                    <label class="form-check-label" for="isPublic">
                                        Public Visibility
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isFeatured" name="is_featured">
                                    <label class="form-check-label" for="isFeatured">
                                        Featured Equb
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="equbNotes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveEqub()">
                        <i class="fas fa-save me-1"></i>
                        Save Equb Term
                    </button>
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
        let currentEditingId = null;
        let paymentTierCounter = 0;
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadEqubData();
            initializePaymentTiers();
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
                                <td>£${parseFloat(equb.calculated_pool_amount || 0).toLocaleString('en-GB', {minimumFractionDigits: 2})}</td>
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

        // View equb details
        function viewEqub(id) {
            const equb = equbsData.find(e => e.id == id);
            if (!equb) return;

            const paymentTiers = JSON.parse(equb.payment_tiers || '[]');
            
            const detailsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Basic Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Equb ID:</strong></td><td>${equb.equb_id}</td></tr>
                            <tr><td><strong>Name:</strong></td><td>${escapeHtml(equb.equb_name)}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="status-badge ${equb.status}">${formatStatus(equb.status)}</span></td></tr>
                            <tr><td><strong>Description:</strong></td><td>${escapeHtml(equb.equb_description || 'N/A')}</td></tr>
                        </table>
                        
                        <h6 class="text-primary mb-3 mt-4">Term Configuration</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Max Members:</strong></td><td>${equb.max_members}</td></tr>
                            <tr><td><strong>Current Members:</strong></td><td>${equb.current_members}</td></tr>
                            <tr><td><strong>Duration:</strong></td><td>${equb.duration_months} months</td></tr>
                            <tr><td><strong>Start Date:</strong></td><td>${formatDate(equb.start_date)}</td></tr>
                            <tr><td><strong>End Date:</strong></td><td>${formatDate(equb.end_date)}</td></tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Financial Details</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Total Pool:</strong></td><td>£${parseFloat(equb.calculated_pool_amount || 0).toLocaleString('en-GB', {minimumFractionDigits: 2})}</td></tr>
                            <tr><td><strong>Collected:</strong></td><td>£${parseFloat(equb.collected_amount || 0).toLocaleString('en-GB', {minimumFractionDigits: 2})}</td></tr>
                            <tr><td><strong>Distributed:</strong></td><td>£${parseFloat(equb.distributed_amount || 0).toLocaleString('en-GB', {minimumFractionDigits: 2})}</td></tr>
                            <tr><td><strong>Payout Day:</strong></td><td>${equb.payout_day}th of each month</td></tr>
                            <tr><td><strong>Admin Fee:</strong></td><td>£${parseFloat(equb.admin_fee || 0).toFixed(2)}</td></tr>
                            <tr><td><strong>Late Fee:</strong></td><td>£${parseFloat(equb.late_fee || 0).toFixed(2)}</td></tr>
                        </table>
                        
                        <h6 class="text-primary mb-3 mt-4">Payment Tiers</h6>
                        <div class="payment-tiers">
                            ${paymentTiers.map(tier => `
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <strong>${escapeHtml(tier.tag || 'N/A')}</strong>
                                    <span>£${parseFloat(tier.amount || 0).toFixed(2)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                
                ${equb.notes ? `
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Admin Notes</h6>
                            <div class="p-3 bg-light rounded">
                                ${escapeHtml(equb.notes)}
                            </div>
                        </div>
                    </div>
                ` : ''}
            `;

            document.getElementById('viewModalBody').innerHTML = detailsHtml;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        // Edit equb
        function editEqub(id) {
            const equb = equbsData.find(e => e.id == id);
            if (!equb) return;

            currentEditingId = id;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Equb Term';
            
            // Fill form with existing data
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
            document.getElementById('autoAssignPositions').checked = equb.auto_assign_positions == 1;
            document.getElementById('approvalRequired').checked = equb.approval_required == 1;
            document.getElementById('isPublic').checked = equb.is_public == 1;
            document.getElementById('isFeatured').checked = equb.is_featured == 1;
            document.getElementById('equbNotes').value = equb.notes || '';
            
            // Load payment tiers
            loadPaymentTiers(JSON.parse(equb.payment_tiers || '[]'));
            
            new bootstrap.Modal(document.getElementById('equbModal')).show();
        }

        // Delete equb
        async function deleteEqub(id) {
            const equb = equbsData.find(e => e.id == id);
            if (!equb) return;

            if (!confirm(`Are you sure you want to delete "${equb.equb_name}"?\n\nThis action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        action: 'delete',
                        id: id
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', data.message);
                    loadEqubData(); // Reload data
                } else {
                    showAlert('error', data.message || 'Failed to delete equb term');
                }
            } catch (error) {
                console.error('Error deleting equb:', error);
                showAlert('error', 'Network error. Please try again.');
            }
        }

        // Create new equb
        function openCreateModal() {
            currentEditingId = null;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Create New Equb Term';
            document.getElementById('equbForm').reset();
            document.getElementById('equbId').value = '';
            
            // Reset payment tiers
            initializePaymentTiers();
            
            new bootstrap.Modal(document.getElementById('equbModal')).show();
        }

        // Save equb (create or update)
        async function saveEqub() {
            const formData = new FormData(document.getElementById('equbForm'));
            const data = Object.fromEntries(formData.entries());
            
            // Add payment tiers
            data.payment_tiers = getPaymentTiersData();
            
            // Convert checkboxes to boolean
            data.auto_assign_positions = document.getElementById('autoAssignPositions').checked;
            data.approval_required = document.getElementById('approvalRequired').checked;
            data.is_public = document.getElementById('isPublic').checked;
            data.is_featured = document.getElementById('isFeatured').checked;
            
            const action = currentEditingId ? 'update' : 'create';
            if (currentEditingId) {
                data.id = currentEditingId;
            }

            try {
                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        action: action,
                        ...data
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', result.message);
                    bootstrap.Modal.getInstance(document.getElementById('equbModal')).hide();
                    loadEqubData(); // Reload data
                } else {
                    if (result.data && result.data.errors) {
                        showAlert('error', 'Validation errors: ' + result.data.errors.join(', '));
                    } else {
                        showAlert('error', result.message || 'Failed to save equb term');
                    }
                }
            } catch (error) {
                console.error('Error saving equb:', error);
                showAlert('error', 'Network error. Please try again.');
            }
        }

        // Payment tiers management
        function initializePaymentTiers() {
            paymentTierCounter = 0;
            const container = document.getElementById('paymentTiersContainer');
            container.innerHTML = '';
            addPaymentTier(); // Add one default tier
        }

        function addPaymentTier(amount = '', tag = '', description = '') {
            paymentTierCounter++;
            const container = document.getElementById('paymentTiersContainer');
            
            const tierHtml = `
                <div class="payment-tier mb-3" data-tier-id="${paymentTierCounter}">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Amount (£)</label>
                            <input type="number" class="form-control tier-amount" step="0.01" min="0" value="${amount}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tag</label>
                            <input type="text" class="form-control tier-tag" value="${tag}" placeholder="e.g., full, half" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control tier-description" value="${description}" placeholder="e.g., Full Member - £1000/month">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-outline-danger btn-sm d-block" onclick="removePaymentTier(${paymentTierCounter})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', tierHtml);
        }

        function removePaymentTier(tierId) {
            const tiers = document.querySelectorAll('.payment-tier');
            if (tiers.length > 1) {
                document.querySelector(`[data-tier-id="${tierId}"]`).remove();
            } else {
                showAlert('error', 'At least one payment tier is required');
            }
        }

        function getPaymentTiersData() {
            const tiers = [];
            document.querySelectorAll('.payment-tier').forEach(tier => {
                const amount = tier.querySelector('.tier-amount').value;
                const tag = tier.querySelector('.tier-tag').value;
                const description = tier.querySelector('.tier-description').value;
                
                if (amount && tag) {
                    tiers.push({
                        amount: parseFloat(amount),
                        tag: tag,
                        description: description
                    });
                }
            });
            return tiers;
        }

        function loadPaymentTiers(tiers) {
            const container = document.getElementById('paymentTiersContainer');
            container.innerHTML = '';
            paymentTierCounter = 0;
            
            if (tiers && tiers.length > 0) {
                tiers.forEach(tier => {
                    addPaymentTier(tier.amount, tier.tag, tier.description);
                });
            } else {
                addPaymentTier(); // Add default empty tier
            }
        }

        // Auto-calculate end date when start date or duration changes
        document.addEventListener('change', function(e) {
            if (e.target.id === 'startDate' || e.target.id === 'durationMonths') {
                const startDate = document.getElementById('startDate').value;
                const duration = document.getElementById('durationMonths').value;
                
                if (startDate && duration) {
                    const start = new Date(startDate);
                    const end = new Date(start);
                    end.setMonth(end.getMonth() + parseInt(duration));
                    
                    document.getElementById('endDate').value = end.toISOString().split('T')[0];
                }
            }
        });

        // Utility functions
        function escapeHtml(text) {
            if (!text) return '';
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

        // Recalculate all values
        async function recalculateAllValues() {
            const btn = document.getElementById('recalculateBtn');
            const originalText = btn.innerHTML;
            
            if (!confirm('This will recalculate ALL equb values, pool amounts, and member data based on current database information.\n\nThis process may take a few seconds. Continue?')) {
                return;
            }
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Recalculating...';
            
            try {
                const response = await fetch('api/equb-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        action: 'recalculate_all_values'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', `✅ Recalculation completed successfully!\n\n${data.message}\n\nUpdated: ${data.data.updated_equbs} equb terms, ${data.data.updated_members} members, ${data.data.updated_positions} positions`);
                    
                    // Refresh the data to show updated values
                    await loadEqubData();
                } else {
                    showAlert('error', `❌ Recalculation failed: ${data.message}`);
                }
            } catch (error) {
                console.error('Error during recalculation:', error);
                showAlert('error', '❌ Network error during recalculation. Please try again.');
            } finally {
                // Re-enable button
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
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