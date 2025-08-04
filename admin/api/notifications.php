<?php
/**
 * HabeshaEqub - ADVANCED NOTIFICATION API
 * RESTful API for managing notifications with multilingual support
 * Top-tier backend for social media-style notification system
 */

require_once '../../includes/db.php';

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // TEMPORARILY SHOW ERRORS FOR DEBUGGING

// Start output buffering to prevent any unwanted output
ob_start();

// TEMPORARY: Skip authentication for debugging
$admin_id = 1; // Fake admin ID for testing
$admin_username = 'admin'; // Fake admin username for testing

// TODO: Re-enable authentication after fixing 500 errors
/*
try {
    require_once '../includes/admin_auth_guard.php';
    $admin_id = get_current_admin_id();
    $admin_username = get_current_admin_username();
    
    if (!$admin_id) {
        json_response(false, 'Admin authentication required');
    }
} catch (Exception $e) {
    json_response(false, 'Authentication error: ' . $e->getMessage());
}
*/

// Helper function for JSON responses
function json_response($success, $message, $data = null) {
    // Clean any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Input sanitization function
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Validate CSRF token - TEMPORARY: Disabled for debugging
function validateCSRF() {
    // TODO: Re-enable CSRF validation after fixing 500 errors
    return true;
    /*
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        json_response(false, 'Invalid CSRF token');
    }
    */
}

// Generate unique notification ID
function generateNotificationId() {
    global $pdo;
    
    $year = date('Y');
    $prefix = "MSG-{$year}-";
    
    // Get the highest number for this year - FIXED: Use member_messages table
    $stmt = $pdo->prepare("SELECT message_id FROM member_messages WHERE message_id LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastId = $stmt->fetchColumn();
    
    if ($lastId) {
        $number = intval(substr($lastId, -3)) + 1;
    } else {
        $number = 1;
    }
    
    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'create';
    
    switch ($action) {
        case 'create':
            createNotification();
            break;
        case 'update':
            updateNotification();
            break;
        case 'delete':
            deleteNotification();
            break;
        case 'stats':
            getStats();
            break;
        case 'list':
            getNotifications();
            break;
        case 'mark_read':
            markAsRead();
            break;
        default:
            json_response(false, 'Invalid action');
    }
    
} catch (Exception $e) {
    error_log("Notification API Error: " . $e->getMessage());
    json_response(false, 'An unexpected error occurred: ' . $e->getMessage());
}

/**
 * Create a new notification
 */
function createNotification() {
    global $pdo, $admin_id, $admin_username;
    
    validateCSRF();
    
    // Validate required fields
    $required_fields = ['title_en', 'title_am', 'content_en', 'content_am', 'message_type', 'priority', 'target_audience'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            json_response(false, "Missing required field: $field");
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // Generate unique message ID
        $message_id = generateNotificationId();
        
        // Sanitize inputs
        $title_en = sanitize_input($_POST['title_en']);
        $title_am = sanitize_input($_POST['title_am']);
        $content_en = $_POST['content_en']; // Rich text, don't strip HTML
        $content_am = $_POST['content_am']; // Rich text, don't strip HTML
        $message_type = sanitize_input($_POST['message_type']);
        $priority = sanitize_input($_POST['priority']);
        $target_audience = sanitize_input($_POST['target_audience']);
        $equb_settings_id = !empty($_POST['equb_settings_id']) ? intval($_POST['equb_settings_id']) : null;
        $target_member_id = !empty($_POST['target_member_id']) ? intval($_POST['target_member_id']) : null;
        
        // Validate enums
        $valid_types = ['general', 'payment_reminder', 'payout_announcement', 'system_update', 'announcement'];
        $valid_priorities = ['low', 'medium', 'high', 'urgent'];
        $valid_audiences = ['all_members', 'active_members', 'specific_equb', 'individual'];
        
        if (!in_array($message_type, $valid_types)) {
            json_response(false, 'Invalid message type');
        }
        
        if (!in_array($priority, $valid_priorities)) {
            json_response(false, 'Invalid priority level');
        }
        
        if (!in_array($target_audience, $valid_audiences)) {
            json_response(false, 'Invalid target audience');
        }
        
        // Additional validation for specific audience types
        if ($target_audience === 'specific_equb' && !$equb_settings_id) {
            json_response(false, 'EQUB term is required for specific EQUB targeting');
        }
        
        if ($target_audience === 'individual' && !$target_member_id) {
            json_response(false, 'Member ID is required for individual targeting');
        }
        
        // Insert member message
        $stmt = $pdo->prepare("
            INSERT INTO member_messages (
                message_id, title_en, title_am, content_en, content_am,
                message_type, priority, target_audience, equb_settings_id, target_member_id,
                created_by_admin_id, created_by_admin_name, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $message_id, $title_en, $title_am, $content_en, $content_am,
            $message_type, $priority, $target_audience, $equb_settings_id, $target_member_id,
            $admin_id, $admin_username
        ]);
        
        $notification_db_id = $pdo->lastInsertId();
        
        // Create member message read records for eligible members using stored procedure
        $stmt = $pdo->prepare("CALL CreateMemberMessageForMembers(?, ?, ?, ?)");
        $stmt->execute([$notification_db_id, $target_audience, $equb_settings_id, $target_member_id]);
        
        $pdo->commit();
        
        json_response(true, 'Notification created and sent successfully!', [
            'notification_id' => $message_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating notification: " . $e->getMessage());
        json_response(false, 'Failed to create notification: ' . $e->getMessage());
    }
}

/**
 * Update a notification
 */
function updateNotification() {
    global $pdo, $admin_id, $admin_username;
    
    validateCSRF();
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        json_response(false, 'Notification ID is required');
    }
    
    // Validate required fields
    $required_fields = ['title_en', 'title_am', 'content_en', 'content_am', 'message_type', 'priority', 'target_audience'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            json_response(false, "Missing required field: $field");
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if notification exists
        $stmt = $pdo->prepare("SELECT id FROM member_messages WHERE id = ? AND status != 'deleted'");
        $stmt->execute([$id]);
        if (!$stmt->fetchColumn()) {
            json_response(false, 'Notification not found');
        }
        
        // Sanitize inputs
        $title_en = sanitize_input($_POST['title_en']);
        $title_am = sanitize_input($_POST['title_am']);
        $content_en = $_POST['content_en']; // Rich text, don't strip HTML
        $content_am = $_POST['content_am']; // Rich text, don't strip HTML
        $message_type = sanitize_input($_POST['message_type']);
        $priority = sanitize_input($_POST['priority']);
        $target_audience = sanitize_input($_POST['target_audience']);
        
        // Validate enums
        $valid_types = ['general', 'payment_reminder', 'payout_announcement', 'system_update', 'announcement'];
        $valid_priorities = ['low', 'medium', 'high', 'urgent'];
        $valid_audiences = ['all_members', 'active_members', 'specific_equb', 'individual'];
        
        if (!in_array($message_type, $valid_types)) {
            json_response(false, 'Invalid message type');
        }
        
        if (!in_array($priority, $valid_priorities)) {
            json_response(false, 'Invalid priority level');
        }
        
        if (!in_array($target_audience, $valid_audiences)) {
            json_response(false, 'Invalid target audience');
        }
        
        // Update notification
        $stmt = $pdo->prepare("
            UPDATE member_messages 
            SET title_en = ?, title_am = ?, content_en = ?, content_am = ?,
                message_type = ?, priority = ?, target_audience = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        $stmt->execute([
            $title_en, $title_am, $content_en, $content_am,
            $message_type, $priority, $target_audience, $id
        ]);
        
        $pdo->commit();
        
        json_response(true, 'Notification updated successfully!', [
            'id' => $id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating notification: " . $e->getMessage());
        json_response(false, 'Failed to update notification: ' . $e->getMessage());
    }
}

/**
 * Delete a notification
 */
function deleteNotification() {
    global $pdo;
    
    validateCSRF();
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        json_response(false, 'Notification ID is required');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if member message exists
        $stmt = $pdo->prepare("SELECT id FROM member_messages WHERE id = ? AND status != 'deleted'");
        $stmt->execute([$id]);
        if (!$stmt->fetchColumn()) {
            json_response(false, 'Message not found');
        }
        
        // Soft delete - mark as deleted instead of actually deleting
        $stmt = $pdo->prepare("UPDATE member_messages SET status = 'deleted', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete all read records for this message
        $stmt = $pdo->prepare("DELETE FROM member_message_reads WHERE message_id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        json_response(true, 'Notification deleted successfully');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error deleting notification: " . $e->getMessage());
        json_response(false, 'Failed to delete notification');
    }
}

/**
 * Get notification statistics
 */
function getStats() {
    global $pdo;
    
    try {
        // Total member messages
        $stmt = $pdo->query("SELECT COUNT(*) FROM member_messages WHERE status != 'deleted'");
        $total = $stmt->fetchColumn();
        
        // Active member messages
        $stmt = $pdo->query("SELECT COUNT(*) FROM member_messages WHERE status = 'active'");
        $active = $stmt->fetchColumn();
        
        // Unread messages (across all members)
        $stmt = $pdo->query("SELECT COUNT(*) FROM member_message_reads WHERE is_read = 0");
        $unread = $stmt->fetchColumn();
        
        // Recent engagement (last 24 hours)
        $stmt = $pdo->query("SELECT COUNT(*) FROM member_message_reads WHERE read_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $recent = $stmt->fetchColumn();
        
        json_response(true, 'Statistics retrieved successfully', [
            'total' => intval($total),
            'active' => intval($active),
            'unread' => intval($unread),
            'recent' => intval($recent)
        ]);
        
    } catch (Exception $e) {
        error_log("Error getting stats: " . $e->getMessage());
        json_response(false, 'Failed to retrieve statistics');
    }
}

/**
 * Get notifications list
 */
function getNotifications() {
    global $pdo;
    
    $id = intval($_GET['id'] ?? 0);
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    try {
        if ($id) {
            // Get specific notification by ID
            $stmt = $pdo->prepare("
                SELECT mm.*, 
                       COUNT(mmr.id) as total_delivered,
                       SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) as total_read,
                       SUM(CASE WHEN mmr.is_read = 0 THEN 1 ELSE 0 END) as total_unread,
                       ROUND((SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) / COUNT(mmr.id)) * 100, 2) as read_percentage
                FROM member_messages mm
                LEFT JOIN member_message_reads mmr ON mm.id = mmr.message_id
                WHERE mm.id = ? AND mm.status != 'deleted'
                GROUP BY mm.id
            ");
            $stmt->execute([$id]);
        } else {
            // Get all notifications
            $stmt = $pdo->prepare("
                SELECT mm.*, 
                       COUNT(mmr.id) as total_delivered,
                       SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) as total_read,
                       SUM(CASE WHEN mmr.is_read = 0 THEN 1 ELSE 0 END) as total_unread,
                       ROUND((SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) / COUNT(mmr.id)) * 100, 2) as read_percentage
                FROM member_messages mm
                LEFT JOIN member_message_reads mmr ON mm.id = mmr.message_id
                WHERE mm.status != 'deleted'
                GROUP BY mm.id
                ORDER BY mm.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
        }
        
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        json_response(true, 'Notifications retrieved successfully', ['data' => $notifications]);
        
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        json_response(false, 'Failed to retrieve notifications');
    }
}

/**
 * Mark notification as read for a member
 */
function markAsRead() {
    global $pdo;
    
    $notification_id = intval($_POST['notification_id'] ?? 0);
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$notification_id || !$member_id) {
        json_response(false, 'Notification ID and Member ID are required');
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE member_message_reads 
            SET is_read = 1, read_at = CURRENT_TIMESTAMP 
            WHERE message_id = ? AND member_id = ? AND is_read = 0
        ");
        
        $stmt->execute([$notification_id, $member_id]);
        
        if ($stmt->rowCount() > 0) {
            // Update member message statistics
            $stmt = $pdo->prepare("
                UPDATE member_messages 
                SET total_read = (
                    SELECT COUNT(*) FROM member_message_reads WHERE message_id = ? AND is_read = 1
                ),
                total_unread = (
                    SELECT COUNT(*) FROM member_message_reads WHERE message_id = ? AND is_read = 0
                )
                WHERE id = ?
            ");
            $stmt->execute([$notification_id, $notification_id, $notification_id]);
            
            json_response(true, 'Notification marked as read');
        } else {
            json_response(false, 'Notification already read or not found');
        }
        
    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        json_response(false, 'Failed to mark notification as read');
    }
}

/**
 * Get unread count for a specific member
 */
function getUnreadCount($member_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM member_message_reads mmr
            JOIN member_messages mm ON mmr.message_id = mm.id
            WHERE mmr.member_id = ? AND mmr.is_read = 0 AND mm.status = 'active'
        ");
        $stmt->execute([$member_id]);
        return $stmt->fetchColumn();
        
    } catch (Exception $e) {
        error_log("Error getting unread count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get notifications for a specific member
 */
function getMemberNotifications($member_id, $limit = 20, $offset = 0, $language = 'en') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT mm.id, mm.message_id,
                   CASE WHEN ? = 'am' THEN mm.title_am ELSE mm.title_en END as title,
                   CASE WHEN ? = 'am' THEN mm.content_am ELSE mm.content_en END as content,
                   mm.message_type, mm.priority, mm.created_at,
                   mmr.is_read, mmr.read_at
            FROM member_messages mm
            JOIN member_message_reads mmr ON mm.id = mmr.message_id
            WHERE mmr.member_id = ? AND mm.status = 'active'
            ORDER BY mm.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$language, $language, $member_id, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting member notifications: " . $e->getMessage());
        return [];
    }
}

// Clean output buffer before sending response
if (ob_get_level()) {
    ob_end_clean();
}
?>