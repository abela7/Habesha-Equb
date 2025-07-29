<?php
/**
 * Quick Admin Session Debug - DELETE AFTER FIXING
 */

session_start();

echo "<h2>🔍 Admin Session Debug</h2>";

echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Required Variables Check:</h3>";
echo "admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'NOT SET') . "<br>";
echo "admin_logged_in: " . (isset($_SESSION['admin_logged_in']) ? ($_SESSION['admin_logged_in'] ? 'TRUE' : 'FALSE') : 'NOT SET') . "<br>";
echo "login_time: " . (isset($_SESSION['login_time']) ? $_SESSION['login_time'] : 'NOT SET') . "<br>";

echo "<h3>Auth Function Test:</h3>";
require_once 'includes/admin_auth_guard.php';

echo "is_admin_authenticated(): " . (is_admin_authenticated() ? 'TRUE' : 'FALSE') . "<br>";

if (is_admin_authenticated()) {
    echo "<h3 style='color: green;'>✅ Authentication PASSED - Should access dashboard</h3>";
} else {
    echo "<h3 style='color: red;'>❌ Authentication FAILED - Will redirect to login</h3>";
}

echo "<p><a href='dashboard.php'>Test Dashboard Access</a></p>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?> 