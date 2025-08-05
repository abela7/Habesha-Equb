<?php
/**
 * HabeshaEqub - Enhanced EQUB Calculator with Regular Tier Logic
 * Top-tier financial calculation engine that properly handles position assignment
 * and member-friendly payout displays
 */

class EnhancedEqubCalculator {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Calculate positions based on regular payment tier
     * This is the CORE logic that was missing!
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
                    END as effective_contribution
                FROM members m 
                WHERE m.equb_settings_id = ? AND m.is_active = 1
                ORDER BY m.payout_position, m.created_at
            ");
            $stmt->execute([$equb_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate position coefficients for each member
            $position_analysis = [];
            $total_positions = 0;
            
            foreach ($members as $member) {
                $contribution = (float)$member['effective_contribution'];
                $position_coefficient = $contribution / $regular_tier;
                
                // Round to nearest 0.5 for practical purposes
                $position_coefficient = round($position_coefficient * 2) / 2;
                
                $position_analysis[] = [
                    'member_id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'contribution' => $contribution,
                    'position_coefficient' => $position_coefficient,
                    'membership_type' => $member['membership_type'],
                    'joint_group_id' => $member['joint_group_id']
                ];
                
                $total_positions += $position_coefficient;
            }
            
            return [
                'success' => true,
                'equb_id' => $equb_id,
                'regular_payment_tier' => $regular_tier,
                'total_positions' => $total_positions,
                'recommended_duration' => ceil($total_positions),
                'position_analysis' => $position_analysis,
                'requires_adjustment' => ($total_positions != $equb['duration_months'])
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate member-friendly payout amounts
     * Shows the amount members want to see (gross - monthly payment not visible)
     * 
     * @param int $member_id
     * @return array Payout calculation with member-friendly display
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
                    es.payout_day
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.id = ? AND m.is_active = 1
            ");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                throw new Exception("Member not found");
            }
            
            $monthly_payment = (float)$member['monthly_payment'];
            $duration = (int)$member['duration_months'];
            $admin_fee = (float)$member['admin_fee'];
            $position_coefficient = (float)$member['position_coefficient'] ?: 1.0;
            
            // TRADITIONAL EQUB CALCULATION
            $gross_payout = $monthly_payment * $duration;
            
            // REAL calculation (what actually happens)
            $real_net_payout = $gross_payout - $monthly_payment - $admin_fee;
            
            // MEMBER-FRIENDLY calculation (what we show them)
            $display_payout = $gross_payout - $admin_fee;
            
            // Handle joint memberships
            $joint_split_info = null;
            if ($member['membership_type'] === 'joint') {
                $joint_split_info = $this->calculateJointSplit($member['joint_group_id'], $member_id);
                
                if ($joint_split_info['success']) {
                    $display_payout = $joint_split_info['member_display_amount'];
                    $real_net_payout = $joint_split_info['member_real_amount'];
                }
            }
            
            return [
                'success' => true,
                'member_info' => [
                    'id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'monthly_payment' => $monthly_payment,
                    'position_coefficient' => $position_coefficient,
                    'payout_position' => $member['payout_position']
                ],
                'calculation' => [
                    'gross_payout' => $gross_payout,
                    'admin_fee' => $admin_fee,
                    'monthly_deduction' => $monthly_payment, // Hidden from member display
                    'display_payout' => $display_payout, // What member sees
                    'real_net_payout' => $real_net_payout, // What member actually gets
                    'position_coefficient' => $position_coefficient
                ],
                'joint_split_info' => $joint_split_info,
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
     * Calculate joint group split with proper position logic
     */
    private function calculateJointSplit($joint_group_id, $requesting_member_id) {
        try {
            // Get joint group info
            $stmt = $this->db->prepare("
                SELECT * FROM joint_membership_groups 
                WHERE joint_group_id = ?
            ");
            $stmt->execute([$joint_group_id]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$group) {
                throw new Exception("Joint group not found");
            }
            
            // Get all members in the group
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    es.duration_months,
                    es.admin_fee,
                    es.regular_payment_tier
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.joint_group_id = ? AND m.is_active = 1
            ");
            $stmt->execute([$joint_group_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $group_total_monthly = (float)$group['total_monthly_payment'];
            $duration = (int)$members[0]['duration_months'];
            $admin_fee = (float)$members[0]['admin_fee'];
            $position_coefficient = (float)$group['position_coefficient'];
            
            // Calculate group totals
            $group_gross_payout = $group_total_monthly * $duration;
            $group_display_payout = $group_gross_payout - $admin_fee;
            $group_real_payout = $group_gross_payout - $group_total_monthly - $admin_fee;
            
            // Calculate individual splits
            $member_splits = [];
            foreach ($members as $member) {
                $individual_contribution = (float)$member['individual_contribution'];
                $contribution_percentage = $group_total_monthly > 0 ? 
                    $individual_contribution / $group_total_monthly : 0;
                
                // Member's share of display amount
                $member_display = $group_display_payout * $contribution_percentage;
                
                // Member's real share (includes their monthly deduction)
                $member_real = $group_real_payout * $contribution_percentage;
                
                $member_splits[] = [
                    'member_id' => $member['id'],
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'contribution' => $individual_contribution,
                    'percentage' => round($contribution_percentage * 100, 2),
                    'display_amount' => round($member_display, 2),
                    'real_amount' => round($member_real, 2)
                ];
                
                if ($member['id'] == $requesting_member_id) {
                    $requesting_member_display = $member_display;
                    $requesting_member_real = $member_real;
                }
            }
            
            return [
                'success' => true,
                'group_info' => $group,
                'group_totals' => [
                    'gross_payout' => $group_gross_payout,
                    'display_payout' => $group_display_payout,
                    'real_payout' => $group_real_payout,
                    'position_coefficient' => $position_coefficient
                ],
                'member_splits' => $member_splits,
                'member_display_amount' => $requesting_member_display ?? 0,
                'member_real_amount' => $requesting_member_real ?? 0
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate payout date based on position
     */
    private function calculatePayoutDate($member) {
        $equb_start = new DateTime($member['start_date'] ?? '2025-07-01');
        $position = (int)$member['payout_position'];
        $payout_day = (int)($member['payout_day'] ?? 5);
        
        // Calculate which month (position 1 = start month, position 2 = next month, etc.)
        $payout_date = clone $equb_start;
        $payout_date->modify('+' . ($position - 1) . ' months');
        $payout_date->setDate(
            $payout_date->format('Y'),
            $payout_date->format('n'),
            $payout_day
        );
        
        return $payout_date->format('Y-m-d');
    }
    
    /**
     * Auto-fix EQUB positions based on contributions and regular tier
     */
    public function autoFixEqubPositions($equb_id) {
        try {
            $position_calc = $this->calculateEqubPositions($equb_id);
            
            if (!$position_calc['success']) {
                return $position_calc;
            }
            
            $this->db->beginTransaction();
            
            // Update equb settings with calculated positions
            $stmt = $this->db->prepare("
                UPDATE equb_settings 
                SET 
                    calculated_positions = ?,
                    duration_months = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $position_calc['recommended_duration'],
                $position_calc['recommended_duration'],
                $equb_id
            ]);
            
            // Update member position coefficients
            foreach ($position_calc['position_analysis'] as $analysis) {
                $stmt = $this->db->prepare("
                    UPDATE members 
                    SET position_coefficient = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $analysis['position_coefficient'],
                    $analysis['member_id']
                ]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'EQUB positions automatically fixed',
                'changes' => $position_calc
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>