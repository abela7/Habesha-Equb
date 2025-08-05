<?php
/**
 * HabeshaEqub - User Approval API
 * Professional member approval system with automated email notifications
 * 
 * @author HabeshaEqub Development Team
 * @version 2.0
 */

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set up error and exception handlers to ensure JSON responses
set_error_handler(function($severity, $message, $file, $line) {
    error_log("🚨 PHP ERROR: $message in $file:$line");
    if (!(error_reporting() & $severity)) return;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($exception) {
    error_log("🚨 UNCAUGHT EXCEPTION: " . $exception->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $exception->getMessage(),
        'error_details' => [
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine()
        ]
    ]);
    exit;
});

// Start output buffering to prevent any unwanted output
ob_start();

// Database connection
try {
    require_once '../../includes/db.php';
    $db = $pdo;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Email service
try {
    require_once '../../includes/email/EmailService.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Email service unavailable']);
    exit;
}

// Admin authentication
try {
    require_once '../includes/admin_auth_guard.php';
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Send JSON response and exit
 */
function send_response($success, $message, $data = null) {
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

/**
 * Get user details for approval
 */
function getUser($db, $user_id) {
    $stmt = $db->prepare("
        SELECT id, member_id, first_name, last_name, email, phone, is_approved, is_active
        FROM members 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['is_approved'] == 1) {
        throw new Exception('User is already approved');
    }
    
    return $user;
}

/**
 * Approve user and send welcome email
 */
function approveUser($db, $user_id, $user, $admin_id) {
    try {
        $db->beginTransaction();
        
        // 1. Update user approval status
        $stmt = $db->prepare("
            UPDATE members 
            SET is_approved = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to approve user');
        }
        
        // 2. Update device tracking
        $device_stmt = $db->prepare("
            UPDATE device_tracking 
            SET is_approved = 1, last_seen = CURRENT_TIMESTAMP 
            WHERE email = ?
        ");
        $device_stmt->execute([$user['email']]);
        
        // 3. Send welcome email
        $email_sent = false;
        $email_error = null;
        
        try {
            $emailService = new EmailService($db);
            
            $email_data = [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'member_id' => $user['member_id'],
                'email' => $user['email'],
                'login_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/user/email-redirect.php?to=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . '/user/login.php')
            ];
            
            $result = $emailService->send(
                'account_approved',
                $user['email'],
                $user['first_name'] . ' ' . $user['last_name'],
                $email_data
            );
            
            if ($result && $result['success']) {
                $email_sent = true;
            } else {
                $email_error = $result['message'] ?? 'Unknown email error';
            }
            
        } catch (Exception $e) {
            $email_error = $e->getMessage();
        }
        
        // 4. Log notification
        $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $log_stmt = $db->prepare("
            INSERT INTO notifications (
                notification_id, recipient_type, recipient_id, type, channel,
                subject, message, language, status, sent_at, sent_by_admin_id, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");
        
        $log_stmt->execute([
            $notification_id,
            'member',
            $user_id,
            'approval',
            'email',
            'Welcome to HabeshaEqub - Account Approved',
            'Your HabeshaEqub account has been approved. Welcome to our equb community!',
            'en',
            $email_sent ? 'sent' : 'failed',
            $admin_id,
            'User approved by admin. Email ' . ($email_sent ? 'sent successfully' : 'failed: ' . $email_error)
        ]);
        
        $db->commit();
        
        return [
            'user_id' => $user_id,
            'member_id' => $user['member_id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'approved_by' => $admin_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'email_sent' => $email_sent,
            'email_error' => $email_error
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Decline user registration
 */
function declineUser($db, $user_id, $user, $admin_id, $reason) {
    try {
        $db->beginTransaction();
        
        // Update user status
        $stmt = $db->prepare("
            UPDATE members 
            SET is_approved = 0, is_active = 0, notes = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute(['Declined: ' . $reason, $user_id]);
        
        // Log notification
        $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $log_stmt = $db->prepare("
            INSERT INTO notifications (
                notification_id, recipient_type, recipient_id, type, channel,
                subject, message, language, status, sent_at, sent_by_admin_id, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");
        
        $log_stmt->execute([
            $notification_id,
            'member',
            $user_id,
            'approval',
            'email',
            'HabeshaEqub - Registration Update',
            'Your registration has been reviewed. ' . $reason,
            'en',
            'pending',
            $admin_id,
            'User declined by admin. Reason: ' . $reason
        ]);
        
        $db->commit();
        
        return [
            'user_id' => $user_id,
            'member_id' => $user['member_id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'action' => 'declined',
            'reason' => $reason,
            'declined_by' => $admin_id,
            'declined_at' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// Main request processing
try {
    error_log("🔍 APPROVAL API: Request started from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }
    
    // Get and validate input
    $input = file_get_contents('php://input');
    error_log("🔍 APPROVAL API: Raw input: " . $input);
    
    $data = json_decode($input, true);
    error_log("🔍 APPROVAL API: Parsed data: " . print_r($data, true));
    
    if (!$data || !isset($data['action']) || !isset($data['user_id'])) {
        throw new Exception('Invalid request data. Action and user_id are required.');
    }
    
    $action = sanitize_input($data['action']);
    $user_id = (int)$data['user_id'];
    $reason = isset($data['reason']) ? sanitize_input($data['reason']) : '';
    
    // Validate user_id
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }
    
    // Get user details
    $user = getUser($db, $user_id);
    
    // Process action
    switch ($action) {
        case 'approve':
            $result = approveUser($db, $user_id, $user, $admin_id);
            send_response(true, 'User approved successfully! Welcome email sent.', $result);
            break;
            
        case 'decline':
            if (empty($reason)) {
                throw new Exception('Decline reason is required');
            }
            $result = declineUser($db, $user_id, $user, $admin_id, $reason);
            send_response(true, 'User registration declined.', $result);
            break;
            
        default:
            throw new Exception('Invalid action. Use "approve" or "decline".');
    }
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("🚨 USER APPROVAL ERROR: " . $e->getMessage());
    error_log("🚨 ERROR FILE: " . $e->getFile());
    error_log("🚨 ERROR LINE: " . $e->getLine());
    error_log("🚨 ERROR TRACE: " . $e->getTraceAsString());
    
    http_response_code(400);
    send_response(false, 'Error: ' . $e->getMessage());
}

// This should never be reached due to send_response() exit calls
ob_end_clean();
?>