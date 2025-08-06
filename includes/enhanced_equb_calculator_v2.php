<?php
/**
 * HabeshaEqub - Enhanced EQUB Calculator V2 - TOP-TIER SYSTEM
 * CORRECT LOGIC: Position Coefficient × Monthly Pool (NO HARDCODED VALUES!)
 * 
 * Michael (1.5 coefficient): 1.5 × £10,000 = £15,000 ✅
 * Koki (0.5 coefficient): 0.5 × £10,000 = £5,000 ✅
 * Individual (1.0 coefficient): 1.0 × £10,000 = £10,000 ✅
 */

class EnhancedEqubCalculatorV2 {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Calculate positions based on regular payment tier
     * CORE LOGIC: Dynamic coefficient calculation
     * 
     * @param int $equb_id
     * @return array Position calculation results
     */
    public function calculateEqubPositions($equb_id) {
        try {
            // Get EQUB settings
            $stmt = $this->db->prepare("
                SELECT * FROM equb_settings WHERE id = ?
            ");
            $stmt->execute([$equb_id]);
            $equb = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$equb) {
                throw new Exception("EQUB not found");
            }
            
            $regular_tier = (float)$equb['regular_payment_tier'];
            
            // Get all members and their contributions
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    CASE 
                        WHEN m.membership_type = 'joint' THEN m.individual_contribution
                        ELSE m.monthly_payment
                    END as effective_contribution,
                    m.position_coefficient
                FROM members m 
                WHERE m.equb_settings_id = ? AND m.is_active = 1
                ORDER BY m.payout_position, m.created_at
            ");
            $stmt->execute([$equb_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total monthly pool and positions
            $position_analysis = [];
            $total_positions = 0;
            $total_monthly_pool = 0;
            
            foreach ($members as $member) {
                $contribution = (float)$member['effective_contribution'];
                $position_coefficient = (float)$member['position_coefficient'];
                
                $total_monthly_pool += $contribution;
                
                $position_analysis[] = [
                    'member_id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'contribution' => $contribution,
                    'position_coefficient' => $position_coefficient,
                    'membership_type' => $member['membership_type'],
                    'joint_group_id' => $member['joint_group_id'],
                    'expected_payout' => $position_coefficient * $total_monthly_pool // This will be recalculated
                ];
                
                $total_positions += $position_coefficient;
            }
            
            // Recalculate expected payouts with correct total pool
            foreach ($position_analysis as &$analysis) {
                $analysis['expected_payout'] = $analysis['position_coefficient'] * $total_monthly_pool;
            }
            
            return [
                'success' => true,
                'equb_id' => $equb_id,
                'regular_payment_tier' => $regular_tier,
                'total_monthly_pool' => $total_monthly_pool,
                'total_positions' => $total_positions,
                'recommended_duration' => ceil($total_positions),
                'actual_duration' => $equb['duration_months'],
                'position_analysis' => $position_analysis,
                'requires_adjustment' => (abs($total_positions - $equb['duration_months']) > 0.1)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate member-friendly payout
     * CORRECT LOGIC: Position Coefficient × Monthly Pool
     * 
     * @param int $member_id
     * @return array Calculation results
     */
    public function calculateMemberFriendlyPayout($member_id) {
        try {
            // Get member and EQUB data
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    es.duration_months,
                    es.admin_fee,
                    es.regular_payment_tier,
                    es.payout_day,
                    jmg.total_monthly_payment as joint_payment,
                    jmg.position_coefficient as joint_coefficient
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
                WHERE m.id = ? AND m.is_active = 1
            ");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                throw new Exception("Member not found");
            }
            
            $duration = (int)$member['duration_months'];
            $admin_fee = (float)$member['admin_fee'];
            $regular_payment_tier = (float)$member['regular_payment_tier'];
            
            // Calculate TOTAL MONTHLY POOL (all contributions combined)
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN m2.membership_type = 'joint' THEN m2.individual_contribution
                        ELSE m2.monthly_payment
                    END as contribution
                FROM members m2
                WHERE m2.equb_settings_id = ? AND m2.is_active = 1
            ");
            $stmt->execute([$member['equb_settings_id']]);
            $contributions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $total_monthly_pool = array_sum($contributions);
            
            // CORRECT LOGIC: Position Coefficient × Monthly Pool
            if ($member['membership_type'] === 'joint') {
                $individual_contribution = (float)$member['individual_contribution'];
                $monthly_payment = $individual_contribution;
                $position_coefficient = (float)$member['position_coefficient'] ?: ($individual_contribution / $regular_payment_tier);
            } else {
                // Individual membership
                $monthly_payment = (float)$member['monthly_payment'];
                $position_coefficient = (float)$member['position_coefficient'] ?: ($monthly_payment / $regular_payment_tier);
            }
            
            // 🎯 THE MAGIC FORMULA: Position Coefficient × Monthly Pool
            $gross_payout = $position_coefficient * $total_monthly_pool;
            
            // REAL calculation (what actually happens)
            $real_net_payout = $gross_payout - $monthly_payment - $admin_fee;
            
            // MEMBER-FRIENDLY calculation (what we show them - gross minus admin fee only)
            $display_payout = $gross_payout - $admin_fee;
            
            return [
                'success' => true,
                'member_info' => [
                    'id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'monthly_payment' => $monthly_payment,
                    'position_coefficient' => $position_coefficient,
                    'payout_position' => $member['payout_position'],
                    'membership_type' => $member['membership_type']
                ],
                'calculation' => [
                    'gross_payout' => $gross_payout,
                    'admin_fee' => $admin_fee,
                    'monthly_deduction' => $monthly_payment, // Hidden from member display
                    'display_payout' => $display_payout, // What member sees (COEFFICIENT × POOL - ADMIN_FEE)
                    'real_net_payout' => $real_net_payout, // What member actually gets
                    'position_coefficient' => $position_coefficient,
                    'total_monthly_pool' => $total_monthly_pool,
                    'duration_months' => $duration,
                    'regular_payment_tier' => $regular_payment_tier,
                    'calculation_method' => 'position_coefficient_x_monthly_pool',
                    'formula_used' => "{$position_coefficient} × £{$total_monthly_pool} = £{$gross_payout}"
                ],
                'payout_date' => $this->calculatePayoutDate($member)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate payout date based on position and EQUB settings
     */
    private function calculatePayoutDate($member) {
        try {
            $start_date = new DateTime($member['start_date'] ?? '2025-07-01');
            $payout_day = (int)($member['payout_day'] ?? 5);
            $payout_position = (int)$member['payout_position'];
            
            // Calculate payout month (position - 1 because position 1 = month 0)
            $payout_month = $start_date->modify("+{$payout_position} months");
            $payout_month->setDate($payout_month->format('Y'), $payout_month->format('n'), $payout_day);
            
            return $payout_month->format('Y-m-d');
        } catch (Exception $e) {
            return date('Y-m-d');
        }
    }
    
    /**
     * Validate EQUB financial balance
     */
    public function validateEqubBalance($equb_id) {
        try {
            $positions_result = $this->calculateEqubPositions($equb_id);
            
            if (!$positions_result['success']) {
                return $positions_result;
            }
            
            $total_pool = $positions_result['total_monthly_pool'];
            $total_coefficients = $positions_result['total_positions'];
            $duration = $positions_result['actual_duration'];
            
            // Total contributions over duration
            $total_contributions = $total_pool * $duration;
            
            // Total expected payouts
            $total_expected_payouts = 0;
            foreach ($positions_result['position_analysis'] as $analysis) {
                $total_expected_payouts += $analysis['expected_payout'];
            }
            
            return [
                'success' => true,
                'financial_summary' => [
                    'total_monthly_pool' => $total_pool,
                    'total_positions' => $total_coefficients,
                    'duration_months' => $duration,
                    'total_contributions' => $total_contributions,
                    'total_expected_payouts' => $total_expected_payouts,
                    'balance_difference' => $total_contributions - $total_expected_payouts,
                    'is_balanced' => abs($total_contributions - $total_expected_payouts) < 0.01
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

/**
 * Helper function to get enhanced calculator instance
 */
function getEnhancedEqubCalculatorV2() {
    global $pdo;
    return new EnhancedEqubCalculatorV2($pdo);
}
?>