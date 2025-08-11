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

function tableExists(PDO $pdo, string $table): bool {
    try {
        $pdo->query("SELECT 1 FROM `{$table}` LIMIT 1");
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function listNotifications(int $user_id): void {
    global $pdo;
    $results = [];

    // Source A: program_notifications + notification_recipients (in-app schema A)
    if (tableExists($pdo, 'program_notifications') && tableExists($pdo, 'notification_recipients')) {
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
        $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Source B: notifications + notification_reads (alternate schema)
    if (tableExists($pdo, 'notifications')) {
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
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([$user_id, $user_id]);
        $results = array_merge($results, $stmt2->fetchAll(PDO::FETCH_ASSOC));
    }

    // Sort combined results by created_at desc, then sent_at
    usort($results, function($a, $b) {
        $ad = $a['created_at'] ?? '';
        $bd = $b['created_at'] ?? '';
        if ($ad === $bd) return 0;
        return strcmp($bd, $ad);
    });

    // Limit to 100 for UI
    if (count($results) > 100) {
        $results = array_slice($results, 0, 100);
    }

    echo json_encode(['success' => true, 'notifications' => $results]);
}

function markRead(int $user_id): void {
    global $pdo;
    $notification_id = (int)($_POST['notification_id'] ?? $_GET['notification_id'] ?? 0);
    if (!$notification_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'notification_id is required']);
        return;
    }
    $updated = 0;
    if (tableExists($pdo, 'notification_recipients')) {
        $stmt = $pdo->prepare("UPDATE notification_recipients SET read_flag = 1, read_at = NOW() WHERE member_id = ? AND notification_id = ?");
        $stmt->execute([$user_id, $notification_id]);
        $updated += $stmt->rowCount();
    }
    if (tableExists($pdo, 'notification_reads')) {
        // Upsert read state for alternate schema
        $sel = $pdo->prepare("SELECT id FROM notification_reads WHERE notification_id = ? AND member_id = ?");
        $sel->execute([$notification_id, $user_id]);
        if ($sel->fetchColumn()) {
            $up = $pdo->prepare("UPDATE notification_reads SET is_read = 1, read_at = NOW() WHERE notification_id = ? AND member_id = ?");
            $up->execute([$notification_id, $user_id]);
            $updated += $up->rowCount();
        } else {
            $ins = $pdo->prepare("INSERT INTO notification_reads (notification_id, member_id, is_read, read_at, created_at) VALUES (?, ?, 1, NOW(), NOW())");
            $ins->execute([$notification_id, $user_id]);
            $updated += $ins->rowCount();
        }
    }
    echo json_encode(['success' => true, 'updated' => $updated]);
}

function markAllRead(int $user_id): void {
    global $pdo;
    $updated = 0;
    if (tableExists($pdo, 'notification_recipients')) {
        $stmt = $pdo->prepare("UPDATE notification_recipients SET read_flag = 1, read_at = NOW() WHERE member_id = ? AND read_flag = 0");
        $stmt->execute([$user_id]);
        $updated += $stmt->rowCount();
    }
    if (tableExists($pdo, 'notifications') && tableExists($pdo, 'notification_reads')) {
        // Mark all broadcasts + personal messages as read for this member
        $sql = "SELECT n.id
                FROM notifications n
                WHERE (n.recipient_type = 'all_members' OR (n.recipient_type = 'member' AND n.recipient_id = ?))";
        $s = $pdo->prepare($sql);
        $s->execute([$user_id]);
        $ids = $s->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($ids)) {
            // Insert any missing rows then mark read
            $ins = $pdo->prepare("INSERT INTO notification_reads (notification_id, member_id, is_read, read_at, created_at) VALUES (?, ?, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()");
            foreach ($ids as $nid) {
                $ins->execute([$nid, $user_id]);
                $updated += 1;
            }
        }
    }
    echo json_encode(['success' => true, 'updated' => $updated]);
}

function countUnread(int $user_id): void {
    global $pdo;
    $total = 0;
    if (tableExists($pdo, 'notification_recipients') && tableExists($pdo, 'program_notifications')) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS unread
             FROM notification_recipients nr
             INNER JOIN program_notifications n ON n.id = nr.notification_id
             WHERE nr.member_id = ? AND nr.read_flag = 0"
        );
        $stmt->execute([$user_id]);
        $total += (int)$stmt->fetchColumn();
    }
    if (tableExists($pdo, 'notifications')) {
        $sql = "SELECT COUNT(*)
                FROM notifications n
                LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.member_id = ?
                WHERE (n.recipient_type = 'all_members' OR (n.recipient_type='member' AND n.recipient_id = ?))
                  AND COALESCE(nr.is_read, 0) = 0";
        $s = $pdo->prepare($sql);
        $s->execute([$user_id, $user_id]);
        $total += (int)$s->fetchColumn();
    }
    echo json_encode(['success' => true, 'unread' => $total]);
}
