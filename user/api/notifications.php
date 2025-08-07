<?php
// Member Notifications API
// Provides: list, mark_read, mark_all_read, count_unread

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/db.php';
require_once '../../languages/translator.php';
require_once '../includes/auth_guard.php';

$user_id = get_current_user_id();
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            listNotifications($user_id);
            break;
        case 'mark_read':
            markRead($user_id);
            break;
        case 'mark_all_read':
            markAllRead($user_id);
            break;
        case 'count_unread':
            countUnread($user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('User Notifications API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function listNotifications(int $user_id): void {
    global $pdo;
    $stmt = $pdo->prepare("SELECT 
            n.id AS notification_id,
            n.notification_code,
            n.title_en, n.title_am,
            n.body_en, n.body_am,
            n.priority, n.created_at, n.sent_at,
            nr.read_flag, nr.read_at
        FROM program_notifications n
        INNER JOIN notification_recipients nr ON nr.notification_id = n.id
        WHERE nr.member_id = ?
        ORDER BY n.created_at DESC
        LIMIT 100");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'notifications' => $rows]);
}

function markRead(int $user_id): void {
    global $pdo;
    $notification_id = (int)($_POST['notification_id'] ?? $_GET['notification_id'] ?? 0);
    if (!$notification_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'notification_id is required']);
        return;
    }
    $stmt = $pdo->prepare("UPDATE notification_recipients SET read_flag = 1, read_at = NOW() WHERE member_id = ? AND notification_id = ?");
    $stmt->execute([$user_id, $notification_id]);
    echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
}

function markAllRead(int $user_id): void {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notification_recipients SET read_flag = 1, read_at = NOW() WHERE member_id = ? AND read_flag = 0");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
}

function countUnread(int $user_id): void {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread FROM notification_recipients WHERE member_id = ? AND read_flag = 0");
    $stmt->execute([$user_id]);
    $count = (int)$stmt->fetchColumn();
    echo json_encode(['success' => true, 'unread' => $count]);
}
