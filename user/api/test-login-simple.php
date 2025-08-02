<?php
/**
 * SIMPLE LOGIN OTP TEST
 * Visit: user/api/test-login-simple.php?email=abelgoytom77@gmail.com
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Database connection
    require_once __DIR__ . '/../../includes/db.php';
    $database = isset($pdo) ? $pdo : $db;
    
    $test_email = $_GET['email'] ?? 'abelgoytom77@gmail.com';
    
    // Find user
    $stmt = $database->prepare("SELECT id, email, first_name, is_approved, is_active FROM members WHERE email = ?");
    $stmt->execute([$test_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    if (!$user['is_approved'] || !$user['is_active']) {
        echo json_encode(['success' => false, 'error' => 'User not approved/active']);
        exit;
    }
    
    // Load EmailService
    require_once __DIR__ . '/../../includes/email/EmailService.php';
    $emailService = new EmailService($database);
    
    // Generate OTP
    $otp_code = $emailService->generateOTP($user['id'], $user['email'], 'otp_login');
    
    // Check storage
    $check_stmt = $database->prepare("
        SELECT otp_code, expires_at, created_at, 
               (expires_at > NOW()) as is_valid,
               TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_left
        FROM user_otps 
        WHERE email = ? AND otp_type = 'otp_login' 
        ORDER BY id DESC LIMIT 1
    ");
    $check_stmt->execute([$user['email']]);
    $stored_otp = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test verification
    $verify_result = $emailService->verifyOTP($user['email'], $otp_code, 'otp_login');
    
    echo json_encode([
        'success' => true, 
        'message' => 'OTP system working!',
        'otp_code' => $otp_code,
        'stored' => $stored_otp,
        'verification' => $verify_result ? 'SUCCESS' : 'FAILED'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>