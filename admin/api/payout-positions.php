<?php
/**
 * HabeshaEqub - Payout Positions Management API
 * Professional API for managing member payout positions
 */

// Prevent any output before JSON
ob_start();

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include database connection
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Only POST requests allowed');
}

// Handle JSON input for save_positions
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = array_merge($_POST, $input);
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_positions':
            getPositions();
            break;
        
        case 'save_positions':
            savePositions();
            break;
            
        case 'factory_reset':
            factoryResetPositions();
            break;
        
        default:
            json_response(false, 'Invalid action');
    }
} catch (Exception $e) {
    error_log("Payout Positions API Error: " . $e->getMessage());
    json_response(false, 'An error occurred: ' . $e->getMessage());
}

function getPositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        // Get EQUB info - using exact schema fields
        $stmt = $pdo->prepare("
            SELECT duration_months, admin_fee, current_members, max_members
            FROM equb_settings 
            WHERE id = ?
        ");
        $stmt->execute([$equb_id]);
        $equb_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb_info) {
            json_response(false, 'EQUB term not found');
        }
        
        // Get members with current positions - using exact schema
        $stmt = $pdo->prepare("
            SELECT 
                m.id, m.member_id, m.first_name, m.last_name, m.email,
                m.monthly_payment, m.payout_position, m.membership_type, 
                m.joint_group_id, m.join_date, m.payout_month,
                jmg.group_name
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            ORDER BY m.payout_position ASC, m.id ASC
        ");
        $stmt->execute([$equb_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add calculated fields
        foreach ($members as &$member) {
            $member['duration_months'] = $equb_info['duration_months'];
            $member['estimated_payout_date'] = calculatePayoutDate($member['payout_position'], $equb_info['duration_months']);
        }
        
        // Get statistics - using actual data
        $stats = [
            'total_members' => count($members),
            'individual_members' => count(array_filter($members, fn($m) => $m['membership_type'] === 'individual')),
            'joint_groups' => count(array_unique(array_filter(array_column($members, 'joint_group_id')))),
            'duration' => $equb_info['duration_months']
        ];
        
        json_response(true, 'Positions loaded successfully', [
            'members' => $members,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        error_log("Error getting positions: " . $e->getMessage());
        json_response(false, 'Database error occurred');
    }
}

function savePositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $positions = $_POST['positions'] ?? [];
    
    if (!$equb_id || !is_array($positions) || empty($positions)) {
        json_response(false, 'Invalid data provided');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update each member's position
        $stmt = $pdo->prepare("
            UPDATE members 
            SET payout_position = ?, updated_at = NOW() 
            WHERE id = ? AND equb_settings_id = ?
        ");
        
        foreach ($positions as $position_data) {
            $member_id = intval($position_data['member_id'] ?? 0);
            $position = intval($position_data['position'] ?? 0);
            
            if ($member_id > 0 && $position > 0) {
                $stmt->execute([$position, $member_id, $equb_id]);
            }
        }
        
        // Update payout months based on new positions
        updatePayoutMonths($equb_id);
        
        $pdo->commit();
        json_response(true, 'Payout positions updated successfully');
        
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error saving positions: " . $e->getMessage());
        json_response(false, 'Failed to save positions');
    }
}

function updatePayoutMonths($equb_id) {
    global $pdo;
    
    // Get EQUB start date
    $stmt = $pdo->prepare("SELECT start_date FROM equb_settings WHERE id = ?");
    $stmt->execute([$equb_id]);
    $start_date = $stmt->fetchColumn();
    
    if (!$start_date) return;
    
    // Update payout months for all members
    $stmt = $pdo->prepare("
        UPDATE members 
        SET payout_month = DATE_ADD(?, INTERVAL (payout_position - 1) MONTH)
        WHERE equb_settings_id = ? AND is_active = 1
    ");
    $stmt->execute([$start_date, $equb_id]);
}

function calculatePayoutDate($position, $duration_months) {
    if (!$position || $position > $duration_months) {
        return 'TBD';
    }
    
    // For display purposes, assume start date is current month
    $start_date = new DateTime();
    $start_date->modify('first day of this month');
    $payout_date = clone $start_date;
    $payout_date->modify('+' . ($position - 1) . ' months');
    
    return $payout_date->format('M Y');
}

/**
 * Factory reset - Clear all payout positions (set to 0)
 */
function factoryResetPositions() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Reset all payout positions to 0 and clear payout months
        $stmt = $pdo->prepare("
            UPDATE members 
            SET payout_position = 0, payout_month = NULL
            WHERE equb_settings_id = ? AND is_active = 1
        ");
        $stmt->execute([$equb_id]);
        
        $affected_rows = $stmt->rowCount();
        
        $pdo->commit();
        
        json_response(true, "Factory reset completed successfully. {$affected_rows} members' positions have been cleared.", [
            'affected_members' => $affected_rows
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error during factory reset: " . $e->getMessage());
        json_response(false, 'Database error during factory reset');
    }
}
?>