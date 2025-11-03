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

function send_sms_copy(PDO $pdo, array $member, string $title_en, string $title_am, string $body_en, string $body_am): bool {
    try {
        require_once '../../includes/sms/SmsService.php';
        $smsService = new SmsService($pdo);
        $res = $smsService->sendProgramNotificationToMember($member, $title_en, $title_am, $body_en, $body_am);
        return !empty($res['success']);
    } catch (Throwable $e) {
        error_log('Notif SMS send error: '.$e->getMessage());
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
        case 'send_quick_sms':
            if (!csrf_ok()) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Invalid security token']); break; }
            sendQuickSms($admin_id);
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
        case 'get_history':
            getNotificationHistory();
            break;
        case 'get_statistics':
            getNotificationStatistics();
            break;
        case 'get_member_notifications':
            getMemberNotifications();
            break;
        case 'get_member_stats':
            getMemberStats();
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
    $st = $pdo->prepare("SELECT id, notification_id, recipient_type, recipient_id, recipient_email, recipient_phone, type, channel, subject, message, language, status, sent_at, delivered_at, created_at, email_provider_response, sms_provider_response, notes FROM notifications WHERE id = ? LIMIT 1");
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
    $sql = "SELECT id, first_name, last_name, member_id AS code, member_id, email, phone, language_preference, is_active, is_approved, email_notifications FROM members WHERE is_active=1";
    $params = [];
    if ($q !== '') {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR member_id LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $w = "%$q%"; $params = [$w,$w,$w,$w,$w];
        $limit = 50; // Limit search results
    } else {
        $limit = 200; // Show more when viewing all members
    }
    $sql .= " ORDER BY first_name, last_name LIMIT " . (int)$limit;
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
    $send_channel = $_POST['send_channel'] ?? 'email'; // 'email', 'sms', or 'both'
    $export_whatsapp = sanitize_bool($_POST['export_whatsapp'] ?? 0);

    if ($title_en==='' || $title_am==='' || $body_en==='' || $body_am==='') {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Both language titles and bodies are required']);
        return;
    }

    // Validate send_channel
    if (!in_array($send_channel, ['email', 'sms', 'both'])) {
        $send_channel = 'email';
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
        $q = $pdo->prepare("SELECT id, first_name, last_name, email, phone, language_preference, is_active, is_approved, email_notifications, notification_preferences FROM members WHERE id IN ($in)");
        $q->execute($member_ids);
        $targets = $q->fetchAll(PDO::FETCH_ASSOC);
    }

    $now = date('Y-m-d H:i:s');
    $notification_code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);

    $sent_emails = 0; $failed_emails = 0; $sent_sms = 0; $failed_sms = 0; $inserted = 0;
    $wa_texts = [];
    $wa_broadcast = [];

    if ($audience === 'all_members') {
        // single broadcast row
        $ins = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?,?,?,?,?,?,?,?, 'sent', ?, ?, ?, ?)");
        $ins->execute([$notification_code, 'all_members', null, 'general', $send_channel, $title_en, $body_en, 'en', $now, $now, $now, $admin_id]);
        $inserted = 1;
        
        // Send to eligible members based on channel
        if ($send_channel === 'email' || $send_channel === 'both') {
            $sel = $pdo->prepare("SELECT id, first_name, last_name, email, phone, language_preference FROM members WHERE is_active=1 AND is_approved=1 AND email_notifications=1 AND email IS NOT NULL AND email<>''");
            $sel->execute();
            while ($m = $sel->fetch(PDO::FETCH_ASSOC)) {
                if (send_email_copy($pdo, $m, $title_en, $title_am, $body_en, $body_am)) { $sent_emails++; } else { $failed_emails++; }
            }
        }
        
        if ($send_channel === 'sms' || $send_channel === 'both') {
            $sel = $pdo->prepare("SELECT id, first_name, last_name, email, phone, language_preference FROM members WHERE is_active=1 AND is_approved=1 AND sms_notifications=1 AND phone IS NOT NULL AND phone<>''");
            $sel->execute();
            while ($m = $sel->fetch(PDO::FETCH_ASSOC)) {
                if (send_sms_copy($pdo, $m, $title_en, $title_am, $body_en, $body_am)) { $sent_sms++; } else { $failed_sms++; }
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
            $ins->execute([$notification_code, 'member', (int)$m['id'], 'general', $send_channel, $title_en, $body_en, 'en', $now, $now, $now, $admin_id]);
            $inserted++;
            
            // Send email if channel includes email
            if (($send_channel === 'email' || $send_channel === 'both') && (int)$m['is_active']===1 && (int)$m['is_approved']===1 && (int)$m['email_notifications']===1 && !empty($m['email'])) {
                if (send_email_copy($pdo, $m, $title_en, $title_am, $body_en, $body_am)) { $sent_emails++; } else { $failed_emails++; }
            }
            
            // Send SMS if channel includes SMS
            if (($send_channel === 'sms' || $send_channel === 'both') && (int)$m['is_active']===1 && (int)$m['is_approved']===1 && !empty($m['phone'])) {
                if (send_sms_copy($pdo, $m, $title_en, $title_am, $body_en, $body_am)) { $sent_sms++; } else { $failed_sms++; }
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
        'sms_result'=>['sent'=>$sent_sms,'failed'=>$failed_sms],
        'notification_code'=>$notification_code,
        'send_channel'=>$send_channel,
        'preview'=>[
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

function sendQuickSms(int $admin_id): void {
    global $pdo;
    
    $member_id = (int)($_POST['member_id'] ?? 0);
    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Member ID required']);
        return;
    }
    
    if (empty($title_en) || empty($body_en)) {
        echo json_encode(['success' => false, 'message' => 'Title and message required']);
        return;
    }
    
    // Get member details
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, language_preference, is_active, is_approved, sms_notifications, member_id FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }
    
    if (empty($member['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Member has no phone number']);
        return;
    }
    
    if ((int)($member['sms_notifications'] ?? 0) !== 1) {
        echo json_encode(['success' => false, 'message' => 'Member has disabled SMS notifications']);
        return;
    }
    
    // Send SMS
    $sent = 0;
    $failed = 0;
    
    if (send_sms_copy($pdo, $member, $title_en, $title_am, $body_en, $body_am)) {
        $sent = 1;
    } else {
        $failed = 1;
    }
    
    // Create notification record
    $notification_code = 'SMS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $now = date('Y-m-d H:i:s');
    
    $ins = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?, 'member', ?, 'general', 'sms', ?, ?, 'en', 'sent', ?, ?, ?, ?)");
    $ins->execute([$notification_code, $member_id, $title_en, $body_en, $now, $now, $now, $admin_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'SMS sent successfully',
        'sms_result' => ['sent' => $sent, 'failed' => $failed],
        'send_channel' => 'sms',
        'notification_code' => $notification_code,
        'preview' => [
            'title_en' => $title_en,
            'title_am' => $title_am,
            'body_en' => $body_en,
            'body_am' => $body_am,
        ]
    ]);
}

function getNotificationHistory(): void {
    global $pdo;
    
    $channel = $_GET['channel'] ?? ''; // 'email', 'sms', 'both', or empty for all
    $status = $_GET['status'] ?? ''; // 'pending', 'sent', 'delivered', 'failed', 'cancelled', or empty for all
    $recipient_type = $_GET['recipient_type'] ?? '';
    $type = $_GET['type'] ?? '';
    $member_search = trim($_GET['member_search'] ?? '');
    $member_id = (int)($_GET['member_id'] ?? 0);
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = max(1, min(100, (int)($_GET['per_page'] ?? 50)));
    $offset = ($page - 1) * $per_page;
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if ($channel !== '') {
        $where[] = "n.channel = ?";
        $params[] = $channel;
    }
    
    if ($status !== '') {
        $where[] = "n.status = ?";
        $params[] = $status;
    }
    
    if ($recipient_type !== '') {
        $where[] = "n.recipient_type = ?";
        $params[] = $recipient_type;
    }
    
    if ($type !== '') {
        $where[] = "n.type = ?";
        $params[] = $type;
    }
    
    if ($member_id > 0) {
        $where[] = "n.recipient_id = ? AND n.recipient_type = 'member'";
        $params[] = $member_id;
    } else if ($member_search !== '') {
        $where[] = "(m.first_name LIKE ? OR m.last_name LIKE ? OR m.member_id LIKE ? OR m.email LIKE ? OR m.phone LIKE ?)";
        $searchParam = "%$member_search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    if ($date_from !== '') {
        $where[] = "DATE(n.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to !== '') {
        $where[] = "DATE(n.created_at) <= ?";
        $params[] = $date_to;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Get total count (need to join with members for member_search)
    $countSql = "SELECT COUNT(*) as total 
                 FROM notifications n
                 LEFT JOIN members m ON n.recipient_id = m.id AND n.recipient_type = 'member'
                 $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get notifications with member info
    $sql = "SELECT n.*, 
                   m.first_name, m.last_name, 
                   CONCAT(m.first_name, ' ', m.last_name) as member_name,
                   m.member_id as member_code, 
                   m.email as member_email, 
                   m.phone as member_phone,
                   a.username as sent_by_username
            FROM notifications n
            LEFT JOIN members m ON n.recipient_id = m.id AND n.recipient_type = 'member'
            LEFT JOIN admins a ON n.sent_by_admin_id = a.id
            $whereClause
            ORDER BY n.created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmtParams = $params;
    $stmtParams[] = $per_page;
    $stmtParams[] = $offset;
    $stmt->execute($stmtParams);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'total' => $total,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]
    ]);
}

