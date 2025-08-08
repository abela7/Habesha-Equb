<?php
// Admin Notifications API - Create, list, get, update, delete and helper lookups
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

require_once '../../includes/db.php';
require_once '../includes/admin_auth_guard.php';

$admin_id = get_current_admin_id();
if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$read_only = ['list', 'get', 'search_members', 'get_equb_terms', 'get_csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $read_only)) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
}

try {
    switch ($action) {
        case 'create':
            createNotification($admin_id);
            break;
        case 'list':
            listNotifications();
            break;
        case 'get':
            getNotification();
            break;
        case 'update':
            updateNotification($admin_id);
            break;
        case 'delete':
            deleteNotification();
            break;
        case 'search_members':
            searchMembers();
            break;
        case 'get_equb_terms':
            getEqubTerms();
            break;
        case 'get_csrf_token':
            echo json_encode(['success' => true, 'csrf_token' => generate_csrf_token()]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('Notifications API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function createNotification(int $admin_id): void {
    global $pdo;

    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    $audience_type = $_POST['audience_type'] ?? 'all';
    $equb_settings_id = !empty($_POST['equb_settings_id']) ? (int)$_POST['equb_settings_id'] : null;
    $priority = $_POST['priority'] ?? 'normal';
    $member_ids_raw = $_POST['member_ids'] ?? '';

    if ($title_en === '' || $title_am === '' || $body_en === '' || $body_am === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Both language titles and bodies are required']);
        return;
    }

    if (!in_array($audience_type, ['all','equb','members'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid audience type']);
        return;
    }

    if ($audience_type === 'equb' && !$equb_settings_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'EQUB term is required for this audience']);
        return;
    }

    $member_ids = [];
    if ($audience_type === 'members') {
        if (is_string($member_ids_raw)) {
            $decoded = json_decode($member_ids_raw, true);
            if (is_array($decoded)) {
                $member_ids = array_map('intval', $decoded);
            } else {
                $member_ids = array_filter(array_map('intval', preg_split('/[,\s]+/', $member_ids_raw))); 
            }
        } elseif (is_array($member_ids_raw)) {
            $member_ids = array_map('intval', $member_ids_raw);
        }
        if (empty($member_ids)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Please select at least one member']);
            return;
        }
    }

    $pdo->beginTransaction();
    try {
        $code = 'NTF-' . date('Ymd') . '-' . sprintf('%03d', random_int(100, 999));
        $stmt = $pdo->prepare('SELECT id FROM program_notifications WHERE notification_code = ?');
        $stmt->execute([$code]);
        while ($stmt->fetch()) {
            $code = 'NTF-' . date('Ymd') . '-' . sprintf('%03d', random_int(100, 999));
            $stmt->execute([$code]);
        }

        $insert = $pdo->prepare('INSERT INTO program_notifications (notification_code, created_by_admin_id, audience_type, equb_settings_id, title_en, title_am, body_en, body_am, priority, status, sent_at, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?, NOW(), NOW(), NOW())');
        $insert->execute([$code, $admin_id, $audience_type, $equb_settings_id, $title_en, $title_am, $body_en, $body_am, $priority, 'sent']);
        $notificationId = (int)$pdo->lastInsertId();

        if ($audience_type === 'all') {
            $sql = 'INSERT IGNORE INTO notification_recipients (notification_id, member_id, created_at) SELECT ?, m.id, NOW() FROM members m WHERE m.is_active = 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$notificationId]);
        } elseif ($audience_type === 'equb') {
            $sql = 'INSERT IGNORE INTO notification_recipients (notification_id, member_id, created_at) SELECT ?, m.id, NOW() FROM members m WHERE m.is_active = 1 AND m.equb_settings_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$notificationId, $equb_settings_id]);
        } else {
            $ins = $pdo->prepare('INSERT IGNORE INTO notification_recipients (notification_id, member_id, created_at) VALUES (?, ?, NOW())');
            foreach ($member_ids as $mid) {
                if ($mid > 0) {
                    // ensure member exists and is active/approved & opted-in
                    $chk = $pdo->prepare('SELECT id FROM members WHERE id = ? AND is_active = 1 AND is_approved = 1 AND COALESCE(email_notifications,1) = 1');
                    $chk->execute([$mid]);
                    if ($chk->fetchColumn()) {
                        $ins->execute([$notificationId, $mid]);
                    }
                }
            }
        }

        $pdo->commit();

        // OPTIONAL EMAIL DISPATCH to members who opted-in (email_notifications = 1)
        // Fire-and-forget: run after DB commit; collect simple stats
        $sent = 0; $failed = 0;
        try {
            require_once '../../includes/email/EmailService.php';
            $mailer = new EmailService($pdo);
            // Select recipients with opt-in and language
            $q = $pdo->prepare("SELECT m.id, m.first_name, m.last_name, m.email, m.language_preference
                                 FROM members m
                                 INNER JOIN notification_recipients nr ON nr.member_id = m.id
                                 WHERE nr.notification_id = ?
                                   AND m.is_active = 1
                                   AND COALESCE(m.is_approved,0) = 1
                                   AND COALESCE(m.email_notifications,1) = 1
                                   AND m.email IS NOT NULL AND m.email != ''");
            $q->execute([$notificationId]);
            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                // Always use English subject; choose body by user preference
                $subject = $title_en;
                $isAm = ((int)($row['language_preference'] ?? 0) === 1);
                $body = $isAm ? $body_am : $body_en;
                $vars = [
                    'subject' => $subject,
                    'title' => $subject,
                    'body' => nl2br($body),
                    'app_name' => 'HabeshaEqub'
                ];
                $res = $mailer->send('program_notification', $row['email'], trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')), $vars);
                if (!empty($res['success'])) { $sent++; } else { $failed++; }
            }
        } catch (Throwable $e) {
            error_log('Notification email dispatch error: '.$e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Notification sent', 'notification_id' => $notificationId, 'notification_code' => $code, 'email_result' => ['sent' => $sent, 'failed' => $failed]]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Create Notification Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create notification']);
    }
}

function listNotifications(): void {
    global $pdo;
    $sql = "
        SELECT 
            n.id,
            n.notification_code,
            n.audience_type,
            n.equb_settings_id,
            n.title_en,
            n.title_am,
            n.priority,
            n.status,
            n.sent_at,
            n.created_at,
            COALESCE(a.username, 'System') AS admin_name,
            (SELECT COUNT(*) FROM notification_recipients nr WHERE nr.notification_id = n.id) AS recipients_count
        FROM program_notifications n
        LEFT JOIN admins a ON n.created_by_admin_id = a.id
        ORDER BY n.created_at DESC
        LIMIT 100
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'notifications' => $rows]);
}

function getNotification(): void {
    global $pdo;
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    $stmt = $pdo->prepare('SELECT n.*, a.username AS admin_name FROM program_notifications n LEFT JOIN admins a ON n.created_by_admin_id = a.id WHERE n.id = ?');
    $stmt->execute([$id]);
    $n = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$n) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found']);
        return;
    }
    $rec = $pdo->prepare('SELECT nr.*, m.first_name, m.last_name, m.member_id AS member_code FROM notification_recipients nr LEFT JOIN members m ON nr.member_id = m.id WHERE nr.notification_id = ? ORDER BY m.first_name, m.last_name');
    $rec->execute([$id]);
    $recipients = $rec->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'notification' => $n, 'recipients' => $recipients]);
}

