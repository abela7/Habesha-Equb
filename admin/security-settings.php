<?php
/**
 * HabeshaEqub - TOP-TIER Security Settings Dashboard
 * Comprehensive security monitoring and member activity tracking system
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get comprehensive security statistics
try {
    // Debug: Check database connection and basic counts
    error_log("🔒 Security Settings: Starting database queries...");
    
    // SIMPLE MEMBER LOGIN TRACKING
    $member_activities = $pdo->query("
        SELECT 
            m.id,
            m.member_id,
            m.first_name,
            m.last_name,
            m.email,
            m.last_login,
            m.created_at,
            m.is_active,
            m.is_approved,
            es.equb_name
        FROM members m
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        ORDER BY m.last_login DESC, m.created_at DESC
        LIMIT 50
    ")->fetchAll();
    
    // SIMPLE SECURITY STATISTICS
    $total_members = $pdo->query("SELECT COUNT(*) as count FROM members")->fetch()['count'];
    $members_with_login = $pdo->query("SELECT COUNT(*) as count FROM members WHERE last_login IS NOT NULL")->fetch()['count'];
    $members_never_logged = $pdo->query("SELECT COUNT(*) as count FROM members WHERE last_login IS NULL")->fetch()['count'];
    $total_otps = $pdo->query("SELECT COUNT(*) as count FROM user_otps")->fetch()['count'];
    $total_devices = $pdo->query("SELECT COUNT(*) as count FROM device_tracking")->fetch()['count'];
    
    // Recent active members (24 hours)
    $active_24h = $pdo->query("
        SELECT COUNT(*) as count 
        FROM members 
        WHERE last_login > DATE_ADD(NOW(), INTERVAL -24 HOUR)
    ")->fetch()['count'];
    
    // Recent OTP activities
    $otp_24h = $pdo->query("
        SELECT COUNT(*) as count 
        FROM user_otps 
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -24 HOUR)
    ")->fetch()['count'];
    
    $security_stats = [
        'total_members' => $total_members,
        'members_with_login' => $members_with_login,
        'never_logged_in' => $members_never_logged,
        'active_24h' => $active_24h,
        'total_otps' => $total_otps,
        'otp_requests_24h' => $otp_24h,
        'total_devices' => $total_devices,
        'successful_logins_24h' => 0,
        'failed_attempts_24h' => 0,
        'new_devices_7d' => 0,
        'unapproved_devices' => 0,
        'active_7d' => $members_with_login
    ];
    
    // SIMPLE OTP ACTIVITIES
    $recent_otp_activities = $pdo->query("
        SELECT 
            uo.email,
            uo.otp_type,
            uo.created_at,
            uo.is_used,
            uo.attempt_count,
            m.first_name,
            m.last_name,
            m.member_id
        FROM user_otps uo
        LEFT JOIN members m ON uo.email = m.email
        ORDER BY uo.created_at DESC
        LIMIT 30
    ")->fetchAll();
    
    // SIMPLE DEVICE TRACKING
    $device_activities = $pdo->query("
        SELECT 
            dt.email,
            dt.user_agent,
            dt.ip_address,
            dt.is_approved,
            dt.created_at,
            dt.last_seen,
            m.first_name,
            m.last_name,
            m.member_id
        FROM device_tracking dt
        LEFT JOIN members m ON dt.email = m.email
        ORDER BY dt.created_at DESC
        LIMIT 20
    ")->fetchAll();
    
    // SIMPLE ADMIN ACTIVITIES
    $admin_activities = $pdo->query("
        SELECT 
            username,
            last_login,
            is_active,
            created_at
        FROM admins
        WHERE is_active = 1
        ORDER BY last_login DESC
    ")->fetchAll();
    
    error_log("🔒 Security data loaded: {$total_members} members, {$total_otps} OTPs, {$total_devices} devices");
    
} catch (Exception $e) {
    error_log("🚨 Security Settings CRITICAL ERROR: " . $e->getMessage());
    error_log("🚨 SQL Error Details: " . $e->getTraceAsString());
    
    // Set default values but with error info
    $database_error = $e->getMessage();
    $member_activities = [];
    $security_stats = [
        'active_24h' => 0, 'active_7d' => 0, 'never_logged_in' => 0,
        'otp_requests_24h' => 0, 'successful_logins_24h' => 0, 'failed_attempts_24h' => 0,
        'new_devices_7d' => 0, 'unapproved_devices' => 0, 'total_members' => 0,
        'total_otps' => 0, 'total_devices' => 0
    ];
    $recent_otp_activities = [];
    $device_activities = [];
    $admin_activities = [];
}

// Debug Information
$debug_mode = true; // Set to false in production
$debug_info = [
    'member_count' => count($member_activities),
    'otp_count' => count($recent_otp_activities), 
    'device_count' => count($device_activities),
    'admin_count' => count($admin_activities),
    'has_error' => isset($database_error),
    'error_message' => $database_error ?? null,
    'stats' => $security_stats
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - HabeshaEqub Admin</title>
    
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
    
    <!-- Chart.js for Security Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* === TOP-TIER SECURITY DASHBOARD DESIGN === */
        
        /* Enhanced Page Header */
        .security-header {
            background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .security-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
            z-index: 1;
        }
        
        .security-header .content {
            position: relative;
            z-index: 2;
        }
        
        .security-header h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .security-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .security-subtitle {
            font-size: 18px;
            opacity: 0.9;
            margin: 0;
        }
        
        .security-time {
            font-size: 14px;
            opacity: 0.7;
            margin-top: 8px;
        }
        
        /* Security Statistics Cards */
        .security-stats {
            margin-bottom: 40px;
        }
        
        .security-stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.08);
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .security-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(220, 38, 38, 0.15);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            margin-bottom: 16px;
        }
        
        .stat-icon.success { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
        .stat-icon.warning { background: linear-gradient(135deg, #D97706 0%, #B45309 100%); }
        .stat-icon.danger { background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%); }
        .stat-icon.info { background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); }
        
        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--color-purple);
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }
        
        /* Activity Tables */
        .activity-section {
            margin-bottom: 40px;
        }
        
        .activity-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
        }
        
        .activity-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-purple);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .security-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        
        .security-table th {
            background: var(--color-cream);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--color-purple);
            border-bottom: 2px solid var(--border-color);
            font-size: 14px;
        }
        
        .security-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            vertical-align: middle;
        }
        
        .security-table tbody tr:hover {
            background: var(--color-cream);
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
        
        .status-success {
            background: rgba(5, 150, 105, 0.1);
            color: #047857;
            border: 1px solid rgba(5, 150, 105, 0.2);
        }
        
        .status-warning {
            background: rgba(217, 119, 6, 0.1);
            color: #B45309;
            border: 1px solid rgba(217, 119, 6, 0.2);
        }
        
        .status-danger {
            background: rgba(220, 38, 38, 0.1);
            color: #991B1B;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }
        
        .status-info {
            background: rgba(37, 99, 235, 0.1);
            color: #1D4ED8;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }
        
        .status-neutral {
            background: rgba(107, 114, 128, 0.1);
            color: #4B5563;
            border: 1px solid rgba(107, 114, 128, 0.2);
        }
        
        /* Member Info */
        .member-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .member-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--color-purple);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .member-details h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: var(--color-purple);
            font-size: 14px;
        }
        
        .member-details p {
            margin: 0;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        /* Charts Section */
        .charts-row {
            margin-bottom: 40px;
        }
        
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            height: 400px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Real-time Updates */
        .last-updated {
            text-align: center;
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 20px;
            padding: 12px;
            background: var(--color-cream);
            border-radius: 8px;
        }
        
        .refresh-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #059669;
            font-weight: 500;
        }
        
        .refresh-indicator i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .security-header {
                padding: 24px;
            }
            
            .security-header h1 {
                font-size: 28px;
            }
            
            .activity-card {
                padding: 20px;
            }
            
            .security-table th,
            .security-table td {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .member-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .chart-card {
                height: 300px;
                padding: 20px;
            }
            
            .chart-container {
                height: 220px;
            }
        }
        
        /* Loading States */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
            height: 20px;
            margin: 4px 0;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/navigation.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Security Header -->
            <div class="security-header">
                <div class="content">
                    <h1>
                        <div class="security-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        Security Settings & Monitoring
                    </h1>
                    <p class="security-subtitle">Real-time security monitoring and member activity tracking</p>
                    <div class="security-time">
                        <i class="fas fa-clock"></i>
                        Last updated: <?php echo date('l, F j, Y - g:i A'); ?>
                    </div>
                </div>
            </div>

            <!-- Debug Information Panel (Remove in production) -->
            <?php if ($debug_mode): ?>
            <div class="alert alert-info" style="background: #E0F2FE; border: 1px solid #0284C7; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                <h5 style="color: #0284C7; margin-bottom: 16px;">
                    <i class="fas fa-bug"></i> Debug Information
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Data Counts:</strong><br>
                        • Members: <?php echo $debug_info['member_count']; ?><br>
                        • OTP Activities: <?php echo $debug_info['otp_count']; ?><br>
                        • Device Activities: <?php echo $debug_info['device_count']; ?><br>
                        • Admin Activities: <?php echo $debug_info['admin_count']; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Statistics:</strong><br>
                        • Total Members: <?php echo $debug_info['stats']['total_members']; ?><br>
                        • Active (24h): <?php echo $debug_info['stats']['active_24h']; ?><br>
                        • Never Logged In: <?php echo $debug_info['stats']['never_logged_in']; ?><br>
                        • Total OTPs: <?php echo $debug_info['stats']['total_otps']; ?>
                    </div>
                </div>
                <?php if ($debug_info['has_error']): ?>
                <div class="alert alert-danger" style="margin-top: 15px; padding: 12px; background: #FEE2E2; border: 1px solid #DC2626; border-radius: 8px;">
                    <strong>Database Error:</strong> <?php echo htmlspecialchars($debug_info['error_message']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Security Statistics -->
            <div class="row security-stats">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="security-stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-users-crown"></i>
                        </div>
                        <h3 class="stat-number"><?php echo $security_stats['active_24h']; ?></h3>
                        <p class="stat-label">Active Members (24h)</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="security-stat-card">
                        <div class="stat-icon info">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="stat-number"><?php echo $security_stats['successful_logins_24h']; ?></h3>
                        <p class="stat-label">Successful Logins (24h)</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="security-stat-card">
                        <div class="stat-icon <?php echo $security_stats['failed_attempts_24h'] > 0 ? 'danger' : 'success'; ?>">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="stat-number"><?php echo $security_stats['failed_attempts_24h']; ?></h3>
                        <p class="stat-label">Failed Attempts (24h)</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="security-stat-card">
                        <div class="stat-icon <?php echo $security_stats['never_logged_in'] > 5 ? 'warning' : 'info'; ?>">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <h3 class="stat-number"><?php echo $security_stats['never_logged_in']; ?></h3>
                        <p class="stat-label">Never Logged In</p>
                    </div>
                </div>
            </div>
            
            <!-- Security Analytics Charts -->
            <div class="row charts-row">
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-chart-pie"></i>
                            Member Activity Distribution
                        </h3>
                        <div class="chart-container">
                            <canvas id="activityChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-chart-bar"></i>
                            Security Events (24h)
                        </h3>
                        <div class="chart-container">
                            <canvas id="securityEventsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Login Activities -->
            <div class="row activity-section">
                <div class="col-12">
                    <div class="activity-card">
                        <h3>
                            <i class="fas fa-users"></i>
                            Member Login Activities
                        </h3>
                        
                        <div class="table-responsive">
                            <table class="security-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Email</th>
                                        <th>EQUB</th>
                                        <th>Last Login</th>
                                        <th>Activity Status</th>
                                        <th>Account Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($member_activities)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-exclamation-circle fa-2x mb-3" style="color: #DC2626;"></i><br>
                                                <strong>No member activities loaded!</strong><br>
                                                <small>Expected to find <?php echo $security_stats['total_members'] ?? 0; ?> members in database</small><br>
                                                <?php if (isset($database_error)): ?>
                                                    <div class="alert alert-danger mt-2" style="display: inline-block; padding: 8px 12px;">
                                                        Database Error: <?php echo htmlspecialchars($database_error); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($member_activities as $activity): ?>
                                            <?php 
                                            // Calculate activity status
                                            $activity_status = 'Never logged in';
                                            $status_class = 'status-warning';
                                            
                                            if ($activity['last_login']) {
                                                $login_time = strtotime($activity['last_login']);
                                                $hours_ago = (time() - $login_time) / 3600;
                                                
                                                if ($hours_ago < 24) {
                                                    $activity_status = 'Active (24h)';
                                                    $status_class = 'status-success';
                                                } elseif ($hours_ago < 168) { // 7 days
                                                    $activity_status = 'Recent (7d)';
                                                    $status_class = 'status-info';
                                                } elseif ($hours_ago < 720) { // 30 days
                                                    $activity_status = 'Inactive (30d)';
                                                    $status_class = 'status-neutral';
                                                } else {
                                                    $activity_status = 'Dormant (30d+)';
                                                    $status_class = 'status-neutral';
                                                }
                                            }
                                            
                                            $days_since_login = $activity['last_login'] ? floor((time() - strtotime($activity['last_login'])) / 86400) : null;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="member-info">
                                                        <div class="member-avatar">
                                                            <?php echo strtoupper(substr($activity['first_name'], 0, 1) . substr($activity['last_name'], 0, 1)); ?>
                                                        </div>
                                                        <div class="member-details">
                                                            <h6><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></h6>
                                                            <p>ID: <?php echo htmlspecialchars($activity['member_id']); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($activity['email']); ?></td>
                                                <td><?php echo htmlspecialchars($activity['equb_name'] ?: 'No EQUB'); ?></td>
                                                <td>
                                                    <?php if ($activity['last_login']): ?>
                                                        <?php echo date('M j, Y g:i A', strtotime($activity['last_login'])); ?>
                                                        <?php if ($days_since_login !== null): ?>
                                                            <br><small class="text-muted"><?php echo $days_since_login; ?> days ago</small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $status_class; ?>">
                                                        <?php echo $activity_status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        echo $activity['is_active'] && $activity['is_approved'] ? 'status-success' : 'status-warning';
                                                    ?>">
                                                        <?php echo $activity['is_active'] && $activity['is_approved'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent OTP Activities -->
            <div class="row activity-section">
                <div class="col-lg-6 mb-4">
                    <div class="activity-card">
                        <h3>
                            <i class="fas fa-key"></i>
                            Recent OTP Activities
                        </h3>
                        
                        <div class="table-responsive">
                            <table class="security-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Attempts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_otp_activities)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                No recent OTP activities
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (array_slice($recent_otp_activities, 0, 10) as $otp): ?>
                                            <?php 
                                            // Simple status calculation
                                            if ($otp['is_used'] == 1) {
                                                $status = 'Success';
                                                $status_class = 'status-success';
                                            } elseif ($otp['attempt_count'] > 3) {
                                                $status = 'Failed';
                                                $status_class = 'status-danger';
                                            } else {
                                                $status = 'Pending';
                                                $status_class = 'status-info';
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php if ($otp['first_name']): ?>
                                                        <div class="member-info">
                                                            <div class="member-avatar" style="width: 28px; height: 28px; font-size: 12px;">
                                                                <?php echo strtoupper(substr($otp['first_name'], 0, 1) . substr($otp['last_name'], 0, 1)); ?>
                                                            </div>
                                                            <div class="member-details">
                                                                <h6 style="font-size: 12px;"><?php echo htmlspecialchars($otp['first_name'] . ' ' . $otp['last_name']); ?></h6>
                                                                <p style="font-size: 10px;"><?php echo htmlspecialchars($otp['email']); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($otp['email']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-uppercase"><?php echo str_replace('_', ' ', $otp['otp_type']); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M j, g:i A', strtotime($otp['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $status_class; ?>" style="font-size: 10px; padding: 3px 8px;">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo $otp['attempt_count']; ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Device Tracking -->
                <div class="col-lg-6 mb-4">
                    <div class="activity-card">
                        <h3>
                            <i class="fas fa-mobile-alt"></i>
                            Device Tracking
                        </h3>
                        
                        <div class="table-responsive">
                            <table class="security-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Device Info</th>
                                        <th>Last Seen</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($device_activities)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                No device activities found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (array_slice($device_activities, 0, 8) as $device): ?>
                                            <?php 
                                            // Simple device status
                                            $device_status = $device['is_approved'] ? 'Trusted' : 'Pending';
                                            $status_class = $device['is_approved'] ? 'status-success' : 'status-info';
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php if ($device['first_name']): ?>
                                                        <div class="member-info">
                                                            <div class="member-avatar" style="width: 28px; height: 28px; font-size: 12px;">
                                                                <?php echo strtoupper(substr($device['first_name'], 0, 1) . substr($device['last_name'], 0, 1)); ?>
                                                            </div>
                                                            <div class="member-details">
                                                                <h6 style="font-size: 12px;"><?php echo htmlspecialchars($device['first_name'] . ' ' . $device['last_name']); ?></h6>
                                                                <p style="font-size: 10px;"><?php echo htmlspecialchars($device['email']); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($device['email']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php 
                                                        // Extract browser from user agent
                                                        $userAgent = $device['user_agent'] ?? '';
                                                        if (strpos($userAgent, 'Chrome') !== false) {
                                                            echo '<i class="fab fa-chrome"></i> Chrome';
                                                        } elseif (strpos($userAgent, 'Firefox') !== false) {
                                                            echo '<i class="fab fa-firefox"></i> Firefox';
                                                        } elseif (strpos($userAgent, 'Safari') !== false) {
                                                            echo '<i class="fab fa-safari"></i> Safari';
                                                        } else {
                                                            echo '<i class="fas fa-globe"></i> Unknown';
                                                        }
                                                        ?>
                                                        <br>
                                                        <span class="text-muted"><?php echo htmlspecialchars($device['ip_address'] ?? 'Unknown IP'); ?></span>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php 
                                                        if ($device['last_seen']) {
                                                            echo date('M j, g:i A', strtotime($device['last_seen']));
                                                        } else {
                                                            echo date('M j, g:i A', strtotime($device['created_at']));
                                                        }
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $status_class; ?>" style="font-size: 10px; padding: 3px 8px;">
                                                        <?php echo $device_status; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Activities -->
            <div class="row activity-section">
                <div class="col-12">
                    <div class="activity-card">
                        <h3>
                            <i class="fas fa-user-shield"></i>
                            Administrator Activities
                        </h3>
                        
                        <div class="table-responsive">
                            <table class="security-table">
                                <thead>
                                    <tr>
                                        <th>Admin Username</th>
                                        <th>Last Login</th>
                                        <th>Account Created</th>
                                        <th>Status</th>
                                        <th>Account Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($admin_activities)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No admin activities found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($admin_activities as $admin): ?>
                                            <?php 
                                            // Simple admin status calculation
                                            $admin_status = 'Never logged in';
                                            $status_class = 'status-neutral';
                                            
                                            if ($admin['last_login']) {
                                                $login_time = strtotime($admin['last_login']);
                                                $hours_ago = (time() - $login_time) / 3600;
                                                
                                                if ($hours_ago < 1) {
                                                    $admin_status = 'Currently Active';
                                                    $status_class = 'status-success';
                                                } elseif ($hours_ago < 24) {
                                                    $admin_status = 'Active Today';
                                                    $status_class = 'status-info';
                                                } elseif ($hours_ago < 168) {
                                                    $admin_status = 'Active This Week';
                                                    $status_class = 'status-warning';
                                                } else {
                                                    $admin_status = 'Inactive';
                                                    $status_class = 'status-neutral';
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="member-info">
                                                        <div class="member-avatar">
                                                            <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                                                        </div>
                                                        <div class="member-details">
                                                            <h6><?php echo htmlspecialchars($admin['username']); ?></h6>
                                                            <p>Administrator</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($admin['last_login']): ?>
                                                        <?php echo date('M j, Y g:i A', strtotime($admin['last_login'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $status_class; ?>">
                                                        <?php echo $admin_status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $admin['is_active'] ? 'status-success' : 'status-danger'; ?>">
                                                        <?php echo $admin['is_active'] ? 'Active' : 'Disabled'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Update Indicator -->
            <div class="last-updated">
                <div class="refresh-indicator">
                    <i class="fas fa-sync-alt"></i>
                    Auto-refreshing every 30 seconds
                </div>
                <br>
                Data last updated: <strong><?php echo date('g:i:s A'); ?></strong>
            </div>
            
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Security Analytics JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔒 Security Settings Dashboard loaded successfully!');
        
        // Member Activity Distribution Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active (24h)', 'Recent (7d)', 'Inactive (30d)', 'Never Logged In'],
                datasets: [{
                    data: [
                        <?php echo $security_stats['active_24h']; ?>,
                        <?php echo $security_stats['active_7d'] - $security_stats['active_24h']; ?>,
                        <?php echo max(0, count($member_activities) - $security_stats['active_7d'] - $security_stats['never_logged_in']); ?>,
                        <?php echo $security_stats['never_logged_in']; ?>
                    ],
                    backgroundColor: [
                        '#059669',  // Green for active
                        '#2563EB',  // Blue for recent
                        '#D97706',  // Orange for inactive
                        '#DC2626'   // Red for never logged in
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
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed * 100) / total).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Security Events Chart
        const securityCtx = document.getElementById('securityEventsChart').getContext('2d');
        new Chart(securityCtx, {
            type: 'bar',
            data: {
                labels: ['OTP Requests', 'Successful Logins', 'Failed Attempts', 'New Devices'],
                datasets: [{
                    label: 'Count (24h)',
                    data: [
                        <?php echo $security_stats['otp_requests_24h']; ?>,
                        <?php echo $security_stats['successful_logins_24h']; ?>,
                        <?php echo $security_stats['failed_attempts_24h']; ?>,
                        <?php echo $security_stats['new_devices_7d']; ?>
                    ],
                    backgroundColor: [
                        '#2563EB',  // Blue for OTP requests
                        '#059669',  // Green for successful
                        '#DC2626',  // Red for failed
                        '#D97706'   // Orange for new devices
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
        
        // Auto-refresh functionality
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(function() {
                // Update timestamp
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-GB');
                const timestampElements = document.querySelectorAll('.last-updated strong');
                timestampElements.forEach(el => {
                    el.textContent = timeString;
                });
                
                // Optional: Reload page every 5 minutes for fresh data
                if (now.getMinutes() % 5 === 0 && now.getSeconds() === 0) {
                    window.location.reload();
                }
            }, 30000); // 30 seconds
        }
        
        // Start auto-refresh
        startAutoRefresh();
        
        // Security monitoring alerts
        const failedAttempts = <?php echo $security_stats['failed_attempts_24h']; ?>;
        const neverLoggedIn = <?php echo $security_stats['never_logged_in']; ?>;
        
        if (failedAttempts > 5) {
            console.warn('⚠️ High number of failed login attempts detected:', failedAttempts);
        }
        
        if (neverLoggedIn > 10) {
            console.info('ℹ️ Many members have never logged in:', neverLoggedIn);
        }
        
        console.log('🔒 Security Statistics:', {
            active24h: <?php echo $security_stats['active_24h']; ?>,
            successfulLogins: <?php echo $security_stats['successful_logins_24h']; ?>,
            failedAttempts: failedAttempts,
            neverLoggedIn: neverLoggedIn,
            totalMembers: <?php echo $security_stats['total_members']; ?>,
            totalOTPs: <?php echo $security_stats['total_otps']; ?>,
            totalDevices: <?php echo $security_stats['total_devices']; ?>
        });
        
        // Debug information
        console.log('🔍 Debug Info:', <?php echo json_encode($debug_info); ?>);
        
        <?php if (isset($database_error)): ?>
        console.error('🚨 Database Error:', <?php echo json_encode($database_error); ?>);
        <?php endif; ?>
        
        <?php if (count($member_activities) > 0): ?>
        console.log('✅ Successfully loaded member activities:', <?php echo count($member_activities); ?>);
        <?php else: ?>
        console.warn('⚠️ No member activities loaded - check database connection');
        <?php endif; ?>
    });
    </script>
</body>
</html>