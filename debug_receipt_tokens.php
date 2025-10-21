<?php
require_once 'includes/db.php';

echo "=== Receipt Token Debug ===\n\n";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'payment_receipts'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✓ Table 'payment_receipts' EXISTS\n\n";
        
        // Get recent tokens
        $stmt = $pdo->query("SELECT id, payment_id, token, created_at FROM payment_receipts ORDER BY created_at DESC LIMIT 5");
        $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Recent receipt tokens:\n";
        foreach ($receipts as $receipt) {
            echo "  - Payment ID: {$receipt['payment_id']}\n";
            echo "    Token: {$receipt['token']}\n";
            echo "    Created: {$receipt['created_at']}\n";
            echo "    URL: https://habeshaequb.com/receipt.php?rt={$receipt['token']}\n\n";
        }
        
        // Check total count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM payment_receipts");
        $count = $stmt->fetch();
        echo "Total receipt tokens: {$count['total']}\n\n";
        
    } else {
        echo "✗ Table 'payment_receipts' DOES NOT EXIST\n";
        echo "Need to create the table first.\n";
    }
    
    // Check recent payments
    $stmt = $pdo->query("SELECT id, payment_id, member_id, amount, status, verification_date FROM payments ORDER BY verification_date DESC LIMIT 3");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nRecent verified payments:\n";
    foreach ($payments as $payment) {
        echo "  - Payment ID: {$payment['id']}\n";
        echo "    Status: {$payment['status']}\n";
        echo "    Verified: {$payment['verification_date']}\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

