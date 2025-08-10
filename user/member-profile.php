<?php
/**
 * HabeshaEqub - Member Profile Details (Standalone Page)
 * Professional member profile page with top-tier design
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
require_once '../includes/enhanced_equb_calculator_final.php';

// Ensure translation function exists
if (!function_exists('t')) {
    function t($key) {
        return $key; // Fallback to key if translation function doesn't exist
    }
}

// Secure authentication check
require_once 'includes/auth_guard.php';
$current_user_id = get_current_user_id();

// Get member ID from request
$member_id = (int)($_GET['id'] ?? 0);

if (!$member_id) {
    header('Location: members.php');
    exit;
}

// Security check: Users can only view their own profile or public profiles
// For now, let's allow viewing public profiles but restrict private data
$viewing_own_profile = ($member_id === $current_user_id);

// Get detailed member information with enhanced data
try {
    // Initialize enhanced calculator
    if (!class_exists('EnhancedEqubCalculator')) {
        throw new Exception('EnhancedEqubCalculator class not found');
    }
    $enhanced_calculator = new EnhancedEqubCalculator($pdo);
    
    $stmt = $pdo->prepare("
        SELECT m.*, 
               es.equb_name, es.start_date, es.end_date, es.payout_day, es.duration_months, 
               es.max_members, es.current_members, es.admin_fee, es.late_fee, es.grace_period_days,
               es.regular_payment_tier, es.currency, es.status as equb_status,
               COALESCE(SUM(CASE WHEN p.status IN ('paid', 'completed') THEN p.amount ELSE 0 END), 0) as total_contributed,
               COALESCE(COUNT(CASE WHEN p.status IN ('paid', 'completed') THEN 1 END), 0) as payments_made,
               COALESCE(
                   (SELECT po.gross_payout FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1), 
                   0
               ) as last_gross_payout,
               COALESCE(
                   (SELECT po.total_amount FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1), 
                   0
               ) as last_total_amount,
               COALESCE(
                   (SELECT po.net_amount FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1), 
                   0
               ) as last_net_amount,
               COALESCE(
                   (SELECT po.actual_payout_date FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed' ORDER BY po.actual_payout_date DESC LIMIT 1), 
                   NULL
               ) as last_payout_date,
               (SELECT COUNT(*) FROM payouts po WHERE po.member_id = m.id AND po.status = 'completed') as total_payouts_received,
               (SELECT COUNT(*) FROM members WHERE equb_settings_id = m.equb_settings_id AND is_active = 1) as total_equb_members,
               -- Get payment history count
               (SELECT COUNT(*) FROM payments p WHERE p.member_id = m.id) as total_payment_records,
               -- Calculate EQUB progress
               CASE 
                   WHEN es.start_date IS NOT NULL THEN 
                       GREATEST(0, FLOOR(DATEDIFF(CURDATE(), es.start_date) / 30.44))
                   ELSE 0 
               END as months_in_equb,
               CASE 
                   WHEN es.duration_months IS NOT NULL AND es.start_date IS NOT NULL THEN 
                       GREATEST(0, es.duration_months - FLOOR(DATEDIFF(CURDATE(), es.start_date) / 30.44))
                   ELSE es.duration_months 
               END as remaining_months_in_equb
        FROM members m 
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        LEFT JOIN payments p ON m.id = p.member_id
        WHERE m.id = ? AND m.is_active = 1
        GROUP BY m.id
    ");
    
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        header('Location: members.php');
        exit;
    }
    
    // Privacy logic: Show real name only if go_public=1 OR if it's the current user
    if ($member['go_public'] == 1 || $viewing_own_profile) {
        $display_name = trim($member['first_name'] . ' ' . $member['last_name']);
        $is_anonymous = false;
    } else {
        $display_name = t('payout_info.anonymous');
        $is_anonymous = true;
    }
    
    // Enhanced calculations using calculator
    try {
        if (isset($enhanced_calculator) && is_object($enhanced_calculator)) {
            $calc_result = $enhanced_calculator->calculateMemberFriendlyPayout($member_id);
            if ($calc_result['success']) {
                $gross_payout = $calc_result['calculation']['gross_payout'];
                $display_payout = $calc_result['calculation']['display_payout'];
                $net_payout = $calc_result['calculation']['real_net_payout'];
                $calculation_method = $calc_result['calculation']['calculation_method'];
                $position_coefficient = $calc_result['calculation']['position_coefficient'];
                $total_monthly_pool = $calc_result['calculation']['total_monthly_pool'];
            } else {
                // Fallback calculation
                $gross_payout = ($member['position_coefficient'] ?? 1) * ($member['regular_payment_tier'] ?? $member['monthly_payment']) * $member['total_equb_members'];
                $display_payout = $gross_payout - $member['admin_fee'];
                $net_payout = $gross_payout - $member['admin_fee'] - $member['monthly_payment'];
                $calculation_method = 'fallback';
                $position_coefficient = $member['position_coefficient'] ?? 1;
                $total_monthly_pool = $member['regular_payment_tier'] * $member['total_equb_members'];
            }
        } else {
            // Fallback calculation if calculator not available
            $gross_payout = ($member['position_coefficient'] ?? 1) * ($member['regular_payment_tier'] ?? $member['monthly_payment']) * $member['total_equb_members'];
            $display_payout = $gross_payout - $member['admin_fee'];
            $net_payout = $gross_payout - $member['admin_fee'] - $member['monthly_payment'];
            $calculation_method = 'basic_fallback';
            $position_coefficient = $member['position_coefficient'] ?? 1;
            $total_monthly_pool = $member['regular_payment_tier'] * $member['total_equb_members'];
        }
    } catch (Exception $e) {
        // Final fallback calculation
        $gross_payout = ($member['position_coefficient'] ?? 1) * ($member['regular_payment_tier'] ?? $member['monthly_payment']) * $member['total_equb_members'];
        $display_payout = $gross_payout - $member['admin_fee'];
        $net_payout = $gross_payout - $member['admin_fee'] - $member['monthly_payment'];
        $calculation_method = 'error_fallback';
        $position_coefficient = $member['position_coefficient'] ?? 1;
        $total_monthly_pool = $member['regular_payment_tier'] * $member['total_equb_members'];
        error_log("Calculator error for member {$member_id}: " . $e->getMessage());
    }
    
    // Get real payout information using the sync service
    $payout_service = getPayoutSyncService();
    $payout_info = $payout_service->getMemberPayoutStatus($member_id);
    
    // Legacy variable for compatibility (but we'll use display_payout instead)
    $expected_payout = $display_payout;

    // Get member's recent payment history (last 6 payments), showing the month being paid FOR
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CASE 
                   WHEN p.payment_month IS NOT NULL AND p.payment_month <> '0000-00-00' THEN DATE_FORMAT(p.payment_month, '%M %Y')
                   WHEN p.payment_date IS NOT NULL AND p.payment_date <> '0000-00-00' THEN DATE_FORMAT(p.payment_date, '%M %Y')
                   ELSE DATE_FORMAT(p.created_at, '%M %Y')
               END AS payment_month_name,
               CASE 
                   WHEN p.payment_date IS NOT NULL AND p.payment_date <> '0000-00-00' THEN DATE_FORMAT(p.payment_date, '%d %M %Y')
                   ELSE DATE_FORMAT(p.created_at, '%d %M %Y')
               END AS formatted_date
        FROM payments p 
        WHERE p.member_id = ? AND p.status IN ('paid', 'completed')
        ORDER BY 
            CASE 
                WHEN p.payment_month IS NOT NULL AND p.payment_month <> '0000-00-00' THEN p.payment_month 
                ELSE p.payment_date 
            END DESC,
            p.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$member_id]);
    $recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get member's payout history
    $stmt = $pdo->prepare("
        SELECT po.*, 
               DATE_FORMAT(po.actual_payout_date, '%d %M %Y') as formatted_date,
               DATE_FORMAT(po.actual_payout_date, '%M %Y') as payout_month_name
        FROM payouts po 
        WHERE po.member_id = ? AND po.status = 'completed'
        ORDER BY po.actual_payout_date DESC
    ");
    $stmt->execute([$member_id]);
    $payout_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Location: members.php');
    exit;
}

// Calculate member data with privacy-aware display
$profile_member_name = $display_name;

// Generate initials - use first letters of display name or AN for Anonymous
if ($is_anonymous) {
    $initials = 'AN'; // Anonymous initials
} else {
    $name_parts = explode(' ', $display_name);
    if (count($name_parts) >= 2) {
        $initials = substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1);
    } else {
        $initials = substr($display_name, 0, 2);
    }
}
$member_since = date('M Y', strtotime($member['created_at']));
$payout_status = $member['total_payouts_received'] > 0 ? 'received' : ($member['payout_position'] == 1 ? 'current' : 'upcoming');

// Use real payout date from sync service
if (isset($payout_info['calculated_payout_date'])) {
    $expected_payout_formatted = date('M j, Y', strtotime($payout_info['calculated_payout_date']));
} else {
    $expected_payout_formatted = t('members_directory.not_available');
}

// Calculate payment progress based on expected payments for the member's equb duration
$expected_payments_total = $member['duration_months'] ?: 8; // Default to 8 months if not set
$payment_progress = $expected_payments_total > 0 ? ($member['payments_made'] / $expected_payments_total) * 100 : 0;

// Strong cache buster for assets
$cache_buster = time() . '_' . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile_member_name, ENT_QUOTES); ?> - <?php echo t('members_directory.member_profile'); ?> - HabeshaEqub</title>
    
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
/* === CLEAN PROFESSIONAL MEMBER PROFILE === */

