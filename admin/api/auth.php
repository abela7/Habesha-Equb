<?php
/**
 * HabeshaEqub Admin Authentication API
 * Handles login, registration, logout, and auth checking
 * Returns JSON responses for AJAX calls
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../includes/db.php';

// Enhanced error logging
error_log("Admin API started - Request method: " . $_SERVER['REQUEST_METHOD']);

// Test database connection with better error handling
try {
    $test_stmt = $pdo->query("SELECT 1");
    error_log("Database connection test: SUCCESS");
} catch (PDOException $e) {
    error_log("Database connection test: FAILED - " . $e->getMessage());
    error_log("Database connection test: Error code - " . $e->getCode());
    send_json_response(false, 'Database connection failed: ' . $e->getMessage());
}

// Test admins table with better error handling
try {
    $test_stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $admin_count = $test_stmt->fetchColumn();
    error_log("Admins table test: SUCCESS - Found " . $admin_count . " admins");
} catch (PDOException $e) {
    error_log("Admins table test: FAILED - " . $e->getMessage());
    error_log("Admins table test: Error code - " . $e->getCode());
    
    // Check if table doesn't exist and create it
    if ($e->getCode() == '42S02') { // Table doesn't exist
        error_log("Admins table doesn't exist, attempting to create it");
        try {
            $create_table_sql = "
                CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    language_preference TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $pdo->exec($create_table_sql);
            error_log("Admins table created successfully");
        } catch (PDOException $create_error) {
            error_log("Failed to create admins table: " . $create_error->getMessage());
            send_json_response(false, 'Database setup failed. Please contact administrator.');
        }
    } else {
        send_json_response(false, 'Database table error: ' . $e->getMessage());
    }
}

// Define to skip auth check for utility functions
define('SKIP_ADMIN_AUTH_CHECK', true);
require_once '../includes/admin_auth_guard.php';

// Set JSON header for all responses
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// CORS headers (if needed for development)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Send JSON response and exit
 */
function send_json_response($success, $message = '', $data = []) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ];
    
    error_log("Sending JSON response: " . json_encode($response));
    echo json_encode($response);
    exit;
}

/**
 * Validate and sanitize input data
 */
function validate_input($data, $type = 'text') {
    $data = trim($data);
    
    switch ($type) {
        case 'username':
            if (empty($data)) {
                return ['valid' => false, 'message' => 'Username is required'];
            }
            if (strlen($data) < 3 || strlen($data) > 50) {
                return ['valid' => false, 'message' => 'Username must be 3-50 characters'];
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data)) {
                return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores'];
            }
            return ['valid' => true, 'value' => sanitize_input($data)];
            
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
            return ['valid' => true, 'value' => $data]; // Don't sanitize passwords
            
        default:
            return ['valid' => true, 'value' => sanitize_input($data)];
    }
}

/**
 * Check if username already exists
 */
function username_exists($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $exists = $stmt->fetchColumn() !== false;
        error_log("Username check for '$username': " . ($exists ? 'EXISTS' : 'AVAILABLE'));
        return $exists;
    } catch (PDOException $e) {
        error_log("Username check error: " . $e->getMessage());
        return true; // Assume exists to be safe
    }
}

/**
 * Create new admin account
 */
