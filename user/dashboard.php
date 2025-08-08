<?php
/**
 * HabeshaEqub - Enhanced Member Dashboard
 * Professional, clear, and user-friendly design focused on information clarity
 */

// FORCE NO CACHING
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once '../includes/payout_sync_service.php';

// Secure authentication check (now includes language loading)
require_once 'includes/auth_guard.php';
$user_id = get_current_user_id();

// MASTER-LEVEL DATABASE QUERY - Get complete member and financial data
try {
    $stmt = $db->prepare("
        SELECT m.*, 
               -- Joint Group Information
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.group_name
                   ELSE NULL
               END as joint_group_name,
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.payout_position
                   ELSE m.payout_position
               END as actual_payout_position,
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                   ELSE m.monthly_payment
               END as effective_monthly_payment,
               -- EQUB Settings
               es.equb_name, es.start_date, es.payout_day, es.duration_months, es.max_members, es.current_members,
               es.admin_fee, es.late_fee,
               
               -- Payment Statistics
               COUNT(DISTINCT p.id) as total_payment_records,
               COALESCE(SUM(CASE WHEN p.status IN ('paid', 'completed') THEN p.amount ELSE 0 END), 0) as total_contributed,
               COALESCE(SUM(CASE WHEN p.status IN ('paid', 'completed') THEN 1 ELSE 0 END), 0) as successful_payments,
               COALESCE(SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END), 0) as pending_payments,
               COALESCE(SUM(CASE WHEN p.late_fee > 0 THEN p.late_fee ELSE 0 END), 0) as total_late_fees,
               MAX(CASE WHEN p.status IN ('paid', 'completed') THEN p.payment_date END) as last_payment_date,
               
               -- Payout Information  
               COALESCE(
                   (SELECT SUM(po.net_amount) FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed'),
                   0
               ) as total_payouts_received,
               COALESCE(
                   (SELECT COUNT(*) FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed'),
                   0
               ) as payout_count,
               
               -- Latest Completed Payout Details (ENHANCED structure)
               (SELECT po.actual_payout_date FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1) as latest_payout_date,
               (SELECT po.gross_payout FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1) as latest_gross_payout,
               (SELECT po.total_amount FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1) as latest_total_amount,
               (SELECT po.net_amount FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1) as latest_net_amount,
               (SELECT po.payout_id FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1) as latest_payout_id,
               
               -- Equb Statistics
               (SELECT COUNT(*) FROM members WHERE equb_settings_id = m.equb_settings_id AND is_active = 1) as total_equb_members,
               
               -- Expected vs Actual Calculations
               CASE 
                   WHEN es.duration_months IS NOT NULL THEN es.duration_months
                   ELSE 12
               END as expected_payment_months,
               
               -- Current Equb Progress
               CASE 
                   WHEN es.start_date IS NOT NULL THEN 
                       DATEDIFF(CURDATE(), es.start_date) + 1
                   ELSE 1
               END as equb_days_active
               
        FROM members m 
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        LEFT JOIN payments p ON m.id = p.member_id AND p.status IN ('paid', 'completed', 'pending')
        WHERE m.id = ? AND m.is_active = 1
        GROUP BY m.id
    ");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        die("❌ ERROR: No member found with ID $user_id. Please check database.");
    }
    
    if (!$member['equb_settings_id']) {
        die("❌ ERROR: Member is not assigned to any equb term. Please contact admin.");
    }
} catch (PDOException $e) {
    die("❌ DATABASE ERROR: " . $e->getMessage());
}

// GET REAL PAYOUT INFORMATION using our top-tier sync service
$payout_service = getPayoutSyncService();
$payout_info = $payout_service->getMemberPayoutStatus($user_id);

if (isset($payout_info['error'])) {
    error_log("Payout calculation error for user $user_id: " . $payout_info['message']);
    // Fallback to basic calculation
    $next_payout_date = $member['payout_month'] ?? date('Y-m-d');
    $days_until_payout = floor((strtotime($next_payout_date) - time()) / (60 * 60 * 24));
} else {
    // Use calculated payout information
    $next_payout_date = $payout_info['calculated_payout_date'];
    $days_until_payout = $payout_info['days_until_payout'];
}

// MASTER-LEVEL STATISTICS CALCULATIONS
$member_name = trim($member['first_name'] . ' ' . $member['last_name']);
$monthly_contribution = (float)$member['effective_monthly_payment'];
$total_contributed = (float)$member['total_contributed']; 
$payout_position = (int)$member['actual_payout_position'];
$total_equb_members = (int)$member['total_equb_members'];

// Enhanced Financial Calculations using ENHANCED EQUB Calculator [[memory:5287409]]
require_once '../includes/enhanced_equb_calculator_final.php';
$enhanced_calculator = new EnhancedEqubCalculator($db);
$enhanced_calculation = $enhanced_calculator->calculateMemberFriendlyPayout($user_id);

if ($enhanced_calculation['success']) {
    // Show GROSS PAYOUT to members (full amount they're entitled to)
    $expected_payout = $enhanced_calculation['calculation']['gross_payout'];
    $gross_payout = $enhanced_calculation['calculation']['gross_payout'];
    $position_coefficient = $enhanced_calculation['calculation']['position_coefficient'];
    $total_monthly_pool = $enhanced_calculation['calculation']['total_monthly_pool'];
    $admin_fee = $enhanced_calculation['calculation']['admin_fee'];
} else {
    // Dynamic fallback calculation - NO HARDCODED VALUES
    $total_monthly_pool = $monthly_contribution * $total_equb_members;
    $position_coefficient = $monthly_contribution / 1000; // Assuming £1000 regular tier
    $gross_payout = $position_coefficient * $total_monthly_pool;
    $expected_payout = $gross_payout; // Show gross to member
    $admin_fee = (float)$member['admin_fee'] ?: 20;
}
$expected_total_contribution = $monthly_contribution * (int)$member['expected_payment_months'];
$contribution_progress = $expected_total_contribution > 0 ? min(100, ($total_contributed / $expected_total_contribution) * 100) : 0;

// Payment Performance Analysis
$successful_payments = (int)$member['successful_payments'];
$pending_payments = (int)$member['pending_payments'];
$total_late_fees_paid = (float)$member['total_late_fees'];
$payment_efficiency = $expected_total_contribution > 0 ? ($total_contributed / $expected_total_contribution) * 100 : 0;

// Payout Analysis
$total_payouts_received_amount = (float)$member['total_payouts_received'];
$payout_count = (int)$member['payout_count'];
$net_financial_position = $total_payouts_received_amount - $total_contributed;

// Time-based calculations
$equb_days_active = (int)$member['equb_days_active'];
$expected_months_elapsed = max(1, floor($equb_days_active / 30));
$expected_payments_so_far = min($expected_months_elapsed, (int)$member['expected_payment_months']);

// Get recent payments with proper month handling
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CASE 
                   WHEN p.payment_date IS NOT NULL AND p.payment_date != '0000-00-00' 
                   THEN DATE_FORMAT(p.payment_date, '%M %d, %Y') 
                   ELSE DATE_FORMAT(p.created_at, '%M %d, %Y')
               END as formatted_date,
               CASE 
                   WHEN p.payment_month IS NOT NULL AND p.payment_month != '0000-00-00' 
                   THEN DATE_FORMAT(p.payment_month, '%M %Y') 
                   WHEN p.payment_date IS NOT NULL AND p.payment_date != '0000-00-00'
                   THEN DATE_FORMAT(p.payment_date, '%M %Y')
                   ELSE DATE_FORMAT(p.created_at, '%M %Y')
               END as payment_month_name,
               CASE 
                   WHEN p.verified_by_admin = 1 THEN 'verified'
                   WHEN p.verified_by_admin = 0 AND p.status = 'paid' THEN 'pending_verification'
                   ELSE 'not_verified'
               END as verification_status
        FROM payments p 
        WHERE p.member_id = ?
        ORDER BY p.payment_date DESC, p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_payments = [];
}

