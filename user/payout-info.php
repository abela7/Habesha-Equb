<?php
/**
 * HabeshaEqub - Professional Payout Information Page
 * Top-tier financial dashboard for member payout details and timeline
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

// Ensure translation function exists
if (!function_exists('t')) {
    function t($key) {
        return $key; // Fallback to key if translation function doesn't exist
    }
}

// Secure authentication check
require_once 'includes/auth_guard.php';
$user_id = get_current_user_id();

// Get REAL member data and payout information with equb details
try {
    $stmt = $pdo->prepare("
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
               CASE 
                   WHEN m.membership_type = 'joint' THEN m.individual_contribution
                   ELSE m.monthly_payment
               END as individual_contribution,
               -- Enhanced EQUB Settings
               es.equb_name, es.start_date, es.end_date, es.payout_day, es.duration_months, 
               es.max_members, es.current_members, es.status as equb_status,
               es.admin_fee, es.late_fee, es.grace_period_days, es.regular_payment_tier,
               es.currency,
               -- Member Statistics
               (SELECT COUNT(*) FROM members WHERE equb_settings_id = m.equb_settings_id AND is_active = 1) as total_equb_members,
               COALESCE(SUM(CASE WHEN p.status IN ('paid', 'completed') THEN p.amount ELSE 0 END), 0) as total_contributed,
               COUNT(p.id) as total_payments,
               COALESCE(SUM(p.late_fee), 0) as total_late_fees,
               MAX(p.payment_date) as last_payment_date,
               -- Payout Statistics  
               COUNT(po.id) as total_payouts_received,
               COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as total_amount_received,
               MAX(po.actual_payout_date) as last_payout_received_date,
               -- EQUB Progress Calculations
               CASE 
                   WHEN es.start_date IS NOT NULL THEN 
                       GREATEST(0, TIMESTAMPDIFF(MONTH, es.start_date, CURDATE()) + 1)
                   ELSE 1
               END as months_in_equb,
               CASE 
                   WHEN es.duration_months IS NOT NULL AND es.start_date IS NOT NULL THEN 
                       GREATEST(0, es.duration_months - (TIMESTAMPDIFF(MONTH, es.start_date, CURDATE()) + 1))
                   ELSE 0
               END as remaining_months_in_equb
        FROM members m 
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        LEFT JOIN payments p ON m.id = p.member_id
        LEFT JOIN payouts po ON m.id = po.member_id
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

// Calculate payout information using Traditional EQUB Logic
$member_name = trim($member['first_name'] . ' ' . $member['last_name']);
$monthly_contribution = (float)$member['effective_monthly_payment'];
$payout_position = (int)$member['actual_payout_position'];
$total_equb_members = (int)$member['total_equb_members'];

// GOLDEN LOGIC: Use EnhancedEqubCalculator for top-tier calculations
require_once '../includes/enhanced_equb_calculator_final.php';
try {
    if (!class_exists('EnhancedEqubCalculator')) {
        throw new Exception('EnhancedEqubCalculator class not found');
    }
    $enhanced_calculator = new EnhancedEqubCalculator($pdo);
    $calculation_result = $enhanced_calculator->calculateMemberFriendlyPayout($user_id);
    
    if ($calculation_result['success']) {
        $member_calculation = $calculation_result['calculation'];
        $position_coefficient = $member_calculation['position_coefficient'];
        $total_monthly_pool = $member_calculation['total_monthly_pool'];
        $gross_payout = $member_calculation['gross_payout'];
        $admin_fee = $member_calculation['admin_fee'];
        $monthly_deduction = $member_calculation['monthly_deduction'];
        $display_payout = $member_calculation['display_payout']; // gross - admin fee (what member sees)
        $expected_payout = $display_payout; // For display purposes
        $real_net_payout = $member_calculation['real_net_payout']; // gross - admin fee - monthly
        $calculation_method = $member_calculation['calculation_method'] ?? 'enhanced';
        $calculation_details = [
            'position_coefficient' => $position_coefficient,
            'total_monthly_pool' => $total_monthly_pool,
            'gross_payout' => $gross_payout,
            'admin_fee' => $admin_fee,
            'monthly_deduction' => $monthly_deduction,
            'display_payout' => $display_payout,
            'real_net_payout' => $real_net_payout,
            'calculation_method' => $calculation_method
        ];
} else {
        // Enhanced fallback calculation
        $position_coefficient = $monthly_contribution / ($member['regular_payment_tier'] ?? 1000);
        $total_monthly_pool = $monthly_contribution * $total_equb_members;
        $gross_payout = $position_coefficient * $total_monthly_pool;
        $admin_fee = (float)($member['admin_fee'] ?? 20);
        $monthly_deduction = $monthly_contribution;
        $display_payout = $gross_payout - $admin_fee;
        $expected_payout = $display_payout;
        $real_net_payout = $gross_payout - $admin_fee - $monthly_deduction;
        $calculation_method = 'fallback';
        $calculation_details = [
            'position_coefficient' => $position_coefficient,
            'total_monthly_pool' => $total_monthly_pool,
            'gross_payout' => $gross_payout,
            'admin_fee' => $admin_fee,
            'monthly_deduction' => $monthly_deduction,
            'display_payout' => $display_payout,
            'real_net_payout' => $real_net_payout,
            'calculation_method' => $calculation_method
        ];
        error_log("Enhanced calculation failed for user {$user_id}: " . ($calculation_result['error'] ?? 'Unknown error'));
    }
} catch (Exception $e) {
    error_log("Enhanced calculator error: " . $e->getMessage());
    // Basic fallback
    $position_coefficient = 1;
    $total_monthly_pool = $monthly_contribution * $total_equb_members;
    $gross_payout = $total_monthly_pool;
    $admin_fee = 20;
    $monthly_deduction = $monthly_contribution;
    $display_payout = $gross_payout - $admin_fee;
    $expected_payout = $display_payout;
    $real_net_payout = $gross_payout - $admin_fee - $monthly_deduction;
    $calculation_method = 'error';
    $calculation_details = [
        'position_coefficient' => $position_coefficient,
        'total_monthly_pool' => $total_monthly_pool,
        'gross_payout' => $gross_payout,
        'admin_fee' => $admin_fee,
        'monthly_deduction' => $monthly_deduction,
        'display_payout' => $display_payout,
        'real_net_payout' => $real_net_payout,
        'calculation_method' => $calculation_method
    ];
}

// GET REAL PAYOUT INFORMATION using our top-tier sync service
$payout_service = getPayoutSyncService();
$payout_info = $payout_service->getMemberPayoutStatus($user_id);

if (isset($payout_info['error'])) {
    error_log("Payout calculation error for user $user_id: " . $payout_info['message']);
    // Fallback to database payout_month
    $payout_date = $member['payout_month'] ?? date('Y-m-d');
    $days_until_payout = floor((strtotime($payout_date) - time()) / (60 * 60 * 24));
} else {
    // Use calculated payout information
    $payout_date = $payout_info['calculated_payout_date'];
    $days_until_payout = $payout_info['days_until_payout'];
}

// Get all members for payout queue display with enhanced privacy logic
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.first_name, m.last_name, m.payout_position, m.monthly_payment,
               m.go_public, m.position_coefficient, m.payout_month, m.membership_type,
               CASE 
                   WHEN po.id IS NOT NULL AND po.status = 'completed' THEN 'completed'
                   WHEN m.payout_position = ? THEN 'current'
                   WHEN m.payout_position < ? THEN 'upcoming'
                   ELSE 'pending'
               END as payout_status,
               po.actual_payout_date as received_date,
               po.gross_payout, po.total_amount, po.net_amount,
               po.status as payout_record_status
        FROM members m
        LEFT JOIN payouts po ON m.id = po.member_id AND po.status = 'completed'
        WHERE m.equb_settings_id = ? AND m.is_active = 1
        ORDER BY m.payout_position ASC
    ");
    $stmt->execute([$payout_position, $payout_position, $member['equb_settings_id']]);
    $payout_queue_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group members by payout position to handle shared positions
    $position_groups = [];
    foreach ($payout_queue_raw as $queue_member) {
        $position = $queue_member['payout_position'];
        if (!isset($position_groups[$position])) {
            $position_groups[$position] = [];
        }
        $position_groups[$position][] = $queue_member;
    }
    
    // Process each position group and create consolidated payout queue
    $payout_queue = [];
    foreach ($position_groups as $position => $members) {
        if (count($members) > 1) {
            // SHARED POSITION: Multiple members share this position
            $combined_monthly_payment = 0;
            $combined_coefficient = 0;
            $all_payout_months = [];
            $has_current_user = false;
            $position_status = 'pending';
            $received_date = null;
            $all_completed = true;
            $member_names = [];
            
            // Aggregate data for shared position
            foreach ($members as $member_data) {
                $combined_monthly_payment += $member_data['monthly_payment'];
                $combined_coefficient += $member_data['position_coefficient'];
                if (!empty($member_data['payout_month'])) {
                    $all_payout_months[] = $member_data['payout_month'];
                }
                if ($member_data['id'] == $user_id) {
                    $has_current_user = true;
                }
                if ($member_data['payout_status'] === 'current') {
                    $position_status = 'current';
                } elseif ($member_data['payout_status'] === 'completed') {
                    $position_status = 'completed';
                    if ($member_data['received_date']) {
                        $received_date = $member_data['received_date'];
                    }
                } else {
                    $all_completed = false;
                }
                
                // Collect member names based on privacy settings
                if ($member_data['go_public'] == 1 || $member_data['id'] == $user_id) {
                    $name = trim($member_data['first_name'] . ' ' . $member_data['last_name']);
                    if ($member_data['id'] == $user_id) {
                        $member_names[] = $name . ' (You)';
                    } else {
                        $member_names[] = $name;
                    }
                } else {
                    $member_names[] = t('payout_info.anonymous');
                }
            }
            
            // Calculate combined payout for shared position
            $gross_payout = $combined_coefficient * $total_monthly_pool;
            $display_payout = $gross_payout - $member['admin_fee'];
            $net_payout = $gross_payout - $member['admin_fee'] - $combined_monthly_payment;
            
            // Use the earliest payout month if multiple exist
            $payout_month = !empty($all_payout_months) ? min($all_payout_months) : null;
            
            $payout_queue[] = [
                'id' => 'joint_' . $position,
                'first_name' => '',
                'last_name' => '',
                'display_name' => t('payout_info.joint_equb'),
                'member_names' => $member_names,
                'is_anonymous' => false,
                'is_current_user' => $has_current_user,
                'is_joint_position' => true,
                'payout_position' => $position,
                'monthly_payment' => $combined_monthly_payment,
                'position_coefficient' => $combined_coefficient,
                'payout_status' => $position_status,
                'received_date' => $received_date,
                'payout_month' => $payout_month,
                'gross_payout' => $gross_payout,
                'display_payout' => $display_payout,
                'net_payout' => $net_payout,
                'received_amount' => null,
                'payout_record_status' => $position_status,
                'member_count' => count($members)
            ];
        } else {
            // INDIVIDUAL POSITION: Single member
            $queue_member = $members[0];
            
            // Privacy logic: Show real name only if go_public=1 OR if it's the logged-in member
            if ($queue_member['go_public'] == 1 || $queue_member['id'] == $user_id) {
                $display_name = trim($queue_member['first_name'] . ' ' . $queue_member['last_name']);
                $is_anonymous = false;
            } else {
                $display_name = t('payout_info.anonymous');
                $is_anonymous = true;
            }
            
            // Calculate dynamic payout using enhanced calculator
            try {
                if (isset($enhanced_calculator) && is_object($enhanced_calculator)) {
                    $calc_result = $enhanced_calculator->calculateMemberFriendlyPayout($queue_member['id']);
                    if ($calc_result['success']) {
                        $gross_payout = $calc_result['calculation']['gross_payout'];
                        $display_payout = $calc_result['calculation']['display_payout'];
                        $net_payout = $calc_result['calculation']['real_net_payout'];
                    } else {
                        // Fallback calculation
                        $gross_payout = $queue_member['position_coefficient'] * $total_monthly_pool;
                        $display_payout = $gross_payout - $member['admin_fee'];
                        $net_payout = $gross_payout - $member['admin_fee'] - $queue_member['monthly_payment'];
                    }
                } else {
                    // Fallback calculation if calculator not available
                    $gross_payout = $queue_member['position_coefficient'] * $total_monthly_pool;
                    $display_payout = $gross_payout - $member['admin_fee'];
                    $net_payout = $gross_payout - $member['admin_fee'] - $queue_member['monthly_payment'];
                }
            } catch (Exception $e) {
                // Fallback calculation
                $gross_payout = $queue_member['position_coefficient'] * $total_monthly_pool;
                $display_payout = $gross_payout - $member['admin_fee'];
                $net_payout = $gross_payout - $member['admin_fee'] - $queue_member['monthly_payment'];
                error_log("Calculator error for member {$queue_member['id']}: " . $e->getMessage());
            }
            
            $payout_queue[] = [
                'id' => $queue_member['id'],
                'first_name' => $queue_member['first_name'],
                'last_name' => $queue_member['last_name'],
                'display_name' => $display_name,
                'is_anonymous' => $is_anonymous,
                'is_current_user' => ($queue_member['id'] == $user_id),
                'is_joint_position' => false,
                'payout_position' => $queue_member['payout_position'],
                'monthly_payment' => $queue_member['monthly_payment'],
                'position_coefficient' => $queue_member['position_coefficient'],
                'payout_status' => $queue_member['payout_status'],
                'received_date' => $queue_member['received_date'],
                'payout_month' => $queue_member['payout_month'],
                'gross_payout' => $gross_payout,
                'display_payout' => $display_payout,
                'net_payout' => $net_payout,
                'received_amount' => $queue_member['net_amount'],
                'payout_record_status' => $queue_member['payout_record_status']
            ];
        }
    }
    
    // Sort by position to ensure proper order
    usort($payout_queue, function($a, $b) {
        return $a['payout_position'] - $b['payout_position'];
    });
} catch (PDOException $e) {
    error_log("Error fetching payout queue: " . $e->getMessage());
    $payout_queue = [];
}

// Get member's payout history
try {
    $stmt = $pdo->prepare("
        SELECT po.*, 
               DATE_FORMAT(po.payout_date, '%M %d, %Y') as formatted_date,
               DATE_FORMAT(po.payout_date, '%M %Y') as payout_month_name
        FROM payouts po 
        WHERE po.member_id = ?
        ORDER BY po.payout_date DESC
    ");
    $stmt->execute([$user_id]);
    $payout_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $payout_history = [];
}

// Strong cache buster for assets
$cache_buster = time() . '_' . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title><?php echo t('payout.page_title'); ?> - HabeshaEqub</title>
    
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
/* === PROFESSIONAL PAYOUT INFORMATION DESIGN === */

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

