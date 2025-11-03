<?php
/**
 * HabeshaEqub Admin Login Page
 * Beautiful, mobile-first login interface with AJAX functionality
 */

// Include database and start session
require_once '../includes/db.php';
require_once '../languages/translator.php';

// Set default language to Amharic for admin login
if (!isset($_SESSION['app_language'])) {
    setLanguage('am');
}

// Include admin auth guard functions (but skip auth check for login page)
define('SKIP_ADMIN_AUTH_CHECK', true);
require_once 'includes/admin_auth_guard.php';

// Redirect if already logged in
if (is_admin_authenticated()) {
    header('Location: welcome_admin.php');
    exit;
}

// Handle logout message
$message = '';
$message_type = 'info';
if (isset($_GET['msg'])) {
    $message = sanitize_input($_GET['msg']);
    $message_type = 'info';
}

// Generate CSRF token for security
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - HabeshaEqub</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="../assets/css/style.css" as="style">
    <link rel="preload" href="../assets/js/auth.js" as="script">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon and meta tags -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../Pictures/Icon/apple-icon-180x180.png">
    <meta name="description" content="Secure admin login for HabeshaEqub management system">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- iPhone/mobile specific meta tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HabeshaEqub Admin">
    
    <!-- PWA Support -->
    <?php include '../includes/pwa-head.php'; ?>
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body>
    <!-- Main authentication wrapper -->
    <div class="auth-wrapper">
        <div class="auth-card fade-in">
            
            <!-- Logo and branding section -->
            <div class="auth-logo">
                <img src="../Pictures/Main Logo.png" alt="HabeshaEqub Logo" class="logo-img">
                <p>Admin Panel Login</p>
            </div>

            <!-- Alert messages (hidden by default) -->
            <div class="alert alert-success" id="successAlert"></div>
            <div class="alert alert-error" id="errorAlert"></div>
            <div class="alert alert-info" id="infoAlert"></div>
            
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> show">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- OTP Login form (email only) -->
            <form id="otpForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div id="stepEmail">
                    <div class="form-group">
                        <label for="adminEmail" class="form-label">Admin Email</label>
                        <input type="email" id="adminEmail" name="email" class="form-control" placeholder="name@company.com" autocomplete="email" required>
                        <div class="error-message" id="emailError"></div>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" id="btnRequestOtp">Send Login Code</button>
                </div>
                <div id="stepCode" style="display:none;">
                    <div class="form-group">
                        <label for="otpCode" class="form-label">Enter 6-digit Code</label>
                        <input type="text" id="otpCode" name="otp_code" class="form-control" inputmode="numeric" pattern="\\d{6}" maxlength="6" placeholder="000000" required>
                        <div class="error-message" id="otpError"></div>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" id="btnVerifyOtp">Verify & Login</button>
                    <button type="button" class="btn btn-secondary btn-block" id="btnResend" style="margin-top:8px;">Resend Code</button>
                </div>
            </form>

            <!-- Additional links removed for security -->

        </div>
    </div>

    <!-- JavaScript -->
    <script src="../assets/js/auth.js"></script>
    
    <!-- Optional: Add loading indicator for slow connections -->
    <script>
        // Show loading indicator if page takes too long
        window.addEventListener('load', function() {
            document.body.classList.add('loaded');
        });
        
        // Focus first input on page load (accessibility)
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.getElementById('adminEmail');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
            const api = 'api/auth.php';
            const csrf = '<?php echo htmlspecialchars($csrf_token); ?>';
            const stepEmail = document.getElementById('stepEmail');
            const stepCode = document.getElementById('stepCode');
            const btnReq = document.getElementById('btnRequestOtp');
            const btnVer = document.getElementById('btnVerifyOtp');
            const btnRes = document.getElementById('btnResend');
            btnReq.addEventListener('click', async ()=>{
                const email = document.getElementById('adminEmail').value.trim();
                if (!email) { alert('Enter your admin email'); return; }
                const fd = new FormData(); fd.append('action','request_otp'); fd.append('csrf_token', csrf); fd.append('email', email);
                const r = await fetch(api, { method:'POST', body: fd }); const d = await r.json();
                if (d && d.success){ stepEmail.style.display='none'; stepCode.style.display='block'; document.getElementById('otpCode').focus(); } else { alert(d.message||'Failed'); }
            });
            async function verifyNow(){
                const code = document.getElementById('otpCode').value.trim(); if (!code) { alert('Enter the code'); return; }
                const fd = new FormData(); fd.append('action','verify_otp'); fd.append('csrf_token', csrf); fd.append('otp_code', code);
                const r = await fetch(api, { method:'POST', body: fd }); const d = await r.json();
                if (d && d.success){ window.location.href = d.data && d.data.redirect ? d.data.redirect : 'welcome_admin.php'; } else { alert(d.message||'Invalid code'); }
            }
            btnVer.addEventListener('click', verifyNow);
            document.getElementById('otpCode').addEventListener('keydown', (e)=>{
                if (e.key === 'Enter') { e.preventDefault(); verifyNow(); }
            });
            btnRes.addEventListener('click', ()=>{ btnReq.click(); });
        });
    </script>

    <!-- Inline critical CSS for faster loading (optional optimization) -->
    <style>
        /* Critical above-the-fold styles */
        body:not(.loaded) {
            opacity: 0.9;
        }
        
        body.loaded {
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        /* Prevent flash of unstyled content */
        .auth-wrapper {
            visibility: visible;
        }
        
        /* Logo styling */
        .logo-img {
            max-width: 120px;
            height: auto;
            margin: 0 auto 12px auto;
            border-radius: 8px;
            display: block;
        }
        
        /* Password toggle functionality */
        .password-input-wrapper {
            position: relative;
        }
        
        .password-input {
            padding-right: 50px !important;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--color-teal);
        }
        
        .password-icon {
            font-size: 18px;
            user-select: none;
        }
        
        @media (max-width: 480px) {
            .logo-img {
                max-width: 100px;
                margin: 0 auto 10px auto;
            }
        }
    </style>
    
    <!-- PWA Footer -->
    <?php include '../includes/pwa-footer.php'; ?>
</body>
</html> 