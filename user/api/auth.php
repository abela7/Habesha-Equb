<?php
/**
 * ENHANCED AUTH - OTP Login System with Better Error Reporting
 */

// Enhanced error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Global error handler to ensure JSON output
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error: $message in $file:$line");
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Server error: ' . $message,
            'debug' => ['file' => basename($file), 'line' => $line]
        ]);
        exit;
    }
});

// Global exception handler
set_exception_handler(function($exception) {
    error_log("PHP Exception: " . $exception->getMessage());
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Server exception: ' . $exception->getMessage(),
            'debug' => ['file' => basename($exception->getFile()), 'line' => $exception->getLine()]
        ]);
        exit;
    }
});

// Set JSON header
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Include database
    require_once __DIR__ . '/../../includes/db.php';
    
    // Check database connection
    if (!isset($pdo) && !isset($db)) {
        throw new Exception('Database variables not found - check includes/db.php');
    }
    
    $database = isset($pdo) ? $pdo : $db;
    
    // Test database with more detailed error info
    $test = $database->query("SELECT 1 as test")->fetch();
    if (!$test || $test['test'] !== 1) {
        throw new Exception('Database query test failed - connection may be broken');
    }
    
    // Test if members table exists
    $members_test = $database->query("SHOW TABLES LIKE 'members'")->fetch();
    if (!$members_test) {
        throw new Exception('Members table not found - database may not be imported');
    }
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'debug' => ['action' => 'database_check']
    ]);
    exit;
}

/**
 * Simple validation
 */
function validate_input($data, $type) {
    $data = trim($data);
    
    switch ($type) {
        case 'name':
            if (empty($data) || strlen($data) < 2) {
                return ['valid' => false, 'message' => 'Name must be at least 2 characters'];
            }
            return ['valid' => true, 'value' => htmlspecialchars($data)];
            
        case 'email':
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'message' => 'Invalid email address'];
            }
            return ['valid' => true, 'value' => strtolower($data)];
            
        case 'phone':
            $clean = preg_replace('/[^0-9+]/', '', $data);
            if (strlen($clean) < 10) {
                return ['valid' => false, 'message' => 'Invalid phone number'];
            }
            return ['valid' => true, 'value' => $clean];
            
        default:
            return ['valid' => true, 'value' => htmlspecialchars($data)];
    }
}

/**
 * Generate member ID
 */
function generate_member_id($first_name, $last_name) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    $number = rand(100, 999);
    return 'HEM-' . $initials . $number;
}

/**
 * Check CSRF token
 */
function check_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Handle the request
$action = $_POST['action'] ?? '';

