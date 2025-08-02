<?php
/**
 * HabeshaEqub - ROBUST Welcome Page 
 * Mobile-first, professional onboarding experience
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    error_log("Welcome.php - Session check failed. user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . ", user_logged_in: " . (isset($_SESSION['user_logged_in']) ? $_SESSION['user_logged_in'] : 'not set'));
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $db->prepare("
    SELECT id, member_id, first_name, last_name, language_preference, rules_agreed, is_approved 
    FROM members 
    WHERE id = ? AND is_active = 1
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    error_log("Welcome.php - User not found in database for user_id: $user_id");
    header('Location: login.php?msg=' . urlencode('User not found'));
    exit;
}

if (!$user['is_approved']) {
    error_log("Welcome.php - User not approved. user_id: $user_id, is_approved: {$user['is_approved']}");
    header('Location: login.php?msg=' . urlencode('Your account is pending approval'));
    exit;
}

// Debug: Log successful welcome page load
error_log("Welcome.php - Successfully loaded for user: {$user['first_name']} {$user['last_name']} (ID: $user_id), rules_agreed: {$user['rules_agreed']}");

// If user has already agreed to rules, redirect to dashboard
if ($user['rules_agreed'] == 1) {
    header('Location: dashboard.php');
    exit;
}

// Set language preference
$current_lang = $user['language_preference'] == 1 ? 'am' : 'en';

// Load translations properly
$lang_file = __DIR__ . '/../languages/' . $current_lang . '.json';
$translations = [];
if (file_exists($lang_file)) {
    $translations = json_decode(file_get_contents($lang_file), true) ?: [];
}

// Enhanced translation function with nested support
function t($key, $default = '') {
    global $translations;
    $keys = explode('.', $key);
    $value = $translations;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return is_string($value) ? $value : $default;
}

// Get equb rules in the correct language
$rules_stmt = $db->prepare("
    SELECT rule_number, rule_en, rule_am 
    FROM equb_rules 
    WHERE is_active = 1 
    ORDER BY rule_number ASC
");
$rules_stmt->execute();
$rules = $rules_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('welcome.page_title', 'Welcome to HabeshaEqub'); ?></title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- ROBUST PROFESSIONAL STYLES -->
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
            --shadow-elegant: 0 16px 32px rgba(48, 25, 52, 0.12);
            --shadow-card: 0 4px 16px rgba(48, 25, 52, 0.08);
            --shadow-button: 0 2px 8px rgba(218, 165, 32, 0.25);
            
            /* Typography Scale */
            --text-xs: 0.75rem;    /* 12px */
            --text-sm: 0.875rem;   /* 14px */
            --text-base: 1rem;     /* 16px */
            --text-lg: 1.125rem;   /* 18px */
            --text-xl: 1.25rem;    /* 20px */
            --text-2xl: 1.5rem;    /* 24px */
            --text-3xl: 1.875rem;  /* 30px */
            
            /* Spacing Scale */
            --space-1: 0.25rem;    /* 4px */
            --space-2: 0.5rem;     /* 8px */
            --space-3: 0.75rem;    /* 12px */
            --space-4: 1rem;       /* 16px */
            --space-5: 1.25rem;    /* 20px */
            --space-6: 1.5rem;     /* 24px */
            --space-8: 2rem;       /* 32px */
            --space-10: 2.5rem;    /* 40px */
            --space-12: 3rem;      /* 48px */
            --space-16: 4rem;      /* 64px */
            
            /* Border Radius */
            --radius-sm: 0.375rem; /* 6px */
            --radius-md: 0.5rem;   /* 8px */
            --radius-lg: 0.75rem;  /* 12px */
            --radius-xl: 1rem;     /* 16px */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--color-cream);
            color: var(--color-navy);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* MOBILE-FIRST CONTAINER SYSTEM */
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, var(--color-cream) 0%, rgba(241, 236, 226, 0.8) 100%);
        }

        .welcome-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-4);
        }

        .welcome-card {
            width: 100%;
            max-width: 28rem; /* 448px */
            background: rgba(255, 255, 255, 0.98);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-elegant);
            border: 1px solid rgba(218, 165, 32, 0.15);
            overflow: hidden;
        }

        /* HEADER SECTION */
        .header {
            background: var(--gradient-primary);
            padding: var(--space-8) var(--space-6);
            text-align: center;
            color: white;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 3.5rem; /* 56px */
            height: 3.5rem;
            background: var(--gradient-secondary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-xl);
            color: white;
            margin-bottom: var(--space-4);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .welcome-title {
            font-size: var(--text-3xl);
            font-weight: 700;
            margin-bottom: var(--space-2);
            letter-spacing: -0.025em;
        }

        .welcome-subtitle {
            font-size: var(--text-sm);
            opacity: 0.9;
            font-weight: 400;
            margin-bottom: var(--space-4);
        }

        .user-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-lg);
            padding: var(--space-2) var(--space-4);
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--text-sm);
            font-weight: 500;
        }

        /* PROGRESS INDICATOR */
        .progress {
            background: rgba(248, 249, 250, 0.95);
            padding: var(--space-5) var(--space-6);
            border-bottom: 1px solid rgba(218, 165, 32, 0.1);
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-8);
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translateX(-50%) translateY(-50%);
            width: 4rem;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .progress-steps.step-2::before {
            background: var(--color-gold);
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-2);
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 2rem; /* 32px */
            height: 2rem;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-xs);
            font-weight: 700;
            color: #6b7280;
            transition: all 0.2s ease;
        }

        .progress-step.active .step-circle {
            background: var(--gradient-secondary);
            color: white;
            box-shadow: var(--shadow-button);
        }

        .progress-step.completed .step-circle {
            background: var(--gradient-primary);
            color: white;
        }

        .step-label {
            font-size: var(--text-xs);
            font-weight: 600;
            color: var(--color-brown);
            text-align: center;
            max-width: 4rem;
            line-height: 1.3;
        }

        /* CONTENT AREA */
        .content {
            padding: var(--space-8) var(--space-6);
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(var(--space-4));
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }

        .step-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--color-navy);
            margin-bottom: var(--space-2);
            line-height: 1.3;
        }

        .step-description {
            font-size: var(--text-sm);
            color: var(--color-brown);
            opacity: 0.8;
            line-height: 1.5;
        }

        /* LANGUAGE SELECTION */
        .language-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-3);
            margin: var(--space-6) 0;
        }

        .language-option {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(218, 165, 32, 0.2);
            border-radius: var(--radius-lg);
            padding: var(--space-5);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-4);
            position: relative;
            overflow: hidden;
        }

        .language-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(218, 165, 32, 0.05), transparent);
            transition: left 0.5s ease;
        }

        .language-option:hover::before {
            left: 100%;
        }

        .language-option:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-card);
            border-color: var(--color-gold);
        }

        .language-option.selected {
            border-color: var(--color-gold);
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.08) 0%, rgba(205, 175, 86, 0.08) 100%);
            box-shadow: var(--shadow-card);
        }

        .language-flag {
            font-size: var(--text-3xl);
            line-height: 1;
        }

        .language-info {
            text-align: left;
        }

        .language-name {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--color-navy);
            line-height: 1.2;
        }

        .language-code {
            font-size: var(--text-xs);
            color: var(--color-brown);
            opacity: 0.7;
            font-weight: 500;
        }

        /* RULES SECTION */
        .rules-container {
            background: rgba(248, 249, 250, 0.8);
            border: 1px solid rgba(218, 165, 32, 0.15);
            border-radius: var(--radius-lg);
            padding: var(--space-5);
            margin: var(--space-5) 0;
            max-height: 20rem; /* 320px */
            overflow-y: auto;
        }

        .rules-container::-webkit-scrollbar {
            width: 4px;
        }

        .rules-container::-webkit-scrollbar-track {
            background: rgba(218, 165, 32, 0.1);
            border-radius: 2px;
        }

        .rules-container::-webkit-scrollbar-thumb {
            background: var(--color-gold);
            border-radius: 2px;
        }

        .rule-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--radius-md);
            padding: var(--space-4);
            margin-bottom: var(--space-3);
            border-left: 3px solid var(--color-gold);
            box-shadow: 0 1px 3px rgba(48, 25, 52, 0.05);
        }

        .rule-item:last-child {
            margin-bottom: 0;
        }

        .rule-header {
            font-size: var(--text-xs);
            font-weight: 700;
            color: var(--color-gold);
            margin-bottom: var(--space-2);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .rule-text {
            font-size: var(--text-sm);
            color: var(--color-navy);
            line-height: 1.5;
        }

        /* AGREEMENT SECTION */
        .agreement {
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.06) 0%, rgba(205, 175, 86, 0.06) 100%);
            border: 2px solid rgba(218, 165, 32, 0.2);
            border-radius: var(--radius-lg);
            padding: var(--space-5);
            margin: var(--space-6) 0;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: var(--space-3);
            cursor: pointer;
        }

        .checkbox {
            width: 1.25rem; /* 20px */
            height: 1.25rem;
            flex-shrink: 0;
            margin-top: 2px;
            accent-color: var(--color-gold);
            cursor: pointer;
        }

        .checkbox-label {
            font-size: var(--text-sm);
            color: var(--color-navy);
            font-weight: 500;
            line-height: 1.5;
            cursor: pointer;
        }

        /* ACTIONS */
        .actions {
            display: flex;
            gap: var(--space-3);
            margin-top: var(--space-8);
        }

        .btn {
            flex: 1;
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            transition: all 0.2s ease;
            text-decoration: none;
            line-height: 1.5;
        }

        .btn-primary {
            background: var(--gradient-secondary);
            color: white;
            box-shadow: var(--shadow-button);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(218, 165, 32, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: var(--color-dark-purple);
            border: 1px solid rgba(77, 64, 82, 0.2);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary:hover {
            background: rgba(77, 64, 82, 0.05);
            border-color: rgba(77, 64, 82, 0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-icon {
            font-size: var(--text-xs);
        }

        /* LOADING STATE */
        .loading {
            display: none;
            text-align: center;
            padding: var(--space-12) var(--space-6);
        }

        .loading.active {
            display: block;
        }

        .spinner {
            width: 2.5rem; /* 40px */
            height: 2.5rem;
            border: 3px solid rgba(218, 165, 32, 0.2);
            border-top: 3px solid var(--color-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto var(--space-4);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: var(--text-sm);
            color: var(--color-brown);
            font-weight: 500;
        }

        /* TABLET STYLES */
        @media (min-width: 768px) {
            .welcome-wrapper {
                padding: var(--space-8);
            }
            
            .welcome-card {
                max-width: 32rem; /* 512px */
            }
            
            .header {
                padding: var(--space-10) var(--space-8);
            }
            
            .content {
                padding: var(--space-10) var(--space-8);
            }
            
            .language-grid {
                grid-template-columns: 1fr 1fr;
                gap: var(--space-4);
            }
            
            .language-option {
                flex-direction: column;
                text-align: center;
                padding: var(--space-6);
            }
            
            .language-info {
                text-align: center;
            }
        }

        /* DESKTOP STYLES */
        @media (min-width: 1024px) {
            .welcome-card {
                max-width: 36rem; /* 576px */
            }
            
            .step-title {
                font-size: var(--text-3xl);
            }
            
            .rules-container {
                max-height: 24rem; /* 384px */
            }
        }

        /* HIGH DPI SCREENS */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .logo {
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            }
        }

        /* FOCUS STATES FOR ACCESSIBILITY */
        .language-option:focus-visible,
        .btn:focus-visible,
        .checkbox:focus-visible {
            outline: 2px solid var(--color-gold);
            outline-offset: 2px;
        }

        /* REDUCED MOTION */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="welcome-wrapper">
            <div class="welcome-card">
                <!-- Header -->
                <header class="header">
                    <div class="header-content">
                        <div class="logo">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h1 class="welcome-title"><?php echo t('welcome.title', 'Welcome!'); ?></h1>
                        <p class="welcome-subtitle"><?php echo t('welcome.subtitle', 'Let\'s get you started with your equb journey'); ?></p>
                        <div class="user-badge">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                        </div>
                    </div>
                </header>

                <!-- Progress -->
                <div class="progress">
                    <div class="progress-steps" id="progressSteps">
                        <div class="progress-step active" id="progressStep1">
                            <div class="step-circle">1</div>
                            <div class="step-label"><?php echo t('welcome.language_selection.title', 'Language'); ?></div>
                        </div>
                        <div class="progress-step" id="progressStep2">
                            <div class="step-circle">2</div>
                            <div class="step-label"><?php echo t('welcome.rules_agreement.title', 'Rules'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <main class="content">
                    <!-- Step 1: Language Selection -->
                    <div class="step active" id="step1">
                        <div class="step-header">
                            <h2 class="step-title">
                                <i class="fas fa-globe"></i> <?php echo t('welcome.language_selection.title', 'Choose Your Language'); ?>
                            </h2>
                            <p class="step-description"><?php echo t('welcome.language_selection.description', 'Please select your preferred language for the system'); ?></p>
                        </div>

                        <div class="language-grid">
                            <div class="language-option <?php echo $current_lang === 'en' ? 'selected' : ''; ?>" data-lang="en" tabindex="0">
                                <span class="language-flag">🇺🇸</span>
                                <div class="language-info">
                                    <div class="language-name">English</div>
                                    <div class="language-code">EN</div>
                                </div>
                            </div>
                            <div class="language-option <?php echo $current_lang === 'am' ? 'selected' : ''; ?>" data-lang="am" tabindex="0">
                                <span class="language-flag">🇪🇹</span>
                                <div class="language-info">
                                    <div class="language-name">አማርኛ</div>
                                    <div class="language-code">AM</div>
                                </div>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="button" class="btn btn-primary" onclick="proceedToRules()" id="continueBtn">
                                <?php echo t('welcome.continue', 'Continue'); ?>
                                <i class="fas fa-arrow-right btn-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Rules Agreement -->
                    <div class="step" id="step2">
                        <div class="step-header">
                            <h2 class="step-title">
                                <i class="fas fa-file-contract"></i> <?php echo t('welcome.rules_agreement.title', 'Equb Rules & Agreement'); ?>
                            </h2>
                            <p class="step-description"><?php echo t('welcome.rules_agreement.description', 'Please read and agree to the following equb rules to continue'); ?></p>
                        </div>

                        <div class="rules-container">
                            <?php foreach ($rules as $rule): ?>
                            <div class="rule-item">
                                <div class="rule-header">
                                    <?php echo ($current_lang === 'am' ? 'ህግ' : 'Rule'); ?> <?php echo $rule['rule_number']; ?>
                                </div>
                                <div class="rule-text">
                                    <?php echo htmlspecialchars($current_lang === 'am' ? $rule['rule_am'] : $rule['rule_en']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="agreement">
                            <label class="checkbox-wrapper" for="agreeRules">
                                <input type="checkbox" id="agreeRules" class="checkbox">
                                <span class="checkbox-label">
                                    <?php echo t('welcome.rules_agreement.agreement_text', 'I have read and understand all the rules. I agree to follow these rules.'); ?>
                                </span>
                            </label>
                        </div>

                        <div class="actions">
                            <button type="button" class="btn btn-secondary" onclick="goBackToLanguage()">
                                <i class="fas fa-arrow-left btn-icon"></i>
                                <?php echo t('welcome.back', 'Back'); ?>
                            </button>
                            <button type="button" class="btn btn-primary" onclick="agreeToRules()" id="agreeBtn" disabled>
                                <i class="fas fa-check btn-icon"></i>
                                <?php echo t('welcome.rules_agreement.agree_button', 'I Agree'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p class="loading-text">
                            <?php echo $current_lang === 'am' ? 'እባኮዎን ይጠብቁ...' : 'Please wait...'; ?>
                        </p>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        let selectedLanguage = '<?php echo $current_lang; ?>';
        let languageChanged = false;

        // Language selection
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', handleLanguageSelect);
            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleLanguageSelect.call(option);
                }
            });
        });

        function handleLanguageSelect() {
            // Remove previous selection
            document.querySelectorAll('.language-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selection to clicked option
            this.classList.add('selected');
            const newLang = this.dataset.lang;
            
            if (newLang !== selectedLanguage) {
                selectedLanguage = newLang;
                languageChanged = true;
            }
        }

        // Rules agreement checkbox
        document.getElementById('agreeRules').addEventListener('change', function() {
            document.getElementById('agreeBtn').disabled = !this.checked;
        });

        function proceedToRules() {
            if (languageChanged) {
                updateLanguage(selectedLanguage);
            } else {
                showStep2();
            }
        }

        function goBackToLanguage() {
            showStep1();
        }

        function updateLanguage(language) {
            hideAllSteps();
            showLoading();

            fetch('api/welcome-simple.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_language',
                    language: language
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating language');
                    showStep1();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
                showStep1();
            });
        }

        function showStep1() {
            hideAllSteps();
            document.getElementById('step1').classList.add('active');
            updateProgress(1);
        }

        function showStep2() {
            hideAllSteps();
            document.getElementById('step2').classList.add('active');
            updateProgress(2);
        }

        function showLoading() {
            hideAllSteps();
            document.getElementById('loading').classList.add('active');
        }

        function hideAllSteps() {
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.remove('active');
            document.getElementById('loading').classList.remove('active');
        }

        function updateProgress(step) {
            const progressSteps = document.getElementById('progressSteps');
            
            // Reset classes
            document.querySelectorAll('.progress-step').forEach(stepEl => {
                stepEl.classList.remove('active', 'completed');
            });

            // Update step classes
            for (let i = 1; i <= 2; i++) {
                const stepElement = document.getElementById('progressStep' + i);
                if (i < step) {
                    stepElement.classList.add('completed');
                } else if (i === step) {
                    stepElement.classList.add('active');
                }
            }

            // Update progress line
            if (step === 2) {
                progressSteps.classList.add('step-2');
            } else {
                progressSteps.classList.remove('step-2');
            }
        }

        function agreeToRules() {
            if (!document.getElementById('agreeRules').checked) {
                const message = '<?php echo $current_lang === "am" ? "ህጎቹን ለመቀበል ይስማሙ" : "You must agree to the rules to continue"; ?>';
                alert(message);
                return;
            }

            hideAllSteps();
            showLoading();

            fetch('api/welcome-simple.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'agree_rules'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 800);
                } else {
                    alert(data.message || 'Error saving agreement');
                    showStep2();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
                showStep2();
            });
        }
    </script>
</body>
</html> 