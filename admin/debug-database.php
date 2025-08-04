<?php
/**
 * EMERGENCY DATABASE STRUCTURE DEBUG
 * This will show us exactly what columns exist in your tables
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

echo "<!DOCTYPE html><html><head><title>Database Debug</title></head><body>";
echo "<h1>🔍 Database Structure Debug</h1>";

try {
    // Check members table structure
    echo "<h2>📋 Members Table Structure</h2>";
    $members_columns = $pdo->query("DESCRIBE members")->fetchAll();
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($members_columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test basic members query
    echo "<h2>👥 Sample Members Data</h2>";
    $sample_members = $pdo->query("SELECT id, member_id, first_name, last_name, email, last_login FROM members LIMIT 3")->fetchAll();
    if (!empty($sample_members)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Member ID</th><th>Name</th><th>Email</th><th>Last Login</th></tr>";
        foreach ($sample_members as $member) {
            echo "<tr>";
            echo "<td>{$member['id']}</td>";
            echo "<td>{$member['member_id']}</td>";
            echo "<td>{$member['first_name']} {$member['last_name']}</td>";
            echo "<td>{$member['email']}</td>";
            echo "<td>" . ($member['last_login'] ?: 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: green;'><strong>✅ Members table and last_login column are working!</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ No members found</p>";
    }
    
    // Check other tables
    echo "<h2>📊 Other Tables</h2>";
    $tables = ['user_otps', 'device_tracking', 'admins'];
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as count FROM {$table}")->fetch()['count'];
            echo "<p>✅ <strong>{$table}</strong>: {$count} records</p>";
        } catch (Exception $e) {
            echo "<p>❌ <strong>{$table}</strong>: Error - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>🔧 Database Connection Info</h2>";
    $server_info = $pdo->getAttribute(PDO::ATTR_SERVER_INFO);
    echo "<p>Server: {$server_info}</p>";
    
    $db_name = $pdo->query("SELECT DATABASE() as db")->fetch()['db'];
    echo "<p>Database: <strong>{$db_name}</strong></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Database Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
}

echo "<hr>";
echo "<p><a href='security-settings.php'>← Back to Security Settings</a></p>";
echo "</body></html>";
?>