<?php
/**
 * HabeshaEqub - Professional Profile Management Page
 * Account settings and personal information for members
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
require_once '../includes/enhanced_equb_calculator_final.php';

// Ensure translation function exists
if (!function_exists('t')) {
    function t($key) {
        return $key; // Fallback to key if translation function doesn't exist
    }
}

// Secure authentication check
require_once 'includes/auth_guard.php';
$user_id = get_current_user_id();

// Get comprehensive member data with enhanced calculations
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
               END as remaining_months_in_equb,
               -- Get latest login directly from members table
               COALESCE(m.last_login, m.created_at) as latest_login
        FROM members m 
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        LEFT JOIN payments p ON m.id = p.member_id
        WHERE m.id = ? AND m.is_active = 1
        GROUP BY m.id
    ");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        die("❌ ERROR: No member found with ID $user_id. Please check database.");
    }
    
    // Enhanced calculations using calculator
    try {
        if (isset($enhanced_calculator) && is_object($enhanced_calculator)) {
            $calc_result = $enhanced_calculator->calculateMemberFriendlyPayout($user_id);
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
            // Basic fallback if calculator not available
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
        error_log("Calculator error for member {$user_id}: " . $e->getMessage());
    }
    
    // Calculate expected payout date
    if (!empty($member['payout_month']) && $member['payout_month'] !== '0000-00-00') {
        $expected_payout_date = $member['payout_month'];
    } else if ($member['start_date'] && $member['payout_position'] > 0) {
        $start_date = new DateTime($member['start_date']);
        $payout_date = clone $start_date;
        $payout_date->add(new DateInterval('P' . ($member['payout_position'] - 1) . 'M'));
        $payout_date->setDate(
            $payout_date->format('Y'), 
            $payout_date->format('n'), 
            $member['payout_day'] ?? 1
        );
        $expected_payout_date = $payout_date->format('Y-m-d');
    } else {
        $expected_payout_date = null;
    }
    
} catch (PDOException $e) {
    die("❌ DATABASE ERROR: " . $e->getMessage());
}

// Calculate member data
$total_payouts_received = (int)$member['total_payouts_received'];
$member_since = date('M Y', strtotime($member['created_at']));
$latest_login_formatted = $member['latest_login'] ? date('M j, Y g:i A', strtotime($member['latest_login'])) : 'Never';

// Strong cache buster for assets
$cache_buster = time() . '_' . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('user_profile.page_title'); ?> - HabeshaEqub</title>
    
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
/* === TOP-TIER PROFESSIONAL PROFILE DESIGN === */

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

/* Enhanced Page Header - Top Tier Design */
.page-header {
    background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
    border-radius: 24px;
    padding: 50px 40px;
    margin-bottom: 45px;
    border: 1px solid var(--color-border);
    box-shadow: 0 12px 40px rgba(48, 25, 52, 0.1);
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
}

.page-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(218, 165, 32, 0.05) 0%, transparent 70%);
    border-radius: 50%;
}

.page-title {
    font-size: 36px;
    font-weight: 700;
    color: var(--color-deep-purple);
    margin: 0 0 12px 0;
    letter-spacing: -0.8px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    z-index: 2;
}

.page-subtitle {
    font-size: 20px;
    color: var(--color-dark-purple);
    margin: 0;
    font-weight: 400;
    opacity: 0.85;
    position: relative;
    z-index: 2;
}

/* Section Styling - Enhanced */
.section-title {
    font-size: 26px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    padding-left: 20px;
}

.section-title::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 30px;
    background: linear-gradient(180deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
    border-radius: 2px;
}

/* Profile Cards - Top Tier Design */
.profile-card {
    background: var(--color-white);
    border-radius: 24px;
    padding: 35px 30px;
    border: 1px solid var(--color-border);
    box-shadow: 0 8px 32px rgba(48, 25, 52, 0.08);
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
    margin-bottom: 30px;
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
    transform: scaleX(0);
    transition: transform 0.5s ease;
}

