<?php
/**
 * HabeshaEqub Admin Authentication Guard
 * Centralized authentication and security protection for admin section
 * Include this file at the top of all protected admin pages
 */

// Include secure session configuration and database functions
require_once __DIR__ . '/admin_session_config.php';
require_once __DIR__ . '/../../includes/db.php';

// Only set headers if this is not an API call
if (!defined('SKIP_ADMIN_AUTH_CHECK') || !strpos($_SERVER['REQUEST_URI'], '/api/')) {
    // Security headers for admin section
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

    // Force HTTPS in production (optional)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

/**
 * Check if admin is properly authenticated
 */
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           isset($_SESSION['login_time']) &&
           is_numeric($_SESSION['admin_id']) &&
           $_SESSION['admin_id'] > 0;
}

/**
 * Check admin session timeout (8 hours default for admin sessions)
 */
function check_admin_session_timeout($timeout_hours = 8) {
    if (!isset($_SESSION['login_time'])) {
        return true; // Session expired
    }
    
    $session_age = time() - $_SESSION['login_time'];
    $timeout_seconds = $timeout_hours * 3600;
    
    return $session_age > $timeout_seconds;
}

/**
 * Get current authenticated admin ID safely
 */
function get_current_admin_id() {
    if (!is_admin_authenticated()) {
        return null;
    }
    return (int)$_SESSION['admin_id'];
}

/**
 * Get current authenticated admin username safely
 */
function get_current_admin_username() {
    if (!is_admin_authenticated()) {
        return null;
    }
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Destroy admin session and redirect to login
 */
function admin_logout_and_redirect($message = '') {
    // Clear all session data
    $_SESSION = array();
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
    
    // Redirect to login with message
    $redirect_url = 'login.php';
    if (!empty($message)) {
        $redirect_url .= '?msg=' . urlencode($message);
    }
    
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Require admin authentication - call this in all protected admin pages
 */
function require_admin_auth() {
    // Check if admin is authenticated
    if (!is_admin_authenticated()) {
        admin_logout_and_redirect('Please log in to access the admin panel.');
    }
    
    // Check session timeout (shorter timeout for admin for security)
    if (check_admin_session_timeout()) {
        admin_logout_and_redirect('Your admin session has expired. Please log in again.');
    }
    
    // Update last activity time
    $_SESSION['admin_last_activity'] = time();
    
    return get_current_admin_id();
}

/**
 * Check if admin has specific permissions (extensible for future role-based access)
 */
function check_admin_permission($permission = 'basic') {
    if (!is_admin_authenticated()) {
        return false;
    }
    
    // For now, all authenticated admins have all permissions
    // This can be extended later for role-based access control
    switch ($permission) {
        case 'basic':
        case 'member_management':
        case 'payment_management':
        case 'payout_management':
        case 'report_access':
        case 'system_admin':
            return true;
        default:
            return false;
    }
}

/**
 * Log admin activity for security audit
 */
function log_admin_activity($action, $details = '') {
    global $pdo;
    
    $admin_id = get_current_admin_id();
    if (!$admin_id) {
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (admin_id, action, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $admin_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        // Log error but don't interrupt admin workflow
        error_log("Admin activity logging error: " . $e->getMessage());
    }
}

/**
 * Validate admin data access (prevent privilege escalation)
 */
function validate_admin_data_access($requested_admin_id = null) {
    $current_admin_id = get_current_admin_id();
    
    if (!$current_admin_id) {
        return false;
    }
    
    // If no specific admin ID requested, access granted
    if ($requested_admin_id === null) {
        return true;
    }
    
    // For now, all admins can access other admin data
    // This can be restricted later for enhanced security
    return true;
}

// Note: generate_csrf_token(), verify_csrf_token(), and sanitize_input() 
// are already defined in the main includes/db.php file

// ===== IMMEDIATE ADMIN SECURITY CHECK =====
// Don't auto-require auth if this file is being included for utility functions only
if (!defined('SKIP_ADMIN_AUTH_CHECK')) {
    $admin_id = require_admin_auth();
    
    // Future: Add page access logging when activity log table is created
    // $current_page = basename($_SERVER['PHP_SELF']);
    // log_admin_activity("page_access", "Accessed: $current_page");
}
?> 