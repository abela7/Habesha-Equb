<?php
/**
 * HabeshaEqub Secure Session Configuration
 * Enhanced session security settings
 */

// Prevent session fixation attacks
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    
    // Session timeout and security
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    ini_set('session.cookie_lifetime', 0); // Session cookie
    
    // Regenerate session ID periodically
    session_start();
    
    // Regenerate session ID every 15 minutes for active sessions
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 900) { // 15 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?> 