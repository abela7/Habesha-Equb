<?php
/**
 * Database Schema and Data Export Script
 * Shows table structures, relationships, and sample data
 */

require_once 'includes/db.php';

try {
    echo "<h1>Database Schema and Data Export</h1>";
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; margin-bottom: 20px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } .section { margin-top: 30px; }</style>";

    // 1. Show all tables
    echo "<div class='section'>";
    echo "<h2>1. All Tables in Database</h2>";
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<table>";
    echo "<tr><th>Table Name</th><th>Description</th></tr>";
    foreach ($tables as $table) {
        $description = get_table_description($table);
        echo "<tr><td>{$table}</td><td>{$description}</td></tr>";
    }
    echo "</table>";
    echo "</div>";

    // 2. Key table structures
    $key_tables = ['members', 'equb_settings', 'payments', 'payouts', 'user_sessions'];
    echo "<div class='section'>";
    echo "<h2>2. Key Table Structures</h2>";

    foreach ($key_tables as $table) {
        if (in_array($table, $tables)) {
            echo "<h3>Table: {$table}</h3>";
            $stmt = $pdo->query("DESCRIBE {$table}");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<table>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "<td>{$column['Default']}</td>";
                echo "<td>{$column['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    echo "</div>";

    // 3. Sample data from key tables
    echo "<div class='section'>";
    echo "<h2>3. Sample Data (First 10 rows)</h2>";

    foreach ($key_tables as $table) {
        if (in_array($table, $tables)) {
            echo "<h3>Sample from: {$table}</h3>";
            try {
                $stmt = $pdo->query("SELECT * FROM {$table} LIMIT 10");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    echo "<table>";
                    // Headers
                    echo "<tr>";
                    foreach (array_keys($rows[0]) as $header) {
                        echo "<th>{$header}</th>";
                    }
                    echo "</tr>";

                    // Data rows
                    foreach ($rows as $row) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            $display_value = is_null($value) ? 'NULL' : htmlspecialchars($value);
                            echo "<td>{$display_value}</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No data in this table</p>";
                }
            } catch (Exception $e) {
                echo "<p>Error reading table {$table}: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    echo "</div>";

    // 4. Foreign key relationships (if any)
    echo "<div class='section'>";
    echo "<h2>4. Foreign Key Relationships</h2>";
    try {
        $stmt = $pdo->query("
            SELECT
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY TABLE_NAME, COLUMN_NAME
        ");
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($fks)) {
            echo "<table>";
            echo "<tr><th>Table</th><th>Column</th><th>References</th><th>Referenced Column</th></tr>";
            foreach ($fks as $fk) {
                echo "<tr>";
                echo "<td>{$fk['TABLE_NAME']}</td>";
                echo "<td>{$fk['COLUMN_NAME']}</td>";
                echo "<td>{$fk['REFERENCED_TABLE_NAME']}</td>";
                echo "<td>{$fk['REFERENCED_COLUMN_NAME']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No foreign key relationships found</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error reading foreign keys: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";

    // 5. Row counts for all tables
    echo "<div class='section'>";
    echo "<h2>5. Row Counts</h2>";
    echo "<table>";
    echo "<tr><th>Table</th><th>Row Count</th></tr>";

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $stmt->fetch()['count'];
            echo "<tr><td>{$table}</td><td>{$count}</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td>{$table}</td><td>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }
    echo "</table>";
    echo "</div>";

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

function get_table_description($table_name) {
    $descriptions = [
        'members' => 'User/member accounts and profiles',
        'equb_settings' => 'EQUB configuration and settings',
        'payments' => 'Payment records and transactions',
        'payouts' => 'Payout records and distributions',
        'user_sessions' => 'User session management',
        'notifications' => 'System notifications',
        'admin_logs' => 'Administrative action logs',
        'security_logs' => 'Security event logs',
        'joint_groups' => 'Joint membership groups',
        'position_assignments' => 'Payout position assignments'
    ];

    return isset($descriptions[$table_name]) ? $descriptions[$table_name] : 'Unknown table';
}
?>