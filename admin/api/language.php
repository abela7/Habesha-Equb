<?php
/**
 * HabeshaEqub - Admin Language Switching API
 * Handles admin language preference updates
 */

require_once '../../includes/db.php';
require_once '../../languages/user_language_handler.php';

// Set JSON header
header('Content-Type: application/json');

// Secure admin authentication check
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'switch_language':
            $language = $_POST['language'] ?? '';
            
            // Validate language
            if (!in_array($language, ['en', 'am'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid language selection']);
                exit;
            }
            
            // Update admin language preference in database
            $result = updateAdminLanguagePreference($admin_id, $language);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Language updated successfully',
                    'language' => $language
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update language preference']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Language API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}
?>