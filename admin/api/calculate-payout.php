<?php
/**
 * HabeshaEqub - Payout Calculation API
 * Calculates correct payout amounts based on equb logic and payment tiers
 */

require_once '../../includes/db.php';
require_once '../../includes/equb_payout_calculator.php';

// Set JSON header
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'calculate':
            calculateMemberPayout();
            break;
        case 'equb_summary':
            getEqubSummary();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Payout Calculation API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Calculation error occurred']);
}

/**
 * Calculate payout amount for a member
 */
function calculateMemberPayout() {
    $member_id = intval($_POST['member_id'] ?? $_GET['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'error' => 'Member ID is required']);
        return;
    }
    
    $calculator = getEqubPayoutCalculator();
    $result = $calculator->calculateMemberPayoutAmount($member_id);
    
    echo json_encode($result);
}

/**
 * Get equb pool summary
 */
function getEqubSummary() {
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'error' => 'Equb ID is required']);
        return;
    }
    
    $calculator = getEqubPayoutCalculator();
    $result = $calculator->getEqubPoolSummary($equb_id);
    
    echo json_encode(['success' => true, 'data' => $result]);
}
?>