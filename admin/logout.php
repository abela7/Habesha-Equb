<?php
/**
 * HabeshaEqub Admin Logout
 * Secure logout for admin section
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database for session handling
require_once '../includes/db.php';

// Simple logout without logging for now (to avoid database issues)
// Future: Add activity logging when table is created

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

// Redirect to login with success message
header('Location: login.php?msg=' . urlencode('You have been successfully logged out.'));
exit;
?> 