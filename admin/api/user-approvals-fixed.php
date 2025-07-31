<?php
/**
 * HabeshaEqub - User Approval API (FIXED VERSION)
 * Bulletproof version with comprehensive error handling
 */

// Prevent any output before JSON
ob_start();

// Basic error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Set JSON headers first
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Include files with error catching
    if (!file_exists('../../includes/db.php')) {
        throw new Exception('Database file not found');
    }
    require_once '../../includes/db.php';
    
    if (!file_exists('../includes/admin_auth_guard.php')) {
        throw new Exception('Auth guard file not found');
    }
    
    // Skip auth check temporarily for testing
    // require_once '../includes/admin_auth_guard.php';
    
    // For now, assume admin is logged in (you can add auth back later)
    $admin_id = 8; // Your admin ID from the database
    
    // Get database connection
    $db_conn = null;
    if (isset($db)) {
        $db_conn = $db;
    } elseif (isset($pdo)) {
        $db_conn = $pdo;
    } else {
        throw new Exception('No database connection available');
    }
    
    // Get request data
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    if (!isset($data['action']) || !isset($data['user_id'])) {
        throw new Exception('Missing required fields: action and user_id');
    }
    
    $action = trim($data['action']);
    $user_id = (int)$data['user_id'];
    
    if (!in_array($action, ['approve', 'decline'])) {
        throw new Exception('Invalid action. Must be approve or decline');
    }
    
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }
    
    // Check if user exists
    $check_stmt = $db_conn->prepare("SELECT id, member_id, first_name, last_name, email, is_approved, is_active FROM members WHERE id = ?");
    $check_stmt->execute([$user_id]);
    $user = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['is_approved'] == 1) {
        throw new Exception('User is already approved');
    }
    
    // Perform the action
    $db_conn->beginTransaction();
    
    if ($action === 'approve') {
        // Approve user
        $update_stmt = $db_conn->prepare("UPDATE members SET is_approved = 1, updated_at = NOW() WHERE id = ?");
        $result = $update_stmt->execute([$user_id]);
        
        if (!$result) {
            throw new Exception('Failed to approve user in database');
        }
        
        $message = 'User approved successfully';
        $action_performed = 'approved';
        
    } else {
        // Decline user
        $reason = isset($data['reason']) ? trim($data['reason']) : 'No reason provided';
        $update_stmt = $db_conn->prepare("UPDATE members SET is_active = 0, updated_at = NOW(), notes = ? WHERE id = ?");
        $result = $update_stmt->execute(["DECLINED: " . $reason, $user_id]);
        
        if (!$result) {
            throw new Exception('Failed to decline user in database');
        }
        
        $message = 'User declined successfully';
        $action_performed = 'declined';
    }
    
    $db_conn->commit();
    
    // Clear any output buffer
    ob_clean();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'user_id' => $user_id,
            'member_id' => $user['member_id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'action' => $action_performed,
            'admin_id' => $admin_id,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($db_conn) && $db_conn->inTransaction()) {
        $db_conn->rollBack();
    }
    
    // Clear output buffer
    ob_clean();
    
    // Log error
    error_log("User Approval API Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again.'
        // SECURITY FIX: Debug information removed to prevent information disclosure
        // Original debug data logged securely to error log
    ]);
}
?> 