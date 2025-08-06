<?php
require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

// Get current member positions for equb_id = 2
$stmt = $pdo->query('
    SELECT id, first_name, last_name, payout_position, position_coefficient, 
           membership_type, joint_group_id 
    FROM members 
    WHERE equb_settings_id = 2 AND is_active = 1 
    ORDER BY payout_position
');
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>🔍 Current Database State</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Coefficient</th><th>Type</th><th>Joint Group</th></tr>";

foreach ($members as $member) {
    echo "<tr>";
    echo "<td>{$member['id']}</td>";
    echo "<td>{$member['first_name']} {$member['last_name']}</td>";
    echo "<td>{$member['payout_position']}</td>";
    echo "<td>{$member['position_coefficient']}</td>";
    echo "<td>{$member['membership_type']}</td>";
    echo "<td>{$member['joint_group_id']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check how many unique positions we have
$positions = array_unique(array_column($members, 'payout_position'));
sort($positions);
echo "<h3>📊 Position Analysis</h3>";
echo "<p><strong>Unique positions:</strong> " . implode(', ', $positions) . "</p>";
echo "<p><strong>Total unique positions:</strong> " . count($positions) . "</p>";

// Check if Michael and Koki are properly separated
$michael = array_filter($members, fn($m) => $m['first_name'] === 'Michael');
$koki = array_filter($members, fn($m) => $m['first_name'] === 'Koki');

if ($michael && $koki) {
    $michael = array_values($michael)[0];
    $koki = array_values($koki)[0];
    
    echo "<h3>🎯 Michael & Koki Analysis</h3>";
    echo "<p><strong>Michael:</strong> Position {$michael['payout_position']}, Coefficient {$michael['position_coefficient']}</p>";
    echo "<p><strong>Koki:</strong> Position {$koki['payout_position']}, Coefficient {$koki['position_coefficient']}</p>";
    
    if ($michael['payout_position'] === $koki['payout_position']) {
        echo "<p style='color: red;'>❌ PROBLEM: Both in same position!</p>";
    } else {
        echo "<p style='color: green;'>✅ CORRECT: They have separate positions!</p>";
    }
}
?>