<?php
/**
 * HabeshaEqub Secure Session Configuration
 * Enhanced session security settings
 */

// Enhanced session security for user section
// Only configure if session hasn't been started yet by db.php
if (session_status() === PHP_SESSION_NONE) {
    // Configure enhanced secure session settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    // Use Lax to allow device_token cookie-assisted flows across redirects
    ini_set('session.cookie_samesite', 'Lax');
    
    // Session timeout and security
    // Keep server-side session available for 7 days to match remember-device
    ini_set('session.gc_maxlifetime', 604800); // 7 days
    // Keep browser session cookie until browser close; auto-login will re-establish
    ini_set('session.cookie_lifetime', 0);
    
    session_start();
}

// Session regeneration (works with existing session too)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 900) { // 15 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
?> 