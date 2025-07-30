<?php
/**
 * HabeshaEqub - Professional Welcome Page 
 * First-time user onboarding with business-class design
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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Professional Styles -->
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
            --shadow-button: 0 4px 15px rgba(218, 165, 32, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--color-cream);
            color: var(--color-navy);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            line-height: 1.6;
        }

        .welcome-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--color-cream) 0%, rgba(241, 236, 226, 0.8) 100%);
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: var(--shadow-elegant);
            width: 100%;
            max-width: 700px;
            overflow: hidden;
            border: 1px solid rgba(218, 165, 32, 0.2);
        }

        .welcome-header {
            background: var(--gradient-primary);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .company-logo {
            width: 60px;
            height: 60px;
            background: var(--gradient-secondary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 20px;
        }

        .welcome-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 12px 20px;
            margin-top: 20px;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.95rem;
        }

        .progress-indicator {
            padding: 25px 30px;
            background: rgba(248, 249, 250, 0.8);
            border-bottom: 1px solid rgba(218, 165, 32, 0.2);
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--color-brown);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #666;
        }

        .progress-step.active .step-number {
            background: var(--gradient-secondary);
            color: white;
        }

        .progress-step.completed .step-number {
            background: var(--gradient-primary);
            color: white;
        }

        .step-content {
            padding: 40px 30px;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .step-title {
            font-size: 1.8rem;
            color: var(--color-navy);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .step-description {
            color: var(--color-brown);
            font-size: 1rem;
            opacity: 0.9;
        }

        .language-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }

        .language-option {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(218, 165, 32, 0.2);
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .language-option:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-card);
            border-color: var(--color-gold);
        }

        .language-option.selected {
            border-color: var(--color-gold);
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1) 0%, rgba(205, 175, 86, 0.1) 100%);
            transform: translateY(-3px);
            box-shadow: var(--shadow-card);
        }

        .language-flag {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }

        .language-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--color-navy);
        }

        .rules-section {
            margin: 25px 0;
        }

        .rules-container {
            background: rgba(248, 249, 250, 0.8);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid rgba(218, 165, 32, 0.2);
        }

        .rules-container::-webkit-scrollbar {
            width: 6px;
        }

        .rules-container::-webkit-scrollbar-track {
            background: rgba(218, 165, 32, 0.1);
            border-radius: 3px;
        }

        .rules-container::-webkit-scrollbar-thumb {
            background: var(--color-gold);
            border-radius: 3px;
        }

        .rule-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 15px;
            border-left: 4px solid var(--color-gold);
            box-shadow: 0 2px 8px rgba(48, 25, 52, 0.05);
        }

        .rule-item:last-child {
            margin-bottom: 0;
        }

        .rule-number {
            font-weight: 700;
            color: var(--color-gold);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .rule-text {
            color: var(--color-navy);
            line-height: 1.5;
            font-size: 0.95rem;
        }

        .agreement-section {
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.08) 0%, rgba(205, 175, 86, 0.08) 100%);
            border: 2px solid rgba(218, 165, 32, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }

        .agreement-checkbox {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px 0;
            cursor: pointer;
            gap: 12px;
        }

        .agreement-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--color-gold);
        }

        .agreement-checkbox label {
            color: var(--color-navy);
            font-weight: 500;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .step-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 35px;
            gap: 15px;
        }

        /* PROFESSIONAL BUTTON DESIGN */
        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            min-width: 120px;
            position: relative;
        }

        .btn-primary {
            background: var(--gradient-secondary);
            color: white;
            box-shadow: 0 2px 8px rgba(218, 165, 32, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(218, 165, 32, 0.3);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.8);
            color: var(--color-dark-purple);
            border: 2px solid rgba(77, 64, 82, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary:hover {
            background: rgba(77, 64, 82, 0.05);
            border-color: rgba(77, 64, 82, 0.3);
            transform: translateY(-1px);
            color: var(--color-dark-purple);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .btn i {
            font-size: 0.85rem;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 50px 30px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(218, 165, 32, 0.2);
            border-top: 3px solid var(--color-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            color: var(--color-brown);
            font-size: 1rem;
            font-weight: 500;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .welcome-container {
                padding: 15px;
            }

            .welcome-header {
                padding: 30px 20px;
            }

            .step-content {
                padding: 25px 20px;
            }

            .language-options {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .step-actions {
                flex-direction: column;
                gap: 12px;
            }

            .btn {
                width: 100%;
                min-width: auto;
            }

            .progress-steps {
                gap: 20px;
            }

            .progress-step span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <!-- Header -->
            <div class="welcome-header">
                <div class="company-logo">
                    <i class="fas fa-handshake"></i>
                </div>
                <h1 class="welcome-title"><?php echo t('welcome.title', 'Welcome!'); ?></h1>
                <p class="welcome-subtitle"><?php echo t('welcome.subtitle', 'Let\'s get you started with your equb journey'); ?></p>
                <div class="user-info">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-steps">
                    <div class="progress-step active" id="progressStep1">
                        <div class="step-number">1</div>
                        <span><?php echo t('welcome.language_selection.title', 'Language Selection'); ?></span>
                    </div>
                    <div class="progress-step" id="progressStep2">
                        <div class="step-number">2</div>
                        <span><?php echo t('welcome.rules_agreement.title', 'Rules Agreement'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="step-content">
                <!-- Step 1: Language Selection -->
                <div class="step active" id="step1">
                    <div class="step-header">
                        <h2 class="step-title">
                            <i class="fas fa-globe"></i> <?php echo t('welcome.language_selection.title', 'Choose Your Language'); ?>
                        </h2>
                        <p class="step-description"><?php echo t('welcome.language_selection.description', 'Please select your preferred language for the system'); ?></p>
                    </div>

                    <div class="language-options">
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
                            <?php echo t('welcome.continue', 'Continue'); ?> <i class="fas fa-arrow-right"></i>
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

                    <div class="rules-section">
                        <div class="rules-container">
                            <?php foreach ($rules as $rule): ?>
                            <div class="rule-item">
                                <div class="rule-number">
                                    <?php echo ($current_lang === 'am' ? 'ህግ' : 'Rule'); ?> <?php echo $rule['rule_number']; ?>
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
                                    <?php echo t('welcome.rules_agreement.agreement_text', 'I have read and understand all the rules. I agree to follow these rules.'); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button class="btn btn-secondary" onclick="goBackToLanguage()">
                            <i class="fas fa-arrow-left"></i> <?php echo t('welcome.back', 'Back'); ?>
                        </button>
                        <button class="btn btn-primary" onclick="agreeToRules()" id="agreeBtn" disabled>
                            <i class="fas fa-check"></i> <?php echo t('welcome.rules_agreement.agree_button', 'I Agree'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p class="loading-text">
                    <?php echo $current_lang === 'am' ? 'እባኮዎን ይጠብቁ...' : 'Please wait...'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let selectedLanguage = '<?php echo $current_lang; ?>';
        let languageChanged = false;

        // Language selection
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', function() {
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
            });
        });

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
                    // FIXED: Reload page to apply language changes properly
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
            document.querySelectorAll('.progress-step').forEach(stepEl => {
                stepEl.classList.remove('active', 'completed');
            });

            // Update progress
            for (let i = 1; i <= 2; i++) {
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
                    // Success - redirect to dashboard
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
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