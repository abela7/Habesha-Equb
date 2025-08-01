<?php
/**
 * Language Switching API
 * Handle AJAX requests for changing language
 */

// Include required files
require_once '../../includes/db.php';
require_once '../../languages/translator.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is authenticated admin
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/**
 * 🔧 CRITICAL FIX: Update admin language preference in database
 * This function ensures that language changes persist across sessions
 */
function updateAdminLanguagePreference($language, $admin_id, $pdo) {
    try {
        // Convert language to database format (0 = English, 1 = Amharic)
        $language_preference = ($language === 'am') ? 1 : 0;
        
        $stmt = $pdo->prepare("
            UPDATE admins 
            SET language_preference = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $result = $stmt->execute([$language_preference, $admin_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            error_log("🔧 LANGUAGE FIX: Admin ID {$admin_id} language preference updated to: {$language} (DB value: {$language_preference})");
            return true;
        } else {
            error_log("⚠️ LANGUAGE WARNING: Admin ID {$admin_id} language update executed but no rows affected");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("❌ LANGUAGE ERROR: Failed to update admin language preference - " . $e->getMessage());
        return false;
    }
}

// Handle POST request for language switching
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    $language = sanitize_input($_POST['language'] ?? '');
    
    if (empty($language)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Language is required']);
        exit;
    }
    
    // Set the language in session
    $session_updated = setLanguage($language);
    
    // 🔧 CRITICAL FIX: Also update the database!
    $admin_id = $_SESSION['admin_id'] ?? null;
    $database_updated = false;
    
    if ($admin_id) {
        $database_updated = updateAdminLanguagePreference($language, $admin_id, $pdo);
    }
    
    if ($session_updated) {
        $response = [
            'success' => true,
            'message' => 'Language changed successfully',
            'current_language' => getCurrentLanguage(),
            'available_languages' => getAvailableLanguages(),
            'session_updated' => true,
            'database_updated' => $database_updated,
            'admin_id' => $admin_id
        ];
        
        // Add warning if database update failed
        if (!$database_updated && $admin_id) {
            $response['message'] = 'Language changed in session, but database update failed';
            $response['warning'] = 'Language preference may not persist after logout';
            error_log("⚠️ ADMIN LANGUAGE: Session updated but database update failed for admin {$admin_id}");
        } elseif (!$admin_id) {
            $response['warning'] = 'Admin ID not found in session';
        }
        
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid language']);
    }
    exit;
}

// Handle GET request for current language info
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'current_language' => getCurrentLanguage(),
        'available_languages' => getAvailableLanguages()
    ]);
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?> 