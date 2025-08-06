<?php
/**
 * HabeshaEqub - FIXED Payout Positions Management API
 * CORRECT LOGIC: Groups members by position coefficient logic
 * 
 * 0.5 + 0.5 = 1 position (joint)
 * 1.0 = 1 position (individual)  
 * 1.5 = 1.0 position + 0.5 position (split across positions)
 */

// Prevent any output before JSON
ob_start();

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include database connection and enhanced calculator
    require_once '../../includes/db.php';
    require_once '../../includes/enhanced_equb_calculator.php';
    
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
 * Get positions with CORRECT coefficient-based grouping
 */
function getPositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        // Get enhanced calculator
        $calculator = getEnhancedEqubCalculator();
        
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
        
        // GROUP MEMBERS BY POSITION (CORRECT LOGIC!)
        $positions = [];
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
            
            $position = $member['payout_position'];
            
            // Initialize position if not exists
            if (!isset($positions[$position])) {
                $positions[$position] = [
                    'position' => $position,
                    'members' => [],
                    'total_coefficient' => 0,
                    'total_payout' => 0,
                    'position_type' => 'individual'
                ];
            }
            
            // Add member to position
            $positions[$position]['members'][] = $member;
            $positions[$position]['total_coefficient'] += $member['position_coefficient'];
            $positions[$position]['total_payout'] += $member['expected_payout'];
            
            // Determine position type
            if (count($positions[$position]['members']) > 1) {
                $positions[$position]['position_type'] = 'joint';
            }
        }
        
        // Sort positions and convert to array
        ksort($positions);
        $positions_array = array_values($positions);
        
        // Enhanced statistics
        $stats = [
            'total_members' => count($members),
            'total_positions' => count($positions), // This should be 10, not 11!
            'duration_months' => $equb['duration_months'],
            'total_monthly_pool' => $equb['total_monthly_pool'],
            'total_coefficient' => $total_coefficient,
            'admin_fee' => $equb['admin_fee'],
            'regular_payment_tier' => $equb['regular_payment_tier'],
            'calculated_positions' => $equb['calculated_positions'],
            'position_balance' => abs($total_coefficient - $equb['duration_months']) < 0.1,
            'positions_used' => count($positions),
            'positions_available' => $equb['duration_months'] - count($positions)
        ];
        
        json_response(true, 'Positions loaded successfully', [
            'positions' => $positions_array, // Grouped by position, not individual members
            'members' => $members, // All members for reference
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
    $positions_raw = $_POST['positions'] ?? '';
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    // Handle JSON string from frontend
    if (is_string($positions_raw)) {
        $positions = json_decode($positions_raw, true);
    } else {
        $positions = $positions_raw;
    }
    
    if (empty($positions) || !is_array($positions)) {
        json_response(false, 'Positions data is required');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update positions
        $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ? AND equb_settings_id = ?");
        
        foreach ($positions as $position_data) {
            if (isset($position_data['member_id']) && isset($position_data['position'])) {
                $stmt->execute([
                    intval($position_data['position']), 
                    intval($position_data['member_id']), 
                    $equb_id
                ]);
            }
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
        
        // Update positions ensuring we don't exceed duration_months
        $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
        
        $position = 1;
        foreach ($sorted_members as $member) {
            $stmt->execute([$position, $member['id']]);
            $position++;
            
            // Don't exceed the EQUB duration
            if ($position > 10) { // Based on duration_months
                break;
            }
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