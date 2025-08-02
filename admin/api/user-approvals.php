<?php
/**
 * HabeshaEqub - User Approval API
 * Handles admin approval/decline actions for member registrations
 */

// SECURITY FIX: Secure error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set up secure error handler
set_error_handler(function($severity, $message, $file, $line) {
    // Log full error details securely
    error_log("PHP Error in user-approvals API: $message in $file on line $line (Severity: $severity)");
    
    // Return generic error message (no system information exposed)
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A system error occurred. Please try again or contact support.'
        // SECURITY FIX: Debug information removed to prevent information disclosure
    ]);
    exit;
});

// Include database and auth with error handling
try {
    require_once '../../includes/db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    require_once '../../includes/email/EmailService.php';
} catch (Exception $e) {
    error_log("Email service not available: " . $e->getMessage());
    // Continue without email service - don't block approval process
}

try {
    require_once '../includes/admin_auth_guard.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Auth system failed: ' . $e->getMessage()]);
    exit;
}

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Debug logging
error_log("User Approval API called - Method: " . $_SERVER['REQUEST_METHOD'] . " - Time: " . date('Y-m-d H:i:s'));

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check admin authentication
$admin_id = get_current_admin_id();
if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin authentication required']);
    exit;
}

// Ensure database connection is available
if (!isset($db)) {
    if (isset($pdo)) {
        $db = $pdo;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
        exit;
    }
}

