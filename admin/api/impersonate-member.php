<?php
/**
 * HabeshaEqub - Member Impersonation API (Admin Only)
 * Securely allows admins to login as members for testing
 * SECURITY: Multiple layers of protection
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db.php';
require_once '../../languages/translator.php';

// SECURITY CHECK 1: Admin Authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in'] || !isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die("❌ SECURITY ERROR: Admin authentication required");
}

// SECURITY CHECK 2: Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die("❌ ERROR: Invalid request method");
}

// SECURITY CHECK 3: Validate Required Parameters
$member_id = filter_input(INPUT_GET, 'member_id', FILTER_VALIDATE_INT);
$admin_id = filter_input(INPUT_GET, 'admin_id', FILTER_VALIDATE_INT);

if (!$member_id || !$admin_id) {
    http_response_code(400);
    die("❌ ERROR: Invalid member ID or admin ID");
}

// SECURITY CHECK 4: Verify Admin ID matches session
if ($admin_id !== $_SESSION['admin_id']) {
    http_response_code(403);
    die("❌ SECURITY ERROR: Admin ID mismatch");
}

try {
    // SECURITY CHECK 5: Verify admin exists and is active
    $stmt = $db->prepare("SELECT id, username, email FROM admins WHERE id = ? AND is_active = 1");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        http_response_code(403);
        die("❌ SECURITY ERROR: Invalid or inactive admin");
    }

    // SECURITY CHECK 6: Verify member exists and is active
    $stmt = $db->prepare("
        SELECT 
            id, member_id, first_name, last_name, email, 
            phone, is_active, is_approved, equb_settings_id
        FROM members 
        WHERE id = ? AND is_active = 1
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        http_response_code(404);
        die("❌ ERROR: Member not found or inactive");
    }

    // SECURITY LOG: Record the impersonation for audit trail
    error_log("ADMIN IMPERSONATION: Admin {$admin['username']} (ID: {$admin_id}) is impersonating member {$member['first_name']} {$member['last_name']} (ID: {$member_id})");

    // CLEAN SLATE: Destroy any existing user sessions
    if (isset($_SESSION['user_id'])) {
        unset($_SESSION['user_id']);
    }
    if (isset($_SESSION['user_logged_in'])) {
        unset($_SESSION['user_logged_in']);
    }
    if (isset($_SESSION['user_email'])) {
        unset($_SESSION['user_email']);
    }
    if (isset($_SESSION['member_name'])) {
        unset($_SESSION['member_name']);
    }

    // CREATE MEMBER SESSION: Set up member session variables
    $_SESSION['user_id'] = $member['id'];
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_email'] = $member['email'];
    $_SESSION['member_name'] = $member['first_name'] . ' ' . $member['last_name'];
    $_SESSION['equb_settings_id'] = $member['equb_settings_id'];
    
    // TESTING FLAG: Mark this as an admin impersonation session
    $_SESSION['admin_impersonation'] = true;
    $_SESSION['impersonating_admin_id'] = $admin_id;
    $_SESSION['impersonating_admin_username'] = $admin['username'];
    $_SESSION['impersonation_timestamp'] = time();

    // SUCCESS LOG
    error_log("IMPERSONATION SUCCESS: Member session created for testing purposes");

    // REDIRECT: Send to member dashboard
    header("Location: ../../user/dashboard.php");
    exit;

} catch (PDOException $e) {
    error_log("IMPERSONATION ERROR: Database error - " . $e->getMessage());
    http_response_code(500);
    die("❌ DATABASE ERROR: Failed to process impersonation request");
} catch (Exception $e) {
    error_log("IMPERSONATION ERROR: " . $e->getMessage());
    http_response_code(500);
    die("❌ ERROR: An unexpected error occurred");
}
?>
