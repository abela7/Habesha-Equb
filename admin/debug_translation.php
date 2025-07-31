<?php
/**
 * Debug script for admin translation issues
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once '../languages/user_language_handler.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

echo "<h1>Debug Admin Translation</h1>";

echo "<h2>Session Information</h2>";
echo "Admin ID: " . $admin_id . "<br>";
echo "Admin Username: " . $admin_username . "<br>";
echo "Session app_language: " . ($_SESSION['app_language'] ?? 'not set') . "<br>";

echo "<h2>Current Language</h2>";
$currentLang = getCurrentLanguage();
echo "Current Language: " . $currentLang . "<br>";

echo "<h2>Database Admin Language Preference</h2>";
try {
    $stmt = $pdo->prepare("SELECT id, username, language_preference FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "Admin DB Record: <pre>" . print_r($admin, true) . "</pre>";
        $expectedLang = ($admin['language_preference'] == 1) ? 'am' : 'en';
        echo "Expected language based on DB: " . $expectedLang . "<br>";
    } else {
        echo "No admin record found!<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

echo "<h2>Translation Tests</h2>";
echo "t('dashboard.welcome_back'): " . t('dashboard.welcome_back') . "<br>";
echo "t('dashboard.welcome_subtitle'): " . t('dashboard.welcome_subtitle') . "<br>";
echo "t('navigation.dashboard'): " . t('navigation.dashboard') . "<br>";

echo "<h2>Translation with Parameters</h2>";
$result = t('dashboard.welcome_back', ['username' => 'TestAdmin']);
echo "t('dashboard.welcome_back', ['username' => 'TestAdmin']): " . $result . "<br>";

echo "<h2>Manual Language Setting Test</h2>";
echo "Setting language to 'en'...<br>";
setLanguage('en');
echo "Current language after setting to 'en': " . getCurrentLanguage() . "<br>";
echo "t('dashboard.welcome_back'): " . t('dashboard.welcome_back') . "<br>";
echo "t('dashboard.welcome_subtitle'): " . t('dashboard.welcome_subtitle') . "<br>";

echo "<br>Setting language back to admin preference...<br>";
setAdminLanguageFromDatabase($admin_id);
echo "Current language after admin preference: " . getCurrentLanguage() . "<br>";
?>