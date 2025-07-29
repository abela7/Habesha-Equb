<?php
/**
 * HabeshaEqub - Welcome Page
 * First-time user onboarding: Language selection & Rules acceptance
 */

// Skip the normal auth check since this is part of the auth flow
define('SKIP_AUTH_CHECK', true);
require_once __DIR__ . '/includes/session_config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../languages/translator.php';

// Check if user is logged in but hasn't completed welcome flow
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get user data to check if welcome flow is complete
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
    
} catch (Exception $e) {
    error_log("Welcome page error: " . $e->getMessage());
    header('Location: login.php?msg=' . urlencode('An error occurred. Please try again.'));
    exit;
}

// Set initial language preference
$current_lang = $user['language_preference'] == 1 ? 'am' : 'en';
$translator = new Translator($current_lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $current_lang === 'am' ? 'ltr' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translator->translate('welcome.page_title'); ?> - HabeshaEqub</title>
    
    <!-- Favicon -->
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
        
        .welcome-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .welcome-card {
            background: rgba(241, 236, 226, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: var(--shadow-elegant);
            max-width: 500px;
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
        
        .welcome-header {
            background: var(--gradient-secondary);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }
        
        .welcome-header::before {
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
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        .logo-icon i {
            font-size: 2.5rem;
            color: var(--color-navy);
        }
        
        .welcome-title {
            color: var(--color-navy);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-subtitle {
            color: var(--color-brown);
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .welcome-content {
            padding: 40px 30px;
        }
        
        .step-container {
            display: none;
            animation: stepFadeIn 0.5s ease-out;
        }
        
        .step-container.active {
            display: block;
        }
        
        @keyframes stepFadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .step-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .step-title {
            color: var(--color-navy);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .step-description {
            color: var(--color-brown);
            opacity: 0.8;
        }
        
        /* Language Selection Styles */
        .language-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .language-option {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid transparent;
            border-radius: 16px;
            padding: 20px;
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
        
        .language-option.selected {
            border-color: var(--color-gold);
            background: rgba(218, 165, 32, 0.1);
            transform: translateY(-2px);
            box-shadow: var(--shadow-card);
        }
        
        .language-option * {
            position: relative;
            z-index: 1;
        }
        
        .language-flag {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .language-name {
            color: var(--color-navy);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        /* Rules Styles */
        .rules-container {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid rgba(77, 64, 82, 0.1);
            border-radius: 16px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.5);
            margin-bottom: 25px;
        }
        
        .rules-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .rules-container::-webkit-scrollbar-track {
            background: rgba(241, 236, 226, 0.5);
            border-radius: 3px;
        }
        
        .rules-container::-webkit-scrollbar-thumb {
            background: var(--color-gold);
            border-radius: 3px;
        }
        
        .rule-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--color-gold);
            transition: all 0.3s ease;
        }
        
        .rule-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-card);
        }
        
        .rule-number {
            color: var(--color-navy);
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        
        .rule-text {
            color: var(--color-brown);
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .agreement-section {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }
        
        .agreement-checkbox {
            margin-bottom: 20px;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .checkbox-wrapper:hover {
            background: rgba(218, 165, 32, 0.1);
        }
        
        .custom-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid var(--color-gold);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .custom-checkbox.checked {
            background: var(--color-gold);
            color: white;
        }
        
        .agreement-text {
            color: var(--color-navy);
            font-weight: 500;
            font-size: 1rem;
        }
        
        /* Button Styles */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-custom {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .btn-primary-custom {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(48, 25, 52, 0.3);
        }
        
        .btn-primary-custom:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
        
        /* Loading Animation */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Progress Indicator */
        .progress-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .progress-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(77, 64, 82, 0.3);
            transition: all 0.3s ease;
        }
        
        .progress-dot.active {
            background: var(--color-gold);
            transform: scale(1.2);
        }
        
        .progress-dot.completed {
            background: var(--color-navy);
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .welcome-container {
                padding: 10px;
            }
            
            .welcome-card {
                border-radius: 20px;
                margin: 10px;
            }
            
            .welcome-header {
                padding: 30px 20px 25px;
            }
            
            .welcome-content {
                padding: 30px 20px;
            }
            
            .welcome-title {
                font-size: 1.6rem;
            }
            
            .language-options {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 12px;
            }
            
            .rules-container {
                max-height: 300px;
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .welcome-title {
                font-size: 1.4rem;
            }
            
            .welcome-subtitle {
                font-size: 0.9rem;
            }
            
            .step-title {
                font-size: 1.2rem;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
            }
            
            .logo-icon i {
                font-size: 2rem;
            }
        }
        
        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--gradient-primary);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-elegant);
            z-index: 1000;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }
        
        .toast-notification.show {
            transform: translateX(0);
        }
        
        .toast-notification.error {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        
        .toast-notification.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <!-- Header -->
            <div class="welcome-header">
                <div class="logo-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h1 class="welcome-title"><?php echo $translator->translate('welcome.title'); ?></h1>
                <p class="welcome-subtitle"><?php echo $translator->translate('welcome.subtitle'); ?></p>
            </div>
            
            <!-- Content -->
            <div class="welcome-content">
                <!-- Progress Indicator -->
                <div class="progress-indicator">
                    <div class="progress-dot active" data-step="1"></div>
                    <div class="progress-dot" data-step="2"></div>
                </div>
                
                <!-- Step 1: Language Selection -->
                <div class="step-container active" id="step-language">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <h3 class="step-title"><?php echo $translator->translate('welcome.language_selection.title'); ?></h3>
                        <p class="step-description"><?php echo $translator->translate('welcome.language_selection.description'); ?></p>
                    </div>
                    
                    <div class="language-options">
                        <div class="language-option" data-lang="en" <?php echo $current_lang === 'en' ? 'class="language-option selected"' : ''; ?>>
                            <span class="language-flag">🇺🇸</span>
                            <div class="language-name">English</div>
                        </div>
                        <div class="language-option" data-lang="am" <?php echo $current_lang === 'am' ? 'class="language-option selected"' : ''; ?>>
                            <span class="language-flag">🇪🇹</span>
                            <div class="language-name">አማርኛ</div>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn-custom btn-primary-custom" id="btn-continue-language" disabled>
                            <span class="loading-spinner"></span>
                            <span class="btn-text"><?php echo $translator->translate('welcome.continue'); ?></span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Rules Agreement -->
                <div class="step-container" id="step-rules">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <h3 class="step-title"><?php echo $translator->translate('welcome.rules_agreement.title'); ?></h3>
                        <p class="step-description"><?php echo $translator->translate('welcome.rules_agreement.description'); ?></p>
                    </div>
                    
                    <div class="rules-container" id="rules-list">
                        <!-- Rules will be populated dynamically -->
                    </div>
                    
                    <div class="agreement-section">
                        <div class="agreement-checkbox">
                            <label class="checkbox-wrapper" for="agreement-check">
                                <div class="custom-checkbox" id="custom-checkbox">
                                    <i class="fas fa-check" style="display: none;"></i>
                                </div>
                                <span class="agreement-text"><?php echo $translator->translate('welcome.rules_agreement.agreement_text'); ?></span>
                                <input type="checkbox" id="agreement-check" style="display: none;">
                            </label>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn-custom btn-secondary-custom" id="btn-back">
                                <i class="fas fa-arrow-left"></i>
                                <span><?php echo $translator->translate('welcome.back'); ?></span>
                            </button>
                            <button type="button" class="btn-custom btn-primary-custom" id="btn-agree" disabled>
                                <span class="loading-spinner"></span>
                                <span class="btn-text"><?php echo $translator->translate('welcome.rules_agreement.agree_button'); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global state
        let currentStep = 1;
        let selectedLanguage = '<?php echo $current_lang; ?>';
        let rulesAgreed = false;
        
        // Rules data
        const rulesData = <?php echo json_encode($rules); ?>;
        
        // DOM elements
        const languageOptions = document.querySelectorAll('.language-option');
        const continueLanguageBtn = document.getElementById('btn-continue-language');
        const backBtn = document.getElementById('btn-back');
        const agreeBtn = document.getElementById('btn-agree');
        const agreementCheck = document.getElementById('agreement-check');
        const customCheckbox = document.getElementById('custom-checkbox');
        const rulesContainer = document.getElementById('rules-list');
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeLanguageSelection();
            initializeRulesAgreement();
            updateButtonStates();
        });
        
        // Language selection functionality
        function initializeLanguageSelection() {
            languageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    languageOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    selectedLanguage = this.dataset.lang;
                    updateButtonStates();
                    
                    // Update page language immediately
                    updatePageLanguage(selectedLanguage);
                });
            });
            
            continueLanguageBtn.addEventListener('click', function() {
                if (selectedLanguage) {
                    saveLanguagePreference();
                }
            });
        }
        
        // Rules agreement functionality
        function initializeRulesAgreement() {
            // Custom checkbox functionality
            agreementCheck.addEventListener('change', function() {
                rulesAgreed = this.checked;
                customCheckbox.classList.toggle('checked', this.checked);
                customCheckbox.querySelector('.fa-check').style.display = this.checked ? 'block' : 'none';
                updateButtonStates();
            });
            
            // Custom checkbox click handler
            document.querySelector('.checkbox-wrapper').addEventListener('click', function(e) {
                e.preventDefault();
                agreementCheck.checked = !agreementCheck.checked;
                agreementCheck.dispatchEvent(new Event('change'));
            });
            
            backBtn.addEventListener('click', function() {
                goToStep(1);
            });
            
            agreeBtn.addEventListener('click', function() {
                if (rulesAgreed) {
                    saveRulesAgreement();
                }
            });
        }
        
        // Update button states
        function updateButtonStates() {
            continueLanguageBtn.disabled = !selectedLanguage;
            agreeBtn.disabled = !rulesAgreed;
        }
        
        // Navigation functions
        function goToStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-container').forEach(container => {
                container.classList.remove('active');
            });
            
            // Show target step
            document.getElementById(`step-${step === 1 ? 'language' : 'rules'}`).classList.add('active');
            
            // Update progress indicator
            document.querySelectorAll('.progress-dot').forEach((dot, index) => {
                dot.classList.remove('active', 'completed');
                if (index + 1 < step) {
                    dot.classList.add('completed');
                } else if (index + 1 === step) {
                    dot.classList.add('active');
                }
            });
            
            currentStep = step;
            
            // Load rules if going to step 2
            if (step === 2) {
                loadRules();
            }
        }
        
        // Load and display rules
        function loadRules() {
            const isAmharic = selectedLanguage === 'am';
            
            rulesContainer.innerHTML = rulesData.map(rule => `
                <div class="rule-item">
                    <div class="rule-number">${isAmharic ? 'ደንብ' : 'Rule'} ${rule.rule_number}</div>
                    <div class="rule-text">${isAmharic ? rule.rule_am : rule.rule_en}</div>
                </div>
            `).join('');
        }
        
        // API calls
        async function saveLanguagePreference() {
            showLoading(continueLanguageBtn);
            
            try {
                const response = await fetch('api/welcome.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_language',
                        language: selectedLanguage
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    goToStep(2);
                    showToast('Language preference updated successfully!', 'success');
                } else {
                    showToast(result.message || 'Failed to update language preference', 'error');
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                hideLoading(continueLanguageBtn);
            }
        }
        
        async function saveRulesAgreement() {
            showLoading(agreeBtn);
            
            try {
                const response = await fetch('api/welcome.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'agree_rules'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Welcome to HabeshaEqub! Redirecting to dashboard...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    showToast(result.message || 'Failed to save agreement', 'error');
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                hideLoading(agreeBtn);
            }
        }
        
        // Update page language dynamically
        async function updatePageLanguage(lang) {
            try {
                const response = await fetch(`../languages/${lang}.json`);
                const translations = await response.json();
                
                // Update text content based on translations
                // This would require more sophisticated translation updating
                // For now, we'll reload the page with the new language
                // In a real implementation, you'd update specific text elements
                
            } catch (error) {
                console.error('Error loading translations:', error);
            }
        }
        
        // Utility functions
        function showLoading(button) {
            const spinner = button.querySelector('.loading-spinner');
            const text = button.querySelector('.btn-text');
            
            spinner.style.display = 'inline-block';
            button.disabled = true;
        }
        
        function hideLoading(button) {
            const spinner = button.querySelector('.loading-spinner');
            
            spinner.style.display = 'none';
            updateButtonStates();
        }
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Hide and remove toast
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        
        // Prevent going back/forward in browser during onboarding
        window.addEventListener('beforeunload', function(e) {
            if (currentStep === 1 || !rulesAgreed) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html> 