function getNotificationStatistics(): void {
    global $pdo;
    
    $channel = $_GET['channel'] ?? '';
    $recipient_type = $_GET['recipient_type'] ?? '';
    $status = $_GET['status'] ?? '';
    $type = $_GET['type'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $where = [];
    $params = [];
    
    if ($channel !== '') {
        $where[] = "channel = ?";
        $params[] = $channel;
    }
    
    if ($recipient_type !== '') {
        $where[] = "recipient_type = ?";
        $params[] = $recipient_type;
    }
    
    if ($status !== '') {
        $where[] = "status = ?";
        $params[] = $status;
    }
    
    if ($type !== '') {
        $where[] = "type = ?";
        $params[] = $type;
    }
    
    if ($date_from !== '') {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to !== '') {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $date_to;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Get channel counts (email, sms, both)
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN channel = 'email' THEN 1 ELSE 0 END) as email,
        SUM(CASE WHEN channel = 'sms' THEN 1 ELSE 0 END) as sms,
        SUM(CASE WHEN channel = 'both' THEN 1 ELSE 0 END) as both
    FROM notifications
    $whereClause");
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'statistics' => [
            'email' => (int)($stats['email'] ?? 0),
            'sms' => (int)($stats['sms'] ?? 0),
            'both' => (int)($stats['both'] ?? 0)
        ]
    ]);
}

