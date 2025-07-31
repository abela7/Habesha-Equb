<?php
/**
 * HabeshaEqub - Update Profile API
 * Handles profile updates for members
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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email already exists for another user
    $email_check_stmt = $db->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
    $email_check_stmt->execute([$email, $user_id]);
    if ($email_check_stmt->fetch()) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Email address is already in use by another account']);
        exit;
    }

    // Update user profile
    $update_stmt = $db->prepare("
        UPDATE members 
        SET first_name = ?, 
            last_name = ?, 
            full_name = ?,
            email = ?, 
            phone = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $full_name = $first_name . ' ' . $last_name;
    $result = $update_stmt->execute([$first_name, $last_name, $full_name, $email, $phone, $user_id]);

    if ($result) {
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully!',
            'data' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone
            ]
        ]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
    }

} catch (PDOException $e) {
    ob_clean();
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    ob_clean();
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}
?>