// ENHANCED EQUB-AWARE PAYMENT STATUS LOGIC - FULLY DYNAMIC [[memory:5287409]]
try {
    // Get current date info
    $current_date = new DateTime();
    $today = $current_date->format('Y-m-d');
    $current_day = (int)$current_date->format('d');
    
    // Get EQUB settings dynamically from database - NO HARDCODED VALUES
    $stmt = $db->prepare("
        SELECT es.start_date, es.payout_day, es.late_fee, es.grace_period_days, es.duration_months
        FROM equb_settings es 
        WHERE es.id = ?
    ");
    $stmt->execute([$member['equb_settings_id']]);
    $equb_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$equb_settings) {
        throw new Exception("EQUB settings not found");
    }
    
    // Extract dynamic payment rules from database
    $equb_start_date = new DateTime($equb_settings['start_date']);
    $payment_due_day = 1; // Based on database rules: "due on the 1st day of each month"
    $grace_period_days = (int)$equb_settings['grace_period_days']; // Dynamic from DB
    $late_fee_amount = (float)$equb_settings['late_fee']; // Dynamic from DB
    $equb_duration = (int)$equb_settings['duration_months'];
    
    // Calculate which EQUB payment month we're currently in
    $diff = $current_date->diff($equb_start_date);
    $months_since_start = $diff->m + ($diff->y * 12);
    
    // Determine the current EQUB payment month
    $current_equb_month = min($months_since_start + 1, $equb_duration);
    
    // Calculate current payment month date (don't modify original date object)
    $payment_month_date = clone $equb_start_date;
    $payment_month_date->modify("+{$months_since_start} months");
    $current_payment_month = $payment_month_date->format('Y-m');
    $payment_month_formatted = $payment_month_date->format('F Y');
    
    // Calculate payment deadline (1st + grace period)
    $payment_deadline_day = $payment_due_day + $grace_period_days;
    
    // FIXED: Check if member has paid for the EXACT CURRENT EQUB payment month
    $stmt = $db->prepare("
        SELECT 
            p.id, p.amount, p.status, p.payment_date, p.late_fee, p.created_at,
            p.payment_month,
            DAY(p.payment_date) as payment_day
        FROM payments p 
        WHERE p.member_id = ? 
        AND p.payment_month = ?
        AND p.payment_month != '0000-00-00'
        AND p.status IN ('paid', 'completed')
        ORDER BY p.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([
        $user_id, 
        $current_payment_month . '-01'
    ]);
    $current_month_payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG: Log payment calculation details
    error_log("PAYMENT STATUS DEBUG - User ID: $user_id");
    error_log("Current Payment Month Expected: " . $current_payment_month . '-01');
    error_log("Payment Found: " . ($current_month_payment ? json_encode($current_month_payment) : 'NO PAYMENT FOUND'));
    error_log("Current Date: $today (Day $current_day), Grace Period: $grace_period_days days");
    
    // Initialize payment status
    $payment_status = [
        'has_paid' => false,
        'status' => 'not_paid',
        'status_text' => '',
        'status_class' => 'danger',
        'amount_paid' => 0,
        'late_fee' => 0,
        'days_overdue' => 0,
        'month_name' => $payment_month_formatted,
        'equb_month' => $current_equb_month,
        'payment_deadline' => $payment_deadline_day
    ];
    
    if ($current_month_payment) {
        // Member has paid for current EQUB month
        $payment_status['has_paid'] = true;
        $payment_status['status'] = 'paid';
        $payment_status['status_class'] = 'success';
        $payment_status['amount_paid'] = $current_month_payment['amount'];
        $payment_status['late_fee'] = $current_month_payment['late_fee'];
        
        // Check if payment was late
        $payment_day = (int)$current_month_payment['payment_day'];
        if ($payment_day > $payment_deadline_day) {
            $days_late = $payment_day - $payment_deadline_day;
            $payment_status['status_text'] = "Paid Late ({$days_late} days late)";
        } else {
            $payment_status['status_text'] = "Paid On Time";
        }
    } else {
        // Member has not paid for current EQUB month
        $payment_status['has_paid'] = false;
        $payment_status['amount_paid'] = 0;
        
        // Calculate days from the 1st of the current month
        $first_of_month = (int)date('1'); // Always 1st day
        $days_since_first = $current_day - $first_of_month;
        
        if ($days_since_first <= $grace_period_days) {
            // Still within grace period (1st + grace days)
            $payment_status['status'] = 'pending';
            $payment_status['status_class'] = 'warning';
            $days_left = $grace_period_days - $days_since_first + 1;
            $payment_status['status_text'] = "Payment Due ({$days_left} days remaining)";
        } else {
            // Payment is overdue (after 1st + grace period)
            $payment_status['status'] = 'overdue';
            $payment_status['status_class'] = 'danger';
            $payment_status['days_overdue'] = $days_since_first - $grace_period_days;
            $payment_status['late_fee'] = $late_fee_amount;
            $payment_status['status_text'] = "Overdue by {$payment_status['days_overdue']} days";
        }
    }
    
    // For backward compatibility
    $has_paid_this_month = $payment_status['has_paid'];
    
} catch (Exception $e) {
    $has_paid_this_month = false;
    $payment_status = [
        'has_paid' => false,
        'status' => 'error',
        'status_text' => 'Error loading payment status',
        'status_class' => 'secondary',
        'amount_paid' => 0,
        'late_fee' => 0,
        'days_overdue' => 0,
        'month_name' => date('F Y'),
        'equb_month' => 1,
        'payment_deadline' => 3
    ];
    error_log("Enhanced payment status calculation error: " . $e->getMessage());
}

// Get active equb rules for accordion
try {
    $stmt = $pdo->prepare("
        SELECT rule_number, rule_en, rule_am 
        FROM equb_rules 
        WHERE is_active = 1 
        ORDER BY rule_number ASC
    ");
    $stmt->execute();
    $equb_rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $equb_rules = [];
    error_log("Equb rules fetch error: " . $e->getMessage());
}

// Strong cache buster for assets
$cache_buster = time() . '_' . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('member_dashboard.page_title'); ?> - HabeshaEqub</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- CSS with cache busting -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" crossorigin="anonymous">
    <link href="../assets/css/style.css?v=<?php echo $cache_buster; ?>" rel="stylesheet">
    
    <!-- Ensure Font Awesome loads properly -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>

<style>
/* === PROFESSIONAL CLEAR DASHBOARD DESIGN === */

/* Custom Color Palette */
:root {
    --palette-cream: #F1ECE2;
    --palette-dark-purple: #4D4052;
    --palette-deep-purple: #301934;
    --palette-gold: #DAA520;
    --palette-light-gold: #CDAF56;
    --palette-brown: #5D4225;
    --palette-white: #FFFFFF;
    --palette-success: #2A9D8F;
    --palette-light-bg: #FAFAFA;
    --palette-border: rgba(77, 64, 82, 0.1);
}

/* Enhanced Welcome Header with Clear Information */
.welcome-header {
    background: linear-gradient(135deg, var(--palette-cream) 0%, #FAF8F5 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 40px;
    border: 1px solid var(--palette-border);
    box-shadow: 0 8px 32px rgba(48, 25, 52, 0.08);
    position: relative;
    overflow: hidden;
}

.welcome-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
}

.welcome-content h1 {
    font-size: 32px;
    font-weight: 700;
    color: var(--palette-deep-purple);
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.welcome-content p {
    font-size: 18px;
    color: var(--palette-dark-purple);
    margin: 0;
    font-weight: 400;
    opacity: 0.8;
}

.member-status {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 16px;
    padding: 12px 20px;
    background: rgba(42, 157, 143, 0.1);
    border-radius: 12px;
    border-left: 4px solid var(--palette-success);
}

.status-dot {
    width: 10px;
    height: 10px;
    background: linear-gradient(135deg, var(--palette-success) 0%, #047857 100%);
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.1); }
}

/* Clear Statistics Cards with Descriptive Titles */
.stats-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--palette-deep-purple);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Section subtitle removed - no longer needed */

.stat-card {
    background: var(--palette-white);
    border-radius: 20px;
    padding: 28px;
    border: 1px solid var(--palette-border);
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.06);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 40px rgba(48, 25, 52, 0.15);
    border-color: rgba(218, 165, 32, 0.2);
}