try {
    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['action']) || !isset($data['user_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $action = sanitize_input($data['action']);
    $user_id = (int)$data['user_id'];
    $reason = isset($data['reason']) ? sanitize_input($data['reason']) : '';
    
    // Validate action
    if (!in_array($action, ['approve', 'decline'])) {
        throw new Exception('Invalid action. Must be "approve" or "decline"');
    }
    
    // Verify user exists and is pending approval
    $user_stmt = $db->prepare("
        SELECT id, member_id, first_name, last_name, email, phone, is_approved, is_active 
        FROM members 
        WHERE id = ?
    ");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['is_approved'] == 1) {
        throw new Exception('User is already approved');
    }
    
    // Process the action
    switch ($action) {
        case 'approve':
            handleUserApproval($db, $user_id, $user, $admin_id);
            break;
            
        case 'decline':
            handleUserDecline($db, $user_id, $user, $admin_id, $reason);
            break;
    }
    
} catch (Exception $e) {
    error_log("User Approval API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
    exit;
}

/**
 * Handle user approval
 */
function handleUserApproval($db, $user_id, $user, $admin_id) {
    try {
        $db->beginTransaction();
        
        // Update user approval status
        $approve_stmt = $db->prepare("
            UPDATE members 
            SET is_approved = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $approve_stmt->execute([$user_id]);
        
        if ($approve_stmt->rowCount() === 0) {
            throw new Exception('Failed to approve user');
        }
        
        // Update device tracking for approved user
        $device_stmt = $db->prepare("
            UPDATE device_tracking 
            SET is_approved = 1, last_seen = CURRENT_TIMESTAMP 
            WHERE email = ?
        ");
        $device_stmt->execute([$user['email']]);
        
        // Send welcome email to approved user
        $email_sent = false;
        $email_error = null;
        
        error_log("Starting email process for user approval - User ID: {$user_id}, Email: {$user['email']}");
        
        if (class_exists('EmailService')) {
            error_log("EmailService class found, attempting to send email...");
            try {
                $emailService = new EmailService($db);
                error_log("EmailService initialized successfully");
                
                // Prepare email variables
                $email_variables = [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'member_id' => $user['member_id'],
                    'email' => $user['email'],
                    'login_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/user/login.php'
                ];
                
                error_log("Email variables prepared: " . json_encode($email_variables));
                
                // Send the welcome email
                $result = $emailService->send(
                    'account_approved',
                    $user['email'],
                    $user['first_name'] . ' ' . $user['last_name'],
                    $email_variables
                );
                
                error_log("Email send result: " . json_encode($result));
                
                if ($result && isset($result['success']) && $result['success']) {
                    $email_sent = true;
                    error_log("Welcome email sent successfully to {$user['email']} (User ID: {$user_id})");
                } else {
                    $email_error = $result['message'] ?? 'Unknown email error';
                    error_log("Email sending returned failure: " . $email_error);
                }
                
            } catch (Exception $e) {
                $email_error = $e->getMessage();
                error_log("Exception during email sending to {$user['email']}: " . $email_error);
                error_log("Exception trace: " . $e->getTraceAsString());
            }
        } else {
            $email_error = "EmailService class not available";
            error_log("EmailService class not found - email cannot be sent");
        }
        
        // Log the approval action (regardless of email status)
        $log_stmt = $db->prepare("
            INSERT INTO notifications (
                notification_id, 
                recipient_type, 
                recipient_id, 
                type, 
                channel, 
                subject, 
                message, 
                language,
                status,
                sent_at,
                sent_by_admin_id,
                notes
            ) VALUES (?, 'member', ?, 'approval', 'email', ?, ?, 'en', ?, NOW(), ?, ?)
        ");
        
        $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $subject = 'Welcome to HabeshaEqub - Account Approved';
        $message = "Congratulations! Your HabeshaEqub account has been approved. You can now log in and start participating in our equb system.";
        $email_status = $email_sent ? 'sent' : 'failed';
        $notes = "User approved by admin ID: {$admin_id}" . ($email_error ? " | Email error: {$email_error}" : " | Email sent successfully");
        
        $log_stmt->execute([
            $notification_id,
            $user_id,
            $subject,
            $message,
            $email_status,
            $admin_id,
            $notes
        ]);
        
        $db->commit();
        
        // Log successful approval
        error_log("Admin ID {$admin_id} approved user ID {$user_id} ({$user['member_id']})");
        
        echo json_encode([
            'success' => true,
            'message' => 'User approved successfully',
            'data' => [
                'user_id' => $user_id,
                'member_id' => $user['member_id'],
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'action' => 'approved',
                'approved_by' => $admin_id,
                'approved_at' => date('Y-m-d H:i:s'),
                'email_sent' => $email_sent,
                'email_status' => $email_sent ? 'Welcome email sent successfully' : 'Welcome email failed to send'
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw new Exception('Database error during approval: ' . $e->getMessage());
    }
}

/**
 * Handle user decline
 */
function handleUserDecline($db, $user_id, $user, $admin_id, $reason) {
    try {
        $db->beginTransaction();
        
        // Update user status to inactive (decline)
        $decline_stmt = $db->prepare("
            UPDATE members 
            SET is_active = 0, updated_at = CURRENT_TIMESTAMP, notes = ? 
            WHERE id = ?
        ");
        $decline_stmt->execute(["DECLINED: {$reason}", $user_id]);
        
        if ($decline_stmt->rowCount() === 0) {
            throw new Exception('Failed to decline user');
        }
        
        // Log the decline action
        $log_stmt = $db->prepare("
            INSERT INTO notifications (
                notification_id, 
                recipient_type, 
                recipient_id, 
                type, 
                channel, 
                subject, 
                message, 
                language,
                status,
                sent_at,
                sent_by_admin_id,
                notes
            ) VALUES (?, 'member', ?, 'general', 'email', ?, ?, 'en', 'sent', NOW(), ?, ?)
        ");
        
        $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $subject = 'HabeshaEqub Account Application Update';
        $message = "Thank you for your interest in HabeshaEqub. Unfortunately, we cannot approve your account at this time. Reason: {$reason}";
        $notes = "User declined by admin ID: {$admin_id}. Reason: {$reason}";
        
        $log_stmt->execute([
            $notification_id,
            $user_id,
            $subject,
            $message,
            $admin_id,
            $notes
        ]);
        
        $db->commit();
        
        // Log successful decline
        error_log("Admin ID {$admin_id} declined user ID {$user_id} ({$user['member_id']}) - Reason: {$reason}");
        
        echo json_encode([
            'success' => true,
            'message' => 'User declined successfully',
            'data' => [
                'user_id' => $user_id,
                'member_id' => $user['member_id'],
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'action' => 'declined',
                'declined_by' => $admin_id,
                'declined_at' => date('Y-m-d H:i:s'),
                'reason' => $reason
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw new Exception('Database error during decline: ' . $e->getMessage());
    }
}

/**
 * Basic input sanitization
 */
function sanitize_input($input) {
    if (is_string($input)) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    return $input;
}
?> 