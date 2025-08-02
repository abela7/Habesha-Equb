<?php
/**
 * SIMPLE LOGIN TEST - Replicate exact login attempt
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 LOGIN TEST</h1>";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token like the real form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<h2>Step 1: Test Database Connection</h2>";
try {
    require_once __DIR__ . '/../../includes/db.php';
    $database = isset($pdo) ? $pdo : $db;
    echo "✅ Database connected<br>";
    
    // Test members table
    $stmt = $database->prepare("SELECT COUNT(*) as count FROM members WHERE is_approved = 1");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Found {$result['count']} approved members<br>";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>Step 2: Find Test User</h2>";
try {
    $stmt = $database->prepare("SELECT email, first_name, is_approved, is_active FROM members WHERE is_approved = 1 AND is_active = 1 LIMIT 1");
    $stmt->execute();
    $test_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($test_user) {
        echo "✅ Test user: {$test_user['email']} (approved: {$test_user['is_approved']}, active: {$test_user['is_active']})<br>";
    } else {
        echo "❌ No approved active users found<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ User lookup error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>Step 3: Test EmailService</h2>";
try {
    require_once __DIR__ . '/../../includes/email/EmailService.php';
    $emailService = new EmailService();
    echo "✅ EmailService loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ EmailService error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>Step 4: Simulate Exact Login Request</h2>";
// Replicate exact POST data from login form
$_POST = [
    'action' => 'request_otp',
    'email' => $test_user['email'],
    'csrf_token' => $_SESSION['csrf_token']
];

echo "📤 Simulating login request for: {$test_user['email']}<br>";
echo "🔑 CSRF token: " . substr($_SESSION['csrf_token'], 0, 16) . "...<br>";

// Capture exact output from auth.php
ob_start();
try {
    include 'auth.php';
    $output = ob_get_clean();
    
    echo "<h3>✅ Auth.php Response:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Try to decode as JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h3>📊 Parsed JSON:</h3>";
        echo "<pre>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<h3>❌ Response is not valid JSON</h3>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Fatal error in auth.php: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>✅ Test Complete</h2>";
echo "<p><strong>Check the output above to see exactly what's happening!</strong></p>";
?>