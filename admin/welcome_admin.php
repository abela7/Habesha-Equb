<?php
/**
 * HabeshaEqub - Admin Dashboard
 * Main dashboard page for administrators with EXACT ORIGINAL DESIGN
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get dashboard statistics
try {
    // Members statistics
    $members_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_members,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_members,
            COUNT(CASE WHEN is_approved = 1 THEN 1 END) as approved_members,
            COUNT(CASE WHEN is_approved = 0 THEN 1 END) as pending_members
        FROM members
    ")->fetch();
    
    // Financial statistics  
    $financial_stats = $pdo->query("
        SELECT 
            COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_collected,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments
        FROM payments
    ")->fetch();
    
    // Payout statistics
    $payout_stats = $pdo->query("
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payouts,
            COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_payouts
        FROM payouts
    ")->fetch();
    
    // Recent members (last 5)
    $recent_members = $pdo->query("
        SELECT full_name, email, created_at, is_approved 
        FROM members 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Recent payments (last 5)
    $recent_payments = $pdo->query("
        SELECT p.amount, p.created_at, m.full_name 
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        WHERE p.status = 'completed'
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values
    $members_stats = ['total_members' => 0, 'active_members' => 0, 'approved_members' => 0, 'pending_members' => 0];
    $financial_stats = ['total_collected' => 0, 'completed_payments' => 0, 'pending_payments' => 0];
    $payout_stats = ['completed_payouts' => 0, 'scheduled_payouts' => 0];
    $recent_members = [];
    $recent_payments = [];
}

$total_members = $members_stats['total_members'];
$active_members = $members_stats['active_members'];
$completed_payouts = $payout_stats['completed_payouts'];
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
    
    <style>
        /* === DASHBOARD PAGE DESIGN - EXACT COPY FROM MEMBERS === */
        
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

        /* Statistics Dashboard */
        .stats-dashboard {
            margin-bottom: 40px;
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
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <?php echo str_replace('{username}', htmlspecialchars($admin_username), t('admin_dashboard.welcome_back')); ?>
                    </h1>
                    <p class="page-subtitle"><?php echo t('admin_dashboard.welcome_subtitle'); ?></p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row stats-dashboard">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon total-members">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <span class="stat-trend"><?php echo t('admin_dashboard.total_members'); ?></span>
                        </div>
                        <h3 class="stat-number"><?php echo $total_members; ?></h3>
                        <p class="stat-label"><?php echo t('admin_dashboard.total_members'); ?></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon total-collected">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"/>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </div>
                            <span class="stat-trend"><?php echo t('admin_dashboard.total_collected'); ?></span>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($financial_stats['total_collected'], 0); ?></h3>
                        <p class="stat-label"><?php echo t('admin_dashboard.total_collected'); ?></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon completed-payouts">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M16 12l-4-4-4 4"/>
                                    <path d="M12 16V8"/>
                                </svg>
                            </div>
                            <span class="stat-trend"><?php echo t('admin_dashboard.completed_payouts'); ?></span>
                        </div>
                        <h3 class="stat-number"><?php echo $completed_payouts; ?></h3>
                        <p class="stat-label"><?php echo t('admin_dashboard.completed_payouts'); ?></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon active-members">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 1v6M12 17v6M4.22 4.22l4.24 4.24M15.54 15.54l4.24 4.24M1 12h6M17 12h6M4.22 19.78l4.24-4.24M15.54 8.46l4.24-4.24"/>
                                </svg>
                            </div>
                            <span class="stat-trend"><?php echo t('admin_dashboard.pending_approvals'); ?></span>
                        </div>
                        <h3 class="stat-number"><?php echo $members_stats['pending_members']; ?></h3>
                        <p class="stat-label"><?php echo t('admin_dashboard.pending_approvals'); ?></p>
                    </div>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 HabeshaEqub Admin Dashboard loaded successfully!');
    });
    </script>
</body>
</html>