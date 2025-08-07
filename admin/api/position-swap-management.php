<?php
/**
 * HabeshaEqub - Position Swap Management API
 * Handle position swap request processing
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
        case 'reject':
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
            
            // Update the request status
            $new_status = ($action === 'approve') ? 'approved' : 'rejected';
            $processed_date = date('Y-m-d H:i:s');
            
            $stmt = $pdo->prepare("
                UPDATE position_swap_requests 
                SET status = ?, 
                    processed_by_admin_id = ?, 
                    processed_date = ?, 
                    admin_notes = ?
                WHERE request_id = ?
            ");
            
            $result = $stmt->execute([
                $new_status,
                $admin_id,
                $processed_date,
                $admin_notes,
                $request_id
            ]);
            
            if (!$result) {
                throw new Exception('Failed to update request status');
            }
            
            // If approved, handle the position swap logic
            if ($action === 'approve') {
                // Start transaction for position swap
                $pdo->beginTransaction();
                
                try {
                    // If there's a target member, swap positions
                    if ($swap_request['target_member_id']) {
                        // Get current positions
                        $member_stmt = $pdo->prepare("SELECT payout_position FROM members WHERE id = ?");
                        $member_stmt->execute([$swap_request['member_id']]);
                        $member_pos = $member_stmt->fetchColumn();
                        
                        $target_stmt = $pdo->prepare("SELECT payout_position FROM members WHERE id = ?");
                        $target_stmt->execute([$swap_request['target_member_id']]);
                        $target_pos = $target_stmt->fetchColumn();
                        
                        // Swap the positions
                        $update1 = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
                        $update1->execute([$target_pos, $swap_request['member_id']]);
                        
                        $update2 = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
                        $update2->execute([$member_pos, $swap_request['target_member_id']]);
                        
                    } else {
                        // Just update the requesting member's position
                        $update = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
                        $update->execute([$swap_request['requested_position'], $swap_request['member_id']]);
                    }
                    
                    // Update request status to completed
                    $complete_stmt = $pdo->prepare("
                        UPDATE position_swap_requests 
                        SET status = 'completed', completed_date = ?
                        WHERE request_id = ?
                    ");
                    $complete_stmt->execute([date('Y-m-d H:i:s'), $request_id]);
                    
                    // Add to position swap history
                    $history_stmt = $pdo->prepare("
                        INSERT INTO position_swap_history 
                        (request_id, member_id, target_member_id, old_position, new_position, admin_id, swap_date, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $history_stmt->execute([
                        $request_id,
                        $swap_request['member_id'],
                        $swap_request['target_member_id'],
                        $swap_request['current_position'],
                        $swap_request['requested_position'],
                        $admin_id,
                        date('Y-m-d H:i:s'),
                        $admin_notes
                    ]);
                    
                    $pdo->commit();
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    throw new Exception('Failed to complete position swap: ' . $e->getMessage());
                }
            }
            
            // Clean output buffer and send response
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Request ' . $action . 'd successfully'
            ]);
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