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
        case 'get_joint_group_details':
            getJointGroupDetails();
            break;
        case 'create_joint_group':
            createJointGroup();
            break;
        case 'update_joint_group':
            updateJointGroup();
            break;
        case 'delete_joint_group':
            deleteJointGroup();
            break;
        case 'calculate_joint_payout':
            calculateJointPayout();
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
 * Get detailed information about a specific joint group
 */
function getJointGroupDetails() {
    global $pdo;
    
    $joint_group_id = $_POST['joint_group_id'] ?? $_GET['joint_group_id'] ?? '';
    
    if (!$joint_group_id) {
        json_response(false, 'Joint group ID is required');
    }
    
    try {
        // Get group details
        $stmt = $pdo->prepare("
            SELECT jmg.*, es.equb_name, COUNT(m.id) as member_count
            FROM joint_membership_groups jmg
            JOIN equb_settings es ON jmg.equb_settings_id = es.id
            LEFT JOIN members m ON jmg.joint_group_id = m.joint_group_id AND m.is_active = 1
            WHERE jmg.joint_group_id = ?
            GROUP BY jmg.id
        ");
        $stmt->execute([$joint_group_id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$group) {
            json_response(false, 'Joint group not found');
        }
        
        // Get group members
        $stmt = $pdo->prepare("
            SELECT m.*, 
                CASE WHEN m.primary_joint_member = 1 THEN 'Primary' ELSE 'Secondary' END as role
            FROM members m
            WHERE m.joint_group_id = ? AND m.is_active = 1
            ORDER BY m.primary_joint_member DESC, m.created_at ASC
        ");
        $stmt->execute([$joint_group_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        json_response(true, 'Joint group details loaded successfully', [
            'group' => $group,
            'members' => $members
        ]);
        
    } catch (Exception $e) {
        error_log("Error fetching joint group details: " . $e->getMessage());
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

/**
 * Update an existing joint group
 */
function updateJointGroup() {
    global $pdo;
    
    // Get and validate input
    $joint_group_id = $_POST['joint_group_id'] ?? '';
    $group_name = trim($_POST['group_name'] ?? '');
    $total_monthly_payment = floatval($_POST['total_monthly_payment'] ?? 0);
    $payout_split_method = $_POST['payout_split_method'] ?? 'equal';
    
    if (!$joint_group_id || !$total_monthly_payment) {
        json_response(false, 'Joint group ID and total payment are required');
    }
    
    if (!in_array($payout_split_method, ['equal', 'proportional', 'custom'])) {
        json_response(false, 'Invalid payout split method');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update joint group
        $stmt = $pdo->prepare("
            UPDATE joint_membership_groups 
            SET group_name = ?, total_monthly_payment = ?, payout_split_method = ?
            WHERE joint_group_id = ?
        ");
        $stmt->execute([$group_name, $total_monthly_payment, $payout_split_method, $joint_group_id]);
        
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            json_response(false, 'Joint group not found or no changes made');
        }
        
        $pdo->commit();
        
        json_response(true, 'Joint group updated successfully');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating joint group: " . $e->getMessage());
        json_response(false, 'Failed to update joint group');
    }
}

/**
 * Delete a joint group
 */
function deleteJointGroup() {
    global $pdo;
    
    $joint_group_id = $_POST['joint_group_id'] ?? '';
    
    if (!$joint_group_id) {
        json_response(false, 'Joint group ID is required');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if group has members
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM members 
            WHERE joint_group_id = ? AND is_active = 1
        ");
        $stmt->execute([$joint_group_id]);
        $member_count = $stmt->fetchColumn();
        
        if ($member_count > 0) {
            $pdo->rollBack();
            json_response(false, 'Cannot delete joint group with active members');
        }
        
        // Delete the joint group
        $stmt = $pdo->prepare("
            UPDATE joint_membership_groups 
            SET is_active = 0 
            WHERE joint_group_id = ?
        ");
        $stmt->execute([$joint_group_id]);
        
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            json_response(false, 'Joint group not found');
        }
        
        $pdo->commit();
        
        json_response(true, 'Joint group deleted successfully');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error deleting joint group: " . $e->getMessage());
        json_response(false, 'Failed to delete joint group');
    }
}

/**
 * Calculate joint payout for a group
 */
function calculateJointPayout() {
    global $pdo;
    
    $joint_group_id = $_POST['joint_group_id'] ?? '';
    
    if (!$joint_group_id) {
        json_response(false, 'Joint group ID is required');
    }
    
    try {
        // Get group details
        $stmt = $pdo->prepare("
            SELECT jmg.*, es.duration_months, es.admin_fee
            FROM joint_membership_groups jmg
            JOIN equb_settings es ON jmg.equb_settings_id = es.id
            WHERE jmg.joint_group_id = ?
        ");
        $stmt->execute([$joint_group_id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$group) {
            json_response(false, 'Joint group not found');
        }
        
        // Calculate total payout
        $total_contribution = $group['total_monthly_payment'] * $group['duration_months'];
        $admin_fee = $group['admin_fee'] ?? 0;
        $net_payout = $total_contribution - $admin_fee;
        
        // Get member split details
        $stmt = $pdo->prepare("
            SELECT m.first_name, m.last_name, m.individual_contribution, m.joint_position_share
            FROM members m
            WHERE m.joint_group_id = ? AND m.is_active = 1
            ORDER BY m.primary_joint_member DESC
        ");
        $stmt->execute([$joint_group_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate individual payouts
        foreach ($members as &$member) {
            if ($group['payout_split_method'] === 'equal') {
                $member['payout_amount'] = $net_payout / count($members);
            } else {
                $member['payout_amount'] = $net_payout * $member['joint_position_share'];
            }
        }
        
        json_response(true, 'Payout calculated successfully', [
            'group' => $group,
            'total_contribution' => $total_contribution,
            'admin_fee' => $admin_fee,
            'net_payout' => $net_payout,
            'members' => $members
        ]);
        
    } catch (Exception $e) {
        error_log("Error calculating joint payout: " . $e->getMessage());
        json_response(false, 'Failed to calculate payout');
    }
}

?>