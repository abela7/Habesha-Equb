<?php
/**
 * Simple test API to diagnose network errors
 */

// Include database connection
require_once '../../includes/db.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Test 1: Basic response
    $response = [
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION
    ];
    
    // Test 2: Session check
    if (isset($_SESSION['admin_id'])) {
        $response['admin_logged_in'] = true;
        $response['admin_id'] = $_SESSION['admin_id'];
    } else {
        $response['admin_logged_in'] = false;
    }
    
    // Test 3: Database check
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM equb_settings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['database_working'] = true;
        $response['equb_count'] = $result['count'];
    } else {
        $response['database_working'] = false;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>