<?php
/**
 * HabeshaEqub-User Language Preference Handler
 * Handles user language preferences from database
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/translator.php';

/**
 * Set language based on user preference from database
 * @param int $user_id User ID from session
 * @return bool Success status
 */
function setUserLanguageFromDatabase($user_id) {
    global $pdo, $db;
    
    // Use whichever database connection is available
    $database = isset($db) ? $db : $pdo;
    
    try {
        // Get user's language preference from database
        $stmt = $database->prepare("SELECT language_preference FROM members WHERE id = ? AND is_active = 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // language_preference: 0 = English, 1 = Amharic
            $language = ($result['language_preference'] == 1) ? 'am' : 'en';
            error_log("setUserLanguageFromDatabase: user_id=$user_id, db_preference={$result['language_preference']}, setting_lang=$language");
            return setLanguage($language);
        }
    } catch (PDOException $e) {
        error_log("Error getting user language preference: " . $e->getMessage());
    }
    
    // Default to Amharic if there's an error
    error_log("setUserLanguageFromDatabase: defaulting to 'am' for user_id=$user_id");
    return setLanguage('am');
}

/**
 * Set language based on admin preference from database
 * @param int $admin_id Admin ID from session
 * @return bool Success status
 */
function setAdminLanguageFromDatabase($admin_id) {
    global $pdo;
    
    try {
        // Get admin's language preference from database
        $stmt = $pdo->prepare("SELECT language_preference FROM admins WHERE id = ? AND is_active = 1");
        $stmt->execute([$admin_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // language_preference: 0 = English, 1 = Amharic
            $language = ($result['language_preference'] == 1) ? 'am' : 'en';
            error_log("setAdminLanguageFromDatabase: admin_id=$admin_id, db_preference={$result['language_preference']}, setting_lang=$language");
            $success = setLanguage($language);
            error_log("setAdminLanguageFromDatabase: setLanguage result=" . ($success ? 'SUCCESS' : 'FAILED'));
            return $success;
        } else {
            error_log("setAdminLanguageFromDatabase: No active admin found with id=$admin_id");
        }
    } catch (PDOException $e) {
        error_log("Error getting admin language preference: " . $e->getMessage());
    }
    
    // Default to Amharic if there's an error
    error_log("setAdminLanguageFromDatabase: defaulting to 'am' for admin_id=$admin_id");
    return setLanguage('am');
}

/**
 * Update user's language preference in database
 * @param int $user_id User ID
 * @param string $language Language code ('en' or 'am')
 * @return bool Success status
 */
function updateUserLanguagePreference($user_id, $language) {
    global $pdo;
    
    try {
        $preference = ($language === 'am') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE members SET language_preference = ? WHERE id = ?");
        $stmt->execute([$preference, $user_id]);
        
        // Update session language
        setLanguage($language);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error updating user language preference: " . $e->getMessage());
        return false;
    }
}

/**
 * Update admin's language preference in database
 * @param int $admin_id Admin ID
 * @param string $language Language code ('en' or 'am')
 * @return bool Success status
 */
function updateAdminLanguagePreference($admin_id, $language) {
    global $pdo;
    
    try {
        $preference = ($language === 'am') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE admins SET language_preference = ? WHERE id = ?");
        $stmt->execute([$preference, $admin_id]);
        
        // Update session language
        setLanguage($language);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error updating admin language preference: " . $e->getMessage());
        return false;
    }
}
?>