<?php
/**
 * HabeshaEqub - Secure User Authentication API
 * Fixed to allow legitimate registrations
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../languages/translator.php';

// Set basic headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

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
            
        case 'password':
            if (empty($data)) {
                return ['valid' => false, 'message' => 'Password is required'];
            }
            if (strlen($data) < 6) {
                return ['valid' => false, 'message' => 'Password must be at least 6 characters'];
            }
            if (!preg_match('/(?=.*[a-zA-Z])(?=.*\d)/', $data)) {
                return ['valid' => false, 'message' => 'Password must contain both letters and numbers'];
            }
            return ['valid' => true, 'value' => $data];
            
        default:
            return ['valid' => true, 'value' => htmlspecialchars($data, ENT_QUOTES, 'UTF-8')];
    }
}

/**
 * Create member with all required fields
 */
function create_member($first_name, $last_name, $email, $phone, $password) {
    global $pdo;
    
    try {
        // Generate full name
        $full_name = $first_name . ' ' . $last_name;
        
        // Hash password securely
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Generate member_id
        $member_id = generate_member_id($first_name, $last_name);
        
        // Generate username from email
        $username_base = explode('@', $email)[0];
        $username = $username_base;
        
        // Check for username uniqueness
        $counter = 1;
        while (user_username_exists($username)) {
            $username = $username_base . $counter;
            $counter++;
            if ($counter > 50) {
                throw new Exception("Unable to generate unique username");
            }
        }
        
        // Get next available payout position
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(payout_position), 0) + 1 as next_position FROM members WHERE payout_position > 0");
        $stmt->execute();
        $next_position = $stmt->fetchColumn();
        
        // Insert with ALL required fields
        $stmt = $pdo->prepare("
            INSERT INTO members (
                member_id, first_name, last_name, full_name, username, email, phone, password,
                monthly_payment, payout_position, guarantor_first_name, guarantor_last_name, guarantor_phone,
                language_preference, status, is_active, join_date, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, CURDATE(), NOW())
        ");
        
        $result = $stmt->execute([
            $member_id,
            $first_name, 
            $last_name, 
            $full_name, 
            $username, 
            $email, 
            $phone, 
            $password_hash,
            0.00, // Default monthly payment - to be set later
            $next_position, // Auto-assign next position
            'Pending', // Default guarantor info - to be updated later
            'Pending',
            'Pending',
            0 // Default to English (0=English, 1=Amharic)
        ]);
        
        if ($result) {
            return $pdo->lastInsertId();
        } else {
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Member creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique member ID
 */
function generate_member_id($first_name, $last_name) {
    global $pdo;
    
    // Create base ID from initials
    $first_initial = strtoupper(substr($first_name, 0, 1));
    $last_initial = strtoupper(substr($last_name, 0, 1));
    
    $base_id = "HEM-{$first_initial}{$last_initial}";
    
    // Find next available number
    $counter = 1;
    do {
        $member_id = $base_id . $counter;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
        $stmt->execute([$member_id]);
        $exists = $stmt->fetchColumn() > 0;
        $counter++;
    } while ($exists && $counter <= 999);
    
    return $member_id;
}

/**
 * Authenticate member login
 */
function authenticate_member($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, first_name, last_name, full_name, email, password, is_approved, is_active 
            FROM members 
            WHERE email = ? 
            LIMIT 1
        ");
        
        $stmt->execute([$email]);
        $member = $stmt->fetch();
        
        if (!$member) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        if (!$member['is_active']) {
            return ['success' => false, 'message' => 'Your account has been declined. Please contact support.'];
        }
        
        if (!$member['is_approved']) {
            return [
                'success' => false, 
                'message' => 'Your account is pending approval. Please wait for admin confirmation.',
                'redirect' => 'waiting-approval.php?email=' . urlencode($email)
            ];
        }
        
        if (password_verify($password, $member['password'])) {
            return [
                'success' => true,
                'member' => [
                    'id' => $member['id'],
                    'username' => $member['username'],
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                    'full_name' => $member['full_name'],
                    'email' => $member['email']
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Authentication failed. Please try again.'];
    }
}

/**
 * Check if username exists
 */
function user_username_exists($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() !== false;
    } catch (Exception $e) {
        error_log("Username check error: " . $e->getMessage());
        return true;
    }
}

/**
 * Check if email exists
 */
function user_email_exists($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() !== false;
    } catch (Exception $e) {
        error_log("Email check error: " . $e->getMessage());
        return true;
    }
}

/**
 * JSON response
 */
function send_json_response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Invalid request method');
}

// Get action
$action = $_POST['action'] ?? '';

if (empty($action)) {
    send_json_response(false, 'Action is required');
}

// Handle actions
switch ($action) {
    case 'login':
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            send_json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Validate input
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
            // Set complete session data for authentication
            $_SESSION['user_id'] = $auth_result['member']['id'];
            $_SESSION['user_logged_in'] = true;  // CRITICAL: Required by auth_guard
            $_SESSION['user_login_time'] = time(); // CRITICAL: Required by auth_guard
            $_SESSION['username'] = $auth_result['member']['username'];
            $_SESSION['first_name'] = $auth_result['member']['first_name'];
            $_SESSION['last_name'] = $auth_result['member']['last_name'];
            $_SESSION['full_name'] = $auth_result['member']['full_name'];
            $_SESSION['email'] = $auth_result['member']['email'];
            $_SESSION['login_time'] = time(); // Legacy compatibility
            
            send_json_response(true, 'Login successful', [
                'redirect' => 'dashboard.php'
            ]);
        } else {
            send_json_response(false, $auth_result['message']);
        }
        break;
        
    case 'register':
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            send_json_response(false, 'Security token mismatch. Please refresh and try again.');
        }
        
        // Validate all input fields
        $validations = [
            'first_name' => validate_user_input($_POST['first_name'] ?? '', 'first_name'),
            'last_name' => validate_user_input($_POST['last_name'] ?? '', 'last_name'),
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
            send_json_response(false, 'Email already registered. Please use a different email.');
        }
        
        // Create member account
        $member_id = create_member(
            $validations['first_name']['value'],
            $validations['last_name']['value'],
            $validations['email']['value'],
            $validations['phone']['value'],
            $validations['password']['value']
        );
        
        if ($member_id) {
            // Store pending user info in session for waiting page
            $_SESSION['pending_email'] = $validations['email']['value'];
            $_SESSION['pending_name'] = $validations['first_name']['value'] . ' ' . $validations['last_name']['value'];
            
            send_json_response(true, 'Registration successful! Your application is now under review.', [
                'redirect' => 'waiting-approval.php?email=' . urlencode($validations['email']['value'])
            ]);
        } else {
            send_json_response(false, 'Registration failed. Please try again later.');
        }
        break;
        
    default:
        send_json_response(false, 'Invalid action');
}
?> 