<?php
require_once 'includes/db.php';

try {
    echo "Database connection successful\n";

    // Show tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";

    // Check members table
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM members');
    $count = $stmt->fetch()['total'];
    echo "Total members: $count\n";

    $stmt = $pdo->query('SELECT COUNT(*) as active FROM members WHERE is_active = 1');
    $active = $stmt->fetch()['active'];
    echo "Active members: $active\n";

    if ($active > 0) {
        // Check contributions
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

        echo "\nContribution breakdown:\n";
        foreach ($contributions as $contrib) {
            echo "  " . $contrib['count'] . " members paying £" . $contrib['contribution'] . " (" . $contrib['membership_type'] . ")\n";
        }

        // Calculate total monthly pool
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

        echo "\nTotal monthly pool: £" . number_format($monthly_total, 2) . "\n";

        // Check EQUB duration
        $stmt = $pdo->query('SELECT duration_months FROM equb_settings LIMIT 1');
        $duration = $stmt->fetch()['duration_months'];

        echo "EQUB duration: $duration months\n";
        echo "Expected total pool: £" . number_format($monthly_total * $duration, 2) . "\n";
    } else {
        echo "No active members found. Please insert sample data.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>