<?php
/**
 * ISOLATE THE EXACT 500 ERROR
 */
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== STARTING ERROR ISOLATION TEST ===\n";

// Test 1: Basic PHP
echo "✅ PHP is working\n";

// Test 2: Try including database
echo "Testing database include...\n";
try {
    require_once '../../includes/db.php';
    echo "✅ Database included successfully\n";
} catch (Exception $e) {
    echo "❌ Database include failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Try database connection
echo "Testing database connection...\n";
try {
    $test = $pdo->query("SELECT 1")->fetchColumn();
    echo "✅ Database connection works\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Try including auth guard
echo "Testing auth guard include...\n";
try {
    require_once '../includes/admin_auth_guard.php';
    echo "✅ Auth guard included successfully\n";
} catch (Exception $e) {
    echo "❌ Auth guard include failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 5: Try auth functions
echo "Testing auth functions...\n";
try {
    $admin_id = get_current_admin_id();
    $admin_username = get_current_admin_username();
    echo "✅ Auth functions work - Admin ID: $admin_id, Username: $admin_username\n";
} catch (Exception $e) {
    echo "❌ Auth functions failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 6: Try member_messages table
echo "Testing member_messages table...\n";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM member_messages")->fetchColumn();
    echo "✅ member_messages table accessible - Count: $count\n";
} catch (Exception $e) {
    echo "❌ member_messages table failed: " . $e->getMessage() . "\n";
    exit;
}

echo "=== ALL TESTS PASSED ===\n";
?>