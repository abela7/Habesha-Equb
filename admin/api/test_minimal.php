<?php
/**
 * MINIMAL TEST - Exact replication of calculate call
 */

session_start();
header('Content-Type: application/json');

// Exact same auth check
if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection
require_once '../../includes/db.php';

// Get the action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Check if it's calculate action
if ($action !== 'calculate') {
    echo json_encode(['success' => false, 'message' => 'Not calculate action. Action: ' . $action]);
    exit;
}

// Get member_id exactly like payouts.php
$member_id = intval($_POST['member_id'] ?? $_GET['member_id'] ?? 0);

if (!$member_id) {
    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
    exit;
}

// Test basic member query
try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        exit;
    }
    
    // Return success with minimal data
    echo json_encode([
        'success' => true,
        'message' => 'Minimal test successful',
        'member_id' => $member_id,
        'member' => $member,
        'session_admin' => $_SESSION['admin_id'],
        'action' => $action,
        'post_data' => $_POST,
        'get_data' => $_GET
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
