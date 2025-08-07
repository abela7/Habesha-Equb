<?php
/**
 * HabeshaEqub - Position Swap API
 * Handle member position swap requests
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

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'submit':
            submitSwapRequest($user_id);
            break;
            
        case 'cancel':
            cancelSwapRequest($user_id);
            break;
            
        case 'get_positions':
            getAvailablePositions($user_id);
            break;
            
        default:
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (PDOException $e) {
    ob_clean();
    error_log("Position swap API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    ob_clean();
    error_log("Position swap API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}

/**
 * Submit a new position swap request
 */
function submitSwapRequest($user_id) {
    global $pdo;
    
    // Validate input
    $requested_position = intval($_POST['requested_position'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($requested_position <= 0) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid position selected']);
        return;
    }
    
    // Get member data
    $stmt = $pdo->prepare("
        SELECT m.*, es.max_members 
        FROM members m
        JOIN equb_settings es ON m.equb_settings_id = es.id
        WHERE m.id = ? AND m.is_active = 1
    ");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }
    
    // Validate position is within range
    if ($requested_position > $member['max_members']) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid position number']);
        return;
    }
    
    // Check if member can request swaps
    if (!$member['swap_requests_allowed']) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'You are not allowed to request position swaps']);
        return;
    }
    
    // Check for pending requests
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_count 
        FROM position_swap_requests 
        WHERE member_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $pending_count = $stmt->fetchColumn();
    
    if ($pending_count > 0) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'You have pending swap requests. Please wait for admin approval.']);
        return;
    }
    
    // Check cooldown period
    if ($member['swap_cooldown_until'] && strtotime($member['swap_cooldown_until']) > time()) {
        $cooldown_date = date('M j, Y', strtotime($member['swap_cooldown_until']));
        ob_clean();
        echo json_encode(['success' => false, 'message' => "You cannot request swaps until {$cooldown_date}"]);
        return;
    }
    
    // Check if requesting same position
    if ($requested_position == $member['payout_position']) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'You cannot request your current position']);
        return;
    }
    
    // Find target member (if position is occupied)
    $stmt = $pdo->prepare("
        SELECT id FROM members 
        WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1 AND id != ?
    ");
    $stmt->execute([$member['equb_settings_id'], $requested_position, $user_id]);
    $target_member_id = $stmt->fetchColumn();
    
    // Generate request ID will be handled by trigger
    $stmt = $pdo->prepare("
        INSERT INTO position_swap_requests (
            member_id, 
            current_position, 
            requested_position, 
            target_member_id, 
            reason, 
            request_type,
            status
        ) VALUES (?, ?, ?, ?, ?, 'specific_position', 'pending')
    ");
    
    $result = $stmt->execute([
        $user_id,
        $member['payout_position'],
        $requested_position,
        $target_member_id ?: null,
        $reason
    ]);
    
    if ($result) {
        // Update member's total swap requests
        $stmt = $pdo->prepare("
            UPDATE members 
            SET total_swaps_requested = total_swaps_requested + 1 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        // Get the generated request ID
        $stmt = $pdo->prepare("
            SELECT request_id FROM position_swap_requests 
            WHERE member_id = ? AND current_position = ? AND requested_position = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$user_id, $member['payout_position'], $requested_position]);
        $request_id = $stmt->fetchColumn();
        
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => t('position_swap.success_message'),
            'request_id' => $request_id
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to submit swap request']);
    }
}

/**
 * Cancel a pending swap request
 */
function cancelSwapRequest($user_id) {
    global $pdo;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $request_id = $input['request_id'] ?? '';
    
    if (empty($request_id)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Request ID is required']);
        return;
    }
    
    // Verify the request belongs to the user and is pending
    $stmt = $pdo->prepare("
        SELECT id FROM position_swap_requests 
        WHERE request_id = ? AND member_id = ? AND status = 'pending'
    ");
    $stmt->execute([$request_id, $user_id]);
    $swap_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$swap_request) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Request not found or cannot be cancelled']);
        return;
    }
    
    // Update request status to cancelled
    $stmt = $pdo->prepare("
        UPDATE position_swap_requests 
        SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$swap_request['id']]);
    
    if ($result) {
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Swap request cancelled successfully'
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to cancel swap request']);
    }
}

/**
 * Get available positions for swap
 */
function getAvailablePositions($user_id) {
    global $pdo;
    
    // Get member data
    $stmt = $pdo->prepare("
        SELECT m.*, es.* 
        FROM members m
        JOIN equb_settings es ON m.equb_settings_id = es.id
        WHERE m.id = ? AND m.is_active = 1
    ");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }
    
    // Get all members in the same equb
    $stmt = $pdo->prepare("
        SELECT payout_position, first_name, last_name, go_public
        FROM members 
        WHERE equb_settings_id = ? AND is_active = 1 AND id != ?
        ORDER BY payout_position ASC
    ");
    $stmt->execute([$member['equb_settings_id'], $user_id]);
    $other_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate future positions only
    $current_date = new DateTime();
    $equb_start = new DateTime($member['start_date']);
    $positions = [];
    
    for ($pos = 1; $pos <= $member['max_members']; $pos++) {
        if ($pos == $member['payout_position']) continue;
        
        // Calculate position date
        $position_date = clone $equb_start;
        $position_date->add(new DateInterval('P' . ($pos - 1) . 'M'));
        $position_date->setDate(
            $position_date->format('Y'),
            $position_date->format('n'),
            $member['payout_day']
        );
        
        // Only include future positions
        if ($position_date > $current_date) {
            $occupant = null;
            foreach ($other_members as $other_member) {
                if ($other_member['payout_position'] == $pos) {
                    $occupant = $other_member;
                    break;
                }
            }
            
            $positions[] = [
                'position' => $pos,
                'date' => $position_date->format('Y-m-d'),
                'month_name' => $position_date->format('M Y'),
                'is_available' => !$occupant,
                'occupant_name' => $occupant ? 
                    ($occupant['go_public'] ? 
                        $occupant['first_name'] . ' ' . $occupant['last_name'] : 
                        'Anonymous') : null
            ];
        }
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'current_position' => $member['payout_position'],
        'available_positions' => $positions
    ]);
}
?>
