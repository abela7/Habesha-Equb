<?php
/**
 * Test Enhanced EQUB System V2
 * Validates that the new position coefficient × monthly pool logic works correctly
 */

require_once 'includes/db.php';
require_once 'includes/enhanced_equb_calculator_v2.php';

echo "🚀 TESTING ENHANCED EQUB SYSTEM V2\n";
echo "=====================================\n\n";

try {
    $calculator = getEnhancedEqubCalculatorV2();
    
    // Test all active members
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, monthly_payment, individual_contribution, 
               membership_type, position_coefficient
        FROM members 
        WHERE is_active = 1 AND equb_settings_id = 2
        ORDER BY payout_position
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 MEMBER PAYOUT CALCULATIONS:\n";
    echo "------------------------------\n";
    
    foreach ($members as $member) {
        $result = $calculator->calculateMemberFriendlyPayout($member['id']);
        
        if ($result['success']) {
            $calc = $result['calculation'];
            
            echo "👤 {$member['first_name']} {$member['last_name']} ({$member['membership_type']})\n";
            echo "   Contribution: £" . ($member['membership_type'] === 'joint' ? $member['individual_contribution'] : $member['monthly_payment']) . "/month\n";
            echo "   Position Coefficient: {$calc['position_coefficient']}\n";
            echo "   Formula: {$calc['formula_used']}\n";
            echo "   Display Payout: £{$calc['display_payout']} (what member sees)\n";
            echo "   Real Net Payout: £{$calc['real_net_payout']} (what member gets)\n";
            echo "   Method: {$calc['calculation_method']}\n\n";
        } else {
            echo "❌ Error calculating for {$member['first_name']}: {$result['error']}\n\n";
        }
    }
    
    // Test EQUB balance validation
    echo "🔍 EQUB BALANCE VALIDATION:\n";
    echo "----------------------------\n";
    
    $balance_result = $calculator->validateEqubBalance(2);
    if ($balance_result['success']) {
        $summary = $balance_result['financial_summary'];
        
        echo "Total Monthly Pool: £{$summary['total_monthly_pool']}\n";
        echo "Total Positions: {$summary['total_positions']}\n";
        echo "Duration: {$summary['duration_months']} months\n";
        echo "Total Contributions: £{$summary['total_contributions']}\n";
        echo "Total Expected Payouts: £{$summary['total_expected_payouts']}\n";
        echo "Balance Difference: £{$summary['balance_difference']}\n";
        echo "Is Balanced: " . ($summary['is_balanced'] ? "✅ YES" : "❌ NO") . "\n\n";
    }
    
    echo "🎯 EXPECTED RESULTS:\n";
    echo "--------------------\n";
    echo "Michael (1.5 coeff): £15,000 gross → £14,980 display\n";
    echo "Koki (0.5 coeff): £5,000 gross → £4,980 display\n";
    echo "Individual (1.0 coeff): £10,000 gross → £9,980 display\n";
    echo "Eldana (0.5 coeff): £5,000 gross → £4,980 display\n";
    echo "Sosina (0.5 coeff): £5,000 gross → £4,980 display\n\n";
    
    echo "✅ TEST COMPLETED - Check results above!\n";
    
} catch (Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
}
?>