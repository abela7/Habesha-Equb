<?php
session_start();

// Set admin session for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 8; // Your admin ID

header('Content-Type: application/json');

echo "Testing list API...\n";

// Test the list action
$_GET['action'] = 'list';

try {
    include 'api/payouts.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
