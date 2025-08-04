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
        // Get EQUB info with start date and payout day
        $stmt = $pdo->prepare("
            SELECT duration_months, admin_fee, current_members, max_members,
                   start_date, payout_day
            FROM equb_settings 
            WHERE id = ?
        ");
        $stmt->execute([$equb_id]);
        $equb_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb_info) {
            json_response(false, 'EQUB term not found');
        }
        
        // Get CORRECT positions - joint groups as single entities
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN m.membership_type = 'joint' THEN CONCAT('joint_', m.joint_group_id)
                    ELSE CONCAT('individual_', m.id)
                END as position_key,
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.payout_position
                    ELSE m.payout_position
                END as actual_payout_position,
                m.membership_type,
                m.joint_group_id,
                CASE 
                    WHEN m.membership_type = 'joint' THEN COALESCE(jmg.group_name, 'Joint Group')
                    ELSE CONCAT(m.first_name, ' ', m.last_name)
                END as display_name,
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                    ELSE m.monthly_payment
                END as position_payment,
                CASE 
                    WHEN m.membership_type = 'joint' THEN GROUP_CONCAT(CONCAT(m.first_name, ' ', m.last_name) ORDER BY m.primary_joint_member DESC SEPARATOR ' & ')
                    ELSE CONCAT(m.first_name, ' ', m.last_name)
                END as member_names,
                CASE 
                    WHEN m.membership_type = 'joint' THEN COUNT(m.id)
                    ELSE 1
                END as member_count,
                MIN(m.join_date) as join_date,
                MIN(m.id) as primary_id
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            GROUP BY 
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.joint_group_id
                    ELSE m.id
                END
            ORDER BY 
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.payout_position
                    ELSE m.payout_position
                END ASC, MIN(m.id) ASC
        ");
        $stmt->execute([$equb_id]);
        $position_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // CORRECT: Convert to position-based structure (joint groups = 1 position each)
        $positions = [];
        $individual_positions = 0;
        $joint_positions = 0;
        $total_people_count = 0;
        
        foreach ($position_data as $position) {
            $position['duration_months'] = $equb_info['duration_months'];
            $position['estimated_payout_date'] = calculatePayoutDate(
                $position['actual_payout_position'], 
                $equb_info['duration_months'],
                $equb_info['start_date'],
                $equb_info['payout_day']
            );
            
            // Convert to position-based structure for frontend compatibility
            $position_entry = [
                'id' => $position['primary_id'],
                'member_id' => $position['position_key'],
                'first_name' => $position['display_name'],
                'last_name' => ($position['membership_type'] === 'joint' ? '(Joint Group)' : ''),
                'email' => '',
                'monthly_payment' => $position['position_payment'],
                'payout_position' => $position['actual_payout_position'],
                'membership_type' => $position['membership_type'],
                'joint_group_id' => $position['joint_group_id'],
                'member_names' => $position['member_names'],
                'member_count' => $position['member_count'],
                'duration_months' => $equb_info['duration_months'],
                'estimated_payout_date' => $position['estimated_payout_date']
            ];
            
            $positions[] = $position_entry;
            
            // CORRECT counting: Each entry = 1 position (whether individual or joint)
            if ($position['membership_type'] === 'joint') {
                $joint_positions++;
                $total_people_count += $position['member_count']; // People in this position
            } else {
                $individual_positions++;
                $total_people_count++; // One person in this position
            }
        }
        
        // CORRECT statistics - positions vs people
        $stats = [
            'total_positions' => count($position_data), // Number of payout positions
            'total_people' => $total_people_count,      // Number of actual people
            'individual_positions' => $individual_positions,
            'joint_positions' => $joint_positions,
            'duration' => $equb_info['duration_months']
        ];
        
        json_response(true, 'Positions loaded successfully', [
            'members' => $positions,  // Actually positions, not individual members
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

function calculatePayoutDate($position, $duration_months, $equb_start_date = null, $payout_day = 5) {
    if (!$position || $position > $duration_months) {
        return 'TBD';
    }
    
    // Use actual EQUB start date if provided
    if ($equb_start_date) {
        $start_date = new DateTime($equb_start_date);
    } else {
        // Fallback to current month for backward compatibility
        $start_date = new DateTime();
        $start_date->modify('first day of this month');
    }
    
    // Calculate payout date: start_date + (position-1) months + payout_day
    $payout_date = clone $start_date;
    $payout_date->modify('+' . ($position - 1) . ' months');
    $payout_date->setDate($payout_date->format('Y'), $payout_date->format('n'), $payout_day);
    
    return $payout_date->format('M d, Y');
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