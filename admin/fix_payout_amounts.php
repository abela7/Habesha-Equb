<?php
/**
 * HabeshaEqub - Fix Incorrect Display Payout Amounts
 * This script recalculates and updates all display_payout_amount values
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';

// Admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();

if (!$admin_id) {
    die("Unauthorized. Please log in as admin first.");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Payout Amounts - HabeshaEqub</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #301943; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #E9C46A; color: #301943; font-weight: bold; }
        .error { background: #fee; color: #c00; }
        .warning { background: #ffc; color: #840; }
        .success { background: #efe; color: #060; }
        .fixed { background: #d4edda; }
        .btn { padding: 10px 20px; background: #13665C; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        .btn:hover { background: #0F766E; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #13665C; }
        .stat-value { font-size: 24px; font-weight: bold; color: #301943; }
        .stat-label { font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>🔧 Fix Incorrect Payout Amounts</h1>";
echo "<p><strong>Admin:</strong> " . htmlspecialchars(get_current_admin_username()) . "</p>";
echo "<p>This tool will diagnose and fix incorrect <code>display_payout_amount</code> values in the members table.</p>";

try {
    // Get calculator
    $calculator = getEnhancedEqubCalculator();
    
    // Step 1: Diagnostic - Check all active members
    echo "<h2>Step 1: Diagnostic Check</h2>";
    
    $stmt = $pdo->query("
        SELECT m.*, es.equb_name, es.regular_payment_tier, es.admin_fee
        FROM members m
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        WHERE m.is_active = 1
        ORDER BY m.equb_settings_id, m.payout_position
    ");
    
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $issues = [];
    $correct = 0;
    $incorrect = 0;
    
    echo "<table>";
    echo "<thead><tr>
        <th>ID</th>
        <th>Name</th>
        <th>Equb</th>
        <th>Type</th>
        <th>Coefficient</th>
        <th>Stored Amount</th>
        <th>Correct Amount</th>
        <th>Difference</th>
        <th>Status</th>
    </tr></thead><tbody>";
    
    foreach ($members as $member) {
        $calc_result = $calculator->calculateMemberFriendlyPayout($member['id']);
        
        if (!$calc_result['success']) {
            echo "<tr class='error'>
                <td>{$member['id']}</td>
                <td>{$member['first_name']} {$member['last_name']}</td>
                <td>{$member['equb_name']}</td>
                <td colspan='6'>❌ Calculation Error: " . htmlspecialchars($calc_result['error'] ?? 'Unknown') . "</td>
            </tr>";
            continue;
        }
        
        $correct_amount = $calc_result['calculation']['display_payout'];
        $stored_amount = (float)$member['display_payout_amount'];
        $difference = abs($correct_amount - $stored_amount);
        
        $status_class = '';
        $status_text = '✅ Correct';
        
        if ($difference > 0.01) { // More than 1 penny difference
            $incorrect++;
            $status_class = 'warning';
            $status_text = '⚠️ Needs Fix';
            $issues[] = [
                'member' => $member,
                'correct_amount' => $correct_amount,
                'stored_amount' => $stored_amount,
                'calculation' => $calc_result['calculation']
            ];
        } else {
            $correct++;
        }
        
        echo "<tr class='{$status_class}'>
            <td>{$member['id']}</td>
            <td>{$member['first_name']} {$member['last_name']}</td>
            <td>{$member['equb_name']}</td>
            <td>" . ucfirst($member['membership_type']) . "</td>
            <td>{$member['position_coefficient']}</td>
            <td>£" . number_format($stored_amount, 2) . "</td>
            <td>£" . number_format($correct_amount, 2) . "</td>
            <td class='" . ($difference > 0.01 ? 'error' : '') . "'>£" . number_format($difference, 2) . "</td>
            <td>{$status_text}</td>
        </tr>";
    }
    
    echo "</tbody></table>";
    
    // Statistics
    echo "<div class='stats'>
        <div class='stat-card'>
            <div class='stat-value'>" . count($members) . "</div>
            <div class='stat-label'>Total Active Members</div>
        </div>
        <div class='stat-card'>
            <div class='stat-value' style='color: #059669;'>{$correct}</div>
            <div class='stat-label'>Correct Values</div>
        </div>
        <div class='stat-card'>
            <div class='stat-value' style='color: #dc2626;'>{$incorrect}</div>
            <div class='stat-label'>Incorrect Values</div>
        </div>
    </div>";
    
    // Step 2: Fix option
    if ($incorrect > 0) {
        echo "<h2>Step 2: Fix Incorrect Values</h2>";
        echo "<p class='warning' style='padding: 15px; border-radius: 5px;'>
            <strong>⚠️ Warning:</strong> Found {$incorrect} members with incorrect payout amounts. 
            Click the button below to recalculate and update all values.
        </p>";
        
        if (!isset($_POST['confirm_fix'])) {
            echo "<form method='post'>";
            echo "<input type='hidden' name='confirm_fix' value='1'>";
            echo "<button type='submit' class='btn' onclick='return confirm(\"Are you sure you want to update {$incorrect} member records?\");'>
                🔧 Fix All Incorrect Values
            </button>";
            echo "<a href='members.php' class='btn' style='background: #6B7280; text-decoration: none; display: inline-block;'>Cancel</a>";
            echo "</form>";
        }
    } else {
        echo "<p class='success' style='padding: 15px; border-radius: 5px; margin-top: 20px;'>
            ✅ <strong>All payout amounts are correct!</strong> No fixes needed.
        </p>";
        echo "<a href='members.php' class='btn'>← Back to Members</a>";
    }
    
    // Perform fixes if confirmed
    if (isset($_POST['confirm_fix']) && $incorrect > 0) {
        echo "<h2>Applying Fixes...</h2>";
        echo "<table><thead><tr>
            <th>ID</th>
            <th>Name</th>
            <th>Old Amount</th>
            <th>New Amount</th>
            <th>Result</th>
        </tr></thead><tbody>";
        
        $fixed_count = 0;
        $pdo->beginTransaction();
        
        try {
            foreach ($issues as $issue) {
                $member = $issue['member'];
                $new_amount = $issue['correct_amount'];
                $old_amount = $issue['stored_amount'];
                
                $update_stmt = $pdo->prepare("
                    UPDATE members 
                    SET display_payout_amount = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                
                $update_stmt->execute([$new_amount, $member['id']]);
                
                echo "<tr class='fixed'>
                    <td>{$member['id']}</td>
                    <td>{$member['first_name']} {$member['last_name']}</td>
                    <td>£" . number_format($old_amount, 2) . "</td>
                    <td>£" . number_format($new_amount, 2) . "</td>
                    <td>✅ Fixed</td>
                </tr>";
                
                $fixed_count++;
            }
            
            $pdo->commit();
            
            echo "</tbody></table>";
            echo "<p class='success' style='padding: 15px; border-radius: 5px; margin-top: 20px;'>
                ✅ <strong>Successfully updated {$fixed_count} member records!</strong>
            </p>";
            echo "<a href='members.php' class='btn'>← Back to Members</a>";
            echo "<a href='fix_payout_amounts.php' class='btn' style='background: #6B7280;'>Run Diagnostic Again</a>";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "</tbody></table>";
            echo "<p class='error' style='padding: 15px; border-radius: 5px; margin-top: 20px;'>
                ❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
            </p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error' style='padding: 15px; border-radius: 5px;'>
        ❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
    </p>";
    error_log("Fix Payout Amounts Error: " . $e->getMessage());
}

echo "</div></body></html>";
?>

