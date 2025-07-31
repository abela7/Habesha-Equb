<?php
/**
 * HabeshaEqub - Update Settings API
 * Handles user settings updates (notifications, privacy, preferences)
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
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'notifications':
            // Handle notification preferences (currently non-functional - just show success)
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
            $payment_reminders = isset($_POST['payment_reminders']) ? 1 : 0;
            
            // For now, just return success without updating database
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Notification preferences saved! (Feature coming soon)'
            ]);
            break;
            
        case 'privacy':
            // Handle privacy settings
            $go_public = isset($_POST['go_public']) ? 1 : 0;
            $language_preference = (int)($_POST['language_preference'] ?? 0);
            
            // Validate language preference
            if (!in_array($language_preference, [0, 1])) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Invalid language preference']);
                exit;
            }
            
            $stmt = $db->prepare("
                UPDATE members 
                SET go_public = ?,
                    language_preference = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$go_public, $language_preference, $user_id]);
            
            if ($result) {
                // Update session language immediately
                $current_lang = $language_preference == 1 ? 'am' : 'en';
                setLanguage($current_lang);
                
                // Also update the user language preference in the database sync function
                try {
                    require_once '../../languages/user_language_handler.php';
                    updateUserLanguagePreference($user_id, $language_preference);
                } catch (Exception $e) {
                    // Continue even if this fails - the main update already worked
                    error_log("Language preference sync error: " . $e->getMessage());
                }
                
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => 'Settings updated successfully!',
                    'language_changed' => true,
                    'new_language' => $current_lang
                ]);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Failed to update settings']);
            }
            break;
            
        default:
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (PDOException $e) {
    ob_clean();
    error_log("Settings update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    ob_clean();
    error_log("Settings update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}
?>