.profile-card::after {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(42, 157, 143, 0.03) 0%, transparent 70%);
    border-radius: 50%;
    transition: all 0.5s ease;
}

.profile-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(48, 25, 52, 0.15);
    border-color: rgba(218, 165, 32, 0.3);
}

.profile-card:hover::before {
    transform: scaleX(1);
}

.profile-card:hover::after {
    transform: scale(1.2);
    opacity: 0.8;
}

/* Form Styling - Premium Design */
.form-section {
    background: var(--color-white);
    border-radius: 24px;
    padding: 40px 35px;
    border: 1px solid var(--color-border);
    box-shadow: 0 8px 32px rgba(48, 25, 52, 0.08);
    margin-bottom: 35px;
    position: relative;
    overflow: hidden;
}

.form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    font-weight: 600;
    color: var(--color-deep-purple);
    margin-bottom: 10px;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-control {
    border: 2px solid rgba(77, 64, 82, 0.15);
    border-radius: 16px;
    padding: 16px 20px;
    font-size: 15px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    background: rgba(250, 250, 250, 0.5);
    backdrop-filter: blur(10px);
}

.form-control:focus {
    border-color: var(--color-gold);
    box-shadow: 0 0 0 0.25rem rgba(218, 165, 32, 0.15);
    background: var(--color-white);
    transform: translateY(-2px);
}

/* Enhanced Button Styling */
.btn {
    border-radius: 16px;
    padding: 15px 30px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border: none;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-deep-purple) 0%, var(--color-dark-purple) 100%);
    color: white;
    box-shadow: 0 6px 24px rgba(48, 25, 52, 0.35);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(48, 25, 52, 0.45);
    background: linear-gradient(135deg, var(--color-dark-purple) 0%, var(--color-deep-purple) 100%);
}

.btn-warning {
    background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
    color: var(--color-deep-purple);
    box-shadow: 0 6px 24px rgba(218, 165, 32, 0.35);
}

.btn-warning:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(218, 165, 32, 0.45);
    color: var(--color-deep-purple);
    background: linear-gradient(135deg, var(--color-light-gold) 0%, var(--color-gold) 100%);
}

.btn-outline-secondary {
    background: transparent;
    border: 2px solid var(--color-border);
    color: var(--color-dark-purple);
    backdrop-filter: blur(10px);
}

.btn-outline-secondary:hover {
    background: var(--color-cream);
    border-color: var(--color-gold);
    color: var(--color-deep-purple);
    transform: translateY(-2px);
}

/* Status Badge - Premium */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
}

.status-badge.active {
    background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
    color: var(--color-deep-purple);
    box-shadow: 0 4px 16px rgba(218, 165, 32, 0.3);
}

.status-badge.pending {
    background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
    color: var(--color-deep-purple);
    box-shadow: 0 4px 16px rgba(218, 165, 32, 0.3);
}

/* Account Info Styling */
.account-info-item {
    background: rgba(241, 236, 226, 0.3);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(77, 64, 82, 0.08);
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.account-info-item:hover {
    background: rgba(241, 236, 226, 0.5);
    transform: translateX(5px);
}

.account-info-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-dark-purple);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.account-info-value {
    font-size: 16px;
    color: var(--color-deep-purple);
    font-weight: 500;
}

