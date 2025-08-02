<?php
/**
 * Device Authentication Helper
 * Handles automatic login for remembered devices
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if device is remembered and auto-login user
 * Call this before showing login form
 */
function checkRememberedDevice() {
    // Skip if user is already logged in
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        return true;
    }
    
    // Check if device token exists
    if (!isset($_COOKIE['device_token']) || empty($_COOKIE['device_token'])) {
        return false;
    }
    
    $device_token = $_COOKIE['device_token'];
    
    try {
        // Include database
        require_once __DIR__ . '/../../includes/db.php';
        $database = isset($pdo) ? $pdo : $db;
        
        // Check if device_tracking has device_token column first
        $check_stmt = $database->prepare("SHOW COLUMNS FROM device_tracking LIKE 'device_token'");
        $check_stmt->execute();
        $has_device_token = $check_stmt->fetch();
        
        if (!$has_device_token) {
            // Database not updated, remove invalid cookie and skip check
            setcookie('device_token', '', time() - 3600, '/', '', true, true);
            return false;
        }
        
        // Check if device token is valid and not expired
        $stmt = $database->prepare("
            SELECT dt.email, m.id, m.member_id, m.first_name, m.last_name, m.email as member_email
            FROM device_tracking dt
            JOIN members m ON dt.email = m.email
            WHERE dt.device_token = ? 
            AND dt.expires_at > NOW() 
            AND m.is_approved = 1 
            AND m.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$device_token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Update last seen
            $update_stmt = $database->prepare("
                UPDATE device_tracking 
                SET last_seen = NOW() 
                WHERE device_token = ?
            ");
            $update_stmt->execute([$device_token]);
            
            // Update member last login
            $login_stmt = $database->prepare("
                UPDATE members 
                SET last_login = NOW() 
                WHERE id = ?
            ");
            $login_stmt->execute([$result['id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $result['member_email'];
            $_SESSION['user_name'] = $result['first_name'] . ' ' . $result['last_name'];
            $_SESSION['member_id'] = $result['member_id'];
            $_SESSION['auto_login'] = true; // Flag to indicate automatic login
            
            return true;
        } else {
            // Token is invalid or expired, remove cookie
            setcookie('device_token', '', time() - 3600, '/', '', true, true);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Device authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Clean up expired device tokens
 * Call this periodically for maintenance
 */
function cleanupExpiredDevices() {
    try {
        require_once __DIR__ . '/../../includes/db.php';
        $database = isset($pdo) ? $pdo : $db;
        
        $stmt = $database->prepare("
            DELETE FROM device_tracking 
            WHERE expires_at < NOW() 
            AND expires_at IS NOT NULL
        ");
        $stmt->execute();
        
        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log("Device cleanup error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Revoke a specific device token
 */
function revokeDeviceToken($token) {
    try {
        require_once __DIR__ . '/../../includes/db.php';
        $database = isset($pdo) ? $pdo : $db;
        
        $stmt = $database->prepare("
            DELETE FROM device_tracking 
            WHERE device_token = ?
        ");
        $stmt->execute([$token]);
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Device revoke error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all devices for a user
 */
function getUserDevices($email) {
    try {
        require_once __DIR__ . '/../../includes/db.php';
        $database = isset($pdo) ? $pdo : $db;
        
        $stmt = $database->prepare("
            SELECT device_fingerprint, user_agent, ip_address, created_at, last_seen, expires_at
            FROM device_tracking 
            WHERE email = ? 
            AND expires_at > NOW()
            ORDER BY last_seen DESC
        ");
        $stmt->execute([$email]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get devices error: " . $e->getMessage());
        return [];
    }
}

/**
 * Logout and optionally forget device
 */
function logoutUser($forgetDevice = false) {
    // Clear session
    session_unset();
    session_destroy();
    
    // Clear device cookie if requested
    if ($forgetDevice && isset($_COOKIE['device_token'])) {
        $device_token = $_COOKIE['device_token'];
        revokeDeviceToken($device_token);
        setcookie('device_token', '', time() - 3600, '/', '', true, true);
    }
    
    return true;
}
?>