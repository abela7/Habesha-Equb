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
    // Member Login Activities
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
            es.equb_name,
            CASE 
                WHEN m.last_login IS NULL THEN 'Never logged in'
                WHEN m.last_login >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'Active (24h)'
                WHEN m.last_login >= DATE_SUB(NOW(), INTERVAL 7 DAYS) THEN 'Recent (7d)'
                WHEN m.last_login >= DATE_SUB(NOW(), INTERVAL 30 DAYS) THEN 'Inactive (30d)'
                ELSE 'Dormant (30d+)'
            END as activity_status,
            TIMESTAMPDIFF(DAY, m.last_login, NOW()) as days_since_login
        FROM members m
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        ORDER BY m.last_login DESC NULLS LAST
        LIMIT 50
    ")->fetchAll();
    
    // Security Statistics
    $security_stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM members WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOURS)) as active_24h,
            (SELECT COUNT(*) FROM members WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAYS)) as active_7d,
            (SELECT COUNT(*) FROM members WHERE last_login IS NULL) as never_logged_in,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOURS)) as otp_requests_24h,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOURS) AND is_used = 1) as successful_logins_24h,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOURS) AND attempt_count > 3) as failed_attempts_24h,
            (SELECT COUNT(*) FROM device_tracking WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)) as new_devices_7d,
            (SELECT COUNT(*) FROM device_tracking WHERE is_approved = 0) as unapproved_devices
    ")->fetch();
    
    // Recent OTP Activities
    $recent_otp_activities = $pdo->query("
        SELECT 
            uo.id,
            uo.email,
            uo.otp_type,
            uo.created_at,
            uo.expires_at,
            uo.is_used,
            uo.attempt_count,
            m.first_name,
            m.last_name,
            m.member_id,
            CASE 
                WHEN uo.is_used = 1 THEN 'Success'
                WHEN uo.expires_at < NOW() THEN 'Expired'
                WHEN uo.attempt_count > 3 THEN 'Failed'
                ELSE 'Pending'
            END as status
        FROM user_otps uo
        LEFT JOIN members m ON uo.email = m.email
        ORDER BY uo.created_at DESC
        LIMIT 30
    ")->fetchAll();
    
    // Device Tracking
    $device_activities = $pdo->query("
        SELECT 
            dt.id,
            dt.email,
            dt.device_fingerprint,
            dt.user_agent,
            dt.ip_address,
            dt.is_approved,
            dt.created_at,
            dt.last_seen,
            dt.expires_at,
            m.first_name,
            m.last_name,
            m.member_id,
            CASE 
                WHEN dt.expires_at < NOW() THEN 'Expired'
                WHEN dt.is_approved = 1 THEN 'Trusted'
                ELSE 'Pending Approval'
            END as device_status
        FROM device_tracking dt
        LEFT JOIN members m ON dt.email = m.email
        ORDER BY dt.last_seen DESC NULLS LAST, dt.created_at DESC
        LIMIT 20
    ")->fetchAll();
    
    // Admin Activities (if available)
    $admin_activities = $pdo->query("
        SELECT 
            a.id,
            a.username,
            a.last_login,
            a.is_active,
            a.created_at,
            CASE 
                WHEN a.last_login >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'Currently Active'
                WHEN a.last_login >= DATE_SUB(NOW(), INTERVAL 24 HOURS) THEN 'Active Today'
                WHEN a.last_login >= DATE_SUB(NOW(), INTERVAL 7 DAYS) THEN 'Active This Week'
                ELSE 'Inactive'
            END as admin_status
        FROM admins a
        WHERE a.is_active = 1
        ORDER BY a.last_login DESC NULLS LAST
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Security Settings error: " . $e->getMessage());
    // Set default values
    $member_activities = [];
    $security_stats = [
        'active_24h' => 0, 'active_7d' => 0, 'never_logged_in' => 0,
        'otp_requests_24h' => 0, 'successful_logins_24h' => 0, 'failed_attempts_24h' => 0,
        'new_devices_7d' => 0, 'unapproved_devices' => 0
    ];
    $recent_otp_activities = [];
    $device_activities = [];
    $admin_activities = [];
}
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
                                                <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                                No member activities found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($member_activities as $activity): ?>
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
                                                        <br><small class="text-muted"><?php echo $activity['days_since_login']; ?> days ago</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        echo $activity['activity_status'] === 'Active (24h)' ? 'status-success' : 
                                                            ($activity['activity_status'] === 'Recent (7d)' ? 'status-info' : 
                                                            ($activity['activity_status'] === 'Never logged in' ? 'status-warning' : 'status-neutral'));
                                                    ?>">
                                                        <?php echo $activity['activity_status']; ?>
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
                                                    <span class="status-badge <?php 
                                                        echo $otp['status'] === 'Success' ? 'status-success' : 
                                                            ($otp['status'] === 'Failed' ? 'status-danger' : 
                                                            ($otp['status'] === 'Expired' ? 'status-warning' : 'status-info'));
                                                    ?>" style="font-size: 10px; padding: 3px 8px;">
                                                        <?php echo $otp['status']; ?>
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
                                                        $userAgent = $device['user_agent'];
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
                                                        <span class="text-muted"><?php echo htmlspecialchars($device['ip_address']); ?></span>
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
                                                    <span class="status-badge <?php 
                                                        echo $device['device_status'] === 'Trusted' ? 'status-success' : 
                                                            ($device['device_status'] === 'Expired' ? 'status-warning' : 'status-info');
                                                    ?>" style="font-size: 10px; padding: 3px 8px;">
                                                        <?php echo $device['device_status']; ?>
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
                                            <tr>
                                                <td>
                                                    <div class="member-info">
                                                        <div class="member-avatar">
                                                            <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                                                        </div>
                                                        <div class="member-details">
                                                            <h6><?php echo htmlspecialchars($admin['username']); ?></h6>
                                                            <p>Admin ID: <?php echo $admin['id']; ?></p>
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
                                                    <span class="status-badge <?php 
                                                        echo $admin['admin_status'] === 'Currently Active' ? 'status-success' : 
                                                            ($admin['admin_status'] === 'Active Today' ? 'status-info' : 
                                                            ($admin['admin_status'] === 'Active This Week' ? 'status-warning' : 'status-neutral'));
                                                    ?>">
                                                        <?php echo $admin['admin_status']; ?>
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
            neverLoggedIn: neverLoggedIn
        });
    });
    </script>
</body>
</html>