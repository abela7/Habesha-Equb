<?php
/**
 * SIMPLIFIED NOTIFICATIONS API - NO AUTH FOR DEBUGGING
 */
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple JSON response function
function json_response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

try {
    // Include database only
    require_once '../../includes/db.php';
    
    $action = $_GET['action'] ?? 'stats';
    
    switch ($action) {
        case 'stats':
            // Get simple stats
            $total = $pdo->query("SELECT COUNT(*) FROM member_messages WHERE status != 'deleted'")->fetchColumn();
            $active = $pdo->query("SELECT COUNT(*) FROM member_messages WHERE status = 'active'")->fetchColumn();
            $unread = $pdo->query("SELECT COUNT(*) FROM member_message_reads WHERE is_read = 0")->fetchColumn();
            
            json_response(true, 'Stats retrieved', [
                'total' => (int)$total,
                'active' => (int)$active,
                'unread' => (int)$unread,
                'recent' => 0
            ]);
            break;
            
        case 'list':
            // Get simple list
            $stmt = $pdo->prepare("
                SELECT mm.*, 
                       COUNT(mmr.id) as total_delivered,
                       SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) as total_read,
                       SUM(CASE WHEN mmr.is_read = 0 THEN 1 ELSE 0 END) as total_unread
                FROM member_messages mm
                LEFT JOIN member_message_reads mmr ON mm.id = mmr.message_id
                WHERE mm.status != 'deleted'
                GROUP BY mm.id
                ORDER BY mm.created_at DESC
                LIMIT 50
            ");
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            json_response(true, 'Notifications retrieved', ['data' => $notifications]);
            break;
            
        default:
            json_response(false, 'Invalid action');
    }
    
} catch (Exception $e) {
    json_response(false, 'Error: ' . $e->getMessage());
}
?>