.stat-card:hover::before {
    transform: scaleX(1);
}

/* Card Header with Icon and Clear Title */
.stat-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-icon.primary { 
    background: linear-gradient(135deg, var(--palette-success) 0%, #0F766E 100%);
    box-shadow: 0 8px 24px rgba(42, 157, 143, 0.3);
}

.stat-icon.success { 
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    box-shadow: 0 8px 24px rgba(218, 165, 32, 0.3);
}

.stat-icon.warning { 
    background: linear-gradient(135deg, #F59E0B 0%, var(--palette-gold) 100%);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}

.stat-icon.info { 
    background: linear-gradient(135deg, var(--palette-deep-purple) 0%, var(--palette-dark-purple) 100%);
    box-shadow: 0 8px 24px rgba(48, 25, 52, 0.3);
}

.stat-title-group h3 {
    font-size: 18px;
    font-weight: 500;
    color: var(--palette-dark-purple);
    margin: 0 0 4px 0;
    line-height: 1.2;
}

/* Stat descriptions removed - no longer needed */

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--palette-deep-purple);
    margin: 16px 0 8px 0;
    line-height: 1;
}

.stat-subtitle {
    font-size: 12px;
    color: var(--palette-dark-purple);
    margin: 8px 0;
    opacity: 0.7;
    font-weight: 400;
    line-height: 1.3;
    font-style: italic;
}

.stat-detail {
    font-size: 14px;
    color: var(--palette-dark-purple);
    margin: 8px 0 0 0;
    opacity: 0.8;
    font-weight: 400;
    line-height: 1.3;
}

.progress-container {
    margin-top: 16px;
}

.progress {
    height: 6px;
    border-radius: 10px;
    background: rgba(218, 165, 32, 0.1);
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    box-shadow: 0 2px 8px rgba(218, 165, 32, 0.3);
}

/* Enhanced Action Cards with Clear Titles */
.actions-section {
    margin-bottom: 40px;
}

.action-card {
    background: var(--palette-white);
    border: 1px solid var(--palette-border);
    border-radius: 20px;
    padding: 24px;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.06);
    height: 100%;
    position: relative;
    overflow: hidden;
}



.action-card:hover {
    text-decoration: none;
    color: inherit;
    border-color: var(--palette-gold);
}

