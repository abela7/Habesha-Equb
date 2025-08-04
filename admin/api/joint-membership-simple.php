<?php
/**
 * HabeshaEqub - Joint Membership Management API (SIMPLE VERSION)
 * Simplified but functional API for joint membership operations
 */

// Prevent any output before JSON
ob_start();

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../includes/db.php';
    
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Clean any output
    if (ob_get_length()) {
        ob_clean();
    }
    
    /**
     * JSON response helper
     */
    function json_response($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Simple admin authentication check
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        json_response(false, 'Unauthorized access');
    }
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error_line' => $e->getLine(),
        'error_file' => basename($e->getFile())
    ]);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_existing_joint_groups':
            getExistingJointGroups();
            break;
        case 'create_joint_group':
            createJointGroup();
            break;
        default:
            json_response(false, 'Invalid action: ' . $action);
    }
} catch (Exception $e) {
    error_log("Joint Membership API Error: " . $e->getMessage());
    json_response(false, 'An error occurred: ' . $e->getMessage());
}

/**
 * Get existing joint groups for an equb term
 */
function getExistingJointGroups() {
    global $pdo;
    
    $equb_term_id = intval($_POST['equb_term_id'] ?? $_GET['equb_term_id'] ?? 0);
    
    if (!$equb_term_id) {
        json_response(false, 'Equb term ID is required');
    }
    
    try {
        // Get existing joint groups
        $stmt = $pdo->prepare("
            SELECT 
                jmg.*,
                COUNT(m.id) as current_member_count,
                es.max_joint_members_per_group
            FROM joint_membership_groups jmg
            LEFT JOIN members m ON jmg.joint_group_id = m.joint_group_id AND m.is_active = 1
            JOIN equb_settings es ON jmg.equb_settings_id = es.id
            WHERE jmg.equb_settings_id = ? AND jmg.is_active = 1
            GROUP BY jmg.id
            HAVING current_member_count < es.max_joint_members_per_group
            ORDER BY jmg.created_at DESC
        ");
        $stmt->execute([$equb_term_id]);
        $joint_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        json_response(true, 'Joint groups loaded successfully', $joint_groups);
        
    } catch (Exception $e) {
        error_log("Error fetching joint groups: " . $e->getMessage());
        json_response(false, 'Database error occurred');
    }
}

/**
 * Create a new joint group
 */
function createJointGroup() {
    global $pdo, $admin_id;
    
    // Get and validate input
    $equb_settings_id = intval($_POST['equb_settings_id'] ?? 0);
    $group_name = trim($_POST['group_name'] ?? '');
    $total_monthly_payment = floatval($_POST['total_monthly_payment'] ?? 0);
    $member_count = intval($_POST['member_count'] ?? 2);
    $payout_position = intval($_POST['payout_position'] ?? 0);
    $payout_split_method = $_POST['payout_split_method'] ?? 'equal';
    
    // Basic validation
    if (!$equb_settings_id || !$total_monthly_payment || !$payout_position) {
        json_response(false, 'All required fields must be provided');
    }
    
    if (!in_array($payout_split_method, ['equal', 'proportional', 'custom'])) {
        json_response(false, 'Invalid payout split method');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if payout position is available
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM members 
            WHERE equb_settings_id = ? AND payout_position = ?
        ");
        $stmt->execute([$equb_settings_id, $payout_position]);
        
        if ($stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            json_response(false, 'Payout position is already occupied');
        }
        
        // Generate unique joint group ID
        $joint_group_id = 'JNT-' . date('Y') . '-' . str_pad($equb_settings_id, 3, '0', STR_PAD_LEFT) . '-' . sprintf('%03d', rand(1, 999));
        
        // Insert joint group
        $stmt = $pdo->prepare("
            INSERT INTO joint_membership_groups 
            (joint_group_id, equb_settings_id, group_name, total_monthly_payment, 
             member_count, payout_position, payout_split_method, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $joint_group_id, $equb_settings_id, $group_name,
            $total_monthly_payment, $member_count, $payout_position,
            $payout_split_method
        ]);
        
        $pdo->commit();
        
        json_response(true, 'Joint group created successfully', [
            'joint_group_id' => $joint_group_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating joint group: " . $e->getMessage());
        json_response(false, 'Failed to create joint group');
    }
}

?>