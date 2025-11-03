<?php
/**
 * Admin Index - Redirect to appropriate page
 */
require_once '../includes/db.php';
require_once '../languages/translator.php';

// Include admin auth guard functions (but skip auth check for index)
define('SKIP_ADMIN_AUTH_CHECK', true);
require_once 'includes/admin_auth_guard.php';

// If already logged in, redirect to dashboard
if (is_admin_authenticated()) {
    header('Location: welcome_admin.php');
    exit;
}

// Otherwise redirect to login
header('Location: login.php');
exit;
?>

