<?php
/**
 * Habesha!-leEqub Admin System Reset API
 * DANGEROUS: Resets entire system by deleting all member data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start output buffering to ensure clean JSON
ob_start();

// Security: Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include required files
require_once '../includes/admin_auth_guard.php';
require_once '../../includes/db.php';

try {
    // Ensure admin is authenticated
    if (!is_admin_authenticated()) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Get current admin info for logging
    $admin_id = get_current_admin_id();
    $admin_username = get_current_admin_username();

    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate request
    if (!$data || $data['action'] !== 'reset_system') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    // Validate confirmation
    if ($data['confirmation'] !== 'DELETE ALL DATA') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid confirmation text']);
        exit;
    }

    // Log the reset attempt
    error_log("SYSTEM RESET INITIATED by admin_id=$admin_id ($admin_username) at " . date('Y-m-d H:i:s'));

    // Count records before deletion (for logging)
    $counts = [];
    $counts['members'] = $db->query("SELECT COUNT(*) FROM members")->fetchColumn();
    $counts['payments'] = $db->query("SELECT COUNT(*) FROM payments")->fetchColumn();
    $counts['payouts'] = $db->query("SELECT COUNT(*) FROM payouts")->fetchColumn();
    $counts['notifications'] = $db->query("SELECT COUNT(*) FROM notifications")->fetchColumn();

    // Start database transaction
    $db->beginTransaction();
    
    // Temporarily disable foreign key checks to avoid constraint issues
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // DANGER ZONE: Delete all member-related data
    // Order doesn't matter now since foreign keys are disabled

    // Delete all data
    $db->exec("DELETE FROM notifications");
    $db->exec("DELETE FROM payments");
    $db->exec("DELETE FROM payouts");
    $db->exec("DELETE FROM members");

    // Reset auto-increment counters to start fresh
    $db->exec("ALTER TABLE members AUTO_INCREMENT = 1");
    $db->exec("ALTER TABLE payments AUTO_INCREMENT = 1");
    $db->exec("ALTER TABLE payouts AUTO_INCREMENT = 1");
    $db->exec("ALTER TABLE notifications AUTO_INCREMENT = 1");
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Commit the transaction
    $db->commit();

    // Log successful reset with counts
    $log_message = "SYSTEM RESET COMPLETED by admin_id=$admin_id ($admin_username) at " . date('Y-m-d H:i:s') . "\n";
    $log_message .= "Deleted records: ";
    $log_message .= "Members: {$counts['members']}, ";
    $log_message .= "Payments: {$counts['payments']}, ";
    $log_message .= "Payouts: {$counts['payouts']}, ";
    $log_message .= "Notifications: {$counts['notifications']}";
    
    error_log($log_message);

    // Return success response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'System reset completed successfully',
        'deleted_counts' => $counts,
        'reset_by' => $admin_username,
        'reset_time' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    // Rollback transaction on database error
    try {
        if ($db->inTransaction()) {
            $db->rollback();
        }
    } catch (Exception $rollbackError) {
        error_log("Rollback failed: " . $rollbackError->getMessage());
    }
    
    error_log("SYSTEM RESET FAILED - Database error: " . $e->getMessage());
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);

} catch (Exception $e) {
    // Rollback transaction on any other error
    try {
        if (isset($db) && $db->inTransaction()) {
            $db->rollback();
        }
    } catch (Exception $rollbackError) {
        error_log("Rollback failed: " . $rollbackError->getMessage());
    }
    
    error_log("SYSTEM RESET FAILED - General error: " . $e->getMessage());
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Reset failed: ' . $e->getMessage()
    ]);
}

exit;
?>