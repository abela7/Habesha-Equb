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

// Email service temporarily disabled for debugging
// try {
//     require_once '../../includes/email/EmailService.php';
// } catch (Exception $e) {
//     error_log("Email service not available: " . $e->getMessage());
//     // Continue without email service - don't block approval process
// }

// Admin auth temporarily disabled for debugging
// try {
//     require_once '../includes/admin_auth_guard.php';
// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode(['success' => false, 'message' => 'Auth system failed: ' . $e->getMessage()]);
//     exit;
// }

// TEMPORARY: Set fake admin ID for testing
$admin_id = 8; // Using your admin ID from database

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
    // DEBUG: Log start of request processing
    error_log("🔍 APPROVAL API: Starting request processing");
    
    // Get request data
    $input = file_get_contents('php://input');
    error_log("🔍 APPROVAL API: Raw input received: " . $input);
    
    $data = json_decode($input, true);
    error_log("🔍 APPROVAL API: Decoded data: " . print_r($data, true));
    
    if (!$data || !isset($data['action']) || !isset($data['user_id'])) {
        error_log("❌ APPROVAL API: Invalid request data");
        throw new Exception('Invalid request data');
    }
    
    error_log("🔍 APPROVAL API: Valid request data received");
    
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
        error_log("🔍 APPROVAL: Starting handleUserApproval for user_id: {$user_id}");
        
        $db->beginTransaction();
        error_log("🔍 APPROVAL: Transaction started");
        
        // Update user approval status
        $approve_stmt = $db->prepare("
            UPDATE members 
            SET is_approved = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        error_log("🔍 APPROVAL: SQL prepared");
        
        $approve_stmt->execute([$user_id]);
        error_log("🔍 APPROVAL: SQL executed");
        
        if ($approve_stmt->rowCount() === 0) {
            throw new Exception('Failed to approve user - no rows affected');
        }
        
        error_log("🔍 APPROVAL: User approval status updated successfully");
        
        // Device tracking temporarily disabled for debugging  
        // try {
        //     $device_stmt = $db->prepare("UPDATE device_tracking SET is_approved = 1, last_seen = CURRENT_TIMESTAMP WHERE email = ?");
        //     $device_stmt->execute([$user['email']]);
        // } catch (Exception $e) {
        //     error_log("Device tracking update failed: " . $e->getMessage());
        //     // Continue - don't fail approval if device tracking fails
        // }
        
        // Email sending temporarily disabled for debugging
        $email_sent = false;
        $email_error = "Email temporarily disabled for debugging";
        error_log("🔍 APPROVAL: Email sending skipped (debugging mode)");
        
        // Notification logging temporarily disabled for debugging
        // Simple approval log  
        error_log("✅ MINIMAL APPROVAL: Admin ID {$admin_id} approved user ID {$user_id} ({$user['member_id']})");
        
        error_log("🔍 APPROVAL: About to commit transaction");
        $db->commit();
        error_log("🔍 APPROVAL: Transaction committed successfully");
        
        // Log successful approval
        error_log("Admin ID {$admin_id} approved user ID {$user_id} ({$user['member_id']})");
        
        // MINIMAL RESPONSE - No complex data
        error_log("🔍 APPROVAL: Preparing JSON response");
        
        $response = [
            'success' => true,
            'message' => 'User approved successfully (minimal mode)',
            'data' => [
                'user_id' => $user_id,
                'member_id' => $user['member_id'],
                'email_report' => [
                    'email_sent' => false,
                    'email_error' => 'Email disabled for debugging'
                ]
            ]
        ];
        
        error_log("🔍 APPROVAL: About to send JSON response: " . json_encode($response));
        echo json_encode($response);
        error_log("✅ APPROVAL: JSON response sent successfully");
        
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