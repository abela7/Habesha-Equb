<?php
/**
 * HabeshaEqub - Bilingual Waiting for Approval Page
 * Beautiful design with Amharic default + language switcher
 */

// Skip auth check since this is for users waiting for approval
define('SKIP_AUTH_CHECK', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../languages/translator.php';

// Start session to check if user just registered
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default language to Amharic if not set
if (!isset($_SESSION['app_language'])) {
    setLanguage('am'); // Default to Amharic
}

// Ensure Amharic is always the default for waiting approval page
if (!isset($_GET['lang'])) {
    setLanguage('am'); // Force Amharic default
}

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'am'])) {
    setLanguage($_GET['lang']);
    // Redirect to remove lang parameter from URL
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    if (isset($_GET['email'])) {
        $redirect_url .= '?email=' . urlencode($_GET['email']);
    }
    header('Location: ' . $redirect_url);
    exit;
}

// Check if user has a pending registration
$user_email = isset($_SESSION['pending_email']) ? $_SESSION['pending_email'] : null;
$user_name = isset($_SESSION['pending_name']) ? $_SESSION['pending_name'] : null;

// If no pending registration in session, check if we have email in URL (from registration redirect)
if (!$user_email && isset($_GET['email'])) {
    $user_email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
}

// If still no email, redirect to registration
if (!$user_email) {
    header('Location: login.php?msg=' . urlencode(t('waiting_approval.pending_message')));
    exit;
}

// Check user status in database
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.member_id, m.first_name, m.last_name, m.email, 
               m.is_approved, m.is_active, m.email_verified, m.created_at
        FROM members m
        WHERE m.email = ?
    ");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: login.php?msg=' . urlencode(t('waiting_approval.pending_message')));
        exit;
    }
    
    // If user is already approved, redirect to login
    if ($user['is_approved'] == 1) {
        unset($_SESSION['pending_email'], $_SESSION['pending_name']);
        header('Location: login.php?msg=' . urlencode(t('waiting_approval.account_approved_message')));
        exit;
    }
    
    // If user is declined (inactive), show message
    $is_declined = ($user['is_active'] == 0);
    
} catch (Exception $e) {
    error_log("Waiting approval page error: " . $e->getMessage());
    header('Location: login.php?msg=' . urlencode(t('errors.something_went_wrong')));
    exit;
}

$user_name = $user['first_name'] . ' ' . $user['last_name'];
$registration_date = new DateTime($user['created_at']);
$waiting_time = (new DateTime())->diff($registration_date);
$waiting_hours = $waiting_time->days * 24 + $waiting_time->h;