// Route to different handlers based on action
switch ($action) {
    case 'register':
        handle_register($database);
        break;
    case 'request_otp':
        handle_otp_request($database);
        break;
    case 'verify_otp':
        handle_otp_verification($database);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

/**
 * Handle user registration
 */
function handle_register($database) {

try {
    // Check CSRF
    if (!check_csrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Security token invalid']);
        exit;
    }
    
    // Validate inputs
    $first_name = validate_input($_POST['first_name'] ?? '', 'name');
    $last_name = validate_input($_POST['last_name'] ?? '', 'name');
    $email = validate_input($_POST['email'] ?? '', 'email');
    $phone = validate_input($_POST['phone'] ?? '', 'phone');
    
    // Check validation
    foreach ([$first_name, $last_name, $email, $phone] as $field) {
        if (!$field['valid']) {
            echo json_encode(['success' => false, 'message' => $field['message']]);
            exit;
        }
    }
    
    // Check terms agreement
    if (!isset($_POST['agree_terms']) || $_POST['agree_terms'] !== 'on') {
        echo json_encode(['success' => false, 'message' => 'You must agree to terms']);
        exit;
    }
    
    // Check if email exists
    $stmt = $database->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email['value']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

    // Create member
    $member_id = generate_member_id($first_name['value'], $last_name['value']);
    $username = explode('@', $email['value'])[0];
    $full_name = $first_name['value'] . ' ' . $last_name['value'];
    
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
            1, CURDATE(), 1, NOW()
        )
    ");
    
    $result = $stmt->execute([
        $member_id, $username, $first_name['value'], $last_name['value'], $full_name,
        $email['value'], $phone['value']
    ]);
    
    if ($result) {
        $new_user_id = $database->lastInsertId();
        
        // Store device tracking (simplified)
        $device_fp = 'dv_' . substr(md5($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 16);
        
        try {
            $stmt = $database->prepare("
                INSERT INTO device_tracking (email, device_fingerprint, user_agent, ip_address, created_at, last_seen) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
        $stmt->execute([
                $email['value'], 
                $device_fp, 
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (Exception $e) {
            // Ignore device tracking errors
        }
        
        // Store session data
        $_SESSION['pending_email'] = $email['value'];
        $_SESSION['pending_name'] = $full_name;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!',
            'redirect' => 'waiting-approval.php?email=' . urlencode($email['value'])
        ]);
        
        } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
}

/**
 * Handle OTP request for login
 */
function handle_otp_request($database) {
    try {
        // Check CSRF
        if (!check_csrf($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Security token invalid']);
            exit;
        }
        
        // Validate email
        $email = validate_input($_POST['email'] ?? '', 'email');
        if (!$email['valid']) {
            echo json_encode(['success' => false, 'message' => $email['message']]);
            exit;
        }
        
        // Check if user exists and is approved
        $stmt = $database->prepare("
            SELECT id, first_name, last_name, email, is_approved, is_active 
            FROM members 
            WHERE email = ?
        ");
        $stmt->execute([$email['value']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'No account found with this email address. Please register first.']);
            exit;
        }
        
        if (!$user['is_approved']) {
            echo json_encode(['success' => false, 'message' => 'Your account is pending approval. Please wait for admin approval.']);
            exit;
        }
        
        if (!$user['is_active']) {
            echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact support.']);
            exit;
        }
        
        // Include EmailService
        require_once __DIR__ . '/../../includes/email/EmailService.php';
        
        // Generate and store OTP
        $emailService = new EmailService($database);
        $otp_code = $emailService->generateOTP($user['id'], $user['email'], 'otp_login');
        
        // Send OTP email
        $result = $emailService->send('otp_login', $user['email'], $user['first_name'], [
            'first_name' => $user['first_name'],
            'otp_code' => $otp_code
        ]);
        
        if ($result['success']) {
            // Store user ID in session for OTP verification
            $_SESSION['otp_user_id'] = $user['id'];
            $_SESSION['otp_email'] = $user['email'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login code sent! Check your email for the verification code.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send login code. Please try again.'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("OTP request error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

/**
 * Handle OTP verification and login
 */
function handle_otp_verification($database) {
    try {
        // Check CSRF
        if (!check_csrf($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Security token invalid']);
            exit;
        }
        
        // Validate inputs
        $otp_code = $_POST['otp_code'] ?? '';
        $remember_device = isset($_POST['remember_device']) && $_POST['remember_device'] === 'on';
        
        if (empty($otp_code)) {
            echo json_encode(['success' => false, 'message' => 'Please enter the verification code']);
            exit;
        }
        
        // Check if we have OTP session data
        if (!isset($_SESSION['otp_user_id']) || !isset($_SESSION['otp_email'])) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please request a new code.']);
            exit;
        }
        
        // Include EmailService for OTP verification
        require_once __DIR__ . '/../../includes/email/EmailService.php';
        $emailService = new EmailService($database);
        
        // Verify OTP
        if (!$emailService->verifyOTP($_SESSION['otp_email'], $otp_code, 'otp_login')) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
            exit;
        }
        
        // Get user details
        $stmt = $database->prepare("
            SELECT id, member_id, first_name, last_name, email 
            FROM members 
            WHERE id = ? AND email = ? AND is_approved = 1 AND is_active = 1
        ");
        $stmt->execute([$_SESSION['otp_user_id'], $_SESSION['otp_email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
            exit;
        }
        
        // Update last login
        $stmt = $database->prepare("UPDATE members SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set up user session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['member_id'] = $user['member_id'];
        
        // Handle device remembering (7 days) - with fallback for missing columns
        if ($remember_device) {
            try {
                // Check if device_tracking table has the new columns
                $check_stmt = $database->prepare("SHOW COLUMNS FROM device_tracking LIKE 'device_token'");
                $check_stmt->execute();
                $has_device_token = $check_stmt->fetch();
                
                if ($has_device_token) {
                    // New version with device tokens
                    $device_token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                    
                    $stmt = $database->prepare("
                        INSERT INTO device_tracking (email, device_fingerprint, device_token, expires_at, user_agent, ip_address, created_at, last_seen) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE 
                        device_token = VALUES(device_token), 
                        expires_at = VALUES(expires_at), 
                        last_seen = NOW()
                    ");
                    
                    $device_fp = 'dv_' . substr(md5($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 16);
                    $stmt->execute([
                        $user['email'],
                        $device_fp,
                        $device_token,
                        $expires_at,
                        $_SERVER['HTTP_USER_AGENT'] ?? '',
                        $_SERVER['REMOTE_ADDR'] ?? ''
                    ]);
                    
                    // Set device cookie
                    setcookie('device_token', $device_token, strtotime('+7 days'), '/', '', true, true);
                } else {
                    // Fallback to basic device tracking (old version)
                    $device_fp = 'dv_' . substr(md5($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 16);
                    $stmt = $database->prepare("
                        INSERT INTO device_tracking (email, device_fingerprint, user_agent, ip_address, created_at, last_seen) 
                        VALUES (?, ?, ?, ?, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE last_seen = NOW()
                    ");
                    $stmt->execute([
                        $user['email'],
                        $device_fp,
                        $_SERVER['HTTP_USER_AGENT'] ?? '',
                        $_SERVER['REMOTE_ADDR'] ?? ''
                    ]);
                    
                    error_log("Device remembering disabled: database not updated. Run database_device_update.sql");
                }
                
            } catch (Exception $e) {
                error_log("Device tracking error: " . $e->getMessage());
                // Continue login even if device tracking fails
            }
        }
        
        // Clean up OTP session data
        unset($_SESSION['otp_user_id']);
        unset($_SESSION['otp_email']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Welcome back.',
            'redirect' => 'dashboard.php'
        ]);
        
    } catch (Exception $e) {
        error_log("OTP verification error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}
?> 