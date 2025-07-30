<?php
/**
 * HabeshaEqub - Waiting for Approval Page
 * User sees this page after registration while waiting for admin approval
 */

// Skip auth check since this is for users waiting for approval
define('SKIP_AUTH_CHECK', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../languages/translator.php';

// Start session to check if user just registered
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user has a pending registration
$user_email = isset($_SESSION['pending_email']) ? $_SESSION['pending_email'] : null;
$user_name = isset($_SESSION['pending_name']) ? $_SESSION['pending_name'] : null;

// If no pending registration in session, check if we have email in URL (from registration redirect)
if (!$user_email && isset($_GET['email'])) {
    $user_email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
}

// Set language from user preference or default to Amharic
if (!isset($_SESSION['app_language'])) {
    setLanguage('am');
}

// Load user's language preference if they have an account
if ($user_email) {
    try {
        $lang_stmt = $db->prepare("SELECT language_preference FROM members WHERE email = ?");
        $lang_stmt->execute([$user_email]);
        $lang_result = $lang_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lang_result) {
            $user_language = ($lang_result['language_preference'] == 1) ? 'am' : 'en';
            setLanguage($user_language);
        }
    } catch (Exception $e) {
        // Fallback to default language if there's an error
        error_log("Error loading user language preference: " . $e->getMessage());
    }
}

// If still no email, redirect to registration
if (!$user_email) {
    header('Location: login.php?msg=' . urlencode('Please register first'));
    exit;
}

// Check user status in database
try {
    $stmt = $db->prepare("
        SELECT id, member_id, first_name, last_name, email, is_approved, is_active, created_at 
        FROM members 
        WHERE email = ?
    ");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: login.php?msg=' . urlencode('Registration not found'));
        exit;
    }
    
    // If user is already approved, redirect to login
    if ($user['is_approved'] == 1) {
        unset($_SESSION['pending_email'], $_SESSION['pending_name']);
        header('Location: login.php?msg=' . urlencode('Your account has been approved! Please log in.'));
        exit;
    }
    
    // If user is declined (inactive), show message
    if ($user['is_active'] == 0) {
        $declined = true;
    } else {
        $declined = false;
    }
    
} catch (Exception $e) {
    error_log("Waiting approval page error: " . $e->getMessage());
    header('Location: login.php?msg=' . urlencode('An error occurred. Please try again.'));
    exit;
}

