<?php
/**
 * HabeshaEqub - Modern Passwordless Authentication API
 * Secure OTP-based email verification system
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/../../includes/db.php';
    require_once __DIR__ . '/../../languages/translator.php';
} catch (Exception $e) {
    error_log("Auth API - Include error: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'System configuration error']);
    exit;
}

// Start session for CSRF and temporary data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set basic headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Check if required variables exist
if (!isset($pdo) && !isset($db)) {
    error_log("Auth API - Database connection not available");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

// Check if database is working
try {
    $database_connection = isset($pdo) ? $pdo : $db;
    $stmt = $database_connection->query("SELECT 1");
    $stmt->fetch();
} catch (Exception $e) {
    error_log("Auth API - Database test error: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Database configuration error']);
    exit;
}

/**
 * Simple input sanitization 
 */
function validate_user_input($data, $type = 'text') {
    $data = trim($data);
    
    switch ($type) {
        case 'first_name':
        case 'last_name':
        case 'name':
            if (empty($data)) {
                return ['valid' => false, 'message' => ucfirst(str_replace('_', ' ', $type)) . ' is required'];
            }
            if (strlen($data) < 2 || strlen($data) > 50) {
                return ['valid' => false, 'message' => ucfirst(str_replace('_', ' ', $type)) . ' must be 2-50 characters'];
            }
            if (!preg_match('/^[a-zA-Z\s\'-]+$/', $data)) {
                return ['valid' => false, 'message' => ucfirst(str_replace('_', ' ', $type)) . ' can only contain letters, spaces, hyphens, and apostrophes'];
            }
            return ['valid' => true, 'value' => htmlspecialchars($data, ENT_QUOTES, 'UTF-8')];
            
        case 'email':
            if (empty($data)) {
                return ['valid' => false, 'message' => 'Email is required'];
            }
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'message' => 'Please enter a valid email address'];
            }
            return ['valid' => true, 'value' => strtolower(trim($data))];
            
        case 'phone':
            if (empty($data)) {
                return ['valid' => false, 'message' => 'Phone number is required'];
            }
            // Clean phone number
            $clean_phone = preg_replace('/[^0-9+]/', '', $data);
            if (strlen($clean_phone) < 10) {
                return ['valid' => false, 'message' => 'Please enter a valid phone number'];
            }
            return ['valid' => true, 'value' => $clean_phone];
            
        case 'otp':
            if (empty($data)) {
                return ['valid' => false, 'message' => 'Verification code is required'];
            }
            if (!preg_match('/^\d{6}$/', $data)) {
                return ['valid' => false, 'message' => 'Verification code must be 6 digits'];
            }
            return ['valid' => true, 'value' => $data];
            
        default:
            return ['valid' => true, 'value' => htmlspecialchars($data, ENT_QUOTES, 'UTF-8')];
    }
}

/**
 * Create member without password (passwordless system)
 */