/* Mobile Responsive Design - Top Tier */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0 15px;
    }
    
    .page-header {
        padding: 35px 25px;
        margin-bottom: 35px;
        border-radius: 20px;
    }
    
    .page-title {
        font-size: 28px;
        text-align: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
    }
    
    .page-subtitle {
        font-size: 16px;
        text-align: center;
    }
    
    .section-title {
        font-size: 22px;
        text-align: center;
        justify-content: center;
        padding-left: 0;
        margin-bottom: 30px;
    }
    
    .section-title::before {
        display: none;
    }
    
    .profile-card {
        padding: 25px 20px;
        margin-bottom: 25px;
        border-radius: 20px;
    }
    
    .form-section {
        padding: 30px 20px;
        border-radius: 20px;
        margin-bottom: 30px;
    }
    
    .btn {
        padding: 12px 24px;
        font-size: 14px;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .account-info-item {
        padding: 16px;
        margin-bottom: 16px;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0 12px;
    }
    
    .page-header {
        padding: 25px 18px;
        margin-bottom: 25px;
        border-radius: 18px;
    }
    
    .page-title {
        font-size: 24px;
        line-height: 1.2;
    }
    
    .page-subtitle {
        font-size: 14px;
    }
    
    .profile-card {
        padding: 20px 16px;
        margin-bottom: 20px;
        border-radius: 18px;
    }
    
    .form-section {
        padding: 25px 18px;
        margin-bottom: 25px;
        border-radius: 18px;
    }
    
    .section-title {
        font-size: 20px;
        margin-bottom: 25px;
    }
    
    .form-control {
        padding: 14px 16px;
        font-size: 14px;
    }
    
    .btn {
        padding: 12px 20px;
        font-size: 13px;
    }
}

@media (max-width: 400px) {
    .page-header {
        padding: 20px 15px;
    }
    
    .profile-card {
        padding: 18px 14px;
    }
    
    .form-section {
        padding: 20px 15px;
    }
}

/* Performance optimizations */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.profile-card, .form-section {
    will-change: transform;
}

/* Loading animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-card, .form-section {
    animation: fadeInUp 0.6s ease-out;
}

.profile-card:nth-child(2) { animation-delay: 0.1s; }
.form-section:nth-child(2) { animation-delay: 0.2s; }

/* Profile Stat Cards */
.profile-stat-card {
    background: var(--color-white);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--color-border);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    align-items: center;
    gap: 16px;
}

.profile-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
}

.profile-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(48, 25, 52, 0.15);
    border-color: var(--color-gold);
}

.profile-stat-card .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
    background: rgba(218, 165, 32, 0.1);
}

.profile-stat-card .stat-icon.text-success {
    background: rgba(40, 167, 69, 0.1);
    color: var(--color-green);
}

.profile-stat-card .stat-icon.text-primary {
    background: rgba(13, 110, 253, 0.1);
    color: var(--color-purple);
}

.profile-stat-card .stat-icon.text-warning {
    background: rgba(255, 193, 7, 0.1);
    color: var(--color-gold);
}

