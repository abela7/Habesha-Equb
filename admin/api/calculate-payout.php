<?php
/**
 * HabeshaEqub - Payout Calculation API
 * Calculates correct payout amounts based on equb logic and payment tiers
 */

require_once '../../includes/db.php';
require_once '../../includes/enhanced_equb_calculator.php';

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
    
    $calculator = getEnhancedEqubCalculator();
    $result = $calculator->calculateMemberFriendlyPayout($member_id);
    
    if ($result['success']) {
        // Add debugging information
        error_log("PAYOUT DEBUG for Member ID $member_id:");
        error_log("- Monthly Pool: " . $result['calculation']['total_monthly_pool']);
        error_log("- Gross Payout Per Position: " . $result['calculation']['gross_payout_per_position'] ?? 'N/A');
        error_log("- Individual Gross: " . $result['calculation']['gross_payout']);
        error_log("- Position Coefficient: " . $result['calculation']['position_coefficient']);
        
        // Format response for compatibility with existing frontend
        $response = [
            'success' => true,
            'member_name' => $result['member_info']['name'],
            'monthly_payment' => $result['member_info']['monthly_payment'],
            'position_coefficient' => $result['calculation']['position_coefficient'],
            'total_monthly_pool' => $result['calculation']['total_monthly_pool'],
            'gross_payout' => $result['calculation']['gross_payout'],
            'admin_fee' => $result['calculation']['admin_fee'],
            'net_payout' => $result['calculation']['real_net_payout'], // Real amount member gets
            'display_payout' => $result['calculation']['display_payout'], // Member-friendly amount
            'share_ratio' => $result['calculation']['position_coefficient'],
            'total_pool' => $result['calculation']['total_monthly_pool'] * 9, // Assuming 9 months duration
            
            // Add debug info to response
            'debug' => [
                'gross_payout_per_position' => $result['calculation']['gross_payout_per_position'] ?? 'N/A',
                'individual_contribution' => $result['member_info']['monthly_payment'],
                'calculation_method' => $result['member_info']['position_coefficient'] > 1 ? 'joint_group' : 'individual'
            ]
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
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
    
    $calculator = getEnhancedEqubCalculator();
    $result = $calculator->calculateEqubPositions($equb_id);
    
    echo json_encode(['success' => true, 'data' => $result]);
}
?>