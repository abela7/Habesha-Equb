<?php
/**
 * HabeshaEqub - Simple Admin Authentication API
 * Simplified version that works reliably
 */

require_once '../../includes/db.php';

// Start session for login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

/**
 * Simple JSON response
 */
function json_response($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get action
$action = $_POST['action'] ?? '';
if (empty($action)) {
    json_response(false, 'Action is required');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    json_response(false, 'Security token mismatch. Please refresh and try again.');
}

// Handle actions
switch ($action) {
    case 'request_otp':
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) { json_response(false, 'Email is required'); }
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, is_active FROM admins WHERE email = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$admin) { json_response(false, 'Admin account not found or inactive'); }
            require_once '../../includes/email/EmailService.php';
            $mailer = new EmailService($pdo);
            $otp = $mailer->generateOTP($admin['id'], $admin['email'], 'admin_login');
            // Send email using a minimal template
            $res = $mailer->send('otp_login', $admin['email'], $admin['username'] ?? 'Admin', [
                'first_name' => $admin['username'] ?? 'Admin',
                'otp_code' => $otp
            ]);
            if (!empty($res['success'])) {
                $_SESSION['admin_otp_id'] = $admin['id'];
                $_SESSION['admin_otp_email'] = $admin['email'];
                json_response(true, 'Verification code sent to your email');
            } else {
                json_response(false, 'Failed to send verification email');
            }
        } catch (Throwable $e) {
            error_log('Admin OTP request error: '.$e->getMessage());
            json_response(false, 'Server error');
        }
        break;
    case 'verify_otp':
        $otp_code = trim($_POST['otp_code'] ?? '');
        if (empty($otp_code)) { json_response(false, 'Enter the verification code'); }
        if (!isset($_SESSION['admin_otp_id']) || !isset($_SESSION['admin_otp_email'])) {
            json_response(false, 'Session expired. Please request a new code.');
        }
        try {
            require_once '../../includes/email/EmailService.php';
            $mailer = new EmailService($pdo);
            $uid = $mailer->verifyOTP($_SESSION['admin_otp_email'], $otp_code, 'admin_login');
            if (!$uid) { json_response(false, 'Invalid or expired code'); }
            $stmt = $pdo->prepare("SELECT id, username, email, is_active FROM admins WHERE id = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$_SESSION['admin_otp_id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$admin) { json_response(false, 'Admin account not found or inactive'); }
            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();
            unset($_SESSION['admin_otp_id']); unset($_SESSION['admin_otp_email']);
            json_response(true, 'Login successful', ['redirect' => 'welcome_admin.php']);
        } catch (Throwable $e) {
            error_log('Admin OTP verify error: '.$e->getMessage());
            json_response(false, 'Server error');
        }
        break;
        
    case 'register':
        // SECURITY FIX: Admin registration disabled for security
        // Public admin registration is a critical security vulnerability
        json_response(false, 'Admin registration is disabled. Contact system administrator.');
        
        /* DISABLED FOR SECURITY - Enable only with proper authorization
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // SECURITY: Only allow if current admin is authenticated and authorized
        if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
            json_response(false, 'Only existing admins can create new admin accounts');
        }
        
        if (empty($username) || empty($password)) {
            json_response(false, 'Username and password are required');
        }
        
        // SECURITY FIX: Strengthen password requirements
        if (strlen($password) < 12) {
            json_response(false, 'Password must be at least 12 characters long');
        }
        
        if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/', $password)) {
            json_response(false, 'Password must contain uppercase, lowercase, number, and special character');
        }
        
        try {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                json_response(false, 'Username already exists. Please choose a different username.');
            }
            
            // Create admin account
            $password_hash = password_hash($password, PASSWORD_ARGON2ID);
            
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, is_active, created_at) VALUES (?, ?, 1, NOW())");
            $stmt->execute([$username, $password_hash]);
            
            json_response(true, 'Admin account created successfully!');
            
        } catch (Exception $e) {
            error_log("Admin registration error: " . $e->getMessage());
            json_response(false, 'Registration failed. Please try again.');
        }
        */
        break;
        
    default:
        json_response(false, 'Invalid action');
}
?> 