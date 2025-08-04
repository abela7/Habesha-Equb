<?php
/**
 * HabeshaEqub - Joint Membership Management API
 * Professional-grade joint membership functionality for traditional EQUB system
 */

require_once '../../includes/db.php';
require_once '../../includes/equb_payout_calculator.php';
require_once '../../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
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
        case 'add_member_to_joint_group':
            addMemberToJointGroup();
            break;
        case 'remove_member_from_joint_group':
            removeMemberFromJointGroup();
            break;
        case 'calculate_joint_payout':
            calculateJointPayout();
            break;
        case 'get_joint_group_summary':
            getJointGroupSummary();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Joint Membership API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

/**
 * Get existing joint groups for an equb term
 */
function getExistingJointGroups() {
    global $pdo;
    
    $equb_term_id = intval($_POST['equb_term_id'] ?? $_GET['equb_term_id'] ?? 0);
    
    if (!$equb_term_id) {
        echo json_encode(['success' => false, 'message' => 'Equb term ID is required']);
        return;
    }
    
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
    
    // Add member count to each group
    foreach ($joint_groups as &$group) {
        $group['member_count'] = $group['current_member_count'];
    }
    
    echo json_encode([
        'success' => true,
        'joint_groups' => $joint_groups
    ]);
}

/**
 * Get detailed information about a specific joint group
 */
function getJointGroupDetails() {
    global $pdo;
    
    $joint_group_id = $_POST['joint_group_id'] ?? $_GET['joint_group_id'] ?? '';
    
    if (!$joint_group_id) {
        echo json_encode(['success' => false, 'message' => 'Joint group ID is required']);
        return;
    }
    
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
        echo json_encode(['success' => false, 'message' => 'Joint group not found']);
        return;
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
    
    echo json_encode([
        'success' => true,
        'group' => $group,
        'members' => $members
    ]);
}

/**
 * Create a new joint membership group
 */
