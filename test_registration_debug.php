<?php
/**
 * Debug Registration API
 * Test the registration process directly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Registration API Debug Test</h2>\n";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>\n";
try {
    require_once 'includes/db.php';
    echo "✅ Database connection: SUCCESS<br>\n";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>\n";
    exit;
}

// Test 2: Session and CSRF
echo "<h3>2. Session & CSRF Test</h3>\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$csrf_token = generate_csrf_token();
echo "✅ CSRF token generated: " . substr($csrf_token, 0, 10) . "...<br>\n";

// Test 3: Simulate Registration Data
echo "<h3>3. Registration Data Simulation</h3>\n";
$test_data = [
    'action' => 'register',
    'csrf_token' => $csrf_token,
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test' . rand(1000, 9999) . '@example.com',
    'phone' => '+1234567890',
    'password' => 'test123',
    'confirm_password' => 'test123',
    'agree_terms' => 'on'
];

// Test 4: Direct API Call
echo "<h3>4. Direct API Test</h3>\n";
echo "<p>Testing with data:</p>\n";
echo "<pre>" . print_r($test_data, true) . "</pre>\n";

// Simulate POST data
$_POST = $test_data;
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h4>API Response:</h4>\n";
echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>\n";

// Capture output
ob_start();
try {
    include 'user/api/auth.php';
} catch (Exception $e) {
    echo "❌ PHP ERROR: " . $e->getMessage() . "<br>\n";
    echo "Stack trace:<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</div>\n";

// Test 5: Check for specific issues
echo "<h3>5. Specific Checks</h3>\n";

// Check if functions exist
$functions_to_check = ['validate_user_input', 'create_member', 'user_email_exists', 'send_json_response'];
foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        echo "✅ Function '$func' exists<br>\n";
    } else {
        echo "❌ Function '$func' missing<br>\n";
    }
}

// Check if tables exist
try {
    $stmt = $pdo->query("DESCRIBE members");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Members table exists with columns: " . implode(', ', $columns) . "<br>\n";
    
    $required_columns = ['first_name', 'last_name', 'email', 'phone', 'password'];
    foreach ($required_columns as $col) {
        if (in_array($col, $columns)) {
            echo "✅ Column '$col' exists<br>\n";
        } else {
            echo "❌ Column '$col' missing<br>\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Table check failed: " . $e->getMessage() . "<br>\n";
}

echo "<h3>🏁 Test Complete</h3>\n";
echo "<p>If you see any errors above, that's what's causing the network error.</p>\n";
echo "<p><strong>Delete this file after checking!</strong></p>\n";
?> 