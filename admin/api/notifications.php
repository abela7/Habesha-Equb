<?php
// New Notifications API (clean build)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/db.php';
require_once '../includes/admin_auth_guard.php';
require_once '../../languages/translator.php';

$admin_id = get_current_admin_id();
if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

function csrf_ok(): bool {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return $token !== '' && verify_csrf_token($token);
}

function sanitize_bool($v): int { return (isset($v) && ($v==='1' || $v===1 || $v===true || $v==='true')) ? 1 : 0; }

function send_email_copy(PDO $pdo, array $member, string $title_en, string $title_am, string $body_en, string $body_am): bool {
    try {
        require_once '../../includes/email/EmailService.php';
        $mailer = new EmailService($pdo);
        $res = $mailer->sendProgramNotificationToMember($member, $title_en, $title_am, $body_en, $body_am);
        return !empty($res['success']);
    } catch (Throwable $e) {
        error_log('Notif email send error: '.$e->getMessage());
        return false;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            if (!csrf_ok()) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Invalid security token']); break; }
            createNotification($admin_id);
            break;
        case 'list':
            listNotifications();
            break;
        case 'search_members':
            searchMembers();
            break;
        case 'get':
            getNotification();
            break;
        case 'update':
            if (!csrf_ok()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid security token']); break; }
            updateNotification();
            break;
        case 'delete':
            if (!csrf_ok()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid security token']); break; }
            deleteNotification();
            break;
        case 'delete_all':
            if (!csrf_ok()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid security token']); break; }
            deleteAllNotifications();
            break;
        case 'mark_all_read':
            if (!csrf_ok()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid security token']); break; }
            markAllRead();
            break;
        case 'get_csrf_token':
            echo json_encode(['success'=>true,'csrf_token'=>generate_csrf_token()]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('Notifications API error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error']);
}

function listNotifications(): void {
    global $pdo;
    // Use legacy notifications table for simplicity and broad compatibility
    $stmt = $pdo->prepare("SELECT id, notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, email_provider_response FROM notifications ORDER BY created_at DESC LIMIT 200");
    $stmt->execute();
    echo json_encode(['success'=>true,'notifications'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function getNotification(): void {
    global $pdo;
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID required']); return; }
    $st = $pdo->prepare("SELECT id, subject, message, recipient_type, recipient_id, sent_at, created_at FROM notifications WHERE id = ? LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Not found']); return; }
    echo json_encode(['success'=>true,'notification'=>$row]);
}

function updateNotification(): void {
    global $pdo;
    $id = (int)($_POST['id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$id || $subject==='' || $message==='') { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID, subject and message are required']); return; }
    $st = $pdo->prepare("UPDATE notifications SET subject = ?, message = ?, updated_at = NOW() WHERE id = ?");
    $st->execute([$subject, $message, $id]);
    echo json_encode(['success'=>true]);
}

function deleteNotification(): void {
    global $pdo;
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID required']); return; }
    // Cascade to notification_reads via FK
    $st = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    $st->execute([$id]);
    echo json_encode(['success'=>true, 'deleted'=>$st->rowCount()]);
}

function deleteAllNotifications(): void {
    global $pdo;
    $st = $pdo->prepare("DELETE FROM notifications");
    $st->execute();
    echo json_encode(['success'=>true, 'deleted'=>$st->rowCount()]);
}

function markAllRead(): void {
    global $pdo;
    // Mark broadcast for all active members
    $ins1 = $pdo->prepare("INSERT INTO notification_reads (notification_id, member_id, is_read, read_at, created_at)
                           SELECT n.id, m.id, 1, NOW(), NOW()
                           FROM notifications n
                           JOIN members m ON n.recipient_type = 'all_members' AND m.is_active = 1
                           ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()");
    $ins1->execute();
    // Member-specific
    $ins2 = $pdo->prepare("INSERT INTO notification_reads (notification_id, member_id, is_read, read_at, created_at)
                           SELECT n.id, n.recipient_id, 1, NOW(), NOW()
                           FROM notifications n
                           WHERE n.recipient_type = 'member'
                           ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()");
    $ins2->execute();
    echo json_encode(['success'=>true]);
}

function searchMembers(): void {
    global $pdo;
    $q = trim($_GET['q'] ?? '');
    $sql = "SELECT id, first_name, last_name, member_id AS code, email, is_active, is_approved, email_notifications FROM members WHERE is_active=1";
    $params = [];
    if ($q !== '') {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR member_id LIKE ? OR email LIKE ?)";
        $w = "%$q%"; $params = [$w,$w,$w,$w];
    }
    $sql .= " ORDER BY first_name, last_name LIMIT 50";
    $st = $pdo->prepare($sql); $st->execute($params);
    echo json_encode(['success'=>true,'members'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}

function createNotification(int $admin_id): void {
    global $pdo;
    $audience = $_POST['audience'] ?? 'all_members'; // 'all_members' | 'member'
    $member_ids = $_POST['member_ids'] ?? '';
    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    $send_email = sanitize_bool($_POST['send_email'] ?? 0);
    $export_whatsapp = sanitize_bool($_POST['export_whatsapp'] ?? 0);

    if ($title_en==='' || $title_am==='' || $body_en==='' || $body_am==='') {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Both language titles and bodies are required']);
        return;
    }

    // Build recipients
    $targets = [];
    if ($audience === 'member') {
        if (is_string($member_ids)) {
            $decoded = json_decode($member_ids, true);
            if (is_array($decoded)) { $member_ids = $decoded; }
        }
        if (!is_array($member_ids) || empty($member_ids)) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Please choose at least one member']);
            return;
        }
        $member_ids = array_map('intval', $member_ids);
        $in = str_repeat('?,', count($member_ids)-1) . '?';
        $q = $pdo->prepare("SELECT id, first_name, last_name, email, language_preference, is_active, is_approved, email_notifications FROM members WHERE id IN ($in)");
        $q->execute($member_ids);
        $targets = $q->fetchAll(PDO::FETCH_ASSOC);
    }

    $now = date('Y-m-d H:i:s');
    $notification_code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);

    $sent_emails = 0; $failed_emails = 0; $inserted = 0;
    $wa_texts = [];
    $wa_broadcast = [];

    if ($audience === 'all_members') {
        // single broadcast row
        $ins = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?,?,?,?,?,?,?,?, 'sent', ?, ?, ?, ?)");
        $ins->execute([$notification_code, 'all_members', null, 'general', ($send_email? 'both':'email'), $title_en, $body_en, 'en', $now, $now, $now, $admin_id]);
        $inserted = 1;
        if ($send_email) {
            // email to eligible members
            $sel = $pdo->prepare("SELECT id, first_name, last_name, email, language_preference FROM members WHERE is_active=1 AND is_approved=1 AND email_notifications=1 AND email IS NOT NULL AND email<>''");
            $sel->execute();
            while ($m = $sel->fetch(PDO::FETCH_ASSOC)) {
                if (send_email_copy($pdo, $m, $title_en, $title_am, $body_en, $body_am)) { $sent_emails++; } else { $failed_emails++; }
            }
        }
        if ($export_whatsapp) {
            $wa_broadcast = [
                'en' => trim($title_en . "\n\n" . $body_en),
                'am' => trim($title_am . "\n\n" . $body_am),
            ];
        }
    } else { // specific members
        $ins = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?,?,?,?,?,?,?,?, 'sent', ?, ?, ?, ?)");
        foreach ($targets as $m) {
            $ins->execute([$notification_code, 'member', (int)$m['id'], 'general', ($send_email? 'both':'email'), $title_en, $body_en, 'en', $now, $now, $now, $admin_id]);
            $inserted++;
            if ($send_email && (int)$m['is_active']===1 && (int)$m['is_approved']===1 && (int)$m['email_notifications']===1 && !empty($m['email'])) {
                if (send_email_copy($pdo, $m, $title_en, $title_am, $body_en, $body_am)) { $sent_emails++; } else { $failed_emails++; }
            }
            if ($export_whatsapp) {
                $first = trim(($m['first_name'] ?? ''));
                $isAm = (int)($m['language_preference'] ?? 0) === 1;
                if ($isAm) {
                    $txt = trim((($first!==''? ('ውድ ' . $first . ' ሆይ፣\n\n') : '')) . $body_am . "\n\n" . $title_am);
                } else {
                    $txt = trim((($first!==''? ('Dear ' . $first . ',\n\n') : '')) . $body_en . "\n\n" . $title_en);
                }
                $wa_texts[] = [
                    'member_id' => (int)$m['id'],
                    'name' => trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')),
                    'language' => $isAm ? 'am' : 'en',
                    'text' => $txt,
                ];
            }
        }
    }

    $resp = [
        'success'=>true,
        'inserted'=>$inserted,
        'email_result'=>['sent'=>$sent_emails,'failed'=>$failed_emails],
        'notification_code'=>$notification_code,
        'email_preview'=>[
            'title_en'=>$title_en,
            'title_am'=>$title_am,
            'body_en'=>$body_en,
            'body_am'=>$body_am,
        ]
    ];
    if ($export_whatsapp) {
        if (!empty($wa_texts)) { $resp['whatsapp_texts'] = $wa_texts; }
        if (!empty($wa_broadcast)) { $resp['whatsapp_broadcast'] = $wa_broadcast; }
    }
    echo json_encode($resp);
}

?>


