<?php
/**
 * Clear email rate limits for testing
 */

header('Content-Type: application/json');

try {
    require_once '../../includes/db.php';
    
    // Clear all email rate limits
    $stmt = $pdo->prepare("DELETE FROM email_rate_limits WHERE email_type = 'account_approved'");
    $stmt->execute();
    
    $cleared = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Cleared {$cleared} email rate limits for testing",
        'cleared_count' => $cleared
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>