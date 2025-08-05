<?php
/**
 * Simple User Approval - Step by Step
 * Direct approach with clear error messages
 */

// Basic headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Simple error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => '', 'steps' => []];

try {
    // Step 1: Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests allowed');
    }
    $response['steps'][] = '✅ POST request verified';

    // Step 2: Get input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['user_id'])) {
        throw new Exception('Missing user_id in request');
    }
    
    $user_id = (int)$data['user_id'];
    if ($user_id <= 0) {
        throw new Exception('Invalid user_id: ' . $user_id);
    }
    $response['steps'][] = '✅ User ID validated: ' . $user_id;

    // Step 3: Database connection
    require_once '../../includes/db.php';
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection failed');
    }
    $response['steps'][] = '✅ Database connected';

    // Step 4: Get user details
    $stmt = $pdo->prepare("SELECT id, member_id, first_name, last_name, email, is_approved FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found with ID: ' . $user_id);
    }
    
    if ($user['is_approved'] == 1) {
        throw new Exception('User is already approved');
    }
    $response['steps'][] = '✅ User found: ' . $user['first_name'] . ' ' . $user['last_name'];

    // Step 5: Update user approval
    $pdo->beginTransaction();
    
    $update_stmt = $pdo->prepare("UPDATE members SET is_approved = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $update_result = $update_stmt->execute([$user_id]);
    
    if (!$update_result || $update_stmt->rowCount() === 0) {
        $pdo->rollBack();
        throw new Exception('Failed to update user approval status');
    }
    $response['steps'][] = '✅ User approved in database';

    // Step 6: Update device tracking (if exists)
    try {
        $device_stmt = $pdo->prepare("UPDATE device_tracking SET is_approved = 1 WHERE email = ?");
        $device_stmt->execute([$user['email']]);
        $response['steps'][] = '✅ Device tracking updated';
    } catch (Exception $e) {
        $response['steps'][] = '⚠️ Device tracking failed (not critical): ' . $e->getMessage();
    }

    // Step 7: Send email
    $email_sent = false;
    $email_error = '';
    
    try {
        require_once '../../includes/email/EmailService.php';
        $emailService = new EmailService($pdo);
        
        $email_data = [
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'member_id' => $user['member_id'],
            'email' => $user['email'],
            'login_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/user/email-redirect.php?to=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . '/user/login.php')
        ];
        
        $email_result = $emailService->send(
            'account_approved',
            $user['email'],
            $user['first_name'] . ' ' . $user['last_name'],
            $email_data
        );
        
        if ($email_result && isset($email_result['success']) && $email_result['success']) {
            $email_sent = true;
            $response['steps'][] = '✅ Welcome email sent to ' . $user['email'];
        } else {
            $email_error = $email_result['message'] ?? 'Unknown email error';
            $response['steps'][] = '❌ Email failed: ' . $email_error;
        }
        
    } catch (Exception $e) {
        $email_error = $e->getMessage();
        $response['steps'][] = '❌ Email service error: ' . $email_error;
    }

    // Step 8: Log notification (simple version)
    try {
        $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $log_stmt = $pdo->prepare("
            INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, notes) 
            VALUES (?, 'member', ?, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', ?, NOW(), ?)
        ");
        
        $log_stmt->execute([
            $notification_id,
            $user_id,
            $email_sent ? 'sent' : 'failed',
            'User approved. Email ' . ($email_sent ? 'sent' : 'failed: ' . $email_error)
        ]);
        $response['steps'][] = '✅ Notification logged';
    } catch (Exception $e) {
        $response['steps'][] = '⚠️ Notification logging failed: ' . $e->getMessage();
    }

    // Step 9: Commit transaction
    $pdo->commit();
    $response['steps'][] = '✅ Transaction committed';

    // Success response
    $response['success'] = true;
    $response['message'] = 'User approved successfully!';
    $response['user'] = [
        'id' => $user_id,
        'member_id' => $user['member_id'],
        'name' => $user['first_name'] . ' ' . $user['last_name'],
        'email' => $user['email']
    ];
    $response['email_sent'] = $email_sent;
    $response['email_error'] = $email_error;

} catch (Exception $e) {
    // Rollback if transaction started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['steps'][] = '❌ FAILED: ' . $e->getMessage();
}

// Send response
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>