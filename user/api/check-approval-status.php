<?php
/**
 * HabeshaEqub - Check Approval Status API
 * Allows waiting users to check if their approval status has changed
 */

// Skip auth check since this is for users waiting for approval
define('SKIP_AUTH_CHECK', true);

require_once '../../includes/db.php';

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['email'])) {
        throw new Exception('Email is required');
    }
    
    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        throw new Exception('Invalid email format');
    }
    
    // Check user status in database
    $stmt = $db->prepare("
        SELECT id, member_id, first_name, last_name, email, is_approved, is_active, updated_at 
        FROM members 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Return user status
    echo json_encode([
        'success' => true,
        'data' => [
            'user_id' => $user['id'],
            'member_id' => $user['member_id'],
            'is_approved' => (bool)$user['is_approved'],
            'is_active' => (bool)$user['is_active'],
            'status' => $user['is_approved'] ? 'approved' : ($user['is_active'] ? 'pending' : 'declined'),
            'last_updated' => $user['updated_at']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Check approval status error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 