$user_name = $user['first_name'] . ' ' . $user['last_name'];
$registration_date = new DateTime($user['created_at']);
$waiting_hours = (new DateTime())->diff($registration_date)->days * 24 + (new DateTime())->diff($registration_date)->h;
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('waiting_approval.page_title'); ?></title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32x32.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --color-cream: #F1ECE2;
            --color-dark-purple: #4D4052;
            --color-navy: #301934;
            --color-gold: #DAA520;
            --color-light-cream: #CDAF56;
            --color-brown: #5D4225;
            --gradient-primary: linear-gradient(135deg, var(--color-navy) 0%, var(--color-dark-purple) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-cream) 100%);
            --shadow-elegant: 0 20px 40px rgba(48, 25, 52, 0.1);
            --shadow-card: 0 8px 32px rgba(48, 25, 52, 0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background patterns */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(218, 165, 32, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(205, 175, 86, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(77, 64, 82, 0.1) 0%, transparent 50%);
            z-index: -1;
            animation: backgroundFloat 20s ease-in-out infinite;
        }
        
        @keyframes backgroundFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }
        
        .waiting-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .waiting-card {
            background: rgba(241, 236, 226, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: var(--shadow-elegant);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            position: relative;
            animation: cardSlideUp 0.8s ease-out;
        }
        
        @keyframes cardSlideUp {
            from { 
                opacity: 0; 
                transform: translateY(50px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        .waiting-header {
            background: var(--gradient-secondary);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }
        
        .waiting-header::before {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 20px;
            background: var(--gradient-secondary);
            border-radius: 100px;
            filter: blur(8px);
            opacity: 0.6;
        }
        
        .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
        
        .status-icon i {
            font-size: 2.5rem;
            color: var(--color-navy);
        }
        
        .waiting-title {
            color: var(--color-navy);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .waiting-subtitle {
            color: var(--color-brown);
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .waiting-content {
            padding: 40px 30px;
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .user-name {
            color: var(--color-navy);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .user-email {
            color: var(--color-brown);
            font-size: 1rem;
            margin-bottom: 15px;
        }
        
        .user-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--color-brown);
            font-size: 0.9rem;
        }
        
        .meta-item i {
            color: var(--color-gold);
            width: 16px;
        }
        
        .status-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .status-title {
            color: var(--color-navy);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .status-message {
            color: var(--color-brown);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .waiting-time {
            background: rgba(255, 193, 7, 0.1);
            border: 2px solid rgba(255, 193, 7, 0.3);
            border-radius: 12px;
            padding: 15px;
            color: #e67e22;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .declined-message {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            padding: 20px;
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .declined-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .next-steps {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid rgba(40, 167, 69, 0.2);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .next-steps-title {
            color: var(--color-navy);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .next-steps-list {
            list-style: none;
            padding: 0;
        }
        
        .next-steps-list li {
            color: var(--color-brown);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }
        
        .next-steps-list li i {
            color: #28a745;
            width: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary-custom {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(48, 25, 52, 0.3);
            color: white;
        }
        
        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.8);
            color: var(--color-navy);
            border: 2px solid var(--color-gold);
        }
        
        .btn-secondary-custom:hover {
            background: var(--color-gold);
            color: white;
            transform: translateY(-2px);
        }
        
        .refresh-info {
            text-align: center;
            color: var(--color-brown);
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 20px;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .waiting-container {
                padding: 10px;
            }
            
            .waiting-card {
                border-radius: 20px;
                margin: 10px;
            }
            
            .waiting-header {
                padding: 30px 20px 25px;
            }
            
            .waiting-content {
                padding: 30px 20px;
            }
            
            .waiting-title {
                font-size: 1.6rem;
            }
            
            .user-meta {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .status-icon {
                width: 60px;
                height: 60px;
            }
            
            .status-icon i {
                font-size: 2rem;
            }
        }
        
        /* Auto-refresh animation */
        .refresh-animation {
            animation: refreshSpin 1s linear;
        }
        
        @keyframes refreshSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="waiting-container">
        <div class="waiting-card">
            <!-- Header -->
            <div class="waiting-header">
                <div class="status-icon">
                    <i class="fas fa-<?php echo $declined ? 'times-circle' : 'hourglass-half'; ?>"></i>
                </div>
                <h1 class="waiting-title">
                    <?php echo $declined ? t('waiting_approval.declined_title') : t('waiting_approval.waiting_title'); ?>
                </h1>
                <p class="waiting-subtitle">
                    <?php echo $declined ? t('waiting_approval.declined_subtitle') : t('waiting_approval.waiting_subtitle'); ?>
                </p>
            </div>
            
            <!-- Content -->
            <div class="waiting-content">
                <!-- User Information -->
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                    <div class="user-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo t('waiting_approval.registered'); ?>: <?php echo $registration_date->format('M j, Y'); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-id-card"></i>
                            <?php echo t('waiting_approval.member_id'); ?>: <?php echo htmlspecialchars($user['member_id']); ?>
                        </div>
                    </div>
                </div>

                <?php if ($declined): ?>
                    <!-- Declined Message -->
                    <div class="declined-message">
                        <div class="declined-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h4><?php echo t('waiting_approval.application_not_approved'); ?></h4>
                        <p><?php echo t('waiting_approval.declined_message'); ?></p>
                        
                        <div class="action-buttons" style="margin-top: 20px;">
                            <a href="login.php" class="btn-custom btn-secondary-custom">
                                <i class="fas fa-arrow-left"></i>
                                <?php echo t('waiting_approval.back_to_login'); ?>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Waiting Status -->
                    <div class="status-section">
                        <h3 class="status-title">
                            <i class="fas fa-clock"></i>
                            <?php echo t('waiting_approval.approval_under_review'); ?>
                        </h3>
                        <p class="status-message">
                            <?php echo t('waiting_approval.approval_message'); ?>
                        </p>
                        <p class="status-message" style="margin-top: 15px; font-size: 0.95em; opacity: 0.9;">
                            <?php echo t('waiting_approval.detailed_message'); ?>
                        </p>
                        
                        <div class="waiting-time">
                            <i class="fas fa-hourglass-half"></i>
                            <?php echo t('waiting_approval.waiting_time'); ?>: <?php echo $waiting_hours; ?> <?php echo t('waiting_approval.hours'); ?>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="next-steps">
                        <h4 class="next-steps-title">
                            <i class="fas fa-list-check"></i>
                            <?php echo t('waiting_approval.what_happens_next'); ?>
                        </h4>
                        <ul class="next-steps-list">
                            <li>
                                <i class="fas fa-user-check"></i>
                                <?php echo t('waiting_approval.review_details'); ?>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <?php echo t('waiting_approval.contact_guarantor'); ?>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <?php echo t('waiting_approval.email_notification'); ?>
                            </li>
                            <li>
                                <i class="fas fa-sign-in-alt"></i>
                                <?php echo t('waiting_approval.login_access'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="button" class="btn-custom btn-primary-custom" onclick="refreshStatus()">
                            <i class="fas fa-sync-alt" id="refresh-icon"></i>
                            <?php echo t('waiting_approval.check_status'); ?>
                        </button>
                        <a href="login.php" class="btn-custom btn-secondary-custom">
                            <i class="fas fa-sign-in-alt"></i>
                            <?php echo t('waiting_approval.try_login'); ?>
                        </a>
                    </div>
                    
                    <div class="refresh-info">
                        <i class="fas fa-info-circle"></i>
                        <?php echo t('waiting_approval.auto_refresh_info'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Refresh status function
        function refreshStatus() {
            const icon = document.getElementById('refresh-icon');
            icon.classList.add('refresh-animation');
            
            // Remove animation after 1 second, then reload page
            setTimeout(() => {
                icon.classList.remove('refresh-animation');
                location.reload();
            }, 1000);
        }
        
        // Auto-refresh every 2 minutes if not declined
        <?php if (!$declined): ?>
        setInterval(() => {
            location.reload();
        }, 120000); // 2 minutes
        <?php endif; ?>
        
        // Check for approval status every 30 seconds
        <?php if (!$declined): ?>
        setInterval(async () => {
            try {
                const response = await fetch('api/check-approval-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: '<?php echo htmlspecialchars($user_email); ?>'
                    })
                });
                
                const result = await response.json();
                
                if (result.success && result.data.is_approved) {
                    // User has been approved, show success message and redirect
                    showApprovalSuccess();
                }
                
            } catch (error) {
                // Silently handle errors to avoid disrupting user experience
                console.log('Status check failed:', error);
            }
        }, 30000); // 30 seconds
        <?php endif; ?>
        
        function showApprovalSuccess() {
            // Create and show success overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(40, 167, 69, 0.95);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                animation: fadeIn 0.5s ease-out;
            `;
            
            overlay.innerHTML = `
                <div style="text-align: center; animation: slideUp 0.8s ease-out;">
                    <i class="fas fa-check-circle" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h2 style="margin-bottom: 15px;"><?php echo t('waiting_approval.account_approved'); ?></h2>
                    <p style="font-size: 1.2rem; margin-bottom: 25px;"><?php echo t('waiting_approval.account_approved_message'); ?></p>
                    <p><?php echo t('waiting_approval.redirecting'); ?></p>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = 'login.php?msg=' + encodeURIComponent('Your account has been approved! Please log in.');
            }, 3000);
        }
        
        // Add some CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(50px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html> 