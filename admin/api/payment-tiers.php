<?php
/**
 * HabeshaEqub - Payment Tiers API
 * Handle payment tier management operations
 */

require_once '../../includes/db.php';

// Set JSON header
header('Content-Type: application/json');

// Security check
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// CSRF token verification for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ]);
        exit;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'update_tiers':
            updatePaymentTiers();
            break;
        case 'update_regular_tier':
            updateRegularTier();
            break;
        case 'get_tier_impact':
            getTierImpact();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Payment Tiers API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}

/**
 * Update payment tiers for an EQUB
 */
function updatePaymentTiers() {
    global $pdo, $admin_id;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $tiers_json = $_POST['tiers'] ?? '';
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    // Validate JSON
    $tiers = json_decode($tiers_json, true);
    if (!is_array($tiers)) {
        echo json_encode(['success' => false, 'message' => 'Invalid tiers data']);
        return;
    }
    
    // Validate each tier
    foreach ($tiers as $tier) {
        if (!isset($tier['amount']) || !isset($tier['tag']) || 
            !is_numeric($tier['amount']) || $tier['amount'] <= 0 || 
            empty(trim($tier['tag']))) {
            echo json_encode(['success' => false, 'message' => 'Invalid tier data: amount and tag are required']);
            return;
        }
    }
    
    // Check for duplicate amounts
    $amounts = array_column($tiers, 'amount');
    if (count($amounts) !== count(array_unique($amounts))) {
        echo json_encode(['success' => false, 'message' => 'Duplicate tier amounts are not allowed']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update payment tiers
        $stmt = $pdo->prepare("
            UPDATE equb_settings 
            SET payment_tiers = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([json_encode($tiers), $equb_id]);
        
        // Recalculate positions if regular tier is set
        $stmt = $pdo->prepare("SELECT regular_payment_tier FROM equb_settings WHERE id = ?");
        $stmt->execute([$equb_id]);
        $regular_tier = $stmt->fetchColumn();
        
        if ($regular_tier > 0) {
            recalculatePositionCoefficients($equb_id, $regular_tier);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment tiers updated successfully',
            'tiers' => $tiers
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Update tiers error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update payment tiers']);
    }
}

/**
 * Update regular payment tier
 */
function updateRegularTier() {
    global $pdo, $admin_id;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $regular_tier = floatval($_POST['regular_tier'] ?? 0);
    
    if (!$equb_id || $regular_tier <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid EQUB ID and regular tier amount are required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update regular tier
        $stmt = $pdo->prepare("
            UPDATE equb_settings 
            SET regular_payment_tier = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$regular_tier, $equb_id]);
        
        // Recalculate position coefficients for all members
        recalculatePositionCoefficients($equb_id, $regular_tier);
        
        // Update calculated positions
        updateCalculatedPositions($equb_id);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Regular tier updated successfully',
            'regular_tier' => $regular_tier
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Update regular tier error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update regular tier']);
    }
}

/**
 * Recalculate position coefficients based on regular tier
 */
function recalculatePositionCoefficients($equb_id, $regular_tier) {
    global $pdo;
    
    // Update individual members
    $stmt = $pdo->prepare("
        UPDATE members 
        SET position_coefficient = monthly_payment / ?,
            updated_at = NOW()
        WHERE equb_settings_id = ? AND membership_type = 'individual' AND is_active = 1
    ");
    $stmt->execute([$regular_tier, $equb_id]);
    
    // Update joint groups
    $stmt = $pdo->prepare("
        UPDATE joint_membership_groups 
        SET position_coefficient = total_monthly_payment / ?,
            updated_at = NOW()
        WHERE equb_settings_id = ?
    ");
    $stmt->execute([$regular_tier, $equb_id]);
    
    // Update joint members individually
    $stmt = $pdo->prepare("
        UPDATE members m
        JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        SET m.position_coefficient = m.individual_contribution / ?,
            m.updated_at = NOW()
        WHERE m.equb_settings_id = ? AND m.membership_type = 'joint' AND m.is_active = 1
    ");
    $stmt->execute([$regular_tier, $equb_id]);
}

/**
 * Update calculated positions based on position coefficients
 */
function updateCalculatedPositions($equb_id) {
    global $pdo;
    
    // Get total position coefficients
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.position_coefficient
                    ELSE m.position_coefficient
                END
            ), 0) as total_coefficients
        FROM members m
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        WHERE m.equb_settings_id = ? AND m.is_active = 1
    ");
    $stmt->execute([$equb_id]);
    $total_coefficients = $stmt->fetchColumn();
    
    // Update calculated positions (round up to nearest integer)
    $calculated_positions = ceil($total_coefficients);
    
    $stmt = $pdo->prepare("
        UPDATE equb_settings 
        SET 
            calculated_positions = ?,
            duration_months = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$calculated_positions, $calculated_positions, $equb_id]);
}

/**
 * Get tier impact analysis
 */
function getTierImpact() {
    global $pdo;
    
    $equb_id = intval($_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    try {
        // Get EQUB data
        $stmt = $pdo->prepare("
            SELECT 
                payment_tiers, regular_payment_tier, calculated_positions,
                max_members, current_members
            FROM equb_settings 
            WHERE id = ?
        ");
        $stmt->execute([$equb_id]);
        $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb_data) {
            echo json_encode(['success' => false, 'message' => 'EQUB not found']);
            return;
        }
        
        $tiers = json_decode($equb_data['payment_tiers'], true) ?: [];
        $regular_tier = floatval($equb_data['regular_payment_tier']);
        
        // Calculate impact metrics
        $impact = [
            'total_tiers' => count($tiers),
            'regular_tier' => $regular_tier,
            'calculated_positions' => $equb_data['calculated_positions'],
            'max_members' => $equb_data['max_members'],
            'current_members' => $equb_data['current_members']
        ];
        
        if (!empty($tiers)) {
            $amounts = array_column($tiers, 'amount');
            $impact['min_amount'] = min($amounts);
            $impact['max_amount'] = max($amounts);
            
            if ($regular_tier > 0) {
                $coefficients = array_map(function($tier) use ($regular_tier) {
                    return $tier['amount'] / $regular_tier;
                }, $tiers);
                
                $impact['min_coefficient'] = min($coefficients);
                $impact['max_coefficient'] = max($coefficients);
                $impact['total_available_positions'] = array_sum($coefficients);
            }
        }
        
        echo json_encode([
            'success' => true,
            'impact' => $impact,
            'tiers' => $tiers
        ]);
        
    } catch (Exception $e) {
        error_log("Tier impact error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to calculate tier impact']);
    }
}
?>