<?php
/**
 * HabeshaEqub - Payouts API
 * Handle all payout-related CRUD operations and management
 */

require_once '../../includes/db.php';
require_once '../../includes/enhanced_equb_calculator.php';

// Set JSON header
header('Content-Type: application/json');

/**
 * MASTER-LEVEL HELPER FUNCTION
 * Synchronize has_received_payout flag with actual payouts table
 * This ensures data integrity across the entire system
 */
function syncMemberPayoutFlag($member_id) {
    global $pdo;
    
    try {
        // Check if member has any completed payouts
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed_payouts 
            FROM payouts 
            WHERE member_id = ? AND status = 'completed'
        ");
        $stmt->execute([$member_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $has_received_payout = $result['completed_payouts'] > 0 ? 1 : 0;
        
        // Update member's flag to match reality
        $stmt = $pdo->prepare("UPDATE members SET has_received_payout = ? WHERE id = ?");
        $stmt->execute([$has_received_payout, $member_id]);
        
        error_log("PAYOUT SYNC: Member $member_id has_received_payout updated to $has_received_payout (based on {$result['completed_payouts']} completed payouts)");
        
        return true;
    } catch (PDOException $e) {
        error_log("PAYOUT SYNC ERROR: " . $e->getMessage());
        return false;
    }
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// CSRF token verification for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'list') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ]);
        exit;
    }
}

try {
    switch ($action) {
        case 'add':
            addPayout();
            break;
        case 'get':
            getPayout();
            break;
        case 'update':
            updatePayout();
            break;
        case 'delete':
            deletePayout();
            break;
        case 'process':
            processPayout();
            break;
        case 'list':
            listPayouts();
            break;
        case 'get_csrf_token':
            echo json_encode([
                'success' => true, 
                'csrf_token' => generate_csrf_token()
            ]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Payouts API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred processing your request']);
}

/**
 * Create individual payouts for all members in a joint group
 * This ensures each member gets their own payout record and receipt
 */
function createJointGroupPayouts($joint_group_id, $total_group_amount, $scheduled_date, $actual_payout_date, $status, $payout_method, $admin_fee, $net_amount, $processed_by_admin_id, $payout_notes) {
    global $pdo;
    
    try {
        // Get enhanced calculator
        $calculator = getEnhancedEqubCalculator();
        
        // Get all members in the joint group
        $stmt = $pdo->prepare("
            SELECT m.id, m.member_id, m.first_name, m.last_name, m.individual_contribution
            FROM members m 
            WHERE m.joint_group_id = ? AND m.is_active = 1
            ORDER BY m.primary_joint_member DESC, m.first_name
        ");
        $stmt->execute([$joint_group_id]);
        $joint_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($joint_members)) {
            return ['success' => false, 'error' => 'No active members found in joint group'];
        }
        
        $created_payouts = [];
        $payout_ids = [];
        
        // Create individual payout for each member
        foreach ($joint_members as $joint_member) {
            // Calculate individual payout amount using enhanced calculator
            $payout_result = $calculator->calculateMemberFriendlyPayout($joint_member['id']);
            
            if (!$payout_result['success']) {
                throw new Exception("Failed to calculate payout for member {$joint_member['first_name']} {$joint_member['last_name']}");
            }
            
            $individual_gross = $payout_result['calculation']['display_payout_amount'];
            $individual_admin_fee = $payout_result['calculation']['admin_fee'];
            $individual_net = $payout_result['calculation']['real_net_payout'];
            
            // Generate unique payout ID for this member
            $member_payout_id = generatePayoutId($joint_member['member_id'], $scheduled_date);
            
            // Ensure payout ID is unique
            $stmt = $pdo->prepare("SELECT id FROM payouts WHERE payout_id = ?");
            $stmt->execute([$member_payout_id]);
            if ($stmt->fetch()) {
                $counter = 1;
                do {
                    $counter++;
                    $temp_id = $member_payout_id . '-' . $counter;
                    $stmt->execute([$temp_id]);
                } while ($stmt->fetch());
                $member_payout_id = $temp_id;
            }
            
            // Insert individual payout
            $stmt = $pdo->prepare("
                INSERT INTO payouts 
                (payout_id, member_id, total_amount, scheduled_date, actual_payout_date, status, payout_method, 
                 admin_fee, net_amount, processed_by_admin_id, payout_notes, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $member_payout_id, 
                $joint_member['id'], 
                $individual_gross, 
                $scheduled_date, 
                $actual_payout_date, 
                $status, 
                $payout_method, 
                $individual_admin_fee, 
                $individual_net, 
                $processed_by_admin_id, 
                $payout_notes . " (Joint Group Member)"
            ]);
            
            // Sync member payout flag
            syncMemberPayoutFlag($joint_member['id']);
            
            $created_payouts[] = [
                'member_id' => $joint_member['id'],
                'member_name' => $joint_member['first_name'] . ' ' . $joint_member['last_name'],
                'member_code' => $joint_member['member_id'],
                'payout_id' => $member_payout_id,
                'individual_contribution' => $joint_member['individual_contribution'],
                'gross_amount' => $individual_gross,
                'admin_fee' => $individual_admin_fee,
                'net_amount' => $individual_net
            ];
            
            $payout_ids[] = $member_payout_id;
        }
        
        return [
            'success' => true,
            'count' => count($created_payouts),
            'payout_ids' => $payout_ids,
            'individual_payouts' => $created_payouts
        ];
        
    } catch (Exception $e) {
        error_log("Joint Group Payout Creation Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Add new payout
 */
function addPayout() {
    global $pdo, $admin_id;
    
    $member_id = intval($_POST['member_id'] ?? 0);
    $total_amount_input = $_POST['total_amount'] ?? '';
    $scheduled_date = $_POST['scheduled_date'] ?? '';
    
    if (!$member_id || !$total_amount_input || !$scheduled_date) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be provided']);
        return;
    }
    
    // SECURITY FIX: Strict financial validation
    if (!is_numeric($total_amount_input)) {
        echo json_encode(['success' => false, 'message' => 'Total amount must be a valid number']);
        return;
    }
    
    $total_amount = round(floatval($total_amount_input), 2); // Round to 2 decimal places
    
    if ($total_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Total amount must be greater than 0']);
        return;
    }
    
    if ($total_amount > 9999999.99) {
        echo json_encode(['success' => false, 'message' => 'Total amount exceeds maximum allowed limit']);
        return;
    }
    
    // Validate member exists and is active, get membership type and joint group info
    $stmt = $pdo->prepare("
        SELECT m.id, m.member_id, m.first_name, m.last_name, m.membership_type, m.joint_group_id 
        FROM members m 
        WHERE m.id = ? AND m.is_active = 1
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive member selected']);
        return;
    }
    
    // Check if member/group already has a payout for the same scheduled date
    if ($member['membership_type'] === 'joint') {
        // For joint members, check if any member in the group already has a payout for this date
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as existing_payouts 
            FROM payouts p 
            JOIN members m ON p.member_id = m.id 
            WHERE m.joint_group_id = ? AND p.scheduled_date = ?
        ");
        $stmt->execute([$member['joint_group_id'], $scheduled_date]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing['existing_payouts'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Payout for this joint group and date already exists']);
            return;
        }
    } else {
        // For individual members, check normal way
        $stmt = $pdo->prepare("SELECT id FROM payouts WHERE member_id = ? AND scheduled_date = ?");
        $stmt->execute([$member_id, $scheduled_date]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Payout for this member and date already exists']);
            return;
        }
    }
    
    // Optional fields with validation
    $payout_method = sanitize_input($_POST['payout_method'] ?? 'bank_transfer');
    $admin_fee_input = $_POST['admin_fee'] ?? 0;
    $status = sanitize_input($_POST['status'] ?? 'scheduled');
    $payout_notes = sanitize_input($_POST['payout_notes'] ?? '');
    $manual_actual_date = $_POST['actual_payout_date'] ?? '';
    
    // SECURITY FIX: Validate admin fee
    if (!is_numeric($admin_fee_input)) {
        echo json_encode(['success' => false, 'message' => 'Admin fee must be a valid number']);
        return;
    }
    
    $admin_fee = round(floatval($admin_fee_input), 2);
    
    if ($admin_fee < 0) {
        echo json_encode(['success' => false, 'message' => 'Admin fee cannot be negative']);
        return;
    }
    
    if ($admin_fee >= $total_amount) {
        echo json_encode(['success' => false, 'message' => 'Admin fee cannot exceed total amount']);
        return;
    }
    
    // Calculate net amount with validation
    $net_amount = round($total_amount - $admin_fee, 2);
    
    if ($net_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Net amount must be greater than 0']);
        return;
    }
    
    // Handle actual payout date for completed status
    $actual_payout_date = null;
    $processed_by_admin_id = null;
    
    // Handle actual payout date based on status
    
    if ($status === 'completed') {
        // Check if manual date is provided and valid
        if (!empty($manual_actual_date) && $manual_actual_date !== '') {
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $manual_actual_date)) {
                $actual_payout_date = $manual_actual_date;
            } else {
                $actual_payout_date = date('Y-m-d');
            }
        } else {
            // Auto-set to today if no manual date provided
            $actual_payout_date = date('Y-m-d');
        }
        $processed_by_admin_id = $admin_id;
    }
    
    // Handle joint group vs individual payout creation
    if ($member['membership_type'] === 'joint') {
        // JOINT GROUP PAYOUT - Create individual payouts for each member
        $created_payouts = createJointGroupPayouts(
            $member['joint_group_id'], 
            $total_amount, 
            $scheduled_date, 
            $actual_payout_date, 
            $status, 
            $payout_method, 
            $admin_fee, 
            $net_amount, 
            $processed_by_admin_id, 
            $payout_notes
        );
        
        if ($created_payouts['success']) {
            echo json_encode([
                'success' => true, 
                'message' => "Joint group payouts created successfully for {$created_payouts['count']} members",
                'payout_ids' => $created_payouts['payout_ids'],
                'individual_payouts' => $created_payouts['individual_payouts'],
                'is_joint_group' => true
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => $created_payouts['error']
            ]);
        }
    } else {
        // INDIVIDUAL PAYOUT - Standard single payout creation
        
        // Generate payout ID: PAYOUT-MEMBERINITIALS-MMYYYY (e.g., PAYOUT-MW-012024)
        $payout_id = generatePayoutId($member['member_id'], $scheduled_date);
        
        // Ensure payout ID is unique
        $stmt = $pdo->prepare("SELECT id FROM payouts WHERE payout_id = ?");
        $stmt->execute([$payout_id]);
        if ($stmt->fetch()) {
            // Add counter if duplicate
            $counter = 1;
            do {
                $counter++;
                $temp_id = $payout_id . '-' . $counter;
                $stmt->execute([$temp_id]);
            } while ($stmt->fetch());
            $payout_id = $temp_id;
        }
        
        // Insert payout
        $stmt = $pdo->prepare("
            INSERT INTO payouts 
            (payout_id, member_id, total_amount, scheduled_date, actual_payout_date, status, payout_method, 
             admin_fee, net_amount, processed_by_admin_id, payout_notes, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $payout_id, $member_id, $total_amount, $scheduled_date, $actual_payout_date, $status, 
            $payout_method, $admin_fee, $net_amount, $processed_by_admin_id, $payout_notes
        ]);
        
        // MASTER-LEVEL: Auto-sync the member's payout flag
        syncMemberPayoutFlag($member_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Individual payout scheduled successfully',
            'payout_id' => $payout_id,
            'member_payout_flag_synced' => true,
            'is_joint_group' => false
        ]);
    }
}

/**
 * Get payout by ID
 */
function getPayout() {
    global $pdo;
    
    $payout_id = intval($_GET['payout_id'] ?? 0);
    
    if (!$payout_id) {
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT p.*, m.first_name, m.last_name, m.member_id as member_code
        FROM payouts p
        LEFT JOIN members m ON p.member_id = m.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payout_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payout) {
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
    echo json_encode(['success' => true, 'payout' => $payout]);
}

/**
 * Update payout
 */
function updatePayout() {
    global $pdo, $admin_id;
    
    $payout_id = intval($_POST['payout_id'] ?? 0);
    $member_id = intval($_POST['member_id'] ?? 0);
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $scheduled_date = $_POST['scheduled_date'] ?? '';
    
    if (!$payout_id || !$member_id || !$total_amount || !$scheduled_date) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be provided']);
        return;
    }
    
    if ($total_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Total amount must be greater than 0']);
        return;
    }
    
    // Get current payout data
    $stmt = $pdo->prepare("SELECT * FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
    $current_payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_payout) {
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
    // Validate member exists and is active
    $stmt = $pdo->prepare("SELECT id FROM members WHERE id = ? AND is_active = 1");
    $stmt->execute([$member_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive member selected']);
        return;
    }
    
    // Check for duplicate payout (same member and date, excluding current payout)
    $stmt = $pdo->prepare("SELECT id FROM payouts WHERE member_id = ? AND scheduled_date = ? AND id != ?");
    $stmt->execute([$member_id, $scheduled_date, $payout_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Payout for this member and date already exists']);
        return;
    }
    
    // Optional fields
    $payout_method = sanitize_input($_POST['payout_method'] ?? 'bank_transfer');
    $admin_fee = floatval($_POST['admin_fee'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'scheduled');
    $payout_notes = sanitize_input($_POST['payout_notes'] ?? '');
    $manual_actual_date = $_POST['actual_payout_date'] ?? '';
    
    // Calculate net amount
    $net_amount = $total_amount - $admin_fee;
    
    // Handle actual payout date logic
    $actual_payout_date = null;
    $processed_by_admin_id = null;
    
    if ($status === 'completed') {
        // Check if manual date is provided and valid
        if (!empty($manual_actual_date) && $manual_actual_date !== '') {
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $manual_actual_date)) {
                $actual_payout_date = $manual_actual_date;
            } else {
                $actual_payout_date = date('Y-m-d');
            }
        } elseif ($current_payout['status'] !== 'completed') {
            // Auto-set to today if changing to completed and no manual date
            $actual_payout_date = date('Y-m-d');
        } else {
            // Keep existing date if already completed
            $actual_payout_date = $current_payout['actual_payout_date'];
        }
        $processed_by_admin_id = $admin_id;
    } elseif ($current_payout['status'] === 'completed' && $status !== 'completed') {
        // If changing from completed to other status, clear date
        $actual_payout_date = null;
        $processed_by_admin_id = null;
    } else {
        // For non-completed status, keep existing date if it exists
        $actual_payout_date = $current_payout['actual_payout_date'];
    }
    
    // Update payout
    $stmt = $pdo->prepare("
        UPDATE payouts SET 
            member_id = ?, total_amount = ?, scheduled_date = ?, status = ?, 
            payout_method = ?, admin_fee = ?, net_amount = ?, payout_notes = ?,
            actual_payout_date = ?, processed_by_admin_id = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $member_id, $total_amount, $scheduled_date, $status, 
        $payout_method, $admin_fee, $net_amount, $payout_notes,
        $actual_payout_date, $processed_by_admin_id, $payout_id
    ]);
    
    // MASTER-LEVEL: Auto-sync the member's payout flag
    syncMemberPayoutFlag($member_id);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payout updated successfully',
        'member_payout_flag_synced' => true
    ]);
}

/**
 * Delete payout
 */
function deletePayout() {
    global $pdo;
    
    $payout_id = intval($_POST['payout_id'] ?? 0);
    
    if (!$payout_id) {
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    // Get payout data before deletion
    $stmt = $pdo->prepare("SELECT * FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payout) {
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
    $member_id = $payout['member_id'];
    
    // Delete payout
    $stmt = $pdo->prepare("DELETE FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
    
    // MASTER-LEVEL: Auto-sync the member's payout flag after deletion
    syncMemberPayoutFlag($member_id);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payout deleted successfully',
        'member_payout_flag_synced' => true
    ]);
}

/**
 * Process payout (mark as completed)
 */
function processPayout() {
    global $pdo, $admin_id;
    
    $payout_id = intval($_POST['payout_id'] ?? 0);
    
    if (!$payout_id) {
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    // Get current payout
    $stmt = $pdo->prepare("SELECT * FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payout) {
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
    if ($payout['status'] === 'completed') {
        echo json_encode(['success' => false, 'message' => 'Payout is already processed']);
        return;
    }
    
    // Update payout status to completed
    $stmt = $pdo->prepare("
        UPDATE payouts SET 
            status = 'completed',
            actual_payout_date = CURDATE(),
            processed_by_admin_id = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$admin_id, $payout_id]);
    
    // MASTER-LEVEL: Auto-sync the member's payout flag
    syncMemberPayoutFlag($payout['member_id']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payout processed successfully',
        'member_payout_flag_synced' => true
    ]);
}

/**
 * List payouts with filters
 */
function listPayouts() {
    global $pdo;
    
    // Get filter parameters
    $search = sanitize_input($_GET['search'] ?? '');
    $status = sanitize_input($_GET['status'] ?? '');
    $member_id = intval($_GET['member_id'] ?? 0);
    
    // Build query
    $query = "
        SELECT p.*, 
               m.first_name, m.last_name, m.member_id as member_code, m.email,
               pa.username as processed_by_name
        FROM payouts p 
        LEFT JOIN members m ON p.member_id = m.id
        LEFT JOIN admins pa ON p.processed_by_admin_id = pa.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Apply filters
    if ($search) {
        $query .= " AND (
            m.first_name LIKE ? OR 
            m.last_name LIKE ? OR 
            p.payout_id LIKE ? OR 
            p.total_amount LIKE ?
        )";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    if ($status) {
        $query .= " AND p.status = ?";
        $params[] = $status;
    }
    
    if ($member_id) {
        $query .= " AND p.member_id = ?";
        $params[] = $member_id;
    }
    
    $query .= " ORDER BY p.scheduled_date DESC, p.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'payouts' => $payouts]);
}

/**
 * Generate payout ID
 */
function generatePayoutId($member_code, $scheduled_date) {
    // Extract initials from member code (e.g., HEM-MW1 -> MW)
    $parts = explode('-', $member_code);
    $initials = isset($parts[1]) ? preg_replace('/\d+/', '', $parts[1]) : 'XX';
    
    // Format date as MMYYYY
    $date = date('mY', strtotime($scheduled_date));
    
    return "PAYOUT-{$initials}-{$date}";
}
?> 