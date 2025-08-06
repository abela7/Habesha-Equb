<?php
/**
 * EMERGENCY POSITION FIX - Run this once to fix database positions
 * Based on coefficient logic: 0.5+0.5=1 position, 1.5=2 positions
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

// Security check
if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Position Fix</title></head><body>";
echo "<h2>🔧 Fixing Database Positions</h2>";

try {
    echo "<p>📊 Current positions:</p>";
    
    // Show current state
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, payout_position, position_coefficient, membership_type
        FROM members 
        WHERE equb_settings_id = 2 AND is_active = 1 
        ORDER BY payout_position
    ");
    $current = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($current as $member) {
        echo "<li>Position {$member['payout_position']}: {$member['first_name']} {$member['last_name']} (coeff: {$member['position_coefficient']})</li>";
    }
    echo "</ul>";
    
    echo "<p>🔄 Applying coefficient-based position fix...</p>";
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // CORRECT POSITION ASSIGNMENT LOGIC
    $stmt = $pdo->prepare("UPDATE members SET payout_position = ? WHERE id = ?");
    
    // Individual members (positions 1-7) - 1.0 coefficient each
    $updates = [
        [1, 7],   // Abel → Position 1
        [2, 13],  // Maruf → Position 2
        [3, 20],  // Samson → Position 3
        [4, 10],  // Barnabas → Position 4
        [5, 17],  // Elias → Position 5
        [6, 12],  // Biniam → Position 6
        [7, 8],   // Sabella → Position 7
        
        // Joint group: Sosina + Eldana (0.5 + 0.5 = 1.0) → Position 8
        [8, 16],  // Eldana → Position 8
        [8, 18],  // Sosina → Position 8
        
        // Michael's complex case:
        // Michael (1.5) = 1.0 position + 0.5 shared with Koki
        [9, 14],   // Michael → Position 9 (his main 1.0)
        [10, 11],  // Koki → Position 10 (shared with Michael's 0.5)
    ];
    
    foreach ($updates as [$position, $member_id]) {
        $stmt->execute([$position, $member_id]);
        echo "✅ Updated member ID {$member_id} to position {$position}<br>";
    }
    
    // Update joint group positions
    $stmt = $pdo->prepare("UPDATE joint_membership_groups SET payout_position = ? WHERE joint_group_id = ?");
    $stmt->execute([8, 'JNT-2025-002-902']);  // Eldana & Sosina
    $stmt->execute([9, 'JNT-2025-002-115']);  // Michael & Koki
    echo "✅ Updated joint group positions<br>";
    
    // Commit changes
    $pdo->commit();
    
    echo "<p>✅ <strong>Position fix completed successfully!</strong></p>";
    
    // Show new state
    echo "<p>📊 New positions:</p>";
    $stmt = $pdo->query("
        SELECT payout_position, COUNT(*) as count, 
               GROUP_CONCAT(CONCAT(first_name, ' ', last_name) SEPARATOR ', ') as members,
               SUM(position_coefficient) as total_coefficient
        FROM members 
        WHERE equb_settings_id = 2 AND is_active = 1 
        GROUP BY payout_position 
        ORDER BY payout_position
    ");
    $new_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($new_positions as $pos) {
        echo "<li><strong>Position {$pos['payout_position']}</strong>: {$pos['members']} (total coeff: {$pos['total_coefficient']})</li>";
    }
    echo "</ul>";
    
    $total_positions = count($new_positions);
    echo "<p><strong>🎯 Total positions: {$total_positions}</strong> (should be 10)</p>";
    
    if ($total_positions == 10) {
        echo "<p style='color: green; font-size: 18px;'>🎉 <strong>SUCCESS!</strong> Now you have exactly 10 positions!</p>";
        echo "<p><a href='payout-positions.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Go back to Payout Positions</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Something went wrong. Expected 10 positions, got {$total_positions}</p>";
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>