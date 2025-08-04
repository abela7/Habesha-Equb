<?php
/**
 * NOTIFICATION SYSTEM CLEANUP SCRIPT
 */

require_once 'includes/db.php';

echo "🗑️ REMOVING NOTIFICATION DATABASE TABLES...\n";

// Remove tables in correct order (foreign keys first)
$tables = ['member_message_reads', 'member_messages'];

foreach ($tables as $table) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "✅ Dropped table: $table\n";
    } catch (Exception $e) {
        echo "❌ Error dropping $table: " . $e->getMessage() . "\n";
    }
}

// Remove stored procedure
try {
    $pdo->exec("DROP PROCEDURE IF EXISTS CreateMemberMessageForMembers");
    echo "✅ Dropped procedure: CreateMemberMessageForMembers\n";
} catch (Exception $e) {
    echo "❌ Error dropping procedure: " . $e->getMessage() . "\n";
}

echo "✅ DATABASE CLEANUP COMPLETED!\n";
?>