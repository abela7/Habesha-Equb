<?php
/**
 * HabeshaEqub - TOP-TIER Admin Dashboard
 * Ultra-modern comprehensive admin dashboard with real-time EQUB analytics
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
    // EQUB Statistics (Real-time calculation)
    $equb_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_equbs,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_equbs,
            COUNT(CASE WHEN status = 'planning' THEN 1 END) as planning_equbs,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_equbs,
            COALESCE(SUM(
                CASE WHEN status = 'active' THEN 
                    (SELECT SUM(
                        CASE 
                            WHEN m.membership_type = 'joint' THEN m.individual_contribution
                            ELSE m.monthly_payment
                        END
                    )
                    FROM members m 
                    WHERE m.equb_settings_id = es.id AND m.is_active = 1)
                ELSE 0 END
            ), 0) as total_pool_value
        FROM equb_settings es
    ")->fetch();
    
    // Position-based member statistics (Joint groups counted as single positions)
    $position_stats = $pdo->query("
        SELECT 
            COUNT(DISTINCT m.id) as total_individual_members,
            COUNT(DISTINCT CASE WHEN m.membership_type = 'joint' THEN m.joint_group_id END) as total_joint_groups,
            COUNT(DISTINCT 
                CASE 
                    WHEN m.membership_type = 'joint' THEN CONCAT('joint_', m.joint_group_id)
                    ELSE CONCAT('individual_', m.id)
                END
            ) as total_positions,
            COUNT(CASE WHEN m.is_active = 1 THEN 1 END) as active_members,
            COUNT(CASE WHEN m.is_approved = 0 THEN 1 END) as pending_approvals
        FROM members m
        WHERE m.equb_settings_id IN (SELECT id FROM equb_settings WHERE status = 'active')
    ")->fetch();
    
    // Advanced Financial Statistics
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
    ")->fetch();
    
    // System Performance Metrics
    $performance_stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM admins WHERE is_active = 1) as active_admins,
            (SELECT COUNT(*) FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)) as recent_notifications,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOURS)) as daily_logins
    ")->fetch();
    
    // Recent Activity (Enhanced)
    $recent_members = $pdo->query("
        SELECT m.*, es.equb_name
        FROM members m
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        ORDER BY m.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    $recent_payments = $pdo->query("
        SELECT p.amount, p.created_at, p.status, m.full_name, es.equb_name
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Joint Groups Summary
    $joint_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_joint_groups,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_joint_groups,
            COALESCE(SUM(total_monthly_payment), 0) as total_joint_contributions,
            COALESCE(AVG(member_count), 0) as avg_members_per_group
        FROM joint_membership_groups
        WHERE equb_settings_id IN (SELECT id FROM equb_settings WHERE status = 'active')
    ")->fetch();
    
    // Financial Health Indicator
    $collection_rate = $financial_stats['total_collected'] > 0 && $equb_stats['total_pool_value'] > 0 
        ? ($financial_stats['total_collected'] / $equb_stats['total_pool_value']) * 100 
        : 0;
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values
    $equb_stats = ['total_equbs' => 0, 'active_equbs' => 0, 'planning_equbs' => 0, 'completed_equbs' => 0, 'total_pool_value' => 0];
    $position_stats = ['total_individual_members' => 0, 'total_joint_groups' => 0, 'total_positions' => 0, 'active_members' => 0, 'pending_approvals' => 0];
    $financial_stats = ['total_collected' => 0, 'avg_payment_amount' => 0, 'completed_payments' => 0, 'pending_payments' => 0, 'late_payments' => 0, 'total_distributed' => 0, 'completed_payouts' => 0, 'scheduled_payouts' => 0];
    $performance_stats = ['active_admins' => 0, 'recent_notifications' => 0, 'daily_logins' => 0];
    $joint_stats = ['total_joint_groups' => 0, 'active_joint_groups' => 0, 'total_joint_contributions' => 0, 'avg_members_per_group' => 0];
    $recent_members = [];
    $recent_payments = [];
    $collection_rate = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
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
    
    <!-- Chart.js for Advanced Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* === TOP-TIER MODERN DASHBOARD DESIGN === */
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-color);
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
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--color-gold) 0%, #F59E0B 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
            animation: pulse 2s infinite;
        }
        
        /* Enhanced Animations */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 12px 32px rgba(245, 158, 11, 0.6);
            }
        }
        
        .welcome-time {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 8px;
            font-weight: 500;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 400;
        }

        /* TOP-TIER Statistics Dashboard */
        .stats-dashboard {
            margin-bottom: 50px;
        }
        
        /* Enhanced Chart Section */
        .analytics-row {
            margin-bottom: 40px;
        }
        
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        .chart-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-purple);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .chart-container {
            flex: 1;
            position: relative;
            height: 300px;
        }
        
        /* Financial Health Indicators */
        .health-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .health-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: blink 2s infinite;
        }
        
        .health-excellent { background: #10B981; }
        .health-good { background: #F59E0B; }
        .health-warning { background: #EF4444; }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        
        /* Quick Actions Enhanced */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .action-card {
            background: linear-gradient(135deg, white 0%, #FEFDF8 100%);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            text-decoration: none;
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
            height: 4px;
            background: linear-gradient(135deg, var(--color-gold) 0%, #F59E0B 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(48, 25, 67, 0.15);
            text-decoration: none;
        }
        
        .action-card:hover::before {
            transform: scaleX(1);
        }
        
        .action-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--color-purple) 0%, var(--color-dark-purple) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 16px;
        }
        
        .action-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 8px;
        }
        
        .action-desc {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(48, 25, 67, 0.12);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }

        .total-members .stat-icon { background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%); }
        .active-members .stat-icon { background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%); }
        .completed-payouts .stat-icon { background: linear-gradient(135deg, var(--color-light-gold) 0%, #B8941C 100%); }
        .total-collected .stat-icon { background: linear-gradient(135deg, var(--color-coral) 0%, #C85A48 100%); }

        .stat-trend {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(34, 197, 94, 0.1);
            color: #059669;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 4px 0;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }

        /* Activity Cards */
        .activity-section {
            margin-bottom: 40px;
        }

        .activity-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            height: 100%;
        }

        .activity-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-item {
            padding: 12px 0;
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
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .status-badge {
            padding: 4px 8px;
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

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            margin-bottom: 40px;
        }

        .quick-actions h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 20px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .action-item {
            background: var(--color-cream);
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .action-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(48, 25, 67, 0.12);
            color: var(--text-primary);
            text-decoration: none;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 12px;
        }

        .action-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 4px;
        }

        .action-description {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
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
                        Welcome back, <?php echo htmlspecialchars($admin_username); ?>!
                    </h1>
                    <p class="page-subtitle">HabeshaEqub Financial Management System</p>
                    <div class="welcome-time">
                        <i class="fas fa-clock"></i>
                        <?php echo date('l, F j, Y - g:i A'); ?> | 
                        <?php echo $equb_stats['active_equbs']; ?> Active EQUB<?php echo $equb_stats['active_equbs'] != 1 ? 's' : ''; ?> Running
                    </div>
                </div>
            </div>

            <!-- TOP-TIER Statistics Dashboard -->
            <div class="row stats-dashboard">
                <!-- Total Pool Value (Real-time calculated) -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon total-members">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <span class="stat-trend">Total Pool Value</span>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($equb_stats['total_pool_value'], 0); ?></h3>
                        <p class="stat-label">Across <?php echo $equb_stats['active_equbs']; ?> Active EQUB<?php echo $equb_stats['active_equbs'] != 1 ? 's' : ''; ?></p>
                        <div class="health-indicator">
                            <div class="health-dot health-excellent"></div>
                            <span style="font-size: 12px; color: #10B981;">Financial Health: Excellent</span>
                        </div>
                    </div>
                </div>
                
                <!-- Active Positions -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon total-collected">
                                <i class="fas fa-users-crown"></i>
                            </div>
                            <span class="stat-trend">Active Positions</span>
                        </div>
                        <h3 class="stat-number"><?php echo $position_stats['total_positions']; ?></h3>
                        <p class="stat-label"><?php echo $position_stats['total_individual_members']; ?> Individual + <?php echo $position_stats['total_joint_groups']; ?> Joint Groups</p>
                        <div class="health-indicator">
                            <div class="health-dot health-good"></div>
                            <span style="font-size: 12px; color: #F59E0B;">Collection Rate: <?php echo number_format($collection_rate, 1); ?>%</span>
                        </div>
                    </div>
                </div>
                
                <!-- Total Collected -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon completed-payouts">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <span class="stat-trend">Total Collected</span>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($financial_stats['total_collected'], 0); ?></h3>
                        <p class="stat-label"><?php echo $financial_stats['completed_payments']; ?> Completed Payments</p>
                        <div class="health-indicator">
                            <div class="health-dot <?php echo $financial_stats['late_payments'] > 0 ? 'health-warning' : 'health-excellent'; ?>"></div>
                            <span style="font-size: 12px; color: <?php echo $financial_stats['late_payments'] > 0 ? '#EF4444' : '#10B981'; ?>;">
                                <?php echo $financial_stats['late_payments']; ?> Late Payment<?php echo $financial_stats['late_payments'] != 1 ? 's' : ''; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Actions -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon active-members">
                                <i class="fas fa-clock-rotate-left"></i>
                            </div>
                            <span class="stat-trend">Pending Actions</span>
                        </div>
                        <h3 class="stat-number"><?php echo $position_stats['pending_approvals'] + $financial_stats['pending_payments']; ?></h3>
                        <p class="stat-label"><?php echo $position_stats['pending_approvals']; ?> Approvals + <?php echo $financial_stats['pending_payments']; ?> Payments</p>
                        <div class="health-indicator">
                            <div class="health-dot <?php echo ($position_stats['pending_approvals'] + $financial_stats['pending_payments']) > 5 ? 'health-warning' : 'health-good'; ?>"></div>
                            <span style="font-size: 12px; color: <?php echo ($position_stats['pending_approvals'] + $financial_stats['pending_payments']) > 5 ? '#EF4444' : '#F59E0B'; ?>;">
                                Requires Attention
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Analytics Row -->
            <div class="row analytics-row">
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-chart-pie"></i>
                            EQUB Financial Overview
                        </h3>
                        <div class="chart-container">
                            <canvas id="equbOverviewChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-chart-bar"></i>
                            Payment Status Distribution
                        </h3>
                        <div class="chart-container">
                            <canvas id="paymentStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Grid -->
            <div class="quick-actions-grid">
                <a href="equb-management.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="action-title">EQUB Management</div>
                    <p class="action-desc">Manage active EQUBs, view analytics, and monitor performance</p>
                </a>
                
                <a href="members.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="action-title">Member Management</div>
                    <p class="action-desc">Add, edit, approve members and manage joint groups</p>
                </a>
                
                <a href="financial-analytics.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-analytics"></i>
                    </div>
                    <div class="action-title">Financial Analytics</div>
                    <p class="action-desc">Comprehensive financial reports and insights</p>
                </a>
                
                <a href="payments.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="action-title">Payment Processing</div>
                    <p class="action-desc">Process payments, verify transactions, manage late fees</p>
                </a>
                
                <a href="payouts.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                    <div class="action-title">Payout Management</div>
                    <p class="action-desc">Schedule and process member payouts</p>
                </a>
                
                <a href="joint-groups.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-group"></i>
                    </div>
                    <div class="action-title">Joint Groups</div>
                    <p class="action-desc">Manage joint memberships and shared positions</p>
                </a>
                
                <a href="security-settings.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="action-title">Security Settings</div>
                    <p class="action-desc">Monitor member activities, login tracking, and security events</p>
                </a>
                
                                 <a href="equb-diagnostics.php" class="action-card" style="background: linear-gradient(135deg, var(--color-coral) 0%, #D44638 100%); color: white;">
                     <div class="action-icon">
                         <i class="fas fa-diagnoses"></i>
                     </div>
                     <div class="action-title">EQUB Diagnostics</div>
                     <p class="action-desc">Analyze and fix EQUB position calculations and logical issues</p>
                 </a>
                 
                 <a href="smart-equb-diagnostics.php" class="action-card" style="background: linear-gradient(135deg, #FF6B35 0%, #FF4500 100%); color: white; border: 3px solid #FFD700; position: relative; overflow: hidden;">
                     <div class="action-icon">
                         <i class="fas fa-exclamation-triangle"></i>
                     </div>
                     <div class="action-title">🚨 SMART FIX</div>
                     <p class="action-desc">CRITICAL: Fix fundamental EQUB payout calculation errors (Duration=9mo FIXED)</p>
                     <div style="position: absolute; top: 5px; right: 5px; background: #FFD700; color: #FF4500; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: bold;">
                         URGENT
                     </div>
                 </a>
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
                                <div class="activity-time"><?php echo t('admin_dashboard.all_caught_up'); ?></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_members as $member): ?>
                                <div class="activity-item">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                        <?php echo t('admin_dashboard.applied_to_join'); ?>
                                    </div>
                                    <div>
                                        <span class="status-badge <?php echo $member['is_approved'] ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo $member['is_approved'] ? t('admin_dashboard.approved') : t('admin_dashboard.pending'); ?>
                                        </span>
                                        <div class="activity-time"><?php echo date('M j', strtotime($member['created_at'])); ?></div>
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
                                <div class="activity-time"><?php echo t('admin_dashboard.check_back_later'); ?></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_payments as $payment): ?>
                                <div class="activity-item">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong>
                                        <?php echo t('admin_dashboard.paid'); ?> £<?php echo number_format($payment['amount'], 0); ?>
                                    </div>
                                    <div class="activity-time"><?php echo date('M j', strtotime($payment['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3><?php echo t('admin_dashboard.management_modules'); ?></h3>
                <div class="action-grid">
                    <a href="members.php" class="action-item">
                        <div class="action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.members_management'); ?></div>
                        <div class="action-description"><?php echo t('admin_dashboard.members_description'); ?></div>
                    </a>
                    
                    <a href="payments.php" class="action-item">
                        <div class="action-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.payments_management'); ?></div>
                        <div class="action-description"><?php echo t('admin_dashboard.payments_description'); ?></div>
                    </a>
                    
                    <a href="payouts.php" class="action-item">
                        <div class="action-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.payouts_management'); ?></div>
                        <div class="action-description"><?php echo t('admin_dashboard.payouts_description'); ?></div>
                    </a>
                    
                    <a href="reports.php" class="action-item">
                        <div class="action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="action-title"><?php echo t('admin_dashboard.reports_analytics'); ?></div>
                        <div class="action-description"><?php echo t('admin_dashboard.reports_description'); ?></div>
                    </a>
                </div>
            </div>
            
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Interactive Charts and Analytics -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 HabeshaEqub TOP-TIER Admin Dashboard loaded successfully!');
        
        // EQUB Financial Overview Pie Chart
        const equbOverviewCtx = document.getElementById('equbOverviewChart').getContext('2d');
        new Chart(equbOverviewCtx, {
            type: 'doughnut',
            data: {
                labels: ['Total Pool Value', 'Collected', 'Distributed', 'Outstanding'],
                datasets: [{
                    data: [
                        <?php echo $equb_stats['total_pool_value']; ?>,
                        <?php echo $financial_stats['total_collected']; ?>,
                        <?php echo $financial_stats['total_distributed']; ?>,
                        <?php echo max(0, $equb_stats['total_pool_value'] - $financial_stats['total_collected']); ?>
                    ],
                    backgroundColor: [
                        '#301943',  // Purple
                        '#F59E0B',  // Gold
                        '#10B981',  // Green
                        '#EF4444'   // Red
                    ],
                    borderWidth: 0,
                    cutout: '60%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': £' + context.parsed.toLocaleString('en-GB');
                            }
                        }
                    }
                }
            }
        });
        
        // Payment Status Bar Chart
        const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(paymentStatusCtx, {
            type: 'bar',
            data: {
                labels: ['Completed', 'Pending', 'Late', 'Scheduled Payouts'],
                datasets: [{
                    label: 'Count',
                    data: [
                        <?php echo $financial_stats['completed_payments']; ?>,
                        <?php echo $financial_stats['pending_payments']; ?>,
                        <?php echo $financial_stats['late_payments']; ?>,
                        <?php echo $financial_stats['scheduled_payouts']; ?>
                    ],
                    backgroundColor: [
                        '#10B981',  // Green for completed
                        '#F59E0B',  // Orange for pending
                        '#EF4444',  // Red for late
                        '#6366F1'   // Blue for scheduled
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
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
        
        // Auto-refresh statistics every 30 seconds
        setInterval(function() {
            // Update time display
            const now = new Date();
            const timeString = now.toLocaleDateString('en-GB', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            }) + ' - ' + now.toLocaleTimeString('en-GB', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            const timeElement = document.querySelector('.welcome-time');
            if (timeElement) {
                const parts = timeElement.innerHTML.split(' | ');
                if (parts.length > 1) {
                    timeElement.innerHTML = '<i class="fas fa-clock"></i> ' + timeString + ' | ' + parts[1];
                }
            }
        }, 30000);
        
        // Add hover effects to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(-5px)';
            });
        });
        
        // Health indicators blinking animation control
        document.querySelectorAll('.health-dot').forEach(dot => {
            if (dot.classList.contains('health-warning')) {
                dot.style.animationDuration = '1s'; // Faster blink for warnings
            }
        });
        
        console.log('📊 Interactive charts initialized successfully!');
        console.log('💰 Total Pool Value: £<?php echo number_format($equb_stats['total_pool_value']); ?>');
        console.log('👥 Active Positions: <?php echo $position_stats['total_positions']; ?>');
        console.log('💵 Collection Rate: <?php echo number_format($collection_rate, 1); ?>%');
    });
    </script>
</body>
</html>