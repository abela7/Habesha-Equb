<?php
/**
 * HabeshaEqub - Enhanced Payout Positions Management API V2
 * Integrates with new position coefficient × monthly pool logic
 */

// Prevent any output before JSON
ob_start();

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include database connection and enhanced calculator
    require_once '../../includes/db.php';
    require_once '../../includes/enhanced_equb_calculator_v2.php';
    
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Clean any output
    if (ob_get_length()) {
        ob_clean();
    }
    
    /**
     * JSON response helper
     */
    function json_response($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Simple admin authentication check
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        json_response(false, 'Unauthorized access');
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_positions':
            getPositions();
            break;
        case 'update_positions':
            updatePositions();
            break;
        case 'auto_sort':
            autoSortPositions();
            break;
        default:
            json_response(false, 'Invalid action');
    }

} catch (Exception $e) {
    ob_clean();
    error_log("Payout Positions API Error: " . $e->getMessage());
    json_response(false, 'Server error occurred');
}

/**
 * Get positions with enhanced calculations
 */
function getPositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        // Get enhanced calculator
        $calculator = getEnhancedEqubCalculatorV2();
        
        // Get EQUB settings
        $stmt = $pdo->prepare("
            SELECT es.*, 
                   COUNT(m.id) as actual_member_count,
                   SUM(CASE WHEN m.membership_type = 'joint' THEN m.individual_contribution ELSE m.monthly_payment END) as total_monthly_pool
            FROM equb_settings es
            LEFT JOIN members m ON es.id = m.equb_settings_id AND m.is_active = 1
            WHERE es.id = ?
            GROUP BY es.id
        ");
        $stmt->execute([$equb_id]);
        $equb = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb) {
            json_response(false, 'EQUB not found');
        }
        
        // Get members with enhanced calculations
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.group_name
                    ELSE NULL
                END as joint_group_name,
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.individual_contribution
                    ELSE m.monthly_payment
                END as effective_payment
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.payout_position ASC, m.created_at ASC
        ");
        $stmt->execute([$equb_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate enhanced payouts for each member
        $enhanced_members = [];
        $total_coefficient = 0;
        
        foreach ($members as $member) {
            $payout_result = $calculator->calculateMemberFriendlyPayout($member['id']);
            
            if ($payout_result['success']) {
                $calc = $payout_result['calculation'];
                $member['expected_payout'] = $calc['display_payout'];
                $member['gross_payout'] = $calc['gross_payout'];
                $member['position_coefficient'] = $calc['position_coefficient'];
                $member['formula_used'] = $calc['formula_used'];
                $member['calculation_method'] = $calc['calculation_method'];
                
                $total_coefficient += $calc['position_coefficient'];
            } else {
                $member['expected_payout'] = 0;
                $member['gross_payout'] = 0;
                $member['position_coefficient'] = 0;
                $member['formula_used'] = 'Error';
                $member['calculation_method'] = 'Error';
            }
            
            $enhanced_members[] = $member;
        }
        
        // Enhanced statistics
        $stats = [
            'total_members' => count($enhanced_members),
            'total_individual_members' => count(array_filter($enhanced_members, fn($m) => $m['membership_type'] === 'individual')),
            'total_joint_members' => count(array_filter($enhanced_members, fn($m) => $m['membership_type'] === 'joint')),
            'total_positions' => $total_coefficient,
            'total_monthly_pool' => $equb['total_monthly_pool'],
            'duration_months' => $equb['duration_months'],
            'admin_fee' => $equb['admin_fee'],
            'regular_payment_tier' => $equb['regular_payment_tier'],
            'calculated_positions' => $equb['calculated_positions'],
            'position_balance' => abs($total_coefficient - $equb['duration_months']) < 0.1
        ];
        
        json_response(true, 'Positions loaded successfully', [
            'members' => $enhanced_members,
            'stats' => $stats,
            'equb' => $equb
        ]);
        
    } catch (Exception $e) {
        error_log("Error in getPositions: " . $e->getMessage());
        json_response(false, 'Database error occurred');
    }
}

/**
 * Update position order
 */
function updatePositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $positions = $_POST['positions'] ?? [];
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    if (empty($positions) || !is_array($positions)) {
        json_response(false, 'Positions data is required');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update positions
        $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ? AND equb_settings_id = ?");
        
        foreach ($positions as $position => $member_id) {
            $stmt->execute([($position + 1), intval($member_id), $equb_id]);
        }
        
        // Update joint groups positions as well
        $stmt = $pdo->prepare("
            UPDATE joint_membership_groups jmg
            SET payout_position = (
                SELECT MIN(m.payout_position) 
                FROM members m 
                WHERE m.joint_group_id = jmg.joint_group_id AND m.is_active = 1
            )
            WHERE jmg.equb_settings_id = ?
        ");
        $stmt->execute([$equb_id]);
        
        $pdo->commit();
        
        json_response(true, 'Positions updated successfully');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating positions: " . $e->getMessage());
        json_response(false, 'Failed to update positions');
    }
}

/**
 * Auto-sort positions based on different criteria
 */
function autoSortPositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $sort_method = $_POST['sort_method'] ?? 'registration_order';
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get members based on sort method
        switch ($sort_method) {
            case 'payment_amount':
                $orderBy = "effective_payment DESC, m.created_at ASC";
                break;
            case 'position_coefficient':
                $orderBy = "m.position_coefficient DESC, m.created_at ASC";
                break;
            case 'random':
                $orderBy = "RAND()";
                break;
            case 'alphabetical':
                $orderBy = "m.first_name ASC, m.last_name ASC";
                break;
            default: // registration_order
                $orderBy = "m.created_at ASC";
        }
        
        $stmt = $pdo->prepare("
            SELECT m.id,
                   CASE 
                       WHEN m.membership_type = 'joint' THEN m.individual_contribution
                       ELSE m.monthly_payment
                   END as effective_payment
            FROM members m
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY {$orderBy}
        ");
        $stmt->execute([$equb_id]);
        $sorted_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update positions
        $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
        
        foreach ($sorted_members as $index => $member) {
            $stmt->execute([($index + 1), $member['id']]);
        }
        
        // Update joint groups positions
        $stmt = $pdo->prepare("
            UPDATE joint_membership_groups jmg
            SET payout_position = (
                SELECT MIN(m.payout_position) 
                FROM members m 
                WHERE m.joint_group_id = jmg.joint_group_id AND m.is_active = 1
            )
            WHERE jmg.equb_settings_id = ?
        ");
        $stmt->execute([$equb_id]);
        
        $pdo->commit();
        
        json_response(true, "Positions sorted by {$sort_method} successfully");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error auto-sorting positions: " . $e->getMessage());
        json_response(false, 'Failed to sort positions');
    }
}
?>