<?php
/**
 * HabeshaEqub - Welcome Page API
 * Handles-language preference updates and rules agreement
 */

// Skip auth check for this API as it's part of the auth flow
define('SKIP_AUTH_CHECK', true);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/session_config.php';

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['action'])) {
        throw new Exception('Invalid request data');
    }
    
    $action = sanitize_input($data['action']);
    
    // Verify user exists and is approved
    $user_stmt = $db->prepare("
        SELECT id, member_id, first_name, last_name, is_approved, language_preference, rules_agreed 
        FROM members 
        WHERE id = ? AND is_active = 1
    ");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if (!$user['is_approved']) {
        throw new Exception('Account not approved yet');
    }
    
    switch ($action) {
        case 'update_language':
            handleLanguageUpdate($db, $user_id, $data);
            break;
            
        case 'agree_rules':
            handleRulesAgreement($db, $user_id, $user);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log("Welcome API Error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

/**
 * Handle language preference update
 */
function handleLanguageUpdate($db, $user_id, $data) {
    if (!isset($data['language'])) {
        throw new Exception('Language parameter is required');
    }
    
    $language = sanitize_input($data['language']);
    
    // Validate language
    if (!in_array($language, ['en', 'am'])) {
        throw new Exception('Invalid language. Must be "en" or "am"');
    }
    
    // Convert to database format (0 = English, 1 = Amharic)
    $language_preference = ($language === 'am') ? 1 : 0;
    
    try {
        $db->beginTransaction();
        
        // Update user's language preference
        $stmt = $db->prepare("
            UPDATE members 
            SET language_preference = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$language_preference, $user_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to update language preference');
        }
        
        // Update session language if needed
        $_SESSION['user_language'] = $language;
        
        $db->commit();
        
        // Log successful language update
        error_log("User ID {$user_id} updated language preference to: {$language}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Language preference updated successfully',
            'data' => [
                'language' => $language,
                'language_preference' => $language_preference
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/**
 * Handle rules agreement
 */
function handleRulesAgreement($db, $user_id, $user) {
    // Check if user has already agreed to rules
    if ($user['rules_agreed'] == 1) {
        echo json_encode([
            'success' => true,
            'message' => 'Rules already agreed',
            'data' => ['redirect' => 'dashboard.php']
        ]);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Update rules agreement status
        $stmt = $db->prepare("
            UPDATE members 
            SET rules_agreed = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to update rules agreement');
        }
        
        // Log rules agreement with timestamp
        $log_stmt = $db->prepare("
            INSERT INTO notifications (
                notification_id, 
                recipient_type, 
                recipient_id, 
                type, 
                channel, 
                subject, 
                message, 
                language,
                status,
                sent_at,
                notes
            ) VALUES (?, 'member', ?, 'general', 'email', ?, ?, ?, 'sent', NOW(), ?)
        ");
        
        $notification_id = 'NOT-' . date('Ym') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $subject = 'Welcome to HabeshaEqub - Rules Agreed';
        $message = "Member {$user['first_name']} {$user['last_name']} (ID: {$user['member_id']}) has agreed to the Equb rules.";
        $language = $_SESSION['user_language'] ?? 'en';
        $notes = "Rules agreement completed during welcome onboarding flow";
        
        $log_stmt->execute([
            $notification_id,
            $user_id,
            $subject,
            $message,
            $language,
            $notes
        ]);
        
        $db->commit();
        
        // Log successful rules agreement
        error_log("User ID {$user_id} ({$user['member_id']}) agreed to rules during welcome flow");
        
        echo json_encode([
            'success' => true,
            'message' => 'Rules agreement saved successfully',
            'data' => [
                'rules_agreed' => true,
                'redirect' => 'dashboard.php',
                'welcome_completed' => true
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/**
 * Basic input sanitization
 */
function sanitize_input($input) {
    if (is_string($input)) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    return $input;
}
?> 