function create_admin($username, $password) {
    global $pdo;
    
    try {
        error_log("Creating admin account for username: " . $username);
        
        // Hash password with bcrypt
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        error_log("Password hashed successfully");
        
        $stmt = $pdo->prepare("
            INSERT INTO admins (username, password, is_active, language_preference) 
            VALUES (?, ?, 1, 1)
        ");
        
        error_log("Prepared statement for admin creation");
        $stmt->execute([$username, $password_hash]);
        $admin_id = $pdo->lastInsertId();
        error_log("Admin created successfully with ID: " . $admin_id);
        
        return $admin_id;
        
    } catch (PDOException $e) {
        error_log("Admin creation error: " . $e->getMessage());
        error_log("Admin creation error code: " . $e->getCode());
        error_log("Admin creation error trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Authenticate admin login
 */
function authenticate_admin($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, password, is_active 
            FROM admins 
            WHERE username = ? 
            LIMIT 1
        ");
        
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        if (!$admin['is_active']) {
            return ['success' => false, 'message' => 'Account is deactivated. Contact system administrator.'];
        }
        
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Update last login (optional)
            $update_stmt = $pdo->prepare("UPDATE admins SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $update_stmt->execute([$admin['id']]);
            
            return [
                'success' => true, 
                'message' => 'Login successful!',
                'redirect' => 'dashboard.php'
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Authentication failed. Please try again.'];
    }
}

/**
 * Check if admin is authenticated
 */
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true;
}

/**
 * Logout admin
 */
function logout_admin() {
    // Clear session variables
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['login_time']);
    
    // Destroy session if no other important data
    if (empty($_SESSION)) {
        session_destroy();
    }
    
    return ['success' => true, 'message' => 'Logged out successfully'];
}

// ===== MAIN API HANDLER =====

try {
    // Get action from request
    $action = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        error_log("Admin API POST request - Action: " . $action);
        error_log("POST data: " . print_r($_POST, true));
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        error_log("Admin API GET request - Action: " . $action);
    }

    // Validate action
    if (empty($action)) {
        error_log("Admin API error: No action specified");
        send_json_response(false, 'No action specified');
    }

    // Handle different actions
    switch ($action) {
        
        case 'login':
            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                send_json_response(false, 'Security token mismatch. Please refresh and try again.');
            }
            
            // Validate input
            $username_validation = validate_input($_POST['username'] ?? '', 'username');
            if (!$username_validation['valid']) {
                send_json_response(false, $username_validation['message']);
            }
            
            $password_validation = validate_input($_POST['password'] ?? '', 'password');
            if (!$password_validation['valid']) {
                send_json_response(false, $password_validation['message']);
            }
            
            // Attempt authentication
            $auth_result = authenticate_admin(
                $username_validation['value'], 
                $password_validation['value']
            );
            
            send_json_response(
                $auth_result['success'], 
                $auth_result['message'], 
                ['redirect' => $auth_result['redirect'] ?? '']
            );
            break;
            
        case 'register':
            error_log("Processing registration request");
            
            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                error_log("CSRF token validation failed");
                send_json_response(false, 'Security token mismatch. Please refresh and try again.');
            }
            
            error_log("CSRF token validated successfully");
            
            // Validate input
            $username_validation = validate_input($_POST['username'] ?? '', 'username');
            if (!$username_validation['valid']) {
                error_log("Username validation failed: " . $username_validation['message']);
                send_json_response(false, $username_validation['message']);
            }
            
            $password_validation = validate_input($_POST['password'] ?? '', 'password');
            if (!$password_validation['valid']) {
                error_log("Password validation failed: " . $password_validation['message']);
                send_json_response(false, $password_validation['message']);
            }
            
            $confirm_password = $_POST['confirm_password'] ?? '';
            if ($password_validation['value'] !== $confirm_password) {
                error_log("Password confirmation failed");
                send_json_response(false, 'Passwords do not match');
            }
            
            error_log("All validations passed, checking if username exists");
            
            // Check if username already exists
            if (username_exists($username_validation['value'])) {
                error_log("Username already exists: " . $username_validation['value']);
                send_json_response(false, 'Username already exists. Please choose a different one.');
            }
            
            error_log("Username is available, creating admin account");
            
            // Create admin account
            $admin_id = create_admin($username_validation['value'], $password_validation['value']);
            
            if ($admin_id) {
                error_log("Admin account created successfully with ID: " . $admin_id);
                send_json_response(true, 'Admin account created successfully! You can now login.');
            } else {
                error_log("Failed to create admin account");
                send_json_response(false, 'Failed to create admin account. Please try again.');
            }
            break;
            
        case 'logout':
            $logout_result = logout_admin();
            send_json_response($logout_result['success'], $logout_result['message']);
            break;
            
        case 'check_auth':
            $is_authenticated = is_admin_authenticated();
            send_json_response(true, 'Auth status checked', [
                'authenticated' => $is_authenticated,
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'username' => $_SESSION['admin_username'] ?? null
            ]);
            break;
            
        default:
            send_json_response(false, 'Invalid action specified');
            break;
    }

} catch (Exception $e) {
    // Log error for debugging
    error_log("Auth API error: " . $e->getMessage());
    error_log("Auth API error trace: " . $e->getTraceAsString());
    
    // Send generic error message (don't expose internal errors)
    send_json_response(false, 'An unexpected error occurred. Please try again.');
}
?> 