<?php
/**
 * STEP BY STEP API TEST
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$steps = [];

try {
    $steps[] = "Starting test...";
    
    // Step 1: Include database
    require_once '../../includes/db.php';
    $steps[] = "✅ Database included";
    
    // Step 2: Test database connection
    $stmt = $pdo->query("SELECT COUNT(*) FROM member_messages");
    $count = $stmt->fetchColumn();
    $steps[] = "✅ Database connected, found $count messages";
    
    // Step 3: Include auth guard
    require_once '../includes/admin_auth_guard.php';
    $steps[] = "✅ Auth guard included";
    
    // Step 4: Test auth functions
    $admin_id = get_current_admin_id();
    $admin_username = get_current_admin_username();
    $steps[] = "✅ Auth functions work, Admin ID: $admin_id";
    
    if (!$admin_id) {
        $steps[] = "⚠️ Admin not authenticated";
    }
    
    // Step 5: Test simple queries
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM member_messages WHERE status != 'deleted'");
    $total = $stmt->fetchColumn();
    $steps[] = "✅ Stats query works: $total total messages";
    
    echo json_encode([
        'success' => true,
        'message' => 'All steps completed',
        'steps' => $steps
    ]);
    
} catch (Exception $e) {
    $steps[] = "❌ ERROR: " . $e->getMessage();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'steps' => $steps,
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>