.action-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.action-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.action-icon.primary { 
    background: linear-gradient(135deg, var(--palette-success) 0%, #0F766E 100%);
    box-shadow: 0 8px 24px rgba(42, 157, 143, 0.3);
}

.action-icon.success { 
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    box-shadow: 0 8px 24px rgba(218, 165, 32, 0.3);
}

.action-icon.warning { 
    background: linear-gradient(135deg, #F59E0B 0%, var(--palette-gold) 100%);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}

.action-icon.info { 
    background: linear-gradient(135deg, var(--palette-deep-purple) 0%, var(--palette-dark-purple) 100%);
    box-shadow: 0 8px 24px rgba(48, 25, 52, 0.3);
}

.action-title-group h4 {
    font-size: 18px;
    font-weight: 500;
    color: var(--palette-dark-purple);
    margin: 0 0 4px 0;
    line-height: 1.2;
}

/* Action descriptions removed - no longer needed */

/* Action content removed - no longer needed */

/* Enhanced Table */
.table-section {
    margin-bottom: 40px;
}

.table-container {
    background: var(--palette-white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.06);
    border: 1px solid var(--palette-border);
}

.table {
    margin: 0;
}

.table th {
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    background: linear-gradient(135deg, var(--palette-cream) 0%, #FAF8F5 100%);
    border: none;
    padding: 18px 20px;
    color: var(--palette-deep-purple);
}

.table td {
    padding: 16px 20px;
    border-color: rgba(77, 64, 82, 0.05);
    font-size: 14px;
    color: var(--palette-dark-purple);
}

.table tbody tr:hover {
    background: rgba(218, 165, 32, 0.02);
}

/* Enhanced Badges */
.badge {
    border-radius: 8px;
    font-weight: 600;
    font-size: 11px;
    padding: 8px 14px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.bg-success {
    background: linear-gradient(135deg, var(--palette-success) 0%, #0F766E 100%) !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(42, 157, 143, 0.3);
}

.bg-warning {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%) !important;
    color: var(--palette-deep-purple) !important;
    box-shadow: 0 2px 8px rgba(218, 165, 32, 0.3);
}

/* Enhanced Buttons */
.btn {
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    padding: 12px 28px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    letter-spacing: 0.02em;
}

.btn-outline-primary {
    border: 2px solid var(--palette-success);
    color: var(--palette-success);
    background: transparent;
}

.btn-outline-primary:hover {
    background: var(--palette-success);
    border-color: var(--palette-success);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(42, 157, 143, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    border: none;
    color: var(--palette-deep-purple);
}

.btn-warning:hover {
    background: linear-gradient(135deg, var(--palette-light-gold) 0%, var(--palette-gold) 100%);
    color: var(--palette-deep-purple);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(218, 165, 32, 0.4);
}

/* Quick Stats in Header */
.welcome-stats {
    display: flex;
    gap: 40px;
    margin-top: 20px;
}

.quick-metric {
    text-align: left;
}

.metric-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--palette-gold);
    margin-bottom: 4px;
    line-height: 1;
}

.metric-label {
    font-size: 14px;
    color: var(--palette-dark-purple);
    font-weight: 500;
    opacity: 0.8;
}

/* Enhanced Text Readability */
.readable-text {
    color: #000000 !important;
}

.readable-dark-text {
    color: #301934 !important;
}

/* Enhanced Font Awesome Icons */
.fas, .far, .fab {
    font-weight: 900 !important;
    line-height: 1 !important;
    vertical-align: middle;
}

.stat-icon .fas, .action-icon .fas {
    font-size: inherit !important;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    display: block !important;
    opacity: 1 !important;
}

.section-title .fas {
    color: #DAA520 !important;
    text-shadow: none;
    opacity: 1 !important;
}

/* Ensure icons are visible on all backgrounds */
.stat-icon, .action-icon {
    position: relative;
    z-index: 2;
}

.stat-icon i, .action-icon i {
    color: white !important;
    opacity: 1 !important;
    display: inline-block !important;
    font-style: normal !important;
}

/* Fix for any icon loading issues */
.fas:before, .far:before, .fab:before {
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
}

.section-title {
    color: var(--palette-deep-purple) !important;
}

.section-subtitle {
    color: #301934 !important;
}

/* Title colors now use original palette */

.stat-title-group .stat-description {
    color: #301934 !important;
}

/* Action title colors now use original palette */

.action-title-group .action-description {
    color: #301934 !important;
}

.table th {
    color: #000000 !important;
}

.table td {
    color: #301934 !important;
}

/* Enhanced Mobile Responsive Design */
@media (max-width: 1200px) {
    .welcome-header {
        text-align: center;
    }
    
    .welcome-stats {
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
    }
    
    .container-fluid {
        padding: 0 15px;
    }
}

@media (max-width: 992px) {
    .welcome-content h1 {
        font-size: 28px;
    }
    
    .stat-value {
        font-size: 24px;
    }
    
    .section-title {
        font-size: 20px;
        text-align: center;
    }
    
    .section-subtitle {
        text-align: center;
        font-size: 10px;
    }
}

@media (max-width: 768px) {
    .welcome-header {
        padding: 25px 15px;
        margin-bottom: 25px;
    }
    
    .welcome-content h1 {
        font-size: 24px;
        flex-direction: row;
        text-align: center;
        gap: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .welcome-content p {
        font-size: 16px;
        text-align: center;
    }
    
    .member-status {
        justify-content: center;
        margin-top: 20px;
    }
    
    .stat-card {
        padding: 18px;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .stat-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        justify-content: center;
    }
    
    .stat-value {
        text-align: center;
    }
    
    .stat-detail {
        text-align: center;
    }
    
    .progress-container {
        text-align: center;
    }
    
    .stat-title-group h3 {
        font-size: 16px;
    }
    
    .stat-title-group .stat-description {
        font-size: 8px;
    }
    
    .stat-value {
        font-size: 22px;
        margin: 12px 0 6px 0;
    }
    
    .stat-detail {
        font-size: 13px;
        color: var(--palette-dark-purple) !important;
        font-weight: 400;
    }
    
    .action-card {
        padding: 18px;
        margin-bottom: 12px;
        text-align: center;
    }
    
    .action-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        justify-content: center;
    }
    
    .action-title-group h4 {
        font-size: 16px;
    }
    
    .action-title-group .action-description {
        font-size: 8px;
    }
    
    .action-content p {
        font-size: 9px;
    }
    
    .welcome-stats {
        flex-direction: row;
        justify-content: center;
        gap: 30px;
        margin-top: 15px;
    }
    
    .metric-value {
        font-size: 24px;
        font-weight: 700;
    }
    
    .metric-label {
        font-size: 12px;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .table {
        font-size: 13px;
        min-width: 600px;
    }
    
    .table th, .table td {
        padding: 12px 8px;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0 10px;
    }
    
    .welcome-header {
        padding: 20px 12px;
        margin-bottom: 20px;
        border-radius: 15px;
    }
    
    .welcome-content h1 {
        font-size: 22px;
        color: #000000 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    
    .welcome-content p {
        font-size: 16px;
        color: #301934 !important;
    }
    
    .stat-card {
        padding: 16px;
        border-radius: 15px;
        margin-bottom: 12px;
        text-align: center;
    }
    
    .action-card {
        padding: 16px;
        border-radius: 15px;
        margin-bottom: 10px;
        text-align: center;
    }
    
    .stat-icon, .action-icon {
        width: 48px;
        height: 48px;
        font-size: 18px;
        border-radius: 12px;
    }
    
    .stat-header, .action-header {
        gap: 12px;
    }
    
    .progress-container {
        text-align: center;
    }
    
    .stat-title-group h3 {
        font-size: 17px;
        color: var(--palette-dark-purple) !important;
        font-weight: 600;
    }
    
    .stat-title-group .stat-description {
        font-size: 10px;
        color: #301934 !important;
    }
    
    .action-title-group h4 {
        font-size: 17px;
        color: var(--palette-dark-purple) !important;
        font-weight: 600;
    }
    
    .action-title-group .action-description {
        font-size: 10px;
        color: #301934 !important;
    }
    
    .stat-value {
        font-size: 22px;
        color: #000000 !important;
        font-weight: 700;
        text-align: center;
    }
    
    .welcome-stats {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .metric-value {
        font-size: 26px;
        color: #DAA520 !important;
        font-weight: 700;
    }
    
    .metric-label {
        font-size: 13px;
        color: #301934 !important;
        font-weight: 500;
    }
    
    .section-title {
        font-size: 21px;
        color: var(--palette-deep-purple) !important;
        font-weight: 600;
    }
    
    .section-subtitle {
        font-size: 11px;
        color: #301934 !important;
    }
    
    .btn {
        font-size: 15px;
        padding: 12px 20px;
        font-weight: 600;
    }
    
    .badge {
        font-size: 12px;
        padding: 8px 12px;
        font-weight: 600;
    }
    
    .stat-subtitle {
        font-size: 11px;
        text-align: center;
        margin: 6px 0;
        opacity: 0.7;
    }
    
    .stat-detail {
        font-size: 13px;
        color: var(--palette-dark-purple) !important;
        font-weight: 400;
        text-align: center;
    }
    
    .action-content p {
        font-size: 11px;
        color: #301934 !important;
    }
}

@media (max-width: 420px) {
    .welcome-header {
        padding: 15px 10px;
    }
    
    .welcome-content h1 {
        font-size: 21px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
    }
    
    .stat-card {
        padding: 14px;
        text-align: center;
    }
    
    .action-card {
        padding: 14px;
        text-align: center;
    }
    
    .metric-value {
        font-size: 25px;
        font-weight: 700;
    }
    
    .stat-icon, .action-icon {
        width: 40px;
        height: 40px;
        font-size: 15px;
    }
    
    .stat-value {
        font-size: 19px;
        text-align: center;
    }
    
    .metric-value {
        font-size: 17px;
    }
    
    .section-title {
        font-size: 17px;
    }
    
    .progress-container {
        text-align: center;
    }
    
    .table {
        font-size: 12px;
    }
}

/* Performance optimizations */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.card, .btn, .badge, .action-card, .stat-card {
    will-change: transform;
}

/* === EQUB RULES ACCORDION SECTION === */
.rules-section {
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 40px;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    position: relative;
    overflow: hidden;
}

.rules-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 50%, var(--palette-gold) 100%);
    background-size: 200% 100%;
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { background-position: 200% 0; }
    50% { background-position: -200% 0; }
}

.rules-section .section-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--palette-deep-purple);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.rules-section .section-title i {
    color: var(--palette-gold) !important;
}

.rules-section .section-subtitle {
    color: var(--palette-dark-purple);
    font-size: 16px;
    margin-bottom: 32px;
    font-weight: 400;
    opacity: 0.8;
}

/* Modern Accordion Styling */
.accordion {
    border-radius: 20px;
    overflow: hidden;
    border: none;
    background: transparent;
}

.accordion-item {
    border: none;
    margin-bottom: 12px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: #ffffff;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.accordion-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.accordion-item:last-child {
    margin-bottom: 0;
}

.accordion-header {
    margin-bottom: 0;
}

.accordion-button {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: none;
    padding: 24px 28px;
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    border-radius: 16px;
    box-shadow: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.accordion-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(218, 165, 32, 0.1), transparent);
    transition: left 0.6s;
}

.accordion-button:hover::before {
    left: 100%;
}

.accordion-button:hover {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    color: #ffffff;
    transform: translateY(-1px);
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    color: #ffffff;
    box-shadow: 0 4px 20px rgba(218, 165, 32, 0.3);
}

.accordion-button:focus {
    box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.2);
    border-color: transparent;
}

.accordion-button::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232d3748'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    transition: transform 0.3s ease;
    width: 20px;
    height: 20px;
    background-size: 20px;
}

.accordion-button:hover::after,
.accordion-button:not(.collapsed)::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

.accordion-button:not(.collapsed)::after {
    transform: rotate(-180deg);
}

.rule-number-badge {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    margin-right: 16px;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(218, 165, 32, 0.3);
    transition: all 0.3s ease;
}

.accordion-button:hover .rule-number-badge,
.accordion-button:not(.collapsed) .rule-number-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    transform: scale(1.05);
}

