<?php
/**
 * HabeshaEqub - TOP-TIER FINANCIAL ANALYTICS DASHBOARD
 * Ultra-modern, responsive financial analytics for professional EQUB management
 * Built for top financial firms with comprehensive member payout analysis
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once '../languages/translator.php';

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

// Initialize variables
$equb_data = null;
$financial_summary = [];
$member_payouts = [];
$position_timeline = [];
$admin_revenue = 0;

if ($selected_equb_id) {
    try {
        // Get selected EQUB data
        $stmt = $pdo->prepare("
            SELECT * FROM equb_settings WHERE id = ?
        ");
        $stmt->execute([$selected_equb_id]);
        $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($equb_data) {
            // Initialize enhanced calculator for DYNAMIC calculations
            $calculator = new EnhancedEqubCalculator($pdo);
            
            // Get REAL-TIME EQUB calculations (NO HARDCODE!)
            $equb_calculation = $calculator->calculateEqubPositions($selected_equb_id);
            
            if (!$equb_calculation['success']) {
                error_log("Enhanced calculator failed: " . $equb_calculation['message']);
                $equb_calculation = [
                    'total_monthly_pool' => 0,
                    'total_positions' => 0,
                    'individual_positions' => 0,
                    'joint_groups' => 0
                ];
            }
            
            // Get POSITION-BASED member data (joint groups as single entities)
            $stmt = $pdo->prepare("
                SELECT 
                    CASE 
                        WHEN m.membership_type = 'joint' THEN CONCAT('joint_', m.joint_group_id)
                        ELSE CONCAT('individual_', m.id)
                    END as position_key,
                    CASE 
                        WHEN m.membership_type = 'joint' THEN jmg.group_name
                        ELSE CONCAT(m.first_name, ' ', m.last_name)
                    END as display_name,
                    CASE 
                        WHEN m.membership_type = 'joint' THEN jmg.payout_position
                        ELSE m.payout_position
                    END as payout_position,
                    CASE 
                        WHEN m.membership_type = 'joint' THEN 'joint'
                        ELSE 'individual'
                    END as membership_type,
                    CASE 
                        WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                        ELSE m.monthly_payment
                    END as monthly_payment,
                    CASE 
                        WHEN m.membership_type = 'joint' THEN jmg.position_coefficient
                        ELSE m.position_coefficient
                    END as position_coefficient,
                    GROUP_CONCAT(
                        CONCAT(m.first_name, ' ', m.last_name, 
                               CASE WHEN m.primary_joint_member = 1 THEN ' (Primary)' ELSE '' END)
                        ORDER BY m.primary_joint_member DESC, m.created_at ASC
                        SEPARATOR ', '
                    ) as member_names,
                    COUNT(m.id) as member_count,
                    MIN(m.id) as primary_member_id,
                    SUM(COALESCE(p.amount, 0)) as total_contributed,
                    MIN(m.has_received_payout) as has_received_payout,
                    MIN(m.join_date) as join_date
                FROM members m
                LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
                LEFT JOIN payments p ON m.id = p.member_id AND p.status IN ('paid', 'completed')
                WHERE m.equb_settings_id = ? AND m.is_active = 1
                GROUP BY position_key, payout_position, display_name, membership_type, monthly_payment, position_coefficient
                ORDER BY payout_position ASC, MIN(m.id) ASC
            ");
            $stmt->execute([$selected_equb_id]);
            $position_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate detailed payouts for each position
            foreach ($position_data as $position) {
                if ($position['payout_position'] > 0) {
                    $calculation = $calculator->calculateMemberFriendlyPayout($position['primary_member_id']);
                    
                    if ($calculation['success']) {
                        // Calculate payout date
                        $payout_date = null;
                        if ($equb_data['start_date'] && $position['payout_position']) {
                            $start_date = new DateTime($equb_data['start_date']);
                            $payout_date = clone $start_date;
                            $payout_date->modify('+' . ($position['payout_position'] - 1) . ' months');
                            $payout_date->setDate(
                                $payout_date->format('Y'),
                                $payout_date->format('n'),
                                $equb_data['payout_day'] ?: 5
                            );
                        }
                        
                        $member_payouts[] = [
                            'position_key' => $position['position_key'],
                            'display_name' => $position['display_name'],
                            'membership_type' => $position['membership_type'],
                            'member_names' => $position['member_names'],
                            'member_count' => $position['member_count'],
                            'payout_position' => $position['payout_position'],
                            'position_coefficient' => $position['position_coefficient'],
                            'monthly_payment' => $position['monthly_payment'],
                            'total_contributions' => $position['monthly_payment'] * $equb_data['duration_months'], // Dynamic from DB
                            'gross_payout' => $calculation['calculation']['gross_payout'],
                            'admin_fee' => $calculation['calculation']['admin_fee'],
                            'net_payout' => $calculation['calculation']['display_payout'],
                            'total_contributed' => $position['total_contributed'],
                            'has_received_payout' => $position['has_received_payout'],
                            'payout_date' => $payout_date ? $payout_date->format('M d, Y') : 'TBD',
                            'payout_month' => $payout_date ? $payout_date->format('M Y') : 'TBD',
                            'join_date' => $position['join_date']
                        ];
                        
                        $admin_revenue += $calculation['calculation']['admin_fee'];
                    }
                }
            }
            
            // Calculate DYNAMIC financial summary (NO HARDCODE!)
            $total_expected_contributions = array_sum(array_column($member_payouts, 'total_contributions'));
            $total_paid_contributions = array_sum(array_column($member_payouts, 'total_contributed'));
            $total_net_payouts = array_sum(array_column($member_payouts, 'net_payout'));
            $total_positions = count($member_payouts);
            $completed_payouts = count(array_filter($member_payouts, fn($p) => $p['has_received_payout']));
            
            // DYNAMIC VALUES from enhanced calculator
            $real_monthly_pool = $equb_calculation['total_monthly_pool'] ?? 0;
            $real_total_pool = $real_monthly_pool * $equb_data['duration_months'];
            $real_positions = $equb_calculation['total_positions'] ?? 0;
            $real_individual_positions = $equb_calculation['individual_positions'] ?? 0;
            $real_joint_groups = $equb_calculation['joint_groups'] ?? 0;
            
            $financial_summary = [
                // REAL-TIME calculations from database
                'monthly_pool' => $real_monthly_pool,
                'total_pool_value' => $real_total_pool,
                'duration_months' => $equb_data['duration_months'],
                'admin_fee_rate' => $equb_data['admin_fee'],
                
                // Position analysis
                'total_positions' => $real_positions,
                'individual_positions' => $real_individual_positions,
                'joint_positions' => $real_joint_groups,
                'calculated_positions' => $equb_data['calculated_positions'],
                
                // Financial metrics
                'total_expected_contributions' => $total_expected_contributions,
                'total_paid_contributions' => $total_paid_contributions,
                'collection_percentage' => $total_expected_contributions > 0 ? ($total_paid_contributions / $total_expected_contributions) * 100 : 0,
                'total_net_payouts' => $total_net_payouts,
                'admin_revenue' => $admin_revenue,
                'completed_payouts' => $completed_payouts,
                'remaining_payouts' => $real_positions - $completed_payouts,
                
                // Additional analytics
                'average_payout' => $real_positions > 0 ? $real_monthly_pool : 0,
                'total_admin_revenue_potential' => $real_positions * $equb_data['admin_fee'],
                'equb_efficiency' => $total_expected_contributions > 0 ? ($total_paid_contributions / $total_expected_contributions) * 100 : 0
            ];
            
            // Create position timeline
            for ($month = 1; $month <= $equb_data['duration_months']; $month++) {
                $position_members = array_filter($member_payouts, fn($p) => $p['payout_position'] == $month);
                $month_date = null;
                
                if ($equb_data['start_date']) {
                    $start_date = new DateTime($equb_data['start_date']);
                    $month_date = clone $start_date;
                    $month_date->modify('+' . ($month - 1) . ' months');
                    $month_date->setDate(
                        $month_date->format('Y'),
                        $month_date->format('n'),
                        $equb_data['payout_day'] ?: 5
                    );
                }
                
                $position_timeline[] = [
                    'month' => $month,
                    'date' => $month_date ? $month_date->format('M d, Y') : 'TBD',
                    'month_year' => $month_date ? $month_date->format('M Y') : "Month $month",
                    'members' => $position_members,
                    'total_payout' => array_sum(array_column($position_members, 'net_payout')),
                    'admin_fee' => array_sum(array_column($position_members, 'admin_fee')),
                    'is_current' => $month_date ? ($month_date->format('Y-m') === date('Y-m')) : false,
                    'is_past' => $month_date ? ($month_date < new DateTime()) : false
                ];
            }
        }
        
    } catch (Exception $e) {
        error_log("Error in financial analytics: " . $e->getMessage());
    }
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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* ===== TOP-TIER FINANCIAL DASHBOARD STYLES ===== */
        
        body {
            overflow-x: hidden;
        }
        
        .admin-container {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .analytics-header {
            background: linear-gradient(135deg, 
                var(--color-purple) 0%, 
                var(--darker-purple) 50%, 
                var(--color-purple) 100%);
            color: var(--white);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .analytics-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .analytics-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .analytics-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .equb-selector-card {
            background: rgba(255,255,255,0.15);
            border-radius: 15px;
            padding: 25px;
            backdrop-filter: blur(10px);
        }
        
        .equb-select {
            background: var(--white);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--darker-purple);
            min-width: 300px;
        }
        
        /* Financial Metrics Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .metric-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 45px rgba(48, 25, 67, 0.1);
            border: 1px solid var(--border-light);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--light-gold));
        }
        
        .metric-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 60px rgba(48, 25, 67, 0.2);
        }
        
        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .metric-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--darker-purple);
            margin-bottom: 8px;
            line-height: 1;
        }
        
        .metric-label {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .metric-change {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .change-positive {
            background: linear-gradient(135deg, #10B981, #34D399);
            color: white;
        }
        
        .change-negative {
            background: linear-gradient(135deg, #EF4444, #F87171);
            color: white;
        }
        
        .change-neutral {
            background: linear-gradient(135deg, #6B7280, #9CA3AF);
            color: white;
        }
        
        /* Member Payout Table */
        .payout-table-container {
            background: var(--white);
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 15px 45px rgba(48, 25, 67, 0.1);
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-light);
        }
        
        .table-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--darker-purple);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .table-actions {
            display: flex;
            gap: 10px;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(48, 25, 67, 0.1);
        }
        
        .modern-table thead th {
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
            color: var(--white);
            padding: 18px 20px;
            font-weight: 600;
            text-align: left;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }
        
        .modern-table tbody tr {
            background: var(--white);
            transition: all 0.3s ease;
        }
        
        .modern-table tbody tr:nth-child(even) {
            background: rgba(248, 250, 252, 0.8);
        }
        
        .modern-table tbody tr:hover {
            background: linear-gradient(135deg, rgba(216, 180, 254, 0.1), rgba(196, 181, 253, 0.1));
            transform: scale(1.01);
            box-shadow: 0 8px 25px rgba(48, 25, 67, 0.15);
        }
        
        .modern-table tbody td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }
        
        .position-badge {
            background: linear-gradient(135deg, var(--gold), var(--light-gold));
            color: var(--white);
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            text-align: center;
            min-width: 50px;
            display: inline-block;
        }
        
        .member-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .member-name {
            font-weight: 600;
            color: var(--darker-purple);
            font-size: 1.05rem;
        }
        
        .member-type {
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 500;
            display: inline-block;
            width: fit-content;
        }
        
        .type-individual {
            background: linear-gradient(135deg, #3B82F6, #60A5FA);
            color: white;
        }
        
        .type-joint {
            background: linear-gradient(135deg, #8B5CF6, #A78BFA);
            color: white;
        }
        
        .amount-display {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .amount-positive {
            color: #059669;
        }
        
        .amount-neutral {
            color: var(--darker-purple);
        }
        
        .amount-fee {
            color: #DC2626;
        }
        
        .payout-date {
            background: linear-gradient(135deg, var(--color-cream), #FAF8F5);
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 600;
            color: var(--darker-purple);
            border: 1px solid var(--border-light);
        }
        
        /* Timeline Section */
        .timeline-container {
            background: var(--white);
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 15px 45px rgba(48, 25, 67, 0.1);
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
        }
        
        .timeline-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .timeline-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--darker-purple);
            margin-bottom: 10px;
        }
        
        .timeline-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .timeline-month {
            background: var(--white);
            border: 2px solid var(--border-light);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .timeline-month.current {
            border-color: var(--gold);
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.1));
            transform: scale(1.05);
        }
        
        .timeline-month.past {
            opacity: 0.7;
            background: rgba(248, 250, 252, 0.8);
        }
        
        .timeline-month:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(48, 25, 67, 0.15);
        }
        
        .month-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-light);
        }
        
        .month-number {
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .month-date {
            font-weight: 600;
            color: var(--darker-purple);
        }
        
        /* Charts Container */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
            overflow: hidden;
        }
        
        .chart-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 45px rgba(48, 25, 67, 0.1);
            border: 1px solid var(--border-light);
            height: 400px;
            position: relative;
        }
        
        .chart-card canvas {
            max-height: 300px !important;
            width: 100% !important;
            height: auto !important;
        }
        
        .chart-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--darker-purple);
            margin-bottom: 25px;
            text-align: center;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 1200px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .analytics-header {
                padding: 25px;
                text-align: center;
            }
            
            .analytics-title {
                font-size: 2rem;
            }
            
            .equb-selector-card {
                padding: 20px;
            }
            
            .equb-select {
                min-width: 100%;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .metric-card {
                padding: 25px;
            }
            
            .metric-value {
                font-size: 1.8rem;
            }
            
            .payout-table-container {
                padding: 20px;
                overflow-x: auto;
            }
            
            .table-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .table-actions {
                width: 100%;
                justify-content: center;
            }
            
            .modern-table {
                min-width: 800px;
            }
            
            .timeline-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .analytics-header {
                padding: 20px;
            }
            
            .analytics-title {
                font-size: 1.7rem;
            }
            
            .metric-card {
                padding: 20px;
            }
            
            .payout-table-container {
                padding: 15px;
            }
            
            .timeline-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <!-- Analytics Header -->
        <div class="analytics-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="analytics-title">
                        <i class="fas fa-chart-line me-3"></i>
                        Financial Analytics Dashboard
                    </h1>
                    <p class="analytics-subtitle">
                        <strong>ENHANCED DYNAMIC ANALYTICS</strong> - Real-time calculations from database with NO hardcoded values!
                        <br>Comprehensive financial insights and member payout analysis for professional EQUB management
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="equb-selector-card">
                        <label class="form-label text-white mb-3">
                            <i class="fas fa-filter me-2"></i>Select EQUB Term
                        </label>
                        <select class="form-select equb-select" onchange="window.location.href='?equb_id=' + this.value">
                            <option value="">Choose EQUB Term...</option>
                            <?php foreach ($all_equbs as $equb): ?>
                                <option value="<?php echo $equb['id']; ?>" <?php echo ($equb['id'] == $selected_equb_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($equb['equb_name']); ?> 
                                    (<?php echo ucfirst($equb['status']); ?>)
                                    <?php if ($equb['start_date']): ?>
                                        - <?php echo date('M Y', strtotime($equb['start_date'])); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($equb_data && !empty($member_payouts)): ?>
            <!-- Financial Metrics Grid -->
            <div class="metrics-grid">
                <!-- Total Positions -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #3B82F6, #60A5FA); color: white;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div id="metric-total-positions" class="metric-value"><?php echo $financial_summary['total_positions'] ?? 0; ?></div>
                    <div class="metric-label">Total Positions</div>
                    <div class="metric-change change-neutral">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $financial_summary['individual_positions']; ?> Individual + 
                        <?php echo $financial_summary['joint_positions']; ?> Joint
                    </div>
                </div>

                <!-- Expected Pool -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #10B981, #34D399); color: white;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div id="metric-expected-total" class="metric-value">£<?php echo isset($financial_summary['total_expected_contributions']) ? number_format($financial_summary['total_expected_contributions'], 0) : '0'; ?></div>
                    <div class="metric-label">Expected Total Pool</div>
                    <div class="metric-change change-positive">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo $equb_data['duration_months']; ?> months
                    </div>
                </div>

                <!-- Collection Rate -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA); color: white;">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div id="metric-collection-rate" class="metric-value"><?php echo isset($financial_summary['collection_percentage']) ? number_format($financial_summary['collection_percentage'], 1) : '0'; ?>%</div>
                    <div class="metric-label">Collection Rate</div>
                    <div class="metric-change <?php echo $financial_summary['collection_percentage'] >= 80 ? 'change-positive' : 'change-negative'; ?>">
                        <i class="fas fa-pound-sign me-1"></i>
                        £<?php echo number_format($financial_summary['total_paid_contributions'], 0); ?> collected
                    </div>
                </div>

                <!-- Outstanding Balance -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #ef4444, #f87171); color: white;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div id="metric-outstanding" class="metric-value">£0</div>
                    <div class="metric-label">Outstanding Balance</div>
                    <div class="metric-change change-negative">
                        <i class="fas fa-users me-1"></i>
                        <span id="metric-overdue-members">0</span> overdue members
                    </div>
                </div>

                <!-- Current Month Collection -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8); color: white;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div id="metric-current-month" class="metric-value">£0</div>
                    <div class="metric-label">Current Month Collected</div>
                    <div class="metric-change change-neutral">
                        <i class="fas fa-bullseye me-1"></i>
                        Target: <span id="metric-current-target">£0</span>
                    </div>
                </div>

                <!-- Admin Revenue -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #F59E0B, #FCD34D); color: white;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div id="metric-admin-revenue" class="metric-value">£<?php echo isset($financial_summary['admin_revenue']) ? number_format($financial_summary['admin_revenue'], 0) : '0'; ?></div>
                    <div class="metric-label">Total Admin Revenue</div>
                    <div class="metric-change change-positive">
                        <i class="fas fa-calculator me-1"></i>
                        <?php echo number_format($equb_data['admin_fee'], 1); ?>% fee rate
                    </div>
                </div>

                <!-- Payout Progress -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #EF4444, #F87171); color: white;">
                        <i class="fas fa-money-bill-transfer"></i>
                    </div>
                    <div id="metric-payouts-completed" class="metric-value"><?php echo ($financial_summary['completed_payouts'] ?? 0) . '/' . ($financial_summary['total_positions'] ?? 0); ?></div>
                    <div class="metric-label">Payouts Completed</div>
                    <div class="metric-change change-neutral">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo $financial_summary['remaining_payouts']; ?> remaining
                    </div>
                </div>

                <!-- Net Payouts -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #059669, #10B981); color: white;">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                    <div id="metric-total-net-payouts" class="metric-value">£<?php echo isset($financial_summary['total_net_payouts']) ? number_format($financial_summary['total_net_payouts'], 0) : '0'; ?></div>
                    <div class="metric-label">Total Net Payouts</div>
                    <div class="metric-change change-positive">
                        <i class="fas fa-minus me-1"></i>
                        After admin fees
                    </div>
                </div>
                
                <!-- NEW DYNAMIC METRICS FROM ENHANCED CALCULATOR -->
                
                <!-- Monthly Pool (Real-Time) -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #7C3AED, #A855F7); color: white;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div id="metric-monthly-pool" class="metric-value">£<?php echo isset($financial_summary['monthly_pool']) ? number_format($financial_summary['monthly_pool'], 0) : '0'; ?></div>
                    <div class="metric-label">Monthly Pool (Real-Time)</div>
                    <div class="metric-change change-positive">
                        <i class="fas fa-database me-1"></i>
                        From actual contributions
                    </div>
                </div>

                <!-- Total Pool Value (Lifetime) -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #DC2626, #F87171); color: white;">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div id="metric-total-pool" class="metric-value">£<?php echo isset($financial_summary['total_pool_value']) ? number_format($financial_summary['total_pool_value'], 0) : '0'; ?></div>
                    <div class="metric-label">Total Pool Value (Lifetime)</div>
                    <div class="metric-change change-positive">
                        <i class="fas fa-times me-1"></i>
                        £<?php echo number_format($financial_summary['monthly_pool'], 0); ?> × <?php echo $financial_summary['duration_months']; ?> months
                    </div>
                </div>

                <!-- Average Payout Per Position -->
                <div class="metric-card">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #0891B2, #06B6D4); color: white;">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div id="metric-average-payout" class="metric-value">£<?php echo isset($financial_summary['average_payout']) ? number_format($financial_summary['average_payout'], 0) : '0'; ?></div>
                    <div class="metric-label">Average Payout/Position</div>
                    <div class="metric-change change-neutral">
                        <i class="fas fa-equals me-1"></i>
                        Gross amount before fees
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-container">
                <!-- Payout Distribution Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-pie text-purple me-2"></i>
                        Payout Distribution
                    </h3>
                    <canvas id="payoutChart"></canvas>
                    <div class="text-center small text-muted mt-2" id="payoutChartSummary"></div>
                </div>

                <!-- Monthly Timeline Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-bar text-gold me-2"></i>
                        Monthly Payout Timeline
                    </h3>
                    <canvas id="timelineChart"></canvas>
                    <div class="text-center small text-muted mt-2" id="timelineChartSummary"></div>
                </div>
            </div>

            <!-- Inflows chart -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-wallet text-teal me-2"></i>
                    Monthly Inflows (Payments)
                </h3>
                <canvas id="inflowChart"></canvas>
            </div>

            <!-- Member Payout Analysis Table -->
            <div class="payout-table-container">
                <div class="table-header">
                    <h2 class="table-title">
                        <i class="fas fa-table text-purple"></i>
                        Detailed Member Payout Analysis
                    </h2>
                    <div class="table-actions">
                        <button class="btn btn-outline-primary" onclick="exportToCSV()">
                            <i class="fas fa-download me-1"></i>Export CSV
                        </button>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print Report
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Member/Group</th>
                                <th>Type</th>
                                <th>Monthly Payment</th>
                                <th>Total Contributions</th>
                                <th>Gross Payout</th>
                                <th>Admin Fee</th>
                                <th>Net Payout</th>
                                <th>Payout Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($member_payouts as $payout): ?>
                                <tr>
                                    <td>
                                        <span class="position-badge"><?php echo $payout['payout_position']; ?></span>
                                    </td>
                                    <td>
                                        <div class="member-info">
                                            <div class="member-name"><?php echo htmlspecialchars($payout['display_name']); ?></div>
                                            <?php if ($payout['membership_type'] === 'joint'): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($payout['member_names']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="member-type <?php echo $payout['membership_type'] === 'joint' ? 'type-joint' : 'type-individual'; ?>">
                                            <?php if ($payout['membership_type'] === 'joint'): ?>
                                                <i class="fas fa-users me-1"></i>Joint (<?php echo $payout['member_count']; ?>)
                                            <?php else: ?>
                                                <i class="fas fa-user me-1"></i>Individual
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount-display amount-neutral">
                                            £<?php echo number_format($payout['monthly_payment'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount-display amount-positive">
                                            £<?php echo number_format($payout['total_contributions'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount-display amount-positive">
                                            £<?php echo number_format($payout['gross_payout'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount-display amount-fee">
                                            -£<?php echo number_format($payout['admin_fee'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount-display amount-positive">
                                            <strong>£<?php echo number_format($payout['net_payout'], 2); ?></strong>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="payout-date">
                                            <?php echo $payout['payout_date']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($payout['has_received_payout']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Completed
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Debtors and Upcoming Payouts -->
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="payout-table-container">
                        <div class="table-header">
                            <h2 class="table-title"><i class="fas fa-user-minus text-danger"></i> Top Debtors</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Code</th>
                                        <th>Remaining Months</th>
                                    </tr>
                                </thead>
                                <tbody id="tbl-top-debtors">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="payout-table-container">
                        <div class="table-header">
                            <h2 class="table-title"><i class="fas fa-hourglass-half text-warning"></i> Upcoming Payouts</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Scheduled Date</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="tbl-upcoming-payouts">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payout Timeline -->
            <div class="timeline-container">
                <div class="timeline-header">
                    <h2 class="timeline-title">
                        <i class="fas fa-calendar-alt text-purple me-3"></i>
                        Payout Schedule Timeline
                    </h2>
                    <p class="timeline-subtitle">
                        Monthly payout schedule for <?php echo htmlspecialchars($equb_data['equb_name']); ?>
                    </p>
                </div>

                <div class="timeline-grid">
                    <?php foreach ($position_timeline as $month): ?>
                        <div class="timeline-month <?php echo $month['is_current'] ? 'current' : ($month['is_past'] ? 'past' : ''); ?>">
                            <div class="month-header">
                                <div class="month-number"><?php echo $month['month']; ?></div>
                                <div class="month-date"><?php echo $month['date']; ?></div>
                            </div>
                            
                            <?php if (!empty($month['members'])): ?>
                                <div class="mb-3">
                                    <strong class="text-purple">Recipients:</strong>
                                    <?php foreach ($month['members'] as $member): ?>
                                        <div class="small mt-1">
                                            <i class="fas fa-<?php echo $member['membership_type'] === 'joint' ? 'users' : 'user'; ?> me-1"></i>
                                            <?php echo htmlspecialchars($member['display_name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="small text-muted">Total Payout</div>
                                        <div class="fw-bold text-success">£<?php echo number_format($month['total_payout'], 0); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted">Admin Fee</div>
                                        <div class="fw-bold text-danger">£<?php echo number_format($month['admin_fee'], 0); ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <div>No payouts scheduled</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-chart-line fa-5x text-muted mb-4"></i>
                    <h3 class="text-muted">No Financial Data Available</h3>
                    <p class="text-muted mb-4">
                        <?php if (empty($all_equbs)): ?>
                            No EQUB terms have been created yet.
                        <?php elseif (!$selected_equb_id): ?>
                            Please select an EQUB term to view financial analytics.
                        <?php else: ?>
                            No members found for the selected EQUB term.
                        <?php endif; ?>
                    </p>
                    <?php if (empty($all_equbs)): ?>
                        <a href="equb-management.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create EQUB Term
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Chart.js Configuration
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
        Chart.defaults.color = '#6B7280';

        // Payout Distribution Pie Chart (initialized empty, filled by API)
        const payoutCtx = document.getElementById('payoutChart').getContext('2d');
        let payoutChartInstance = new Chart(payoutCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Individual Members', 
                    'Joint Groups', 
                    'Admin Revenue'
                ],
                datasets: [{
                    data: [0,0,0],
                    backgroundColor: [
                        '#3B82F6',
                        '#8B5CF6',
                        '#F59E0B'
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxHeight: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': £' + context.parsed.toLocaleString();
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Monthly Timeline Bar Chart (initialized empty)
        const timelineCtx = document.getElementById('timelineChart').getContext('2d');
        let timelineChartInstance = new Chart(timelineCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Net Payouts',
                    data: [],
                    backgroundColor: '#10B981',
                    borderColor: '#059669',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }, {
                    label: 'Admin Fees',
                    data: [],
                    backgroundColor: '#EF4444',
                    borderColor: '#DC2626',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '£' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxHeight: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': £' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Export to CSV functionality
        function exportToCSV() {
            const table = document.querySelector('.modern-table');
            const rows = Array.from(table.querySelectorAll('tr'));
            
            const csvContent = rows.map(row => {
                const cols = Array.from(row.querySelectorAll('th, td'));
                return cols.map(col => {
                    let text = col.textContent.trim();
                    // Remove special characters and clean up
                    text = text.replace(/[\n\r\t]/g, ' ').replace(/\s+/g, ' ');
                    return '"' + text.replace(/"/g, '""') + '"';
                }).join(',');
            }).join('\n');
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'financial-analytics-<?php echo date('Y-m-d'); ?>.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Live data: fetch analytics via API
        async function loadAnalytics() {
            try {
                const params = new URLSearchParams({ action: 'summary', equb_id: '<?php echo (int)$selected_equb_id; ?>', _t: Date.now() });
                const res = await fetch('api/analytics.php?' + params, { cache: 'no-store' });
                const d = await res.json();
                if (!d || !d.success) return;

                // Update metrics
                const fmt = n => '£' + Number(n||0).toLocaleString();
                const el = (id,v) => { const e=document.getElementById(id); if(e) e.textContent=v; };
                el('metric-total-positions', d.summary.total_positions);
                el('metric-expected-total', fmt(d.summary.expected_total));
                el('metric-collection-rate', (Number(d.summary.collection_rate||0).toFixed(1)) + '%');
                el('metric-admin-revenue', fmt(d.summary.admin_revenue_collected));
                el('metric-payouts-completed', `${d.summary.payouts_completed}/${d.summary.total_positions}`);
                el('metric-total-net-payouts', fmt(d.summary.net_payouts_total));
                el('metric-monthly-pool', fmt(d.summary.monthly_pool));
                el('metric-total-pool', fmt(d.summary.total_pool_value));
                el('metric-average-payout', fmt(d.summary.average_payout));
                // New KPIs
                el('metric-outstanding', fmt(d.summary.outstanding_balance));
                el('metric-overdue-members', d.summary.overdue_members);
                el('metric-current-month', fmt(d.summary.collected_current_month));
                el('metric-current-target', fmt(d.summary.current_month_target));

                // Update charts
                payoutChartInstance.data.datasets[0].data = [
                    d.charts.payout_distribution.individual,
                    d.charts.payout_distribution.joint,
                    d.charts.payout_distribution.admin_revenue
                ];
                payoutChartInstance.update();
                const pSum = d.charts.payout_distribution;
                const pcs = document.getElementById('payoutChartSummary');
                if (pcs) pcs.textContent = `Individuals: ${fmt(pSum.individual)} • Joints: ${fmt(pSum.joint)} • Admin: ${fmt(pSum.admin_revenue)}`;

                timelineChartInstance.data.labels = d.charts.timeline.labels;
                timelineChartInstance.data.datasets[0].data = d.charts.timeline.net_payouts;
                timelineChartInstance.data.datasets[1].data = d.charts.timeline.admin_fees;
                timelineChartInstance.update();
                const tcs = document.getElementById('timelineChartSummary');
                if (tcs) tcs.textContent = `Period: ${d.charts.timeline.labels[0] || ''} — ${d.charts.timeline.labels.at(-1) || ''}`;

                // Inflows
                const inflowCanvas = document.getElementById('inflowChart');
                if (inflowCanvas && window.Chart) {
                    if (!window._inflowChart) {
                        window._inflowChart = new Chart(inflowCanvas.getContext('2d'), {
                            type: 'line',
                            data: { labels: [], datasets: [{ label: 'Payments', data: [], borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.15)', tension: 0.3, borderWidth: 2, fill: true }] },
                            options: { responsive:true, maintainAspectRatio:true, aspectRatio:2, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true, ticks:{ callback:v=>'£'+Number(v).toLocaleString() } } } }
                        });
                    }
                    window._inflowChart.data.labels = d.charts.inflows.labels;
                    window._inflowChart.data.datasets[0].data = d.charts.inflows.payments;
                    window._inflowChart.update();
                }

                // Tables
                const td = document.getElementById('tbl-top-debtors');
                if (td) {
                    td.innerHTML = d.tables.top_debtors.map(r => `<tr><td>${r.name}</td><td>${r.code||''}</td><td>${r.remaining}</td></tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted">No debtors</td></tr>';
                }
                const up = document.getElementById('tbl-upcoming-payouts');
                if (up) {
                    up.innerHTML = d.tables.upcoming_payouts.map(r => `<tr><td>${r.name}</td><td>${r.scheduled_date}</td><td>£${Number(r.amount).toLocaleString()}</td></tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted">No upcoming payouts</td></tr>';
                }
            } catch(e) { console.error('Analytics load failed', e); }
        }

        loadAnalytics();
        setInterval(loadAnalytics, 45000); // refresh every 45s
    </script>

</body>
</html>