// Get current language for HTML attributes
$current_lang = getCurrentLanguage();
$current_lang_name = ($current_lang === 'am') ? 'አማርኛ' : 'English';
$opposite_lang = ($current_lang === 'am') ? 'en' : 'am';
$opposite_lang_name = ($current_lang === 'am') ? 'English' : 'አማርኛ';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('waiting_approval.page_title'); ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">
    
    <style>
        /* Beautiful Color Palette - Same as login.php */
        :root {
            --cream: #F1ECE2;
            --dark-purple: #4D4052;
            --darker-purple: #301934;
            --gold: #DAA520;
            --light-gold: #CDAF56;
            --brown: #5D4225;
            --white: #FFFFFF;
            --success: #28a745;
            --error: #dc3545;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-lg: 0 25px 50px -12px rgba(48, 25, 52, 0.25);
            --shadow-xl: 0 35px 60px -15px rgba(48, 25, 52, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--darker-purple) 0%, var(--dark-purple) 40%, rgba(241, 236, 226, 0.3) 70%, var(--cream) 100%);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Animated Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(218, 165, 32, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(77, 64, 82, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(241, 236, 226, 0.2) 0%, transparent 50%);
            filter: blur(80px);
            animation: float 25s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-15px) rotate(120deg); }
            66% { transform: translateY(-10px) rotate(240deg); }
        }
        
        /* Language Switcher */
        .language-switcher {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 25px rgba(48, 25, 52, 0.15);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--darker-purple);
            font-weight: 600;
            font-size: 14px;
        }
        
        .language-switcher:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(48, 25, 52, 0.25);
            text-decoration: none;
            color: var(--darker-purple);
        }
        
        .language-switcher i {
            font-size: 16px;
            color: var(--gold);
        }
        
        /* Main Container */
        .waiting-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        
        .waiting-header {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .waiting-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle, rgba(255, 255, 255, 0.2) 1px, transparent 1px),
                radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px, 60px 60px;
            background-position: 0 0, 15px 15px;
            opacity: 0.6;
            animation: patternMove 20s linear infinite;
        }
        
        @keyframes patternMove {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(30px) translateY(30px); }
        }
        
        .status-icon {
            font-size: 64px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .status-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-subtitle {
            font-size: 18px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .waiting-content {
            padding: 50px 40px;
            text-align: center;
        }
        
        .user-greeting {
            font-size: 28px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 24px;
        }
        
        .waiting-message {
            font-size: 18px;
            color: var(--darker-purple);
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }
        
        .status-details {
            background: linear-gradient(135deg, var(--cream) 0%, rgba(255, 255, 255, 0.8) 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 4px solid var(--gold);
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(77, 64, 82, 0.1);
        }
        
        .detail-label {
            font-size: 12px;
            color: var(--dark-purple);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .detail-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--darker-purple);
        }
        
        .approval-time {
            text-align: center;
            color: var(--darker-purple);
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.9);
            padding: 16px 24px;
            border-radius: 12px;
            border: 1px solid rgba(218, 165, 32, 0.2);
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(77, 64, 82, 0.08);
        }
        
        .approval-time i {
            color: var(--gold);
            font-size: 16px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--dark-purple) 0%, var(--darker-purple) 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(77, 64, 82, 0.3);
            margin-top: 30px;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(77, 64, 82, 0.4);
            text-decoration: none;
            color: white;
        }
        
        .back-button:active {
            transform: translateY(0);
        }
        
        /* Declined Status */
        .declined .waiting-header {
            background: linear-gradient(135deg, var(--error) 0%, #c82333 100%);
        }
        
        .declined-message {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid var(--error);
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            color: var(--error);
            font-weight: 600;
            font-size: 16px;
            line-height: 1.6;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.15);
            text-align: center;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .language-switcher {
                top: 20px;
                right: 20px;
                padding: 10px 16px;
                font-size: 13px;
            }
            
            .waiting-header {
                padding: 40px 30px;
            }
            
            .waiting-content {
                padding: 40px 30px;
            }
            
            .status-title {
                font-size: 24px;
            }
            
            .user-greeting {
                font-size: 24px;
            }
            
            .waiting-message {
                font-size: 16px;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .detail-item {
                padding: 16px;
            }
            
            .approval-time {
                font-size: 14px;
                padding: 14px 20px;
            }
            
            .waiting-message {
                font-size: 16px;
            }
            
            .declined-message {
                font-size: 15px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <a href="?lang=<?php echo $opposite_lang; ?><?php echo isset($_GET['email']) ? '&email=' . urlencode($_GET['email']) : ''; ?>" class="language-switcher">
        <i class="fas fa-globe"></i>
        <span><?php echo $opposite_lang_name; ?></span>
    </a>

    <div class="waiting-container <?= $is_declined ? 'declined' : '' ?>">
        <div class="waiting-header">
            <div class="status-icon">
                <?= $is_declined ? '❌' : '⏳' ?>
            </div>
            <h1 class="status-title">
                <?= $is_declined ? t('waiting_approval.declined_title') : t('waiting_approval.waiting_title') ?>
            </h1>
            <p class="status-subtitle">
                <?= $is_declined ? t('waiting_approval.declined_subtitle') : t('waiting_approval.waiting_subtitle') ?>
            </p>
        </div>
        
        <div class="waiting-content">
            <h2 class="user-greeting"><?= t('waiting_approval.personal_greeting', ['name' => htmlspecialchars($user['first_name'])]) ?> 👋</h2>
            
            <p class="waiting-message">
                <?php if ($is_declined): ?>
                    <?= t('waiting_approval.declined_message') ?>
                <?php else: ?>
                    <?= t('waiting_approval.detailed_message') ?>
                <?php endif; ?>
            </p>
            
            <div class="status-details">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label"><?= t('waiting_approval.member_id') ?></div>
                        <div class="detail-value"><?= htmlspecialchars($user['member_id']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><?= t('waiting_approval.registered') ?></div>
                        <div class="detail-value"><?= $registration_date->format('M j, Y') ?></div>
                    </div>
                </div>
                
                <?php if (!$is_declined): ?>
                <p class="approval-time">
                    <i class="fas fa-clock"></i> <?= t('waiting_approval.typical_approval_time') ?>
                </p>
                <?php endif; ?>
            </div>
            
            <?php if ($is_declined): ?>
            <div class="declined-message">
                <strong><?= t('waiting_approval.contact_support') ?></strong><br>
                <?= t('waiting_approval.support_message') ?>
            </div>
            <?php endif; ?>
            
            <a href="login.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <?= t('waiting_approval.back_to_login') ?>
            </a>
        </div>
    </div>

    <script>
        // Auto-refresh page every 5 minutes to check for approval status changes
        <?php if (!$is_declined): ?>
        setInterval(() => {
            fetch('api/check-approval-status.php?email=<?= urlencode($user_email) ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.approved) {
                        window.location.href = 'login.php?msg=' + encodeURIComponent('<?= t('waiting_approval.account_approved_message') ?>');
                    }
                })
                .catch(error => {
                    console.log('Status check failed:', error);
                });
        }, 300000); // 5 minutes
        <?php endif; ?>
    </script>
</body>
</html>