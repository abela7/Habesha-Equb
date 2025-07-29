<?php
/**
 * HabeshaEqub - AMAZING Welcome Page 
 * First-time user onboarding with stunning design
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
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
    header('Location: login.php?msg=' . urlencode('User not found'));
    exit;
}

if (!$user['is_approved']) {
    header('Location: login.php?msg=' . urlencode('Your account is pending approval'));
    exit;
}

// If user has already agreed to rules, redirect to dashboard
if ($user['rules_agreed'] == 1) {
    header('Location: dashboard.php');
    exit;
}

// Get equb rules
$rules_stmt = $db->prepare("
    SELECT rule_number, rule_en, rule_am 
    FROM equb_rules 
    WHERE is_active = 1 
    ORDER BY rule_number ASC
");
$rules_stmt->execute();
$rules = $rules_stmt->fetchAll(PDO::FETCH_ASSOC);

// Set initial language preference
$current_lang = $user['language_preference'] == 1 ? 'am' : 'en';

// Include translations - simple approach
$lang_file = __DIR__ . '/../languages/' . $current_lang . '.json';
$translations = [];
if (file_exists($lang_file)) {
    $translations = json_decode(file_get_contents($lang_file), true) ?: [];
}

// Simple translation function
function t($key, $default = '') {
    global $translations;
    return $translations[$key] ?? $default;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('welcome_title', 'Welcome'); ?> - HabeshaEqub</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            --gradient-welcome: linear-gradient(135deg, var(--color-cream) 0%, rgba(241, 236, 226, 0.8) 100%);
            --shadow-elegant: 0 20px 40px rgba(48, 25, 52, 0.1);
            --shadow-card: 0 8px 32px rgba(48, 25, 52, 0.08);
            --shadow-button: 0 4px 15px rgba(218, 165, 32, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--gradient-welcome);
            color: var(--color-navy);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(218, 165, 32, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(77, 64, 82, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(48, 25, 52, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .welcome-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: var(--shadow-elegant);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            border: 1px solid rgba(218, 165, 32, 0.2);
            position: relative;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-secondary);
        }

        .welcome-header {
            background: var(--gradient-primary);
            color: white;
            padding: 40px 40px 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translateX(-50px) translateY(-50px); }
            100% { transform: translateX(-30px) translateY(-30px); }
        }

        .welcome-header * {
            position: relative;
            z-index: 1;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: var(--gradient-secondary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 15px;
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }

        @keyframes pulse-glow {
            0% { box-shadow: 0 0 20px rgba(218, 165, 32, 0.5); }
            100% { box-shadow: 0 0 40px rgba(218, 165, 32, 0.8); }
        }

        .welcome-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #fff, var(--color-light-cream));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .user-greeting {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 15px 25px;
            margin-top: 20px;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .progress-container {
            padding: 0 40px;
            margin-top: -25px;
            position: relative;
            z-index: 2;
        }

        .progress-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(218, 165, 32, 0.2);
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 60%;
            right: -40%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }

        .progress-step.active:not(:last-child)::after,
        .progress-step.completed:not(:last-child)::after {
            background: var(--gradient-secondary);
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .progress-step.active .step-icon {
            background: var(--gradient-secondary);
            color: white;
            animation: pulse 1s ease-in-out infinite alternate;
        }

        .progress-step.completed .step-icon {
            background: var(--gradient-primary);
            color: white;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .step-label {
            font-size: 0.9rem;
            color: var(--color-brown);
            font-weight: 600;
            text-align: center;
        }

        .step-content {
            padding: 40px;
        }

        .step {
            display: none;
            animation: fadeInUp 0.6s ease-out;
        }

        .step.active {
            display: block;
        }

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

        .step-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .step-title {
            font-size: 2rem;
            color: var(--color-navy);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .step-description {
            color: var(--color-brown);
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .language-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 35px 0;
        }

        .language-option {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(218, 165, 32, 0.2);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
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
            background: var(--gradient-secondary);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 0;
        }

        .language-option:hover::before {
            left: 0;
            opacity: 0.1;
        }

        .language-option:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-card);
            border-color: var(--color-gold);
        }

        .language-option.selected {
            border-color: var(--color-gold);
            background: var(--gradient-secondary);
            color: white;
            transform: translateY(-5px);
            box-shadow: var(--shadow-button);
        }

        .language-option * {
            position: relative;
            z-index: 1;
        }

        .language-flag {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .language-name {
            font-weight: 700;
            font-size: 1.3rem;
        }

        .rules-container {
            background: rgba(248, 249, 250, 0.8);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            max-height: 450px;
            overflow-y: auto;
            border: 1px solid rgba(218, 165, 32, 0.2);
        }

        .rules-container::-webkit-scrollbar {
            width: 8px;
        }

        .rules-container::-webkit-scrollbar-track {
            background: rgba(218, 165, 32, 0.1);
            border-radius: 4px;
        }

        .rules-container::-webkit-scrollbar-thumb {
            background: var(--gradient-secondary);
            border-radius: 4px;
        }

        .rule-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--color-gold);
            box-shadow: 0 2px 10px rgba(48, 25, 52, 0.05);
            transition: all 0.3s ease;
        }

        .rule-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-card);
        }

        .rule-item:last-child {
            margin-bottom: 0;
        }

        .rule-number {
            font-weight: 700;
            color: var(--color-gold);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .rule-text {
            color: var(--color-navy);
            line-height: 1.6;
            font-size: 1rem;
        }

        .agreement-section {
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1) 0%, rgba(205, 175, 86, 0.1) 100%);
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }

        .agreement-checkbox {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            cursor: pointer;
            gap: 15px;
        }

        .agreement-checkbox input[type="checkbox"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
            accent-color: var(--color-gold);
        }

        .agreement-checkbox label {
            color: var(--color-navy);
            font-weight: 600;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .step-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            gap: 20px;
        }

        .btn {
            padding: 15px 35px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            min-width: 140px;
        }

        .btn-primary {
            background: var(--gradient-secondary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-button);
            color: white;
        }

        .btn-secondary {
            background: rgba(77, 64, 82, 0.1);
            color: var(--color-dark-purple);
            border: 2px solid rgba(77, 64, 82, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(77, 64, 82, 0.2);
            transform: translateY(-2px);
            color: var(--color-dark-purple);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 60px 40px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(218, 165, 32, 0.2);
            border-top: 4px solid var(--color-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 25px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            color: var(--color-brown);
            font-size: 1.2rem;
            font-weight: 600;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .welcome-container {
                padding: 15px;
            }

            .welcome-header {
                padding: 30px 25px 40px;
            }

            .welcome-title {
                font-size: 2.2rem;
            }

            .step-content {
                padding: 25px;
            }

            .progress-container {
                padding: 0 25px;
            }

            .language-selection {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .language-option {
                padding: 25px;
            }

            .step-actions {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                width: 100%;
                padding: 18px 30px;
            }

            .progress-steps {
                display: none;
            }
        }

        /* Success Animation */
        .success-icon {
            color: #28a745;
            animation: successPulse 0.6s ease-out;
        }

        @keyframes successPulse {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <!-- Header -->
            <div class="welcome-header">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <h1 class="welcome-title">
                    <i class="fas fa-sparkles"></i> <?php echo t('welcome', 'Welcome'); ?>!
                </h1>
                <p class="welcome-subtitle"><?php echo t('welcome_subtitle', 'Your journey with HabeshaEqub begins here'); ?></p>
                <div class="user-greeting">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-container">
                <div class="progress-card">
                    <div class="progress-steps">
                        <div class="progress-step active" id="progressStep1">
                            <div class="step-icon">
                                <i class="fas fa-language"></i>
                            </div>
                            <div class="step-label"><?php echo t('language_selection', 'Language'); ?></div>
                        </div>
                        <div class="progress-step" id="progressStep2">
                            <div class="step-icon">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div class="step-label"><?php echo t('rules_agreement', 'Rules'); ?></div>
                        </div>
                        <div class="progress-step" id="progressStep3">
                            <div class="step-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="step-label"><?php echo t('complete', 'Complete'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="step-content">
                <!-- Step 1: Language Selection -->
                <div class="step active" id="step1">
                    <div class="step-header">
                        <h2 class="step-title">
                            <i class="fas fa-globe"></i> <?php echo t('choose_language', 'Choose Your Language'); ?>
                        </h2>
                        <p class="step-description"><?php echo t('language_description', 'Select your preferred language to personalize your experience'); ?></p>
                    </div>

                    <div class="language-selection">
                        <div class="language-option <?php echo $current_lang === 'en' ? 'selected' : ''; ?>" data-lang="en">
                            <span class="language-flag">🇺🇸</span>
                            <div class="language-name">English</div>
                        </div>
                        <div class="language-option <?php echo $current_lang === 'am' ? 'selected' : ''; ?>" data-lang="am">
                            <span class="language-flag">🇪🇹</span>
                            <div class="language-name">አማርኛ</div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <div></div> <!-- Spacer -->
                        <button class="btn btn-primary" onclick="proceedToRules()" id="langNextBtn">
                            <?php echo t('continue', 'Continue'); ?> <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Rules Agreement -->
                <div class="step" id="step2">
                    <div class="step-header">
                        <h2 class="step-title">
                            <i class="fas fa-scroll"></i> <?php echo t('equb_rules', 'Equb Rules & Regulations'); ?>
                        </h2>
                        <p class="step-description"><?php echo t('rules_description', 'Please read and accept the following rules to join our community'); ?></p>
                    </div>

                    <div class="rules-container">
                        <?php foreach ($rules as $rule): ?>
                        <div class="rule-item">
                            <div class="rule-number">
                                <i class="fas fa-bookmark"></i> <?php echo t('rule', 'Rule'); ?> <?php echo $rule['rule_number']; ?>
                            </div>
                            <div class="rule-text">
                                <?php echo htmlspecialchars($current_lang === 'am' ? $rule['rule_am'] : $rule['rule_en']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="agreement-section">
                        <div class="agreement-checkbox">
                            <input type="checkbox" id="agreeRules">
                            <label for="agreeRules">
                                <i class="fas fa-shield-check"></i> <?php echo t('agree_rules', 'I have read, understood, and agree to follow all the rules above'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button class="btn btn-secondary" onclick="goBackToLanguage()">
                            <i class="fas fa-arrow-left"></i> <?php echo t('back', 'Back'); ?>
                        </button>
                        <button class="btn btn-primary" onclick="agreeToRules()" id="agreeBtn" disabled>
                            <i class="fas fa-heart"></i> <?php echo t('agree_continue', 'ተስማምቻለሁ'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p class="loading-text"><?php echo t('processing', 'Processing your preferences...'); ?></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let selectedLanguage = '<?php echo $current_lang; ?>';

        // Language selection
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.language-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selection to clicked option
                this.classList.add('selected');
                selectedLanguage = this.dataset.lang;
            });
        });

        // Rules agreement checkbox
        document.getElementById('agreeRules').addEventListener('change', function() {
            document.getElementById('agreeBtn').disabled = !this.checked;
        });

        function proceedToRules() {
            // Update language preference if changed
            if (selectedLanguage !== '<?php echo $current_lang; ?>') {
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
                    // Reload page to show updated language
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
            // Reset all steps
            document.querySelectorAll('.progress-step').forEach(step => {
                step.classList.remove('active', 'completed');
            });

            // Update progress
            for (let i = 1; i <= 3; i++) {
                const stepElement = document.getElementById('progressStep' + i);
                if (i < step) {
                    stepElement.classList.add('completed');
                } else if (i === step) {
                    stepElement.classList.add('active');
                }
            }
        }

        function agreeToRules() {
            if (!document.getElementById('agreeRules').checked) {
                alert('<?php echo t('must_agree', 'You must agree to the rules to continue'); ?>');
                return;
            }

            hideAllSteps();
            showLoading();
            updateProgress(3);

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
                    // Show success and redirect
                    document.getElementById('progressStep3').querySelector('.step-icon').innerHTML = '<i class="fas fa-check success-icon"></i>';
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
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

        // Add some interactive touches
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to rule items
            document.querySelectorAll('.rule-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(10px) scale(1.02)';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(5px) scale(1)';
                });
            });
        });
    </script>
</body>
</html> 