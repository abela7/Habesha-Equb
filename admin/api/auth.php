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
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            json_response(false, 'Username and password are required');
        }
        
        try {
            // Get admin by username
            $stmt = $pdo->prepare("SELECT id, username, password, is_active FROM admins WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin || !password_verify($password, $admin['password'])) {
                json_response(false, 'Invalid username or password');
            }
            
            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time(); // Auth guard expects 'login_time', not 'admin_login_time'
            
            json_response(true, 'Login successful', ['redirect' => 'welcome_admin.php']);
            
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            json_response(false, 'Login failed. Please try again.');
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