function getMemberNotifications(): void {
    global $pdo;
    
    $member_id = (int)($_GET['member_id'] ?? 0);
    if (!$member_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Member ID required']);
        return;
    }
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = max(1, min(100, (int)($_GET['per_page'] ?? 50)));
    $offset = ($page - 1) * $per_page;
    
    // Get member info
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, member_id, email, phone FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE recipient_id = ? AND recipient_type = 'member'");
    $countStmt->execute([$member_id]);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get notifications
    $stmt = $pdo->prepare("SELECT n.*, a.username as sent_by_username
                           FROM notifications n
                           LEFT JOIN admins a ON n.sent_by_admin_id = a.id
                           WHERE n.recipient_id = ? AND n.recipient_type = 'member'
                           ORDER BY n.created_at DESC
                           LIMIT ? OFFSET ?");
    $stmt->execute([$member_id, $per_page, $offset]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'member' => $member,
        'notifications' => $notifications,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]
    ]);
}

function getMemberStats(): void {
    global $pdo;
    
    $search = trim($_GET['search'] ?? '');
    if (empty($search)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Search term required']);
        return;
    }
    
    // Find member by search term
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, member_id, email, phone 
                           FROM members 
                           WHERE (first_name LIKE ? OR last_name LIKE ? OR member_id LIKE ? OR email LIKE ? OR phone LIKE ?)
                           AND is_active = 1
                           LIMIT 1");
    $searchParam = "%$search%";
    $stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }
    
    // Get notification counts by channel
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_notifications,
        SUM(CASE WHEN channel = 'email' THEN 1 ELSE 0 END) as email_count,
        SUM(CASE WHEN channel = 'sms' THEN 1 ELSE 0 END) as sms_count,
        SUM(CASE WHEN channel = 'both' THEN 1 ELSE 0 END) as both_count
    FROM notifications
    WHERE recipient_id = ? AND recipient_type = 'member'");
    $stmt->execute([$member['id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'member_stats' => [
            'member_id' => $member['id'],
            'member_name' => trim($member['first_name'] . ' ' . $member['last_name']),
            'member_code' => $member['member_id'],
            'total_notifications' => (int)($stats['total_notifications'] ?? 0),
            'email_count' => (int)($stats['email_count'] ?? 0),
            'sms_count' => (int)($stats['sms_count'] ?? 0),
            'both_count' => (int)($stats['both_count'] ?? 0)
        ]
    ]);
}

?>