function createJointGroup() {
    global $pdo, $admin_id;
    
    $equb_settings_id = intval($_POST['equb_settings_id'] ?? 0);
    $group_name = trim($_POST['group_name'] ?? '');
    $total_monthly_payment = floatval($_POST['total_monthly_payment'] ?? 0);
    $payout_position = intval($_POST['payout_position'] ?? 0);
    $payout_split_method = $_POST['payout_split_method'] ?? 'equal';
    
    // Validation
    if (!$equb_settings_id || !$total_monthly_payment || !$payout_position) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be provided']);
        return;
    }
    
    if (!in_array($payout_split_method, ['equal', 'proportional', 'custom'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid payout split method']);
        return;
    }
    
    // Check if position is available
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM members 
        WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1
        UNION ALL
        SELECT COUNT(*) FROM joint_membership_groups 
        WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1
    ");
    $stmt->execute([$equb_settings_id, $payout_position, $equb_settings_id, $payout_position]);
    $existing_count = array_sum($stmt->fetchAll(PDO::FETCH_COLUMN));
    
    if ($existing_count > 0) {
        echo json_encode(['success' => false, 'message' => 'Payout position is already occupied']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Generate joint group ID
        $stmt = $pdo->prepare("SELECT equb_id FROM equb_settings WHERE id = ?");
        $stmt->execute([$equb_settings_id]);
        $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equb_data) {
            throw new Exception('Invalid equb settings ID');
        }
        
        // Generate unique joint group ID: JNT-EQUBID-001
        $stmt = $pdo->prepare("
            SELECT COUNT(*) + 1 as next_number 
            FROM joint_membership_groups 
            WHERE equb_settings_id = ?
        ");
        $stmt->execute([$equb_settings_id]);
        $next_number = str_pad($stmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
        $joint_group_id = "JNT-{$equb_data['equb_id']}-{$next_number}";
        
        // Create joint group
        $stmt = $pdo->prepare("
            INSERT INTO joint_membership_groups (
                joint_group_id, equb_settings_id, group_name, total_monthly_payment,
                payout_position, payout_split_method, member_count
            ) VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([
            $joint_group_id, $equb_settings_id, $group_name, $total_monthly_payment,
            $payout_position, $payout_split_method
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Joint group created successfully',
            'joint_group_id' => $joint_group_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Update an existing joint membership group
 */
function updateJointGroup() {
    global $pdo, $admin_id;
    
    $joint_group_id = trim($_POST['joint_group_id'] ?? '');
    $group_name = trim($_POST['group_name'] ?? '');
    $total_monthly_payment = floatval($_POST['total_monthly_payment'] ?? 0);
    $payout_position = intval($_POST['payout_position'] ?? 0);
    $payout_split_method = $_POST['payout_split_method'] ?? 'equal';
    
    // Validation
    if (!$joint_group_id) {
        echo json_encode(['success' => false, 'message' => 'Joint group ID is required']);
        return;
    }
    
    if (!$total_monthly_payment || !$payout_position) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be provided']);
        return;
    }
    
    if (!in_array($payout_split_method, ['equal', 'proportional', 'custom'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid payout split method']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if group exists
        $stmt = $pdo->prepare("SELECT equb_settings_id FROM joint_membership_groups WHERE joint_group_id = ?");
        $stmt->execute([$joint_group_id]);
        $equb_settings_id = $stmt->fetchColumn();
        
        if (!$equb_settings_id) {
            echo json_encode(['success' => false, 'message' => 'Joint group not found']);
            return;
        }
        
        // Update joint group
        $stmt = $pdo->prepare("
            UPDATE joint_membership_groups 
            SET group_name = ?, total_monthly_payment = ?, payout_position = ?, 
                payout_split_method = ?, updated_at = NOW()
            WHERE joint_group_id = ?
        ");
        
        $stmt->execute([
            $group_name, $total_monthly_payment, $payout_position, 
            $payout_split_method, $joint_group_id
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Joint group updated successfully'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Delete a joint membership group
 */
function deleteJointGroup() {
    global $pdo, $admin_id;
    
    $joint_group_id = trim($_POST['joint_group_id'] ?? '');
    
    if (!$joint_group_id) {
        echo json_encode(['success' => false, 'message' => 'Joint group ID is required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if group has members
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE joint_group_id = ? AND is_active = 1");
        $stmt->execute([$joint_group_id]);
        $member_count = $stmt->fetchColumn();
        
        if ($member_count > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Cannot delete joint group with active members. Remove members first.'
            ]);
            return;
        }
        
        // Delete joint group
        $stmt = $pdo->prepare("UPDATE joint_membership_groups SET is_active = 0 WHERE joint_group_id = ?");
        $result = $stmt->execute([$joint_group_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Joint group deleted successfully'
            ]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Joint group not found or already deleted']);
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Calculate joint membership payout distribution
 */
function calculateJointPayout() {
    global $pdo;
    
    $joint_group_id = $_POST['joint_group_id'] ?? '';
    
    if (!$joint_group_id) {
        echo json_encode(['success' => false, 'message' => 'Joint group ID is required']);
        return;
    }
    
    try {
        // Get joint group info
        $stmt = $pdo->prepare("
            SELECT jmg.*, es.duration_months, es.admin_fee
            FROM joint_membership_groups jmg
            JOIN equb_settings es ON jmg.equb_settings_id = es.id
            WHERE jmg.joint_group_id = ?
        ");
        $stmt->execute([$joint_group_id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$group) {
            echo json_encode(['success' => false, 'message' => 'Joint group not found']);
            return;
        }
        
        // Calculate payout using traditional EQUB logic
        $monthly_payment = $group['total_monthly_payment'];
        $duration_months = $group['duration_months'];
        $admin_fee = $group['admin_fee'];
        
        $gross_payout = $monthly_payment * $duration_months;
        $net_payout = $gross_payout - $admin_fee;
        
        // Get member split details
        $stmt = $pdo->prepare("
            SELECT m.id, m.first_name, m.last_name, m.individual_contribution, 
                   m.joint_position_share, m.primary_joint_member
            FROM members m
            WHERE m.joint_group_id = ? AND m.is_active = 1
        ");
        $stmt->execute([$joint_group_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $joint_split_details = [];
        foreach ($members as $member) {
            $share_percentage = $member['joint_position_share'] * 100;
            $member_net_amount = $net_payout * $member['joint_position_share'];
            
            $joint_split_details[] = [
                'member_name' => $member['first_name'] . ' ' . $member['last_name'],
                'share_percentage' => $share_percentage,
                'net_amount' => $member_net_amount
            ];
        }
        
        echo json_encode([
            'success' => true,
            'gross_payout' => $gross_payout,
            'admin_fee' => $admin_fee,
            'net_payout' => $net_payout,
            'monthly_payment' => $monthly_payment,
            'duration_months' => $duration_months,
            'calculation_method' => 'Traditional EQUB',
            'joint_split_details' => $joint_split_details
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get comprehensive joint group summary
 */
function getJointGroupSummary() {
    global $pdo;
    
    $equb_settings_id = intval($_POST['equb_settings_id'] ?? $_GET['equb_settings_id'] ?? 0);
    
    if (!$equb_settings_id) {
        echo json_encode(['success' => false, 'message' => 'Equb settings ID is required']);
        return;
    }
    
    // Get all joint groups for this equb
    $stmt = $pdo->prepare("
        SELECT 
            jmg.*,
            COUNT(m.id) as current_members,
            GROUP_CONCAT(
                CONCAT(m.first_name, ' ', m.last_name, 
                       CASE WHEN m.primary_joint_member = 1 THEN ' (Primary)' ELSE '' END)
                SEPARATOR ', '
            ) as member_names
        FROM joint_membership_groups jmg
        LEFT JOIN members m ON jmg.joint_group_id = m.joint_group_id AND m.is_active = 1
        WHERE jmg.equb_settings_id = ? AND jmg.is_active = 1
        GROUP BY jmg.id
        ORDER BY jmg.payout_position ASC
    ");
    $stmt->execute([$equb_settings_id]);
    $joint_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate summary statistics
    $total_groups = count($joint_groups);
    $total_joint_members = array_sum(array_column($joint_groups, 'current_members'));
    $total_joint_contribution = array_sum(array_column($joint_groups, 'total_monthly_payment'));
    
    echo json_encode([
        'success' => true,
        'joint_groups' => $joint_groups,
        'summary' => [
            'total_groups' => $total_groups,
            'total_joint_members' => $total_joint_members,
            'total_monthly_contribution' => $total_joint_contribution,
            'average_group_size' => $total_groups > 0 ? round($total_joint_members / $total_groups, 2) : 0
        ]
    ]);
}

/**
 * Helper function to generate unique joint group ID
 */
function generateJointGroupId($equb_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 as next_number 
        FROM joint_membership_groups 
        WHERE joint_group_id LIKE ?
    ");
    $stmt->execute(["JNT-{$equb_id}-%"]);
    $next_number = str_pad($stmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
    
    return "JNT-{$equb_id}-{$next_number}";
}

/**
 * Helper function to validate joint membership data
 */
function validateJointMembershipData($data) {
    $errors = [];
    
    if (empty($data['total_monthly_payment']) || $data['total_monthly_payment'] <= 0) {
        $errors[] = 'Total monthly payment must be greater than 0';
    }
    
    if (empty($data['payout_position']) || $data['payout_position'] <= 0) {
        $errors[] = 'Valid payout position is required';
    }
    
    if (!in_array($data['payout_split_method'], ['equal', 'proportional', 'custom'])) {
        $errors[] = 'Invalid payout split method';
    }
    
    return $errors;
}
?>