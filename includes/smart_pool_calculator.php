<?php
/**
 * HabeshaEqub - SMART POOL-BASED CALCULATOR
 * Fixes the fundamental logical error in EQUB calculations
 * 
 * CORRECT LOGIC:
 * - Duration = Total Monthly Pool ÷ Regular Tier (NOT member count!)
 * - Gross Payout = Monthly Pool Amount (SAME for everyone!)
 * - Joint groups get Coefficient × Gross, then split
 */

class SmartPoolCalculator {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Calculate CORRECT EQUB metrics (duration is FIXED by admin!)
     */
    public function calculateCorrectEqubMetrics($equb_id) {
        try {
            // Get EQUB settings
            $stmt = $this->db->prepare("
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
            
            // DURATION IS FIXED (set by admin) - NOT calculated!
            $fixed_duration = intval($equb['duration_months']);
            
            // Calculate TOTAL MONTHLY POOL
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                        ELSE m.monthly_payment
                    END as contribution
                FROM members m
                LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
                WHERE m.equb_settings_id = ? AND m.is_active = 1
                GROUP BY 
                    CASE 
                        WHEN m.membership_type = 'joint' THEN m.joint_group_id
                        ELSE m.id
                    END
            ");
            $stmt->execute([$equb_id]);
            $contributions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $total_monthly_pool = array_sum($contributions);
            
            // Count actual positions (individuals + joint groups)
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT 
                    CASE 
                        WHEN m.membership_type = 'joint' THEN CONCAT('joint_', m.joint_group_id)
                        ELSE CONCAT('individual_', m.id)
                    END
                ) as actual_positions
                FROM members m
                WHERE m.equb_settings_id = ? AND m.is_active = 1
            ");
            $stmt->execute([$equb_id]);
            $actual_positions = intval($stmt->fetchColumn());
            
            // GROSS PAYOUT = Monthly Pool Amount (SAME for all positions!)
            $gross_payout_per_position = $total_monthly_pool;
            
            // Check if positions match duration (should be equal)
            $positions_duration_match = ($actual_positions == $fixed_duration);
            
            return [
                'success' => true,
                'total_monthly_pool' => $total_monthly_pool,
                'fixed_duration' => $fixed_duration,
                'actual_positions' => $actual_positions,
                'gross_payout_per_position' => $gross_payout_per_position,
                'regular_tier' => $regular_tier,
                'positions_duration_match' => $positions_duration_match,
                'needs_correction' => !$positions_duration_match,
                'error_type' => $positions_duration_match ? 'none' : 'positions_duration_mismatch'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Calculate CORRECT member payouts using pool-based logic
     */
    public function calculateCorrectMemberPayout($member_id) {
        try {
            // Get member and EQUB data
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    es.regular_payment_tier,
                    es.admin_fee,
                    jmg.total_monthly_payment as joint_payment,
                    jmg.position_coefficient as joint_coefficient
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
                WHERE m.id = ?
            ");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                return ['success' => false, 'message' => 'Member not found'];
            }
            
            // Get correct EQUB metrics
            $pool_metrics = $this->calculateCorrectEqubMetrics($member['equb_settings_id']);
            if (!$pool_metrics['success']) {
                return $pool_metrics;
            }
            
            $gross_payout = $pool_metrics['gross_payout_per_position'];
            $admin_fee = floatval($member['admin_fee']) ?: 20;
            
            if ($member['membership_type'] === 'joint') {
                // Joint member: Get share of (Coefficient × Gross Payout)
                $coefficient = floatval($member['joint_coefficient']) ?: 1.0;
                $joint_total_gross = $gross_payout * $coefficient;
                
                // Calculate individual share within joint group
                $stmt = $this->db->prepare("
                    SELECT 
                        individual_contribution,
                        SUM(individual_contribution) OVER() as total_joint_contribution
                    FROM members 
                    WHERE joint_group_id = ? AND is_active = 1
                ");
                $stmt->execute([$member['joint_group_id']]);
                $joint_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $individual_contribution = floatval($member['individual_contribution']);
                $total_joint_contribution = floatval($joint_members[0]['total_joint_contribution']);
                
                $individual_share = $total_joint_contribution > 0 ? 
                    ($individual_contribution / $total_joint_contribution) : 0.5;
                
                $individual_gross = $joint_total_gross * $individual_share;
                $individual_net = $individual_gross - $admin_fee;
                
                return [
                    'success' => true,
                    'gross_payout' => $individual_gross,
                    'admin_fee' => $admin_fee,
                    'net_payout' => $individual_net,
                    'individual_contribution' => $individual_contribution,
                    'joint_total_gross' => $joint_total_gross,
                    'individual_share_percentage' => $individual_share * 100,
                    'calculation_method' => 'joint_pool_based'
                ];
                
            } else {
                // Individual member: Gets full gross payout
                $net_payout = $gross_payout - $admin_fee;
                
                return [
                    'success' => true,
                    'gross_payout' => $gross_payout,
                    'admin_fee' => $admin_fee,
                    'net_payout' => $net_payout,
                    'calculation_method' => 'individual_pool_based'
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Fix EQUB payout calculations (duration stays FIXED!)
     */
    public function fixEqubPayoutCalculations($equb_id) {
        try {
            $this->db->beginTransaction();
            
            // Get correct metrics
            $pool_metrics = $this->calculateCorrectEqubMetrics($equb_id);
            if (!$pool_metrics['success']) {
                throw new Exception($pool_metrics['message']);
            }
            
            // Update EQUB calculated positions and pool amount (NOT duration!)
            $stmt = $this->db->prepare("
                UPDATE equb_settings 
                SET 
                    calculated_positions = ?,
                    total_pool_amount = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $pool_metrics['actual_positions'], 
                $pool_metrics['total_monthly_pool'] * $pool_metrics['fixed_duration'],
                $equb_id
            ]);
            
            // Update all member payout amounts with correct calculations
            $this->recalculateAllMemberPayouts($equb_id);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'EQUB payout calculations fixed successfully (duration remains ' . $pool_metrics['fixed_duration'] . ' months)',
                'fixed_duration' => $pool_metrics['fixed_duration'],
                'actual_positions' => $pool_metrics['actual_positions'],
                'total_monthly_pool' => $pool_metrics['total_monthly_pool'],
                'gross_payout_per_position' => $pool_metrics['gross_payout_per_position'],
                'positions_duration_match' => $pool_metrics['positions_duration_match']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Recalculate all member display payout amounts
     */
    private function recalculateAllMemberPayouts($equb_id) {
        // Get all members
        $stmt = $this->db->prepare("
            SELECT id FROM members 
            WHERE equb_settings_id = ? AND is_active = 1
        ");
        $stmt->execute([$equb_id]);
        $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($member_ids as $member_id) {
            $payout_calc = $this->calculateCorrectMemberPayout($member_id);
            if ($payout_calc['success']) {
                // Update member display payout amount
                $stmt = $this->db->prepare("
                    UPDATE members 
                    SET display_payout_amount = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$payout_calc['gross_payout'], $member_id]);
            }
        }
    }
    
    /**
     * Generate detailed breakdown for admin review
     */
    public function generateEqubBreakdown($equb_id) {
        $pool_metrics = $this->calculateCorrectEqubMetrics($equb_id);
        if (!$pool_metrics['success']) {
            return $pool_metrics;
        }
        
        // Get detailed member breakdown
        $stmt = $this->db->prepare("
            SELECT 
                m.id,
                m.first_name,
                m.last_name,
                m.monthly_payment,
                m.membership_type,
                m.individual_contribution,
                m.joint_group_id,
                jmg.group_name,
                jmg.total_monthly_payment as joint_payment,
                jmg.position_coefficient as joint_coefficient
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.membership_type, m.joint_group_id, m.id
        ");
        $stmt->execute([$equb_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $breakdown = [
            'pool_metrics' => $pool_metrics,
            'members' => [],
            'summary' => [
                'total_individual_members' => 0,
                'total_joint_groups' => 0,
                'total_positions' => 0,
                'individual_contributions' => 0,
                'joint_contributions' => 0
            ]
        ];
        
        $processed_joints = [];
        
        foreach ($members as $member) {
            $payout_calc = $this->calculateCorrectMemberPayout($member['id']);
            
            $member_data = [
                'member_info' => $member,
                'payout_calculation' => $payout_calc
            ];
            
            if ($member['membership_type'] === 'joint') {
                if (!in_array($member['joint_group_id'], $processed_joints)) {
                    $breakdown['summary']['total_joint_groups']++;
                    $breakdown['summary']['joint_contributions'] += $member['joint_payment'];
                    $processed_joints[] = $member['joint_group_id'];
                }
            } else {
                $breakdown['summary']['total_individual_members']++;
                $breakdown['summary']['individual_contributions'] += $member['monthly_payment'];
            }
            
            $breakdown['members'][] = $member_data;
        }
        
        $breakdown['summary']['total_positions'] = $breakdown['summary']['total_individual_members'] + $breakdown['summary']['total_joint_groups'];
        
        return ['success' => true, 'breakdown' => $breakdown];
    }
}

// Helper function to get the smart calculator
function getSmartPoolCalculator() {
    global $pdo;
    
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }
    
    return new SmartPoolCalculator($pdo);
}
?>