/* Professional 6-Color Palette - Clean & Consistent */
:root {
    --color-cream: #F1ECE2;
    --color-dark-purple: #4D4052;
    --color-deep-purple: #301934;
    --color-gold: #DAA520;
    --color-light-gold: #CDAF56;
    --color-brown: #5D4225;
    --color-white: #FFFFFF;
    --color-light-bg: #F1ECE2;
    --color-border: rgba(77, 64, 82, 0.15);
}

/* Typography - Consistent Font Sizes */
.page-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin: 0 0 8px 0;
}

.page-subtitle {
    font-size: 14px;
    color: var(--color-dark-purple);
    margin: 0;
}

.back-button {
    background: var(--color-gold);
    color: var(--color-deep-purple);
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 16px;
}

.back-button:hover {
    background: var(--color-light-gold);
    color: var(--color-deep-purple);
}

/* Member Profile Header - Clean & Simple */
.member-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding: 20px;
    background: var(--color-white);
    border-radius: 12px;
    border: 1px solid var(--color-border);
    position: relative;
    overflow: hidden;
}

.member-profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
}

.profile-avatar {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: var(--color-deep-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-cream);
    font-size: 24px;
    font-weight: 600;
    flex-shrink: 0;
}

.profile-avatar.anonymous {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.profile-info h2 {
    font-size: 24px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin: 0 0 8px 0;
}

.profile-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--color-dark-purple);
    background: var(--color-cream);
    padding: 4px 8px;
    border-radius: 16px;
    font-weight: 500;
}

