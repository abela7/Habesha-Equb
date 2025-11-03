<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../includes/db.php';
require_once '../includes/admin_auth_guard.php';

header('Content-Type: application/json');

$admin_id = get_current_admin_id();
if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'list':
            listTemplates($pdo);
            break;
        case 'get':
            getTemplate($pdo);
            break;
        case 'create':
            createTemplate($pdo, $admin_id);
            break;
        case 'update':
            updateTemplate($pdo, $admin_id);
            break;
        case 'delete':
            deleteTemplate($pdo, $admin_id);
            break;
        case 'increment_usage':
            incrementUsage($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('SMS Templates API Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function listTemplates(PDO $pdo): void {
    $stmt = $pdo->prepare("SELECT id, template_name, title_en, title_am, body_en, body_am, category, usage_count, is_active, created_at FROM sms_templates ORDER BY usage_count DESC, created_at DESC");
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'templates' => $templates]);
}

function getTemplate(PDO $pdo): void {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM sms_templates WHERE id = ?");
    $stmt->execute([$id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$template) {
        echo json_encode(['success' => false, 'message' => 'Template not found']);
        return;
    }
    
    echo json_encode(['success' => true, 'template' => $template]);
}

function createTemplate(PDO $pdo, int $admin_id): void {
    $template_name = trim($_POST['template_name'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    
    if (empty($template_name) || empty($title_en) || empty($body_en)) {
        echo json_encode(['success' => false, 'message' => 'Template name, English title and body are required']);
        return;
    }
    
    $stmt = $pdo->prepare("INSERT INTO sms_templates (template_name, title_en, title_am, body_en, body_am, category, created_by_admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$template_name, $title_en, $title_am, $body_en, $body_am, $category, $admin_id]);
    
    echo json_encode(['success' => true, 'message' => 'Template created', 'id' => $pdo->lastInsertId()]);
}

function updateTemplate(PDO $pdo, int $admin_id): void {
    $id = (int)($_POST['template_id'] ?? $_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    // Check ownership
    $check = $pdo->prepare("SELECT created_by_admin_id FROM sms_templates WHERE id = ?");
    $check->execute([$id]);
    $template = $check->fetch(PDO::FETCH_ASSOC);
    if (!$template) {
        echo json_encode(['success' => false, 'message' => 'Template not found']);
        return;
    }
    
    $template_name = trim($_POST['template_name'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    
    if (empty($template_name) || empty($title_en) || empty($body_en)) {
        echo json_encode(['success' => false, 'message' => 'Template name, English title and body are required']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE sms_templates SET template_name = ?, title_en = ?, title_am = ?, body_en = ?, body_am = ?, category = ? WHERE id = ?");
    $stmt->execute([$template_name, $title_en, $title_am, $body_en, $body_am, $category, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Template updated']);
}

function deleteTemplate(PDO $pdo, int $admin_id): void {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    // Check ownership
    $check = $pdo->prepare("SELECT created_by_admin_id FROM sms_templates WHERE id = ?");
    $check->execute([$id]);
    $template = $check->fetch(PDO::FETCH_ASSOC);
    if (!$template) {
        echo json_encode(['success' => false, 'message' => 'Template not found']);
        return;
    }
    
    // Soft delete (set is_active = 0) instead of hard delete
    $stmt = $pdo->prepare("UPDATE sms_templates SET is_active = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Template deleted']);
}

function incrementUsage(PDO $pdo): void {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) return;
    
    $stmt = $pdo->prepare("UPDATE sms_templates SET usage_count = usage_count + 1 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}
?>

