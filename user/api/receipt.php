<?php
// User-side Receipt API: returns/creates public receipt token for a user's own payment
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/db.php';
require_once '../includes/auth_guard.php';

$user_id = get_current_user_id();
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Authentication required']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_receipt_token':
            get_receipt_token($user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('User receipt API error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error']);
}

function get_receipt_token(int $user_id): void {
    global $pdo;
    $payment_id = (int)($_GET['payment_id'] ?? $_POST['payment_id'] ?? 0);
    if (!$payment_id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Payment ID is required']); return; }

    // Ensure the payment belongs to the authenticated user
    $check = $pdo->prepare('SELECT id FROM payments WHERE id = ? AND member_id = ? LIMIT 1');
    $check->execute([$payment_id, $user_id]);
    if (!$check->fetchColumn()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Not allowed']); return; }

    // Ensure mapping table exists
    $pdo->prepare("CREATE TABLE IF NOT EXISTS payment_receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        payment_id INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_payment (payment_id),
        CONSTRAINT fk_receipt_payment_user FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")->execute();

    // Fetch existing or create new token
    $sel = $pdo->prepare('SELECT token FROM payment_receipts WHERE payment_id = ? LIMIT 1');
    $sel->execute([$payment_id]);
    $token = $sel->fetchColumn();
    if (!$token) {
        $token = bin2hex(random_bytes(16));
        $ins = $pdo->prepare('INSERT INTO payment_receipts (payment_id, token) VALUES (?, ?)');
        $ins->execute([$payment_id, $token]);
    }

    echo json_encode(['success'=>true,'token'=>$token,'receipt_url'=>'/receipt.php?rt='.$token]);
}

?>


