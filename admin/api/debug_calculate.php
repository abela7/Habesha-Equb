<?php
/**
 * DEBUG: Minimal test for calculate function
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Check session
    if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
        echo json_encode(['success' => false, 'message' => 'Not logged in', 'session' => $_SESSION]);
        exit;
    }

    // Test database connection
    require_once '../../includes/db.php';
    if (!isset($pdo) || !$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Test basic query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM members");
    $member_count = $stmt->fetch()['count'];

    // Test calculator file exists
    $calculator_path = '../../includes/enhanced_equb_calculator_final.php';
    if (!file_exists($calculator_path)) {
        echo json_encode(['success' => false, 'message' => 'Calculator file not found at: ' . $calculator_path]);
        exit;
    }

    // Test calculator loads
    require_once $calculator_path;
    if (!class_exists('EnhancedEqubCalculator')) {
        echo json_encode(['success' => false, 'message' => 'EnhancedEqubCalculator class not found']);
        exit;
    }

    // Test calculator creation
    $calculator = new EnhancedEqubCalculator($pdo);

    // Get first active member
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM members WHERE is_active = 1 LIMIT 1");
    $test_member = $stmt->fetch();
    
    if (!$test_member) {
        echo json_encode(['success' => false, 'message' => 'No active members found']);
        exit;
    }

    // Test calculation
    $result = $calculator->calculateMemberFriendlyPayout($test_member['id']);

    echo json_encode([
        'success' => true,
        'message' => 'All tests passed',
        'debug' => [
            'session_admin_id' => $_SESSION['admin_id'],
            'member_count' => $member_count,
            'calculator_path' => $calculator_path,
            'calculator_exists' => file_exists($calculator_path),
            'test_member' => $test_member,
            'calculation_result' => $result
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
