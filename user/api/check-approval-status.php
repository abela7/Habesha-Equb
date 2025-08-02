<?php
/**
 * HabeshaEqub - Check User Approval Status API
 * Used by waiting-approval.php to auto-refresh approval status
 */

// Start output buffering to catch any unwanted output
ob_start();

// Error handling - prevent HTML error output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Custom error handler for API
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error in check-approval-status: $message in $file on line $line");
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    exit;
});

// Custom exception handler for API
set_exception_handler(function($exception) {
    error_log("PHP Exception in check-approval-status: " . $exception->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    exit;
});

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../includes/db.php';
    
    // Get email from request
    $email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) : null;
    
    if (!$email) {
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid email parameter'
        ]);
        exit;
    }
    
    // Check user approval status
    $stmt = $pdo->prepare("
        SELECT id, is_approved, is_active 
        FROM members 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => false, 
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Return approval status
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => true,
        'approved' => ($user['is_approved'] == 1),
        'active' => ($user['is_active'] == 1),
        'declined' => ($user['is_active'] == 0)
    ]);
    
} catch (Exception $e) {
    error_log("Check approval status error: " . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred'
    ]);
}
?>