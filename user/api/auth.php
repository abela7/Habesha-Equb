<?php
/**
 * SUPER SIMPLE AUTH - Just to get registration working
 */

// Prevent any HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
        throw new Exception('Database not connected');
    }
    
    $database = isset($pdo) ? $pdo : $db;
    
    // Test database
    $test = $database->query("SELECT 1")->fetch();
    if (!$test) {
        throw new Exception('Database test failed');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
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

if ($action !== 'register') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

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
?> 