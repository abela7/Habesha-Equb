<?php
/**
 * HabeshaEqub - Secure Admin Authentication API
 * Advanced security implementation with comprehensive protection
 */

// Include security system
require_once '../../includes/security.php';
require_once '../../includes/db.php';

// Set security headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

/**
 * Secure JSON response
 */
function json_response($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => SecurityValidator::sanitizeInput($message, 'html'),
        'data' => $data,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Check if admin is authenticated
 */
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Secure admin authentication with brute force protection
 */
function authenticate_admin($username, $password) {
    global $pdo;
    
    try {
        // Rate limiting for admin login attempts
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateCheck = RateLimiter::checkRateLimit("admin_login_" . $ip . "_" . $username, 3, 1800); // 3 attempts per 30 minutes
        
        if (!$rateCheck['allowed']) {
            SecurityLogger::logSecurityEvent('admin_brute_force_attempt', [
                'ip' => $ip,
                'username' => $username,
                'message' => $rateCheck['message']
            ]);
            return ['success' => false, 'message' => $rateCheck['message']];
        }
        
        // Use secure SQL wrapper
        $stmt = SQLProtection::query($pdo, "
            SELECT id, username, email, password, is_active 
            FROM admins 
            WHERE username = ? AND is_active = 1
            LIMIT 1
        ", [$username]);
        
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($password, $admin['password'])) {
            // Record failed attempt
            RateLimiter::recordAttempt("admin_login_" . $ip . "_" . $username);
            SecurityLogger::logSecurityEvent('failed_admin_login', [
                'ip' => $ip,
                'username' => $username,
                'reason' => !$admin ? 'admin_not_found' : 'invalid_password'
            ]);
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // Reset rate limiting on successful login
        RateLimiter::resetAttempts("admin_login_" . $ip . "_" . $username);
        
        // Log successful admin login
        SecurityLogger::logSecurityEvent('successful_admin_login', [
            'admin_id' => $admin['id'],
            'username' => $username,
            'ip' => $ip
        ]);
        
        return [
            'success' => true,
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Admin authentication error: " . $e->getMessage());
        SecurityLogger::logSecurityEvent('admin_authentication_error', [
            'error' => $e->getMessage(),
            'username' => $username
        ]);
        return ['success' => false, 'message' => 'Authentication system error'];
    }
}

/**
 * Secure admin registration with enhanced security
 */
function create_admin($username, $password) {
    global $pdo;
    
    try {
        // Rate limiting for admin registration
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateCheck = RateLimiter::checkRateLimit("admin_registration_" . $ip, 1, 86400); // 1 attempt per day
        
        if (!$rateCheck['allowed']) {
            SecurityLogger::logSecurityEvent('admin_registration_rate_limit', [
                'ip' => $ip,
                'username' => $username
            ]);
            return false;
        }
        
        // Check if username already exists
        $stmt = SQLProtection::query($pdo, "SELECT id FROM admins WHERE username = ?", [$username]);
        if ($stmt->fetchColumn()) {
            SecurityLogger::logSecurityEvent('duplicate_admin_username', [
                'username' => $username,
                'ip' => $ip
            ]);
            return false;
        }
        
        // Hash password with stronger settings
        $password_hash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
        
        // Create admin with secure SQL wrapper
        $stmt = SQLProtection::query($pdo, "
            INSERT INTO admins (username, password, is_active, created_at) 
            VALUES (?, ?, 1, NOW())
        ", [$username, $password_hash]);
        
        $admin_id = $pdo->lastInsertId();
        
        // Log successful admin creation
        SecurityLogger::logSecurityEvent('successful_admin_registration', [
            'admin_id' => $admin_id,
            'username' => $username,
            'ip' => $ip
        ]);
        
        return $admin_id;
        
    } catch (Exception $e) {
        error_log("Admin creation error: " . $e->getMessage());
        SecurityLogger::logSecurityEvent('admin_registration_error', [
            'error' => $e->getMessage(),
            'username' => $username ?? 'unknown'
        ]);
        return false;
    }
}

// Validate session security
if (!SessionSecurity::validateSession()) {
    json_response(false, 'Session security validation failed');
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SecurityLogger::logSecurityEvent('invalid_admin_request_method', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    json_response(false, 'Invalid request method');
}

// Get and validate action
$action = SecurityValidator::sanitizeInput($_POST['action'] ?? '', 'alphanum');

if (empty($action)) {
    json_response(false, 'Action is required');
}

// Handle actions with enhanced security
switch ($action) {
    case 'login':
        // Verify CSRF token
        if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
            SecurityLogger::logSecurityEvent('admin_csrf_token_mismatch', [
                'action' => 'login',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Enhanced input validation
        $username_validation = SecurityValidator::validateInput($_POST['username'] ?? '', 'username', true);
        $password = $_POST['password'] ?? '';
        
        if (!$username_validation['valid']) {
            json_response(false, $username_validation['message']);
        }
        
        if (empty($password)) {
            json_response(false, 'Password is required');
        }
        
        // Additional security check for suspicious content
        if (SecurityValidator::detectSuspiciousInput($username_validation['value']) || 
            SecurityValidator::detectSuspiciousInput($password)) {
            SecurityLogger::logSecurityEvent('suspicious_admin_login_attempt', [
                'username' => substr($username_validation['value'], 0, 50),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            json_response(false, 'Invalid input detected');
        }
        
        // Attempt authentication
        $auth_result = authenticate_admin($username_validation['value'], $password);
        
        if ($auth_result['success']) {
            // Set secure admin session
            $_SESSION['admin_id'] = $auth_result['admin']['id'];
            $_SESSION['admin_username'] = $auth_result['admin']['username'];
            $_SESSION['admin_email'] = $auth_result['admin']['email'];
            $_SESSION['admin_login_time'] = time();
            session_regenerate_id(true);
            
            json_response(true, 'Login successful', [
                'redirect' => 'dashboard.php'
            ]);
        } else {
            json_response(false, $auth_result['message']);
        }
        break;
        
    case 'register':
        // Verify CSRF token
        if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
            SecurityLogger::logSecurityEvent('admin_csrf_token_mismatch', [
                'action' => 'register',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Enhanced input validation
        $username_validation = SecurityValidator::validateInput($_POST['username'] ?? '', 'username', true);
        $password_validation = SecurityValidator::validateInput($_POST['password'] ?? '', 'password', true);
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (!$username_validation['valid']) {
            json_response(false, $username_validation['message']);
        }
        
        if (!$password_validation['valid']) {
            json_response(false, $password_validation['message']);
        }
        
        if ($password_validation['value'] !== $confirm_password) {
            json_response(false, 'Passwords do not match');
        }
        
        // Additional security checks
        if (SecurityValidator::detectSuspiciousInput($username_validation['value']) || 
            SecurityValidator::detectSuspiciousInput($password_validation['value'])) {
            SecurityLogger::logSecurityEvent('suspicious_admin_registration_attempt', [
                'username' => substr($username_validation['value'], 0, 50),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            json_response(false, 'Invalid input detected');
        }
        
        // Create admin with enhanced security
        $admin_id = create_admin($username_validation['value'], $password_validation['value']);
        
        if ($admin_id) {
            json_response(true, 'Admin account created successfully! You can now login.');
        } else {
            json_response(false, 'Registration failed. Username may already exist or rate limit exceeded.');
        }
        break;
        
    default:
        SecurityLogger::logSecurityEvent('invalid_admin_action_attempt', [
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        json_response(false, 'Invalid action');
}
?> 