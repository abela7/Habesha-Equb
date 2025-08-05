<?php
/**
 * HabeshaEqub - Smart EQUB Fixer API
 * Fixes the fundamental logical error in EQUB calculations
 */

require_once '../../includes/db.php';
require_once '../../includes/smart_equb_calculator.php';

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Admin authentication check
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $calculator = getSmartEqubCalculator();
    
    switch ($action) {
        case 'diagnose_equb':
            diagnoseEqub($calculator);
            break;
        case 'fix_equb_duration':
            fixEqubDuration($calculator);
            break;
        case 'calculate_correct_member_payout':
            calculateCorrectMemberPayout($calculator);
            break;
        case 'calculate_correct_joint_payout':
            calculateCorrectJointPayout($calculator);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Smart EQUB Fixer API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}

/**
 * Diagnose EQUB for logical errors
 */
function diagnoseEqub($calculator) {
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    try {
        $diagnosis = $calculator->calculateCorrectEqubParameters($equb_id);
        
        if (!$diagnosis['success']) {
            echo json_encode($diagnosis);
            return;
        }
        
        // Get member analysis
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT 
                m.id, m.first_name, m.last_name, m.membership_type,
                m.monthly_payment, m.position_coefficient, m.joint_group_id,
                jmg.group_name, jmg.total_monthly_payment as joint_total,
                jmg.position_coefficient as joint_coefficient
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.membership_type, m.monthly_payment DESC
        ");
        $stmt->execute([$equb_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Analyze each member's correct payout
        $member_analysis = [];
        $processed_joint_groups = [];
        
        foreach ($members as $member) {
            if ($member['membership_type'] === 'joint') {
                if (in_array($member['joint_group_id'], $processed_joint_groups)) {
                    continue; // Skip duplicate joint group members
                }
                $processed_joint_groups[] = $member['joint_group_id'];
                
                $payout_calc = $calculator->calculateCorrectJointGroupPayout($member['joint_group_id']);
                if ($payout_calc['success']) {
                    $member_analysis[] = [
                        'type' => 'joint_group',
                        'name' => $member['group_name'] ?: 'Joint Group',
                        'monthly_payment' => floatval($member['joint_total']),
                        'position_coefficient' => floatval($member['joint_coefficient']),
                        'correct_gross_payout' => $payout_calc['group_gross_payout'],
                        'correct_display_payout' => $payout_calc['group_display_payout'],
                        'individual_splits' => $payout_calc['individual_splits']
                    ];
                }
            } else {
                $payout_calc = $calculator->calculateCorrectMemberPayout($member['id']);
                if ($payout_calc['success']) {
                    $member_analysis[] = [
                        'type' => 'individual',
                        'name' => $member['first_name'] . ' ' . $member['last_name'],
                        'monthly_payment' => floatval($member['monthly_payment']),
                        'position_coefficient' => floatval($member['position_coefficient']),
                        'correct_gross_payout' => $payout_calc['gross_payout'],
                        'correct_display_payout' => $payout_calc['display_payout']
                    ];
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'diagnosis' => $diagnosis,
            'member_analysis' => $member_analysis,
            'issues_found' => [
                'duration_mismatch' => $diagnosis['needs_fix'],
                'total_members' => count($members),
                'total_positions' => $diagnosis['total_position_coefficients']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Diagnose EQUB error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to diagnose EQUB']);
    }
}

/**
 * Fix EQUB duration based on correct calculations
 */
function fixEqubDuration($calculator) {
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    $result = $calculator->fixEqubDuration($equb_id);
    echo json_encode($result);
}

/**
 * Calculate correct member payout
 */
function calculateCorrectMemberPayout($calculator) {
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Member ID is required']);
        return;
    }
    
    $result = $calculator->calculateCorrectMemberPayout($member_id);
    echo json_encode($result);
}

/**
 * Calculate correct joint group payout
 */
function calculateCorrectJointPayout($calculator) {
    $joint_group_id = $_POST['joint_group_id'] ?? '';
    
    if (!$joint_group_id) {
        echo json_encode(['success' => false, 'message' => 'Joint group ID is required']);
        return;
    }
    
    $result = $calculator->calculateCorrectJointGroupPayout($joint_group_id);
    echo json_encode($result);
}
?>