<?php
/**
 * HabeshaEqub - Members Management Page
 * Admin interface for managing equib members
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Generate CSRF token for form security
$csrf_token = generate_csrf_token();

// Get members data with CORRECT joint group logic
try {
    $stmt = $pdo->query("
        SELECT m.*, 
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.payout_position
                   ELSE m.payout_position
               END as actual_payout_position,
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                   ELSE m.monthly_payment
               END as effective_monthly_payment,
               jmg.group_name, jmg.payout_split_method,
               COUNT(p.id) as total_payments,
               COALESCE(SUM(p.amount), 0) as total_paid
        FROM members m 
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        LEFT JOIN payments p ON m.id = p.member_id AND p.status = 'completed'
        GROUP BY m.id 
        ORDER BY 
            CASE 
                WHEN m.membership_type = 'joint' THEN jmg.payout_position
                ELSE m.payout_position
            END ASC, m.created_at DESC
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate DYNAMIC payouts using enhanced calculator (NO HARDCODE!)
    $calculator = getEnhancedEqubCalculator();
    foreach ($members as &$member) {
        $payout_calc = $calculator->calculateMemberFriendlyPayout($member['id']);
        if ($payout_calc['success']) {
            // Store BOTH display and real net amounts for flexibility
            $member['display_payout'] = $payout_calc['calculation']['display_payout']; // Member-friendly
            $member['real_net_payout'] = $payout_calc['calculation']['real_net_payout']; // Actual amount
            $member['gross_payout'] = $payout_calc['calculation']['gross_payout']; // Before deductions
            $member['admin_fee'] = $payout_calc['calculation']['admin_fee']; // Admin fee
            $member['monthly_deduction'] = $payout_calc['calculation']['monthly_deduction']; // Monthly payment
            $member['position_coefficient'] = $payout_calc['calculation']['position_coefficient']; // Position coefficient
            $member['payout_calculation'] = $payout_calc;
            
            // Keep backward compatibility
            $member['expected_payout'] = $payout_calc['calculation']['display_payout'];
        } else {
            $member['display_payout'] = 0;
            $member['real_net_payout'] = 0;
            $member['gross_payout'] = 0;
            $member['admin_fee'] = 0;
            $member['monthly_deduction'] = 0;
            $member['position_coefficient'] = 0;
            $member['expected_payout'] = 0;
            $member['payout_calculation'] = null;
        }
    }
    unset($member); // Clean up reference
    
} catch (PDOException $e) {
    error_log("Error fetching members: " . $e->getMessage());
    $members = [];
}

// ENHANCED member counting logic with validation
$total_individual_members = count(array_filter($members, fn($m) => $m['membership_type'] === 'individual'));
$total_joint_members = count(array_filter($members, fn($m) => $m['membership_type'] === 'joint'));
$unique_joint_groups = count(array_unique(array_filter(array_column($members, 'joint_group_id'))));
$total_positions = $total_individual_members + $unique_joint_groups;
$active_members = count(array_filter($members, fn($m) => $m['is_active']));
$inactive_members = count(array_filter($members, fn($m) => !$m['is_active']));
$approved_members = count(array_filter($members, fn($m) => $m['is_approved']));
$pending_approval = count(array_filter($members, fn($m) => !$m['is_approved']));
$completed_payouts = count(array_filter($members, fn($m) => $m['has_received_payout']));
$pending_payouts = count($members) - $completed_payouts;
$total_members = count($members);

// Calculate DYNAMIC financial metrics (NO HARDCODE!)
$total_monthly_contributions = array_sum(array_column($members, 'effective_monthly_payment'));
$total_contributions_received = array_sum(array_column($members, 'total_paid'));
$average_payment = $total_members > 0 ? $total_contributions_received / $total_members : 0;

// ENHANCED payout calculations using real amounts
$total_display_payouts = array_sum(array_column($members, 'display_payout'));
$total_real_net_payouts = array_sum(array_column($members, 'real_net_payout'));
$total_gross_payouts = array_sum(array_column($members, 'gross_payout'));
$total_admin_fees = array_sum(array_column($members, 'admin_fee'));
$total_monthly_deductions = array_sum(array_column($members, 'monthly_deduction'));

// Get EQUB-wide calculations from enhanced calculator
$active_equb_ids = array_unique(array_filter(array_column($members, 'equb_settings_id')));
$equb_totals = [];
foreach ($active_equb_ids as $equb_id) {
    $equb_calc = $calculator->calculateEqubPositions($equb_id);
    if ($equb_calc['success']) {
        $equb_totals[$equb_id] = $equb_calc;
    }
}

// Member activity metrics  
$recent_joiners = count(array_filter($members, fn($m) => strtotime($m['created_at']) > strtotime('-30 days')));
$never_paid = count(array_filter($members, fn($m) => floatval($m['total_paid']) == 0));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('members.page_title'); ?> - HabeshaEqub Admin</title>
    
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
        /* === SUPER CLEAN MODERN MEMBERS PAGE DESIGN === */
        
        /* Variables for cleaner code */
        :root {
            --border-light: #E5DDD1;
            --text-secondary: #5A4A6B;
        }
        
        /* Page Header - Modern & Clean */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px 48px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(19, 102, 92, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .page-title-section {
            position: relative;
            z-index: 1;
        }
        
        .page-title-section h1 {
            font-size: 36px;
            font-weight: 800;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            letter-spacing: -0.8px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .page-title-section h1 svg {
            color: var(--color-teal);
        }
        
        .page-subtitle {
            font-size: 16px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
            line-height: 1.5;
        }
        
        .page-subtitle strong {
            color: var(--color-teal);
            font-weight: 700;
        }
        
        .page-actions {
            display: flex;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .page-actions .btn {
            padding: 14px 28px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            box-shadow: 0 4px 16px rgba(19, 102, 92, 0.2);
            font-size: 15px;
        }

        .btn-add-member {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%);
            color: white;
        }

        .btn-add-member:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(19, 102, 92, 0.35);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(233, 196, 106, 0.35);
            color: white;
        }

        /* Statistics Dashboard - Clean Cards */
        .stats-dashboard {
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(19, 102, 92, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(48, 25, 67, 0.15);
            border-color: var(--color-teal);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .total-members .stat-icon { background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%); }
        .active-members .stat-icon { background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%); }
        .completed-payouts .stat-icon { background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); }
        .financial-stats .stat-icon { background: linear-gradient(135deg, #059669 0%, #047857 100%); }

        .stat-trend {
            font-size: 11px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 8px;
            background: rgba(34, 197, 94, 0.1);
            color: #059669;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            line-height: 1;
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0 0 12px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        /* Search and Filter Section - Modern Clean Design */
        .search-filter-section {
            background: white;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 32px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
        }

        .search-bar {
            position: relative;
            flex: 1;
        }

        .search-input {
            width: 100%;
            padding: 14px 18px 14px 50px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--color-cream);
            font-weight: 500;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--color-teal);
            box-shadow: 0 0 0 4px rgba(19, 102, 92, 0.08);
            background: white;
            transform: translateY(-1px);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 18px;
            pointer-events: none;
        }

        .filter-group {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            background: white;
            color: var(--color-purple);
            font-weight: 500;
            min-width: 150px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            font-size: 14px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--color-teal);
            box-shadow: 0 0 0 4px rgba(19, 102, 92, 0.08);
            background: white;
            transform: translateY(-1px);
        }
        
        .filter-select:hover {
            border-color: var(--color-gold);
        }

        /* Members Table */
        .members-table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
        }

        .table-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .table-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0;
        }

        .members-table {
            width: 100%;
            margin: 0;
        }

        .members-table thead th {
            background: var(--color-cream);
            color: var(--color-purple);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 20px;
            border: none;
            border-bottom: 2px solid var(--border-light);
        }

        .members-table tbody tr {
            border-bottom: 1px solid var(--border-light);
            transition: all 0.2s ease;
        }

        .members-table tbody tr:hover {
            background: rgba(233, 196, 106, 0.05);
        }

        .members-table tbody td {
            padding: 24px 20px;
            vertical-align: middle;
            border: none;
        }
        
        .members-table tbody td:first-child {
            padding-left: 28px;
        }
        
        .members-table tbody tr:last-child {
            border-bottom: none;
        }

        /* Member Info Cell - Enhanced Design */
        .member-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .member-avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 17px;
            box-shadow: 0 4px 12px rgba(19, 102, 92, 0.2);
            transition: all 0.3s ease;
        }
        
        .member-info:hover .member-avatar {
            transform: scale(1.08);
            box-shadow: 0 6px 16px rgba(19, 102, 92, 0.3);
        }

        .member-details .member-name {
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 6px 0;
            font-size: 15px;
            letter-spacing: -0.2px;
        }

        .member-name-link {
            color: var(--color-teal);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .member-name-link:hover {
            color: var(--color-gold);
            text-decoration: underline;
        }

        .member-id {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Contact Info */
        .contact-info .contact-email {
            font-weight: 600;
            color: var(--color-purple);
            margin: 0 0 6px 0;
            font-size: 14px;
        }

        .contact-phone {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }

        /* Payment Info - Enhanced */
        .payment-amount {
            font-size: 20px;
            font-weight: 800;
            color: var(--color-teal);
            margin: 0 0 6px 0;
            letter-spacing: -0.5px;
        }

        .payment-status {
            font-size: 13px;
            margin: 0;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Status Badges - Modern Design */
        .status-badge {
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .status-active {
            background: rgba(34, 197, 94, 0.12);
            color: #059669;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.12);
            color: #DC2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .payout-badge {
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .payout-received {
            background: rgba(34, 197, 94, 0.12);
            color: #059669;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .payout-pending {
            background: rgba(251, 191, 36, 0.12);
            color: #D97706;
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        /* Enhanced Action Buttons - Super Clean Design */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-start;
        }

        .btn-action {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .btn-action i {
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: #2563EB;
        }

        .btn-edit:hover {
            background: #2563EB;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
        }

        .btn-toggle {
            background: rgba(233, 196, 106, 0.15);
            color: var(--color-gold);
        }

        .btn-toggle:hover {
            background: var(--color-gold);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(233, 196, 106, 0.4);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
        }

        .btn-delete:hover {
            background: #DC2626;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }

        /* Enhanced Stat Cards - Secondary Style */
        .stat-card-secondary {
            background: linear-gradient(135deg, rgba(19, 102, 92, 0.03) 0%, rgba(233, 196, 106, 0.03) 100%);
            border: 1px solid rgba(19, 102, 92, 0.15);
        }
        
        .stat-card-secondary .stat-icon {
            background: linear-gradient(135deg, rgba(19, 102, 92, 0.12) 0%, rgba(233, 196, 106, 0.12) 100%);
            color: var(--color-teal);
            box-shadow: 0 2px 8px rgba(19, 102, 92, 0.1);
        }
        
        .stat-card-secondary:hover {
            border-color: var(--color-teal);
        }
        
        .stat-breakdown {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            position: relative;
            z-index: 1;
        }
        
        .stat-breakdown small {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .joint-groups .stat-icon,
        .total-positions .stat-icon {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
        }
        
        .stat-card-secondary .stat-number {
            font-size: 24px;
            color: var(--color-dark-purple);
            background: none;
            -webkit-background-clip: unset;
            -webkit-text-fill-color: unset;
            background-clip: unset;
        }
        
        /* Enhanced Filter Section */
        .search-filter-section {
            background: linear-gradient(135deg, #FFFFFF 0%, var(--color-cream) 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
        }
        
        .advanced-filters {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
            display: none;
        }
        
        .advanced-filters.active {
            display: block;
        }
        
        .filter-toggle {
            background: none;
            border: none;
            color: var(--color-purple);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .filter-toggle:hover {
            color: var(--color-dark-purple);
        }
        
        .filter-toggle i {
            transition: transform 0.3s ease;
        }
        
        .filter-toggle.active i {
            transform: rotate(180deg);
        }
        
        /* Bulk Actions - Modern Design */
        .bulk-actions {
            background: linear-gradient(135deg, rgba(233, 196, 106, 0.08) 0%, rgba(19, 102, 92, 0.08) 100%);
            border: 2px solid var(--color-gold);
            border-radius: 14px;
            padding: 18px 24px;
            margin-bottom: 24px;
            display: none;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 16px rgba(233, 196, 106, 0.15);
        }
        
        .bulk-actions.active {
            display: flex;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .bulk-actions-text {
            color: var(--color-purple);
            font-weight: 700;
            margin: 0;
            font-size: 15px;
        }
        
        .bulk-action-btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: white;
        }
        
        .bulk-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.18);
        }
        
        .bulk-action-btn.activate {
            background: rgba(34, 197, 94, 0.1);
            color: #059669;
        }
        
        .bulk-action-btn.activate:hover {
            background: #10B981;
            color: white;
        }
        
        .bulk-action-btn.deactivate {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
        }
        
        .bulk-action-btn.deactivate:hover {
            background: #DC2626;
            color: white;
        }
        
        .bulk-action-btn.export {
            background: rgba(19, 102, 92, 0.1);
            color: var(--color-teal);
        }
        
        .bulk-action-btn.export:hover {
            background: var(--color-teal);
            color: white;
        }
        
        /* Enhanced Table */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-controls-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .table-controls-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .select-all-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .member-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }
        
        .export-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }
        
        /* Page Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .page-header {
            animation: fadeInUp 0.5s ease;
        }
        
        .stats-dashboard {
            animation: fadeInUp 0.6s ease;
        }
        
        .search-filter-section {
            animation: fadeInUp 0.7s ease;
        }
        
        .members-table-container {
            animation: fadeInUp 0.8s ease;
        }
        
        /* Member Status Indicators - Enhanced */
        .member-status-indicators {
            display: flex;
            gap: 6px;
            margin-top: 6px;
        }
        
        .status-indicator {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .status-indicator.online {
            background: #10B981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            }
            50% {
                box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.1);
            }
        }
        
        .status-indicator.recent {
            background: #F59E0B;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.15);
        }
        
        .status-indicator.never-logged {
            background: #EF4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
        }
        
        .status-indicator.joint {
            background: #8B5CF6;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.15);
        }
        
        /* Badge Styles - Modern */
        .badge {
            padding: 5px 10px;
            border-radius: 14px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-left: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .badge:hover {
            transform: scale(1.05);
        }
        
        .badge-joint {
            background: rgba(139, 92, 246, 0.12);
            color: #7C3AED;
            border: 1px solid rgba(139, 92, 246, 0.25);
        }
        
        /* Sortable Headers */
        .sortable {
            cursor: pointer;
            position: relative;
            user-select: none;
            transition: all 0.3s ease;
        }
        
        .sortable:hover {
            background: rgba(139, 69, 19, 0.05);
        }
        
        .sort-icon {
            font-size: 12px;
            margin-left: 8px;
            opacity: 0.6;
            transition: all 0.3s ease;
        }
        
        .sortable:hover .sort-icon {
            opacity: 1;
        }
        
        .sortable.asc .sort-icon::before {
            content: "\f0de";
        }
        
        .sortable.desc .sort-icon::before {
            content: "\f0dd";
        }
        
        /* Table View Options */
        .table-view-options {
            display: flex;
            gap: 5px;
        }
        
        .table-view-options .btn {
            padding: 8px 12px;
            border-radius: 6px;
        }
        
        .table-view-options .btn.active {
            background: var(--color-purple);
            border-color: var(--color-purple);
            color: white;
        }
        
        /* Joint Group Info */
        .joint-group-info {
            margin-top: 4px;
        }
        
        .joint-group-info small {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        /* Responsive Design - Enhanced for All Devices */
        @media (max-width: 1200px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 24px;
                padding: 36px 32px;
            }
            
            .page-header::before {
                opacity: 0.5;
            }
            
            .page-actions {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .stats-dashboard {
                margin-bottom: 32px;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 28px 20px;
            }

            .page-title-section h1 {
                font-size: 28px;
                gap: 12px;
            }
            
            .page-title-section h1 svg {
                width: 24px;
                height: 24px;
            }
            
            .page-subtitle {
                font-size: 14px;
            }

            .search-filter-section {
                padding: 20px;
            }

            .filter-group {
                flex-direction: column;
                justify-content: flex-start;
                margin-top: 16px;
                width: 100%;
            }
            
            .filter-select {
                width: 100%;
            }

            .search-filter-section .row {
                flex-direction: column;
            }

            .search-filter-section .col-lg-6 {
                width: 100%;
                margin-bottom: 16px;
            }

            .members-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .members-table {
                min-width: 900px;
            }

            .stat-number {
                font-size: 26px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-icon {
                width: 44px;
                height: 44px;
            }
            
            .action-buttons {
                gap: 6px;
            }
            
            .btn-action {
                width: 36px;
                height: 36px;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 24px 16px;
                margin-bottom: 24px;
                border-radius: 16px;
            }
            
            .page-title-section h1 {
                font-size: 24px;
                flex-direction: column;
                gap: 8px;
            }

            .search-filter-section {
                padding: 16px;
                border-radius: 12px;
            }

            .members-table tbody td {
                padding: 16px 12px;
                font-size: 13px;
            }
            
            .member-avatar {
                width: 44px;
                height: 44px;
                font-size: 15px;
            }
            
            .stat-number {
                font-size: 24px;
            }
            
            .payment-amount {
                font-size: 18px;
            }
            
            .page-actions .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <!-- Include Navigation -->
        <?php include 'includes/navigation.php'; ?>

            <!-- Members Page Content -->
            <div class="page-header">
                <div class="page-title-section">
                    <h1>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <?php echo t('members.page_title'); ?>
                    </h1>
                    <p class="page-subtitle">
                        <strong>ENHANCED DYNAMIC MEMBERS</strong> - Real-time payout calculations from database with NO hardcoded values!
                        <br><?php echo t('members.page_subtitle'); ?>
                    </p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-add-member" onclick="showAddMemberModal()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <path d="M20 8v6M23 11h-6"/>
                        </svg>
                        <?php echo t('members.add_new_member'); ?>
                    </button>
                    <button class="btn btn-success" onclick="syncAllPayoutDates()" id="syncPayoutsBtn">
                        <i class="fas fa-sync-alt me-2"></i>
                        Sync Payout Dates
                    </button>
                </div>
            </div>

            <!-- Enhanced Statistics Dashboard -->
            <div class="row stats-dashboard">
                <!-- Row 1: Primary Metrics -->
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
                            <span class="stat-trend">+<?php echo $recent_joiners; ?> this month</span>
                        </div>
                        <h3 class="stat-number"><?php echo $total_members; ?></h3>
                        <p class="stat-label"><?php echo t('members.total_members'); ?></p>
                        <div class="stat-breakdown">
                            <small class="text-muted"><?php echo $total_individual_members; ?> Individual + <?php echo $total_joint_members; ?> Joint</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon active-members">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                </svg>
                            </div>
                            <span class="stat-trend"><?php echo round(($active_members/$total_members)*100, 1); ?>% active</span>
                        </div>
                        <h3 class="stat-number"><?php echo $active_members; ?></h3>
                        <p class="stat-label"><?php echo t('members.active_members'); ?></p>
                        <div class="stat-breakdown">
                            <small class="text-muted"><?php echo $inactive_members; ?> inactive</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon financial-stats">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M16 12l-4-4-4 4"/>
                                    <path d="M12 16V8"/>
                                </svg>
                            </div>
                            <span class="stat-trend">£<?php echo number_format($average_payment, 0); ?> avg</span>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($total_contributions_received, 0); ?></h3>
                        <p class="stat-label">Total Contributed</p>
                        <div class="stat-breakdown">
                            <small class="text-muted">£<?php echo number_format($total_monthly_contributions, 0); ?>/month expected</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon completed-payouts">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M9 12l2 2 4-4"/>
                                </svg>
                            </div>
                            <span class="stat-trend"><?php echo $pending_payouts; ?> pending</span>
                        </div>
                        <h3 class="stat-number"><?php echo $completed_payouts; ?></h3>
                        <p class="stat-label"><?php echo t('members.completed_payouts'); ?></p>
                        <div class="stat-breakdown">
                            <small class="text-muted"><?php echo round(($completed_payouts/$total_members)*100, 1); ?>% complete</small>
                        </div>
                    </div>
                </div>
                
                <!-- Row 2: Secondary Metrics -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card stat-card-secondary">
                        <div class="stat-header">
                            <div class="stat-icon joint-groups">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <h3 class="stat-number"><?php echo $unique_joint_groups; ?></h3>
                        <p class="stat-label">Joint Groups</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card stat-card-secondary">
                        <div class="stat-header">
                            <div class="stat-icon total-positions">
                                <i class="fas fa-sort-numeric-up"></i>
                            </div>
                        </div>
                        <h3 class="stat-number"><?php echo $total_positions; ?></h3>
                        <p class="stat-label">Total Positions</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card stat-card-secondary">
                        <div class="stat-header">
                            <div class="stat-icon pending-approval">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <h3 class="stat-number"><?php echo $pending_approval; ?></h3>
                        <p class="stat-label">Pending Approval</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card stat-card-secondary">
                        <div class="stat-header">
                            <div class="stat-icon never-paid">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <h3 class="stat-number"><?php echo $never_paid; ?></h3>
                        <p class="stat-label">Never Paid</p>
                    </div>
                </div>
            </div>
            
            <!-- NEW DYNAMIC FINANCIAL METRICS (NO HARDCODE!) -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card" style="border-left: 5px solid #10B981;">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($total_real_net_payouts, 0); ?></h3>
                        <p class="stat-label">Total Real Net Payouts</p>
                        <small class="text-muted">Actual amounts to be received</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card" style="border-left: 5px solid #3B82F6;">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6, #60A5FA);">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($total_display_payouts, 0); ?></h3>
                        <p class="stat-label">Total Display Payouts</p>
                        <small class="text-muted">Member-friendly amounts</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card" style="border-left: 5px solid #F59E0B;">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #FCD34D);">
                                <i class="fas fa-calculator"></i>
                            </div>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($total_admin_fees, 0); ?></h3>
                        <p class="stat-label">Total Admin Fees</p>
                        <small class="text-muted">Revenue from all payouts</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card" style="border-left: 5px solid #8B5CF6;">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA);">
                                <i class="fas fa-minus-circle"></i>
                            </div>
                        </div>
                        <h3 class="stat-number">£<?php echo number_format($total_monthly_deductions, 0); ?></h3>
                        <p class="stat-label">Monthly Deductions</p>
                        <small class="text-muted">Total saved (no payment in payout month)</small>
                    </div>
                </div>
            </div>

            <!-- Enhanced Members Management Section -->
            <div class="search-filter-section">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="search-bar">
                            <input type="text" class="search-input" id="memberSearch" placeholder="<?php echo t('members.search_placeholder'); ?>" oninput="searchMembers()">
                            <span class="search-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 21l-6-6"/>
                                    <circle cx="11" cy="11" r="6"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select" onchange="filterMembers()">
                                <option value=""><?php echo t('members.all_status'); ?></option>
                                <option value="active"><?php echo t('members.active'); ?></option>
                                <option value="inactive"><?php echo t('members.inactive'); ?></option>
                                <option value="pending_approval">Pending Approval</option>
                            </select>
                            <button class="filter-toggle" onclick="toggleAdvancedFilters()">
                                <i class="fas fa-filter"></i>
                                Advanced Filters
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Filters -->
                <div class="advanced-filters" id="advancedFilters">
                    <div class="row">
                        <div class="col-lg-3">
                            <label for="payoutFilter" class="form-label">Payout Status</label>
                            <select id="payoutFilter" class="filter-select" onchange="filterMembers()">
                                <option value=""><?php echo t('members.all_payouts'); ?></option>
                                <option value="completed"><?php echo t('members.received_payout'); ?></option>
                                <option value="pending"><?php echo t('members.pending_payout'); ?></option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="membershipTypeFilter" class="form-label">Membership Type</label>
                            <select id="membershipTypeFilter" class="filter-select" onchange="filterMembers()">
                                <option value="">All Types</option>
                                <option value="individual">Individual</option>
                                <option value="joint">Joint</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="equbTermFilter" class="form-label">EQUB Term</label>
                            <select id="equbTermFilter" class="filter-select" onchange="filterMembers()">
                                <option value="">All EQUB Terms</option>
                                <?php
                                try {
                                    $equb_terms = $pdo->query("SELECT id, equb_name FROM equb_settings ORDER BY equb_name");
                                    while ($term = $equb_terms->fetch()) {
                                        echo "<option value='{$term['id']}'>{$term['equb_name']}</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>Error loading terms</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="paymentRangeFilter" class="form-label">Payment Range</label>
                            <select id="paymentRangeFilter" class="filter-select" onchange="filterMembers()">
                                <option value="">All Amounts</option>
                                <option value="0-500">£0 - £500</option>
                                <option value="500-1000">£500 - £1,000</option>
                                <option value="1000-1500">£1,000 - £1,500</option>
                                <option value="1500+">£1,500+</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Actions Bar -->
            <div class="bulk-actions" id="bulkActions">
                <p class="bulk-actions-text">
                    <span id="selectedCount">0</span> members selected
                </p>
                <div class="bulk-action-buttons">
                    <button class="bulk-action-btn activate" onclick="bulkActivateMembers()">
                        <i class="fas fa-check"></i> Activate
                    </button>
                    <button class="bulk-action-btn deactivate" onclick="bulkDeactivateMembers()">
                        <i class="fas fa-times"></i> Deactivate
                    </button>
                    <button class="bulk-action-btn export" onclick="exportSelectedMembers()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

                <!-- Enhanced Members Table -->
                <div class="members-table-container">
                    <div class="table-controls">
                        <div class="table-controls-left">
                            <h3 class="table-title"><?php echo t('members.all_members'); ?></h3>
                            <div class="select-all-wrapper">
                                <input type="checkbox" id="selectAll" class="member-checkbox" onchange="toggleSelectAll()">
                                <label for="selectAll" class="form-label mb-0">Select All</label>
                            </div>
                        </div>
                        <div class="table-controls-right">
                            <button class="export-btn" onclick="exportMembers()">
                                <i class="fas fa-download"></i>
                                Export All
                            </button>
                            <div class="table-view-options">
                                <button class="btn btn-sm btn-outline-primary" onclick="toggleTableView('compact')" id="compactViewBtn">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary active" onclick="toggleTableView('detailed')" id="detailedViewBtn">
                                    <i class="fas fa-th-large"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="members-table" id="membersTable">
                            <thead>
                                <tr>
                                    <th width="40"><input type="checkbox" id="selectAllHeader" class="member-checkbox" onchange="toggleSelectAll()"></th>
                                    <th onclick="sortTable('member')" class="sortable">
                                        <?php echo t('members.member'); ?>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('contact')" class="sortable">
                                        <?php echo t('members.contact'); ?>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('payment')" class="sortable">
                                        <?php echo t('members.payment_details'); ?>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('payout')" class="sortable">
                                        <?php echo t('members.payout_status'); ?>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('status')" class="sortable">
                                        <?php echo t('members.status'); ?>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th><?php echo t('members.actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <?php foreach ($members as $member): 
                                    // Calculate member activity status
                                    $last_login = $member['last_login'];
                                    $activity_status = 'never-logged';
                                    $activity_text = 'Never logged in';
                                    
                                    if ($last_login) {
                                        $login_time = strtotime($last_login);
                                        $now = time();
                                        $hours_diff = ($now - $login_time) / 3600;
                                        
                                        if ($hours_diff <= 24) {
                                            $activity_status = 'online';
                                            $activity_text = 'Active (24h)';
                                        } elseif ($hours_diff <= 168) { // 7 days
                                            $activity_status = 'recent';
                                            $activity_text = 'Recent (7d)';
                                        } else {
                                            $activity_status = 'never-logged';
                                            $activity_text = 'Inactive';
                                        }
                                    }
                                ?>
                                    <tr data-member-id="<?php echo $member['id']; ?>" data-status="<?php echo $member['is_active'] ? 'active' : 'inactive'; ?>" data-payout="<?php echo $member['has_received_payout'] ? 'completed' : 'pending'; ?>" data-membership-type="<?php echo $member['membership_type']; ?>" data-equb-id="<?php echo $member['equb_settings_id']; ?>" data-payment-amount="<?php echo $member['monthly_payment']; ?>">
                                        <td>
                                            <input type="checkbox" class="member-checkbox member-select" value="<?php echo $member['id']; ?>" onchange="updateBulkActions()">
                                        </td>
                                        <td>
                                            <div class="member-info">
                                                <div class="member-avatar">
                                                    <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                                                </div>
                                                <div class="member-details">
                                                    <div class="member-name">
                                                        <a href="member-profile.php?id=<?php echo $member['id']; ?>" class="member-name-link">
                                                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                                        </a>
                                                        <?php if ($member['membership_type'] === 'joint'): ?>
                                                            <span class="badge badge-joint">Joint</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="member-id"><?php echo htmlspecialchars($member['member_id']); ?></div>
                                                    <div class="member-status-indicators">
                                                        <span class="status-indicator <?php echo $activity_status; ?>" title="<?php echo $activity_text; ?>"></span>
                                                        <?php if ($member['membership_type'] === 'joint'): ?>
                                                            <span class="status-indicator joint" title="Joint Membership"></span>
                                                        <?php endif; ?>
                                                        <?php if (!$member['is_approved']): ?>
                                                            <span class="status-indicator" style="background: #F59E0B;" title="Pending Approval"></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div class="contact-email"><?php echo htmlspecialchars($member['email']); ?></div>
                                                <div class="contact-phone"><?php echo htmlspecialchars($member['phone']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="payment-info">
                                                <div class="payment-amount">
                                                    £<?php echo number_format($member['effective_monthly_payment'] ?: $member['monthly_payment'], 0); ?>/<?php echo t('members.month'); ?>
                                                    <?php if ($member['membership_type'] === 'joint' && $member['individual_contribution']): ?>
                                                        <small class="text-muted">(£<?php echo number_format($member['individual_contribution'], 0); ?> individual)</small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="payment-status">
                                                    <?php echo t('members.paid'); ?>: £<?php echo number_format($member['total_paid'], 0); ?>
                                                                                                    <?php if ($member['expected_payout'] > 0): ?>
                                                    <br><div class="payout-details">
                                                        <small class="text-success">
                                                            <strong>Display Payout:</strong> £<?php echo number_format($member['display_payout'], 0); ?>
                                                            <br><strong>Real Net (Receipt):</strong> £<?php echo number_format($member['real_net_payout'], 0); ?>
                                                            <br><span class="text-muted">
                                                                Gross: £<?php echo number_format($member['gross_payout'], 0); ?> - 
                                                                Fee: £<?php echo number_format($member['admin_fee'], 0); ?> - 
                                                                Month: £<?php echo number_format($member['monthly_deduction'], 0); ?>
                                                            </span>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                </div>
                                                <?php if ($member['membership_type'] === 'joint' && $member['group_name']): ?>
                                                    <div class="joint-group-info">
                                                        <small class="text-info"><i class="fas fa-users"></i> <?php echo htmlspecialchars($member['group_name']); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($member['has_received_payout']): ?>
                                                <span class="payout-badge payout-received"><?php echo t('members.received'); ?></span>
                                            <?php else: ?>
                                                <span class="payout-badge payout-pending"><?php echo t('members.pending'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($member['is_active']): ?>
                                                <span class="status-badge status-active"><?php echo t('members.active'); ?></span>
                                            <?php else: ?>
                                                <span class="status-badge status-inactive"><?php echo t('members.inactive'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-action btn-edit" onclick="editMember(<?php echo $member['id']; ?>)" title="<?php echo t('members.edit_member'); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-action btn-toggle" 
                                                        onclick="toggleMemberStatus(<?php echo $member['id']; ?>, <?php echo $member['is_active'] ? 0 : 1; ?>)" 
                                                        title="<?php echo $member['is_active'] ? t('members.deactivate') : t('members.activate'); ?>">
                                                    <?php if ($member['is_active']): ?>
                                                        <i class="fas fa-toggle-on"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-toggle-off"></i>
                                                    <?php endif; ?>
                                                </button>
                                                <button class="btn btn-action btn-delete" 
                                                        onclick="deleteMember(<?php echo $member['id']; ?>)" title="<?php echo t('members.delete_member'); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

        </div> <!-- End app-content -->
    </main> <!-- End app-main -->
</div> <!-- End app-layout -->

    <!-- Add/Edit Member Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalLabel"><?php echo t('members.add_member_title'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="memberForm">
                    <div class="modal-body">
                        <input type="hidden" id="memberId" name="member_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <!-- Personal Information -->
                        <h6 class="text-primary mb-3"><?php echo t('members.personal_information'); ?></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label"><?php echo t('members.first_name'); ?> *</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastName" class="form-label"><?php echo t('members.last_name'); ?> *</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo t('members.email'); ?> *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label"><?php echo t('members.phone'); ?> *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                        </div>
                        
                                <!-- Equib Information -->
        <h6 class="text-primary mb-3 mt-4"><?php echo t('members.equib_information'); ?></h6>
        
        <!-- Equib Term Assignment -->
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="equbTerm" class="form-label">
                        <i class="fas fa-calendar-alt text-warning"></i>
                        Assign to Equb Term *
                    </label>
                    <select class="form-select" id="equbTerm" name="equb_settings_id" required>
                        <option value="">Select Equb Term...</option>
                        <?php
                        // Get active equb terms
                        try {
                            $equb_stmt = $pdo->query("
                                SELECT id, equb_id, equb_name, status, max_members, current_members, 
                                       start_date, end_date, duration_months,
                                       JSON_EXTRACT(payment_tiers, '$[0].amount') as tier1_amount,
                                       JSON_EXTRACT(payment_tiers, '$[1].amount') as tier2_amount,
                                       JSON_EXTRACT(payment_tiers, '$[2].amount') as tier3_amount
                                FROM equb_settings 
                                WHERE status IN ('planning', 'active') 
                                ORDER BY created_at DESC
                            ");
                            $equb_terms = $equb_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($equb_terms as $term) {
                                $available_spots = $term['max_members'] - $term['current_members'];
                                $status_badge = $term['status'] === 'active' ? '🟢' : '🟡';
                                echo "<option value='{$term['id']}' data-max-members='{$term['max_members']}' data-current-members='{$term['current_members']}' data-duration='{$term['duration_months']}' data-tier1='{$term['tier1_amount']}' data-tier2='{$term['tier2_amount']}' data-tier3='{$term['tier3_amount']}'>";
                                echo "{$status_badge} {$term['equb_name']} ({$term['equb_id']}) - {$available_spots}/{$term['max_members']} spots available";
                                echo "</option>";
                            }
                        } catch (PDOException $e) {
                            echo "<option value=''>Error loading equb terms</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">Choose which equb term this member will join</div>
                </div>
            </div>
        </div>

        <!-- Payment Tier Selection -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="paymentTier" class="form-label">
                        <i class="fas fa-money-bill-wave text-success"></i>
                        Payment Tier *
                    </label>
                    <select class="form-select" id="paymentTier" name="payment_tier" required>
                        <option value="">Select payment tier...</option>
                    </select>
                    <div class="form-text">Available tiers will show after selecting equb term</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="monthlyPayment" class="form-label"><?php echo t('members.monthly_payment'); ?> *</label>
                    <input type="number" class="form-control" id="monthlyPayment" name="monthly_payment" min="1" step="0.01" required readonly>
                    <div class="form-text">Auto-filled based on selected payment tier</div>
                </div>
            </div>
        </div>

        <!-- Membership Type Selection -->
        <div class="row">
            <div class="col-md-12">
                <h6 class="text-primary mb-3 mt-4">
                    <i class="fas fa-users text-info"></i>
                    <?php echo t('joint_membership.title'); ?>
                </h6>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="membershipType" class="form-label">
                        <i class="fas fa-user-friends text-info"></i>
                        Membership Type *
                    </label>
                    <select class="form-select" id="membershipType" name="membership_type" required>
                        <option value="individual">Individual Membership</option>
                        <option value="joint">Joint Membership</option>
                    </select>
                    <div class="form-text"><?php echo t('joint_membership.description'); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3" id="existingJointGroupField" style="display: none;">
                    <label for="existingJointGroup" class="form-label">
                        <i class="fas fa-link text-warning"></i>
                        Existing Joint Group
                    </label>
                    <select class="form-select" id="existingJointGroup" name="existing_joint_group">
                        <option value="">Create new joint group</option>
                    </select>
                    <div class="form-text">Join existing group or create new one</div>
                </div>
            </div>
        </div>

        <!-- Joint Membership Configuration (shown when joint type is selected) -->
        <div id="jointMembershipConfig" style="display: none;">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Joint Membership Configuration</h6>
                <p class="mb-0">Multiple people will share one position in the equb. Each person contributes individually but receives their share when the group's turn comes.</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="jointGroupName" class="form-label">
                            <i class="fas fa-tag text-primary"></i>
                            <?php echo t('joint_membership.group_name'); ?>
                        </label>
                        <input type="text" class="form-control" id="jointGroupName" name="joint_group_name" placeholder="e.g., Smith Family Group">
                        <div class="form-text">Optional descriptive name</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="individualContribution" class="form-label">
                            <i class="fas fa-pound-sign text-success"></i>
                            <?php echo t('joint_membership.individual_contribution'); ?> *
                        </label>
                        <input type="number" class="form-control" id="individualContribution" name="individual_contribution" min="1" step="0.01">
                        <div class="form-text">This person's monthly contribution</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payoutSplitMethod" class="form-label">
                            <i class="fas fa-chart-pie text-warning"></i>
                            <?php echo t('joint_membership.split_method'); ?> *
                        </label>
                        <select class="form-select" id="payoutSplitMethod" name="payout_split_method">
                            <option value="equal"><?php echo t('joint_membership.equal_split'); ?></option>
                            <option value="proportional"><?php echo t('joint_membership.proportional_split'); ?></option>
                            <option value="custom"><?php echo t('joint_membership.custom_split'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row" id="customShareField" style="display: none;">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="jointPositionShare" class="form-label">
                            <i class="fas fa-percentage text-info"></i>
                            Custom Share Percentage
                        </label>
                        <input type="number" class="form-control" id="jointPositionShare" name="joint_position_share" min="0.01" max="1" step="0.01" placeholder="0.50">
                        <div class="form-text">Enter as decimal (0.50 = 50%)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Primary Member</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="primaryJointMember" name="primary_joint_member" value="1" checked>
                            <label class="form-check-label" for="primaryJointMember">
                                This is the primary contact for the joint group
                            </label>
                        </div>
                        <div class="form-text">Primary member receives group communications</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout Assignment -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="payoutPosition" class="form-label">
                        <i class="fas fa-sort-numeric-up text-info"></i>
                        Payout Position *
                    </label>
                    <select class="form-select" id="payoutPosition" name="payout_position" required>
                        <option value="">Select position...</option>
                    </select>
                    <div class="form-text">Available positions will show after selecting equb term</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="payoutMonth" class="form-label">
                        <i class="fas fa-calendar-check text-primary"></i>
                        Payout Month
                    </label>
                    <input type="month" class="form-control" id="payoutMonth" name="payout_month" readonly>
                    <div class="form-text">Auto-calculated based on position and equb start date</div>
                </div>
            </div>
        </div>
                        
                        <!-- Guarantor Information -->
                        <h6 class="text-primary mb-3 mt-4"><?php echo t('members.guarantor_information'); ?></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guarantorFirstName" class="form-label"><?php echo t('members.guarantor_first_name'); ?></label>
                                    <input type="text" class="form-control" id="guarantorFirstName" name="guarantor_first_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guarantorLastName" class="form-label"><?php echo t('members.guarantor_last_name'); ?></label>
                                    <input type="text" class="form-control" id="guarantorLastName" name="guarantor_last_name">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="guarantorPhone" class="form-label"><?php echo t('members.guarantor_phone'); ?></label>
                                    <input type="tel" class="form-control" id="guarantorPhone" name="guarantor_phone">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="guarantorEmail" class="form-label"><?php echo t('members.guarantor_email'); ?></label>
                                    <input type="email" class="form-control" id="guarantorEmail" name="guarantor_email">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="guarantorRelationship" class="form-label"><?php echo t('members.relationship'); ?></label>
                                    <select class="form-select" id="guarantorRelationship" name="guarantor_relationship">
                                        <option value=""><?php echo t('members.select'); ?></option>
                                        <option value="Father"><?php echo t('members.father'); ?></option>
                                        <option value="Mother"><?php echo t('members.mother'); ?></option>
                                        <option value="Brother"><?php echo t('members.brother'); ?></option>
                                        <option value="Sister"><?php echo t('members.sister'); ?></option>
                                        <option value="Husband"><?php echo t('members.husband'); ?></option>
                                        <option value="Wife"><?php echo t('members.wife'); ?></option>
                                        <option value="Friend"><?php echo t('members.friend'); ?></option>
                                        <option value="Other"><?php echo t('members.other'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Privacy & Permissions -->
                        <h6 class="text-primary mb-3 mt-4"><i class="fas fa-shield-alt text-warning"></i> Privacy & Permissions</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="goPublic" name="go_public">
                                    <label class="form-check-label" for="goPublic">Public Profile (show in members directory)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="swapAllowed" name="swap_terms_allowed">
                                    <label class="form-check-label" for="swapAllowed">Allow Position Swap Requests</label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?php echo t('members.notes'); ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('members.cancel'); ?></button>
                        <button type="submit" class="btn btn-primary" id="submitBtn"><?php echo t('members.add_member_btn'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Message Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel"><?php echo t('members.member_added_success'); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="successMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal"><?php echo t('members.ok'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/auth.js"></script>
    
    <script>
        // Handle logout
        async function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=logout'
                    });
                    const result = await response.json();
                    if (result.success) {
                        window.location.href = 'login.php';
                    }
                } catch (error) {
                    window.location.href = 'login.php';
                }
            }
        }

        // Enhanced search and filter functionality
        let currentSortColumn = null;
        let currentSortDirection = 'asc';
        let filteredMembers = [];
        
        function searchMembers() {
            const searchTerm = document.getElementById('memberSearch').value.toLowerCase();
            filterAndDisplayMembers();
        }
        
        function filterMembers() {
            filterAndDisplayMembers();
        }
        
        function filterAndDisplayMembers() {
            const searchTerm = document.getElementById('memberSearch').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const payoutFilter = document.getElementById('payoutFilter').value;
            const membershipTypeFilter = document.getElementById('membershipTypeFilter')?.value || '';
            const equbTermFilter = document.getElementById('equbTermFilter')?.value || '';
            const paymentRangeFilter = document.getElementById('paymentRangeFilter')?.value || '';
            
            const rows = document.querySelectorAll('#membersTableBody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.getAttribute('data-status');
                const rowPayout = row.getAttribute('data-payout');
                const rowMembershipType = row.getAttribute('data-membership-type');
                const rowEqubId = row.getAttribute('data-equb-id');
                const rowPaymentAmount = parseFloat(row.getAttribute('data-payment-amount'));
                
                let show = true;
                
                // Search filter
                if (searchTerm && !text.includes(searchTerm)) {
                    show = false;
                }
                
                // Status filter
                if (statusFilter) {
                    if (statusFilter === 'pending_approval') {
                        // Check if row has pending approval indicator
                        if (!row.querySelector('.status-indicator[title="Pending Approval"]')) {
                            show = false;
                        }
                    } else if (statusFilter !== rowStatus) {
                        show = false;
                    }
                }
                
                // Payout filter
                if (payoutFilter && payoutFilter !== rowPayout) {
                    show = false;
                }
                
                // Membership type filter
                if (membershipTypeFilter && membershipTypeFilter !== rowMembershipType) {
                    show = false;
                }
                
                // EQUB term filter
                if (equbTermFilter && equbTermFilter !== rowEqubId) {
                    show = false;
                }
                
                // Payment range filter
                if (paymentRangeFilter && rowPaymentAmount) {
                    const [min, max] = paymentRangeFilter.split('-').map(val => {
                        if (val.includes('+')) return [parseFloat(val), Infinity];
                        return parseFloat(val);
                    });
                    
                    if (paymentRangeFilter.includes('-')) {
                        const [minVal, maxVal] = paymentRangeFilter.split('-').map(parseFloat);
                        if (rowPaymentAmount < minVal || rowPaymentAmount > maxVal) {
                            show = false;
                        }
                    } else if (paymentRangeFilter.includes('+')) {
                        const minVal = parseFloat(paymentRangeFilter);
                        if (rowPaymentAmount < minVal) {
                            show = false;
                        }
                    }
                }
                
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Update visible count
            updateTableInfo(visibleCount, rows.length);
        }
        
        function updateTableInfo(visible, total) {
            const info = document.getElementById('tableInfo');
            if (info) {
                info.textContent = `Showing ${visible} of ${total} members`;
            }
        }
        
        // Advanced filter toggle
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            const toggle = document.querySelector('.filter-toggle');
            
            filters.classList.toggle('active');
            toggle.classList.toggle('active');
        }
        
        // Bulk selection functionality
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll') || document.getElementById('selectAllHeader');
            const memberCheckboxes = document.querySelectorAll('.member-select:not([style*="display: none"])');
            
            memberCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            // Sync both select all checkboxes
            document.getElementById('selectAll').checked = selectAll.checked;
            document.getElementById('selectAllHeader').checked = selectAll.checked;
            
            updateBulkActions();
        }
        
        function updateBulkActions() {
            const selectedCheckboxes = document.querySelectorAll('.member-select:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (selectedCheckboxes.length > 0) {
                bulkActions.classList.add('active');
                selectedCount.textContent = selectedCheckboxes.length;
            } else {
                bulkActions.classList.remove('active');
            }
        }
        
        // Bulk actions
        function bulkActivateMembers() {
            const selectedMembers = Array.from(document.querySelectorAll('.member-select:checked')).map(cb => cb.value);
            if (selectedMembers.length === 0) return;
            
            if (confirm(`Activate ${selectedMembers.length} selected members?`)) {
                bulkUpdateMemberStatus(selectedMembers, 1);
            }
        }
        
        function bulkDeactivateMembers() {
            const selectedMembers = Array.from(document.querySelectorAll('.member-select:checked')).map(cb => cb.value);
            if (selectedMembers.length === 0) return;
            
            if (confirm(`Deactivate ${selectedMembers.length} selected members?`)) {
                bulkUpdateMemberStatus(selectedMembers, 0);
            }
        }
        
        function bulkUpdateMemberStatus(memberIds, status) {
            // Show loading state
            const bulkActions = document.getElementById('bulkActions');
            bulkActions.style.opacity = '0.6';
            
            Promise.all(memberIds.map(id => 
                fetch('api/members.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=toggle_status&member_id=${id}&status=${status}&csrf_token=${document.querySelector('[name="csrf_token"]').value}`
                })
            )).then(() => {
                location.reload(); // Refresh to show updated data
            }).catch(error => {
                alert('Error updating member status');
                bulkActions.style.opacity = '1';
            });
        }
        
        function exportSelectedMembers() {
            const selectedMembers = Array.from(document.querySelectorAll('.member-select:checked')).map(cb => cb.value);
            if (selectedMembers.length === 0) {
                alert('Please select members to export');
                return;
            }
            
            // Create form to submit selected member IDs to export.php
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.target = '_blank'; // Open in new tab
            
            // Add action type
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'export_type';
            actionInput.value = 'members';
            form.appendChild(actionInput);
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = document.querySelector('[name="csrf_token"]').value;
            form.appendChild(csrfInput);
            
            selectedMembers.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'member_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
        
        function exportMembers() {
            window.open('export-members.php?all=1', '_blank');
        }
        
        // Table sorting
        function sortTable(column) {
            const table = document.getElementById('membersTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Update sort direction
            if (currentSortColumn === column) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortDirection = 'asc';
                currentSortColumn = column;
            }
            
            // Update header classes
            document.querySelectorAll('.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
            });
            document.querySelector(`[onclick="sortTable('${column}')"]`).classList.add(currentSortDirection);
            
            // Sort rows
            rows.sort((a, b) => {
                let aVal, bVal;
                
                switch(column) {
                    case 'member':
                        aVal = a.querySelector('.member-name-link').textContent;
                        bVal = b.querySelector('.member-name-link').textContent;
                        break;
                    case 'contact':
                        aVal = a.querySelector('.contact-email').textContent;
                        bVal = b.querySelector('.contact-email').textContent;
                        break;
                    case 'payment':
                        aVal = parseFloat(a.getAttribute('data-payment-amount'));
                        bVal = parseFloat(b.getAttribute('data-payment-amount'));
                        break;
                    case 'payout':
                        aVal = a.getAttribute('data-payout');
                        bVal = b.getAttribute('data-payout');
                        break;
                    case 'status':
                        aVal = a.getAttribute('data-status');
                        bVal = b.getAttribute('data-status');
                        break;
                    default:
                        return 0;
                }
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (currentSortDirection === 'asc') {
                    return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
                } else {
                    return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
                }
            });
            
            // Reorder rows
            rows.forEach(row => tbody.appendChild(row));
        }
        
        // Table view toggle
        function toggleTableView(view) {
            const table = document.getElementById('membersTable');
            const compactBtn = document.getElementById('compactViewBtn');
            const detailedBtn = document.getElementById('detailedViewBtn');
            
            if (view === 'compact') {
                table.classList.add('compact-view');
                compactBtn.classList.add('active');
                detailedBtn.classList.remove('active');
            } else {
                table.classList.remove('compact-view');
                detailedBtn.classList.add('active');
                compactBtn.classList.remove('active');
            }
        }
        
        // Initialize filters
        document.addEventListener('DOMContentLoaded', function() {
            // Set up search listener
            document.getElementById('memberSearch').addEventListener('input', searchMembers);
            
            // Initialize table info
            const totalRows = document.querySelectorAll('#membersTableBody tr').length;
            updateTableInfo(totalRows, totalRows);
        });

        // Member management functions
        let isEditMode = false;
        let currentMemberId = null;



        function showAddMemberModal() {
            isEditMode = false;
            currentMemberId = null;
            document.getElementById('memberModalLabel').textContent = 'Add New Member';
            document.getElementById('submitBtn').textContent = 'Add Member';
            document.getElementById('memberForm').reset();
            document.getElementById('memberId').value = '';
            new bootstrap.Modal(document.getElementById('memberModal')).show();
        }

        function editMember(id) {
            isEditMode = true;
            currentMemberId = id;
            document.getElementById('memberModalLabel').textContent = 'Edit Member';
            document.getElementById('submitBtn').textContent = 'Update Member';
            
            // Fetch member data
            fetch('api/members.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_member&member_id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const member = data.member;
                    
                    // Basic member info
                    document.getElementById('memberId').value = member.id;
                    document.getElementById('firstName').value = member.first_name;
                    document.getElementById('lastName').value = member.last_name;
                    document.getElementById('email').value = member.email;
                    document.getElementById('phone').value = member.phone;
                    
                    // Equb assignment
                    const equbTermSelect = document.getElementById('equbTerm');
                    equbTermSelect.value = member.equb_settings_id || '';
                    
                    // Trigger equb term change to populate dependent fields
                    if (member.equb_settings_id) {
                        // Simulate equb term selection to populate tiers and positions
                        const changeEvent = new Event('change');
                        equbTermSelect.dispatchEvent(changeEvent);
                        
                        // Set values after a delay to allow async loading
                        setTimeout(() => {
                            // Set payment tier (this will auto-fill monthly payment)
                            const paymentTierSelect = document.getElementById('paymentTier');
                            paymentTierSelect.value = member.monthly_payment;
                            paymentTierSelect.dispatchEvent(new Event('change'));
                            
                            // Set payout position
                            const payoutPositionSelect = document.getElementById('payoutPosition');
                            // Add current position to available options if not already there
                            const currentPositionExists = Array.from(payoutPositionSelect.options).some(option => option.value == member.payout_position);
                            if (!currentPositionExists && member.payout_position) {
                                payoutPositionSelect.innerHTML += `<option value="${member.payout_position}">Position ${member.payout_position} (Current)</option>`;
                            }
                            payoutPositionSelect.value = member.payout_position;
                            
                            // Set payout month
                            document.getElementById('payoutMonth').value = member.formatted_payout_month || '';
                        }, 500);
                    }
                    
                    // Set monthly payment (might be overridden by payment tier selection)
                    document.getElementById('monthlyPayment').value = member.monthly_payment;
                    
                    // Joint membership information
                    const membershipTypeSelect = document.getElementById('membershipType');
                    membershipTypeSelect.value = member.membership_type || 'individual';
                    
                    if (member.membership_type === 'joint') {
                        // Show joint membership fields
                        document.getElementById('jointMembershipConfig').style.display = 'block';
                        document.getElementById('existingJointGroupField').style.display = 'block';
                        
                        // Load joint groups and set the current one
                        if (member.equb_settings_id) {
                            loadExistingJointGroups(member.equb_settings_id);
                            setTimeout(() => {
                                document.getElementById('existingJointGroup').value = member.joint_group_id || '';
                            }, 500);
                        }
                        
                        // Set joint membership fields
                        document.getElementById('individualContribution').value = member.individual_contribution || '';
                        document.getElementById('payoutSplitMethod').value = member.payout_split_method || 'equal';
                        document.getElementById('jointPositionShare').value = member.joint_position_share || '';
                        document.getElementById('primaryJointMember').checked = member.primary_joint_member == 1;
                        
                        // Show custom share field if needed
                        if (member.payout_split_method === 'custom') {
                            document.getElementById('customShareField').style.display = 'block';
                        }
                    } else {
                        // Hide joint membership fields
                        document.getElementById('jointMembershipConfig').style.display = 'none';
                        document.getElementById('existingJointGroupField').style.display = 'none';
                    }
                    
                    // Privacy & permissions
                    document.getElementById('goPublic').checked = (parseInt(member.go_public || 0, 10) === 1);
                    document.getElementById('swapAllowed').checked = (parseInt(member.swap_terms_allowed || 0, 10) === 1);

                    // Guarantor information
                    document.getElementById('guarantorFirstName').value = member.guarantor_first_name || '';
                    document.getElementById('guarantorLastName').value = member.guarantor_last_name || '';
                    document.getElementById('guarantorPhone').value = member.guarantor_phone || '';
                    document.getElementById('guarantorEmail').value = member.guarantor_email || '';
                    document.getElementById('guarantorRelationship').value = member.guarantor_relationship || '';
                    document.getElementById('notes').value = member.notes || '';
                    
                    new bootstrap.Modal(document.getElementById('memberModal')).show();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching member data');
            });
        }

        function toggleMemberStatus(id, status) {
            const action = status ? 'activate' : 'deactivate';
            if (confirm(`Are you sure you want to ${action} this member?`)) {
                fetch('api/members.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=toggle_status&member_id=${id}&status=${status}&csrf_token=${document.querySelector('[name="csrf_token"]').value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadMembers();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating member status');
                });
            }
        }

        function deleteMember(id) {
            if (confirm('Are you sure you want to delete this member? This action cannot be undone.')) {
                fetch('api/members.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&member_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadMembers();
                        updateStats();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting member');
                });
            }
        }

        // Form submission
        document.getElementById('memberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = isEditMode ? 'update' : 'add';
            formData.append('action', action);
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = isEditMode ? 'Updating...' : 'Adding...';
            
            fetch('api/members.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('memberModal')).hide();
                    
                    if (!isEditMode && data.member_id && data.password) {
                        // Show success modal with member credentials
                        document.getElementById('successMessage').innerHTML = `
                            <div class="alert alert-info">
                                <h6>New member created successfully!</h6>
                                <p><strong>Member ID:</strong> ${data.member_id}</p>
                                <p><strong>Password:</strong> ${data.password}</p>
                                <small class="text-muted">Please save these credentials and share them with the member.</small>
                            </div>
                        `;
                        new bootstrap.Modal(document.getElementById('successModal')).show();
                    } else {
                        showToast(data.message, 'success');
                    }
                    
                    loadMembers();
                    updateStats();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving member data');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });

        // Load members with filters
        function loadMembers() {
            const searchTerm = document.getElementById('memberSearch').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const payoutFilter = document.getElementById('payoutFilter').value;
            
            const formData = new FormData();
            formData.append('action', 'list');
            if (searchTerm) formData.append('search', searchTerm);
            if (statusFilter !== '') formData.append('status', statusFilter);
            if (payoutFilter !== '') formData.append('payout_status', payoutFilter);
            
            fetch('api/members.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateMembersTable(data.members);
                }
            })
            .catch(error => {
                console.error('Error loading members:', error);
            });
        }

        // Update members table
        function updateMembersTable(members) {
            const tbody = document.getElementById('membersTableBody');
            tbody.innerHTML = '';
            
            members.forEach(member => {
                const initials = (member.first_name.charAt(0) + member.last_name.charAt(0)).toUpperCase();
                const statusBadge = member.is_active ? 
                    '<span class="status-badge status-active">Active</span>' : 
                    '<span class="status-badge status-inactive">Inactive</span>';
                const payoutBadge = member.has_received_payout ? 
                    '<span class="payout-badge payout-received">Received</span>' : 
                    '<span class="payout-badge payout-pending">Pending</span>';
                
                const row = `
                    <tr>
                        <td>
                            <input type="checkbox" class="member-checkbox member-select" value="${member.id}" onchange="updateBulkActions()">
                        </td>
                        <td>
                            <div class="member-info">
                                <div class="member-avatar">${initials}</div>
                                <div class="member-details">
                                    <div class="member-name">
                                        <a href="member-profile.php?id=${member.id}" class="member-name-link">
                                            ${member.first_name} ${member.last_name}
                                        </a>
                                    </div>
                                    <div class="member-id">${member.member_id}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div class="contact-email">${member.email}</div>
                                <div class="contact-phone">${member.phone}</div>
                            </div>
                        </td>
                        <td>
                            <div class="payment-info">
                                <div class="payment-amount">£${parseFloat(member.monthly_payment).toLocaleString()}/month</div>
                                <div class="payment-status">Paid: £${parseFloat(member.total_paid).toLocaleString()}</div>
                            </div>
                        </td>
                        <td>${payoutBadge}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-action btn-edit" onclick="editMember(${member.id})" title="Edit Member">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-action btn-toggle" onclick="toggleMemberStatus(${member.id}, ${member.is_active ? 0 : 1})" title="${member.is_active ? 'Deactivate' : 'Activate'}">
                                    ${member.is_active ? 
                                        '<i class="fas fa-toggle-on"></i>' :
                                        '<i class="fas fa-toggle-off"></i>'
                                    }
                                </button>
                                <button class="btn btn-action btn-delete" onclick="deleteMember(${member.id})" title="Delete Member">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Update statistics
        function updateStats() {
            // You can implement this to refresh stats after member changes
            setTimeout(() => location.reload(), 1000);
        }

        // Show toast notifications
        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;margin-left:10px;cursor:pointer;">×</button>
            `;
            
            // Add toast styles if not already added
            if (!document.getElementById('toast-styles')) {
                const style = document.createElement('style');
                style.id = 'toast-styles';
                style.textContent = `
                    .toast-notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        padding: 15px 20px;
                        border-radius: 8px;
                        color: white;
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        animation: slideIn 0.3s ease;
                    }
                    .toast-success { background: #10B981; }
                    .toast-error { background: #EF4444; }
                    .toast-info { background: #3B82F6; }
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }

        // Filter event listeners
        document.getElementById('statusFilter').addEventListener('change', loadMembers);
        document.getElementById('payoutFilter').addEventListener('change', loadMembers);

        // Enhanced search with debounce
        let searchTimeout;
        document.getElementById('memberSearch').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadMembers();
            }, 300);
        });

        // Close mobile menu on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // This function is no longer needed as mobile menu is handled by navigation.php
                // Keeping it for now in case it's used elsewhere or for future updates.
            }
        });

        // =================
        // EQUB MANAGEMENT FUNCTIONALITY
        // =================

        // Dynamic Equb Term Selection
        document.getElementById('equbTerm').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const paymentTierSelect = document.getElementById('paymentTier');
            const payoutPositionSelect = document.getElementById('payoutPosition');
            const monthlyPaymentInput = document.getElementById('monthlyPayment');
            
            // Clear dependent fields
            paymentTierSelect.innerHTML = '<option value="">Select payment tier...</option>';
            payoutPositionSelect.innerHTML = '<option value="">Select position...</option>';
            monthlyPaymentInput.value = '';
            document.getElementById('payoutMonth').value = '';

            if (this.value) {
                // Get equb term data
                const tier1 = selectedOption.dataset.tier1;
                const tier2 = selectedOption.dataset.tier2;
                const tier3 = selectedOption.dataset.tier3;
                const maxMembers = parseInt(selectedOption.dataset.maxMembers);
                const currentMembers = parseInt(selectedOption.dataset.currentMembers);
                const duration = parseInt(selectedOption.dataset.duration);

                // Populate payment tiers
                if (tier1) paymentTierSelect.innerHTML += `<option value="${tier1}">Full Member - £${tier1}/month</option>`;
                if (tier2) paymentTierSelect.innerHTML += `<option value="${tier2}">Half Member - £${tier2}/month</option>`;
                if (tier3) paymentTierSelect.innerHTML += `<option value="${tier3}">Quarter Member - £${tier3}/month</option>`;

                // Load available payout positions
                loadAvailablePositions(this.value, maxMembers);
            }
        });

        // Payment Tier Selection
        document.getElementById('paymentTier').addEventListener('change', function() {
            const monthlyPaymentInput = document.getElementById('monthlyPayment');
            monthlyPaymentInput.value = this.value || '';
        });

        // Joint Membership Type Selection
        document.getElementById('membershipType').addEventListener('change', function() {
            const isJoint = this.value === 'joint';
            const jointConfig = document.getElementById('jointMembershipConfig');
            const existingJointField = document.getElementById('existingJointGroupField');
            const equbTermSelect = document.getElementById('equbTerm');
            
            if (isJoint) {
                jointConfig.style.display = 'block';
                existingJointField.style.display = 'block';
                
                // Load existing joint groups for the selected equb term
                if (equbTermSelect.value) {
                    loadExistingJointGroups(equbTermSelect.value);
                }
            } else {
                jointConfig.style.display = 'none';
                existingJointField.style.display = 'none';
                resetJointFields();
            }
        });

        // Payout Split Method Selection
        document.getElementById('payoutSplitMethod').addEventListener('change', function() {
            const customShareField = document.getElementById('customShareField');
            if (this.value === 'custom') {
                customShareField.style.display = 'block';
                document.getElementById('jointPositionShare').required = true;
            } else {
                customShareField.style.display = 'none';
                document.getElementById('jointPositionShare').required = false;
            }
        });

        // Existing Joint Group Selection
        document.getElementById('existingJointGroup').addEventListener('change', function() {
            if (this.value) {
                // Populate fields with existing group data
                fetch('api/members.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_joint_group_details&joint_group_id=${this.value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const group = data.group;
                        document.getElementById('jointGroupName').value = group.group_name || '';
                        document.getElementById('payoutSplitMethod').value = group.payout_split_method;
                        document.getElementById('paymentTier').value = group.total_monthly_payment;
                        document.getElementById('monthlyPayment').value = group.total_monthly_payment;
                        document.getElementById('payoutPosition').value = group.payout_position;
                        
                        // Calculate suggested individual contribution
                        const memberCount = group.member_count || 1;
                        const suggestedContribution = group.total_monthly_payment / (memberCount + 1);
                        document.getElementById('individualContribution').value = suggestedContribution.toFixed(2);
                        
                        // Show split method field if custom
                        if (group.payout_split_method === 'custom') {
                            document.getElementById('customShareField').style.display = 'block';
                            document.getElementById('jointPositionShare').required = true;
                        }
                        
                        // Set as secondary member by default
                        document.getElementById('primaryJointMember').checked = false;
                    }
                })
                .catch(error => console.error('Error loading joint group:', error));
            } else {
                resetJointFields();
            }
        });

        // Load existing joint groups for an equb term
        function loadExistingJointGroups(equbTermId) {
            const existingJointSelect = document.getElementById('existingJointGroup');
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_existing_joint_groups&equb_term_id=${equbTermId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    existingJointSelect.innerHTML = '<option value="">Create new joint group</option>';
                    data.data.forEach(group => {
                        const memberCount = group.member_count || 0;
                        const maxMembers = 3; // This should come from equb settings
                        if (memberCount < maxMembers) {
                            existingJointSelect.innerHTML += `
                                <option value="${group.joint_group_id}">
                                    ${group.group_name || group.joint_group_id} - ${memberCount}/${maxMembers} members - £${group.total_monthly_payment}/month
                                </option>
                            `;
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading joint groups:', error));
        }

        // Reset joint membership fields
        function resetJointFields() {
            document.getElementById('jointGroupName').value = '';
            document.getElementById('individualContribution').value = '';
            document.getElementById('payoutSplitMethod').value = 'equal';
            document.getElementById('jointPositionShare').value = '';
            document.getElementById('primaryJointMember').checked = true;
            document.getElementById('existingJointGroup').value = '';
            document.getElementById('customShareField').style.display = 'none';
        }

        // Payout Position Selection
        document.getElementById('payoutPosition').addEventListener('change', function() {
            if (this.value) {
                calculatePayoutMonth();
            }
        });

        // Load Available Payout Positions
        function loadAvailablePositions(equbTermId, maxMembers) {
            const payoutPositionSelect = document.getElementById('payoutPosition');
            
            // Get occupied positions via AJAX
            fetch('api/members.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_occupied_positions&equb_term_id=${equbTermId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const occupiedPositions = data.occupied_positions || [];
                    
                    // Generate available positions
                    for (let pos = 1; pos <= maxMembers; pos++) {
                        if (!occupiedPositions.includes(pos)) {
                            payoutPositionSelect.innerHTML += `<option value="${pos}">Position ${pos}</option>`;
                        }
                    }
                } else {
                    console.error('Error loading positions:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Calculate Payout Month
        function calculatePayoutMonth() {
            const equbTermSelect = document.getElementById('equbTerm');
            const selectedOption = equbTermSelect.options[equbTermSelect.selectedIndex];
            const position = parseInt(document.getElementById('payoutPosition').value);
            
            if (position && selectedOption.value) {
                // Get equb start date via AJAX
                fetch('api/members.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_equb_start_date&equb_term_id=${selectedOption.value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.start_date) {
                        const startDate = new Date(data.start_date);
                        const payoutDate = new Date(startDate);
                        payoutDate.setMonth(payoutDate.getMonth() + (position - 1));
                        
                        // Format as YYYY-MM for month input
                        const year = payoutDate.getFullYear();
                        const month = String(payoutDate.getMonth() + 1).padStart(2, '0');
                        document.getElementById('payoutMonth').value = `${year}-${month}`;
                    }
                })
                .catch(error => {
                    console.error('Error calculating payout month:', error);
                });
            }
        }

        // Enhanced Form Validation
        function validateEqubAssignment() {
            const equbTerm = document.getElementById('equbTerm').value;
            const paymentTier = document.getElementById('paymentTier').value;
            const payoutPosition = document.getElementById('payoutPosition').value;

            if (!equbTerm) {
                alert('Please select an equb term');
                return false;
            }
            if (!paymentTier) {
                alert('Please select a payment tier');
                return false;
            }
            if (!payoutPosition) {
                alert('Please select a payout position');
                return false;
            }
            return true;
        }

        // Update form submission to include validation
        const originalFormSubmit = document.getElementById('memberForm').onsubmit;
        document.getElementById('memberForm').addEventListener('submit', function(e) {
            if (!validateEqubAssignment()) {
                e.preventDefault();
                return false;
            }
        });

        // =================
        // PAYOUT SYNCHRONIZATION FUNCTIONALITY
        // =================

        // Sync all payout dates
        function syncAllPayoutDates() {
            const btn = document.getElementById('syncPayoutsBtn');
            const originalText = btn.innerHTML;
            
            if (confirm('This will recalculate and update all member payout dates based on their current equb assignments and positions. Continue?')) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Syncing...';
                
                fetch('api/payout-sync.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=bulk_sync_all'
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadMembers(); // Refresh the member list
                        updateStats(); // Refresh statistics
                    } else {
                        showToast('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    console.error('Error:', error);
                    showToast('Network error occurred', 'error');
                });
            }
        }

        // Sync individual member payout date
        function syncMemberPayout(memberId) {
            fetch('api/payout-sync.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=sync_member&member_id=${memberId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Payout date synchronized for member', 'success');
                    loadMembers(); // Refresh the member list
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'error');
            });
        }
    </script>
</body>
</html> 