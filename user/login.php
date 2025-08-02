<?php
// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../languages/translator.php';

// Set default language to Amharic only if no language is set in session
if (!isset($_SESSION['app_language'])) {
    setLanguage('am');
}

// Force no caching
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Include auth guard functions (but skip auth check for login page)
define('SKIP_AUTH_CHECK', true);
require_once 'includes/auth_guard.php';

// Check for remembered device - auto login if valid
require_once 'includes/device_auth.php';
if (checkRememberedDevice()) {
    header('Location: dashboard.php');
    exit;
}

// Handle language switching
if (isset($_POST['language'])) {
    setLanguage($_POST['language']);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Handle logout message
$message = '';
$message_type = 'info';
if (isset($_GET['msg'])) {
    $message = sanitize_input($_GET['msg']);
    $message_type = 'info';
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('user_auth.page_title'); ?> - HabeshaEqub</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">
    
    <!-- 🚀 TRULY TOP-TIER 2024/2025 DESIGN - Modern Split-Screen Layout -->
    <style>
        /* Your Stunning Color Palette - Used Creatively! */
        :root {
            --cream: #F1ECE2;
            --dark-purple: #4D4052;
            --darker-purple: #301934;
            --gold: #DAA520;
            --light-gold: #CDAF56;
            --brown: #5D4225;
            --white: #FFFFFF;
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
        
        .auth-container {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
        }
        
                 /* Left Side - Creative Brand Section */
         .auth-brand {
             display: flex;
             flex-direction: column;
             justify-content: center;
             align-items: center;
             padding: 60px 40px;
             background: linear-gradient(135deg, var(--darker-purple) 0%, var(--dark-purple) 70%, rgba(77, 64, 82, 0.8) 100%);
             position: relative;
             overflow: hidden;
         }
        
                 .auth-brand::before {
             content: '';
             position: absolute;
             top: -50%;
             left: -50%;
             width: 200%;
             height: 200%;
             background: 
                 radial-gradient(circle, rgba(218, 165, 32, 0.3) 1px, transparent 1px),
                 radial-gradient(circle, rgba(241, 236, 226, 0.2) 1px, transparent 1px);
             background-size: 60px 60px, 120px 120px;
             background-position: 0 0, 30px 30px;
             opacity: 0.4;
             animation: patternMove 35s linear infinite;
         }
        
                 @keyframes patternMove {
             0% { transform: translateX(0) translateY(0); }
             50% { transform: translateX(30px) translateY(30px); }
             100% { transform: translateX(60px) translateY(60px); }
         }
        
        .brand-content {
            text-align: center;
            z-index: 2;
            position: relative;
        }
        
                 .brand-logo {
             width: 160px;
             height: 160px;
             object-fit: contain;
             margin-bottom: 30px;
             filter: drop-shadow(0 10px 25px rgba(0,0,0,0.3));
             animation: logoGlow 3s ease-in-out infinite alternate;
         }
        
        @keyframes logoGlow {
            0% { filter: drop-shadow(0 10px 25px rgba(0,0,0,0.3)); }
            100% { filter: drop-shadow(0 15px 35px rgba(218, 165, 32, 0.4)); }
        }
        
        .brand-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 50%, var(--cream) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            line-height: 1.1;
        }
        
        .brand-subtitle {
            font-size: 1.25rem;
            color: rgba(241, 236, 226, 0.9);
            font-weight: 300;
            letter-spacing: 0.5px;
            margin-bottom: 40px;
        }
        
                 .brand-features {
             display: flex;
             flex-direction: column;
             gap: 20px;
             align-items: center;
             width: 100%;
         }
        
                 .feature-item {
             display: flex;
             align-items: flex-start;
             gap: 15px;
             padding: 20px 25px;
             background: var(--glass);
             backdrop-filter: blur(10px);
             border: 1px solid var(--glass-border);
             border-radius: 16px;
             color: var(--cream);
             font-weight: 500;
             transition: all 0.3s ease;
             text-align: left;
             max-width: 400px;
             margin: 0 auto;
         }
        
        .feature-item:hover {
            transform: translateY(-2px);
            background: rgba(218, 165, 32, 0.2);
        }
        
                 .feature-icon {
             width: 40px;
             height: 40px;
             background: var(--gold);
             border-radius: 12px;
             display: flex;
             align-items: center;
             justify-content: center;
             color: var(--white);
             font-size: 16px;
             font-weight: bold;
             box-shadow: 0 4px 12px rgba(218, 165, 32, 0.3);
             flex-shrink: 0;
         }
        
                 /* Right Side - Glassmorphism Form */
         .auth-form-section {
             display: flex;
             flex-direction: column;
             justify-content: center;
             padding: 60px 40px;
             background: linear-gradient(135deg, rgba(241, 236, 226, 0.9) 0%, rgba(77, 64, 82, 0.05) 50%, rgba(255, 255, 255, 0.95) 100%);
             position: relative;
         }
        
                 .auth-card {
             background: rgba(255, 255, 255, 0.92);
             backdrop-filter: blur(25px);
             border: 1px solid rgba(77, 64, 82, 0.1);
             border-radius: 24px;
             padding: 40px;
             box-shadow: 0 25px 50px -12px rgba(48, 25, 52, 0.15);
             position: relative;
             overflow: hidden;
             max-width: 520px;
             width: 100%;
             margin: 0 auto;
         }
        
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold) 0%, var(--light-gold) 50%, var(--brown) 100%);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--darker-purple);
            margin-bottom: 8px;
        }
        
        .form-subtitle {
            color: var(--dark-purple);
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .page-toggle {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: linear-gradient(135deg, rgba(241, 236, 226, 0.3) 0%, rgba(218, 165, 32, 0.1) 100%);
            border-radius: 12px;
            border: 1px solid rgba(218, 165, 32, 0.2);
        }
        
        .toggle-text {
            font-size: 14px;
            color: var(--dark-purple);
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .toggle-link {
            color: var(--gold);
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .toggle-link:hover {
            color: var(--brown);
            transform: translateY(-1px);
        }
        
        .toggle-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            background: var(--gold);
            transition: all 0.3s ease;
        }
        
        .toggle-link:hover::after {
            width: 100%;
            left: 0;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
            animation: slideIn 0.4s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
                 .form-group {
             margin-bottom: 24px;
             position: relative;
         }
         
         .password-input-wrapper {
             position: relative;
         }
         
         .password-toggle {
             position: absolute;
             right: 16px;
             top: 50%;
             transform: translateY(-50%);
             background: none;
             border: none;
             color: var(--dark-purple);
             cursor: pointer;
             padding: 4px;
             border-radius: 4px;
             transition: all 0.2s ease;
             z-index: 2;
         }
         
         .password-toggle:hover {
             color: var(--gold);
             background: rgba(218, 165, 32, 0.1);
         }
         
         .form-control.has-toggle {
             padding-right: 50px;
         }
        
                 .form-label {
             display: flex;
             align-items: center;
             gap: 8px;
             font-weight: 600;
             font-size: 14px;
             color: var(--darker-purple);
             margin-bottom: 8px;
             letter-spacing: 0.3px;
         }
         
         .form-label i {
             color: var(--gold);
             font-size: 14px;
             width: 16px;
             text-align: center;
         }
        
        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid rgba(77, 64, 82, 0.1);
            border-radius: 12px;
            font-size: 16px;
            color: var(--darker-purple);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            font-family: inherit;
            -webkit-appearance: none;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 4px rgba(218, 165, 32, 0.1);
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: rgba(77, 64, 82, 0.5);
        }
        
        .form-control.error {
            border-color: #DC2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }
        
        .error-message {
            display: none;
            color: #DC2626;
            font-size: 13px;
            margin-top: 6px;
            font-weight: 500;
        }
        
        .error-message.show {
            display: block;
            animation: shake 0.3s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 30px;
            padding: 15px;
            background: rgba(241, 236, 226, 0.3);
            border-radius: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--gold);
            cursor: pointer;
        }
        
        .checkbox-group label {
            font-size: 14px;
            color: var(--dark-purple);
            cursor: pointer;
            font-weight: 500;
        }
        

        
        /* Terms and Privacy Policy Links */
        .terms-link {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid transparent;
            transition: all 0.3s ease;
            padding-bottom: 1px;
        }
        
        .terms-link:hover {
            color: var(--brown);
            border-bottom-color: var(--brown);
            text-shadow: 0 0 1px rgba(93, 66, 37, 0.3);
        }
        
        .btn-primary {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 50%, var(--brown) 100%);
            border: none;
            border-radius: 12px;
            color: var(--white);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            box-shadow: 0 8px 20px rgba(218, 165, 32, 0.3);
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(218, 165, 32, 0.4);
            background: linear-gradient(135deg, var(--brown) 0%, var(--gold) 50%, var(--light-gold) 100%);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            margin-left: -10px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(77, 64, 82, 0.1);
        }
        
        .auth-footer a {
            color: var(--dark-purple);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .auth-footer a:hover {
            color: var(--gold);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            display: none;
            border-width: 1px;
            border-style: solid;
            font-weight: 500;
        }
        
        .alert.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #F0FDF4 0%, #ECFDF5 100%);
            color: #059669;
            border-color: #BBF7D0;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEF1F1 100%);
            color: #DC2626;
            border-color: #FECACA;
        }
        
        .language-toggle {
            position: absolute;
            top: 30px;
            right: 30px;
            z-index: 10;
        }
        
        .language-btn {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 10px 16px;
            color: var(--white);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .language-btn:hover {
            background: rgba(218, 165, 32, 0.2);
            transform: translateY(-1px);
        }
        
                 .language-dropdown {
             position: absolute;
             top: calc(100% + 10px);
             right: 0;
             background: rgba(255, 255, 255, 0.95);
             backdrop-filter: blur(20px);
             border: 1px solid rgba(77, 64, 82, 0.2);
             border-radius: 8px;
             box-shadow: 0 25px 50px -12px rgba(48, 25, 52, 0.25);
             min-width: 140px;
             display: none;
             overflow: hidden;
         }
        
        .language-dropdown.show {
            display: block;
            animation: dropdownShow 0.2s ease-out;
        }
        
        @keyframes dropdownShow {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .language-option {
            display: block;
            width: 100%;
            padding: 12px 18px;
            border: none;
            background: none;
            text-align: left;
            color: var(--darker-purple);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .language-option:hover {
            background: rgba(218, 165, 32, 0.1);
        }
        
        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .auth-container {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }
            
            .auth-brand {
                padding: 40px 30px;
                min-height: auto;
            }
            
            .brand-title {
                font-size: 2.5rem;
            }
            
            .brand-features {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .auth-brand {
                padding: 30px 20px;
            }
            
            .auth-form-section {
                padding: 30px 20px;
            }
            
                         .auth-card {
                 padding: 30px 24px;
                 max-width: 480px;
             }
            
                         .brand-logo {
                 width: 130px;
                 height: 130px;
             }
            
            .brand-title {
                font-size: 2rem;
            }
            
            .form-title {
                font-size: 1.75rem;
            }
            
            .language-toggle {
                top: 20px;
                right: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .auth-container {
                min-height: 100vh;
            }
            
            .auth-brand {
                padding: 24px 16px;
            }
            
            .auth-form-section {
                padding: 24px 16px;
            }
            
                         .auth-card {
                 padding: 24px 20px;
                 border-radius: 16px;
                 max-width: 100%;
                 margin: 0 16px;
             }
            
                         .brand-logo {
                 width: 110px;
                 height: 110px;
             }
            
            .brand-title {
                font-size: 1.75rem;
            }
            
            .brand-subtitle {
                font-size: 1rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .feature-item {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Language Toggle -->
        <div class="language-toggle">
            <button class="language-btn" onclick="toggleLanguageDropdown()">
                <i class="fas fa-globe"></i>
                <span id="currentLang"><?php echo getCurrentLanguage() == 'en' ? 'EN' : 'አማ'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="language-dropdown" id="languageDropdown">
                <button class="language-option" onclick="changeLanguage('en')">English</button>
                <button class="language-option" onclick="changeLanguage('am')">አማርኛ</button>
            </div>
        </div>
        
        <!-- Left Side - Creative Brand Section -->
        <div class="auth-brand">
            <div class="brand-content">
                <img src="../Pictures/TransparentLogo.png" alt="HabeshaEqub Logo" class="brand-logo">
                <h1 class="brand-title"><?php echo t('user_auth.page_title'); ?></h1>
                <p class="brand-subtitle"><?php echo t('user_auth.page_subtitle'); ?></p>
                
                <div class="brand-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <span><?php echo t('user_auth.feature_transparency'); ?></span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span><?php echo t('user_auth.feature_tracking'); ?></span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <span><?php echo t('user_auth.feature_management'); ?></span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span><?php echo t('user_auth.feature_security'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Glassmorphism Form -->
        <div class="auth-form-section">
            <div class="auth-card">
                <!-- Alert Messages -->
                <div class="alert alert-success" id="successAlert"></div>
                <div class="alert alert-error" id="errorAlert"></div>
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Login Form (Default) -->
                <form class="auth-form active" id="loginForm">
                    <div class="form-header">
                        <h2 class="form-title"><?php echo t('user_auth.login_title'); ?></h2>
                        <p class="form-subtitle"><?php echo t('user_auth.login_subtitle'); ?></p>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label class="form-label" for="login_email">
                            <i class="fas fa-envelope"></i>
                            <?php echo t('user_auth.email'); ?>
                        </label>
                        <input 
                            type="email" 
                            id="login_email" 
                            name="email" 
                            class="form-control" 
                            placeholder="<?php echo t('user_auth.email_placeholder'); ?>"
                            autocomplete="email"
                            required
                        >
                        <div class="error-message" id="loginEmailError"></div>
                    </div>

                    <div class="form-group">
                        <div class="security-notice" style="background-color: rgba(218, 165, 32, 0.1); border: 1px solid rgba(218, 165, 32, 0.3); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 12px; color: #DAA520;">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <strong><?php echo t('user_auth.security_title'); ?></strong>
                                    <p style="margin: 4px 0 0 0; font-size: 14px; color: #4D4052; line-height: 1.4;">
                                        <?php echo t('user_auth.security_description'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <span class="btn-text"><?php echo t('user_auth.send_code_button'); ?></span>
                    </button>

                    <div class="page-toggle">
                        <span class="toggle-text"><?php echo t('user_auth.switch_to_register'); ?></span><br>
                        <a href="#" class="toggle-link" onclick="showRegister(event)"><?php echo t('user_auth.register_title'); ?></a>
                    </div>
                </form>

                <!-- Register Form (Hidden) -->
                <form class="auth-form" id="registerForm">
                    <div class="form-header">
                        <h2 class="form-title"><?php echo t('user_auth.register_title'); ?></h2>
                        <p class="form-subtitle"><?php echo t('user_auth.register_subtitle'); ?></p>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="register">

                    <div class="form-group">
                        <label class="form-label" for="register_first_name">
                            <i class="fas fa-user"></i>
                            <?php echo t('user_auth.first_name'); ?>
                        </label>
                        <input 
                            type="text" 
                            id="register_first_name" 
                            name="first_name" 
                            class="form-control" 
                            placeholder="<?php echo t('user_auth.first_name_placeholder'); ?>"
                            autocomplete="given-name"
                            required
                        >
                        <div class="error-message" id="registerFirstNameError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="register_last_name">
                            <i class="fas fa-user"></i>
                            <?php echo t('user_auth.last_name'); ?>
                        </label>
                        <input 
                            type="text" 
                            id="register_last_name" 
                            name="last_name" 
                            class="form-control" 
                            placeholder="<?php echo t('user_auth.last_name_placeholder'); ?>"
                            autocomplete="family-name"
                            required
                        >
                        <div class="error-message" id="registerLastNameError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="register_email">
                            <i class="fas fa-envelope"></i>
                            <?php echo t('user_auth.email'); ?>
                        </label>
                        <input 
                            type="email" 
                            id="register_email" 
                            name="email" 
                            class="form-control" 
                            placeholder="<?php echo t('user_auth.email_placeholder'); ?>"
                            autocomplete="email"
                            required
                        >
                        <div class="error-message" id="registerEmailError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="register_phone">
                            <i class="fas fa-phone"></i>
                            <?php echo t('user_auth.phone'); ?>
                        </label>
                        <input 
                            type="tel" 
                            id="register_phone" 
                            name="phone" 
                            class="form-control" 
                            placeholder="<?php echo t('user_auth.phone_placeholder'); ?>"
                            autocomplete="tel"
                            required
                        >
                        <div class="error-message" id="registerPhoneError"></div>
                    </div>



                    <div class="checkbox-group">
                        <input type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label for="agree_terms"><?php echo t('user_auth.terms_agree'); ?></label>
                    </div>

                    <button type="submit" class="btn-primary">
                        <span class="btn-text"><?php echo t('user_auth.register_button'); ?></span>
                    </button>

                    <div class="page-toggle">
                        <span class="toggle-text"><?php echo t('user_auth.switch_to_login'); ?></span><br>
                        <a href="#" class="toggle-link" onclick="showLogin(event)"><?php echo t('user_auth.login_title'); ?></a>
                    </div>
                </form>

                <!-- Footer Links -->
                <div class="auth-footer">
                    <!-- Admin login link removed for security -->
                </div>
            </div>
        </div>
    </div>

    <script>
        class UserAuthManager {
            constructor() {
                this.initializeEventListeners();
                this.initializeLanguageHandler();
            }

            initializeEventListeners() {
                // Form submissions
                document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLogin(e));
                document.getElementById('registerForm').addEventListener('submit', (e) => this.handleRegister(e));

                // Real-time validation
                document.querySelectorAll('.form-control').forEach(input => {
                    input.addEventListener('blur', (e) => this.validateField(e.target));
                    input.addEventListener('input', (e) => this.clearFieldError(e.target));
                });
            }

            initializeLanguageHandler() {
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.language-toggle')) {
                        document.getElementById('languageDropdown').classList.remove('show');
                    }
                });
            }

            showRegisterForm() {
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                document.getElementById('registerForm').classList.add('active');
                this.clearAllAlerts();
            }

            showLoginForm() {
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                document.getElementById('loginForm').classList.add('active');
                this.clearAllAlerts();
            }

            async handleLogin(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const submitBtn = form.querySelector('.btn-primary');

                if (!this.validateLoginForm(form)) return;

                this.setButtonLoading(submitBtn, true);
                this.clearAllAlerts();

                // Add action type for OTP request
                formData.set('action', 'request_otp');

                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        let errorText = `HTTP ${response.status}: ${response.statusText}`;
                        try {
                            const errorData = await response.text();
                            if (errorData.trim().startsWith('{')) {
                                const errorJson = JSON.parse(errorData);
                                errorText = errorJson.message || errorText;
                            } else {
                                errorText += ' - Server returned HTML error page (check server logs)';
                                console.log('Server HTML response:', errorData.substring(0, 500));
                            }
                        } catch (e) {}
                        throw new Error(errorText);
                    }
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showAlert('success', result.message || '<?php echo t('user_auth.otp_sent'); ?>');
                        // Redirect to OTP verification page
                        setTimeout(() => {
                            window.location.href = 'verify-otp.php?email=' + encodeURIComponent(formData.get('email'));
                        }, 1500);
                    } else {
                        this.showAlert('error', result.message || '<?php echo t('user_auth.otp_error'); ?>');
                        if (result.field_errors) {
                            this.displayFieldErrors(result.field_errors, 'login');
                        }
                        // Check if there's a redirect for pending approval
                        if (result.redirect) {
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 2000);
                        }
                    }
                } catch (error) {
                    console.error('OTP request error:', error);
                    let errorMessage = 'Unknown error occurred';
                    if (error.name === 'SyntaxError') {
                        errorMessage = 'Server returned invalid response (likely 500 error). Check server logs for PHP errors.';
                    } else if (error.message) {
                        errorMessage = error.message;
                    } else {
                        errorMessage = 'Network connection failed. Check your internet connection.';
                    }
                    this.showAlert('error', '❌ Login failed: ' + errorMessage);
                } finally {
                    this.setButtonLoading(submitBtn, false);
                }
            }

            async handleRegister(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const submitBtn = form.querySelector('.btn-primary');

                if (!this.validateRegisterForm(form)) return;

                // Add device fingerprint to form data
                const deviceFingerprint = generateDeviceFingerprint();
                formData.append('device_fingerprint', deviceFingerprint);

                this.setButtonLoading(submitBtn, true);
                this.clearAllAlerts();

                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showAlert('success', result.message || '<?php echo t('user_auth.register_success'); ?>');
                        form.reset();
                        setTimeout(() => {
                            if (result.redirect) {
                                window.location.href = result.redirect;
                            } else {
                                this.showLoginForm();
                            }
                        }, 2000);
                    } else {
                        this.showAlert('error', result.message || '<?php echo t('user_auth.register_error'); ?>');
                        if (result.field_errors) {
                            this.displayFieldErrors(result.field_errors, 'register');
                        }
                    }
                } catch (error) {
                    console.error('Registration error:', error);
                    this.showAlert('error', '<?php echo t('user_auth.network_error'); ?>');
                } finally {
                    this.setButtonLoading(submitBtn, false);
                }
            }

            validateLoginForm(form) {
                const email = form.querySelector('#login_email');
                let isValid = true;

                // Clear any previous errors
                this.clearFieldError(email);

                if (!email.value.trim()) {
                    this.showFieldError(email, '<?php echo t('user_auth.email_required'); ?>');
                    isValid = false;
                } else if (!this.validateEmail(email.value)) {
                    this.showFieldError(email, '<?php echo t('user_auth.email_invalid'); ?>');
                    isValid = false;
                }

                return isValid;
            }

            validateRegisterForm(form) {
                const firstName = form.querySelector('#register_first_name');
                const lastName = form.querySelector('#register_last_name');
                const email = form.querySelector('#register_email');
                const phone = form.querySelector('#register_phone');
                const agreeTerms = form.querySelector('#agree_terms');
                let isValid = true;

                if (firstName.value.trim().length < 2) {
                    this.showFieldError(firstName, '<?php echo t('user_auth.first_name_required'); ?>');
                    isValid = false;
                }

                if (lastName.value.trim().length < 2) {
                    this.showFieldError(lastName, '<?php echo t('user_auth.last_name_required'); ?>');
                    isValid = false;
                }

                if (!this.validateEmail(email.value)) {
                    this.showFieldError(email, '<?php echo t('user_auth.email_invalid'); ?>');
                    isValid = false;
                }

                if (!this.validatePhone(phone.value)) {
                    this.showFieldError(phone, '<?php echo t('user_auth.phone_invalid'); ?>');
                    isValid = false;
                }

                if (!agreeTerms.checked) {
                    this.showAlert('error', '<?php echo t('user_auth.terms_required'); ?>');
                    isValid = false;
                }

                return isValid;
            }

            validateField(field) {
                const value = field.value.trim();
                const fieldType = field.type;
                const fieldName = field.name;

                switch (fieldType) {
                    case 'email':
                        if (value && !this.validateEmail(value)) {
                            this.showFieldError(field, '<?php echo t('user_auth.email_invalid'); ?>');
                        } else {
                            this.clearFieldError(field);
                        }
                        break;
                    case 'tel':
                        if (value && !this.validatePhone(value)) {
                            this.showFieldError(field, '<?php echo t('user_auth.phone_invalid'); ?>');
                        } else {
                            this.clearFieldError(field);
                        }
                        break;
                    case 'password':
                        if (fieldName === 'confirm_password') {
                            const password = field.form.querySelector('[name="password"]');
                            if (value && value !== password.value) {
                                this.showFieldError(field, '<?php echo t('user_auth.password_mismatch'); ?>');
                            } else {
                                this.clearFieldError(field);
                            }
                        } else if (value && value.length < 6) {
                            this.showFieldError(field, '<?php echo t('user_auth.password_min_length'); ?>');
                        } else {
                            this.clearFieldError(field);
                        }
                        break;
                    default:
                        if (fieldName === 'full_name' && value && value.length < 2) {
                            this.showFieldError(field, '<?php echo t('user_auth.full_name_required'); ?>');
                        } else {
                            this.clearFieldError(field);
                        }
                }
            }

            validateEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            validatePhone(phone) {
                return /^[\+]?[0-9\s\-\(\)]{10,}$/.test(phone);
            }

            showFieldError(field, message) {
                field.classList.add('error');
                const errorEl = field.parentNode.querySelector('.error-message');
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.add('show');
                }
            }

            clearFieldError(field) {
                field.classList.remove('error');
                const errorEl = field.parentNode.querySelector('.error-message');
                if (errorEl) {
                    errorEl.classList.remove('show');
                }
            }

            displayFieldErrors(errors, prefix) {
                Object.keys(errors).forEach(field => {
                    const fieldEl = document.getElementById(`${prefix}_${field}`);
                    if (fieldEl) {
                        this.showFieldError(fieldEl, errors[field]);
                    }
                });
            }

            showAlert(type, message) {
                const alertEl = document.getElementById(type === 'success' ? 'successAlert' : 'errorAlert');
                alertEl.textContent = message;
                alertEl.classList.add('show');
                
                setTimeout(() => {
                    alertEl.classList.remove('show');
                }, 5000);
            }

            clearAllAlerts() {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.classList.remove('show');
                });
                document.querySelectorAll('.error-message').forEach(error => {
                    error.classList.remove('show');
                });
                document.querySelectorAll('.form-control').forEach(input => {
                    input.classList.remove('error');
                });
            }

            setButtonLoading(button, loading) {
                const text = button.querySelector('.btn-text');
                if (loading) {
                    button.disabled = true;
                    button.classList.add('btn-loading');
                    text.style.opacity = '0';
                } else {
                    button.disabled = false;
                    button.classList.remove('btn-loading');
                    text.style.opacity = '1';
                }
            }
        }

        // Form switching functions
        function showRegister(event) {
            event.preventDefault();
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            document.getElementById('registerForm').classList.add('active');
        }

        function showLogin(event) {
            event.preventDefault();
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            document.getElementById('loginForm').classList.add('active');
        }

        // Password toggle function
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Device fingerprinting functions
        function generateDeviceFingerprint() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillText('Device fingerprint', 2, 2);
            
            const fingerprint = [
                navigator.userAgent,
                navigator.language,
                screen.width + 'x' + screen.height,
                screen.colorDepth,
                new Date().getTimezoneOffset(),
                navigator.hardwareConcurrency || 'unknown',
                canvas.toDataURL()
            ].join('|');
            
            return 'dv_' + btoa(fingerprint).substring(0, 16);
        }

        // Language functions
        function toggleLanguageDropdown() {
            const dropdown = document.getElementById('languageDropdown');
            dropdown.classList.toggle('show');
        }

        async function changeLanguage(lang) {
            try {
                window.location.href = '../languages/switch_language.php?lang=' + lang + '&redirect=' + encodeURIComponent(window.location.pathname);
            } catch (error) {
                console.error('Language change error:', error);
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            new UserAuthManager();
        });
    </script>
</body>
</html> 