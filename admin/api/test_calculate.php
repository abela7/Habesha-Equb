<?php
/**
 * Quick test for calculate function
 */

// Start session and basic includes
session_start();
require_once '../../includes/db.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Test basic connectivity
    echo json_encode([
        'success' => true,
        'message' => 'API is reachable',
        'session_check' => isset($_SESSION['admin_id']),
        'admin_id' => $_SESSION['admin_id'] ?? 'not set',
        'post_data' => $_POST,
        'get_data' => $_GET
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
