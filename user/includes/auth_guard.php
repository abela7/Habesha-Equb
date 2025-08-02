<?php
/**
 * HabeshaEqub User Authentication Guard
 * Centralized authentication and security protection for user section
 * Include this file at the top of all protected user pages
 */

// Include secure session configuration and database functions
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../languages/user_language_handler.php';

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
    // Debug: Log authentication attempt
    error_log("Auth Guard - Authentication check for page: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
    error_log("Auth Guard - Session data: user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", user_logged_in=" . (isset($_SESSION['user_logged_in']) ? $_SESSION['user_logged_in'] : 'not set') . 
              ", user_login_time=" . (isset($_SESSION['user_login_time']) ? $_SESSION['user_login_time'] : 'not set'));
    
    // Check if user is authenticated
    if (!is_user_authenticated()) {
        error_log("Auth Guard - Authentication failed, redirecting to login");
        logout_and_redirect();
    }

    // Check session timeout
    if (check_session_timeout()) {
        error_log("Auth Guard - Session timeout, redirecting to login");
        logout_and_redirect('Your session has expired. Please log in again.');
    }
    
    error_log("Auth Guard - Authentication successful for user_id: " . $_SESSION['user_id']);
    
    // Load user's language preference from database
    $user_id = get_current_user_id();
    if ($user_id) {
        setUserLanguageFromDatabase($user_id);
    }
    
    // Check welcome flow completion (except for welcome page itself)
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'welcome.php') {
        check_welcome_flow_completion();
    }
    
    // Update last activity time
    $_SESSION['user_last_activity'] = time();
    
    return $user_id;
}

/**
 * Check if user has completed the welcome flow (rules agreement)
 * Redirects to welcome page if not completed
 */
function check_welcome_flow_completion() {
    global $db;
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }
    
    try {
        $stmt = $db->prepare("
            SELECT rules_agreed, is_approved 
            FROM members 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            logout_and_redirect('User account not found.');
            return;
        }
        
        // Check if user is approved
        if (!$user['is_approved']) {
            logout_and_redirect('Your account is pending approval.');
            return;
        }
        
        // Check if user has agreed to rules
        if ($user['rules_agreed'] != 1) {
            header('Location: welcome.php');
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Welcome flow check error: " . $e->getMessage());
        // Don't block access if there's a database error
    }
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