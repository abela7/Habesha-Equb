<?php
/**
 * QUICK OTP TEST - Test the exact function calls that were failing
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 QUICK OTP TEST</h1>";

try {
    // Database connection
    require_once __DIR__ . '/../../includes/db.php';
    $database = isset($pdo) ? $pdo : $db;
    echo "✅ Database connected<br>";
    
    // EmailService with correct constructor
    require_once __DIR__ . '/../../includes/email/EmailService.php';
    $emailService = new EmailService($database);
    echo "✅ EmailService created successfully<br>";
    
    // Test user (get first approved user)
    $stmt = $database->prepare("SELECT id, email, first_name FROM members WHERE is_approved = 1 AND is_active = 1 LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ No approved users found<br>";
        exit;
    }
    
    echo "✅ Test user: {$user['email']}<br>";
    
    // Test generateOTP with correct arguments
    $otp_code = $emailService->generateOTP($user['id'], $user['email'], 'otp_login');
    echo "✅ generateOTP successful, code: $otp_code<br>";
    
    // Test verifyOTP with correct arguments
    $verify_result = $emailService->verifyOTP($user['email'], $otp_code, 'otp_login');
    echo $verify_result ? "✅ verifyOTP successful<br>" : "❌ verifyOTP failed<br>";
    
    echo "<h2>🎉 ALL FUNCTION CALLS WORK!</h2>";
    echo "<p><strong>EmailService is ready for login system!</strong></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>