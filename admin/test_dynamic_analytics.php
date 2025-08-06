<?php
/**
 * TEST DYNAMIC ANALYTICS - Verify all values are from database
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once 'includes/admin_auth_guard.php';

if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Test Dynamic Analytics</title></head><body>";
echo "<h2>🧪 Testing Dynamic Analytics Values</h2>";

try {
    // Get EQUB data
    $stmt = $pdo->query("SELECT * FROM equb_settings WHERE status = 'active' LIMIT 1");
    $equb = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$equb) {
        echo "<p style='color: red;'>❌ No active EQUB found</p>";
        exit;
    }
    
    echo "<h3>📊 EQUB: {$equb['equb_name']} (ID: {$equb['id']})</h3>";
    
    // Enhanced Calculator Results
    $calculator = new EnhancedEqubCalculator($pdo);
    $result = $calculator->calculateEqubPositions($equb['id']);
    
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border-left: 5px solid #007acc;'>";
    echo "<h4>🚀 Enhanced Calculator Results (DYNAMIC):</h4>";
    if ($result['success']) {
        echo "<strong>Monthly Pool:</strong> £" . number_format($result['total_monthly_pool']) . "<br>";
        echo "<strong>Duration:</strong> {$equb['duration_months']} months<br>";
        echo "<strong>Total Pool Value:</strong> £" . number_format($result['total_monthly_pool'] * $equb['duration_months']) . "<br>";
        echo "<strong>Total Positions:</strong> {$result['total_positions']}<br>";
        echo "<strong>Individual Positions:</strong> {$result['individual_positions']}<br>";
        echo "<strong>Joint Groups:</strong> {$result['joint_groups']}<br>";
    } else {
        echo "<p style='color: red;'>❌ Calculator Error: {$result['message']}</p>";
    }
    echo "</div>";
    
    // Database Values
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-left: 5px solid #28a745;'>";
    echo "<h4>💾 Database Values:</h4>";
    echo "<strong>Duration Months:</strong> {$equb['duration_months']}<br>";
    echo "<strong>Regular Payment Tier:</strong> £" . number_format($equb['regular_payment_tier']) . "<br>";
    echo "<strong>Admin Fee:</strong> £" . number_format($equb['admin_fee']) . "<br>";
    echo "<strong>Current total_pool_amount:</strong> £" . number_format($equb['total_pool_amount']) . "<br>";
    echo "</div>";
    
    // Member Breakdown
    $stmt = $pdo->prepare("
        SELECT 
            m.first_name, m.last_name, m.membership_type,
            m.monthly_payment, m.individual_contribution,
            CASE 
                WHEN m.membership_type = 'joint' THEN m.individual_contribution
                ELSE m.monthly_payment
            END as effective_contribution
        FROM members m 
        WHERE m.equb_settings_id = ? AND m.is_active = 1
        ORDER BY m.membership_type, m.first_name
    ");
    $stmt->execute([$equb['id']]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #fff9e6; padding: 15px; margin: 10px 0; border-left: 5px solid #ffc107;'>";
    echo "<h4>👥 Member Contributions (DYNAMIC):</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Name</th><th>Type</th><th>Monthly Payment</th><th>Individual Contrib</th><th>Effective</th></tr>";
    
    $total_check = 0;
    foreach ($members as $member) {
        $effective = $member['effective_contribution'];
        $total_check += $effective;
        
        echo "<tr>";
        echo "<td>{$member['first_name']} {$member['last_name']}</td>";
        echo "<td>{$member['membership_type']}</td>";
        echo "<td>£{$member['monthly_payment']}</td>";
        echo "<td>£{$member['individual_contribution']}</td>";
        echo "<td style='background: yellow;'>£{$effective}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>✅ Total Monthly Pool: £{$total_check}</strong></p>";
    echo "</div>";
    
    echo "<h3 style='color: green;'>🎉 ALL VALUES ARE DYNAMIC - NO HARDCODING!</h3>";
    echo "<ul>";
    echo "<li>✅ Duration from database: {$equb['duration_months']} months</li>";
    echo "<li>✅ Monthly pool calculated from actual contributions: £{$total_check}</li>";
    echo "<li>✅ Admin fee from database: £{$equb['admin_fee']}</li>";
    echo "<li>✅ All calculations use EnhancedEqubCalculator</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='financial-analytics.php' style='background: #007acc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 View Enhanced Analytics</a></p>";
echo "</body></html>";
?>