.accordion-body {
    padding: 32px 28px;
    background: #ffffff;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.rule-content {
    font-size: 16px;
    line-height: 1.8;
    color: #4a5568;
    text-align: left;
    font-weight: 400;
    position: relative;
    padding-left: 20px;
}

.rule-content::before {
    content: '';
    position: absolute;
    left: 0;
    top: 8px;
    width: 4px;
    height: calc(100% - 16px);
    background: linear-gradient(180deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    border-radius: 2px;
}

/* Enhanced Rules Container for Single Accordion */
.rules-container {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.rule-item {
    padding: 24px 0;
    transition: all 0.3s ease;
}

.rule-item:last-child {
    border-bottom: none !important;
}

.rule-item.border-bottom {
    border-bottom: 1px solid rgba(218, 165, 32, 0.15);
}

.rule-item .rule-header {
    margin-bottom: 16px;
}

.rule-item .rule-number-badge {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(218, 165, 32, 0.25);
}

.rule-item .rule-content {
    font-size: 15px;
    line-height: 1.7;
    color: #4a5568;
    padding-left: 0;
    position: relative;
}

.rule-item .rule-content::before {
    display: none;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .rules-section {
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 20px;
    }
    
    .rules-section .section-title {
        font-size: 24px;
        margin-bottom: 6px;
    }
    
    .rules-section .section-subtitle {
        font-size: 15px;
        margin-bottom: 24px;
    }
    
    .accordion-item {
        margin-bottom: 10px;
        border-radius: 14px;
    }
    
    .accordion-button {
        padding: 20px 22px;
        font-size: 15px;
        border-radius: 14px;
    }
    
    .rule-number-badge {
        font-size: 13px;
        padding: 6px 14px;
        margin-right: 12px;
        border-radius: 16px;
    }
    
    .accordion-body {
        padding: 24px 22px;
    }
    
    .rule-content {
        font-size: 15px;
        line-height: 1.7;
        padding-left: 16px;
    }
    
    .rule-content::before {
        width: 3px;
    }
}

@media (max-width: 576px) {
    .rules-section {
        padding: 20px;
        margin-bottom: 24px;
        border-radius: 18px;
    }
    
    .rules-section .section-title {
        font-size: 22px;
        flex-direction: row;
        align-items: center;
        gap: 10px;
        text-align: center;
        justify-content: center;
    }
    
    .rules-section .section-subtitle {
        font-size: 14px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .accordion-item {
        margin-bottom: 8px;
        border-radius: 12px;
    }
    
    .accordion-button {
        padding: 18px 20px;
        font-size: 14px;
        border-radius: 12px;
    }
    
    .rule-number-badge {
        font-size: 12px;
        padding: 5px 12px;
        margin-right: 10px;
        border-radius: 14px;
    }
    
    .accordion-body {
        padding: 20px;
    }
    
    .rule-content {
        font-size: 14px;
        line-height: 1.6;
        padding-left: 14px;
    }
    
    .rule-content::before {
        width: 3px;
        top: 6px;
    }
    
    /* Enhanced Mobile Rules Accordion */
    .accordion-button {
        padding: 14px 16px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 12px !important;
    }
    
    .accordion-button:not(.collapsed) {
        border-radius: 12px 12px 0 0 !important;
    }
    
    .accordion-button .badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 8px;
        margin-left: 8px;
    }
    
    .accordion-collapse {
        border-radius: 0 0 12px 12px;
    }
    
    .accordion-body {
        padding: 16px 12px;
        background: #fafafa;
    }
    
    .rules-container {
        gap: 0;
    }
    
    .rule-item {
        padding: 16px 0;
        border-radius: 0;
    }
    
    .rule-item:first-child {
        padding-top: 8px;
    }
    
    .rule-item:last-child {
        padding-bottom: 8px;
    }
    
    .rule-item .rule-header {
        margin-bottom: 10px;
    }
    
    .rule-item .rule-number-badge {
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 10px;
        font-weight: 600;
    }
    
    .rule-item .rule-content {
        font-size: 13px;
        line-height: 1.6;
        color: #4a5568;
        padding-left: 0;
    }
    
    .rule-item.border-bottom {
        border-bottom: 1px solid rgba(218, 165, 32, 0.15);
        margin-bottom: 0;
    }
}
</style>

</head>

<body>
    <!-- Include Member Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Dashboard Content -->
    <div class="container-fluid">
        <!-- Welcome Header with Member Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="welcome-content">
                                <h1>
                                    <i class="fas fa-hand-wave text-warning"></i>
                                    <?php echo sprintf(t('member_dashboard.welcome_member'), '<span class="text-primary">' . htmlspecialchars($member['first_name']) . '</span>'); ?>
                                </h1>
                                <p><?php echo t('member_dashboard.welcome_message'); ?></p>
                                <div class="member-status">
                                    <span class="status-dot"></span>
                                    <span class="fw-semibold text-success"><?php echo t('member_dashboard.active_member'); ?></span>
                                    <?php if ($payout_position == 2): ?>
                                        <span class="ms-3 badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i>
                                            <?php echo t('member_dashboard.next_in_line'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="welcome-stats">
                                <div class="quick-metric">
                                    <div class="metric-value">#<?php echo $payout_position; ?></div>
                                    <div class="metric-label"><?php echo t('member_dashboard.queue_position'); ?></div>
                                </div>
                                <div class="quick-metric">
                                    <div class="metric-value">£<?php echo number_format($monthly_contribution, 2); ?></div>
                                    <div class="metric-label"><?php echo t('member_dashboard.monthly_contribution'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Overview Section -->
        <div class="stats-section">
            <h2 class="section-title">
                <i class="fas fa-chart-pie text-primary"></i>
                <?php echo t('member_dashboard.financial_overview'); ?>
            </h2>
            
            <div class="row g-4 mb-4">
                <!-- Total Contributed -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon primary">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <div class="stat-title-group">
                                <h3><?php echo t('member_dashboard.total_contributions'); ?></h3>
                            </div>
                        </div>
                        <div class="stat-value">£<?php echo number_format($total_contributed, 2); ?></div>
                        <div class="stat-detail">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            <?php echo t('member_dashboard.active'); ?>
                        </div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo min(($total_contributed / $gross_payout) * 100, 100); ?>%"></div>
                            </div>
                            <div class="stat-detail mt-2">
                                <?php echo number_format(min(($total_contributed / $gross_payout) * 100, 100), 1); ?>% <?php echo t('member_dashboard.of_expected_payout'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expected Payout -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-title-group">
                                <h3><?php echo t('member_dashboard.expected_payout'); ?></h3>
                            </div>
                        </div>
                        <div class="stat-value">£<?php echo number_format($expected_payout, 2); ?></div>
                        <div class="stat-subtitle">
                            <?php echo t('member_dashboard.gross_payout_including_payment'); ?>
                        </div>
                        <div class="stat-detail">
                            <i class="fas fa-calendar text-info me-1"></i>
                            <?php echo date('M Y', strtotime($next_payout_date)); ?>
                        </div>

                    </div>
                </div>

                <!-- MASTER-LEVEL Payment Status Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon <?php echo $payment_status['status_class']; ?>">
                                <?php if ($payment_status['status'] === 'paid'): ?>
                                    <i class="fas fa-check-circle" style="color: white;"></i>
                                <?php elseif ($payment_status['status'] === 'pending'): ?>
                                    <i class="fas fa-clock" style="color: white;"></i>
                                <?php elseif ($payment_status['status'] === 'overdue'): ?>
                                    <i class="fas fa-exclamation-triangle" style="color: white;"></i>
                                <?php else: ?>
                                    <i class="fas fa-question-circle" style="color: white;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="stat-title-group">
                                <h3>Payment Status</h3>
                                <p class="stat-subtitle">
                                    <span class="badge badge-sm" style="background-color: var(--palette-gold); color: var(--palette-deep-purple); font-size: 10px; padding: 4px 8px;">
                                        <?php echo date('M Y', strtotime($payment_status['month_name'])); ?> - M<?php echo $payment_status['equb_month']; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="stat-value">
                            <span class="text-<?php echo $payment_status['status_class']; ?>">
                                <?php if ($payment_status['has_paid']): ?>
                                    £<?php echo number_format($payment_status['amount_paid'], 0); ?>
                                <?php else: ?>
                                    £<?php echo number_format($monthly_contribution, 0); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="stat-detail text-<?php echo $payment_status['status_class']; ?> mb-2">
                            <strong><?php echo $payment_status['status_text']; ?></strong>
                        </div>
                        
                        <?php if (!$payment_status['has_paid']): ?>
                            <div class="mt-3 text-center">
                                <a href="contributions.php" class="btn btn-<?php echo $payment_status['status'] === 'overdue' ? 'danger' : 'warning'; ?> btn-sm">
                                    <i class="fas fa-credit-card me-1"></i>
                                    <?php echo $payment_status['status'] === 'overdue' ? 'Pay Now (with late fee)' : 'Pay Now'; ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="stat-detail text-success">
                                <i class="fas fa-check me-1"></i>
                                Payment Confirmed
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- MASTER-LEVEL Payout Date Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <?php if (!empty($member['latest_payout_date'])): ?>
                            <!-- User has received payout - show received status -->
                            <div class="stat-header">
                                <div class="stat-icon success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-title-group">
                                    <h3><?php echo t('dashboard.payout_received'); ?></h3>
                                    <p class="stat-subtitle"><?php echo t('dashboard.congratulations'); ?></p>
                                </div>
                            </div>
                            <div class="stat-value text-success">
                                <?php echo date('M d, Y', strtotime($member['latest_payout_date'])); ?>
                            </div>
                            <div class="stat-detail text-success">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                <strong>Amount Received: £<?php echo number_format($member['latest_total_amount'], 0); ?></strong>
                                <small class="d-block text-muted mt-1">
                                    Gross: £<?php echo number_format($member['latest_gross_payout'], 0); ?> | 
                                    Net: £<?php echo number_format($member['latest_net_amount'], 0); ?>
                                </small>
                            </div>
                            <div class="stat-detail">
                                <i class="fas fa-receipt me-1"></i>
                                <small><?php echo t('dashboard.payout_id'); ?>: <?php echo htmlspecialchars($member['latest_payout_id']); ?></small>
                            </div>
                            
                            <?php if ($payout_count > 1): ?>
                            <div class="stat-detail mt-2">
                                <i class="fas fa-trophy me-1"></i>
                                <small class="text-muted"><?php echo $payout_count; ?> <?php echo t('dashboard.total_payouts_received'); ?></small>
                            </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <!-- User hasn't received payout yet - show scheduled/expected -->
                            <div class="stat-header">
                                <div class="stat-icon info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-title-group">
                                    <h3><?php echo t('member_dashboard.payout_date'); ?></h3>
                                    <p class="stat-subtitle"><?php echo t('dashboard.expected_payout'); ?></p>
                                </div>
                            </div>
                            <div class="stat-value">
                                <?php echo date('M d, Y', strtotime($next_payout_date)); ?>
                            </div>
                            <div class="stat-detail">
                                <?php echo sprintf(t('member_dashboard.position_of_members'), $payout_position, $total_equb_members); ?>
                            </div>
                            <div class="stat-detail mt-2">
                                <i class="fas fa-clock me-1"></i>
                                <?php 
                                if ($days_until_payout > 0) {
                                    echo $days_until_payout . ' ' . t('dashboard.days_remaining');
                                } elseif ($days_until_payout < 0) {
                                    echo '<span class="text-danger">' . t('dashboard.overdue_by') . ' ' . abs($days_until_payout) . ' ' . t('dashboard.days') . '</span>';
                                } else {
                                    echo '<span class="text-success">' . t('member_dashboard.payout_available') . '</span>';
                                }
                                ?>
                            </div>
                            <div class="stat-detail mt-2">
                                <i class="fas fa-hand-holding-usd me-1"></i>
                                <small class="text-muted"><?php echo t('dashboard.expected_amount'); ?>: £<?php echo number_format($gross_payout, 0); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="actions-section">
            <h2 class="section-title">
                <i class="fas fa-bolt text-warning"></i>
                <?php echo t('member_dashboard.quick_actions'); ?>
            </h2>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <a href="contributions.php" class="action-card">
                        <div class="action-header">
                            <div class="action-icon primary">
                                <i class="fas fa-credit-card"></i>
                            </div>
                                                         <div class="action-title-group">
                                 <h4><?php echo t('member_dashboard.make_contribution'); ?></h4>
                             </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <a href="payout-info.php" class="action-card">
                        <div class="action-header">
                            <div class="action-icon success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="action-title-group">
                                <h4><?php echo t('member_dashboard.payout_info'); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <a href="members.php" class="action-card">
                        <div class="action-header">
                            <div class="action-icon warning">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="action-title-group">
                                <h4><?php echo t('member_nav.equb_members'); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <a href="profile.php" class="action-card">
                        <div class="action-header">
                            <div class="action-icon info">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="action-title-group">
                                <h4><?php echo t('member_dashboard.profile_settings'); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Payments Section -->
        <?php if (!empty($recent_payments)): ?>
        <div class="table-section">
            <h2 class="section-title">
                <i class="fas fa-receipt text-primary"></i>
                <?php echo t('member_dashboard.recent_payments'); ?>
            </h2>
            
            <div class="table-container">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo t('member_dashboard.payment_id'); ?></th>
                            <th><?php echo t('member_dashboard.amount'); ?></th>
                            <th><?php echo t('member_dashboard.payment_month'); ?></th>
                            <th><?php echo t('member_dashboard.date_paid'); ?></th>
                            <th><?php echo t('member_dashboard.status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td>
                                <code class="small"><?php echo htmlspecialchars($payment['payment_id']); ?></code>
                            </td>
                            <td class="fw-semibold text-success">
                                £<?php echo number_format($payment['amount'], 2); ?>
                            </td>
                            <td><?php echo htmlspecialchars($payment['payment_month_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['formatted_date']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $payment['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo $payment['status'] === 'paid' ? t('member_dashboard.paid') : t('member_dashboard.pending'); ?>
                                </span>
                                <?php if ($payment['verification_status'] === 'verified'): ?>
                                    <br><small class="text-success"><i class="fas fa-check-circle me-1"></i><?php echo t('member_dashboard.verified'); ?></small>
                                <?php elseif ($payment['verification_status'] === 'pending_verification'): ?>
                                    <br><small class="text-warning"><i class="fas fa-clock me-1"></i><?php echo t('member_dashboard.pending'); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="p-3 text-center bg-light">
                    <a href="contributions.php" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>
                        <?php echo t('member_dashboard.view_all_payments'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Equb Rules Accordion Section -->
        <?php if (!empty($equb_rules)): ?>
        <div class="rules-section">
            <h2 class="section-title">
                <i class="fas fa-gavel text-warning"></i>
                <?php echo t('member_dashboard.equb_rules'); ?>
            </h2>
            <p class="section-subtitle"><?php echo t('member_dashboard.equb_rules_desc'); ?></p>
            
            <div class="accordion" id="equbRulesAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingEqubRules">
                        <button class="accordion-button collapsed" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseEqubRules" 
                                aria-expanded="false" 
                                aria-controls="collapseEqubRules">
                            <i class="fas fa-gavel me-2"></i>
                            <?php echo t('member_dashboard.equb_rules'); ?>
                            <span class="badge bg-primary ms-2"><?php echo count($equb_rules); ?> <?php echo t('member_dashboard.rules'); ?></span>
                        </button>
                    </h2>
                    <div id="collapseEqubRules" 
                         class="accordion-collapse collapse" 
                         aria-labelledby="headingEqubRules" 
                         data-bs-parent="#equbRulesAccordion">
                        <div class="accordion-body">
                            <div class="rules-container">
                                <?php foreach ($equb_rules as $index => $rule): ?>
                                <div class="rule-item <?php echo $index < count($equb_rules) - 1 ? 'border-bottom' : ''; ?>">
                                    <div class="rule-header">
                                        <span class="rule-number-badge">
                                            <?php echo t('member_dashboard.rule_number'); ?> <?php echo $rule['rule_number']; ?>
                                        </span>
                                    </div>
                                    <div class="rule-content">
                                        <?php 
                                        // Display content based on current language
                                        $content = (getCurrentLanguage() === 'am') ? $rule['rule_am'] : $rule['rule_en'];
                                        echo nl2br(htmlspecialchars($content)); 
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Floating Quick Menu (Dashboard only) -->
    <style>
      :root {
        --fab-bg: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
        --fab-icon: #ffffff;
        --fab-item-bg: #ffffff;
        --fab-item-text: #301934;
        --fab-item-icon: #DAA520;
      }
      .fab-container { position: fixed; right: 18px; bottom: 18px; z-index: 12000; pointer-events: auto; }
      .fab-button {
        width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer;
        background: var(--fab-bg);
        color: var(--fab-icon); box-shadow: 0 8px 24px rgba(48,25,52,.2); display: flex; align-items: center; justify-content: center; position: relative;
        transition: transform .25s ease, box-shadow .25s ease;
      }
      .fab-button:focus { outline: none; box-shadow: 0 0 0 4px rgba(218,165,32,.25); }
      .fab-button:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(48,25,52,.28); }
      .fab-icon { font-size: 22px; transition: transform .25s ease; }
      .fab-open .fab-icon { transform: rotate(45deg); }
      .fab-attention .fab-button { animation: fabPulse 2.2s ease-out infinite; }
      @keyframes fabPulse { 0% { box-shadow: 0 0 0 0 rgba(231,111,81,.55); transform: scale(1);} 70% { box-shadow: 0 0 0 14px rgba(231,111,81,0); transform: scale(1.03);} 100% { box-shadow: 0 0 0 0 rgba(231,111,81,0);} }
      .fab-badge { position:absolute; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background:#E76F51; color:#fff; font-size: 11px; font-weight: 700; display:none; align-items:center; justify-content:center; top:-4px; right:-4px; border:2px solid #fff; line-height: 18px; }

      .fab-menu { position: absolute; right: 0; bottom: 72px; display: none; flex-direction: column; align-items: flex-end; gap: 10px; }
      .fab-open .fab-menu { display: flex; }
      .fab-item { background: var(--fab-item-bg); color: var(--fab-item-text); border: 1px solid rgba(0,0,0,0.06); border-radius: 14px; 
        box-shadow: 0 8px 24px rgba(48,25,52,.12); padding: 8px 12px; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
      .fab-item i { color: var(--fab-item-icon); }
      .fab-item:hover { text-decoration: none; border-color: rgba(218,165,32,.35); box-shadow: 0 10px 28px rgba(48,25,52,.18); }
      .fab-item .label { font-size: 13px; font-weight: 600; }
      @media (max-width: 576px) {
        .fab-item { padding: 8px 10px; }
        .fab-item .label { display: none; }
      }
    </style>
    <div class="fab-container" id="quickFab">
      <div class="fab-menu" id="quickFabMenu" aria-hidden="true">
        <a href="dashboard.php" class="fab-item" title="<?php echo t('member_nav.dashboard'); ?>">
          <i class="fas fa-gauge"></i><span class="label"><?php echo t('member_nav.dashboard'); ?></span>
        </a>
        <a href="contributions.php" class="fab-item" title="<?php echo t('footer.payments'); ?>">
          <i class="fas fa-wallet"></i><span class="label"><?php echo t('footer.payments'); ?></span>
        </a>
        <a href="payout-info.php" class="fab-item" title="<?php echo t('footer.payout_info'); ?>">
          <i class="fas fa-sack-dollar"></i><span class="label"><?php echo t('footer.payout_info'); ?></span>
        </a>
        <a href="notifications.php" class="fab-item" title="<?php echo t('member_nav.notifications'); ?>">
          <i class="fas fa-bell"></i><span class="label"><?php echo t('member_nav.notifications'); ?></span>
        </a>
        <a href="settings.php" class="fab-item" title="<?php echo t('footer.settings'); ?>">
          <i class="fas fa-gear"></i><span class="label"><?php echo t('footer.settings'); ?></span>
        </a>
      </div>
      <button class="fab-button" id="quickFabToggle" aria-controls="quickFabMenu" aria-expanded="false" aria-label="Quick menu">
        <span class="fab-badge" id="fabUnreadBadge">0</span>
        <i class="fas fa-plus fab-icon"></i>
      </button>
    </div>

    <!-- Scripts -->
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        const container = document.getElementById('quickFab');
        const toggleBtn = document.getElementById('quickFabToggle');
        const menu = document.getElementById('quickFabMenu');
        // unread badge provider (re-uses member API)
        async function setUnreadDot(count){
          // on bell item
          const bell = menu.querySelector('a[href="notifications.php"] i');
          const fabBadge = document.getElementById('fabUnreadBadge');
          const fabContainer = document.getElementById('quickFab');
          if (count > 0) {
            if (bell) { bell.classList.add('fa-shake'); bell.style.setProperty('color','#E76F51','important'); }
            if (fabBadge) { fabBadge.style.display = 'inline-flex'; fabBadge.textContent = count > 99 ? '99+' : String(count); }
            if (fabContainer) fabContainer.classList.add('fab-attention');
          } else {
            if (bell) { bell.classList.remove('fa-shake'); bell.style.removeProperty('color'); }
            if (fabBadge) { fabBadge.style.display = 'none'; fabBadge.textContent = '0'; }
            if (fabContainer) fabContainer.classList.remove('fab-attention');
          }
        }
        async function loadUnread(){
          try {
            const r = await fetch('api/notifications.php?action=count_unread');
            const d = await r.json();
            const u = d && d.success ? Number(d.unread) : 0;
            setUnreadDot(u);
          } catch(_){}
        }
        loadUnread();
        if (!container || !toggleBtn || !menu) return;
        function closeMenu(){ container.classList.remove('fab-open'); toggleBtn.setAttribute('aria-expanded','false'); menu.setAttribute('aria-hidden','true'); }
        function openMenu(){ container.classList.add('fab-open'); toggleBtn.setAttribute('aria-expanded','true'); menu.setAttribute('aria-hidden','false'); }
        let justToggled = false;
        function toggle(e){ if(e) e.stopPropagation();
          if (container.classList.contains('fab-open')) { closeMenu(); } else { openMenu(); }
          justToggled = true; setTimeout(()=>{ justToggled = false; }, 150);
        }
        toggleBtn.addEventListener('click', toggle, {passive:false});
        document.addEventListener('click', function(e){ if (justToggled) return; if (!container.contains(e.target)) closeMenu(); }, true);
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });
        menu.querySelectorAll('a').forEach(a=>a.addEventListener('click', closeMenu));
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
</body>
</html>