<?php
/**
 * HabeshaEqub - Financial Analytics Dashboard
 * Modern, comprehensive financial analytics with real-time data
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once '../includes/enhanced_equb_calculator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get selected equb or default to first active equb
$selected_equb_id = intval($_GET['equb_id'] ?? 0);

// Get all equbs for selection
try {
    $stmt = $pdo->query("
        SELECT id, equb_id, equb_name, status, start_date, end_date, 
               duration_months, admin_fee, max_members, current_members,
               total_pool_amount, payout_day
        FROM equb_settings 
        ORDER BY 
            CASE WHEN status = 'active' THEN 1 WHEN status = 'planning' THEN 2 ELSE 3 END,
            created_at DESC
    ");
    $all_equbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$selected_equb_id && !empty($all_equbs)) {
        $selected_equb_id = $all_equbs[0]['id'];
    }
} catch (PDOException $e) {
    $all_equbs = [];
    error_log("Error fetching equbs: " . $e->getMessage());
}

// Initialize financial data
$financial_data = [];
$equb_data = null;

if ($selected_equb_id) {
    try {
        // Get equb settings
        $stmt = $pdo->prepare("SELECT * FROM equb_settings WHERE id = ?");
        $stmt->execute([$selected_equb_id]);
        $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($equb_data) {
            // Initialize calculator
            $calculator = new EnhancedEqubCalculator($pdo);
            $equb_calculation = $calculator->calculateEqubPositions($selected_equb_id);
            
            // Get member count
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT CASE WHEN m.membership_type = 'joint' THEN CONCAT('joint_', m.joint_group_id) ELSE CONCAT('individual_', m.id) END) as total_positions,
                       COUNT(DISTINCT CASE WHEN m.membership_type = 'individual' THEN m.id END) as individual_positions,
                       COUNT(DISTINCT CASE WHEN m.membership_type = 'joint' THEN m.joint_group_id END) as joint_positions
                FROM members m
                WHERE m.equb_settings_id = ? AND m.is_active = 1
            ");
            $stmt->execute([$selected_equb_id]);
            $position_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate monthly pool
            $monthly_pool = $equb_calculation['success'] ? (float)$equb_calculation['total_monthly_pool'] : 0;
            $duration = (int)$equb_data['duration_months'];
            $total_pool_value = $monthly_pool * $duration;
            
            // Get payment statistics
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_collected,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'late' THEN amount ELSE 0 END) as late_amount,
                    SUM(CASE WHEN status = 'missed' THEN amount ELSE 0 END) as missed_amount,
                    SUM(late_fee) as total_late_fees,
                    COUNT(DISTINCT member_id) as paying_members,
                    AVG(CASE WHEN status = 'paid' THEN amount ELSE NULL END) as avg_payment
                FROM payments p
                JOIN members m ON p.member_id = m.id
                WHERE m.equb_settings_id = ?
            ");
            $stmt->execute([$selected_equb_id]);
            $payment_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get payout statistics
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_payouts,
                    SUM(CASE WHEN status = 'completed' THEN net_amount ELSE 0 END) as total_distributed,
                    SUM(CASE WHEN status = 'scheduled' THEN net_amount ELSE 0 END) as scheduled_amount,
                    SUM(CASE WHEN status = 'processing' THEN net_amount ELSE 0 END) as processing_amount,
                    SUM(CASE WHEN status = 'completed' THEN admin_fee ELSE 0 END) as total_admin_fees,
                    AVG(CASE WHEN status = 'completed' THEN net_amount ELSE NULL END) as avg_payout
                FROM payouts po
                JOIN members m ON po.member_id = m.id
                WHERE m.equb_settings_id = ?
            ");
            $stmt->execute([$selected_equb_id]);
            $payout_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Monthly collection trends (last 12 months)
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(payment_date, '%Y-%m') as month,
                    DATE_FORMAT(payment_date, '%b %Y') as month_label,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as collected,
                    COUNT(CASE WHEN status = 'paid' THEN 1 END) as payment_count,
                    SUM(late_fee) as late_fees
                FROM payments p
                JOIN members m ON p.member_id = m.id
                WHERE m.equb_settings_id = ? 
                  AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                ORDER BY month ASC
            ");
            $stmt->execute([$selected_equb_id]);
            $monthly_trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Payment method distribution
            $stmt = $pdo->prepare("
                SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM payments p
                JOIN members m ON p.member_id = m.id
                WHERE m.equb_settings_id = ? AND status = 'paid'
                GROUP BY payment_method
            ");
            $stmt->execute([$selected_equb_id]);
            $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top paying members
            $stmt = $pdo->prepare("
                SELECT 
                    m.id,
                    m.first_name,
                    m.last_name,
                    m.member_id as member_code,
                    COUNT(p.id) as payment_count,
                    SUM(p.amount) as total_paid,
                    AVG(CASE WHEN p.status = 'paid' THEN DATEDIFF(p.payment_date, p.payment_month) ELSE NULL END) as avg_days_late
                FROM members m
                LEFT JOIN payments p ON m.id = p.member_id AND p.status = 'paid'
                WHERE m.equb_settings_id = ? AND m.is_active = 1
                GROUP BY m.id
                ORDER BY total_paid DESC
                LIMIT 10
            ");
            $stmt->execute([$selected_equb_id]);
            $top_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate collection rate
            $expected_contributions = $monthly_pool * $duration;
            $collected = (float)($payment_stats['total_collected'] ?? 0);
            $collection_rate = $expected_contributions > 0 ? ($collected / $expected_contributions) * 100 : 0;
            
            // Calculate financial health score
            $health_score = 0;
            if ($collection_rate >= 90) $health_score += 40;
            elseif ($collection_rate >= 75) $health_score += 30;
            elseif ($collection_rate >= 60) $health_score += 20;
            else $health_score += 10;
            
            $on_time_rate = $payment_stats['total_payments'] > 0 ? 
                (($payment_stats['total_payments'] - ($payment_stats['late_amount'] ?? 0) - ($payment_stats['missed_amount'] ?? 0)) / $payment_stats['total_payments']) * 100 : 0;
            
            if ($on_time_rate >= 90) $health_score += 30;
            elseif ($on_time_rate >= 75) $health_score += 20;
            elseif ($on_time_rate >= 60) $health_score += 15;
            else $health_score += 10;
            
            $payout_completion = $position_data['total_positions'] > 0 ? 
                (($payout_stats['total_payouts'] ?? 0) / $position_data['total_positions']) * 100 : 0;
            
            if ($payout_completion >= 50) $health_score += 30;
            elseif ($payout_completion >= 25) $health_score += 20;
            else $health_score += 10;
            
            // Compile financial data
            $financial_data = [
                'equb_info' => $equb_data,
                'positions' => $position_data,
                'monthly_pool' => $monthly_pool,
                'total_pool_value' => $total_pool_value,
                'duration' => $duration,
                'payment_stats' => $payment_stats,
                'payout_stats' => $payout_stats,
                'monthly_trends' => $monthly_trends,
                'payment_methods' => $payment_methods,
                'top_members' => $top_members,
                'collection_rate' => $collection_rate,
                'expected_contributions' => $expected_contributions,
                'on_time_rate' => $on_time_rate,
                'payout_completion' => $payout_completion,
                'health_score' => min(100, $health_score),
                'admin_revenue' => (float)($payout_stats['total_admin_fees'] ?? 0) + (float)($payment_stats['total_late_fees'] ?? 0)
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching financial data: " . $e->getMessage());
    }
}

// Set page title
$page_title = 'Financial Analytics';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - HabeshaEqub Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Include Navigation -->
    <?php include 'includes/navigation.php'; ?>
    
    <style>
        :root {
            --primary-color: #4D4052;
            --secondary-color: #DAA520;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #F1ECE2;
            --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .analytics-container {
            padding: 24px;
            background: #f8f9fa;
            min-height: calc(100vh - 70px);
        }
        
        .page-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-header h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 700;
        }
        
        .equb-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .equb-selector select {
            padding: 10px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            min-width: 250px;
            background: white;
            cursor: pointer;
        }
        
        .equb-selector select:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .stat-card-title {
            font-size: 14px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-card-icon.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card-icon.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card-icon.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card-icon.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card-icon.gold { background: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%); }
        
        .stat-card-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .stat-card-change {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .stat-card-change.positive { color: var(--success-color); }
        .stat-card-change.negative { color: var(--danger-color); }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .chart-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }
        
        .chart-card-header {
            margin-bottom: 20px;
        }
        
        .chart-card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .table-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
        }
        
        .table-card-header {
            margin-bottom: 20px;
        }
        
        .table-card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table thead {
            background: #f8f9fa;
        }
        
        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .health-score {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
        }
        
        .health-score-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 8px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            background: rgba(255,255,255,0.1);
        }
        
        .health-score-info h3 {
            margin: 0 0 8px 0;
            font-size: 24px;
        }
        
        .health-score-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .analytics-container {
                padding: 16px;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .equb-selector {
                width: 100%;
            }
            
            .equb-selector select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="analytics-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Financial Analytics</h1>
            <div class="equb-selector">
                <label for="equbSelect"><strong>Select Equb:</strong></label>
                <select id="equbSelect" onchange="window.location.href='?equb_id=' + this.value">
                    <?php foreach ($all_equbs as $equb): ?>
                        <option value="<?php echo $equb['id']; ?>" <?php echo $selected_equb_id == $equb['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($equb['equb_name']); ?> 
                            (<?php echo ucfirst($equb['status']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if ($equb_data && !empty($financial_data)): ?>
            <!-- Key Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Total Pool Value</span>
                        <div class="stat-card-icon primary">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        ETB <?php echo number_format($financial_data['total_pool_value'], 2); ?>
                    </div>
                    <div class="stat-card-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $financial_data['duration']; ?> months duration
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Total Collected</span>
                        <div class="stat-card-icon success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        ETB <?php echo number_format($financial_data['payment_stats']['total_collected'] ?? 0, 2); ?>
                    </div>
                    <div class="stat-card-change positive">
                        <i class="fas fa-check-circle"></i>
                        <?php echo number_format($financial_data['collection_rate'], 1); ?>% collected
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Total Distributed</span>
                        <div class="stat-card-icon info">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        ETB <?php echo number_format($financial_data['payout_stats']['total_distributed'] ?? 0, 2); ?>
                    </div>
                    <div class="stat-card-change positive">
                        <i class="fas fa-chart-line"></i>
                        <?php echo $financial_data['payout_stats']['total_payouts'] ?? 0; ?> payouts completed
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Admin Revenue</span>
                        <div class="stat-card-icon gold">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        ETB <?php echo number_format($financial_data['admin_revenue'], 2); ?>
                    </div>
                    <div class="stat-card-change positive">
                        <i class="fas fa-trophy"></i>
                        Fees + Late fees
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Monthly Pool</span>
                        <div class="stat-card-icon warning">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        ETB <?php echo number_format($financial_data['monthly_pool'], 2); ?>
                    </div>
                    <div class="stat-card-change">
                        <i class="fas fa-users"></i>
                        <?php echo $financial_data['positions']['total_positions'] ?? 0; ?> positions
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">On-Time Rate</span>
                        <div class="stat-card-icon success">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        <?php echo number_format($financial_data['on_time_rate'], 1); ?>%
                    </div>
                    <div class="stat-card-change <?php echo $financial_data['on_time_rate'] >= 90 ? 'positive' : ($financial_data['on_time_rate'] >= 75 ? '' : 'negative'); ?>">
                        <i class="fas fa-<?php echo $financial_data['on_time_rate'] >= 90 ? 'check' : 'exclamation-triangle'; ?>"></i>
                        Payment performance
                    </div>
                </div>
            </div>
            
            <!-- Financial Health Score -->
            <div class="health-score" style="margin-bottom: 24px;">
                <div class="health-score-circle">
                    <?php echo round($financial_data['health_score']); ?>
                </div>
                <div class="health-score-info">
                    <h3>Financial Health Score</h3>
                    <p>
                        Collection Rate: <?php echo number_format($financial_data['collection_rate'], 1); ?>% | 
                        On-Time Rate: <?php echo number_format($financial_data['on_time_rate'], 1); ?>% | 
                        Payout Completion: <?php echo number_format($financial_data['payout_completion'], 1); ?>%
                    </p>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h3 class="chart-card-title">Monthly Collection Trends</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h3 class="chart-card-title">Payment Method Distribution</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Top Members Table -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3 class="table-card-title">Top Contributing Members</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Member ID</th>
                            <th>Payments</th>
                            <th>Total Paid</th>
                            <th>Avg Days Late</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financial_data['top_members'] as $member): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($member['member_code']); ?></td>
                                <td><?php echo $member['payment_count']; ?></td>
                                <td>ETB <?php echo number_format($member['total_paid'] ?? 0, 2); ?></td>
                                <td>
                                    <?php 
                                    $avg_late = $member['avg_days_late'] ?? 0;
                                    if ($avg_late <= 0) {
                                        echo '<span class="badge badge-success">On Time</span>';
                                    } elseif ($avg_late <= 7) {
                                        echo '<span class="badge badge-warning">' . round($avg_late) . ' days</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">' . round($avg_late) . ' days</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">Active</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Payment Status Summary -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3 class="chart-card-title">Payment Status Summary</h3>
                </div>
                <div class="chart-container" style="height: 250px;">
                    <canvas id="paymentStatusChart"></canvas>
                </div>
            </div>
            
        <?php else: ?>
            <div class="stat-card" style="text-align: center; padding: 48px;">
                <i class="fas fa-chart-line" style="font-size: 64px; color: #ccc; margin-bottom: 16px;"></i>
                <h3 style="color: #666;">No Financial Data Available</h3>
                <p style="color: #999;">Please select an equb or ensure the selected equb has financial data.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        <?php if ($equb_data && !empty($financial_data)): ?>
        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
        if (monthlyTrendCtx) {
            const monthlyData = <?php echo json_encode($financial_data['monthly_trends']); ?>;
            new Chart(monthlyTrendCtx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(d => d.month_label),
                    datasets: [{
                        label: 'Collected Amount',
                        data: monthlyData.map(d => parseFloat(d.collected || 0)),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'ETB ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Payment Method Chart
        const paymentMethodCtx = document.getElementById('paymentMethodChart');
        if (paymentMethodCtx) {
            const methodData = <?php echo json_encode($financial_data['payment_methods']); ?>;
            new Chart(paymentMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: methodData.map(d => d.payment_method.replace('_', ' ').toUpperCase()),
                    datasets: [{
                        data: methodData.map(d => parseFloat(d.total_amount || 0)),
                        backgroundColor: [
                            '#667eea',
                            '#f093fb',
                            '#4facfe',
                            '#43e97b'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ETB ' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Payment Status Chart
        const paymentStatusCtx = document.getElementById('paymentStatusChart');
        if (paymentStatusCtx) {
            const paymentStats = <?php echo json_encode($financial_data['payment_stats']); ?>;
            new Chart(paymentStatusCtx, {
                type: 'bar',
                data: {
                    labels: ['Paid', 'Pending', 'Late', 'Missed'],
                    datasets: [{
                        label: 'Amount',
                        data: [
                            parseFloat(paymentStats.total_collected || 0),
                            parseFloat(paymentStats.pending_amount || 0),
                            parseFloat(paymentStats.late_amount || 0),
                            parseFloat(paymentStats.missed_amount || 0)
                        ],
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#fd7e14',
                            '#dc3545'
                        ]
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
                                    return 'ETB ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'ETB ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
