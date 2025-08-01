<?php
/**
 * Translation Test File - Use this to debug translation issues
 * Delete this file after testing
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

$admin_id = get_current_admin_id();

// Check admin language preference
try {
    $stmt = $pdo->prepare("SELECT language_preference FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_data = $stmt->fetch();
    
    if ($admin_data) {
        $lang = ($admin_data['language_preference'] == 1) ? 'am' : 'en';
        $_SESSION['app_language'] = $lang;
    } else {
        $_SESSION['app_language'] = 'am';
    }
} catch (Exception $e) {
    $_SESSION['app_language'] = 'am';
}

// Load translator AFTER setting session language
require_once '../languages/translator.php';

echo "<!DOCTYPE html><html><head><title>Translation Test</title></head><body>";
echo "<h1>Translation Test Results</h1>";
echo "<p><strong>Session Language:</strong> " . ($_SESSION['app_language'] ?? 'Not set') . "</p>";
echo "<p><strong>Current Language:</strong> " . getCurrentLanguage() . "</p>";
echo "<p><strong>Admin ID:</strong> " . $admin_id . "</p>";

// Test the specific translations that were failing
echo "<h2>Dashboard Translations:</h2>";
echo "<p><strong>welcome_back:</strong> " . t('dashboard.welcome_back') . "</p>";
echo "<p><strong>welcome_subtitle:</strong> " . t('dashboard.welcome_subtitle') . "</p>";
echo "<p><strong>total_members:</strong> " . t('dashboard.total_members') . "</p>";
echo "<p><strong>management_center:</strong> " . t('dashboard.management_center') . "</p>";

echo "<h2>Test with Username:</h2>";
$admin_username = get_current_admin_username() ?? 'Admin';
echo "<p>" . str_replace('{username}', htmlspecialchars($admin_username), t('dashboard.welcome_back')) . "</p>";

echo "</body></html>";
?>