<?php
/**
 * TEST PAYOUT MONTH UPDATE API
 * Debug why database payout_month is not updating
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Test Payout Month Update</title></head><body>";
echo "<h2>🧪 Testing Payout Month Update API</h2>";

try {
    // Test the API directly
    $equb_id = 2; // Selam Equb
    $test_updates = [
        ['member_id' => 7, 'position' => 5],  // Move Abel to position 5
        ['member_id' => 13, 'position' => 1], // Move Maruf to position 1
    ];
    
    echo "<h3>📋 Test Data:</h3>";
    echo "<ul>";
    foreach ($test_updates as $update) {
        echo "<li>Member ID {$update['member_id']} → Position {$update['position']}</li>";
    }
    echo "</ul>";
    
    // Show current state
    echo "<h3>📊 BEFORE Update:</h3>";
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, payout_position, payout_month FROM members WHERE id IN (7, 13)");
    $stmt->execute();
    $before = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Payout Month</th></tr>";
    foreach ($before as $member) {
        echo "<tr><td>{$member['id']}</td><td>{$member['first_name']} {$member['last_name']}</td><td>{$member['payout_position']}</td><td>{$member['payout_month']}</td></tr>";
    }
    echo "</table>";
    
    // Simulate the API call
    $_POST['action'] = 'update_positions';
    $_POST['equb_id'] = $equb_id;
    $_POST['positions'] = json_encode($test_updates);
    
    echo "<h3>🚀 Calling API...</h3>";
    
    // Include the API file
    ob_start();
    include 'api/payout-positions.php';
    $api_output = ob_get_clean();
    
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
    echo "<strong>API Output:</strong><br>";
    echo htmlspecialchars($api_output);
    echo "</div>";
    
    // Show result state
    echo "<h3>📊 AFTER Update:</h3>";
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, payout_position, payout_month FROM members WHERE id IN (7, 13)");
    $stmt->execute();
    $after = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Payout Month</th></tr>";
    foreach ($after as $member) {
        echo "<tr><td>{$member['id']}</td><td>{$member['first_name']} {$member['last_name']}</td><td>{$member['payout_position']}</td><td>{$member['payout_month']}</td></tr>";
    }
    echo "</table>";
    
    // Compare changes
    echo "<h3>🔍 Changes Detected:</h3>";
    foreach ($after as $member) {
        $before_member = array_filter($before, fn($m) => $m['id'] == $member['id']);
        $before_member = array_values($before_member)[0];
        
        if ($before_member['payout_position'] != $member['payout_position']) {
            echo "<div style='color: blue;'>✅ {$member['first_name']}: Position {$before_member['payout_position']} → {$member['payout_position']}</div>";
        }
        
        if ($before_member['payout_month'] != $member['payout_month']) {
            echo "<div style='color: green;'>✅ {$member['first_name']}: Payout Month {$before_member['payout_month']} → {$member['payout_month']}</div>";
        } else {
            echo "<div style='color: red;'>❌ {$member['first_name']}: Payout Month NOT updated (still {$member['payout_month']})</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='payout-positions.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Go to Payout Positions</a></p>";
echo "</body></html>";
?>