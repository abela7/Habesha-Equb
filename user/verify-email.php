<?php
/**
 * HabeshaEqub - Email Verification Page
 * Users enter OTP code to verify their email address
 */

// Skip auth check since this is for unverified users
define('SKIP_AUTH_CHECK', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../languages/translator.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get email from URL or session
$email = '';
if (isset($_GET['email'])) {
    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
} elseif (isset($_SESSION['temp_registration']['email'])) {
    $email = $_SESSION['temp_registration']['email'];
}

// If no valid email, redirect to registration
if (!$email) {
    header('Location: login.php?msg=' . urlencode('Please register first'));
    exit;
}

// Check if user exists and email verification status
try {
    $stmt = $pdo->prepare("SELECT id, first_name, email_verified FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: login.php?msg=' . urlencode('User not found. Please register again.'));
        exit;
    }
    
    // If already verified, redirect to waiting page
    if ($user['email_verified'] == 1) {
        header('Location: waiting-approval.php?email=' . urlencode($email));
        exit;
    }
    
} catch (Exception $e) {
    error_log("Email verification page error: " . $e->getMessage());
    header('Location: login.php?msg=' . urlencode('An error occurred. Please try again.'));
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Set default language
if (!isset($_SESSION['app_language'])) {
    setLanguage('en');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - HabeshaEqub</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">
    
    <style>
        /* Golden Color Palette */
        :root {
            --cream: #F1ECE2;
            --dark-purple: #4D4052;
            --darker-purple: #301934;
            --gold: #DAA520;
            --light-gold: #CDAF56;
            --white: #FFFFFF;
            --success: #28a745;
            --error: #dc3545;
            --warning: #ffc107;
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
                radial-gradient(circle at 20% 20%, rgba(218, 165, 32, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(205, 175, 86, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(77, 64, 82, 0.05) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }
        
        /* Main Container */
        .verification-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        
        .verification-header {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .verification-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .verification-icon {
            font-size: 48px;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        
        .verification-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .verification-subtitle {
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .verification-content {
            padding: 40px 30px;
        }
        
        .user-info {
            background: var(--cream);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
            border-left: 4px solid var(--gold);
        }
        
        .user-email {
            font-size: 18px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 8px;
        }
        
        .user-message {
            font-size: 14px;
            color: var(--dark-purple);
            opacity: 0.8;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 8px;
        }
        
        .otp-input-container {
            position: relative;
        }
        
        .otp-input {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
            transition: all 0.3s ease;
            background: var(--white);
        }
        
        .otp-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
            transform: translateY(-2px);
        }
        
        .otp-input.error {
            border-color: var(--error);
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .verify-button {
            width: 100%;
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            color: white;
            border: none;
            padding: 18px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(218, 165, 32, 0.3);
        }
        
        .verify-button:active {
            transform: translateY(0);
        }
        
        .verify-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .verify-button .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        .verify-button.loading .loading-spinner {
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .resend-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .resend-text {
            font-size: 14px;
            color: var(--dark-purple);
            margin-bottom: 12px;
        }
        
        .resend-button {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            transition: all 0.3s ease;
        }
        
        .resend-button:hover {
            color: var(--darker-purple);
        }
        
        .resend-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .countdown {
            font-size: 12px;
            color: var(--dark-purple);
            opacity: 0.7;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .alert.success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: var(--success);
        }
        
        .alert.error {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: var(--error);
        }
        
        .help-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .help-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 12px;
        }
        
        .help-list {
            font-size: 13px;
            color: var(--dark-purple);
            line-height: 1.5;
            margin: 0;
            padding-left: 16px;
        }
        
        .help-list li {
            margin-bottom: 6px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .verification-header {
                padding: 30px 20px;
            }
            
            .verification-content {
                padding: 30px 20px;
            }
            
            .verification-title {
                font-size: 24px;
            }
            
            .otp-input {
                font-size: 20px;
                letter-spacing: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <div class="verification-icon">📧</div>
            <h1 class="verification-title">Verify Your Email</h1>
            <p class="verification-subtitle">Enter the verification code we sent to your email</p>
        </div>
        
        <div class="verification-content">
            <div class="user-info">
                <div class="user-email"><?= htmlspecialchars($email) ?></div>
                <div class="user-message">Hello <?= htmlspecialchars($user['first_name']) ?>! We've sent a 6-digit code to this email address.</div>
            </div>
            
            <div class="alert" id="alertMessage"></div>
            
            <form id="verificationForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="action" value="verify_email">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                
                <div class="form-group">
                    <label class="form-label" for="otp_code">
                        <i class="fas fa-key"></i> Verification Code
                    </label>
                    <div class="otp-input-container">
                        <input 
                            type="text" 
                            id="otp_code" 
                            name="otp_code" 
                            class="otp-input" 
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            autocomplete="one-time-code"
                            required
                        >
                    </div>
                </div>
                
                <button type="submit" class="verify-button" id="verifyButton">
                    <span class="loading-spinner"></span>
                    <span class="button-text">🔐 Verify Email</span>
                </button>
            </form>
            
            <div class="resend-section">
                <div class="resend-text">Didn't receive the code?</div>
                <button type="button" class="resend-button" id="resendButton">
                    📨 Resend Verification Code
                </button>
                <div class="countdown" id="resendCountdown" style="display: none;"></div>
            </div>
            
            <div class="help-section">
                <div class="help-title">💡 Having trouble?</div>
                <ul class="help-list">
                    <li>Check your spam/junk folder</li>
                    <li>Make sure you entered the correct email address</li>
                    <li>The code expires in 10 minutes</li>
                    <li>Contact support if you continue having issues</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        class EmailVerification {
            constructor() {
                this.form = document.getElementById('verificationForm');
                this.otpInput = document.getElementById('otp_code');
                this.verifyButton = document.getElementById('verifyButton');
                this.resendButton = document.getElementById('resendButton');
                this.alertDiv = document.getElementById('alertMessage');
                this.countdownDiv = document.getElementById('resendCountdown');
                
                this.resendCooldown = 60; // 60 seconds
                this.resendTimer = null;
                
                this.init();
            }
            
            init() {
                this.form.addEventListener('submit', (e) => this.handleVerify(e));
                this.resendButton.addEventListener('click', () => this.handleResend());
                
                // Auto-focus OTP input
                this.otpInput.focus();
                
                // Format OTP input (numbers only)
                this.otpInput.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
                });
                
                // Auto-submit when 6 digits entered
                this.otpInput.addEventListener('input', (e) => {
                    if (e.target.value.length === 6) {
                        setTimeout(() => this.form.dispatchEvent(new Event('submit')), 500);
                    }
                });
            }
            
            async handleVerify(e) {
                e.preventDefault();
                
                const formData = new FormData(this.form);
                
                if (!this.validateForm(formData)) return;
                
                this.setLoading(true);
                this.hideAlert();
                
                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showAlert('success', '✅ Email verified successfully! Redirecting...');
                        
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 2000);
                    } else {
                        this.showAlert('error', result.message);
                        this.otpInput.classList.add('error');
                        this.otpInput.select();
                        
                        setTimeout(() => {
                            this.otpInput.classList.remove('error');
                        }, 500);
                    }
                    
                } catch (error) {
                    console.error('Verification error:', error);
                    this.showAlert('error', 'Network error. Please check your connection and try again.');
                } finally {
                    this.setLoading(false);
                }
            }
            
            async handleResend() {
                if (this.resendButton.disabled) return;
                
                const formData = new FormData();
                formData.append('action', 'resend_verification');
                formData.append('email', '<?= htmlspecialchars($email) ?>');
                formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');
                
                this.resendButton.disabled = true;
                this.resendButton.textContent = '📨 Sending...';
                
                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showAlert('success', result.message);
                        this.startResendCooldown();
                    } else {
                        this.showAlert('error', result.message);
                        this.resendButton.disabled = false;
                        this.resendButton.textContent = '📨 Resend Verification Code';
                    }
                    
                } catch (error) {
                    console.error('Resend error:', error);
                    this.showAlert('error', 'Failed to resend code. Please try again.');
                    this.resendButton.disabled = false;
                    this.resendButton.textContent = '📨 Resend Verification Code';
                }
            }
            
            validateForm(formData) {
                const otpCode = formData.get('otp_code');
                
                if (!otpCode || otpCode.length !== 6) {
                    this.showAlert('error', 'Please enter a valid 6-digit verification code');
                    this.otpInput.focus();
                    return false;
                }
                
                if (!/^\d{6}$/.test(otpCode)) {
                    this.showAlert('error', 'Verification code must contain only numbers');
                    this.otpInput.focus();
                    return false;
                }
                
                return true;
            }
            
            setLoading(loading) {
                this.verifyButton.disabled = loading;
                this.verifyButton.classList.toggle('loading', loading);
                
                if (loading) {
                    this.verifyButton.querySelector('.button-text').textContent = 'Verifying...';
                } else {
                    this.verifyButton.querySelector('.button-text').textContent = '🔐 Verify Email';
                }
            }
            
            showAlert(type, message) {
                this.alertDiv.className = `alert ${type}`;
                this.alertDiv.textContent = message;
                this.alertDiv.style.display = 'block';
                
                // Auto-hide success messages
                if (type === 'success') {
                    setTimeout(() => this.hideAlert(), 3000);
                }
            }
            
            hideAlert() {
                this.alertDiv.style.display = 'none';
            }
            
            startResendCooldown() {
                let timeLeft = this.resendCooldown;
                
                this.countdownDiv.style.display = 'block';
                this.resendButton.style.display = 'none';
                
                this.resendTimer = setInterval(() => {
                    this.countdownDiv.textContent = `You can resend in ${timeLeft} seconds`;
                    timeLeft--;
                    
                    if (timeLeft < 0) {
                        clearInterval(this.resendTimer);
                        this.countdownDiv.style.display = 'none';
                        this.resendButton.style.display = 'inline';
                        this.resendButton.disabled = false;
                        this.resendButton.textContent = '📨 Resend Verification Code';
                    }
                }, 1000);
            }
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new EmailVerification();
        });
    </script>
</body>
</html>