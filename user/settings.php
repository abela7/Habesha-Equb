<?php
/**
 * HabeshaEqub - Professional User Settings Page
 * Account preferences, security settings, and user customization
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

// Secure authentication check
require_once 'includes/auth_guard.php';
$user_id = get_current_user_id();

// Get member data
try {
    $stmt = $db->prepare("
        SELECT m.*
        FROM members m 
        WHERE m.id = ? AND m.is_active = 1
    ");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        die("❌ ERROR: No member found with ID $user_id. Please check database.");
    }
} catch (PDOException $e) {
    die("❌ DATABASE ERROR: " . $e->getMessage());
}

// Get notification preferences from new database columns
$email_notifications = (bool)($member['email_notifications'] ?? 1); // Default to 1 (enabled)
$sms_notifications = (bool)($member['email_notifications'] ?? 1); // For now, use same as email (SMS column doesn't exist yet)
$payment_reminders = (bool)($member['payment_reminders'] ?? 1); // Default to 1 (enabled)
$swap_terms_allowed = (bool)($member['swap_terms_allowed'] ?? 0); // Default to 0 (disabled)

// Strong cache buster for assets
$cache_buster = time() . '_' . rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('settings.page_title'); ?> - HabeshaEqub</title>
    
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
/* === TOP-TIER PROFESSIONAL SETTINGS DESIGN === */

/* Professional 6-Color Palette - Consistent */
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
    --color-success: #2A9D8F;
    --color-warning: #E9C46A;
    --color-danger: #E76F51;
}

