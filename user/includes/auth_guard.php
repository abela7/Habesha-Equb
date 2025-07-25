<?php
/**
 * HabeshaEqub User Authentication Guard
 * Centralized authentication and security protection for user section
 * Include this file at the top of all protected user pages
 */

// Include secure session configuration and database functions
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/../../includes/db.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Force HTTPS in production (optional)
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

/**
 * Check if user is properly authenticated
 */
function is_user_authenticated() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['user_logged_in']) && 
           $_SESSION['user_logged_in'] === true &&
           isset($_SESSION['user_login_time']) &&
           is_numeric($_SESSION['user_id']) &&
           $_SESSION['user_id'] > 0;
}

/**
 * Check session timeout (24 hours default)
 */
function check_session_timeout($timeout_hours = 24) {
    if (!isset($_SESSION['user_login_time'])) {
        return true; // Session expired
    }
    
    $session_age = time() - $_SESSION['user_login_time'];
    $timeout_seconds = $timeout_hours * 3600;
    
    return $session_age > $timeout_seconds;
}

/**
 * Get current authenticated user ID safely
 */
function get_current_user_id() {
    if (!is_user_authenticated()) {
        return null;
    }
    return (int)$_SESSION['user_id'];
}

/**
 * Destroy session and redirect to login
 */
function logout_and_redirect($message = '') {
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
 * Require authentication - call this in all protected pages
 */
function require_user_auth() {
    // Check if user is authenticated
    if (!is_user_authenticated()) {
        logout_and_redirect('Please log in to access this page.');
    }
    
    // Check session timeout
    if (check_session_timeout()) {
        logout_and_redirect('Your session has expired. Please log in again.');
    }
    
    // Update last activity time
    $_SESSION['user_last_activity'] = time();
    
    return get_current_user_id();
}

// Note: generate_csrf_token(), verify_csrf_token(), and sanitize_input() 
// are already defined in the main includes/db.php file

/**
 * Check if user has access to specific member data
 * Prevents users from accessing other users' information
 */
function can_access_member_data($requested_member_id = null) {
    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        return false;
    }
    
    // If no specific member ID requested, user can access their own data
    if ($requested_member_id === null) {
        return true;
    }
    
    // Users can only access their own data
    return (int)$requested_member_id === $current_user_id;
}

// ===== IMMEDIATE SECURITY CHECK =====
// Don't auto-require auth if this file is being included for utility functions only
if (!defined('SKIP_AUTH_CHECK')) {
    require_user_auth();
}
?> 