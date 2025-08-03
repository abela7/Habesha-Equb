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

// Include auth guard functions (but skip auth check for OTP page)
define('SKIP_AUTH_CHECK', true);
require_once 'includes/auth_guard.php';

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

// Check if user has OTP session (they should have been redirected here from login)
if (!isset($_SESSION['otp_email'])) {
    header('Location: login.php');
    exit;
}

// Get email from session or URL parameter
$email = $_SESSION['otp_email'] ?? $_GET['email'] ?? '';
$user_name = $_SESSION['pending_name'] ?? 'User';

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
    <title><?php echo t('otp_verification.page_title'); ?> - HabeshaEqub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--darker-purple) 0%, var(--dark-purple) 40%, rgba(241, 236, 226, 0.3) 70%, var(--cream) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .otp-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Language Toggle */
        .language-toggle {
            position: absolute;
            top: 20px;
            right: 24px;
            z-index: 1000;
        }

        .language-btn {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 8px 16px;
            color: var(--white);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .language-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 140px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .language-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .language-option {
            display: block;
            width: 100%;
            padding: 12px 16px;
            background: none;
            border: none;
            text-align: left;
            color: var(--dark-purple);
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 14px;
        }

        .language-option:first-child {
            border-radius: 12px 12px 0 0;
        }

        .language-option:last-child {
            border-radius: 0 0 12px 12px;
        }

        .language-option:hover {
            background-color: rgba(218, 165, 32, 0.1);
        }

        /* Main Content */
        .otp-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .otp-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .otp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold) 0%, var(--light-gold) 50%, var(--brown) 100%);
        }

        .otp-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .otp-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: var(--white);
            font-size: 32px;
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.3);
        }

        .otp-title {
            color: var(--white);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .otp-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            line-height: 1.5;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .otp-info {
            background: rgba(241, 236, 226, 0.95);
            border: 1px solid rgba(218, 165, 32, 0.3);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 24px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .otp-info-text {
            color: var(--darker-purple);
            font-size: 15px;
            font-weight: 500;
            margin: 0;
            line-height: 1.6;
        }

        .otp-info strong {
            color: var(--gold);
            font-weight: 700;
        }

        /* Form Styles */
        .otp-form {
            margin-top: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--white);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .otp-input-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 20px 0;
        }

        .otp-digit {
            width: 50px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--white);
            outline: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .otp-digit:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--gold);
            cursor: pointer;
        }

        .checkbox-group label {
            color: var(--white);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            line-height: 1.4;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 50%, var(--brown) 100%);
            border: none;
            border-radius: 12px;
            padding: 16px 24px;
            color: var(--white);
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 24px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .alert.show {
            opacity: 1;
            transform: translateY(0);
        }

        .alert-success {
            background: linear-gradient(135deg, #F0FDF4 0%, #ECFDF5 100%);
            color: #059669;
            border: 1px solid #BBF7D0;
        }

        .alert-error {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEF1F1 100%);
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error-message {
            color: #EF4444;
            font-size: 12px;
            margin-top: 4px;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        .error-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .otp-card {
                padding: 32px 24px;
                margin: 20px;
                border-radius: 20px;
            }
            
            .otp-title {
                font-size: 1.75rem;
            }
            
            .language-toggle {
                top: 16px;
                right: 16px;
            }
        }

        @media (max-width: 480px) {
            .otp-container {
                min-height: 100vh;
            }
            
            .otp-card {
                padding: 24px 20px;
                margin: 16px;
                border-radius: 16px;
            }
            
            .otp-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .otp-title {
                font-size: 1.5rem;
            }
            
            .otp-digit {
                width: 45px;
                height: 55px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="otp-container">
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

        <!-- Main Content -->
        <div class="otp-content">
            <div class="otp-card">
                <!-- Header -->
                <div class="otp-header">
                    <div class="otp-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h1 class="otp-title"><?php echo t('otp_verification.title'); ?></h1>
                    <p class="otp-subtitle"><?php echo t('otp_verification.subtitle'); ?></p>
                </div>

                <!-- Info Box -->
                <div class="otp-info">
                    <p class="otp-info-text">
                        <?php echo t('otp_verification.info_text'); ?> <strong><?php echo htmlspecialchars($email); ?></strong>
                    </p>
                </div>

                <!-- Alert Messages -->
                <div class="alert alert-success" id="successAlert"></div>
                <div class="alert alert-error" id="errorAlert"></div>
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- OTP Form -->
                <form class="otp-form" id="otpForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="verify_otp">

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i>
                            <?php echo t('otp_verification.enter_code'); ?>
                        </label>
                        <div class="otp-input-container">
                            <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="0">
                            <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="1">
                            <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="2">
                            <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="3">
                        </div>
                        <div class="error-message" id="otpError"></div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="remember_device" name="remember_device">
                        <label for="remember_device"><?php echo t('otp_verification.remember_device'); ?></label>
                    </div>

                    <button type="submit" class="btn-primary">
                        <span class="btn-text"><?php echo t('otp_verification.verify_button'); ?></span>
                    </button>

                    <button type="button" class="btn-secondary" onclick="requestNewCode()">
                        <i class="fas fa-redo"></i>
                        <?php echo t('otp_verification.resend_code'); ?>
                    </button>
                </form>

                <!-- Back to Login -->
                <div style="text-align: center; margin-top: 24px;">
                    <a href="login.php" style="color: var(--white); text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.3s ease; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--white)'">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                        <?php echo t('otp_verification.back_to_login'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        class OTPVerification {
            constructor() {
                this.initializeOTPInputs();
                this.bindEvents();
            }

            initializeOTPInputs() {
                const otpInputs = document.querySelectorAll('.otp-digit');
                
                otpInputs.forEach((input, index) => {
                    input.addEventListener('input', (e) => {
                        const value = e.target.value;
                        
                        // Only allow numbers
                        if (!/^[0-9]$/.test(value)) {
                            e.target.value = '';
                            return;
                        }
                        
                        // Move to next input
                        if (value && index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                        
                        // Check if all inputs are filled
                        this.checkAllInputsFilled();
                    });
                    
                    input.addEventListener('keydown', (e) => {
                        // Handle backspace
                        if (e.key === 'Backspace' && !e.target.value && index > 0) {
                            otpInputs[index - 1].focus();
                        }
                        
                        // Handle paste
                        if (e.key === 'v' && e.ctrlKey) {
                            e.preventDefault();
                            navigator.clipboard.readText().then(text => {
                                this.fillOTPFromClipboard(text);
                            });
                        }
                    });
                });
                
                // Focus first input
                otpInputs[0].focus();
            }

            fillOTPFromClipboard(text) {
                const digits = text.replace(/\D/g, '').slice(0, 6);
                const otpInputs = document.querySelectorAll('.otp-digit');
                
                digits.split('').forEach((digit, index) => {
                    if (otpInputs[index]) {
                        otpInputs[index].value = digit;
                    }
                });
                
                if (digits.length === 4) {
                    this.checkAllInputsFilled();
                }
            }

            checkAllInputsFilled() {
                const otpInputs = document.querySelectorAll('.otp-digit');
                const allFilled = Array.from(otpInputs).every(input => input.value.length === 1);
                
                if (allFilled) {
                    // Auto-submit after a brief delay
                    setTimeout(() => {
                        this.handleVerification(null);
                    }, 300);
                }
            }

            getOTPValue() {
                const otpInputs = document.querySelectorAll('.otp-digit');
                return Array.from(otpInputs).map(input => input.value).join('');
            }

            clearOTPInputs() {
                const otpInputs = document.querySelectorAll('.otp-digit');
                otpInputs.forEach(input => input.value = '');
                otpInputs[0].focus();
            }

            bindEvents() {
                document.getElementById('otpForm').addEventListener('submit', (e) => this.handleVerification(e));
            }

            async handleVerification(e) {
                if (e) e.preventDefault();
                
                const form = document.getElementById('otpForm');
                const submitBtn = form.querySelector('.btn-primary');
                const otpCode = this.getOTPValue();
                
                if (otpCode.length !== 4) {
                    this.showAlert('error', '<?php echo t('otp_verification.code_required'); ?>');
                    return;
                }

                this.setButtonLoading(submitBtn, true);
                this.clearAllAlerts();

                const formData = new FormData(form);
                formData.append('otp_code', otpCode);

                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showAlert('success', result.message || '<?php echo t('otp_verification.success'); ?>');
                        setTimeout(() => {
                            window.location.href = result.redirect || 'dashboard.php';
                        }, 1000);
                    } else {
                        this.showAlert('error', result.message || '<?php echo t('otp_verification.error'); ?>');
                        this.clearOTPInputs();
                    }
                } catch (error) {
                    console.error('Verification error:', error);
                    this.showAlert('error', '<?php echo t('user_auth.network_error'); ?>');
                    this.clearOTPInputs();
                } finally {
                    this.setButtonLoading(submitBtn, false);
                }
            }

            setButtonLoading(button, loading) {
                const btnText = button.querySelector('.btn-text');
                
                if (loading) {
                    button.disabled = true;
                    btnText.innerHTML = '<div class="loading-spinner"></div><?php echo t('common.loading'); ?>';
                } else {
                    button.disabled = false;
                    btnText.textContent = '<?php echo t('otp_verification.verify_button'); ?>';
                }
            }

            showAlert(type, message) {
                const alertElement = document.getElementById(type === 'error' ? 'errorAlert' : 'successAlert');
                alertElement.textContent = message;
                alertElement.classList.add('show');
                
                setTimeout(() => {
                    alertElement.classList.remove('show');
                }, 5000);
            }

            clearAllAlerts() {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.classList.remove('show');
                });
            }
        }

        // Language toggle functionality
        function toggleLanguageDropdown() {
            const dropdown = document.getElementById('languageDropdown');
            dropdown.classList.toggle('show');
        }

        function changeLanguage(lang) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'language';
            input.value = lang;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        async function requestNewCode() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<div class="loading-spinner"></div><?php echo t('common.loading'); ?>';
            
            try {
                const formData = new FormData();
                formData.append('action', 'request_otp');
                formData.append('email', '<?php echo htmlspecialchars($email); ?>');
                formData.append('csrf_token', '<?php echo htmlspecialchars($csrf_token); ?>');
                
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    otpApp.showAlert('success', result.message || '<?php echo t('otp_verification.code_resent'); ?>');
                    otpApp.clearOTPInputs();
                } else {
                    otpApp.showAlert('error', result.message || '<?php echo t('otp_verification.resend_error'); ?>');
                }
            } catch (error) {
                console.error('Resend error:', error);
                otpApp.showAlert('error', '<?php echo t('user_auth.network_error'); ?>');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('languageDropdown');
            const toggle = document.querySelector('.language-btn');
            
            if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Initialize OTP verification
        const otpApp = new OTPVerification();
    </script>
</body>
</html>