<?php
/**
 * DEBUG POOL CALCULATION
 * Find why £110,000 instead of £10,000
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once 'includes/admin_auth_guard.php';

if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Debug Pool Calculation</title></head><body>";
echo "<h2>🔍 Debugging Pool Calculation</h2>";

try {
    $calculator = getEnhancedEqubCalculator();
    $equb_id = 2; // Selam Equb
    
    // Manual calculation
    echo "<h3>📋 Manual Member Calculation:</h3>";
    
    $stmt = $pdo->prepare("
        SELECT 
            m.id, m.first_name, m.last_name, m.monthly_payment, m.individual_contribution,
            m.membership_type, m.position_coefficient,
            CASE 
                WHEN m.membership_type = 'joint' THEN m.individual_contribution
                ELSE m.monthly_payment
            END as effective_contribution
        FROM members m 
        WHERE m.equb_settings_id = ? AND m.is_active = 1
        ORDER BY m.membership_type, m.first_name
    ");
    $stmt->execute([$equb_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $manual_total = 0;
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Type</th><th>Monthly Payment</th><th>Individual Contribution</th><th>Effective</th><th>Coefficient</th></tr>";
    
    foreach ($members as $member) {
        $effective = $member['effective_contribution'];
        $manual_total += $effective;
        
        echo "<tr>";
        echo "<td>{$member['first_name']} {$member['last_name']}</td>";
        echo "<td>{$member['membership_type']}</td>";
        echo "<td>£{$member['monthly_payment']}</td>";
        echo "<td>£{$member['individual_contribution']}</td>";
        echo "<td style='background: yellow;'>£{$effective}</td>";
        echo "<td>{$member['position_coefficient']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Manual Total Monthly Pool: £{$manual_total}</strong></p>";
    
    // Calculator result
    echo "<h3>🧮 Calculator Result:</h3>";
    $result = $calculator->calculateEqubPositions($equb_id);
    
    if ($result['success']) {
        echo "<div style='background: #f0f0f0; padding: 10px;'>";
        echo "<strong>Calculator Total Monthly Pool:</strong> £{$result['total_monthly_pool']}<br>";
        echo "<strong>Total Positions:</strong> {$result['total_positions']}<br>";
        echo "<strong>Duration:</strong> {$result['actual_duration']} months<br>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>Calculator Error: {$result['error']}</p>";
    }
    
    // Check equb-management.php calculation
    echo "<h3>🏢 Equb Management Calculation:</h3>";
    
    $stmt = $pdo->query("
        SELECT 
            es.*,
            COUNT(DISTINCT m.id) as current_members,
            COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) as collected_amount,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as distributed_amount
        FROM equb_settings es
        LEFT JOIN members m ON m.equb_settings_id = es.id AND m.is_active = 1
        LEFT JOIN payments p ON p.member_id = m.id
        LEFT JOIN payouts po ON po.member_id = m.id
        WHERE es.id = 2
        GROUP BY es.id
    ");
    $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #f0f0f0; padding: 10px;'>";
    echo "<strong>Database EQUB Data:</strong><br>";
    echo "Total Pool Amount: £{$equb_data['total_pool_amount']}<br>";
    echo "Current Members: {$equb_data['current_members']}<br>";
    echo "Duration: {$equb_data['duration_months']} months<br>";
    echo "</div>";
    
    // The calculation that equb-management.php is using
    $equb_calculation = $calculator->calculateEqubPositions($equb_data['id']);
    if ($equb_calculation['success']) {
        $calculated_pool = $equb_calculation['total_monthly_pool'];
        echo "<p><strong>What equb-management.php should show:</strong> £{$calculated_pool}</p>";
    }
    
    // Check welcome_admin.php calculation (the wrong one)
    echo "<h3>🏠 Welcome Admin Wrong Calculation:</h3>";
    
    $wrong_calc = $pdo->query("
        SELECT 
            COALESCE(SUM(
                CASE WHEN status = 'active' THEN 
                    (SELECT SUM(
                        CASE 
                            WHEN m.membership_type = 'joint' THEN m.individual_contribution
                            ELSE m.monthly_payment
                        END
                    ) * es.duration_months
                    FROM members m 
                    WHERE m.equb_settings_id = es.id AND m.is_active = 1)
                ELSE 0 END
            ), 0) as wrong_total_pool_value
        FROM equb_settings es
        WHERE es.id = 2
    ")->fetch();
    
    echo "<div style='background: #ffcccc; padding: 10px;'>";
    echo "<strong>Wrong Calculation (monthly_pool × duration):</strong> £{$wrong_calc['wrong_total_pool_value']}<br>";
    echo "<strong>This explains the £110,000!</strong><br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='equb-management.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Go to Equb Management</a></p>";
echo "</body></html>";
?>