<?php
/**
 * HabeshaEqub - Financial Analytics Dashboard
 * Comprehensive financial analytics with real-time data
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = $_SESSION['admin_username'];

// Get selected equb or default to first active equb
$selected_equb_id = intval($_GET['equb_id'] ?? 0);

// Get all equbs for selection
try {
    $stmt = $pdo->query("
        SELECT id, equb_id, equb_name, status, start_date, end_date, 
               duration_months, admin_fee, max_members, current_members
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

// Initialize financial data with defaults
$financial_data = [
    'total_collected' => 0,
    'total_distributed' => 0,
    'pending_payments' => 0,
    'late_payments' => 0,
    'completed_payouts' => 0,
    'scheduled_payouts' => 0,
    'admin_revenue' => 0,
    'collection_rate' => 0,
    'avg_payment' => 0,
    'avg_payout' => 0
];

$monthly_trends = [];
$payment_methods = [];
$payment_status = [];

// Fetch financial data - show all data if no equb selected, filtered if equb selected
try {
    // Build WHERE clause based on equb selection
    $equb_filter = $selected_equb_id ? "WHERE m.equb_settings_id = ?" : "";
    $params = $selected_equb_id ? [$selected_equb_id] : [];
    
    // Overall payment statistics
    $sql = "
        SELECT 
            COUNT(*) as total_payments,
            COALESCE(SUM(CASE WHEN p.status IN ('paid', 'completed') THEN p.amount ELSE 0 END), 0) as total_collected,
            COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END), 0) as pending_amount,
            COALESCE(SUM(CASE WHEN p.status = 'late' THEN p.amount ELSE 0 END), 0) as late_amount,
            COUNT(CASE WHEN p.status IN ('paid', 'completed') THEN 1 END) as completed_payments,
            COUNT(CASE WHEN p.status = 'pending' THEN 1 END) as pending_payments,
            COUNT(CASE WHEN p.status = 'late' THEN 1 END) as late_payments,
            COALESCE(AVG(CASE WHEN p.status IN ('paid', 'completed') THEN p.amount END), 0) as avg_payment,
            COALESCE(SUM(p.late_fee), 0) as total_late_fees
        FROM payments p
        INNER JOIN members m ON p.member_id = m.id
        " . $equb_filter;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payment_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Payout statistics
    $sql = "
        SELECT 
            COUNT(*) as total_payouts,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.total_amount ELSE 0 END), 0) as total_distributed,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as total_net_distributed,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.admin_fee ELSE 0 END), 0) as total_admin_fees,
            COALESCE(SUM(CASE WHEN po.status = 'scheduled' THEN po.total_amount ELSE 0 END), 0) as scheduled_amount,
            COUNT(CASE WHEN po.status = 'completed' THEN 1 END) as completed_payouts,
            COUNT(CASE WHEN po.status = 'scheduled' THEN 1 END) as scheduled_payouts,
            COALESCE(AVG(CASE WHEN po.status = 'completed' THEN po.net_amount END), 0) as avg_payout
        FROM payouts po
        INNER JOIN members m ON po.member_id = m.id
        " . $equb_filter;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payout_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Monthly payment trends (last 12 months) - use payment_date if payment_month is null
    $sql = "
        SELECT 
            DATE_FORMAT(COALESCE(p.payment_month, p.payment_date), '%Y-%m') as month,
            COUNT(*) as payment_count,
            COALESCE(SUM(CASE WHEN p.status IN ('paid', 'completed') THEN p.amount ELSE 0 END), 0) as collected_amount,
            COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END), 0) as pending_amount
        FROM payments p
        INNER JOIN members m ON p.member_id = m.id
        " . ($selected_equb_id ? "WHERE m.equb_settings_id = ? AND " : "WHERE ") . "
        COALESCE(p.payment_month, p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(COALESCE(p.payment_month, p.payment_date), '%Y-%m')
        ORDER BY month ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $monthly_trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Payment methods breakdown
    $sql = "
        SELECT 
            p.payment_method,
            COUNT(*) as count,
            COALESCE(SUM(p.amount), 0) as total_amount
        FROM payments p
        INNER JOIN members m ON p.member_id = m.id
        " . ($selected_equb_id ? "WHERE m.equb_settings_id = ? AND " : "WHERE ") . "
        p.status IN ('paid', 'completed')
        GROUP BY p.payment_method
        ORDER BY total_amount DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Payment status distribution
    $sql = "
        SELECT 
            p.status,
            COUNT(*) as count,
            COALESCE(SUM(p.amount), 0) as total_amount
        FROM payments p
        INNER JOIN members m ON p.member_id = m.id
        " . $equb_filter . "
        GROUP BY p.status
        ORDER BY count DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payment_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate collection rate - get expected total from actual payments or members
    $expected_total = 0;
    if ($selected_equb_id) {
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(monthly_payment), 0) as total_monthly,
                COUNT(*) as member_count
            FROM members
            WHERE equb_settings_id = ? AND is_active = 1
        ");
        $stmt->execute([$selected_equb_id]);
        $monthly_total = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($monthly_total && $monthly_total['total_monthly'] > 0) {
            // Get duration from equb_settings
            $stmt = $pdo->prepare("SELECT duration_months FROM equb_settings WHERE id = ?");
            $stmt->execute([$selected_equb_id]);
            $equb_info = $stmt->fetch(PDO::FETCH_ASSOC);
            $duration = $equb_info['duration_months'] ?? 12;
            $expected_total = $monthly_total['total_monthly'] * $duration;
        }
    } else {
        // For all equbs, calculate based on total expected
        $stmt = $pdo->query("
            SELECT 
                COALESCE(SUM(m.monthly_payment * es.duration_months), 0) as total_expected
            FROM members m
            INNER JOIN equb_settings es ON m.equb_settings_id = es.id
            WHERE m.is_active = 1
        ");
        $total_expected = $stmt->fetch(PDO::FETCH_ASSOC);
        $expected_total = floatval($total_expected['total_expected'] ?? 0);
    }
    
    // Compile financial data
    $financial_data = [
        'total_collected' => floatval($payment_stats['total_collected'] ?? 0),
        'total_distributed' => floatval($payout_stats['total_net_distributed'] ?? 0),
        'pending_payments' => intval($payment_stats['pending_payments'] ?? 0),
        'pending_amount' => floatval($payment_stats['pending_amount'] ?? 0),
        'late_payments' => intval($payment_stats['late_payments'] ?? 0),
        'late_amount' => floatval($payment_stats['late_amount'] ?? 0),
        'completed_payouts' => intval($payout_stats['completed_payouts'] ?? 0),
        'scheduled_payouts' => intval($payout_stats['scheduled_payouts'] ?? 0),
        'admin_revenue' => floatval($payout_stats['total_admin_fees'] ?? 0),
        'collection_rate' => $expected_total > 0 ? (floatval($payment_stats['total_collected'] ?? 0) / $expected_total) * 100 : 0,
        'avg_payment' => floatval($payment_stats['avg_payment'] ?? 0),
        'avg_payout' => floatval($payout_stats['avg_payout'] ?? 0),
        'total_payments' => intval($payment_stats['total_payments'] ?? 0),
        'completed_payments' => intval($payment_stats['completed_payments'] ?? 0),
        'total_late_fees' => floatval($payment_stats['total_late_fees'] ?? 0)
    ];
    
} catch (PDOException $e) {
    error_log("Error fetching financial data: " . $e->getMessage());
    // Keep default values on error
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Analytics - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* === FINANCIAL ANALYTICS PAGE DESIGN === */
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
        }
        
        .page-title-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-title-section p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 16px;
        }
        
        .equb-selector {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            color: var(--color-purple);
            min-width: 300px;
            transition: all 0.3s ease;
        }
        
        .equb-selector:focus {
            outline: none;
            border-color: var(--color-gold);
            box-shadow: 0 0 0 3px rgba(233, 196, 106, 0.1);
        }
        
        /* Statistics Cards */
        .stats-container {
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.12);
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 24px;
        }
        
        .stat-icon.teal { background: rgba(19, 102, 92, 0.1); color: var(--color-teal); }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #10B981; }
        .stat-icon.gold { background: rgba(233, 196, 106, 0.1); color: var(--color-gold); }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8B5CF6; }
        .stat-icon.orange { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
        .stat-icon.red { background: rgba(239, 68, 68, 0.1); color: #EF4444; }
        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0 0 12px 0;
            font-weight: 500;
        }
        
        .stat-change {
            font-size: 13px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .stat-change.positive {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }
        
        .stat-change.neutral {
            background: rgba(107, 114, 128, 0.1);
            color: #6B7280;
        }
        
        .stat-change.negative {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
        }
        
        /* Charts Section */
        .charts-section {
            margin-bottom: 40px;
        }
        
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            margin-bottom: 30px;
        }
        
        .chart-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-purple);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 350px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                padding: 25px;
            }
            
            .page-title-section h1 {
                font-size: 24px;
            }
            
            .equb-selector {
                min-width: 100%;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 24px;
            }
            
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="page-title-section">
                        <h1>
                            <i class="fas fa-chart-line"></i>
                            Financial Analytics
                        </h1>
                        <p>
                            Comprehensive financial insights and performance metrics
                            <?php if ($selected_equb_id): ?>
                                <span class="badge bg-teal ms-2">Filtered: <?php echo htmlspecialchars($all_equbs[array_search($selected_equb_id, array_column($all_equbs, 'id'))]['equb_name'] ?? 'Selected EQUB'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-info ms-2">All EQUBs</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold mb-2">Select EQUB</label>
                    <select class="form-select equb-selector" onchange="window.location.href='?equb_id=' + this.value">
                        <option value="">Choose EQUB...</option>
                        <?php foreach ($all_equbs as $equb): ?>
                            <option value="<?php echo $equb['id']; ?>" <?php echo ($equb['id'] == $selected_equb_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($equb['equb_name']); ?> 
                                (<?php echo ucfirst($equb['status']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <?php if ($financial_data['total_payments'] > 0 || $financial_data['completed_payouts'] > 0): ?>
                <div class="row g-4">
                    <!-- Total Collected -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['total_collected'], 2); ?></div>
                            <div class="stat-label">Total Collected</div>
                            <div class="stat-change positive">
                                <i class="fas fa-check-circle me-1"></i>
                                <?php echo $financial_data['completed_payments']; ?> Payments
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Distributed -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon gold">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['total_distributed'], 2); ?></div>
                            <div class="stat-label">Total Distributed</div>
                            <div class="stat-change positive">
                                <i class="fas fa-check me-1"></i>
                                <?php echo $financial_data['completed_payouts']; ?> Completed
                            </div>
                        </div>
                    </div>
                    
                    <!-- Collection Rate -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['collection_rate'], 1); ?>%</div>
                            <div class="stat-label">Collection Rate</div>
                            <div class="stat-change <?php echo $financial_data['collection_rate'] >= 85 ? 'positive' : ($financial_data['collection_rate'] >= 70 ? 'neutral' : 'negative'); ?>">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo $financial_data['collection_rate'] >= 85 ? 'Excellent' : ($financial_data['collection_rate'] >= 70 ? 'Good' : 'Needs Improvement'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Payments -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon orange">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['pending_amount'], 2); ?></div>
                            <div class="stat-label">Pending Payments</div>
                            <div class="stat-change neutral">
                                <i class="fas fa-hourglass-half me-1"></i>
                                <?php echo $financial_data['pending_payments']; ?> Payments
                            </div>
                        </div>
                    </div>
                    
                    <!-- Late Payments -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon red">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['late_amount'], 2); ?></div>
                            <div class="stat-label">Late Payments</div>
                            <div class="stat-change negative">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo $financial_data['late_payments']; ?> Payments
                            </div>
                        </div>
                    </div>
                    
                    <!-- Admin Revenue -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon teal">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['admin_revenue'], 2); ?></div>
                            <div class="stat-label">Admin Revenue</div>
                            <div class="stat-change positive">
                                <i class="fas fa-dollar-sign me-1"></i>
                                From Completed Payouts
                            </div>
                        </div>
                    </div>
                    
                    <!-- Average Payment -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($financial_data['avg_payment'], 2); ?></div>
                            <div class="stat-label">Average Payment</div>
                            <div class="stat-change neutral">
                                <i class="fas fa-chart-bar me-1"></i>
                                Per Transaction
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scheduled Payouts -->
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-number"><?php echo $financial_data['scheduled_payouts']; ?></div>
                            <div class="stat-label">Scheduled Payouts</div>
                            <div class="stat-change neutral">
                                <i class="fas fa-calendar me-1"></i>
                                Upcoming
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <!-- No Data Message -->
                <div class="alert alert-warning border-0" style="border-radius: 16px;">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No Financial Data Found
                    </h5>
                    <p class="mb-0">
                        <?php if ($selected_equb_id): ?>
                            No payments or payouts found for the selected EQUB. Data will appear here once payments are recorded.
                        <?php else: ?>
                            No payments or payouts found in the system. Data will appear here once payments are recorded.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="row">
                    <!-- Monthly Trends Chart -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-line text-teal"></i>
                                Monthly Payment Trends
                            </h3>
                            <div class="chart-container">
                                <canvas id="monthlyTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Methods Chart -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h3 class="chart-title">
                                <i class="fas fa-credit-card text-purple"></i>
                                Payment Methods Breakdown
                            </h3>
                            <div class="chart-container">
                                <canvas id="paymentMethodsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Status Chart -->
                    <div class="col-lg-12">
                        <div class="chart-card">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-pie text-gold"></i>
                                Payment Status Distribution
                            </h3>
                            <div class="chart-container">
                                <canvas id="paymentStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Chart data from PHP
        const monthlyTrendsData = <?php echo json_encode($monthly_trends); ?>;
        const paymentMethodsData = <?php echo json_encode($payment_methods); ?>;
        const paymentStatusData = <?php echo json_encode($payment_status); ?>;

        // Monthly Trends Chart
        if (monthlyTrendsData.length > 0) {
            const monthlyCtx = document.getElementById('monthlyTrendsChart');
            if (monthlyCtx) {
                const months = monthlyTrendsData.map(t => {
                    const date = new Date(t.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                });
                const collected = monthlyTrendsData.map(t => parseFloat(t.collected_amount || 0));
                const pending = monthlyTrendsData.map(t => parseFloat(t.pending_amount || 0));
                
                new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Collected',
                            data: collected,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Pending',
                            data: pending,
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + parseFloat(context.parsed.y).toLocaleString('en-US', {minimumFractionDigits: 2, style: 'currency', currency: 'USD'});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('en-US', {style: 'currency', currency: 'USD', minimumFractionDigits: 0});
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Payment Methods Chart
        if (paymentMethodsData.length > 0) {
            const methodsCtx = document.getElementById('paymentMethodsChart');
            if (methodsCtx) {
                const labels = paymentMethodsData.map(m => {
                    const method = m.payment_method || 'unknown';
                    return method.charAt(0).toUpperCase() + method.slice(1).replace('_', ' ');
                });
                const amounts = paymentMethodsData.map(m => parseFloat(m.total_amount || 0));
                const colors = [
                    'rgba(19, 102, 92, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(233, 196, 106, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                ];
                
                new Chart(methodsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: amounts,
                            backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'bottom',
                                labels: { padding: 15, font: { size: 12 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = parseFloat(context.parsed || 0);
                                        const total = amounts.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return label + ': ' + value.toLocaleString('en-US', {style: 'currency', currency: 'USD', minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Payment Status Chart
        if (paymentStatusData.length > 0) {
            const statusCtx = document.getElementById('paymentStatusChart');
            if (statusCtx) {
                const labels = paymentStatusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1));
                const counts = paymentStatusData.map(s => parseInt(s.count || 0));
                const colors = {
                    'Paid': 'rgba(16, 185, 129, 0.8)',
                    'Completed': 'rgba(16, 185, 129, 0.8)',
                    'Pending': 'rgba(245, 158, 11, 0.8)',
                    'Late': 'rgba(239, 68, 68, 0.8)',
                    'Missed': 'rgba(107, 114, 128, 0.8)'
                };
                
                new Chart(statusCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Payment Count',
                            data: counts,
                            backgroundColor: labels.map(l => colors[l] || 'rgba(59, 130, 246, 0.8)'),
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Count: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>

