<?php
/**
 * Direct test for approval system - will show exact PHP errors
 */

// Show all errors directly
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Direct Approval System Test</h2>";
echo "<p>Testing each component separately...</p>";

// Test 1: Database connection
echo "<h3>1. Testing Database Connection</h3>";
try {
    require_once '../../includes/db.php';
    echo "✅ Database connection: SUCCESS<br>";
    echo "Database object type: " . get_class($pdo) . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Email service
echo "<h3>2. Testing Email Service</h3>";
try {
    require_once '../../includes/email/EmailService.php';
    echo "✅ EmailService include: SUCCESS<br>";
    
    $emailService = new EmailService($pdo);
    echo "✅ EmailService instantiation: SUCCESS<br>";
} catch (Exception $e) {
    echo "❌ EmailService: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 3: Admin authentication
echo "<h3>3. Testing Admin Authentication</h3>";
try {
    require_once '../includes/admin_auth_guard.php';
    echo "✅ Admin auth include: SUCCESS<br>";
    echo "Admin ID: " . (isset($admin_id) ? $admin_id : 'NOT SET') . "<br>";
} catch (Exception $e) {
    echo "❌ Admin auth: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 4: Sample user query
echo "<h3>4. Testing User Query</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, member_id, first_name, last_name, email, is_approved FROM members WHERE is_approved = 0 LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User query: SUCCESS<br>";
        echo "Found pending user: " . $user['first_name'] . " " . $user['last_name'] . " (ID: " . $user['id'] . ")<br>";
    } else {
        echo "⚠️ User query: SUCCESS but no pending users found<br>";
    }
} catch (Exception $e) {
    echo "❌ User query: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 5: Notifications table
echo "<h3>5. Testing Notifications Table</h3>";
try {
    $stmt = $pdo->prepare("DESCRIBE notifications");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Notifications table: SUCCESS<br>";
    echo "Columns: " . count($columns) . " found<br>";
} catch (Exception $e) {
    echo "❌ Notifications table: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 6: Email template
echo "<h3>6. Testing Email Template</h3>";
$template_path = '../../includes/email/templates/account_approved.html';
if (file_exists($template_path)) {
    echo "✅ Email template: EXISTS<br>";
    echo "Template size: " . filesize($template_path) . " bytes<br>";
} else {
    echo "❌ Email template: NOT FOUND<br>";
    echo "Looking for: " . $template_path . "<br>";
}

echo "<h3>✅ Test Complete</h3>";
echo "<p>If all tests pass, the approval system should work. If any fail, that's your issue!</p>";
?>