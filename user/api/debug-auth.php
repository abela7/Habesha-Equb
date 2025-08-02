<?php
/**
 * DEBUG AUTH - Test auth.php directly to see exact errors
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>🔍 AUTH.PHP DEBUG TEST</h2>";
echo "<p>Testing auth.php with real data...</p>";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<h3>📋 Test 1: Database Connection</h3>";
try {
    require_once __DIR__ . '/../../includes/db.php';
    if (!isset($pdo) && !isset($db)) {
        throw new Exception('Database not connected');
    }
    $database = isset($pdo) ? $pdo : $db;
    $test = $database->query("SELECT 1")->fetch();
    echo "✅ Database connection: OK<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 Test 2: EmailService</h3>";
try {
    require_once __DIR__ . '/../../includes/email/EmailService.php';
    $emailService = new EmailService();
    echo "✅ EmailService: OK<br>";
} catch (Exception $e) {
    echo "❌ EmailService error: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 Test 3: Device Tracking Table</h3>";
try {
    $stmt = $database->prepare("DESCRIBE device_tracking");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Device tracking columns:<br>";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
} catch (Exception $e) {
    echo "❌ Device tracking error: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 Test 4: Members Table</h3>";
try {
    $stmt = $database->prepare("SELECT email, is_approved, is_active FROM members WHERE is_approved = 1 LIMIT 3");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Approved users found:<br>";
    foreach ($users as $user) {
        echo "- {$user['email']} (approved: {$user['is_approved']}, active: {$user['is_active']})<br>";
    }
} catch (Exception $e) {
    echo "❌ Members table error: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 Test 5: Simulate OTP Request</h3>";
// Test with a real approved user
if (!empty($users)) {
    $test_email = $users[0]['email'];
    echo "Testing with email: $test_email<br>";
    
    // Simulate POST data
    $_POST = [
        'action' => 'request_otp',
        'email' => $test_email,
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "🔄 Calling auth.php...<br>";
    
    // Capture output
    ob_start();
    try {
        include 'auth.php';
        $output = ob_get_clean();
        echo "✅ Auth.php response:<br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Auth.php error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ No approved users found to test with<br>";
}

echo "<h3>📋 Test 6: Check Required Functions</h3>";
try {
    echo "validate_input function: " . (function_exists('validate_input') ? "✅ Exists" : "❌ Missing") . "<br>";
    echo "generate_member_id function: " . (function_exists('generate_member_id') ? "✅ Exists" : "❌ Missing") . "<br>";
    echo "check_csrf function: " . (function_exists('check_csrf') ? "✅ Exists" : "❌ Missing") . "<br>";
} catch (Exception $e) {
    echo "❌ Function check error: " . $e->getMessage() . "<br>";
}

echo "<p><strong>🏁 Debug complete. Check output above for errors.</strong></p>";
?>