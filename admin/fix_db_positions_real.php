<?php
/**
 * Fix Database Positions Based on ACTUAL Database Data
 * This fixes the wrong position assignments I see in the database
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Real Position Fix</title></head><body>";
echo "<h2>🔧 Fixing Real Database Position Issues</h2>";

try {
    // Show current problems
    echo "<h3>📊 Current Database Problems:</h3>";
    
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, payout_position, membership_type, joint_group_id, position_coefficient
        FROM members 
        WHERE equb_settings_id = 2 AND is_active = 1 
        ORDER BY payout_position, id
    ");
    $current = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($current as $member) {
        echo "<li>Position {$member['payout_position']}: {$member['first_name']} {$member['last_name']} ({$member['membership_type']}) - Coeff: {$member['position_coefficient']}</li>";
    }
    echo "</ul>";
    
    echo "<h3>🔧 Fixing positions to match actual EQUB logic:</h3>";
    
    $pdo->beginTransaction();
    
    // Based on database data, fix the actual position conflicts:
    
    // Michael and Koki both have position 6 - separate them
    // According to the logic: Michael (1.5) gets position 5, Koki (0.5) gets position 10
    $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
    
    // Individual members get positions 1-7
    $stmt->execute([1, 7]);   // Abel (already 1 - OK)
    $stmt->execute([2, 13]);  // Maruf (already 2 - OK) 
    $stmt->execute([3, 20]);  // Samson (already 3 - OK)
    $stmt->execute([4, 10]);  // Barnabas (already 4 - OK)
    $stmt->execute([5, 17]);  // Elias (already 5 - OK)
    $stmt->execute([6, 12]);  // Biniam (already 7, move to 6)
    $stmt->execute([7, 8]);   // Sabella (already 8, move to 7)
    
    // Joint groups get shared positions
    $stmt->execute([8, 16]);  // Eldana (already 9, move to 8)
    $stmt->execute([8, 18]);  // Sosina (already 9, keep same as Eldana)
    
    // Michael gets position 9, Koki gets position 10
    $stmt->execute([9, 14]);  // Michael (was 6, move to 9)
    $stmt->execute([10, 11]); // Koki (was 6, move to 10)
    
    echo "✅ Updated member positions<br>";
    
    // Update joint group positions to match
    $stmt = $pdo->prepare("UPDATE joint_membership_groups SET payout_position = ? WHERE joint_group_id = ?");
    $stmt->execute([8, 'JNT-2025-002-902']);  // Eldana & Sosina → position 8
    $stmt->execute([9, 'JNT-2025-002-115']);  // Michael & Koki → position 9 (Michael's position)
    
    echo "✅ Updated joint group positions<br>";
    
    $pdo->commit();
    
    echo "<h3>📊 New Position Assignment:</h3>";
    
    $stmt = $pdo->query("
        SELECT payout_position, COUNT(*) as count,
               GROUP_CONCAT(CONCAT(first_name, ' ', last_name) ORDER BY first_name SEPARATOR ', ') as members
        FROM members 
        WHERE equb_settings_id = 2 AND is_active = 1 
        GROUP BY payout_position 
        ORDER BY payout_position
    ");
    $new_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($new_positions as $pos) {
        echo "<li><strong>Position {$pos['payout_position']}</strong>: {$pos['members']}</li>";
    }
    echo "</ul>";
    
    $total_positions = count($new_positions);
    echo "<p><strong>🎯 Total unique positions: {$total_positions}</strong></p>";
    
    if ($total_positions == 10) {
        echo "<p style='color: green; font-size: 18px;'>🎉 <strong>SUCCESS!</strong> Now you have exactly 10 positions!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Got {$total_positions} positions. Expected 10.</p>";
    }
    
    echo "<p><a href='payout-positions.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Go back to Payout Positions</a></p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>