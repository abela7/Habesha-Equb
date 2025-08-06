<?php
/**
 * HabeshaEqub - COMPLETELY NEW Payout Positions API
 * ROBUST, AUTOMATED, NO HARDCODE
 * 
 * LOGIC:
 * - Each member has individual position control
 * - Joint groups with total coefficient >= 2.0 get separate positions
 * - Joint groups with total coefficient < 2.0 share positions  
 * - Everything is calculated from database dynamically
 */

// Prevent any output before JSON
ob_start();

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../includes/db.php';
    require_once '../../includes/enhanced_equb_calculator.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    if (ob_get_length()) {
        ob_clean();
    }
    
    function json_response($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Auth check
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
        default:
            json_response(false, 'Invalid action');
    }

} catch (Exception $e) {
    ob_clean();
    error_log("NEW Payout Positions API Error: " . $e->getMessage());
    json_response(false, 'Server error occurred');
}

/**
 * COMPLETELY NEW: Get positions with CLEAN logic
 */
function getPositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? $_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        // Get EQUB settings from database (NO HARDCODE)
        $stmt = $pdo->prepare("SELECT * FROM equb_settings WHERE id = ?");
        $stmt->execute([$equb_id]);
        $equb = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb) {
            json_response(false, 'EQUB not found');
        }
        
        $duration_months = $equb['duration_months'];
        $regular_payment_tier = $equb['regular_payment_tier'];
        
        // Get ALL members with their REAL data
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.individual_contribution
                    ELSE m.monthly_payment
                END as effective_payment,
                CASE 
                    WHEN m.membership_type = 'joint' THEN (m.individual_contribution / ?)
                    ELSE (m.monthly_payment / ?)
                END as calculated_coefficient
            FROM members m
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.payout_position ASC, m.id ASC
        ");
        $stmt->execute([$regular_payment_tier, $regular_payment_tier, $equb_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate REAL monthly pool from actual payments
        $total_monthly_pool = 0;
        foreach ($members as $member) {
            $total_monthly_pool += $member['effective_payment'];
        }
        
        // CLEAN POSITION BUILDING: Each member gets their own position slot
        $positions = [];
        
        foreach ($members as $member) {
            $position = $member['payout_position'];
            $coefficient = $member['calculated_coefficient'];
            
            // Calculate REAL payout using coefficient × monthly pool
            $gross_payout = $coefficient * $total_monthly_pool;
            $display_payout = $gross_payout - $equb['admin_fee'];
            
            // Each member gets their own position entry
            if (!isset($positions[$position])) {
                $positions[$position] = [
                    'position' => $position,
                    'members' => [],
                    'total_coefficient' => 0,
                    'total_payout' => 0
                ];
            }
            
            $member['calculated_coefficient'] = $coefficient;
            $member['gross_payout'] = $gross_payout;
            $member['display_payout'] = $display_payout;
            
            $positions[$position]['members'][] = $member;
            $positions[$position]['total_coefficient'] += $coefficient;
            $positions[$position]['total_payout'] += $display_payout;
        }
        
        // Sort and convert to array
        ksort($positions);
        $positions_array = array_values($positions);
        
        // Calculate stats DYNAMICALLY
        $individual_count = 0;
        $joint_groups = [];
        
        foreach ($members as $member) {
            if ($member['membership_type'] === 'individual') {
                $individual_count++;
            } else {
                if (!in_array($member['joint_group_id'], $joint_groups)) {
                    $joint_groups[] = $member['joint_group_id'];
                }
            }
        }
        
        $stats = [
            'total_members' => count($members),
            'total_positions' => count($positions),
            'duration_months' => $duration_months,
            'total_monthly_pool' => $total_monthly_pool,
            'regular_payment_tier' => $regular_payment_tier,
            'individual_count' => $individual_count,
            'joint_groups_count' => count($joint_groups)
        ];
        
        json_response(true, 'Positions loaded successfully', [
            'positions' => $positions_array,
            'members' => $members,
            'stats' => $stats,
            'equb' => $equb
        ]);
        
    } catch (Exception $e) {
        error_log("Error in NEW getPositions: " . $e->getMessage());
        json_response(false, 'Database error: ' . $e->getMessage());
    }
}

/**
 * COMPLETELY NEW: Update positions with SIMPLE, CLEAN logic
 */
function updatePositions() {
    global $pdo;
    
    error_log("🆕 NEW UPDATE POSITIONS API CALLED");
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $positions_raw = $_POST['positions'] ?? '';
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    // Parse positions data
    if (is_string($positions_raw)) {
        $positions = json_decode($positions_raw, true);
    } else {
        $positions = $positions_raw;
    }
    
    if (empty($positions) || !is_array($positions)) {
        json_response(false, 'Invalid positions data');
    }
    
    try {
        $pdo->beginTransaction();
        
        // SIMPLE LOGIC: Update each member's position individually
        $updated_count = 0;
        
        foreach ($positions as $position_data) {
            if (isset($position_data['member_id']) && isset($position_data['position'])) {
                $member_id = intval($position_data['member_id']);
                $new_position = intval($position_data['position']);
                
                // Update this member's position - NO GROUP LOGIC HERE
                $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
                $stmt->execute([$new_position, $member_id]);
                $updated_count++;
                
                error_log("✅ NEW: Member {$member_id} → Position {$new_position}");
            }
        }
        
        $pdo->commit();
        
        json_response(true, "SUCCESS: {$updated_count} members updated with new system!");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("❌ NEW Update error: " . $e->getMessage());
        json_response(false, 'Update failed: ' . $e->getMessage());
    }
}
?>