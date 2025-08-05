<?php
/**
 * HabeshaEqub - SMART EQUB Calculator
 * Fixes the fundamental logical error in EQUB calculations
 * Position-coefficient based duration and payout calculations
 */

class SmartEqubCalculator {
    private $pdo;
    
    public function __construct($database) {
        $this->pdo = $database;
    }
    
    /**
     * Calculate CORRECT EQUB parameters based on position coefficients
     * This fixes the fundamental logical error!
     */
    public function calculateCorrectEqubParameters($equb_id) {
        try {
            // Get EQUB settings
            $stmt = $this->pdo->prepare("
                SELECT * FROM equb_settings WHERE id = ?
            ");
            $stmt->execute([$equb_id]);
            $equb = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$equb) {
                return ['success' => false, 'message' => 'EQUB not found'];
            }
            
            $regular_tier = floatval($equb['regular_payment_tier']);
            if ($regular_tier <= 0) {
                return ['success' => false, 'message' => 'Regular payment tier not set'];
            }
            
            // Calculate total position coefficients from ALL members and joint groups
            $total_position_coefficients = $this->calculateTotalPositionCoefficients($equb_id);
            
            // CORRECT DURATION = Total Position Coefficients (rounded up)
            $correct_duration = ceil($total_position_coefficients);
            
            // CORRECT Monthly Pool = Total of all monthly contributions
            $monthly_pool = $this->calculateMonthlyPool($equb_id);
            
            // CORRECT Total Pool = Monthly Pool × Correct Duration
            $total_pool = $monthly_pool * $correct_duration;
            
            // CORRECT Per-Position Payout = Total Pool ÷ Total Position Coefficients
            $per_position_payout = $total_pool / $total_position_coefficients;
            
            return [
                'success' => true,
                'current_duration' => intval($equb['duration_months']),
                'correct_duration' => $correct_duration,
                'total_position_coefficients' => $total_position_coefficients,
                'monthly_pool' => $monthly_pool,
                'total_pool' => $total_pool,
                'per_position_payout' => $per_position_payout,
                'regular_tier' => $regular_tier,
                'needs_fix' => ($correct_duration != intval($equb['duration_months']))
            ];
            
        } catch (Exception $e) {
            error_log("Smart EQUB Calculator error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Calculation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Calculate total position coefficients from all members and joint groups
     */
    private function calculateTotalPositionCoefficients($equb_id) {
        // Individual members
        $stmt = $this->pdo->prepare("
            SELECT SUM(position_coefficient) as individual_total
            FROM members 
            WHERE equb_settings_id = ? AND membership_type = 'individual' AND is_active = 1
        ");
        $stmt->execute([$equb_id]);
        $individual_total = floatval($stmt->fetchColumn()) ?: 0;
        
        // Joint groups (avoid double counting)
        $stmt = $this->pdo->prepare("
            SELECT SUM(position_coefficient) as joint_total
            FROM joint_membership_groups 
            WHERE equb_settings_id = ? AND is_active = 1
        ");
        $stmt->execute([$equb_id]);
        $joint_total = floatval($stmt->fetchColumn()) ?: 0;
        
        return $individual_total + $joint_total;
    }
    
    /**
     * Calculate total monthly pool from all contributions
     */
    private function calculateMonthlyPool($equb_id) {
        // Individual members
        $stmt = $this->pdo->prepare("
            SELECT SUM(monthly_payment) as individual_pool
            FROM members 
            WHERE equb_settings_id = ? AND membership_type = 'individual' AND is_active = 1
        ");
        $stmt->execute([$equb_id]);
        $individual_pool = floatval($stmt->fetchColumn()) ?: 0;
        
        // Joint groups
        $stmt = $this->pdo->prepare("
            SELECT SUM(total_monthly_payment) as joint_pool
            FROM joint_membership_groups 
            WHERE equb_settings_id = ? AND is_active = 1
        ");
        $stmt->execute([$equb_id]);
        $joint_pool = floatval($stmt->fetchColumn()) ?: 0;
        
        return $individual_pool + $joint_pool;
    }
    
    /**
     * Calculate CORRECT payout for a specific member/group
     */
    public function calculateCorrectMemberPayout($member_id, $equb_id = null) {
        try {
            // Get member info
            $stmt = $this->pdo->prepare("
                SELECT m.*, es.id as equb_id, es.admin_fee, es.regular_payment_tier
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.id = ?
            ");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                return ['success' => false, 'message' => 'Member not found'];
            }
            
            $equb_id = $equb_id ?: $member['equb_id'];
            
            // Get correct EQUB parameters
            $equb_params = $this->calculateCorrectEqubParameters($equb_id);
            if (!$equb_params['success']) {
                return $equb_params;
            }
            
            // Calculate member's correct payout
            $member_coefficient = floatval($member['position_coefficient']);
            $per_position_payout = $equb_params['per_position_payout'];
            $admin_fee = floatval($member['admin_fee']) ?: 20;
            
            // CORRECT Gross Payout = Position Coefficient × Per-Position Payout
            $gross_payout = $member_coefficient * $per_position_payout;
            
            // Net payout (subtract admin fee and their own monthly contribution)
            $net_payout = $gross_payout - $admin_fee - floatval($member['monthly_payment']);
            
            // Member-friendly display (hide the monthly deduction)
            $display_payout = $gross_payout - $admin_fee;
            
            return [
                'success' => true,
                'member_id' => $member_id,
                'position_coefficient' => $member_coefficient,
                'gross_payout' => $gross_payout,
                'admin_fee' => $admin_fee,
                'monthly_contribution' => floatval($member['monthly_payment']),
                'net_payout' => $net_payout,
                'display_payout' => $display_payout,
                'per_position_amount' => $per_position_payout
            ];
            
        } catch (Exception $e) {
            error_log("Member payout calculation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Calculation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Calculate CORRECT joint group payout with individual splits
     */
    public function calculateCorrectJointGroupPayout($joint_group_id) {
        try {
            // Get joint group info
            $stmt = $this->pdo->prepare("
                SELECT jmg.*, es.admin_fee
                FROM joint_membership_groups jmg
                JOIN equb_settings es ON jmg.equb_settings_id = es.id
                WHERE jmg.joint_group_id = ?
            ");
            $stmt->execute([$joint_group_id]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$group) {
                return ['success' => false, 'message' => 'Joint group not found'];
            }
            
            // Get correct EQUB parameters
            $equb_params = $this->calculateCorrectEqubParameters($group['equb_settings_id']);
            if (!$equb_params['success']) {
                return $equb_params;
            }
            
            // Calculate group's CORRECT total payout
            $group_coefficient = floatval($group['position_coefficient']);
            $per_position_payout = $equb_params['per_position_payout'];
            $admin_fee = floatval($group['admin_fee']) ?: 20;
            
            // CORRECT Group Gross Payout = Group Position Coefficient × Per-Position Payout
            $group_gross_payout = $group_coefficient * $per_position_payout;
            
            // Group net payout (subtract admin fee and total monthly contribution)
            $group_net_payout = $group_gross_payout - $admin_fee - floatval($group['total_monthly_payment']);
            
            // Group display payout (hide monthly deduction)
            $group_display_payout = $group_gross_payout - $admin_fee;
            
            // Get individual members for splitting
            $stmt = $this->pdo->prepare("
                SELECT * FROM members 
                WHERE joint_group_id = ? AND is_active = 1
                ORDER BY primary_joint_member DESC, created_at ASC
            ");
            $stmt->execute([$joint_group_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate individual splits
            $individual_splits = [];
            foreach ($members as $member) {
                $individual_contribution = floatval($member['individual_contribution']);
                $split_percentage = $individual_contribution / floatval($group['total_monthly_payment']);
                
                $individual_splits[] = [
                    'member_id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'individual_contribution' => $individual_contribution,
                    'split_percentage' => $split_percentage,
                    'gross_share' => $group_gross_payout * $split_percentage,
                    'display_share' => $group_display_payout * $split_percentage,
                    'net_share' => ($group_net_payout + floatval($group['total_monthly_payment'])) * $split_percentage - $individual_contribution
                ];
            }
            
            return [
                'success' => true,
                'group' => $group,
                'group_coefficient' => $group_coefficient,
                'group_gross_payout' => $group_gross_payout,
                'group_display_payout' => $group_display_payout,
                'group_net_payout' => $group_net_payout,
                'admin_fee' => $admin_fee,
                'per_position_amount' => $per_position_payout,
                'individual_splits' => $individual_splits
            ];
            
        } catch (Exception $e) {
            error_log("Joint group payout calculation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Calculation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Fix EQUB duration based on correct calculations
     */
    public function fixEqubDuration($equb_id) {
        try {
            $params = $this->calculateCorrectEqubParameters($equb_id);
            if (!$params['success']) {
                return $params;
            }
            
            if (!$params['needs_fix']) {
                return ['success' => true, 'message' => 'EQUB duration is already correct'];
            }
            
            // Update the EQUB with correct duration
            $stmt = $this->pdo->prepare("
                UPDATE equb_settings 
                SET 
                    duration_months = ?,
                    calculated_positions = ?,
                    total_pool_amount = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $success = $stmt->execute([
                $params['correct_duration'],
                $params['total_position_coefficients'],
                $params['total_pool'],
                $equb_id
            ]);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'EQUB duration fixed successfully',
                    'old_duration' => $params['current_duration'],
                    'new_duration' => $params['correct_duration'],
                    'total_position_coefficients' => $params['total_position_coefficients']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to update EQUB duration'];
            }
            
        } catch (Exception $e) {
            error_log("Fix EQUB duration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to fix EQUB duration: ' . $e->getMessage()];
        }
    }
}

/**
 * Helper function to get the smart calculator instance
 */
function getSmartEqubCalculator() {
    global $pdo;
    
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }
    
    return new SmartEqubCalculator($pdo);
}
?>