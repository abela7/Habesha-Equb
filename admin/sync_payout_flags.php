<?php
/**
 * HabeshaEqub - One-Time Payout Flag Sync Script
 * This script fixes any existing data inconsistencies in has_received_payout flags
 */

require_once '../includes/db.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';

echo "<h2>🔧 HabeshaEqub Payout Flag Sync Utility</h2>";
echo "<p>This utility will synchronize all member payout flags with the actual payouts table data.</p>";

if ($_POST['action'] === 'sync' ?? false) {
    try {
        // Get all members
        $stmt = $pdo->query("SELECT id, first_name, last_name, has_received_payout FROM members WHERE is_active = 1");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fixed_count = 0;
        $total_count = count($members);
        
        echo "<h3>📊 Sync Results:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Member</th><th>Current Flag</th><th>Actual Payouts</th><th>New Flag</th><th>Action</th></tr>";
        
        foreach ($members as $member) {
            // Check actual completed payouts
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as completed_payouts 
                FROM payouts 
                WHERE member_id = ? AND status = 'completed'
            ");
            $stmt->execute([$member['id']]);
            $payout_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $actual_has_payouts = $payout_data['completed_payouts'] > 0;
            $current_flag = (int)$member['has_received_payout'];
            $correct_flag = $actual_has_payouts ? 1 : 0;
            
            $action = "✅ No change needed";
            $row_class = "";
            
            if ($current_flag !== $correct_flag) {
                // Update the flag
                $stmt = $pdo->prepare("UPDATE members SET has_received_payout = ? WHERE id = ?");
                $stmt->execute([$correct_flag, $member['id']]);
                
                $action = "🔧 FIXED";
                $row_class = "style='background-color: #ffffcc;'";
                $fixed_count++;
            }
            
            echo "<tr $row_class>";
            echo "<td>" . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . "</td>";
            echo "<td>" . ($current_flag ? '✓ Yes' : '✗ No') . "</td>";
            echo "<td>" . $payout_data['completed_payouts'] . " completed</td>";
            echo "<td>" . ($correct_flag ? '✓ Yes' : '✗ No') . "</td>";
            echo "<td><strong>$action</strong></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h3>🎯 Summary:</h3>";
        echo "<ul>";
        echo "<li><strong>Total Members Processed:</strong> $total_count</li>";
        echo "<li><strong>Flags Fixed:</strong> $fixed_count</li>";
        echo "<li><strong>Already Correct:</strong> " . ($total_count - $fixed_count) . "</li>";
        echo "</ul>";
        
        if ($fixed_count > 0) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>✅ SUCCESS!</strong> $fixed_count member payout flags have been corrected.";
            echo "</div>";
        } else {
            echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>ℹ️ INFO:</strong> All payout flags were already correct. No changes needed.";
            echo "</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ ERROR:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
} else {
    // Show form
    echo "<form method='post'>";
    echo "<input type='hidden' name='action' value='sync'>";
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠️ IMPORTANT:</strong> This will check all active members and update their <code>has_received_payout</code> flags to match the actual payouts in the database.";
    echo "</div>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "🔧 Start Sync Process";
    echo "</button>";
    echo "</form>";
}

echo "<p><a href='dashboard.php'>← Back to Admin Dashboard</a></p>";
?>