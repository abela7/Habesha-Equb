<?php
/**
 * HabeshaEqub - Enhanced Payout Calculation API V2
 * CORRECT LOGIC: Position Coefficient × Monthly Pool
 * TOP-TIER CALCULATION SYSTEM - NO ERRORS!
 */

require_once '../../includes/db.php';
require_once '../../includes/enhanced_equb_calculator_v2.php';

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
        case 'validate_balance':
            validateEqubBalance();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Payout Calculation API V2 Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Calculation error occurred']);
}

/**
 * Calculate payout amount for a member - ENHANCED VERSION
 */
function calculateMemberPayout() {
    $member_id = intval($_POST['member_id'] ?? $_GET['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'error' => 'Member ID is required']);
        return;
    }
    
    $calculator = getEnhancedEqubCalculatorV2();
    $result = $calculator->calculateMemberFriendlyPayout($member_id);
    
    if ($result['success']) {
        // Enhanced debugging information
        error_log("ENHANCED PAYOUT CALCULATION for Member ID $member_id:");
        error_log("- Formula: " . $result['calculation']['formula_used']);
        error_log("- Position Coefficient: " . $result['calculation']['position_coefficient']);
        error_log("- Monthly Pool: £" . $result['calculation']['total_monthly_pool']);
        error_log("- Gross Payout: £" . $result['calculation']['gross_payout']);
        error_log("- Display Payout: £" . $result['calculation']['display_payout']);
        error_log("- Method: " . $result['calculation']['calculation_method']);
        
        // Format response for frontend compatibility
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
            'total_pool' => $result['calculation']['total_monthly_pool'] * $result['calculation']['duration_months'],
            
            // Enhanced debug info
            'debug' => [
                'regular_payment_tier' => $result['calculation']['regular_payment_tier'],
                'individual_contribution' => $result['member_info']['monthly_payment'],
                'calculation_method' => $result['calculation']['calculation_method'],
                'formula_used' => $result['calculation']['formula_used'],
                'membership_type' => $result['member_info']['membership_type']
            ]
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
}

/**
 * Get equb pool summary with enhanced calculations
 */
function getEqubSummary() {
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'error' => 'Equb ID is required']);
        return;
    }
    
    $calculator = getEnhancedEqubCalculatorV2();
    $result = $calculator->calculateEqubPositions($equb_id);
    
    echo json_encode(['success' => true, 'data' => $result]);
}

/**
 * Validate EQUB financial balance
 */
function validateEqubBalance() {
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'error' => 'Equb ID is required']);
        return;
    }
    
    $calculator = getEnhancedEqubCalculatorV2();
    $result = $calculator->validateEqubBalance($equb_id);
    
    echo json_encode($result);
}
?>