.profile-stat-card .stat-icon.text-info {
    background: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.profile-stat-card .stat-content h3 {
    font-size: 28px;
    font-weight: 700;
    color: var(--color-deep-purple);
    margin-bottom: 4px;
    line-height: 1;
}

.profile-stat-card .stat-content p {
    font-size: 15px;
    color: var(--color-text);
    margin-bottom: 0;
    font-weight: 500;
}

.profile-stat-card .stat-content small {
    font-size: 13px;
    color: var(--color-text-muted);
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
                        <i class="fas fa-user-circle text-warning"></i>
                        <?php echo t('user_profile.page_title'); ?>
                    </h1>
                    <p class="page-subtitle"><?php echo t('user_profile.page_subtitle'); ?></p>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="row">
            <!-- Personal Information Form -->
            <div class="col-lg-8">
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user-edit text-primary"></i>
                        <?php echo t('user_profile.personal_info'); ?>
                    </h2>
                    
                    <form id="profileForm" method="POST" action="api/update-profile.php">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user text-primary"></i>
                                        <?php echo t('user_profile.first_name'); ?>
                                    </label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($member['first_name'] ?? '', ENT_QUOTES); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user text-primary"></i>
                                        <?php echo t('user_profile.last_name'); ?>
                                    </label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($member['last_name'] ?? '', ENT_QUOTES); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope text-warning"></i>
                                        <?php echo t('user_profile.email'); ?>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($member['email'] ?? '', ENT_QUOTES); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone text-success"></i>
                                        <?php echo t('user_profile.phone'); ?>
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone'] ?? '', ENT_QUOTES); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?php echo t('user_profile.update_profile'); ?>
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                <?php echo t('user_profile.back_dashboard'); ?>
                            </a>
                        </div>
                    </form>
                </div>


            </div>

            <!-- Account Summary Sidebar -->
            <div class="col-lg-4">
                <div class="profile-card">
                    <h2 class="section-title">
                        <i class="fas fa-user-shield text-primary"></i>
                        <?php echo t('user_profile.account_summary'); ?>
                    </h2>
                    
                    <div class="account-info-item">
                        <div class="account-info-label">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?php echo t('user_profile.member_since'); ?>
                        </div>
                        <div class="account-info-value"><?php echo $member_since; ?></div>
                    </div>
                    
                    <div class="account-info-item">
                        <div class="account-info-label">
                            <i class="fas fa-id-card me-1"></i>
                            <?php echo t('user_profile.member_id'); ?>
                        </div>
                        <div class="account-info-value">
                            <code style="background: var(--color-cream); padding: 4px 8px; border-radius: 6px; color: var(--color-deep-purple);">
                                <?php echo htmlspecialchars($member['member_id'] ?? 'N/A'); ?>
                            </code>
                        </div>
                    </div>
                    
                    <div class="account-info-item">
                        <div class="account-info-label">
                            <i class="fas fa-check-circle me-1"></i>
                            <?php echo t('user_profile.account_status'); ?>
                        </div>
                        <div class="account-info-value">
                            <span class="status-badge active">
                                <i class="fas fa-check"></i>
                                <?php echo t('user_profile.active'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="account-info-item">
                        <div class="account-info-label">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Last Login
                        </div>
                        <div class="account-info-value">
                            <span class="text-info fw-bold"><?php echo $latest_login_formatted; ?></span>
                        </div>
                    </div>

                    <div class="account-info-item">
                        <div class="account-info-label">
                            <i class="fas fa-shield-alt me-1"></i>
                            Privacy Setting
                        </div>
                        <div class="account-info-value">
                            <?php if ($member['go_public'] == 1): ?>
                                <span class="text-success fw-bold">Public Profile</span>
                            <?php else: ?>
                                <span class="text-secondary fw-bold">Private Profile</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr style="border-color: var(--color-border); margin: 25px 0;">
                    
                    <div class="d-grid gap-3">
                        <a href="contributions.php" class="btn btn-primary">
                            <i class="fas fa-credit-card me-2"></i>
                            <?php echo t('user_profile.view_payments'); ?>
                        </a>
                        <a href="payout-info.php" class="btn btn-warning">
                            <i class="fas fa-chart-line me-2"></i>
                            <?php echo t('user_profile.payout_info'); ?>
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
    
    <script>
    // Enhanced form validation and interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Form loading animation
        const cards = document.querySelectorAll('.profile-card, .form-section');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Enhanced stat card animations
        const statCards = document.querySelectorAll('.profile-stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Profile form enhancement
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            // Real-time email validation
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.style.borderColor = 'var(--color-brown)';
                    this.style.boxShadow = '0 0 0 0.25rem rgba(93, 66, 37, 0.15)';
                } else if (this.value) {
                    this.style.borderColor = 'var(--color-gold)';
                    this.style.boxShadow = '0 0 0 0.25rem rgba(218, 165, 32, 0.15)';
                }
            });

            profileForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email.value)) {
                    showAlert('<?php echo t('user_profile.email_invalid'); ?>', 'warning');
                    return false;
                }
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showAlert(result.message, 'success');
                    } else {
                        showAlert(result.message, 'danger');
                    }
                } catch (error) {
                    showAlert('Network error. Please try again.', 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        }

        // Enhanced form interactions
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                this.parentNode.style.transform = 'scale(1.02)';
                this.parentNode.style.transition = 'transform 0.3s ease';
            });
            
            control.addEventListener('blur', function() {
                this.parentNode.style.transform = 'scale(1)';
            });
        });
    });

    // Alert system
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[alerts.length - 1].remove();
            }
        }, 5000);
    }
    </script>
</body>
</html> 