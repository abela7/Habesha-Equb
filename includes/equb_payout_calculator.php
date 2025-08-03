<?php
/**
 * HabeshaEqub - Professional Traditional EQUB Payout Calculator
 * Implements authentic traditional EQUB logic with joint membership support
 * 
 * TRADITIONAL EQUB PRINCIPLE:
 * Each member receives their own contributions × duration when their turn comes
 * NOT a redistributed pool system
 */

class EqubPayoutCalculator {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Calculate the correct payout amount for a member using TRADITIONAL EQUB LOGIC
     * 
     * @param int $member_id
     * @return array Calculation details
     */
    public function calculateMemberPayoutAmount($member_id) {
        try {
            // Get comprehensive member and equb data
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.first_name,
                    m.last_name,
                    m.monthly_payment,
                    m.payout_position,
                    m.membership_type,
                    m.joint_group_id,
                    m.joint_position_share,
                    m.individual_contribution,
                    m.primary_joint_member,
                    es.id as equb_id,
                    es.equb_name,
                    es.duration_months,
                    es.payment_tiers,
                    es.admin_fee,
                    es.supports_joint_membership
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.id = ? AND m.is_active = 1
            ");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                throw new Exception("Member not found or not in active equb");
            }
            
            // Validate payment tiers configuration
            $payment_tiers = json_decode($member['payment_tiers'], true);
            if (!is_array($payment_tiers) || empty($payment_tiers)) {
                throw new Exception("Invalid or missing payment tiers configuration");
            }
            
            // Get member's actual contribution details
            $member_payment = (float)$member['monthly_payment'];
            $duration_months = (int)$member['duration_months'];
            $admin_fee = (float)$member['admin_fee'];
            
            // TRADITIONAL EQUB CALCULATION:
            // Member receives their own contributions × duration
            $gross_payout = $member_payment * $duration_months;
            $net_payout = $gross_payout - $admin_fee;
            
            // Handle joint membership calculations
            $joint_members = [];
            $joint_split_details = null;
            
            if ($member['membership_type'] === 'joint' && !empty($member['joint_group_id'])) {
                $joint_details = $this->calculateJointMembershipPayout($member['joint_group_id'], $member_id);
                $joint_members = $joint_details['joint_members'];
                $joint_split_details = $joint_details['split_details'];
                
                // For joint members, calculate their individual share
                if (isset($joint_split_details[$member_id])) {
                    $member_share = $joint_split_details[$member_id];
                    $net_payout = $member_share['net_amount'];
                }
            }
            
            // Get equb pool summary for context
            $pool_summary = $this->getEqubPoolSummary($member['equb_id']);
            
            // Financial validation
            $this->validatePayoutCalculation($member, $gross_payout, $net_payout);
            
            return [
                'success' => true,
                'calculation_method' => 'traditional_equb',
                'member_id' => $member_id,
                'member_name' => trim($member['first_name'] . ' ' . $member['last_name']),
                'equb_name' => $member['equb_name'],
                'membership_type' => $member['membership_type'],
                'joint_group_id' => $member['joint_group_id'],
                
                // Core financial details
                'monthly_payment' => $member_payment,
                'duration_months' => $duration_months,
                'payout_position' => (int)$member['payout_position'],
                'gross_payout' => $gross_payout,
                'admin_fee' => $admin_fee,
                'net_payout' => $net_payout,
                
                // Joint membership details (if applicable)
                'joint_members' => $joint_members,
                'joint_split_details' => $joint_split_details,
                'is_primary_joint_member' => (bool)$member['primary_joint_member'],
                
                // Equb context
                'equb_pool_summary' => $pool_summary,
                'payment_tiers' => $payment_tiers,
                
                // Calculation transparency
                'calculation_details' => [
                    'principle' => 'Traditional EQUB: Member receives their own contributions × duration',
                    'formula' => 'Net Payout = (Monthly Payment × Duration) - Admin Fee',
                    'calculation' => sprintf(
                        '(£%s × %d months) - £%s = £%s',
                        number_format($member_payment, 2),
                        $duration_months,
                        number_format($admin_fee, 2),
                        number_format($net_payout, 2)
                    ),
                    'validation_status' => 'verified',
                    'calculated_at' => date('Y-m-d H:i:s')
                ],
                
                // Financial audit trail
                'audit_info' => [
                    'total_expected_contributions' => $gross_payout,
                    'admin_fee_percentage' => $gross_payout > 0 ? round(($admin_fee / $gross_payout) * 100, 2) : 0,
                    'net_percentage' => $gross_payout > 0 ? round(($net_payout / $gross_payout) * 100, 2) : 0
                ]
            ];
            
        } catch (Exception $e) {
            error_log("EQUB Payout Calculation Error for Member {$member_id}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'CALCULATION_ERROR',
                'member_id' => $member_id ?? null
            ];
        }
    }
    
    /**
     * Calculate joint membership payout distribution
     * 
     * @param string $joint_group_id
     * @param int $requesting_member_id
     * @return array Joint payout details
     */
    private function calculateJointMembershipPayout($joint_group_id, $requesting_member_id) {
        try {
            // Get all members in the joint group
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.first_name,
                    m.last_name,
                    m.monthly_payment,
                    m.individual_contribution,
                    m.joint_position_share,
                    m.primary_joint_member,
                    jmg.total_monthly_payment,
                    jmg.payout_split_method,
                    es.duration_months,
                    es.admin_fee
                FROM members m
                JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.joint_group_id = ? AND m.is_active = 1
                ORDER BY m.primary_joint_member DESC, m.id ASC
            ");
            $stmt->execute([$joint_group_id]);
            $joint_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($joint_members)) {
                throw new Exception("No active members found in joint group: {$joint_group_id}");
            }
            
            // Calculate total group payout
            $group_data = $joint_members[0]; // All have same group data
            $total_monthly_payment = (float)$group_data['total_monthly_payment'];
            $duration_months = (int)$group_data['duration_months'];
            $admin_fee = (float)$group_data['admin_fee'];
            $split_method = $group_data['payout_split_method'];
            
            $total_gross_payout = $total_monthly_payment * $duration_months;
            $total_net_payout = $total_gross_payout - $admin_fee;
            
            // Calculate individual splits based on method
            $split_details = [];
            
            foreach ($joint_members as $member) {
                $member_id = $member['id'];
                $member_name = trim($member['first_name'] . ' ' . $member['last_name']);
                
                switch ($split_method) {
                    case 'equal':
                        $share_percentage = 1.0 / count($joint_members);
                        break;
                        
                    case 'proportional':
                        $individual_contribution = (float)$member['individual_contribution'];
                        $share_percentage = $total_monthly_payment > 0 ? 
                            $individual_contribution / $total_monthly_payment : 0;
                        break;
                        
                    case 'custom':
                        $share_percentage = (float)$member['joint_position_share'];
                        break;
                        
                    default:
                        $share_percentage = 1.0 / count($joint_members);
                }
                
                // Calculate individual amounts
                $gross_amount = $total_gross_payout * $share_percentage;
                $admin_fee_share = $admin_fee * $share_percentage;
                $net_amount = $gross_amount - $admin_fee_share;
                
                $split_details[$member_id] = [
                    'member_id' => $member_id,
                    'member_name' => $member_name,
                    'is_primary' => (bool)$member['primary_joint_member'],
                    'share_percentage' => round($share_percentage * 100, 2),
                    'individual_contribution' => (float)$member['individual_contribution'],
                    'gross_amount' => round($gross_amount, 2),
                    'admin_fee_share' => round($admin_fee_share, 2),
                    'net_amount' => round($net_amount, 2),
                    'split_method' => $split_method
                ];
            }
            
            return [
                'joint_group_id' => $joint_group_id,
                'total_members' => count($joint_members),
                'split_method' => $split_method,
                'total_gross_payout' => $total_gross_payout,
                'total_admin_fee' => $admin_fee,
                'total_net_payout' => $total_net_payout,
                'joint_members' => $joint_members,
                'split_details' => $split_details
            ];
            
        } catch (Exception $e) {
            throw new Exception("Joint membership calculation error: " . $e->getMessage());
        }
    }
    
    /**
     * Validate payout calculation for financial integrity
     * 
     * @param array $member
     * @param float $gross_payout
     * @param float $net_payout
     * @throws Exception
     */
    private function validatePayoutCalculation($member, $gross_payout, $net_payout) {
        // Validate positive amounts
        if ($gross_payout <= 0) {
            throw new Exception("Invalid gross payout amount: £" . number_format($gross_payout, 2));
        }
        
        if ($net_payout < 0) {
            throw new Exception("Net payout cannot be negative. Admin fee exceeds contribution.");
        }
        
        // Validate reasonable amounts (prevent calculation errors)
        $max_reasonable_payout = $member['monthly_payment'] * $member['duration_months'] * 1.1; // 10% buffer
        if ($gross_payout > $max_reasonable_payout) {
            throw new Exception("Calculated payout exceeds reasonable maximum. Please verify calculation.");
        }
        
        // Validate admin fee is reasonable
        $admin_fee_percentage = $gross_payout > 0 ? ($member['admin_fee'] / $gross_payout) * 100 : 0;
        if ($admin_fee_percentage > 50) {
            throw new Exception("Admin fee ({$admin_fee_percentage}%) exceeds reasonable limit (50%)");
        }
    }
    
    /**
     * Get comprehensive equb pool summary with financial analytics
     * 
     * @param int $equb_id
     * @return array Detailed equb summary
     */
    public function getEqubPoolSummary($equb_id) {
        try {
            // Get basic equb information
            $stmt = $this->db->prepare("
                SELECT 
                    es.equb_name,
                    es.equb_id,
                    es.duration_months,
                    es.max_members,
                    es.current_members,
                    es.status,
                    es.start_date,
                    es.end_date,
                    es.admin_fee,
                    es.late_fee,
                    es.payment_tiers,
                    es.supports_joint_membership,
                    es.total_pool_amount,
                    es.collected_amount,
                    es.distributed_amount
                FROM equb_settings es
                WHERE es.id = ?
            ");
            $stmt->execute([$equb_id]);
            $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$equb_data) {
                throw new Exception("Equb not found with ID: {$equb_id}");
            }
            
            // Get member statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_active_members,
                    COUNT(CASE WHEN membership_type = 'individual' THEN 1 END) as individual_members,
                    COUNT(CASE WHEN membership_type = 'joint' THEN 1 END) as joint_members,
                    COUNT(DISTINCT joint_group_id) as joint_groups,
                    SUM(monthly_payment) as total_monthly_pool,
                    AVG(monthly_payment) as average_monthly_payment,
                    MIN(monthly_payment) as min_monthly_payment,
                    MAX(monthly_payment) as max_monthly_payment
                FROM members 
                WHERE equb_settings_id = ? AND is_active = 1
            ");
            $stmt->execute([$equb_id]);
            $member_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get payment tier distribution
            $stmt = $this->db->prepare("
                SELECT 
                    monthly_payment,
                    COUNT(*) as member_count,
                    SUM(monthly_payment) as tier_total_monthly
                FROM members 
                WHERE equb_settings_id = ? AND is_active = 1
                GROUP BY monthly_payment
                ORDER BY monthly_payment DESC
            ");
            $stmt->execute([$equb_id]);
            $payment_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate financial projections
            $total_monthly_pool = (float)$member_stats['total_monthly_pool'];
            $duration_months = (int)$equb_data['duration_months'];
            $admin_fee = (float)$equb_data['admin_fee'];
            
            $projected_total_collection = $total_monthly_pool * $duration_months;
            $projected_total_admin_fees = $member_stats['total_active_members'] * $admin_fee;
            $projected_net_distribution = $projected_total_collection - $projected_total_admin_fees;
            
            // Financial health indicators
            $collected_percentage = $projected_total_collection > 0 ? 
                ($equb_data['collected_amount'] / $projected_total_collection) * 100 : 0;
            $distribution_percentage = $projected_net_distribution > 0 ? 
                ($equb_data['distributed_amount'] / $projected_net_distribution) * 100 : 0;
            
            return [
                'success' => true,
                'equb_info' => $equb_data,
                'member_statistics' => $member_stats,
                'payment_distribution' => $payment_distribution,
                'payment_tiers' => json_decode($equb_data['payment_tiers'], true),
                
                'financial_projections' => [
                    'total_monthly_pool' => $total_monthly_pool,
                    'projected_total_collection' => $projected_total_collection,
                    'projected_total_admin_fees' => $projected_total_admin_fees,
                    'projected_net_distribution' => $projected_net_distribution,
                    'average_payout_per_member' => $member_stats['total_active_members'] > 0 ? 
                        $projected_net_distribution / $member_stats['total_active_members'] : 0
                ],
                
                'financial_health' => [
                    'collected_percentage' => round($collected_percentage, 2),
                    'distribution_percentage' => round($distribution_percentage, 2),
                    'outstanding_balance' => $projected_total_collection - $equb_data['collected_amount'],
                    'pending_distributions' => $projected_net_distribution - $equb_data['distributed_amount']
                ],
                
                'calculated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'equb_id' => $equb_id
            ];
        }
    }
    
    /**
     * Generate financial audit report for an equb
     * 
     * @param int $equb_id
     * @return array Comprehensive financial audit
     */
    public function generateFinancialAudit($equb_id) {
        try {
            $pool_summary = $this->getEqubPoolSummary($equb_id);
            
            if (!$pool_summary['success']) {
                throw new Exception($pool_summary['error']);
            }
            
            // Get all member payout calculations
            $stmt = $this->db->prepare("
                SELECT id FROM members 
                WHERE equb_settings_id = ? AND is_active = 1
                ORDER BY payout_position ASC
            ");
            $stmt->execute([$equb_id]);
            $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $member_calculations = [];
            $total_calculated_payouts = 0;
            
            foreach ($member_ids as $member_id) {
                $calculation = $this->calculateMemberPayoutAmount($member_id);
                if ($calculation['success']) {
                    $member_calculations[] = $calculation;
                    $total_calculated_payouts += $calculation['net_payout'];
                }
            }
            
            return [
                'success' => true,
                'audit_date' => date('Y-m-d H:i:s'),
                'equb_summary' => $pool_summary,
                'member_calculations' => $member_calculations,
                'financial_summary' => [
                    'total_members_audited' => count($member_calculations),
                    'total_calculated_payouts' => $total_calculated_payouts,
                    'calculation_method' => 'traditional_equb',
                    'audit_status' => 'completed'
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'audit_date' => date('Y-m-d H:i:s')
            ];
        }
    }
}

/**
 * Helper function to get calculator instance
 */
function getEqubPayoutCalculator() {
    global $pdo, $db;
    $database = isset($db) ? $db : $pdo;
    return new EqubPayoutCalculator($database);
}
?>