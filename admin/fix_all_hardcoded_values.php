sa<?php
/**
 * FIX ALL HARDCODED VALUES
 * Replace with dynamic database calculations
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Fix All Hardcoded Values</title></head><body>";
echo "<h2>🔧 Fixing All Hardcoded Values</h2>";

try {
    $pdo->beginTransaction();
    
    // Get all EQUB terms
    $stmt = $pdo->query("SELECT * FROM equb_settings ORDER BY id");
    $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($equb_terms as $equb) {
        echo "<h3>📊 Processing: {$equb['equb_name']} (ID: {$equb['id']})</h3>";
        
        // Calculate REAL monthly pool from database
        $stmt = $pdo->prepare("
            SELECT SUM(
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.individual_contribution
                    ELSE m.monthly_payment
                END
            ) as real_monthly_pool
            FROM members m 
            WHERE m.equb_settings_id = ? AND m.is_active = 1
        ");
        $stmt->execute([$equb['id']]);
        $real_monthly_pool = $stmt->fetchColumn() ?: 0;
        
        // Get duration from database (NO HARDCODE)
        $duration_months = $equb['duration_months'];
        
        // Calculate CORRECT total pool value
        $correct_total_pool = $real_monthly_pool * $duration_months;
        
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Database Values:</strong><br>";
        echo "Duration (from DB): {$duration_months} months<br>";
        echo "Real Monthly Pool (calculated): £{$real_monthly_pool}<br>";
        echo "Correct Total Pool: £{$real_monthly_pool} × {$duration_months} = £{$correct_total_pool}<br>";
        echo "<strong>Current DB total_pool_amount:</strong> £{$equb['total_pool_amount']}<br>";
        echo "</div>";
        
        // UPDATE the database with correct values
        $stmt = $pdo->prepare("
            UPDATE equb_settings 
            SET total_pool_amount = ?, 
                calculated_positions = ?
            WHERE id = ?
        ");
        
        // Calculate positions based on coefficient logic
        $stmt2 = $pdo->prepare("
            SELECT SUM(
                CASE 
                    WHEN m.membership_type = 'joint' THEN (m.individual_contribution / ?)
                    ELSE (m.monthly_payment / ?)
                END
            ) as total_coefficient
            FROM members m 
            WHERE m.equb_settings_id = ? AND m.is_active = 1
        ");
        $stmt2->execute([$equb['regular_payment_tier'], $equb['regular_payment_tier'], $equb['id']]);
        $total_coefficient = $stmt2->fetchColumn() ?: 0;
        $calculated_positions = ceil($total_coefficient);
        
        $stmt->execute([$correct_total_pool, $calculated_positions, $equb['id']]);
        
        echo "<div style='color: green;'>";
        echo "✅ Updated total_pool_amount: £{$correct_total_pool}<br>";
        echo "✅ Updated calculated_positions: {$calculated_positions}<br>";
        echo "</div>";
        
        // Show member breakdown
        $stmt = $pdo->prepare("
            SELECT 
                m.first_name, m.last_name, m.membership_type,
                m.monthly_payment, m.individual_contribution,
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.individual_contribution
                    ELSE m.monthly_payment
                END as effective_contribution,
                CASE 
                    WHEN m.membership_type = 'joint' THEN (m.individual_contribution / ?)
                    ELSE (m.monthly_payment / ?)
                END as calculated_coefficient
            FROM members m 
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.membership_type, m.first_name
        ");
        $stmt->execute([$equb['regular_payment_tier'], $equb['regular_payment_tier'], $equb['id']]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📋 Member Breakdown:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Name</th><th>Type</th><th>Monthly Payment</th><th>Individual Contribution</th><th>Effective</th><th>Coefficient</th></tr>";
        
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
            echo "<td>{$member['calculated_coefficient']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>✅ Total calculated: £{$total_check} (should match £{$real_monthly_pool})</strong></p>";
        echo "<hr>";
    }
    
    $pdo->commit();
    
    echo "<h3 style='color: green;'>🎉 ALL HARDCODED VALUES FIXED!</h3>";
    echo "<ul>";
    echo "<li>✅ All total_pool_amount values calculated from database</li>";
    echo "<li>✅ All duration values read from database</li>";
    echo "<li>✅ All payment tiers read from database</li>";
    echo "<li>✅ All member contributions calculated dynamically</li>";
    echo "</ul>";
    
    echo "<p><strong>NOW ALL VALUES ARE DYNAMIC - NO MORE HARDCODING!</strong></p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='equb-management.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Go to Equb Management</a></p>";
echo "</body></html>";
?>