.profile-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    font-size: 16px;
    color: var(--color-gold);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.stat-card {
    background: var(--color-white);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    transition: all 0.2s ease;
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
    background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(77, 64, 82, 0.15);
    border-color: var(--color-gold);
}

.stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    margin: 0 auto 12px;
}

.stat-icon.primary { 
    background: var(--color-cream);
    color: var(--color-deep-purple);
}

.stat-icon.warning { 
    background: rgba(218, 165, 32, 0.15);
    color: var(--color-gold);
}

.stat-icon.info { 
    background: rgba(77, 64, 82, 0.1);
    color: var(--color-dark-purple);
}

.stat-value {
    font-size: 20px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin: 0 0 4px 0;
}

.stat-label {
    font-size: 12px;
    color: var(--color-dark-purple);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payment-history {
    background: var(--color-white);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.payment-item {
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s ease;
}

.payment-item:hover {
    background: var(--color-cream);
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-info h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin: 0 0 4px 0;
}

.payment-date {
    font-size: 12px;
    color: var(--color-dark-purple);
    font-weight: 500;
}

.payment-amount {
    font-size: 18px;
    font-weight: 600;
    color: var(--color-gold);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-received {
    background: var(--color-cream);
    color: var(--color-gold);
}

.status-current {
    background: rgba(218, 165, 32, 0.15);
    color: var(--color-gold);
}

.status-upcoming {
    background: rgba(77, 64, 82, 0.1);
    color: var(--color-dark-purple);
}

.progress-bar-custom {
    background: var(--color-cream);
    border-radius: 8px;
    height: 8px;
    overflow: hidden;
    margin: 12px 0;
}

.progress-fill {
    height: 100%;
    background: var(--color-gold);
    border-radius: 8px;
    transition: width 1s ease;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: var(--color-dark-purple);
}

.no-data i {
    color: var(--color-gold);
    margin-bottom: 12px;
}

.no-data h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin-bottom: 8px;
}

.no-data p {
    font-size: 14px;
    color: var(--color-dark-purple);
    margin: 0;
}

/* Mobile Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0 16px;
    }
    
    .member-profile-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        padding: 16px;
    }
    
    .profile-avatar {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .profile-info h2 {
        font-size: 20px;
    }
    
    .profile-meta {
        justify-content: center;
        gap: 8px;
    }
    
    .meta-item {
        font-size: 11px;
        padding: 3px 6px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat-card {
        padding: 12px;
    }
    
    .stat-icon {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .stat-value {
        font-size: 18px;
    }
    
    .payment-item {
        flex-direction: column;
        text-align: center;
        gap: 8px;
        padding: 12px 16px;
    }
    
    .section-title {
        font-size: 16px;
        justify-content: center;
    }
    
    .back-button {
        width: 100%;
        justify-content: center;
        padding: 12px;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0 12px;
    }
    
    .member-profile-header,
    .stat-card {
        padding: 12px;
    }
}

/* Animations - Subtle */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-card, .payment-item, .member-profile-header {
    animation: fadeIn 0.3s ease-out;
}
</style>

</head>

<body>
    <!-- Include Member Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Page Content -->
    <div class="container-fluid">
        <!-- Back Button -->
        <a href="members.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <?php echo t('members_directory.back_to_members'); ?>
        </a>



        <!-- Member Profile Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="member-profile-header">
                    <div class="profile-avatar <?php echo $is_anonymous ? 'anonymous' : ''; ?>">
                        <?php echo strtoupper($initials); ?>
                    </div>
                    <div class="profile-info">
                        <h2>
                            <?php echo htmlspecialchars($profile_member_name, ENT_QUOTES); ?>
                            <?php if ($is_anonymous): ?>
                                <i class="fas fa-user-secret text-muted ms-2" title="<?php echo t('payout_info.anonymous'); ?>"></i>
                            <?php endif; ?>
                        </h2>
                        <div class="profile-meta">
                            <div class="meta-item">
                                <i class="fas fa-trophy"></i>
                                <?php echo t('members_directory.position_number'); ?><?php echo $member['payout_position']; ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <?php echo t('members_directory.member_since'); ?> <?php echo $member_since; ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-check-circle"></i>
                                <span class="status-badge status-<?php echo $payout_status; ?>">
                                    <?php 
                                    echo $payout_status === 'received' ? t('members_directory.payout_received') : 
                                         ($payout_status === 'current' ? t('members_directory.current_turn') : t('members_directory.upcoming')); 
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fas fa-chart-line text-primary"></i>
                        <?php echo t('members_directory.financial_overview'); ?>
                    </h3>
                    
                    <?php if (!empty($member['equb_name'])): ?>
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-tag me-2"></i>
                            <strong><?php echo t('members_directory.equb_label'); ?>:</strong> <?php echo htmlspecialchars($member['equb_name']); ?>
                            <span class="ms-3">
                                <i class="fas fa-users me-1"></i>
                                <?php echo t('members_directory.position_of'); ?> <?php echo $member['payout_position']; ?> <?php echo t('members_directory.of'); ?> <?php echo $member['total_equb_members']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-value">£<?php echo number_format($member['monthly_payment'], 0); ?></div>
                            <div class="stat-label"><?php echo t('members_directory.monthly_payment_full'); ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <div class="stat-value">£<?php echo number_format($member['total_contributed'], 0); ?></div>
                            <div class="stat-label"><?php echo t('members_directory.total_contributed'); ?></div>
                            <div class="stat-detail">
                                <?php echo $member['payments_made']; ?> <?php echo t('members_directory.payments_made'); ?>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="stat-value">£<?php echo number_format($display_payout, 0); ?></div>
                            <div class="stat-label"><?php echo t('members_directory.expected_payout'); ?></div>
                            <div class="stat-detail">
                                <?php echo t('payout_info.based_on_contribution'); ?>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <?php if ($member['total_payouts_received'] > 0 && !empty($member['last_payout_date'])): ?>
                                <div class="stat-icon warning">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-value">
                                    <?php echo date('M j, Y', strtotime($member['last_payout_date'])); ?>
                                </div>
                                <div class="stat-label"><?php echo t('members_directory.paid_out'); ?></div>
                                <div class="stat-detail">
                                    <i class="fas fa-money-bill-wave text-success me-1"></i>
                                    <?php echo t('members_directory.received_amount'); ?> £<?php echo number_format($member['last_total_amount'], 0); ?>
                                </div>
                                <?php if ($member['total_payouts_received'] > 1): ?>
                                <div class="stat-detail mt-1">
                                    <small class="text-muted"><?php echo $member['total_payouts_received']; ?> <?php echo t('members_directory.total_payouts_received'); ?></small>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="stat-icon success">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-value"><?php echo $expected_payout_formatted; ?></div>
                                <div class="stat-label"><?php echo t('members_directory.payout_date'); ?></div>
                                <?php if (isset($payout_info['days_until_payout'])): ?>
                                <div class="stat-detail">
                                                                    <?php 
                                if ($payout_info['days_until_payout'] > 0) {
                                    echo $payout_info['days_until_payout'] . ' ' . t('members_directory.days_remaining');
                                } elseif ($payout_info['days_until_payout'] < 0) {
                                    echo t('members_directory.overdue_by') . ' ' . abs($payout_info['days_until_payout']) . ' ' . t('members_directory.days');
                                } else {
                                    echo 'Payout available today!';
                                }
                                ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fas fa-history text-success"></i>
                        <?php echo t('members_directory.recent_payment_history'); ?>
                    </h3>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold fs-5 text-dark"><strong><?php echo $member['payments_made']; ?></strong> <?php echo t('members_directory.payments_made'); ?></span>
                            <span class="fw-bold fs-5 text-primary"><strong><?php echo number_format($payment_progress, 1); ?>%</strong> <?php echo t('members_directory.completion'); ?></span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: <?php echo $payment_progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if (count($recent_payments) > 0): ?>
                        <div class="payment-history">
                            <?php foreach ($recent_payments as $payment): ?>
                                <div class="payment-item">
                                    <div class="payment-info">
                                        <h5><?php echo $payment['payment_month_name']; ?></h5>
                                        <div class="payment-date">
                                            <?php echo t('members_directory.paid_on'); ?> <?php echo $payment['formatted_date']; ?>
                                        </div>
                                    </div>
                                    <div class="payment-amount">
                                        £<?php echo number_format($payment['amount'], 0); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h5><?php echo t('members_directory.no_payment_history'); ?></h5>
                            <p><?php echo t('members_directory.no_payment_history_message'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payout History -->
        <?php if (count($payout_history) > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-trophy text-warning"></i>
                            <?php echo t('members_directory.payout_history'); ?>
                        </h3>
                        
                        <div class="payment-history">
                            <?php foreach ($payout_history as $payout): ?>
                                <div class="payment-item">
                                    <div class="payment-info">
                                        <h5><?php echo $payout['payout_month_name']; ?> <?php echo t('members_directory.payout'); ?></h5>
                                        <div class="payment-date">
                                            <?php echo t('members_directory.received_on'); ?> <?php echo $payout['formatted_date']; ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div class="payment-amount" style="color: var(--palette-gold);">
                                            £<?php echo number_format($payout['total_amount'], 0); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Member Details -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="profile-section">
                    <h3 class="section-title">
                        <i class="fas fa-user text-info"></i>
                        <?php echo t('members_directory.member_details'); ?>
                    </h3>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo $member['payments_made']; ?></div>
                            <div class="stat-label"><?php echo t('members_directory.successful_payments'); ?></div>
                            <div class="stat-detail">
                                <?php if ($member['duration_months']): ?>
                                    <small><?php echo $member['payments_made']; ?> <?php echo t('members_directory.of'); ?> <?php echo $member['duration_months']; ?> <?php echo t('members_directory.expected'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="stat-value"><?php echo $member['total_payouts_received']; ?></div>
                            <div class="stat-label"><?php echo t('members_directory.payouts_received'); ?></div>
                            <div class="stat-detail">
                                <?php if ($member['last_total_amount'] > 0): ?>
                                    <small><?php echo t('members_directory.last'); ?>: £<?php echo number_format($member['last_total_amount'], 0); ?></small>
                                <?php else: ?>
                                    <small><?php echo t('members_directory.no_payment_history'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="stat-value"><?php echo $member['payout_position']; ?> / <?php echo $member['total_equb_members']; ?></div>
                            <div class="stat-label"><?php echo t('members_directory.queue_position'); ?></div>
                            <div class="stat-detail">
                                <?php if (!empty($member['equb_name'])): ?>
                                    <small><?php echo htmlspecialchars($member['equb_name']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-value">
                                <?php echo $member_since; ?>
                            </div>
                            <div class="stat-label"><?php echo t('members_directory.member_since'); ?></div>
                            <div class="stat-detail">
                                <small>
                                    <?php 
                                    $status = $member['is_approved'] ? t('members_directory.approved') : t('members_directory.pending');
                                    $status_color = $member['is_approved'] ? 'text-success' : 'text-warning';
                                    ?>
                                    <span class="<?php echo $status_color; ?>"><?php echo $status; ?></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
    
    <script>
    // Enhanced profile page functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bar on load
        const progressFill = document.querySelector('.progress-fill');
        if (progressFill) {
            const targetWidth = progressFill.style.width;
            progressFill.style.width = '0%';
            setTimeout(() => {
                progressFill.style.width = targetWidth;
            }, 500);
        }
        
        // Add smooth hover effects
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html> 