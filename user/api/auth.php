<?php
/**
 * HabeshaEqub - Secure User Authentication API
 * Advanced security implementation with comprehensive protection
 */

// Include security system
require_once '../../includes/security.php';
require_once '../../includes/db.php';
require_once '../../languages/translator.php';

// Set security headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

/**
 * Enhanced input sanitization with security validation
 */
function validate_user_input($data, $type = 'text') {
    // Use new security validator
    $validation = SecurityValidator::validateInput($data, $type, true);
    
    if (!$validation['valid']) {
        return $validation;
    }
    
    // Additional security check for suspicious content
    if (SecurityValidator::detectSuspiciousInput($data)) {
        SecurityLogger::logSecurityEvent('suspicious_input_attempt', [
            'type' => $type,
            'value' => substr($data, 0, 100),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        return ['valid' => false, 'message' => 'Invalid input detected'];
    }
    
    return $validation;
}

/**
 * Secure user registration with rate limiting
 */
function create_member($full_name, $email, $phone, $password) {
    global $pdo;
    
    try {
        // Additional security: Check for duplicate attempts from same IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateCheck = RateLimiter::checkRateLimit("registration_" . $ip, 3, 3600); // 3 attempts per hour
        
        if (!$rateCheck['allowed']) {
            SecurityLogger::logSecurityEvent('registration_rate_limit', [
                'ip' => $ip,
                'email' => $email
            ]);
            return false;
        }
        
        // Hash password with stronger settings
        $password_hash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
        
        // Generate secure username from email
        $username_base = SecurityValidator::sanitizeInput(explode('@', $email)[0], 'alphanum');
        $username = $username_base;
        
        // Check for username uniqueness
        $counter = 1;
        while (user_username_exists($username)) {
            $username = $username_base . $counter;
            $counter++;
            if ($counter > 100) { // Prevent infinite loop
                throw new Exception("Unable to generate unique username");
            }
        }
        
        // Use secure SQL wrapper
        $stmt = SQLProtection::query($pdo, "
            INSERT INTO members (
                full_name, username, email, phone, password, 
                status, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, 'active', 1, NOW())
        ", [$full_name, $username, $email, $phone, $password_hash]);
        
        $member_id = $pdo->lastInsertId();
        
        // Log successful registration
        SecurityLogger::logSecurityEvent('successful_registration', [
            'member_id' => $member_id,
            'email' => $email,
            'ip' => $ip
        ]);
        
        return $member_id;
        
    } catch (Exception $e) {
        error_log("Secure member creation error: " . $e->getMessage());
        SecurityLogger::logSecurityEvent('registration_error', [
            'error' => $e->getMessage(),
            'email' => $email ?? 'unknown'
        ]);
        return false;
    }
}

/**
 * Secure authentication with brute force protection
 */
function authenticate_member($email, $password) {
    global $pdo;
    
    try {
        // Rate limiting for login attempts
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateCheck = RateLimiter::checkRateLimit("login_" . $ip . "_" . $email, 5, 900); // 5 attempts per 15 minutes
        
        if (!$rateCheck['allowed']) {
            SecurityLogger::logSecurityEvent('brute_force_attempt', [
                'ip' => $ip,
                'email' => $email,
                'message' => $rateCheck['message']
            ]);
            return ['success' => false, 'message' => $rateCheck['message']];
        }
        
        // Use secure SQL wrapper
        $stmt = SQLProtection::query($pdo, "
            SELECT id, username, full_name, email, password, status, is_active 
            FROM members 
            WHERE email = ? 
            LIMIT 1
        ", [$email]);
        
        $member = $stmt->fetch();
        
        if (!$member || !password_verify($password, $member['password'])) {
            // Record failed attempt
            RateLimiter::recordAttempt("login_" . $ip . "_" . $email);
            SecurityLogger::logSecurityEvent('failed_login_attempt', [
                'ip' => $ip,
                'email' => $email,
                'reason' => !$member ? 'user_not_found' : 'invalid_password'
            ]);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        if (!$member['is_active']) {
            return ['success' => false, 'message' => 'Your account is inactive. Please contact support.'];
        }
        
        if ($member['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is pending approval. Please wait for admin confirmation.'];
        }
        
        // Reset rate limiting on successful login
        RateLimiter::resetAttempts("login_" . $ip . "_" . $email);
        
        // Log successful login
        SecurityLogger::logSecurityEvent('successful_login', [
            'member_id' => $member['id'],
            'email' => $email,
            'ip' => $ip
        ]);
        
        return [
            'success' => true,
            'member' => [
                'id' => $member['id'],
                'username' => $member['username'],
                'full_name' => $member['full_name'],
                'email' => $member['email']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        SecurityLogger::logSecurityEvent('authentication_error', [
            'error' => $e->getMessage(),
            'email' => $email
        ]);
        return ['success' => false, 'message' => 'Authentication system error'];
    }
}

/**
 * Check if username exists (secure)
 */
function user_username_exists($username) {
    global $pdo;
    
    try {
        $stmt = SQLProtection::query($pdo, "SELECT id FROM members WHERE username = ? LIMIT 1", [$username]);
        return $stmt->fetchColumn() !== false;
    } catch (Exception $e) {
        error_log("Username check error: " . $e->getMessage());
        return true; // Assume exists to be safe
    }
}

/**
 * Check if email exists (secure)
 */
function user_email_exists($email) {
    global $pdo;
    
    try {
        $stmt = SQLProtection::query($pdo, "SELECT id FROM members WHERE email = ? LIMIT 1", [$email]);
        return $stmt->fetchColumn() !== false;
    } catch (Exception $e) {
        error_log("Email check error: " . $e->getMessage());
        return true; // Assume exists to be safe
    }
}

/**
 * Secure JSON response
 */
function send_json_response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => SecurityValidator::sanitizeInput($message, 'html'),
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate session security
if (!SessionSecurity::validateSession()) {
    send_json_response(false, 'Session security validation failed');
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SecurityLogger::logSecurityEvent('invalid_request_method', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    send_json_response(false, 'Invalid request method');
}

// Get and validate action
$action = SecurityValidator::sanitizeInput($_POST['action'] ?? '', 'alphanum');

if (empty($action)) {
    send_json_response(false, 'Action is required');
}

// Handle actions with enhanced security
switch ($action) {
    case 'login':
        // Verify CSRF token
        if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
            SecurityLogger::logSecurityEvent('csrf_token_mismatch', [
                'action' => 'login',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            send_json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Validate input with enhanced security
        $email_validation = validate_user_input($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        
        if (!$email_validation['valid']) {
            send_json_response(false, $email_validation['message']);
        }
        
        if (empty($password)) {
            send_json_response(false, 'Password is required');
        }
        
        // Attempt authentication
        $auth_result = authenticate_member($email_validation['value'], $password);
        
        if ($auth_result['success']) {
            // Set secure session
            $_SESSION['user_id'] = $auth_result['member']['id'];
            $_SESSION['username'] = $auth_result['member']['username'];
            $_SESSION['full_name'] = $auth_result['member']['full_name'];
            $_SESSION['email'] = $auth_result['member']['email'];
            $_SESSION['login_time'] = time();
            session_regenerate_id(true);
            
            send_json_response(true, 'Login successful', [
                'redirect' => 'dashboard.php'
            ]);
        } else {
            send_json_response(false, $auth_result['message']);
        }
        break;
        
    case 'register':
        // Verify CSRF token
        if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
            SecurityLogger::logSecurityEvent('csrf_token_mismatch', [
                'action' => 'register',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            send_json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Enhanced input validation
        $validations = [
            'full_name' => validate_user_input($_POST['full_name'] ?? '', 'name'),
            'email' => validate_user_input($_POST['email'] ?? '', 'email'),
            'phone' => validate_user_input($_POST['phone'] ?? '', 'phone'),
            'password' => validate_user_input($_POST['password'] ?? '', 'password')
        ];
        
        // Check for validation errors
        foreach ($validations as $field => $validation) {
            if (!$validation['valid']) {
                send_json_response(false, $validation['message']);
            }
        }
        
        // Check password confirmation
        $confirm_password = $_POST['confirm_password'] ?? '';
        if ($validations['password']['value'] !== $confirm_password) {
            send_json_response(false, 'Passwords do not match');
        }
        
        // Check terms agreement
        if (!isset($_POST['agree_terms']) || $_POST['agree_terms'] !== 'on') {
            send_json_response(false, 'You must agree to the terms and conditions');
        }
        
        // Check if email already exists
        if (user_email_exists($validations['email']['value'])) {
            SecurityLogger::logSecurityEvent('duplicate_email_registration', [
                'email' => $validations['email']['value'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            send_json_response(false, 'Email already registered. Please use a different email.');
        }
        
        // Create member account with enhanced security
        $member_id = create_member(
            $validations['full_name']['value'],
            $validations['email']['value'],
            $validations['phone']['value'],
            $validations['password']['value']
        );
        
        if ($member_id) {
            send_json_response(true, 'Registration successful! You can now login with your credentials.');
        } else {
            send_json_response(false, 'Registration failed. Please try again later.');
        }
        break;
        
    default:
        SecurityLogger::logSecurityEvent('invalid_action_attempt', [
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        send_json_response(false, 'Invalid action');
}
?> 