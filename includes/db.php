<?php
/**
 * HabeshaEqub Database Connection
 * Secure configuration with credential protection
 */

// Load secure configuration
define('CONFIG_LOADED', true);
$config = require_once __DIR__ . '/config.php';

try {
    // Database configuration from secure config
    $db_config = $config['database'];
    
    // Create PDO connection with secure configuration
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}",
        $db_config['username'],
        $db_config['password'],
        $db_config['options']
    );
    
    // Create alias for backward compatibility
    $db = $pdo;
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check configuration.");
}

/**
 * Simple input sanitization function
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate secure CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Clean suspicious members from database
 */
function clean_suspicious_members() {
    global $pdo;
    
    try {
        // Find and remove suspicious members
        $suspicious_patterns = [
            'boldsoar%',
            '%localglobalmail%',
            '%@localglobalmail.com',
            'Simone Fidradoeia'
        ];
        
        $removed_count = 0;
        
        foreach ($suspicious_patterns as $pattern) {
            // Check by email pattern
            $stmt = $pdo->prepare("
                SELECT id, email, full_name, phone, created_at 
                FROM members 
                WHERE email LIKE ? OR full_name LIKE ?
            ");
            $stmt->execute([$pattern, $pattern]);
            $suspicious_members = $stmt->fetchAll();
            
            foreach ($suspicious_members as $member) {
                // Log the suspicious member before removal
                // SecurityLogger::logSecurityEvent('suspicious_member_removed', [
                //     'member_id' => $member['id'],
                //     'email' => $member['email'],
                //     'full_name' => $member['full_name'],
                //     'phone' => $member['phone'],
                //     'created_at' => $member['created_at']
                // ]);
                
                // Delete the suspicious member
                $delete_stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
                $delete_stmt->execute([$member['id']]);
                $removed_count++;
            }
        }
        
        return $removed_count;
        
    } catch (Exception $e) {
        error_log("Error cleaning suspicious members: " . $e->getMessage());
        return false;
    }
}

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict'
    ]);
}
?> 