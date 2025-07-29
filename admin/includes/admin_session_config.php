<?php
/**
 * HabeshaEqub Admin Secure Session Configuration
 * Enhanced session security settings for admin section
 */

// Enhanced session security for admin section
// Only configure if session hasn't been started yet by db.php
if (session_status() === PHP_SESSION_NONE) {
    // Configure enhanced secure session settings for admin
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    
    // Shorter session timeout for admin security (8 hours)
    ini_set('session.gc_maxlifetime', 28800); // 8 hours
    ini_set('session.cookie_lifetime', 0); // Session cookie
    
    // Enhanced admin session security
    ini_set('session.use_strict_mode', '1');
    ini_set('session.sid_length', '48');
    ini_set('session.sid_bits_per_character', '6');
    
    // Start session silently
    @session_start();
}

// Enhanced session regeneration for admin (every 10 minutes for security)
// Only if this is not an API call to prevent interference
if (!strpos($_SERVER['REQUEST_URI'], '/api/')) {
    if (!isset($_SESSION['admin_last_regeneration'])) {
        $_SESSION['admin_last_regeneration'] = time();
    } elseif (time() - $_SESSION['admin_last_regeneration'] > 600) { // 10 minutes
        @session_regenerate_id(true);
        $_SESSION['admin_last_regeneration'] = time();
    }
}

// Track admin session for security monitoring
if (isset($_SESSION['admin_id']) && !isset($_SESSION['admin_session_start'])) {
    $_SESSION['admin_session_start'] = time();
}
?> 