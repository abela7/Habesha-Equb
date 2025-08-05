<?php
/**
 * HabeshaEqub - EQUB Position Fixer API
 * Fixes logical errors in EQUB position calculations and member assignments
 */

require_once '../../includes/db.php';
require_once '../../includes/enhanced_equb_calculator.php';

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
$calculator = new EnhancedEqubCalculator($pdo);

try {
    switch ($action) {
        case 'analyze_positions':
            analyzeEqubPositions();
            break;
        case 'auto_fix_positions':
            autoFixEqubPositions();
            break;
        case 'calculate_member_payout':
            calculateMemberPayout();
            break;
        case 'fix_joint_group_positions':
            fixJointGroupPositions();
            break;
        case 'get_equb_analysis':
            getEqubAnalysis();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("EQUB Position Fixer API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}

/**
 * Analyze current EQUB position assignments
 */
function analyzeEqubPositions() {
    global $calculator;
    
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    $analysis = $calculator->calculateEqubPositions($equb_id);
    
    if ($analysis['success']) {
        // Add current vs recommended comparison
        $analysis['comparison'] = [
            'current_issues' => [],
            'recommendations' => []
        ];
        
        // Check for logical issues
        if ($analysis['requires_adjustment']) {
            $analysis['comparison']['current_issues'][] = "Duration ({$analysis['total_positions']} months) doesn't match position count";
        }
        
        // Check for joint group position issues
        foreach ($analysis['position_analysis'] as $member) {
            if ($member['position_coefficient'] > 1.5) {
                $analysis['comparison']['current_issues'][] = 
                    "{$member['name']} represents {$member['position_coefficient']} positions - may need position adjustment";
            }
        }
        
        $analysis['comparison']['recommendations'] = [
            "Set duration to {$analysis['recommended_duration']} months",
            "Update position coefficients for accurate calculations",
            "Reassign positions if necessary"
        ];
    }
    
    echo json_encode($analysis);
}

/**
 * Automatically fix EQUB position issues
 */
function autoFixEqubPositions() {
    global $calculator;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    $result = $calculator->autoFixEqubPositions($equb_id);
    echo json_encode($result);
}

/**
 * Calculate member-friendly payout display
 */
function calculateMemberPayout() {
    global $calculator;
    
    $member_id = intval($_POST['member_id'] ?? $_GET['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Member ID is required']);
        return;
    }
    
    $calculation = $calculator->calculateMemberFriendlyPayout($member_id);
    echo json_encode($calculation);
}

/**
 * Fix specific joint group position assignments
 */
function fixJointGroupPositions() {
    global $pdo, $admin_id;
    
    $joint_group_id = $_POST['joint_group_id'] ?? '';
    $new_position_coefficient = floatval($_POST['position_coefficient'] ?? 1.0);
    
    if (!$joint_group_id) {
        echo json_encode(['success' => false, 'message' => 'Joint group ID is required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update joint group position coefficient
        $stmt = $pdo->prepare("
            UPDATE joint_membership_groups 
            SET position_coefficient = ?, updated_at = NOW()
            WHERE joint_group_id = ?
        ");
        $stmt->execute([$new_position_coefficient, $joint_group_id]);
        
        // Get total group payment to recalculate individual coefficients
        $stmt = $pdo->prepare("
            SELECT total_monthly_payment FROM joint_membership_groups 
            WHERE joint_group_id = ?
        ");
        $stmt->execute([$joint_group_id]);
        $group_payment = $stmt->fetchColumn();
        
        // Update individual member coefficients proportionally
        $stmt = $pdo->prepare("
            UPDATE members 
            SET position_coefficient = (individual_contribution / ?) * ?,
                updated_at = NOW()
            WHERE joint_group_id = ? AND is_active = 1
        ");
        $stmt->execute([$group_payment, $new_position_coefficient, $joint_group_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Joint group positions updated successfully',
            'new_coefficient' => $new_position_coefficient
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Joint group position fix error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update joint group positions']);
    }
}

/**
 * Get comprehensive EQUB analysis
 */
function getEqubAnalysis() {
    global $pdo, $calculator;
    
    $equb_id = intval($_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    try {
        // Get EQUB details
        $stmt = $pdo->prepare("
            SELECT 
                es.*,
                COUNT(m.id) as actual_member_count,
                SUM(m.monthly_payment) as total_monthly_pool,
                SUM(m.position_coefficient) as total_position_coefficients
            FROM equb_settings es
            LEFT JOIN members m ON m.equb_settings_id = es.id AND m.is_active = 1
            WHERE es.id = ?
            GROUP BY es.id
        ");
        $stmt->execute([$equb_id]);
        $equb = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb) {
            echo json_encode(['success' => false, 'message' => 'EQUB not found']);
            return;
        }
        
        // Get position analysis
        $position_analysis = $calculator->calculateEqubPositions($equb_id);
        
        // Get member details with position coefficients
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.individual_contribution
                    ELSE m.monthly_payment
                END as effective_contribution,
                jmg.group_name,
                jmg.payout_split_method,
                jmg.position_coefficient as group_position_coefficient
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.payout_position, m.created_at
        ");
        $stmt->execute([$equb_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Identify issues
        $issues = [];
        $recommendations = [];
        
        if ($equb['duration_months'] != $equb['total_position_coefficients']) {
            $issues[] = "Duration mismatch: {$equb['duration_months']} months vs {$equb['total_position_coefficients']} positions";
            $recommendations[] = "Adjust duration to match position count";
        }
        
        // Check for joint group issues
        foreach ($members as $member) {
            if ($member['membership_type'] === 'joint' && $member['position_coefficient'] > 1.5) {
                $issues[] = "Member {$member['first_name']} {$member['last_name']} has high position coefficient: {$member['position_coefficient']}";
            }
        }
        
        echo json_encode([
            'success' => true,
            'equb_details' => $equb,
            'members' => $members,
            'position_analysis' => $position_analysis,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'financial_summary' => [
                'total_monthly_pool' => $equb['total_monthly_pool'],
                'projected_total' => $equb['total_monthly_pool'] * $equb['duration_months'],
                'regular_tier' => $equb['regular_payment_tier'],
                'admin_fee_total' => $equb['actual_member_count'] * $equb['admin_fee']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("EQUB analysis error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to analyze EQUB']);
    }
}
?>