/* Enhanced Page Header */
.page-header {
    background: linear-gradient(135deg, var(--palette-cream) 0%, #FAF8F5 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 40px;
    border: 1px solid var(--palette-border);
    box-shadow: 0 8px 32px rgba(48, 25, 52, 0.08);
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--palette-deep-purple);
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    font-size: 18px;
    color: var(--palette-dark-purple);
    margin: 0;
    font-weight: 400;
    opacity: 0.8;
}

/* Section Styling */
.section-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--palette-deep-purple);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Payout Cards */
.payout-card {
    background: var(--palette-white);
    border-radius: 20px;
    padding: 28px;
    border: 1px solid var(--palette-border);
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.06);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
    margin-bottom: 30px;
}

.payout-card::before {
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

.payout-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 40px rgba(48, 25, 52, 0.15);
    border-color: rgba(218, 165, 32, 0.2);
}

.payout-card:hover::before {
    transform: scaleX(1);
}

/* Card Header with Icon */
.payout-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.payout-icon {
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

.payout-icon.primary { 
    background: linear-gradient(135deg, var(--palette-success) 0%, #0F766E 100%);
    box-shadow: 0 8px 24px rgba(42, 157, 143, 0.3);
}

.payout-icon.success { 
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    box-shadow: 0 8px 24px rgba(218, 165, 32, 0.3);
}

.payout-icon.warning { 
    background: linear-gradient(135deg, #F59E0B 0%, var(--palette-gold) 100%);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}

.payout-icon.info { 
    background: linear-gradient(135deg, var(--palette-deep-purple) 0%, var(--palette-dark-purple) 100%);
    box-shadow: 0 8px 24px rgba(48, 25, 52, 0.3);
}

.payout-title-group h3 {
    font-size: 18px;
    font-weight: 500;
    color: var(--palette-dark-purple);
    margin: 0 0 4px 0;
    line-height: 1.2;
}

.payout-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--palette-deep-purple);
    margin: 16px 0 8px 0;
    line-height: 1;
}

.payout-detail {
    font-size: 14px;
    color: var(--palette-dark-purple);
    margin: 8px 0 0 0;
    opacity: 0.8;
    font-weight: 400;
    line-height: 1.3;
}

/* Journey Timeline Styling */
.journey-container {
    background: var(--palette-white);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid var(--palette-border);
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.06);
    margin-bottom: 30px;
}

.journey-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 20px;
    background: linear-gradient(135deg, var(--palette-cream) 0%, #FAF8F5 100%);
    border-radius: 16px;
    border: 1px solid var(--palette-border);
}

.journey-progress {
    flex: 1;
    margin-right: 30px;
}

.progress-track {
    height: 8px;
    background: rgba(218, 165, 32, 0.2);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    border-radius: 10px;
    transition: width 0.8s ease;
    box-shadow: 0 2px 8px rgba(218, 165, 32, 0.4);
}

.journey-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: var(--palette-gold);
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: var(--palette-dark-purple);
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.journey-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.journey-step {
    position: relative;
    background: var(--palette-light-bg);
    border: 2px solid var(--palette-border);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}

.journey-step::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--palette-border);
    transition: background 0.4s ease;
}

