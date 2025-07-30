<?php
/**
 * HabeshaEqub - Language Switching Endpoint
 * Handle language changes via GET or POST and sync with database
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the translator and database
require_once 'translator.php';
require_once '../includes/db.php';

/**
 * Update user language preference in database
 */
function updateUserLanguagePreference($language) {
    global $db;
    
    // Only update if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['user_logged_in']) {
        return false;
    }
    
    try {
        // Convert language to database format (0 = English, 1 = Amharic)
        $language_preference = ($language === 'am') ? 1 : 0;
        
        $stmt = $db->prepare("
            UPDATE members 
            SET language_preference = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND is_active = 1
        ");
        $result = $stmt->execute([$language_preference, $_SESSION['user_id']]);
        
        if ($result && $stmt->rowCount() > 0) {
            error_log("User ID {$_SESSION['user_id']} language preference updated to: {$language}");
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Error updating user language preference: " . $e->getMessage());
        return false;
    }
}

// Handle both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request (used by login page and navigation)
    $language = $_GET['lang'] ?? '';
    $redirect = $_GET['redirect'] ?? '../user/login.php';
    
    // Validate language
    if (in_array($language, ['en', 'am'])) {
        // Set the language using the translator (updates session)
        setLanguage($language);
        
        // Also update database if user is logged in
        updateUserLanguagePreference($language);
    }
    
    // Redirect back to the page
    header('Location: ' . $redirect);
    exit;
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request (AJAX)
    header('Content-Type: application/json');
    
    // Get POST data
    $language = $_POST['language'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Basic CSRF protection for AJAX requests
    if (empty($csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'CSRF token required']);
        exit;
    }
    
    // Validate language
    if (!in_array($language, ['en', 'am'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid language']);
        exit;
    }
    
    // Set the language using the existing translator (updates session)
    $session_updated = setLanguage($language);
    
    // Also update database if user is logged in
    $database_updated = updateUserLanguagePreference($language);
    
    if ($session_updated) {
        $response = [
            'success' => true, 
            'message' => 'Language changed successfully',
            'language' => $language,
            'database_updated' => $database_updated
        ];
        
        if (!$database_updated && isset($_SESSION['user_id'])) {
            $response['message'] = 'Language changed in session, but database update failed';
            error_log("Language session updated but database update failed for user {$_SESSION['user_id']}");
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to change language']);
    }
} else {
    // Method not allowed
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 