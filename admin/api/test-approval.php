<?php
/**
 * Test file to debug admin API issues
 */

// Basic headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Test basic PHP functionality
try {
    // Test database connection
    require_once '../../includes/db.php';
    
    // Test admin auth
    require_once '../includes/admin_auth_guard.php';
    
    $admin_id = get_current_admin_id();
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working correctly',
        'data' => [
            'admin_id' => $admin_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?> 