/* STUNNING Page Header - Premium Design */
.page-header {
    background: linear-gradient(135deg, 
        var(--color-white) 0%, 
        var(--color-cream) 30%, 
        #FAF8F5 100%);
    border-radius: 30px;
    padding: 60px 50px;
    margin-bottom: 50px;
    border: 2px solid var(--color-gold);
    box-shadow: 
        0 20px 60px rgba(48, 25, 52, 0.15),
        0 0 0 1px rgba(218, 165, 32, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(20px);
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

/* STUNNING Settings Card - Top-Tier Design */
.settings-card {
    background: linear-gradient(145deg, 
        var(--color-white) 0%, 
        rgba(241, 236, 226, 0.4) 100%);
    border-radius: 30px;
    padding: 45px 40px;
    border: 2px solid var(--color-gold);
    box-shadow: 
        0 20px 60px rgba(48, 25, 52, 0.15),
        0 8px 25px rgba(218, 165, 32, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9),
        inset 0 -1px 0 rgba(218, 165, 32, 0.1);
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
    margin-bottom: 35px;
    backdrop-filter: blur(10px);
}

.settings-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, 
        transparent 0%, 
        var(--color-gold) 50%, 
        transparent 100%);
    transform: scaleX(0);
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}



.settings-card::after {
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

.settings-card:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 
        0 30px 80px rgba(48, 25, 52, 0.2),
        0 15px 35px rgba(218, 165, 32, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 1),
        0 0 0 1px rgba(218, 165, 32, 0.4);
    border-color: var(--color-gold);
}

.settings-card:hover::before {
    transform: scaleX(1);
    background: linear-gradient(90deg, 
        var(--color-gold) 0%, 
        var(--color-light-gold) 50%, 
        var(--color-gold) 100%);
}

.settings-card:hover::after {
    transform: scale(1.2);
    opacity: 0.8;
}

/* Section Styling - Enhanced */
.section-title {
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, 
        var(--color-dark-purple) 0%, 
        var(--color-gold) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 35px;
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    padding-left: 25px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-title::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 40px;
    background: linear-gradient(180deg, 
        var(--color-gold) 0%, 
        var(--color-light-gold) 50%,
        var(--color-gold) 100%);
    border-radius: 6px;
    box-shadow: 
        0 4px 12px rgba(218, 165, 32, 0.4),
        0 2px 6px rgba(218, 165, 32, 0.2);
}

/* Form Controls - Premium */
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

.form-control, .form-select {
    border: 2px solid rgba(77, 64, 82, 0.15);
    border-radius: 16px;
    padding: 16px 20px;
    font-size: 15px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    background: rgba(250, 250, 250, 0.5);
    backdrop-filter: blur(10px);
}

.form-control:focus, .form-select:focus {
    border-color: var(--color-gold);
    box-shadow: 0 0 0 0.25rem rgba(218, 165, 32, 0.15);
    background: var(--color-white);
    transform: translateY(-2px);
}

/* STUNNING Toggle Switch Design */
.form-check {
    margin-bottom: 25px;
    padding-left: 0;
    display: flex;
    align-items: center;
    gap: 20px;
}

.form-check-input {
    width: 60px;
    height: 30px;
    border-radius: 30px;
    background: linear-gradient(145deg, #f0f0f0, #e5e7eb);
    border: 2px solid #d1d5db;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    margin: 0;
    flex-shrink: 0;
    appearance: none;
    -webkit-appearance: none;
    box-shadow: 
        inset 0 2px 4px rgba(0, 0, 0, 0.1),
        0 2px 8px rgba(0, 0, 0, 0.05);
}

.form-check-input:focus {
    box-shadow: 
        0 0 0 4px rgba(218, 165, 32, 0.2),
        inset 0 2px 4px rgba(0, 0, 0, 0.1),
        0 2px 8px rgba(0, 0, 0, 0.05);
    outline: none;
    border-color: var(--color-gold);
}

.form-check-input:checked {
    background: linear-gradient(145deg, var(--color-gold), var(--color-light-gold));
    border-color: var(--color-gold);
    box-shadow: 
        0 0 0 4px rgba(218, 165, 32, 0.15),
        inset 0 2px 4px rgba(0, 0, 0, 0.1),
        0 4px 12px rgba(218, 165, 32, 0.3);
}

.form-check-input::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 24px;
    height: 24px;
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    border-radius: 50%;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.15),
        0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-check-input:checked::before {
    transform: translateX(30px);
    background: linear-gradient(145deg, #ffffff, #fff8e1);
    box-shadow: 
        0 4px 12px rgba(0, 0, 0, 0.2),
        0 2px 6px rgba(218, 165, 32, 0.3);
}

.form-check-label {
    font-weight: 600;
    color: var(--color-dark-purple);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 0;
    flex: 1;
    font-size: 1.1rem;
}

/* Enhanced Button Styling */
.btn {
    border-radius: 20px;
    padding: 18px 35px;
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

.btn-success {
    background: linear-gradient(135deg, var(--color-success) 0%, #26a69a 100%);
    color: white;
    box-shadow: 0 6px 24px rgba(42, 157, 143, 0.35);
}

.btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(42, 157, 143, 0.45);
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

/* Feature Cards */
.feature-item {
    background: rgba(241, 236, 226, 0.3);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
    border: 1px solid rgba(77, 64, 82, 0.08);
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.feature-item:hover {
    background: rgba(241, 236, 226, 0.5);
    transform: translateX(5px);
    border-color: rgba(218, 165, 32, 0.2);
}

.feature-content {
    flex: 1;
}

.feature-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-deep-purple);
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.feature-description {
    font-size: 14px;
    color: var(--color-dark-purple);
    opacity: 0.8;
    margin: 0;
}

.feature-toggle {
    margin-left: 20px;
}

/* Account Info Display */
.account-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-item {
    background: rgba(241, 236, 226, 0.3);
    border-radius: 16px;
    padding: 20px;
    border: 1px solid rgba(77, 64, 82, 0.08);
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    text-align: center;
}

.info-item:hover {
    background: rgba(241, 236, 226, 0.5);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(48, 25, 52, 0.1);
}

.info-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-dark-purple);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    opacity: 0.7;
}

.info-value {
    font-size: 18px;
    font-weight: 600;
    color: var(--color-deep-purple);
}

.info-value code {
    background: var(--color-cream);
    padding: 4px 8px;
    border-radius: 6px;
    color: var(--color-deep-purple);
    font-size: 16px;
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
    
    .settings-card {
        padding: 30px 20px;
        margin-bottom: 25px;
        border-radius: 20px;
    }
    
    .btn {
        padding: 12px 24px;
        font-size: 14px;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .feature-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .feature-toggle {
        margin-left: 0;
    }
    
    .form-check {
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .form-check-input {
        width: 55px;
        height: 28px;
    }
    
    .form-check-input::before {
        width: 22px;
        height: 22px;
        top: 1px;
        left: 1px;
    }
    
    .form-check-input:checked::before {
        transform: translateX(27px);
    }
    
    .account-info-grid {
        grid-template-columns: 1fr;
        gap: 15px;
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
    
    .settings-card {
        padding: 25px 18px;
        margin-bottom: 20px;
        border-radius: 18px;
    }
    
    .section-title {
        font-size: 20px;
        margin-bottom: 25px;
    }
    
    .form-control, .form-select {
        padding: 14px 16px;
        font-size: 14px;
    }
    
    .btn {
        padding: 12px 20px;
        font-size: 13px;
    }
}

/* Performance optimizations */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.settings-card {
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

.settings-card {
    animation: fadeInUp 0.6s ease-out;
}

.settings-card:nth-child(2) { animation-delay: 0.1s; }
.settings-card:nth-child(3) { animation-delay: 0.2s; }
.settings-card:nth-child(4) { animation-delay: 0.3s; }
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
                        <i class="fas fa-cogs text-warning"></i>
                        <?php echo t('settings.page_title'); ?>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Notification Preferences -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h2 class="section-title">
                        <i class="fas fa-bell text-warning"></i>
                        <?php echo t('settings.notification_preferences'); ?>
                    </h2>
                    
                    <form id="notificationForm" action="api/update-settings.php" method="POST">
                        <input type="hidden" name="action" value="notifications">
                        
                        <div class="feature-item">
                            <div class="feature-content">
                                <div class="feature-title">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <?php echo t('settings.email_notifications'); ?>
                                </div>
                                <p class="feature-description">
                                    <?php echo t('settings.email_notifications_desc'); ?>
                                </p>
                            </div>
                            <div class="feature-toggle">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                           <?php echo $email_notifications ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-content">
                                <div class="feature-title">
                                    <i class="fas fa-sms text-success"></i>
                                    <?php echo t('settings.sms_notifications'); ?>
                                </div>
                                <p class="feature-description">
                                    <?php echo t('settings.sms_notifications_desc'); ?>
                                </p>
                            </div>
                            <div class="feature-toggle">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications"
                                           <?php echo $sms_notifications ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-content">
                                <div class="feature-title">
                                    <i class="fas fa-crown text-warning"></i>
                                    <?php echo t('settings.payment_reminders'); ?>
                                </div>
                                <p class="feature-description">
                                    <?php echo t('settings.payment_reminders_desc'); ?>
                                </p>
                            </div>
                            <div class="feature-toggle">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="payment_reminders" name="payment_reminders"
                                           <?php echo $payment_reminders ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?php echo t('settings.save_preferences'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Language Preference -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h2 class="section-title">
                        <i class="fas fa-globe text-success"></i>
                        <?php echo t('settings.language_preference'); ?>
                    </h2>
                    
                    <form id="languageForm" action="api/update-settings.php" method="POST">
                        <input type="hidden" name="action" value="privacy">
                        <input type="hidden" name="go_public" value="<?php echo ($member['go_public'] ?? 0); ?>">
                        
                        <div class="form-group">
                            <label for="language_preference" class="form-label">
                                <i class="fas fa-language text-warning"></i>
                                <?php echo t('settings.interface_language'); ?>
                            </label>
                            
                            <!-- Debug Info -->
                            <div class="alert alert-info mb-3" style="font-size: 12px; padding: 8px;">
                                <strong>Debug:</strong> 
                                DB Value: <?php echo $member['language_preference'] ?? 'NULL'; ?> | 
                                Session: <?php echo $_SESSION['app_language'] ?? 'NOT SET'; ?>
                            </div>
                            
                            <select class="form-select" id="language_preference" name="language_preference" style="border-radius: 12px; border: 2px solid #daa520; padding: 12px 16px; background: white; font-size: 16px;">
                                <option value="0" <?php echo ($member['language_preference'] == 0) ? 'selected' : ''; ?>>
                                    🇺🇸 English
                                </option>
                                <option value="1" <?php echo ($member['language_preference'] == 1) ? 'selected' : ''; ?>>
                                    🇪🇹 አማርኛ (Amharic)
                                </option>
                            </select>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-warning flex-fill">
                                <i class="fas fa-sync-alt me-2"></i>
                                <?php echo t('settings.apply_language'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Privacy & Security -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h2 class="section-title">
                        <i class="fas fa-shield-alt text-primary"></i>
                        <?php echo t('settings.privacy_security'); ?>
                    </h2>
                    
                    <form id="privacyForm" action="api/update-settings.php" method="POST">
                        <input type="hidden" name="action" value="privacy">
                        <input type="hidden" name="language_preference" value="<?php echo ($member['language_preference'] ?? 0); ?>">
                        
                        <div class="feature-item">
                            <div class="feature-content">
                                <div class="feature-title">
                                    <i class="fas fa-eye text-info"></i>
                                    <?php echo t('settings.public_profile'); ?>
                                </div>
                                <p class="feature-description">
                                    <?php echo t('settings.public_profile_desc'); ?>
                                </p>
                            </div>
                            <div class="feature-toggle">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="go_public" name="go_public"
                                           <?php echo $member['go_public'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-warning flex-fill">
                                <i class="fas fa-shield-alt me-2"></i>
                                <?php echo t('settings.save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
    
    <script>
    // Enhanced settings page functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Form loading animation
        const cards = document.querySelectorAll('.settings-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Notification form handling
        const notificationForm = document.getElementById('notificationForm');
        if (notificationForm) {
            notificationForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch('api/update-settings.php', {
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

        // Language form handling
        const languageForm = document.getElementById('languageForm');
        if (languageForm) {
            languageForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Applying...';
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch('api/update-settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        console.log('Language update response:', result);
                        showAlert(result.message, 'success');
                        if (result.language_changed) {
                            console.log('Language changed to:', result.new_language);
                            setTimeout(() => {
                                console.log('Reloading page...');
                                window.location.reload();
                            }, 1000);
                        }
                    } else {
                        console.error('Language update failed:', result);
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

        // Privacy form handling
        const privacyForm = document.getElementById('privacyForm');
        if (privacyForm) {
            privacyForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch('api/update-settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showAlert(result.message, 'success');
                        
                        // If language was changed, reload page immediately to apply
                        if (result.language_changed) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
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
        const switches = document.querySelectorAll('.form-check-input[type="checkbox"]');
        switches.forEach(switchElement => {
            switchElement.addEventListener('change', function() {
                this.parentNode.style.transform = 'scale(1.05)';
                this.parentNode.style.transition = 'transform 0.2s ease';
                
                setTimeout(() => {
                    this.parentNode.style.transform = 'scale(1)';
                }, 200);
            });
        });
        
        // Feature item hover effects
        const featureItems = document.querySelectorAll('.feature-item');
        featureItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.borderColor = 'rgba(218, 165, 32, 0.3)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.borderColor = 'rgba(77, 64, 82, 0.08)';
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