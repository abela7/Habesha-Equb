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
    ini_set('session.cookie_samesite', 'Strict');
    
    // Session timeout and security
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    ini_set('session.cookie_lifetime', 0); // Session cookie
    
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