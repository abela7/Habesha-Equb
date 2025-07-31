<?php
/**
 * HabeshaEqub - Change Password API
 * Handles password changes for members
 */

// Start output buffering for clean JSON response
ob_start();

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON response header
header('Content-Type: application/json');

require_once '../../includes/db.php';
require_once '../../languages/translator.php';

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Validate and sanitize input
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }

    // Check password length
    if (strlen($new_password) < 6) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit;
    }

    // Get current user data
    $user_stmt = $db->prepare("SELECT password FROM members WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }

    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $update_stmt = $db->prepare("
        UPDATE members 
        SET password = ?, 
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $result = $update_stmt->execute([$new_password_hash, $user_id]);

    if ($result) {
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Password changed successfully!'
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to change password. Please try again.']);
    }

} catch (PDOException $e) {
    ob_clean();
    error_log("Password change error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    ob_clean();
    error_log("Password change error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}
?>