.journey-step.current {
    background: rgba(218, 165, 32, 0.05);
    border-color: var(--palette-gold);
    transform: scale(1.02);
}

.journey-step.current::before {
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
}

.journey-step.next {
    background: rgba(42, 157, 143, 0.05);
    border-color: var(--palette-success);
}

.journey-step.next::before {
    background: linear-gradient(90deg, var(--palette-success) 0%, #0F766E 100%);
}

.journey-step:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(48, 25, 52, 0.12);
}

.step-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--palette-white);
    border: 3px solid var(--palette-border);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 18px;
    color: var(--palette-dark-purple);
    transition: all 0.4s ease;
    box-shadow: 0 4px 12px rgba(48, 25, 52, 0.1);
}

.journey-step.current .step-badge {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    border-color: var(--palette-gold);
    color: white;
    animation: pulse-gold 2s infinite;
}

.journey-step.next .step-badge {
    background: linear-gradient(135deg, var(--palette-success) 0%, #0F766E 100%);
    border-color: var(--palette-success);
    color: white;
}

@keyframes pulse-gold {
    0%, 100% { transform: scale(1); box-shadow: 0 4px 12px rgba(218, 165, 32, 0.3); }
    50% { transform: scale(1.05); box-shadow: 0 6px 20px rgba(218, 165, 32, 0.5); }
}

.step-number {
    font-weight: 700;
    font-size: 20px;
}

.step-content {
    text-align: center;
}

.step-member {
    font-size: 16px;
    font-weight: 600;
    color: var(--palette-deep-purple);
    margin-bottom: 8px;
    line-height: 1.3;
}

.step-amount {
    font-size: 20px;
    font-weight: 700;
    color: var(--palette-success);
    margin-bottom: 6px;
}

.step-date {
    font-size: 14px;
    color: var(--palette-dark-purple);
    opacity: 0.8;
    margin-bottom: 12px;
}

.step-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.current-badge {
    background: linear-gradient(135deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    color: var(--palette-deep-purple);
    box-shadow: 0 2px 8px rgba(218, 165, 32, 0.3);
}

.next-badge {
    background: linear-gradient(135deg, var(--palette-success) 0%, #0F766E 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(42, 157, 143, 0.3);
}

/* Enhanced Table */
.table-container {
    background: var(--palette-white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.06);
    border: 1px solid var(--palette-border);
    margin-bottom: 30px;
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
    text-align: center;
}

.table td {
    padding: 16px 20px;
    border-color: rgba(77, 64, 82, 0.05);
    font-size: 14px;
    color: var(--palette-dark-purple);
    text-align: center;
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

.bg-primary {
    background: linear-gradient(135deg, var(--palette-deep-purple) 0%, var(--palette-dark-purple) 100%) !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(48, 25, 52, 0.3);
}

/* Enhanced Font Awesome Icons */
.fas, .far, .fab {
    font-weight: 900 !important;
    line-height: 1 !important;
    vertical-align: middle;
}

.payout-icon .fas {
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

/* Alert Styling */
.alert-info {
    background: rgba(42, 157, 143, 0.1);
    border: 1px solid rgba(42, 157, 143, 0.2);
    border-left: 4px solid var(--palette-success);
    color: var(--palette-deep-purple);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.alert-warning {
    background: rgba(218, 165, 32, 0.1);
    border: 1px solid rgba(218, 165, 32, 0.2);
    border-left: 4px solid var(--palette-gold);
    color: var(--palette-deep-purple);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

/* Progress Bar */
.progress {
    height: 8px;
    border-radius: 10px;
    background: rgba(218, 165, 32, 0.1);
    overflow: hidden;
    margin-top: 15px;
}

.progress-bar {
    border-radius: 10px;
    background: linear-gradient(90deg, var(--palette-gold) 0%, var(--palette-light-gold) 100%);
    box-shadow: 0 2px 8px rgba(218, 165, 32, 0.3);
}

/* Enhanced Mobile Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0 15px;
    }
    
         .row {
         margin-left: 0;
         margin-right: 0;
         gap: 0;
     }
     
     .row.mb-5 .col-xl-3,
     .row.mb-5 .col-md-6 {
         display: flex;
         flex-direction: column;
     }
    
         .col-xl-3, .col-md-6 {
         padding-left: 12px;
         padding-right: 12px;
         margin-bottom: 20px;
     }
    
    .page-header {
        padding: 30px 20px;
        margin-bottom: 30px;
        border-radius: 16px;
    }
    
    .page-title {
        font-size: 26px;
        text-align: center;
        justify-content: center;
    }
    
    .page-subtitle {
        font-size: 16px;
        text-align: center;
    }
    
         /* Enhanced Mobile Card Design */
     .row.mb-5 {
         margin-bottom: 2.5rem !important;
         row-gap: 0 !important;
     }
    
         .payout-card {
         padding: 24px 20px;
         margin-bottom: 28px;
         border-radius: 16px;
         box-shadow: 0 6px 24px rgba(48, 25, 52, 0.1);
         transform: none !important;
     }
    
    .payout-card:hover {
        transform: translateY(-2px) !important;
    }
    
    .payout-header {
        flex-direction: column;
        text-align: center;
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .payout-icon {
        width: 64px;
        height: 64px;
        font-size: 24px;
        margin: 0 auto;
    }
    
    .payout-title-group {
        text-align: center;
    }
    
    .payout-title-group h3 {
        font-size: 19px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .payout-value {
        font-size: 34px;
        text-align: center;
        margin: 20px 0 16px 0;
        font-weight: 700;
    }
    
    .payout-detail {
        text-align: center;
        font-size: 16px;
        line-height: 1.4;
    }
    
    .progress {
        height: 10px;
        margin-top: 20px;
    }
    
    /* Enhanced Journey Design for Mobile */
    .journey-container {
        padding: 24px 16px;
        border-radius: 16px;
    }
    
    .journey-header {
        flex-direction: column;
        gap: 24px;
        text-align: center;
        padding: 24px 16px;
    }
    
    .journey-progress {
        margin-right: 0;
        margin-bottom: 0;
    }
    
    .progress-track {
        height: 10px;
    }
    
    .journey-stats {
        justify-content: center;
        gap: 40px;
    }
    
    .stat-number {
        font-size: 28px;
    }
    
    .stat-label {
        font-size: 13px;
    }
    
    .journey-steps {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-top: 0;
    }
    
    .journey-step {
        padding: 24px 20px;
        border-radius: 16px;
        margin-bottom: 0;
        transform: none !important;
    }
    
    .journey-step:hover {
        transform: translateY(-2px) !important;
    }
    
    .step-badge {
        width: 70px;
        height: 70px;
        font-size: 20px;
        margin-bottom: 20px;
    }
    
    .step-member {
        font-size: 18px;
        margin-bottom: 12px;
        line-height: 1.3;
    }
    
    .step-amount {
        font-size: 26px;
        margin-bottom: 8px;
    }
    
    .step-date {
        font-size: 16px;
        margin-bottom: 16px;
    }
    
    .step-status {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .section-title {
        font-size: 22px;
        text-align: center;
        margin-bottom: 24px;
    }
    
    /* Alert Mobile */
    .alert-info, .alert-warning {
        padding: 20px 16px;
        border-radius: 16px;
        text-align: center;
        font-size: 15px;
        line-height: 1.5;
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: 16px;
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
         padding: 0 12px;
     }
     
     .col-xl-3, .col-md-6 {
         padding-left: 10px;
         padding-right: 10px;
         margin-bottom: 16px;
     }
    
    .page-header {
        padding: 24px 16px;
        border-radius: 16px;
        margin-bottom: 24px;
    }
    
    .page-title {
        font-size: 24px;
        line-height: 1.2;
    }
    
    .page-subtitle {
        font-size: 15px;
        margin-top: 8px;
    }
    
         /* Super Enhanced Mobile Cards */
     .payout-card {
         padding: 20px 16px;
         border-radius: 16px;
         margin-bottom: 24px;
         box-shadow: 0 4px 20px rgba(48, 25, 52, 0.08);
     }
    
    .payout-icon {
        width: 60px;
        height: 60px;
        font-size: 22px;
        margin: 0 auto;
    }
    
    .payout-title-group h3 {
        font-size: 17px;
        font-weight: 600;
    }
    
    .payout-value {
        font-size: 28px;
        margin: 16px 0 12px 0;
        line-height: 1.1;
    }
    
    .payout-detail {
        font-size: 14px;
        line-height: 1.4;
    }
    
    .progress {
        height: 8px;
        margin-top: 16px;
    }
    
    /* Journey Mobile Enhancement */
    .journey-container {
        padding: 20px 12px;
        border-radius: 16px;
    }
    
    .journey-header {
        padding: 20px 12px;
        gap: 20px;
    }
    
    .progress-track {
        height: 8px;
    }
    
    .journey-stats {
        gap: 32px;
    }
    
    .stat-number {
        font-size: 24px;
    }
    
    .stat-label {
        font-size: 12px;
    }
    
    .journey-steps {
        gap: 12px;
    }
    
    .journey-step {
        padding: 20px 16px;
        border-radius: 14px;
    }
    
    .step-badge {
        width: 64px;
        height: 64px;
        font-size: 18px;
        margin-bottom: 16px;
    }
    
    .step-member {
        font-size: 17px;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .step-amount {
        font-size: 24px;
        margin-bottom: 6px;
        font-weight: 700;
    }
    
    .step-date {
        font-size: 15px;
        margin-bottom: 14px;
    }
    
    .step-status {
        padding: 6px 14px;
        font-size: 12px;
        border-radius: 16px;
    }
    
    .section-title {
        font-size: 20px;
        margin-bottom: 20px;
    }
    
    /* Button Mobile */
    .btn-lg {
        padding: 14px 20px;
        font-size: 16px;
        border-radius: 12px;
        margin-bottom: 12px;
    }
    
    .alert-info, .alert-warning {
        padding: 16px 12px;
        font-size: 14px;
        border-radius: 14px;
    }
}

@media (max-width: 400px) {
     .container-fluid {
         padding: 0 10px;
     }
     
     .col-xl-3, .col-md-6 {
         padding-left: 8px;
         padding-right: 8px;
         margin-bottom: 14px;
     }
    
    .page-header {
        padding: 20px 14px;
        margin-bottom: 20px;
    }
    
    .page-title {
        font-size: 22px;
    }
    
    .page-subtitle {
        font-size: 14px;
    }
    
         .payout-card {
         padding: 18px 14px;
         margin-bottom: 20px;
     }
    
    .payout-icon {
        width: 56px;
        height: 56px;
        font-size: 20px;
    }
    
    .payout-value {
        font-size: 26px;
    }
    
    .journey-container {
        padding: 18px 10px;
    }
    
    .journey-header {
        padding: 18px 10px;
        gap: 18px;
    }
    
    .journey-stats {
        gap: 28px;
    }
    
    .stat-number {
        font-size: 22px;
    }
    
    .journey-step {
        padding: 18px 14px;
    }
    
    .step-badge {
        width: 60px;
        height: 60px;
        font-size: 17px;
    }
    
    .step-amount {
        font-size: 22px;
    }
    
    .step-member {
        font-size: 16px;
    }
    
    .btn-lg {
        padding: 12px 18px;
        font-size: 15px;
    }
}

/* Performance optimizations */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.payout-card, .journey-container, .table-container, .journey-step {
    will-change: transform;
}

/* Enhanced Info Grid Styles for Comprehensive Analytics */
.info-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(248, 250, 252, 0.6);
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.info-item:hover {
    background: rgba(248, 250, 252, 0.8);
    border-color: rgba(218, 165, 32, 0.2);
    transform: translateX(2px);
}

.info-label {
    font-size: 13px;
    color: var(--palette-dark-purple);
    font-weight: 500;
    opacity: 0.8;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--palette-deep-purple);
}

/* Enhanced card header styling for analytics section */
.card-header h5 {
    font-size: 16px;
    font-weight: 600;
    letter-spacing: -0.3px;
}

.card-header .badge {
    font-size: 0.7em;
    padding: 4px 8px;
    border-radius: 6px;
}

/* Responsive adjustments for enhanced info */
@media (max-width: 991px) {
    .info-grid {
        gap: 8px;
    }
    
    .info-item {
        padding: 6px 10px;
    }
    
    .info-label {
        font-size: 13px;
    }
    
    .info-value {
        font-size: 14px;
    }
}

@media (max-width: 767px) {
    .card-header h5 {
        font-size: 14px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .info-value {
        align-self: flex-end;
    }
}

/* Beautiful Mobile-First Accordion Styles */
.accordion-card {
    background: white;
    border-radius: 16px;
    border: 1px solid var(--palette-border);
    box-shadow: 0 4px 20px rgba(48, 25, 52, 0.08);
    overflow: hidden;
}

.accordion-card .accordion-header {
    background: linear-gradient(135deg, var(--palette-deep-purple) 0%, var(--palette-light-purple) 100%);
    color: white;
    padding: 20px 24px;
    border-bottom: none;
}

.accordion-card .accordion-header h5 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    letter-spacing: -0.3px;
}

.badge-method {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 0.7em;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.accordion {
    border: none;
}

.accordion-item {
    border: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    background: white;
}

.accordion-item:last-child {
    border-bottom: none;
}

        .accordion-button {
            background: white;
            border: none;
            padding: 20px 24px;
            font-size: 15px;
            font-weight: 500;
            color: #000000;
            box-shadow: none;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .accordion-button:not(.collapsed) {
            background: rgba(218, 165, 32, 0.05);
            color: #000000;
            box-shadow: none;
        }

.accordion-button:hover {
    background: rgba(218, 165, 32, 0.08);
}

.accordion-button:focus {
    box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.2);
}

        .accordion-title {
            flex: 1;
            text-align: left;
            color: #000000;
            font-weight: 600;
        }

.accordion-badge {
    background: rgba(218, 165, 32, 0.1);
    color: var(--palette-gold);
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 500;
    margin-left: auto;
}

.accordion-button:not(.collapsed) .accordion-badge {
    background: var(--palette-gold);
    color: white;
}

.accordion-button::after {
    background-image: none;
    content: '\f107';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: var(--palette-gold);
    font-size: 14px;
    margin-left: 12px;
    transition: transform 0.3s ease;
}

.accordion-button.collapsed::after {
    transform: rotate(-90deg);
}

.accordion-body {
    padding: 0 24px 20px 24px;
    background: white;
}

/* Mobile-First Responsive Design */
@media (max-width: 768px) {
    .accordion-card .accordion-header {
        padding: 16px 20px;
    }
    
    .accordion-card .accordion-header h5 {
        font-size: 17px;
    }
    
    .accordion-button {
        padding: 16px 20px;
        font-size: 15px;
    }
    
    .accordion-body {
        padding: 0 20px 16px 20px;
    }
    
    .accordion-title {
        font-size: 15px;
    }
    
    .accordion-badge {
        font-size: 12px;
        padding: 3px 6px;
    }
}

@media (max-width: 480px) {
    .accordion-card .accordion-header {
        padding: 14px 16px;
    }
    
    .accordion-card .accordion-header h5 {
        font-size: 16px;
    }
    
    .accordion-button {
        padding: 14px 16px;
        font-size: 14px;
        gap: 8px;
    }
    
    .accordion-body {
        padding: 0 16px 14px 16px;
    }
    
    .badge-method {
        font-size: 0.65em;
        padding: 2px 6px;
    }
}
</style>

</head>

<body>
    <!-- Include Member Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Page Content -->
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                                 <div class="page-header">
                     <h1 class="page-title">
                         <i class="fas fa-chart-line text-warning"></i>
                         <?php echo t('payout.page_title'); ?>
                     </h1>
                     <p class="page-subtitle"><?php echo t('payout.page_subtitle'); ?></p>
                 </div>
            </div>
        </div>

        <!-- Payout Summary Cards -->
        <div class="row mb-5">
            <!-- Your Position -->
            <div class="col-xl-3 col-md-6">
                <div class="payout-card">
                    <div class="payout-header">
                        <div class="payout-icon primary">
                            <i class="fas fa-trophy"></i>
                        </div>
                                                 <div class="payout-title-group">
                             <h3><?php echo t('payout.your_position'); ?></h3>
                         </div>
                     </div>
                     <div class="payout-value">#<?php echo $payout_position; ?></div>
                     <div class="payout-detail">
                         <i class="fas fa-users text-info me-1"></i>
                         <?php echo t('payout.out_of'); ?> <?php echo $total_equb_members; ?> <?php echo t('payout.active_members'); ?>
                     </div>
                </div>
            </div>

            <!-- Expected Payout -->
            <div class="col-xl-3 col-md-6">
                <div class="payout-card">
                    <div class="payout-header">
                        <div class="payout-icon success">
                            <i class="fas fa-coins"></i>
                        </div>
                                                 <div class="payout-title-group">
                             <h3><?php echo t('payout.expected_payout'); ?></h3>
                         </div>
                    </div>
                    <div class="payout-value">£<?php echo number_format($real_net_payout, 2); ?></div>
                    <div class="payout-detail">
                        <i class="fas fa-money-bill-wave text-success me-1"></i>
                        <strong><?php echo t('payout_info.net_amount'); ?></strong> (<?php echo t('payout_info.what_you_receive'); ?>)
                    </div>
                    <div class="payout-detail mt-2">
                        <i class="fas fa-coins text-secondary me-1"></i>
                        <small><?php echo t('payout_info.based_on_contribution'); ?></small>
                    </div>
                </div>
            </div>

            <!-- Payout Date -->
            <div class="col-xl-3 col-md-6">
                <div class="payout-card">
                    <div class="payout-header">
                        <div class="payout-icon warning">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                                                 <div class="payout-title-group">
                             <h3><?php echo t('payout.payout_date'); ?></h3>
                         </div>
                     </div>
                         <?php 
                     // Dynamic payout date calculation based on EQUB term and member position
                     $equb_start = new DateTime($member['start_date']);
                     $member_payout_month = $payout_position; // Position determines payout month
                     $payout_target_date = clone $equb_start;
                     $payout_target_date->modify("+".($member_payout_month - 1)." months");
                     // Set the day dynamically from equb_settings payout_day (no hardcoding)
                     $payout_day = (int)($member['payout_day'] ?? 1);
                     $payout_target_date->setDate(
                         (int)$payout_target_date->format('Y'),
                         (int)$payout_target_date->format('n'),
                         max(1, $payout_day)
                     );
                     
                     // Check if member has already received payout
                     $has_received_payout = $member['total_payouts_received'] > 0;
                     $actual_payout_date = $member['last_payout_received_date'];
                     
                     if ($has_received_payout && $actual_payout_date) {
                         // Member has received payout - show actual date
                         $display_date = date('M d, Y', strtotime($actual_payout_date));
                         $days_info = '<i class="fas fa-check-circle text-success me-1"></i>Payout Completed';
                         $value_class = 'text-success';
                         } else {
                         // Member hasn't received payout yet - show scheduled date
                         $display_date = $payout_target_date->format('M d, Y');
                         $current_date = new DateTime();
                         $days_until = $current_date->diff($payout_target_date)->days;
                         $is_future = $payout_target_date > $current_date;
                         
                         if ($is_future) {
                             $days_info = '<i class="fas fa-clock text-info me-1"></i>' . $days_until . ' days remaining';
                             $value_class = '';
                         } else {
                             $days_info = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>Due ' . $days_until . ' days ago';
                             $value_class = 'text-warning';
                         }
                     }
                     ?>
                     <div class="payout-value <?php echo $value_class; ?>"><?php echo $display_date; ?></div>
                     <div class="payout-detail">
                         <?php echo $days_info; ?>
                     </div>
                </div>
            </div>

            <!-- Progress Status -->
            <div class="col-xl-3 col-md-6">
                <div class="payout-card">
                    <div class="payout-header">
                        <div class="payout-icon info">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                                                 <div class="payout-title-group">
                             <h3><?php echo t('payout.queue_progress'); ?></h3>
                         </div>
                     </div>
                     <?php
                     // Dynamic queue progress calculation
                     $current_equb_month = $member['months_in_equb'];
                     $members_who_received = 0;
                     $members_pending = 0;
                     
                     // Get actual queue status from database
                     try {
                         $stmt = $pdo->prepare("
                             SELECT 
                                 COUNT(CASE WHEN m.payout_position <= ? AND po.status = 'completed' THEN 1 END) as completed_before_me,
                                 COUNT(CASE WHEN m.payout_position <= ? THEN 1 END) as total_before_me_inclusive,
                                 COUNT(CASE WHEN po.status = 'completed' THEN 1 END) as total_completed
                             FROM members m 
                             LEFT JOIN payouts po ON m.id = po.member_id 
                             WHERE m.equb_settings_id = ? AND m.is_active = 1
                         ");
                         $stmt->execute([$payout_position, $payout_position, $member['equb_settings_id']]);
                         $queue_stats = $stmt->fetch(PDO::FETCH_ASSOC);
                         
                         $members_who_received = (int)$queue_stats['completed_before_me'];
                         $my_position_reached = ($current_equb_month >= $payout_position);
                         $total_completed = (int)$queue_stats['total_completed'];
                         
                         // Calculate progress based on EQUB timeline vs member position
                         if ($my_position_reached) {
                             $progress_percentage = min(100, (($members_who_received + 1) / $total_equb_members) * 100);
                             $status_text = t('payout_info.your_turn_arrived');
                             $icon_class = "fas fa-check-circle text-success";
                         } else {
                             $progress_percentage = ($current_equb_month / $payout_position) * 100;
                             $months_to_wait = $payout_position - $current_equb_month;
                             $status_text = $months_to_wait . " " . t('payout_info.months_until_turn');
                             $icon_class = "fas fa-clock text-info";
                         }
                         
                     } catch (Exception $e) {
                         // Fallback calculation
                         $progress_percentage = ($current_equb_month / $member['duration_months']) * 100;
                         $status_text = "Position " . $payout_position . " of " . $total_equb_members;
                         $icon_class = "fas fa-list-ol text-warning";
                     }
                     ?>
                     <div class="payout-value"><?php echo round($progress_percentage, 1); ?>%</div>
                     <div class="payout-detail">
                         <i class="<?php echo $icon_class; ?> me-1"></i>
                         <?php echo $status_text; ?>
                     </div>
                     <div class="payout-detail mt-1">
                         <i class="fas fa-users text-secondary me-1"></i>
                         <small>Position <?php echo $payout_position; ?> • Month <?php echo $current_equb_month; ?>/<?php echo $member['duration_months']; ?></small>
                     </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beautiful Mobile-First Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion-card">
                    <div class="accordion-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            EQUB Analytics & Member Information
                            <span class="badge badge-method d-none d-sm-inline">
                                <?php echo ucfirst($calculation_method); ?>
                            </span>
                        </h5>
                    </div>
                    
                    <div class="accordion" id="memberAccordion">
                        <!-- Financial Summary -->
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#financialSummary" aria-expanded="true" aria-controls="financialSummary">
                                    <i class="fas fa-coins me-2 text-success"></i>
                                    <span class="accordion-title"><?php echo t('payout_info.financial_summary'); ?></span>
                                    <span class="accordion-badge">3 <?php echo t('payout_info.items'); ?></span>
                                </button>
                            </div>
                            <div id="financialSummary" class="accordion-collapse collapse show" data-bs-parent="#memberAccordion">
                                <div class="accordion-body">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.total_monthly_pool'); ?></span>
                                            <span class="info-value text-success">£<?php echo number_format($total_monthly_pool, 2); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.monthly_contribution'); ?></span>
                                            <span class="info-value">£<?php echo number_format($monthly_contribution, 2); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.total_contributed'); ?></span>
                                            <span class="info-value text-success">£<?php echo number_format($member['total_contributed'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- EQUB Progress -->
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#equbProgress" aria-expanded="false" aria-controls="equbProgress">
                                    <i class="fas fa-calendar me-2 text-info"></i>
                                    <span class="accordion-title"><?php echo t('payout_info.equb_progress_title'); ?></span>
                                    <span class="accordion-badge"><?php echo $member['months_in_equb']; ?>/<?php echo $member['duration_months']; ?> <?php echo t('payout_info.months'); ?></span>
                                </button>
                            </div>
                            <div id="equbProgress" class="accordion-collapse collapse" data-bs-parent="#memberAccordion">
                                <div class="accordion-body">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.start_date'); ?></span>
                                            <span class="info-value"><?php echo $member['start_date'] ? date('M d, Y', strtotime($member['start_date'])) : t('payout_info.not_set'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.duration'); ?></span>
                                            <span class="info-value"><?php echo $member['duration_months']; ?> <?php echo t('payout_info.months'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.months_completed'); ?></span>
                                            <span class="info-value text-primary"><?php echo $member['months_in_equb']; ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.remaining_months'); ?></span>
                                            <span class="info-value text-warning"><?php echo $member['remaining_months_in_equb']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Member Performance -->
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#memberPerformance" aria-expanded="false" aria-controls="memberPerformance">
                                    <i class="fas fa-trophy me-2 text-warning"></i>
                                    <span class="accordion-title"><?php echo t('payout_info.member_performance_title'); ?></span>
                                    <span class="accordion-badge"><?php echo t('payout_info.position_hash'); ?><?php echo $payout_position; ?></span>
                                </button>
                            </div>
                            <div id="memberPerformance" class="accordion-collapse collapse" data-bs-parent="#memberAccordion">
                                <div class="accordion-body">
                                    <div class="info-grid">
                                        <?php if ($member['membership_type'] === 'joint'): ?>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.joint_group'); ?></span>
                                            <span class="info-value"><?php echo htmlspecialchars($member['joint_group_name']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.payout_position'); ?></span>
                                            <span class="info-value text-primary fw-bold">#<?php echo $payout_position; ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.total_payments_made'); ?></span>
                                            <span class="info-value"><?php echo $member['total_payments']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- EQUB Settings & Rules -->
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#equbSettings" aria-expanded="false" aria-controls="equbSettings">
                                    <i class="fas fa-sliders-h me-2 text-secondary"></i>
                                    <span class="accordion-title"><?php echo t('payout_info.equb_settings_rules'); ?></span>
                                    <span class="accordion-badge"><?php echo t('payout_info.rules'); ?></span>
                                </button>
                            </div>
                            <div id="equbSettings" class="accordion-collapse collapse" data-bs-parent="#memberAccordion">
                                <div class="accordion-body">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.admin_fee'); ?></span>
                                            <span class="info-value text-warning">£<?php echo number_format($member['admin_fee'], 2); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.late_fee'); ?></span>
                                            <span class="info-value text-danger">£<?php echo number_format($member['late_fee'], 2); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.grace_period'); ?></span>
                                            <span class="info-value"><?php echo $member['grace_period_days']; ?> <?php echo t('payout_info.days'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label"><?php echo t('payout_info.regular_payment_tier'); ?></span>
                                            <span class="info-value">£<?php echo number_format($member['regular_payment_tier'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Alert -->
        <?php if ($days_until_payout <= 30 && $days_until_payout > 0): ?>
                 <div class="alert alert-warning">
             <i class="fas fa-bell me-2"></i>
             <strong><?php echo t('payout.payout_approaching'); ?></strong> <?php echo t('payout.will_receive_payout'); ?> <strong>£<?php echo number_format($expected_payout, 2); ?></strong> <?php echo t('payout.on_date'); ?> <strong><?php echo date('M d, Y', strtotime($payout_date)); ?></strong>.
         </div>
         <?php elseif ($days_until_payout <= 0): ?>
         <div class="alert alert-info">
             <i class="fas fa-check-circle me-2"></i>
             <strong><?php echo t('payout.payout_ready'); ?></strong> <?php echo t('payout.contact_admin'); ?> <strong>£<?php echo number_format($expected_payout, 2); ?></strong>.
         </div>
        <?php endif; ?>

        <!-- Upcoming Payout Timeline -->
        <div class="row mb-5">
            <div class="col-12">
                                 <h2 class="section-title">
                     <i class="fas fa-route text-primary"></i>
                     <?php echo t('payout_info.upcoming_payout_journey'); ?>
                 </h2>
                
                <div class="journey-container">
                    <!-- Progress Header -->
                    <div class="journey-header">
                        <div class="journey-progress">
                            <div class="progress-track">
                                <?php 
                                $completed_members = array_filter($payout_queue, function($member) {
                                    return $member['payout_status'] === 'completed';
                                });
                                $total_members = count($payout_queue);
                                $completed_count = count($completed_members);
                                $current_member_position = 0;
                                
                                // Find current member's position in queue
                                foreach ($payout_queue as $index => $member) {
                                    if ($member['is_current_user']) {
                                        $current_member_position = $index + 1;
                                        break;
                                    }
                                }
                                
                                $progress_percentage = $total_members > 0 ? ($completed_count / $total_members) * 100 : 0;
                                ?>
                                <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                            </div>
                        </div>
                        <div class="journey-stats">
                                                         <div class="stat-item">
                                 <span class="stat-number"><?php echo $current_member_position; ?></span>
                                 <span class="stat-label">Your Position</span>
                             </div>
                             <div class="stat-item">
                                 <span class="stat-number"><?php echo $completed_count; ?>/<?php echo $total_members; ?></span>
                                 <span class="stat-label">Completed</span>
                             </div>
                        </div>
                    </div>

                    <!-- Journey Steps -->
                    <div class="journey-steps">
                        <?php 
                        $step_count = 0;
                        foreach ($payout_queue as $index => $queue_member): 
                            $step_count++;
                            $is_current = $queue_member['is_current_user'];
                            $is_next = ($step_count == 1 && !$is_current && $queue_member['payout_status'] !== 'completed');
                            
                            // Use actual payout_month from database (dynamic, not hardcoded)
                            if (!empty($queue_member['payout_month']) && $queue_member['payout_month'] !== '0000-00-00') {
                                $member_payout_date = new DateTime($queue_member['payout_month']);
                            } else {
                                // Fallback: if payout_month is not set, calculate based on equb settings
                            $start_date = new DateTime($member['start_date']);
                            $member_payout_date = clone $start_date;
                            $member_payout_date->add(new DateInterval('P' . ($queue_member['payout_position'] - 1) . 'M'));
                            $member_payout_date->setDate(
                                $member_payout_date->format('Y'), 
                                $member_payout_date->format('n'), 
                                $member['payout_day']
                            );
                            }
                        ?>
                        <div class="journey-step <?php echo $queue_member['payout_status'] === 'completed' ? 'completed' : ($is_current ? 'current' : ($is_next ? 'next' : '')); ?>">
                            <div class="step-badge">
                                <?php if ($queue_member['payout_status'] === 'completed'): ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                <?php elseif ($is_current): ?>
                                    <i class="fas fa-user-crown"></i>
                                <?php elseif ($is_next): ?>
                                    <i class="fas fa-hourglass-half"></i>
                                <?php else: ?>
                                    <span class="step-number"><?php echo $queue_member['payout_position']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="step-content">
                                <div class="step-member">
                                    <?php 
                                                                        if (isset($queue_member['is_joint_position']) && $queue_member['is_joint_position']) {
                                        // Joint position display
                                        echo '<strong><i class="fas fa-users text-warning me-1"></i>' . htmlspecialchars($queue_member['display_name']) . '</strong>';
                                        echo '<br><small class="text-muted">' . implode(', ', $queue_member['member_names']) . '</small>';
                                    if ($is_current) {
                                            echo '<br><span class="badge bg-primary">You\'re in this group</span>';
                                        }
                                    } else {
                                        // Individual position display
                                        if ($is_current) {
                                            echo '<strong>👤 ' . htmlspecialchars($queue_member['display_name']) . ' (You)</strong>';
                                        } else {
                                            echo htmlspecialchars($queue_member['display_name']);
                                            if ($queue_member['is_anonymous']) {
                                                echo ' <i class="fas fa-user-secret text-muted ms-1" title="' . t('payout_info.anonymous') . '"></i>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="step-amount">
                                    £<?php echo number_format($queue_member['display_payout'], 2); ?>
                                </div>
                                <div class="step-date">
                                    <?php echo $member_payout_date->format('M j, Y'); ?>
                                </div>
                                 <?php if ($queue_member['payout_status'] === 'completed'): ?>
                                     <div class="step-status completed-badge">
                                         <i class="fas fa-check-circle me-1"></i>Completed
                                         <?php if ($queue_member['received_date']): ?>
                                         <br><small><?php echo date('M j, Y', strtotime($queue_member['received_date'])); ?></small>
                                         <?php endif; ?>
                                     </div>
                                 <?php elseif ($is_current): ?>
                                     <div class="step-status current-badge">
                                         <i class="fas fa-star me-1"></i>Your Turn
                                     </div>
                                 <?php elseif ($is_next): ?>
                                     <div class="step-status next-badge">
                                         <i class="fas fa-clock me-1"></i>Next Up
                                     </div>
                                 <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout History -->
        <?php if (!empty($payout_history)): ?>
        <div class="row mb-5">
            <div class="col-12">
                                 <h2 class="section-title">
                     <i class="fas fa-history text-primary"></i>
                     <?php echo t('payout.payout_history'); ?>
                 </h2>
                
                <div class="table-container">
                    <table class="table table-hover mb-0">
                                                 <thead>
                             <tr>
                                 <th><?php echo t('payout.payout_id'); ?></th>
                                 <th><?php echo t('payout.amount'); ?></th>
                                 <th><?php echo t('payout.date_received'); ?></th>
                                 <th><?php echo t('payout.month'); ?></th>
                                 <th><?php echo t('payout.status'); ?></th>
                             </tr>
                         </thead>
                        <tbody>
                            <?php foreach ($payout_history as $payout): ?>
                            <tr>
                                <td>
                                    <code class="small"><?php echo htmlspecialchars($payout['id']); ?></code>
                                </td>
                                <td class="fw-semibold text-success">
                                    £<?php echo number_format($payout['amount'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($payout['formatted_date']); ?></td>
                                <td><?php echo htmlspecialchars($payout['payout_month_name']); ?></td>
                                                                 <td>
                                     <span class="badge bg-success">
                                         <i class="fas fa-check me-1"></i>
                                         <?php echo t('payout.completed'); ?>
                                     </span>
                                 </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>



        <!-- Quick Actions -->
        <div class="row mb-4">
                         <div class="col-md-6">
                 <a href="contributions.php" class="btn btn-outline-primary btn-lg w-100">
                     <i class="fas fa-credit-card me-2"></i>
                     <?php echo t('payout.view_payments'); ?>
                 </a>
             </div>
             <div class="col-md-6">
                 <a href="dashboard.php" class="btn btn-warning btn-lg w-100">
                     <i class="fas fa-chart-pie me-2"></i>
                     <?php echo t('payout.back_dashboard'); ?>
                 </a>
             </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
</body>
</html>