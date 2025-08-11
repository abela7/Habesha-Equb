<?php
// Member Notifications API (new, legacy-table compatible)
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
    echo json_encode(['success'=>false,'message'=>'Authentication required']);
    exit;
}

function listNotifications(int $user_id): void {
    global $pdo;
    // Legacy schema: notifications + notification_reads
    $sql = "SELECT 
                n.id AS notification_id,
                n.notification_id AS notification_code,
                n.subject AS title_en,
                n.subject AS title_am,
                n.message AS body_en,
                n.message AS body_am,
                CASE WHEN n.priority IN ('high','urgent') THEN 'high' ELSE 'normal' END AS priority,
                n.created_at, n.sent_at,
                COALESCE(nr.is_read, 0) AS read_flag,
                nr.read_at
            FROM notifications n
            LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.member_id = ?
            WHERE (
                (n.recipient_type = 'member' AND n.recipient_id = ?)
                OR n.recipient_type = 'all_members'
            )
            ORDER BY n.created_at DESC
            LIMIT 100";
    $st = $pdo->prepare($sql);
    $st->execute([$user_id, $user_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'notifications'=>$rows]);
}

function markRead(int $user_id): void {
    global $pdo;
    $notification_id = (int)($_POST['notification_id'] ?? $_GET['notification_id'] ?? 0);
    if (!$notification_id) {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'notification_id is required']);
        return;
    }
    // Upsert read state
    $sel = $pdo->prepare("SELECT id FROM notification_reads WHERE notification_id = ? AND member_id = ?");
    $sel->execute([$notification_id, $user_id]);
    if ($sel->fetchColumn()) {
        $up = $pdo->prepare("UPDATE notification_reads SET is_read = 1, read_at = NOW() WHERE notification_id = ? AND member_id = ?");
        $up->execute([$notification_id, $user_id]);
        echo json_encode(['success'=>true,'updated'=>$up->rowCount()]);
    } else {
        $ins = $pdo->prepare("INSERT INTO notification_reads (notification_id, member_id, is_read, read_at, created_at) VALUES (?, ?, 1, NOW(), NOW())");
        $ins->execute([$notification_id, $user_id]);
        echo json_encode(['success'=>true,'updated'=>$ins->rowCount()]);
    }
}

function markAllRead(int $user_id): void {
    global $pdo;
    // Get all visible notifications for this user
    $s = $pdo->prepare("SELECT n.id
                        FROM notifications n
                        WHERE (n.recipient_type='all_members' OR (n.recipient_type='member' AND n.recipient_id = ?))
                        ORDER BY n.created_at DESC LIMIT 500");
    $s->execute([$user_id]);
    $ids = $s->fetchAll(PDO::FETCH_COLUMN);
    $updated = 0;
    if (!empty($ids)) {
        $ins = $pdo->prepare("INSERT INTO notification_reads (notification_id, member_id, is_read, read_at, created_at) VALUES (?, ?, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()");
        foreach ($ids as $nid) {
            $ins->execute([$nid, $user_id]);
            $updated += 1;
        }
    }
    echo json_encode(['success'=>true,'updated'=>$updated]);
}

function countUnread(int $user_id): void {
    global $pdo;
    $sql = "SELECT COUNT(*)
            FROM notifications n
            LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.member_id = ?
            WHERE (n.recipient_type='all_members' OR (n.recipient_type='member' AND n.recipient_id = ?))
              AND COALESCE(nr.is_read, 0) = 0";
    $st = $pdo->prepare($sql);
    $st->execute([$user_id, $user_id]);
    $count = (int)$st->fetchColumn();
    echo json_encode(['success'=>true,'unread'=>$count]);
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
try {
    switch ($action) {
        case 'list': listNotifications($user_id); break;
        case 'mark_read': markRead($user_id); break;
        case 'mark_all_read': markAllRead($user_id); break;
        case 'count_unread': countUnread($user_id); break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('User Notifications API error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error']);
}

?>


