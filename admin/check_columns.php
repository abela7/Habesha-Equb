<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: text/plain');

echo "=== CHECKING DATABASE STRUCTURE ===\n";

try {
    // Check payouts table columns
    $stmt = $pdo->query("DESCRIBE payouts");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "PAYOUTS TABLE COLUMNS:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n=== CHECKING EXISTING PAYOUTS ===\n";
    
    // Check if payouts exist and can be selected
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payouts");
    $count = $stmt->fetchColumn();
    echo "Total payouts: $count\n";
    
    if ($count > 0) {
        // Try simple select
        $stmt = $pdo->query("SELECT id, payout_id, member_id, status FROM payouts LIMIT 3");
        $simple_payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSimple payout data:\n";
        foreach ($simple_payouts as $p) {
            echo "- ID: {$p['id']}, Payout ID: {$p['payout_id']}, Member: {$p['member_id']}, Status: {$p['status']}\n";
        }
        
        // Try with JOIN
        echo "\n=== TESTING JOIN QUERY ===\n";
        $stmt = $pdo->query("
            SELECT 
                p.id, p.payout_id, p.member_id, p.status,
                CONCAT(m.first_name, ' ', m.last_name) as member_name
            FROM payouts p
            LEFT JOIN members m ON p.member_id = m.id
            LIMIT 3
        ");
        $join_payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "JOIN query results:\n";
        foreach ($join_payouts as $p) {
            echo "- {$p['payout_id']}: {$p['member_name']} (Status: {$p['status']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
?>
