<?php
/**
 * HabeshaEqub - Position Swap Management API
 * COMPLETE WORKFLOW: Update positions, delete requests, populate history
 */

require_once '../../includes/db.php';
require_once '../../languages/translator.php';
require_once '../includes/admin_auth_guard.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent any accidental output
ob_start();

try {
    // Get current admin
    $admin_id = get_current_admin_id();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests allowed');
    }
    
    $action = $_POST['action'] ?? '';
    
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid security token');
    }
    
    switch ($action) {
        case 'approve':
            $request_id = $_POST['request_id'] ?? '';
            $admin_notes = $_POST['admin_notes'] ?? '';
            
            if (empty($request_id)) {
                throw new Exception('Request ID is required');
            }
            
            // Get the swap request
            $stmt = $pdo->prepare("SELECT * FROM position_swap_requests WHERE request_id = ?");
            $stmt->execute([$request_id]);
            $swap_request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$swap_request) {
                throw new Exception('Swap request not found');
            }
            
            if ($swap_request['status'] !== 'pending') {
                throw new Exception('Request has already been processed');
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // 1. Update the member's position
                $update_stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
                $result = $update_stmt->execute([
                    $swap_request['requested_position'], 
                    $swap_request['member_id']
                ]);
                
                if (!$result) {
                    throw new Exception('Failed to update member position');
                }
                
                // 2. Add to position_swap_history (using correct table structure)
                $history_stmt = $pdo->prepare("
                    INSERT INTO position_swap_history 
                    (swap_request_id, member_a_id, member_b_id, position_a_before, position_b_before, position_a_after, position_b_after, processed_by_admin_id, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Get the request ID (we'll use the numeric ID from the request if available)
                $request_numeric_id = $swap_request['id'] ?? 0;
                
                $history_stmt->execute([
                    $request_numeric_id,
                    $swap_request['member_id'],
                    $swap_request['target_member_id'] ?? $swap_request['member_id'], // If no target, use same member
                    $swap_request['current_position'],
                    $swap_request['target_member_id'] ? 0 : 0, // Position before for target (0 if no target)
                    $swap_request['requested_position'],
                    $swap_request['target_member_id'] ? 0 : 0, // Position after for target (0 if no target)
                    $admin_id,
                    $admin_notes
                ]);
                
                // 3. DELETE the request since it's approved and completed
                $delete_stmt = $pdo->prepare("DELETE FROM position_swap_requests WHERE request_id = ?");
                $delete_stmt->execute([$request_id]);
                
                $pdo->commit();
                
                // Clean output buffer and send response
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => 'Position swap approved! Member position updated and request completed.'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollback();
                throw $e;
            }
            break;
            
        case 'reject':
            $request_id = $_POST['request_id'] ?? '';
            $admin_notes = $_POST['admin_notes'] ?? '';
            
            if (empty($request_id)) {
                throw new Exception('Request ID is required');
            }
            
            if (empty($admin_notes)) {
                throw new Exception('Admin notes/reason is required for rejection');
            }
            
            // Get the swap request
            $stmt = $pdo->prepare("SELECT * FROM position_swap_requests WHERE request_id = ?");
            $stmt->execute([$request_id]);
            $swap_request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$swap_request) {
                throw new Exception('Swap request not found');
            }
            
            if ($swap_request['status'] !== 'pending') {
                throw new Exception('Request has already been processed');
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // 1. Update request status to rejected and add reason
                $reject_stmt = $pdo->prepare("
                    UPDATE position_swap_requests 
                    SET status = 'rejected', 
                        admin_notes = ?,
                        processed_by_admin_id = ?
                    WHERE request_id = ?
                ");
                
                $result = $reject_stmt->execute([
                    $admin_notes,
                    $admin_id,
                    $request_id
                ]);
                
                if (!$result) {
                    throw new Exception('Failed to update request status');
                }
                
                // 2. Add rejection to history for record keeping (using correct table structure)
                $history_stmt = $pdo->prepare("
                    INSERT INTO position_swap_history 
                    (swap_request_id, member_a_id, member_b_id, position_a_before, position_b_before, position_a_after, position_b_after, processed_by_admin_id, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Get the request ID (we'll use the numeric ID from the request if available)
                $request_numeric_id = $swap_request['id'] ?? 0;
                
                $history_stmt->execute([
                    $request_numeric_id,
                    $swap_request['member_id'],
                    $swap_request['target_member_id'] ?? $swap_request['member_id'], // If no target, use same member
                    $swap_request['current_position'],
                    $swap_request['target_member_id'] ? 0 : 0, // Position before for target (0 if no target)
                    $swap_request['current_position'], // Position stays the same - REJECTED
                    $swap_request['target_member_id'] ? 0 : 0, // Position after for target (0 if no target)
                    $admin_id,
                    'REJECTED: ' . $admin_notes
                ]);
                
                $pdo->commit();
                
                // Clean output buffer and send response
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => 'Request rejected and recorded in history.'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollback();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    // Clean output buffer and send error response
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>