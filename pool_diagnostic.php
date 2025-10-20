<?php
/**
 * Pool Calculation Diagnostic Page
 * Shows actual database values for pool calculation debugging
 */

require_once 'includes/db.php';
require_once 'includes/security.php';

try {
    echo "<h1>Pool Calculation Diagnostic</h1>";
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>";

    // 1. Total active members
    $stmt = $pdo->query('SELECT COUNT(*) as total_members FROM members WHERE is_active = 1');
    $total_active = $stmt->fetch()['total_members'];
    echo "<h2>1. Active Members: {$total_active}</h2>";

    // 2. Contribution breakdown
    echo "<h2>2. Contribution Breakdown</h2>";
    $stmt = $pdo->prepare('
        SELECT
            CASE
                WHEN membership_type = "joint" THEN individual_contribution
                ELSE monthly_payment
            END as contribution,
            membership_type,
            COUNT(*) as count
        FROM members
        WHERE is_active = 1
        GROUP BY contribution, membership_type
        ORDER BY contribution DESC
    ');
    $stmt->execute();
    $contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>Contribution Amount</th><th>Membership Type</th><th>Count</th><th>Monthly Total</th></tr>";
    $total_monthly = 0;
    foreach ($contributions as $contrib) {
        $monthly_for_group = $contrib['contribution'] * $contrib['count'];
        $total_monthly += $monthly_for_group;
        echo "<tr>";
        echo "<td>£" . number_format($contrib['contribution'], 2) . "</td>";
        echo "<td>{$contrib['membership_type']}</td>";
        echo "<td>{$contrib['count']}</td>";
        echo "<td>£" . number_format($monthly_for_group, 2) . "</td>";
        echo "</tr>";
    }
    echo "<tr><th colspan='3'>Total Monthly Pool</th><th>£" . number_format($total_monthly, 2) . "</th></tr>";
    echo "</table>";

    // 3. EQUB duration
    $stmt = $pdo->query('SELECT duration_months FROM equb_settings LIMIT 1');
    $duration = $stmt->fetch()['duration_months'];
    echo "<h2>3. EQUB Duration: {$duration} months</h2>";

    // 4. Expected total pool
    $expected_total = $total_monthly * $duration;
    echo "<h2>4. Expected Total Pool: £" . number_format($expected_total, 2) . "</h2>";

    // 5. Current calculation in admin interface
    echo "<h2>5. Current Admin Interface Value</h2>";
    echo "<p>Please check admin/equb-management.php and report the total pool value shown there.</p>";

    // 6. Detailed member list
    echo "<h2>6. Detailed Member List</h2>";
    $stmt = $pdo->prepare('
        SELECT id, full_name, email, monthly_payment, membership_type, individual_contribution, is_active
        FROM members
        WHERE is_active = 1
        ORDER BY monthly_payment DESC, individual_contribution DESC
    ');
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Monthly Payment</th><th>Type</th><th>Individual Contribution</th><th>Effective Contribution</th></tr>";
    foreach ($members as $member) {
        $effective = ($member['membership_type'] == 'joint') ? $member['individual_contribution'] : $member['monthly_payment'];
        echo "<tr>";
        echo "<td>{$member['id']}</td>";
        echo "<td>{$member['full_name']}</td>";
        echo "<td>{$member['email']}</td>";
        echo "<td>£" . number_format($member['monthly_payment'], 2) . "</td>";
        echo "<td>{$member['membership_type']}</td>";
        echo "<td>£" . number_format($member['individual_contribution'], 2) . "</td>";
        echo "<td>£" . number_format($effective, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 7. API calculation check
    echo "<h2>7. API Calculation Check</h2>";
    require_once 'admin/api/equb-management.php';
    $api_total = calculate_total_pool();
    echo "<p>API calculate_total_pool() result: £" . number_format($api_total, 2) . "</p>";

    // 8. Expected vs Actual comparison
    echo "<h2>8. Expected vs Actual Comparison</h2>";
    echo "<table>";
    echo "<tr><th>Metric</th><th>Expected</th><th>Actual</th><th>Difference</th></tr>";
    echo "<tr><td>Monthly Pool</td><td>£10,000.00</td><td>£" . number_format($total_monthly, 2) . "</td><td>£" . number_format(10000 - $total_monthly, 2) . "</td></tr>";
    echo "<tr><td>Total Pool (10 months)</td><td>£100,000.00</td><td>£" . number_format($expected_total, 2) . "</td><td>£" . number_format(100000 - $expected_total, 2) . "</td></tr>";
    echo "</table>";

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>