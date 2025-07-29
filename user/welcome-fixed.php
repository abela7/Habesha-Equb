<?php
/**
 * HabeshaEqub - Welcome Page (FIXED VERSION)
 * First-time user onboarding: Language selection & Rules acceptance
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .welcome-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            position: relative;
        }

        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="0%" r="100%"><stop offset="0%" stop-color="rgba(255,255,255,.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><rect width="100" height="20" fill="url(%23a)"/></svg>');
        }

        .welcome-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .welcome-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .step-container {
            padding: 40px;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeInUp 0.5s ease-out;
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
            margin-bottom: 30px;
        }

        .step-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }

        .step-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .language-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 30px 0;
        }

        .language-option {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .language-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
            transform: translateY(-2px);
        }

        .language-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .language-option .flag {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .language-option .name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .rules-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .rule-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .rule-item:last-child {
            margin-bottom: 0;
        }

        .rule-number {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }

        .rule-text {
            color: #333;
            line-height: 1.5;
        }

        .agreement-section {
            background: #fff5f5;
            border: 2px solid #fed7d7;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }

        .agreement-checkbox {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            cursor: pointer;
        }

        .agreement-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
        }

        .agreement-checkbox label {
            color: #333;
            font-weight: 500;
            cursor: pointer;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102,126,234,0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn i {
            margin-left: 8px;
        }

        .step-actions {
            text-align: center;
            margin-top: 30px;
        }

        .progress-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin: 30px 0 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            transition: width 0.5s ease;
            width: 50%;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .welcome-header h1 {
                font-size: 2rem;
            }
            
            .step-container {
                padding: 20px;
            }
            
            .language-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <!-- Header -->
        <div class="welcome-header">
            <h1><i class="fas fa-hand-wave"></i> <?php echo t('welcome', 'Welcome'); ?>!</h1>
            <div class="subtitle"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <!-- Step 1: Language Selection -->
        <div class="step active" id="step1">
            <div class="step-container">
                <div class="step-header">
                    <h2 class="step-title"><?php echo t('choose_language', 'Choose Your Language'); ?></h2>
                    <p class="step-subtitle"><?php echo t('language_subtitle', 'Select your preferred language for the application'); ?></p>
                </div>

                <div class="language-options">
                    <div class="language-option <?php echo $current_lang === 'en' ? 'selected' : ''; ?>" data-lang="en">
                        <span class="flag">🇺🇸</span>
                        <div class="name">English</div>
                    </div>
                    <div class="language-option <?php echo $current_lang === 'am' ? 'selected' : ''; ?>" data-lang="am">
                        <span class="flag">🇪🇹</span>
                        <div class="name">አማርኛ</div>
                    </div>
                </div>

                <div class="step-actions">
                    <button class="btn" onclick="proceedToRules()" id="langNextBtn">
                        <?php echo t('continue', 'Continue'); ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Rules Agreement -->
        <div class="step" id="step2">
            <div class="step-container">
                <div class="step-header">
                    <h2 class="step-title"><?php echo t('equb_rules', 'Equb Rules & Regulations'); ?></h2>
                    <p class="step-subtitle"><?php echo t('rules_subtitle', 'Please read and accept the following rules to continue'); ?></p>
                </div>

                <div class="rules-container">
                    <?php foreach ($rules as $rule): ?>
                    <div class="rule-item">
                        <div class="rule-number">Rule <?php echo $rule['rule_number']; ?></div>
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
                            <?php echo t('agree_rules', 'I have read and agree to follow all the rules above'); ?>
                        </label>
                    </div>
                </div>

                <div class="step-actions">
                    <button class="btn" onclick="agreeToRules()" id="agreeBtn" disabled>
                        <?php echo t('agree_continue', 'ተስማምቻለሁ'); ?> <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p><?php echo t('processing', 'Processing your preferences...'); ?></p>
        </div>
    </div>

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

        function updateLanguage(language) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('loading').classList.add('active');

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
            document.getElementById('loading').classList.remove('active');
            document.getElementById('step1').style.display = 'block';
            document.getElementById('progressFill').style.width = '50%';
        }

        function showStep2() {
            document.getElementById('loading').classList.remove('active');
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').classList.add('active');
            document.getElementById('progressFill').style.width = '100%';
        }

        function agreeToRules() {
            if (!document.getElementById('agreeRules').checked) {
                alert('<?php echo t('must_agree', 'You must agree to the rules to continue'); ?>');
                return;
            }

            document.getElementById('step2').style.display = 'none';
            document.getElementById('loading').classList.add('active');

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
                    // Redirect to dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    alert(data.message || 'Error saving agreement');
                    document.getElementById('loading').classList.remove('active');
                    document.getElementById('step2').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
                document.getElementById('loading').classList.remove('active');
                document.getElementById('step2').style.display = 'block';
            });
        }
    </script>
</body>
</html> 