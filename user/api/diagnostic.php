<?php
/**
 * DIAGNOSTIC SCRIPT - Find the exact issue
 * Visit this page to see what's working/broken
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$response = [
    'success' => false,
    'diagnostics' => [],
    'error' => null
];

try {
    // Test 1: Check if includes work
    $response['diagnostics']['step1_includes'] = 'Testing includes...';
    
    if (!file_exists(__DIR__ . '/../../includes/db.php')) {
        throw new Exception('db.php file not found');
    }
    
    require_once __DIR__ . '/../../includes/db.php';
    $response['diagnostics']['step1_includes'] = '✅ DB include successful';
    
    // Test 2: Check database connection
    $response['diagnostics']['step2_db_connection'] = 'Testing database connection...';
    
    if (!isset($pdo) && !isset($db)) {
        throw new Exception('No database connection variables found');
    }
    
    $database = isset($pdo) ? $pdo : $db;
    $stmt = $database->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if (!$result) {
        throw new Exception('Basic database query failed');
    }
    
    $response['diagnostics']['step2_db_connection'] = '✅ Database connection working';
    
    // Test 3: Check members table structure
    $response['diagnostics']['step3_table_structure'] = 'Checking members table...';
    
    $stmt = $database->query("DESCRIBE members");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $response['diagnostics']['step3_table_structure'] = [
        'columns_found' => $columns,
        'has_password_column' => in_array('password', $columns),
        'has_email_verified' => in_array('email_verified', $columns)
    ];
    
    // Test 4: Check if new tables exist
    $response['diagnostics']['step4_new_tables'] = 'Checking new tables...';
    
    $tables_to_check = ['user_otps', 'email_preferences', 'email_rate_limits'];
    $existing_tables = [];
    
    foreach ($tables_to_check as $table) {
        $stmt = $database->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->fetch() !== false;
        $existing_tables[$table] = $exists ? '✅ Exists' : '❌ Missing';
    }
    
    $response['diagnostics']['step4_new_tables'] = $existing_tables;
    
    // Test 5: Check session
    $response['diagnostics']['step5_session'] = 'Checking session...';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $response['diagnostics']['step5_session'] = '✅ Session working';
    
    // All tests passed
    $response['success'] = true;
    $response['message'] = '🎉 All diagnostic tests passed successfully!';
    
} catch (Exception $e) {
    $response['error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    $response['message'] = '❌ Error found: ' . $e->getMessage();
} catch (Error $e) {
    $response['error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    $response['message'] = '❌ Fatal error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>