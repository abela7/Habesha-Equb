<?php
/**
 * Fix Pool Amount Script
 * Updates the equb_settings table with the correct total pool amount
 */

require_once 'includes/db.php';

try {
    echo "<h1>Fix Pool Amount</h1>";
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; margin-bottom: 20px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>";

    // Get current equb settings
    $stmt = $pdo->query('SELECT * FROM equb_settings LIMIT 1');
    $equb = $stmt->fetch();

    if (!$equb) {
        echo "<p>No equb settings found.</p>";
        exit;
    }

    echo "<h2>Current Equb Settings</h2>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Equb ID</td><td>{$equb['equb_id']}</td></tr>";
    echo "<tr><td>Duration (months)</td><td>{$equb['duration_months']}</td></tr>";
    echo "<tr><td>Current total_pool_amount</td><td>£" . number_format($equb['total_pool_amount'], 2) . "</td></tr>";
    echo "</table>";

    // Calculate correct monthly pool
    $stmt = $pdo->prepare('
        SELECT COALESCE(SUM(
            CASE
                WHEN membership_type = "joint" THEN individual_contribution
                ELSE monthly_payment
            END
        ), 0) as total_monthly
        FROM members
        WHERE is_active = 1
    ');
    $stmt->execute();
    $monthly_total = $stmt->fetch()['total_monthly'];

    $correct_total_pool = $monthly_total * $equb['duration_months'];

    echo "<h2>Calculation Breakdown</h2>";
    echo "<table>";
    echo "<tr><th>Metric</th><th>Value</th></tr>";
    echo "<tr><td>Total Monthly Pool</td><td>£" . number_format($monthly_total, 2) . "</td></tr>";
    echo "<tr><td>Duration</td><td>{$equb['duration_months']} months</td></tr>";
    echo "<tr><td>Correct Total Pool</td><td>£" . number_format($correct_total_pool, 2) . "</td></tr>";
    echo "<tr><td>Current Value</td><td>£" . number_format($equb['total_pool_amount'], 2) . "</td></tr>";
    echo "<tr><td>Difference</td><td>£" . number_format($correct_total_pool - $equb['total_pool_amount'], 2) . "</td></tr>";
    echo "</table>";

    // Update the database
    if ($correct_total_pool != $equb['total_pool_amount']) {
        echo "<h2>Updating Database...</h2>";

        $stmt = $pdo->prepare('
            UPDATE equb_settings
            SET total_pool_amount = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$correct_total_pool, $equb['id']]);

        echo "<p style='color: green; font-weight: bold;'>✅ Successfully updated total_pool_amount to £" . number_format($correct_total_pool, 2) . "</p>";

        // Verify the update
        $stmt = $pdo->query('SELECT total_pool_amount FROM equb_settings LIMIT 1');
        $updated_amount = $stmt->fetch()['total_pool_amount'];

        echo "<p><strong>Verification:</strong> Database now shows £" . number_format($updated_amount, 2) . "</p>";

    } else {
        echo "<p style='color: blue;'>ℹ️ The total pool amount is already correct.</p>";
    }

    echo "<hr>";
    echo "<p><a href='admin/equb-management.php' style='color: #007bff;'>← Back to Equb Management</a></p>";

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>