<?php
/**
 * HabeshaEqub - Admin Position Swap Management API
 * Handle admin processing of position swap requests
 */

// Start output buffering for clean JSON response
ob_start();

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON response header
header('Content-Type: application/json');

require_once '../../includes/db.php';
require_once '../../languages/translator.php';

// Secure admin authentication check
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    if (empty($request_id)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Request ID is required']);
        exit;
    }

    switch ($action) {
        case 'approve':
            approveSwapRequest($request_id, $admin_notes, $admin_id);
            break;
            
        case 'reject':
            rejectSwapRequest($request_id, $admin_notes, $admin_id);
            break;
            
        default:
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (PDOException $e) {
    ob_clean();
    error_log("Admin swap management API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    ob_clean();
    error_log("Admin swap management API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}

/**
 * Approve a position swap request and execute the swap
 */
function approveSwapRequest($request_id, $admin_notes, $admin_id) {
    global $pdo;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get the swap request details
        $stmt = $pdo->prepare("
            SELECT psr.*, 
                   m1.first_name as member_fname, m1.last_name as member_lname,
                   m2.first_name as target_fname, m2.last_name as target_lname
            FROM position_swap_requests psr
            LEFT JOIN members m1 ON psr.member_id = m1.id
            LEFT JOIN members m2 ON psr.target_member_id = m2.id
            WHERE psr.request_id = ? AND psr.status = 'pending'
        ");
        $stmt->execute([$request_id]);
        $swap_request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$swap_request) {
            throw new Exception('Swap request not found or already processed');
        }
        
        // Check if positions are still valid
        $stmt = $pdo->prepare("
            SELECT id, payout_position FROM members 
            WHERE id IN (?, ?) AND is_active = 1
        ");
        $stmt->execute([
            $swap_request['member_id'], 
            $swap_request['target_member_id'] ?: $swap_request['member_id']
        ]);
        $current_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Execute the position swap
        if ($swap_request['target_member_id']) {
            // DIRECT SWAP: Two members swapping positions
            $member_current_position = null;
            $target_current_position = null;
            
            foreach ($current_positions as $pos) {
                if ($pos['id'] == $swap_request['member_id']) {
                    $member_current_position = $pos['payout_position'];
                } elseif ($pos['id'] == $swap_request['target_member_id']) {
                    $target_current_position = $pos['payout_position'];
                }
            }
            
            // Swap the positions
            $stmt = $pdo->prepare("
                UPDATE members 
                SET payout_position = ? 
                WHERE id = ?
            ");
            $stmt->execute([$swap_request['requested_position'], $swap_request['member_id']]);
            $stmt->execute([$member_current_position, $swap_request['target_member_id']]);
            
            // Record in swap history
            $stmt = $pdo->prepare("
                INSERT INTO position_swap_history (
                    swap_request_id, member_a_id, member_b_id,
                    position_a_before, position_b_before,
                    position_a_after, position_b_after,
                    processed_by_admin_id, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $swap_request['id'],
                $swap_request['member_id'],
                $swap_request['target_member_id'],
                $member_current_position,
                $target_current_position,
                $swap_request['requested_position'],
                $member_current_position,
                $admin_id,
                $admin_notes
            ]);
            
        } else {
            // POSITION REQUEST: Member moving to available position
            $stmt = $pdo->prepare("
                UPDATE members 
                SET payout_position = ? 
                WHERE id = ?
            ");
            $stmt->execute([$swap_request['requested_position'], $swap_request['member_id']]);
            
            // Record in swap history
            $stmt = $pdo->prepare("
                INSERT INTO position_swap_history (
                    swap_request_id, member_a_id, member_b_id,
                    position_a_before, position_b_before,
                    position_a_after, position_b_after,
                    processed_by_admin_id, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $swap_request['id'],
                $swap_request['member_id'],
                $swap_request['member_id'], // Same member for both sides
                $swap_request['current_position'],
                $swap_request['current_position'],
                $swap_request['requested_position'],
                $swap_request['requested_position'],
                $admin_id,
                $admin_notes
            ]);
        }
        
        // Update swap request status
        $stmt = $pdo->prepare("
            UPDATE position_swap_requests 
            SET status = 'completed',
                admin_response_date = CURRENT_TIMESTAMP,
                processed_by_admin_id = ?,
                admin_notes = ?,
                completion_date = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$admin_id, $admin_notes, $swap_request['id']]);
        
        // Update member swap statistics
        $stmt = $pdo->prepare("
            UPDATE members 
            SET total_swaps_completed = total_swaps_completed + 1,
                last_swap_date = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$swap_request['member_id']]);
        
        if ($swap_request['target_member_id']) {
            $stmt->execute([$swap_request['target_member_id']]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => t('swap_management.swap_approved')
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Reject a position swap request
 */
function rejectSwapRequest($request_id, $admin_notes, $admin_id) {
    global $pdo;
    
    // Get the swap request
    $stmt = $pdo->prepare("
        SELECT id FROM position_swap_requests 
        WHERE request_id = ? AND status = 'pending'
    ");
    $stmt->execute([$request_id]);
    $swap_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$swap_request) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Swap request not found or already processed']);
        return;
    }
    
    // Update request status to rejected
    $stmt = $pdo->prepare("
        UPDATE position_swap_requests 
        SET status = 'rejected',
            admin_response_date = CURRENT_TIMESTAMP,
            processed_by_admin_id = ?,
            admin_notes = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$admin_id, $admin_notes, $swap_request['id']]);
    
    if ($result) {
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => t('swap_management.swap_rejected')
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to reject swap request']);
    }
}
?>
