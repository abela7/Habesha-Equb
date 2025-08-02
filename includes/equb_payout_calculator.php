<?php
/**
 * HabeshaEqub - Proper Equb Payout Calculator
 * Implements the correct equb logic based on payment tiers
 */

class EqubPayoutCalculator {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Calculate the correct payout amount for a member based on their payment tier
     * 
     * @param int $member_id
     * @return array Calculation details
     */
    public function calculateMemberPayoutAmount($member_id) {
        try {
            // Get member and equb data
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.first_name,
                    m.last_name,
                    m.monthly_payment,
                    m.payout_position,
                    es.id as equb_id,
                    es.equb_name,
                    es.duration_months,
                    es.payment_tiers,
                    es.admin_fee
                FROM members m
                JOIN equb_settings es ON m.equb_settings_id = es.id
                WHERE m.id = ? AND m.is_active = 1
            ");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                throw new Exception("Member not found or not in active equb");
            }
            
            // Get all active members in the same equb
            $stmt = $this->db->prepare("
                SELECT monthly_payment, COUNT(*) as member_count
                FROM members 
                WHERE equb_settings_id = ? AND is_active = 1
                GROUP BY monthly_payment
                ORDER BY monthly_payment DESC
            ");
            $stmt->execute([$member['equb_id']]);
            $payment_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Parse payment tiers from JSON
            $payment_tiers = json_decode($member['payment_tiers'], true);
            if (!$payment_tiers) {
                throw new Exception("Invalid payment tiers configuration");
            }
            
            // Calculate total monthly pool
            $total_monthly_pool = 0;
            foreach ($payment_groups as $group) {
                $total_monthly_pool += ($group['monthly_payment'] * $group['member_count']);
            }
            
            // Calculate total pool for entire duration
            $total_pool = $total_monthly_pool * $member['duration_months'];
            
            // Find the highest tier amount (full member tier)
            $highest_tier = max(array_column($payment_tiers, 'amount'));
            
            // Calculate member's share ratio
            $member_payment = (float)$member['monthly_payment'];
            $share_ratio = $member_payment / $highest_tier;
            
            // Calculate base payout amount (before admin fee)
            $base_payout = $total_pool / $member['duration_months']; // Per position
            $member_payout = $base_payout * $share_ratio;
            
            // Subtract admin fee
            $admin_fee = (float)$member['admin_fee'];
            $net_payout = $member_payout - $admin_fee;
            
            return [
                'success' => true,
                'member_id' => $member_id,
                'member_name' => trim($member['first_name'] . ' ' . $member['last_name']),
                'equb_name' => $member['equb_name'],
                'monthly_payment' => $member_payment,
                'payout_position' => $member['payout_position'],
                'duration_months' => $member['duration_months'],
                'total_monthly_pool' => $total_monthly_pool,
                'total_pool' => $total_pool,
                'highest_tier' => $highest_tier,
                'share_ratio' => $share_ratio,
                'base_payout_per_position' => $base_payout,
                'gross_payout' => $member_payout,
                'admin_fee' => $admin_fee,
                'net_payout' => $net_payout,
                'payment_groups' => $payment_groups,
                'calculation_details' => [
                    'formula' => 'Net Payout = (Total Pool ÷ Duration) × (Member Payment ÷ Highest Tier) - Admin Fee',
                    'calculation' => sprintf(
                        '(£%s ÷ %d) × (£%s ÷ £%s) - £%s = £%s',
                        number_format($total_pool, 2),
                        $member['duration_months'],
                        number_format($member_payment, 2),
                        number_format($highest_tier, 2),
                        number_format($admin_fee, 2),
                        number_format($net_payout, 2)
                    )
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get equb pool summary
     */
    public function getEqubPoolSummary($equb_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    es.equb_name,
                    es.duration_months,
                    es.max_members,
                    COUNT(m.id) as current_members,
                    SUM(m.monthly_payment) as total_monthly_contribution,
                    es.payment_tiers
                FROM equb_settings es
                LEFT JOIN members m ON es.id = m.equb_settings_id AND m.is_active = 1
                WHERE es.id = ?
                GROUP BY es.id
            ");
            $stmt->execute([$equb_id]);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($summary) {
                $summary['total_pool'] = $summary['total_monthly_contribution'] * $summary['duration_months'];
                $summary['payment_tiers'] = json_decode($summary['payment_tiers'], true);
            }
            
            return $summary;
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
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