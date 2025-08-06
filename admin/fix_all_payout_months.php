<?php
/**
 * FIX ALL PAYOUT MONTHS AUTOMATICALLY
 * NO HARDCODING - Everything from database
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

if (!isset($_SESSION['admin_id'])) {
    die('Access denied');
}

echo "<!DOCTYPE html><html><head><title>Fix All Payout Months</title></head><body>";
echo "<h2>🔧 Fixing All Payout Months Automatically</h2>";

try {
    $pdo->beginTransaction();
    
    // Get all active EQUB terms
    $stmt = $pdo->query("SELECT id, equb_name, start_date, payout_day, duration_months FROM equb_settings WHERE status IN ('active', 'planning')");
    $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($equb_terms as $equb) {
        echo "<h3>📊 Processing: {$equb['equb_name']}</h3>";
        echo "<p><strong>Start Date:</strong> {$equb['start_date']}, <strong>Payout Day:</strong> {$equb['payout_day']}</p>";
        
        // Get all members for this EQUB
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, payout_position FROM members WHERE equb_settings_id = ? AND is_active = 1");
        $stmt->execute([$equb['id']]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $start_date = new DateTime($equb['start_date']);
        $payout_day = intval($equb['payout_day']);
        $updated_count = 0;
        
        foreach ($members as $member) {
            $position = intval($member['payout_position']);
            
            if ($position > 0) {
                // Calculate payout month: start_date + (position - 1) months, set day to payout_day
                $payout_date = clone $start_date;
                $payout_date->add(new DateInterval('P' . ($position - 1) . 'M'));
                $payout_date->setDate($payout_date->format('Y'), $payout_date->format('n'), $payout_day);
                $payout_month = $payout_date->format('Y-m-d');
                
                // Update the member's payout month
                $update_stmt = $pdo->prepare("UPDATE members SET payout_month = ? WHERE id = ?");
                $update_stmt->execute([$payout_month, $member['id']]);
                $updated_count++;
                
                echo "<div>✅ {$member['first_name']} {$member['last_name']}: Position {$position} → {$payout_month}</div>";
            }
        }
        
        echo "<p><strong>✅ Updated {$updated_count} members for {$equb['equb_name']}</strong></p><hr>";
    }
    
    $pdo->commit();
    echo "<h3 style='color: green;'>🎉 ALL PAYOUT MONTHS FIXED SUCCESSFULLY!</h3>";
    echo "<p><strong>Now test the drag & drop - payout months should update automatically!</strong></p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='payout-positions.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Go to Payout Positions</a></p>";
echo "</body></html>";
?>