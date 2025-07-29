<?php
/**
 * HabeshaEqub - Secure Database Connection
 * Enhanced with security features and attack prevention
 */

// Include security system
require_once __DIR__ . '/security.php';

try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'habeshjv_habeshaequb';
    $username = 'habeshjv_abel';
    $password = '2121@Habesha';
    
    // Create secure PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    SecurityLogger::logSecurityEvent('database_connection_failed', [
        'error' => $e->getMessage()
    ]);
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check configuration.");
}

/**
 * Enhanced secure input sanitization function
 * @deprecated Use SecurityValidator::sanitizeInput() instead
 */
function sanitize_input($data) {
    return SecurityValidator::sanitizeInput($data, 'html');
}

/**
 * Generate secure CSRF token
 * @deprecated Use CSRFProtection::generateToken() instead
 */
function generate_csrf_token() {
    return CSRFProtection::generateToken();
}

/**
 * Verify CSRF token
 * @deprecated Use CSRFProtection::validateToken() instead
 */
function verify_csrf_token($token) {
    return CSRFProtection::validateToken($token);
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
                SecurityLogger::logSecurityEvent('suspicious_member_removed', [
                    'member_id' => $member['id'],
                    'email' => $member['email'],
                    'full_name' => $member['full_name'],
                    'phone' => $member['phone'],
                    'created_at' => $member['created_at']
                ]);
                
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
SessionSecurity::startSecureSession();

// Log database connection
SecurityLogger::logSecurityEvent('database_connected', [
    'database' => $dbname,
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 