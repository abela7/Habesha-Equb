<?php
/**
 * Simple Welcome API - No complex dependencies
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clean any output before JSON
ob_start();
ob_clean();

// JSON headers
header('Content-Type: application/json; charset=utf-8');

try {
    // Include database connection
    require_once __DIR__ . '/../../includes/db.php';
    
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['user_logged_in']) {
        throw new Exception('User not authenticated');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['action'])) {
        throw new Exception('Invalid request data');
    }
    
    $action = trim($data['action']);
    
    // Get database connection
    $db_conn = isset($db) ? $db : $pdo;
    
    // Handle actions
    if ($action === 'update_language') {
        
        if (!isset($data['language'])) {
            throw new Exception('Language parameter required');
        }
        
        $language = trim($data['language']);
        
        if (!in_array($language, ['en', 'am'])) {
            throw new Exception('Invalid language');
        }
        
        // Convert to database format (0 = English, 1 = Amharic)
        $language_preference = ($language === 'am') ? 1 : 0;
        
        // Update database
        $stmt = $db_conn->prepare("UPDATE members SET language_preference = ? WHERE id = ?");
        $result = $stmt->execute([$language_preference, $user_id]);
        
        if (!$result) {
            throw new Exception('Failed to update language');
        }
        
        // Update session
        $_SESSION['user_language'] = $language;
        
        echo json_encode([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => ['language' => $language]
        ]);
        
    } elseif ($action === 'agree_rules') {
        
        // Update rules agreement
        $stmt = $db_conn->prepare("UPDATE members SET rules_agreed = 1 WHERE id = ?");
        $result = $stmt->execute([$user_id]);
        
        if (!$result) {
            throw new Exception('Failed to save rules agreement');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Rules agreement saved',
            'data' => ['redirect' => 'dashboard.php']
        ]);
        
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log("Welcome API Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again.'
        // SECURITY FIX: Debug information removed to prevent information disclosure
    ]);
}
?> 