function updateNotification(int $admin_id): void {
    global $pdo;
    $id = (int)($_POST['id'] ?? 0);
    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    if ($title_en === '' || $title_am === '' || $body_en === '' || $body_am === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Both language titles and bodies are required']);
        return;
    }

    $stmt = $pdo->prepare('UPDATE program_notifications SET title_en = ?, title_am = ?, body_en = ?, body_am = ?, priority = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$title_en, $title_am, $body_en, $body_am, $priority, $id]);
    echo json_encode(['success' => true, 'message' => 'Notification updated']);
}

function deleteNotification(): void {
    global $pdo;
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    // CASCADE: recipients table has FK ON DELETE CASCADE to program_notifications
    $stmt = $pdo->prepare('DELETE FROM program_notifications WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        return;
    }
    echo json_encode(['success' => true, 'message' => 'Notification deleted']);
}

function searchMembers(): void {
    global $pdo;
    $q = trim($_GET['q'] ?? '');
    $equb = isset($_GET['equb_settings_id']) ? (int)$_GET['equb_settings_id'] : null;
    $sql = 'SELECT id, first_name, last_name, member_id AS code FROM members WHERE is_active = 1';
    $params = [];
    if ($q !== '') {
        $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR member_id LIKE ?)';
        $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
    }
    if ($equb) {
        $sql .= ' AND equb_settings_id = ?';
        $params[] = $equb;
    }
    $sql .= ' ORDER BY first_name, last_name LIMIT 50';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'members' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function getEqubTerms(): void {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, equb_name, start_date FROM equb_settings ORDER BY start_date DESC');
    $stmt->execute();
    echo json_encode(['success' => true, 'terms' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