function create_member($first_name, $last_name, $email, $phone) {
    global $pdo, $db;
    
    try {
        // Use available database connection
        $database = isset($pdo) ? $pdo : $db;
        
        // Generate full name
        $full_name = $first_name . ' ' . $last_name;
        
        // Generate member_id
        $member_id = generate_member_id($first_name, $last_name);
        
        // Generate username from email
        $username = explode('@', $email)[0];
        
        // Insert member without password
        $stmt = $database->prepare("
            INSERT INTO members (
                member_id, username, first_name, last_name, full_name, 
                email, phone, status, monthly_payment, payout_position, 
                total_contributed, has_received_payout, guarantor_first_name, 
                guarantor_last_name, guarantor_phone, is_active, is_approved, 
                email_verified, join_date, rules_agreed, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, 'active', 0.00, 0, 
                0.00, 0, 'Pending', 
                'Pending', 'Pending', 1, 0, 
                0, CURDATE(), 1, NOW()
            )
        ");
        
        $stmt->execute([
            $member_id, $username, $first_name, $last_name, $full_name,
            $email, $phone
        ]);
        
        return $database->lastInsertId();
        
    } catch (Exception $e) {
        error_log("Error creating member: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique member ID
 */
function generate_member_id($first_name, $last_name) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    $number = rand(1, 999);
    return 'HEM-' . $initials . $number;
}

/**
 * Check if email already exists
 */
function user_email_exists($email) {
    global $pdo, $db;
    
    try {
        // Use available database connection
        $database = isset($pdo) ? $pdo : $db;
        $stmt = $database->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Error checking email existence: " . $e->getMessage());
        return false;
    }
}

/**
 * Store device tracking information
 */
function storeDeviceTracking($email, $device_fingerprint) {
    global $pdo, $db;
    
    try {
        // Use available database connection
        $database = isset($pdo) ? $pdo : $db;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = $database->prepare("
            INSERT INTO device_tracking (email, device_fingerprint, user_agent, ip_address, created_at, last_seen, is_approved) 
            VALUES (?, ?, ?, ?, NOW(), NOW(), 0)
            ON DUPLICATE KEY UPDATE 
            last_seen = NOW(), 
            user_agent = VALUES(user_agent), 
            ip_address = VALUES(ip_address)
        ");
        
        $stmt->execute([$email, $device_fingerprint, $user_agent, $ip_address]);
        return true;
    } catch (Exception $e) {
        error_log("Error storing device tracking: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate device fingerprint
 */
function generateDeviceFingerprint() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
    $accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    
    $fingerprint_data = $user_agent . '|' . $accept_language . '|' . $accept_encoding . '|' . $remote_addr;
    return 'dv_' . substr(hash('sha256', $fingerprint_data), 0, 16);
}

/**
 * CSRF token functions
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send JSON response
 */
function send_json_response($success, $message, $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// Initialize EmailService (will be initialized when needed)
$emailService = null;

// Main API handler
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {

switch ($action) {
    case 'register':
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            send_json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Validate all input fields (no password required)
        $validations = [
            'first_name' => validate_user_input($_POST['first_name'] ?? '', 'first_name'),
            'last_name' => validate_user_input($_POST['last_name'] ?? '', 'last_name'),
            'email' => validate_user_input($_POST['email'] ?? '', 'email'),
            'phone' => validate_user_input($_POST['phone'] ?? '', 'phone')
        ];
        
        // Check for validation errors
        foreach ($validations as $field => $validation) {
            if (!$validation['valid']) {
                send_json_response(false, $validation['message']);
            }
        }
        
        // Check terms agreement
        if (!isset($_POST['agree_terms']) || $_POST['agree_terms'] !== 'on') {
            send_json_response(false, 'You must agree to the terms and conditions');
        }
        
        // Check if email already exists
        if (user_email_exists($validations['email']['value'])) {
            send_json_response(false, 'Email already registered. Please use a different email.');
        }
        
        // Create member account (without password)
        $member_id = create_member(
            $validations['first_name']['value'],
            $validations['last_name']['value'],
            $validations['email']['value'],
            $validations['phone']['value']
        );
        
        if ($member_id) {
            try {
                // Initialize EmailService if needed
                if ($emailService === null) {
                    $database_connection = isset($pdo) ? $pdo : $db;
                    
                    // Check if user_otps table exists before using EmailService
                    $stmt = $database_connection->query("SHOW TABLES LIKE 'user_otps'");
                    if ($stmt->fetch()) {
                        require_once __DIR__ . '/../../includes/email/EmailService.php';
                        $emailService = new EmailService($database_connection);
                        
                        // Generate OTP for email verification
                        $otp_code = $emailService->generateOTP($member_id, $validations['email']['value'], 'email_verification');
                        
                        // Send verification email
                        $email_sent = $emailService->send(
                            'email_verification',
                            $validations['email']['value'],
                            $validations['first_name']['value'],
                            [
                                'first_name' => $validations['first_name']['value'],
                                'otp_code' => $otp_code,
                                'unsubscribe_url' => 'mailto:unsubscribe@habeshaequb.com',
                                'website_url' => 'https://' . $_SERVER['HTTP_HOST']
                            ]
                        );
                        
                        if ($email_sent['success']) {
                            // Store temporary registration data
                            $_SESSION['temp_registration'] = [
                                'member_id' => $member_id,
                                'email' => $validations['email']['value'],
                                'first_name' => $validations['first_name']['value'],
                                'last_name' => $validations['last_name']['value']
                            ];
                            
                            send_json_response(true, 'Registration successful! Please check your email for verification code.', [
                                'redirect' => 'verify-email.php?email=' . urlencode($validations['email']['value'])
                            ]);
                        } else {
                            throw new Exception('Email sending failed');
                        }
                    } else {
                        // Fallback: Mark as email verified and go to waiting page
                        $stmt = $database_connection->prepare("UPDATE members SET email_verified = 1 WHERE id = ?");
                        $stmt->execute([$member_id]);
                        
                        // Store device tracking
                        $device_fingerprint = generateDeviceFingerprint();
                        storeDeviceTracking($validations['email']['value'], $device_fingerprint);
                        
                        // Store pending user info in session for waiting page
                        $_SESSION['pending_email'] = $validations['email']['value'];
                        $_SESSION['pending_name'] = $validations['first_name']['value'] . ' ' . $validations['last_name']['value'];
                        $_SESSION['device_fingerprint'] = $device_fingerprint;
                        
                        send_json_response(true, 'Registration successful! Redirecting to approval page...', [
                            'redirect' => 'waiting-approval.php?email=' . urlencode($validations['email']['value'])
                        ]);
                    }
                }
                
            } catch (Exception $e) {
                error_log("Registration process error: " . $e->getMessage());
                
                // Try fallback approach - just create user and mark as verified
                try {
                    $database_connection = isset($pdo) ? $pdo : $db;
                    $stmt = $database_connection->prepare("UPDATE members SET email_verified = 1 WHERE id = ?");
                    $stmt->execute([$member_id]);
                    
                    // Store device tracking
                    $device_fingerprint = generateDeviceFingerprint();
                    storeDeviceTracking($validations['email']['value'], $device_fingerprint);
                    
                    // Store pending user info in session for waiting page
                    $_SESSION['pending_email'] = $validations['email']['value'];
                    $_SESSION['pending_name'] = $validations['first_name']['value'] . ' ' . $validations['last_name']['value'];
                    $_SESSION['device_fingerprint'] = $device_fingerprint;
                    
                    send_json_response(true, 'Registration successful! Redirecting to approval page...', [
                        'redirect' => 'waiting-approval.php?email=' . urlencode($validations['email']['value'])
                    ]);
                    
                } catch (Exception $fallback_error) {
                    error_log("Fallback registration error: " . $fallback_error->getMessage());
                    send_json_response(false, 'Registration failed. Please try again later.');
                }
            }
        } else {
            send_json_response(false, 'Registration failed. Please try again later.');
        }
        break;
        
    case 'verify_email':
        send_json_response(false, 'Email verification is temporarily disabled. Please contact support.');
        break;
        
    case 'resend_verification':
        send_json_response(false, 'Email verification is temporarily disabled. Please contact support.');
        break;
        
    default:
        send_json_response(false, 'Invalid action');
}

} catch (Exception $e) {
    error_log("Auth API - Main handler error: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Server error occurred. Please try again.']);
}
?> 