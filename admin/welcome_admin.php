<?php
/**
 * HabeshaEqub - Modern Admin Dashboard
 * Professional admin dashboard with real-time analytics and reports
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once '../includes/enhanced_equb_calculator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get comprehensive dashboard statistics
try {
    // EQUB Statistics
    $equb_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_equbs,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_equbs,
            COUNT(CASE WHEN status = 'planning' THEN 1 END) as planning_equbs,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_equbs,
            COALESCE(SUM(
                CASE WHEN status = 'active' THEN 
                    (SELECT COALESCE(SUM(
                        CASE 
                            WHEN m.membership_type = 'joint' THEN COALESCE(m.individual_contribution, m.monthly_payment)
                            ELSE m.monthly_payment
                        END
                    ), 0)
                    FROM members m 
                    WHERE m.equb_settings_id = es.id AND m.is_active = 1)
                ELSE 0 END
            ), 0) as total_pool_value
        FROM equb_settings es
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Member statistics
    $position_stats = $pdo->query("
        SELECT 
            COUNT(DISTINCT m.id) as total_individual_members,
            COUNT(DISTINCT CASE WHEN m.membership_type = 'joint' THEN m.joint_group_id END) as total_joint_groups,
            COUNT(DISTINCT 
                CASE 
                    WHEN m.membership_type = 'joint' THEN CONCAT('joint_', COALESCE(m.joint_group_id, m.id))
                    ELSE CONCAT('individual_', m.id)
                END
            ) as total_positions,
            COUNT(CASE WHEN m.is_active = 1 THEN 1 END) as active_members,
            COUNT(CASE WHEN m.is_approved = 0 THEN 1 END) as pending_approvals
        FROM members m
        WHERE m.equb_settings_id IN (SELECT id FROM equb_settings WHERE status = 'active')
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Financial Statistics
    $financial_stats = $pdo->query("
        SELECT 
            COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) as total_collected,
            COALESCE(AVG(CASE WHEN p.status = 'paid' THEN p.amount END), 0) as avg_payment_amount,
            COUNT(CASE WHEN p.status = 'paid' THEN 1 END) as completed_payments,
            COUNT(CASE WHEN p.status = 'pending' THEN 1 END) as pending_payments,
            COUNT(CASE WHEN p.status = 'late' THEN 1 END) as late_payments,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as total_distributed,
            COUNT(CASE WHEN po.status = 'completed' THEN 1 END) as completed_payouts,
            COUNT(CASE WHEN po.status = 'scheduled' THEN 1 END) as scheduled_payouts
        FROM payments p
        LEFT JOIN payouts po ON po.member_id = p.member_id
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Performance Metrics
    $performance_stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM admins WHERE is_active = 1) as active_admins,
            (SELECT COUNT(*) FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)) as recent_notifications,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOURS)) as daily_logins
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Recent Members
    $recent_members = $pdo->query("
        SELECT m.*, es.equb_name
        FROM members m
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        ORDER BY m.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent Payments - FIXED: Use member's database ID, not member code
    $recent_payments = $pdo->query("
        SELECT 
            p.amount, 
            p.created_at, 
            p.status, 
            CONCAT(m.first_name, ' ', m.last_name) as full_name,
            es.equb_name
        FROM payments p 
        JOIN members m ON p.member_id = m.member_id
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Chart Data - Monthly Collections (Last 6 months)
    $monthly_data = $pdo->query("
        SELECT 
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            DATE_FORMAT(payment_date, '%b %Y') as month_label,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as collected,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as payment_count
        FROM payments
        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Payment Status Distribution
    $status_data = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count,
            COALESCE(SUM(amount), 0) as total_amount
        FROM payments
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Financial Health Indicator
    $collection_rate = ($financial_stats['total_collected'] > 0 && $equb_stats['total_pool_value'] > 0) 
        ? ($financial_stats['total_collected'] / $equb_stats['total_pool_value']) * 100 
        : 0;
        
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values
    $equb_stats = ['total_equbs' => 0, 'active_equbs' => 0, 'planning_equbs' => 0, 'completed_equbs' => 0, 'total_pool_value' => 0];
    $position_stats = ['total_individual_members' => 0, 'total_joint_groups' => 0, 'total_positions' => 0, 'active_members' => 0, 'pending_approvals' => 0];
    $financial_stats = ['total_collected' => 0, 'avg_payment_amount' => 0, 'completed_payments' => 0, 'pending_payments' => 0, 'late_payments' => 0, 'total_distributed' => 0, 'completed_payouts' => 0, 'scheduled_payouts' => 0];
    $performance_stats = ['active_admins' => 0, 'recent_notifications' => 0, 'daily_logins' => 0];
    $recent_members = [];
    $recent_payments = [];
    $monthly_data = [];
    $status_data = [];
    $collection_rate = 0;
}

// Prepare chart data
$chart_labels = array_column($monthly_data, 'month_label');
$chart_collected = array_column($monthly_data, 'collected');
$status_labels = array_column($status_data, 'status');
$status_counts = array_column($status_data, 'count');
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('navigation.dashboard'); ?> - HabeshaEqub Admin</title>
    
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --color-cream: #F1ECE2;
            --color-dark-purple: #4D4052;
            --color-deep-purple: #301934;
            --color-gold: #DAA520;
            --color-light-gold: #CDAF56;
            --color-brown: #5D4225;
            --border-color: rgba(77, 64, 82, 0.15);
            --text-primary: #301934;
            --text-secondary: #6B7280;
        }
        
        body {
            background: #F9FAFB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .admin-container {
            min-height: 100vh;
        }
        
        .main-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #FFFFFF 0%, var(--color-cream) 100%);
            border-radius: 20px;
            padding: 35px 40px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.08);
        }
        
        .page-title-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-deep-purple);
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .page-title-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            box-shadow: 0 4px 16px rgba(218, 165, 32, 0.3);
        }
        
        .page-subtitle {
            font-size: 16px;
            color: var(--text-secondary);
            margin: 0 0 12px 0;
        }
        
        .welcome-time {
            font-size: 14px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 12px rgba(48, 25, 67, 0.06);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(48, 25, 67, 0.12);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .stat-icon.primary { background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); }
        .stat-icon.success { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .stat-icon.warning { background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%); }
        .stat-icon.danger { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); }
        .stat-icon.info { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
        
        .stat-trend {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-deep-purple);
            margin: 0 0 6px 0;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }
        
        .stat-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            font-size: 12px;
        }
        
        .indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .indicator-dot.success { background: #10B981; }
        .indicator-dot.warning { background: #F59E0B; }
        .indicator-dot.danger { background: #EF4444; }
        
        /* Charts Section */
        .charts-section {
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 12px rgba(48, 25, 67, 0.06);
            height: 100%;
            min-height: 400px;
        }
        
        .chart-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-deep-purple);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }
        
        /* Quick Actions */
        .quick-actions-section {
            margin-bottom: 30px;
        }
        
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }
        
        .action-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(48, 25, 67, 0.06);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(48, 25, 67, 0.12);
            text-decoration: none;
            color: inherit;
        }
        
        .action-card:hover::before {
            transform: scaleX(1);
        }
        
        .action-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--color-deep-purple) 0%, var(--color-dark-purple) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin-bottom: 12px;
        }
        
        .action-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--color-deep-purple);
            margin-bottom: 6px;
        }
        
        .action-desc {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
            line-height: 1.4;
        }
        
        /* Activity Cards */
        .activity-section {
            margin-bottom: 30px;
        }
        
        .activity-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 12px rgba(48, 25, 67, 0.06);
            height: 100%;
        }
        
        .activity-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-deep-purple);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .activity-item {
            padding: 14px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-text {
            flex: 1;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 14px;
        }
        
        .activity-time {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 8px;
        }
        
        .status-pending {
            background: rgba(234, 179, 8, 0.1);
            color: #B45309;
        }
        
        .status-approved {
            background: rgba(34, 197, 94, 0.1);
            color: #059669;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            
            .page-header {
                padding: 24px 20px;
            }
            
            .page-title-section h1 {
                font-size: 24px;
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Navigation -->
        <?php include 'includes/navigation.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-section">
                    <h1>
                        <div class="page-title-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <?php echo t('admin_dashboard.welcome_back'); ?>, <?php echo htmlspecialchars($admin_username); ?>!
                    </h1>
                    <p class="page-subtitle"><?php echo t('admin_dashboard.system_overview'); ?></p>
                    <div class="welcome-time">
                        <i class="fas fa-clock"></i>
                        <span><?php echo date('l, F j, Y - g:i A'); ?></span>
                        <span style="margin: 0 8px;">|</span>
                        <span><?php echo $equb_stats['active_equbs']; ?> <?php echo t('admin_dashboard.active_equbs'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon primary">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                    </div>
                    <h3 class="stat-number">£<?php echo number_format($equb_stats['total_pool_value'], 0); ?></h3>
                    <p class="stat-label"><?php echo t('admin_dashboard.total_pool_value'); ?></p>
                    <div class="stat-indicator">
                        <div class="indicator-dot success"></div>
                        <span><?php echo $equb_stats['active_equbs']; ?> <?php echo t('admin_dashboard.active_equbs'); ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3 class="stat-number"><?php echo number_format($position_stats['total_positions'] ?? 0); ?></h3>
                    <p class="stat-label"><?php echo t('admin_dashboard.total_positions'); ?></p>
                    <div class="stat-indicator">
                        <div class="indicator-dot success"></div>
                        <span><?php echo number_format($position_stats['active_members'] ?? 0); ?> <?php echo t('admin_dashboard.active_members'); ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon success">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                    <h3 class="stat-number">£<?php echo number_format($financial_stats['total_collected'] ?? 0, 0); ?></h3>
                    <p class="stat-label"><?php echo t('admin_dashboard.total_collected'); ?></p>
                    <div class="stat-indicator">
                        <div class="indicator-dot <?php echo ($financial_stats['late_payments'] ?? 0) > 0 ? 'warning' : 'success'; ?>"></div>
                        <span><?php echo number_format($financial_stats['completed_payments'] ?? 0); ?> <?php echo t('admin_dashboard.completed_payments'); ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon warning">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <h3 class="stat-number"><?php echo number_format(($position_stats['pending_approvals'] ?? 0) + ($financial_stats['pending_payments'] ?? 0)); ?></h3>
                    <p class="stat-label"><?php echo t('admin_dashboard.pending_actions'); ?></p>
                    <div class="stat-indicator">
                        <div class="indicator-dot <?php echo (($position_stats['pending_approvals'] ?? 0) + ($financial_stats['pending_payments'] ?? 0)) > 5 ? 'warning' : 'success'; ?>"></div>
                        <span><?php echo t('admin_dashboard.requires_attention'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="row charts-section">
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-chart-line text-primary"></i>
                            <?php echo t('admin_dashboard.monthly_collections'); ?>
                        </h3>
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-chart-pie text-success"></i>
                            <?php echo t('admin_dashboard.payment_status'); ?>
                        </h3>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h3 class="mb-3" style="color: var(--color-deep-purple); font-weight: 600;">
                    <i class="fas fa-bolt text-warning"></i>
                    <?php echo t('admin_dashboard.quick_actions'); ?>
                </h3>
                <div class="quick-actions-grid">
                    <a href="members.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.members_management'); ?></div>
                        <p class="action-desc"><?php echo t('admin_dashboard.members_description'); ?></p>
                    </a>
                    
                    <a href="payments.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.payments_management'); ?></div>
                        <p class="action-desc"><?php echo t('admin_dashboard.payments_description'); ?></p>
                    </a>
                    
                    <a href="payouts.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.payouts_management'); ?></div>
                        <p class="action-desc"><?php echo t('admin_dashboard.payouts_description'); ?></p>
                    </a>
                    
                    <a href="notifications.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.notifications'); ?></div>
                        <p class="action-desc"><?php echo t('admin_dashboard.send_notifications'); ?></p>
                    </a>
                    
                    <a href="reports.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.reports_analytics'); ?></div>
                        <p class="action-desc"><?php echo t('admin_dashboard.reports_description'); ?></p>
                    </a>
                    
                    <a href="equb-management.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.equb_management'); ?></div>
                        <p class="action-desc"><?php echo t('admin_dashboard.equb_description'); ?></p>
                    </a>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="row activity-section">
                <div class="col-lg-6 mb-4">
                    <div class="activity-card">
                        <h3>
                            <i class="fas fa-user-plus text-primary"></i>
                            <?php echo t('admin_dashboard.recent_applications'); ?>
                        </h3>
                        
                        <?php if (empty($recent_members)): ?>
                            <div class="activity-item">
                                <div class="activity-text"><?php echo t('admin_dashboard.no_recent_applications'); ?></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_members as $member): ?>
                                <div class="activity-item">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')); ?></strong>
                                        <?php echo t('admin_dashboard.applied_to_join'); ?>
                                    </div>
                                    <div>
                                        <span class="status-badge <?php echo ($member['is_approved'] ?? 0) ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo ($member['is_approved'] ?? 0) ? t('admin_dashboard.approved') : t('admin_dashboard.pending'); ?>
                                        </span>
                                        <div class="activity-time"><?php echo date('M j', strtotime($member['created_at'] ?? 'now')); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="activity-card">
                        <h3>
                            <i class="fas fa-credit-card text-success"></i>
                            <?php echo t('admin_dashboard.recent_payments'); ?>
                        </h3>
                        
                        <?php if (empty($recent_payments)): ?>
                            <div class="activity-item">
                                <div class="activity-text"><?php echo t('admin_dashboard.no_recent_payments'); ?></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_payments as $payment): ?>
                                <div class="activity-item">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($payment['full_name'] ?? 'N/A'); ?></strong>
                                        <?php echo t('admin_dashboard.paid'); ?> £<?php echo number_format($payment['amount'] ?? 0, 0); ?>
                                    </div>
                                    <div class="activity-time"><?php echo date('M j', strtotime($payment['created_at'] ?? 'now')); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Charts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Collections Line Chart
        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chart_labels); ?>,
                    datasets: [{
                        label: '<?php echo t('admin_dashboard.collected'); ?>',
                        data: <?php echo json_encode($chart_collected); ?>,
                        borderColor: '#6366F1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '£' + context.parsed.y.toLocaleString('en-GB');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '£' + value.toLocaleString('en-GB');
                                }
                            },
                            grid: {
                                color: '#F3F4F6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Payment Status Pie Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($status_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($status_counts); ?>,
                        backgroundColor: [
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
                            '#6366F1'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
