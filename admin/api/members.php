<?php
/**
 * HabeshaEqub - Members Management API
 * AJAX endpoint for all member CRUD operations
 */

require_once '../../includes/db.php';
require_once '../../includes/payout_sync_service.php';

// Set JSON header
header('Content-Type: application/json');

// SECURITY FIX: Use standardized admin authentication
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get the action from POST data
$action = $_POST['action'] ?? '';

// SECURITY FIX: Add CSRF protection for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, ['list', 'get_member', 'get_occupied_positions', 'get_equb_start_date'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ]);
        exit;
    }
}

switch ($action) {
    case 'add':
        addMember();
        break;
    case 'edit':
        editMember();
        break;
    case 'update':
        updateMember();
        break;
    case 'delete':
        deleteMember();
        break;
    case 'toggle_status':
        toggleMemberStatus();
        break;
    case 'get_member':
        getMember();
        break;
    case 'list':
        listMembers();
        break;
    case 'get_occupied_positions':
        getOccupiedPositions();
        break;
    case 'get_equb_start_date':
        getEqubStartDate();
        break;
    case 'get_existing_joint_groups':
        getExistingJointGroups();
        break;
    case 'get_joint_group_details':
        getJointGroupDetails();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Add new member
 */
function addMember() {
    global $pdo;
    
    try {
        // Start transaction for atomic operations
        $pdo->beginTransaction();
        
        // Validate required fields (including new equb fields)
        $required_fields = ['first_name', 'last_name', 'email', 'phone', 'equb_settings_id', 'monthly_payment', 'payout_position'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                return;
            }
        }
        
        // Sanitize inputs
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $equb_settings_id = intval($_POST['equb_settings_id']);
        $monthly_payment = floatval($_POST['monthly_payment']);
        $payout_position = intval($_POST['payout_position']);
        
        // Optional fields
        $guarantor_first_name = sanitize_input($_POST['guarantor_first_name'] ?? 'Pending');
        $guarantor_last_name = sanitize_input($_POST['guarantor_last_name'] ?? 'Pending');
        $guarantor_phone = sanitize_input($_POST['guarantor_phone'] ?? 'Pending');
        $guarantor_email = sanitize_input($_POST['guarantor_email'] ?? '');
        $guarantor_relationship = sanitize_input($_POST['guarantor_relationship'] ?? '');
        $notes = sanitize_input($_POST['notes'] ?? '');
        $payout_month = $_POST['payout_month'] ?? null;
        $go_public = isset($_POST['go_public']) ? 1 : 0;
        $swap_terms_allowed = isset($_POST['swap_terms_allowed']) ? 1 : 0;
        
        // Joint membership fields
        $membership_type = sanitize_input($_POST['membership_type'] ?? 'individual');
        $existing_joint_group = sanitize_input($_POST['existing_joint_group'] ?? '');
        $joint_group_name = sanitize_input($_POST['joint_group_name'] ?? '');
        $individual_contribution = floatval($_POST['individual_contribution'] ?? 0);
        $payout_split_method = sanitize_input($_POST['payout_split_method'] ?? 'equal');
        $joint_position_share = floatval($_POST['joint_position_share'] ?? 1.0);
        $primary_joint_member = isset($_POST['primary_joint_member']) ? 1 : 0;
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            $pdo->rollback();
            return;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $pdo->rollback();
            return;
        }
        
        // Handle joint membership logic
        $joint_group_id = null;
        $joint_member_count = 1;
        
        if ($membership_type === 'joint') {
            if (!empty($existing_joint_group)) {
                // Joining existing joint group
                $joint_group_id = $existing_joint_group;
                
                // Validate existing group
                $stmt = $pdo->prepare("
                    SELECT jmg.*, COUNT(m.id) as current_members, es.max_joint_members_per_group
                    FROM joint_membership_groups jmg
                    LEFT JOIN members m ON jmg.joint_group_id = m.joint_group_id AND m.is_active = 1
                    JOIN equb_settings es ON jmg.equb_settings_id = es.id
                    WHERE jmg.joint_group_id = ? AND jmg.is_active = 1
                    GROUP BY jmg.id
                ");
                $stmt->execute([$joint_group_id]);
                $group_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$group_data) {
                    echo json_encode(['success' => false, 'message' => 'Joint group not found']);
                    $pdo->rollback();
                    return;
                }
                
                if ($group_data['current_members'] >= $group_data['max_joint_members_per_group']) {
                    echo json_encode(['success' => false, 'message' => 'Joint group is full']);
                    $pdo->rollback();
                    return;
                }
                
                // Use group's payout position and monthly payment
                $payout_position = $group_data['payout_position'];
                $monthly_payment = $group_data['total_monthly_payment'];
                $payout_split_method = $group_data['payout_split_method'];
                $joint_member_count = $group_data['current_members'] + 1;
                
                // For existing groups, new members are usually secondary
                $primary_joint_member = 0;
                
            } else {
                // Creating new joint group
                if ($individual_contribution <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Individual contribution is required for joint membership']);
                    $pdo->rollback();
                    return;
                }
                
                // Check if position is available (for new joint groups)
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM (
                        SELECT 1 FROM members WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1
                        UNION ALL
                        SELECT 1 FROM joint_membership_groups WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1
                    ) as occupied
                ");
                $stmt->execute([$equb_settings_id, $payout_position, $equb_settings_id, $payout_position]);
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Payout position already taken']);
                    $pdo->rollback();
                    return;
                }
                
                // Generate joint group ID
                $stmt = $pdo->prepare("SELECT equb_id FROM equb_settings WHERE id = ?");
                $stmt->execute([$equb_settings_id]);
                $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) + 1 as next_number 
                    FROM joint_membership_groups 
                    WHERE equb_settings_id = ?
                ");
                $stmt->execute([$equb_settings_id]);
                $next_number = str_pad($stmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
                $joint_group_id = "JNT-{$equb_data['equb_id']}-{$next_number}";
                
                // Create joint group record
                $stmt = $pdo->prepare("
                    INSERT INTO joint_membership_groups (
                        joint_group_id, equb_settings_id, group_name, total_monthly_payment,
                        payout_position, payout_split_method, member_count
                    ) VALUES (?, ?, ?, ?, ?, ?, 0)
                ");
                $stmt->execute([
                    $joint_group_id, $equb_settings_id, $joint_group_name, $monthly_payment,
                    $payout_position, $payout_split_method
                ]);
                
                // First member is primary by default
                $primary_joint_member = 1;
            }
        } else {
            // Individual membership - check position availability
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM (
                    SELECT 1 FROM members WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1
                    UNION ALL
                    SELECT 1 FROM joint_membership_groups WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1
                ) as occupied
            ");
            $stmt->execute([$equb_settings_id, $payout_position, $equb_settings_id, $payout_position]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Payout position already taken']);
            $pdo->rollback();
            return;
            }
        }
        
        // Verify equb term has available spots
        $stmt = $pdo->prepare("SELECT max_members, current_members FROM equb_settings WHERE id = ?");
        $stmt->execute([$equb_settings_id]);
        $equb_data = $stmt->fetch();
        
        if (!$equb_data) {
            echo json_encode(['success' => false, 'message' => 'Equb term not found']);
            $pdo->rollback();
            return;
        }
        
        if ($equb_data['current_members'] >= $equb_data['max_members']) {
            echo json_encode(['success' => false, 'message' => 'Equb term is full']);
            $pdo->rollback();
            return;
        }
        
        // Generate member ID
        $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
        
        // Find next available number for these initials
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE member_id LIKE ? ORDER BY member_id DESC LIMIT 1");
        $stmt->execute(["HEM-{$initials}%"]);
        $last_member = $stmt->fetch();
        
        if ($last_member) {
            $last_number = intval(substr($last_member['member_id'], -1));
            $next_number = $last_number + 1;
        } else {
            $next_number = 1;
        }
        
        $member_id = "HEM-{$initials}{$next_number}";
        
        // Convert payout_month to proper date format
        if ($payout_month) {
            $payout_month_date = $payout_month . '-05'; // Set to 5th of the month (payout day)
        } else {
            $payout_month_date = null;
        }
        
        // Generate derived fields to match your exact database schema
        $username = strtolower(str_replace([' ', '.', '@', '+'], ['', '', '', ''], substr($email, 0, strpos($email, '@'))));
        $full_name = trim($first_name . ' ' . $last_name);
        
        // Insert member with ALL columns matching your EXACT database schema
        $stmt = $pdo->prepare("
            INSERT INTO members (
                equb_settings_id, member_id, username, first_name, last_name, full_name,
                email, phone, status, monthly_payment, payout_position, payout_month, 
                total_contributed, has_received_payout, guarantor_first_name, guarantor_last_name, 
                guarantor_phone, guarantor_email, guarantor_relationship, is_active, is_approved, 
                email_verified, join_date, notification_preferences, go_public, language_preference, 
                rules_agreed, notes, created_at, updated_at, email_notifications, payment_reminders, 
                swap_terms_allowed, membership_type, joint_group_id, joint_member_count, 
                individual_contribution, joint_position_share, primary_joint_member, payout_split_method
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, 1, 1, 0, CURDATE(), 'both', 1, 1, 0, ?, NOW(), NOW(), 1, 1, 0, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $equb_settings_id, $member_id, $username, $first_name, $last_name, $full_name,
            $email, $phone, $monthly_payment, $payout_position, $payout_month_date,
            $guarantor_first_name, $guarantor_last_name, $guarantor_phone, 
            $guarantor_email, $guarantor_relationship, $notes, $membership_type, $joint_group_id,
            $joint_member_count, $individual_contribution, $joint_position_share,
            $primary_joint_member, $payout_split_method
        ]);
        
        if ($result) {
            // Set privacy and permission flags explicitly based on admin input
            try {
                $new_member_id = (int)$pdo->lastInsertId();
                $up = $pdo->prepare("UPDATE members SET go_public = ?, swap_terms_allowed = ?, updated_at = NOW() WHERE id = ?");
                $up->execute([$go_public, $swap_terms_allowed, $new_member_id]);
            } catch (Exception $e) {
                // continue; not fatal
            }
            // Update current_members count in equb_settings
            $stmt = $pdo->prepare("
                UPDATE equb_settings 
                SET current_members = current_members + 1,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$equb_settings_id]);
            
            // Update joint group member count if applicable
            if ($membership_type === 'joint' && $joint_group_id) {
                $stmt = $pdo->prepare("
                    UPDATE joint_membership_groups 
                    SET member_count = member_count + 1,
                        updated_at = NOW()
                    WHERE joint_group_id = ?
                ");
                $stmt->execute([$joint_group_id]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Auto-sync payout date for the new member
            try {
                $payout_service = getPayoutSyncService();
                $new_member_result = $pdo->lastInsertId();
                $payout_sync_result = $payout_service->calculateMemberPayoutDate($new_member_result, true);
                
                $message = 'Member added successfully and assigned to equb term';
                if ($membership_type === 'joint') {
                    $message .= " (Joint Group: {$joint_group_id})";
                }
                if (isset($payout_sync_result['calculated_payout_date'])) {
                    $message .= '. Payout date: ' . date('M j, Y', strtotime($payout_sync_result['calculated_payout_date']));
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'member_id' => $member_id,
                    'password' => $password,
                    'membership_type' => $membership_type,
                    'joint_group_id' => $joint_group_id,
                    'payout_info' => $payout_sync_result
                ]);
                
            } catch (Exception $e) {
                error_log("Payout sync after member add failed: " . $e->getMessage());
                echo json_encode([
                    'success' => true, 
                    'message' => 'Member added successfully but payout sync failed',
                    'member_id' => $member_id,
                    'password' => $password
                ]);
            }
        } else {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to add member']);
        }
        
    } catch (PDOException $e) {
        $pdo->rollback();
        error_log("Add member error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Get member details for editing
 */
function getMember() {
    global $pdo;
    
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   es.equb_name, es.equb_id, es.start_date, es.duration_months,
                   DATE_FORMAT(m.payout_month, '%Y-%m') as formatted_payout_month
            FROM members m 
            LEFT JOIN equb_settings es ON m.equb_settings_id = es.id 
            WHERE m.id = ?
        ");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($member) {
            echo json_encode(['success' => true, 'member' => $member]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
        }
    } catch (PDOException $e) {
        error_log("Get member error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Update member
 */
function updateMember() {
    global $pdo;
    
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
        return;
    }
    
    try {
        // Start transaction for atomic operations
        $pdo->beginTransaction();
        
        // Get current member data to compare changes
        $stmt = $pdo->prepare("SELECT equb_settings_id, payout_position FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $current_member = $stmt->fetch();
        
        if (!$current_member) {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
            $pdo->rollback();
            return;
        }
        
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email', 'phone', 'equb_settings_id', 'monthly_payment', 'payout_position'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                $pdo->rollback();
                return;
            }
        }
        
        // Sanitize inputs
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $new_equb_settings_id = intval($_POST['equb_settings_id']);
        $monthly_payment = floatval($_POST['monthly_payment']);
        $payout_position = intval($_POST['payout_position']);
        
        // Optional fields
        $guarantor_first_name = sanitize_input($_POST['guarantor_first_name'] ?? '');
        $guarantor_last_name = sanitize_input($_POST['guarantor_last_name'] ?? '');
        $guarantor_phone = sanitize_input($_POST['guarantor_phone'] ?? '');
        $guarantor_email = sanitize_input($_POST['guarantor_email'] ?? '');
        $guarantor_relationship = sanitize_input($_POST['guarantor_relationship'] ?? '');
        $notes = sanitize_input($_POST['notes'] ?? '');
        $payout_month = $_POST['payout_month'] ?? null;
        $go_public = isset($_POST['go_public']) ? 1 : 0;
        $swap_terms_allowed = isset($_POST['swap_terms_allowed']) ? 1 : 0;
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            $pdo->rollback();
            return;
        }
        
        // Check if email already exists (excluding current member)
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
        $stmt->execute([$email, $member_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $pdo->rollback();
            return;
        }
        
        // Check if payout position is already taken in the target equb term (excluding current member)
        $stmt = $pdo->prepare("SELECT id FROM members WHERE equb_settings_id = ? AND payout_position = ? AND id != ? AND is_active = 1");
        $stmt->execute([$new_equb_settings_id, $payout_position, $member_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Payout position already taken in the target equb term']);
            $pdo->rollback();
            return;
        }
        
        // If equb term is changing, handle member count updates
        $old_equb_settings_id = $current_member['equb_settings_id'];
        $equb_changed = ($old_equb_settings_id != $new_equb_settings_id);
        
        if ($equb_changed) {
            // Check if new equb has available spots
            $stmt = $pdo->prepare("SELECT max_members, current_members FROM equb_settings WHERE id = ?");
            $stmt->execute([$new_equb_settings_id]);
            $new_equb_data = $stmt->fetch();
            
            if (!$new_equb_data) {
                echo json_encode(['success' => false, 'message' => 'Target equb term not found']);
                $pdo->rollback();
                return;
            }
            
            if ($new_equb_data['current_members'] >= $new_equb_data['max_members']) {
                echo json_encode(['success' => false, 'message' => 'Target equb term is full']);
                $pdo->rollback();
                return;
            }
        }
        
        // Convert payout_month to proper date format
        if ($payout_month) {
            $payout_month_date = $payout_month . '-05'; // Set to 5th of the month (payout day)
        } else {
            $payout_month_date = null;
        }
        
        // Update member
        $stmt = $pdo->prepare("
            UPDATE members SET 
                equb_settings_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?, 
                monthly_payment = ?, payout_position = ?, payout_month = ?,
                guarantor_first_name = ?, guarantor_last_name = ?, 
                guarantor_phone = ?, guarantor_email = ?, guarantor_relationship = ?, 
                notes = ?, go_public = ?, swap_terms_allowed = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $new_equb_settings_id, $first_name, $last_name, $email, $phone, 
            $monthly_payment, $payout_position, $payout_month_date,
            $guarantor_first_name, $guarantor_last_name, $guarantor_phone, 
            $guarantor_email, $guarantor_relationship, $notes, $go_public, $swap_terms_allowed, $member_id
        ]);
        
        if ($result) {
            // Update equb member counts if equb term changed
            if ($equb_changed && $old_equb_settings_id) {
                // Decrease count in old equb
                $stmt = $pdo->prepare("
                    UPDATE equb_settings 
                    SET current_members = current_members - 1,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$old_equb_settings_id]);
                
                // Increase count in new equb
                $stmt = $pdo->prepare("
                    UPDATE equb_settings 
                    SET current_members = current_members + 1,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_equb_settings_id]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Auto-sync payout date after update
            try {
                $payout_service = getPayoutSyncService();
                $payout_sync_result = $payout_service->calculateMemberPayoutDate($member_id, true);
                
                $message = $equb_changed ? 
                    'Member updated successfully and reassigned to new equb term' : 
                    'Member updated successfully';
                
                if (isset($payout_sync_result['calculated_payout_date'])) {
                    $message .= '. Payout date: ' . date('M j, Y', strtotime($payout_sync_result['calculated_payout_date']));
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'payout_info' => $payout_sync_result
                ]);
                
            } catch (Exception $e) {
                error_log("Payout sync after member update failed: " . $e->getMessage());
                $message = $equb_changed ? 
                    'Member updated successfully but payout sync failed' : 
                    'Member updated successfully but payout sync failed';
                echo json_encode(['success' => true, 'message' => $message]);
            }
        } else {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to update member']);
        }
        
    } catch (PDOException $e) {
        $pdo->rollback();
        error_log("Update member error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Delete member
 */
function deleteMember() {
    global $pdo;
    
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
        return;
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get member's equb assignment before deletion
        $stmt = $pdo->prepare("SELECT equb_settings_id FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $member_data = $stmt->fetch();
        
        if (!$member_data) {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
            $pdo->rollback();
            return;
        }
        
        // Check if member has payments or payouts
        $stmt = $pdo->prepare("SELECT COUNT(*) as payment_count FROM payments WHERE member_id = ?");
        $stmt->execute([$member_id]);
        $payment_count = $stmt->fetch()['payment_count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as payout_count FROM payouts WHERE member_id = ?");
        $stmt->execute([$member_id]);
        $payout_count = $stmt->fetch()['payout_count'];
        
        if ($payment_count > 0 || $payout_count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete member with existing payments or payouts']);
            $pdo->rollback();
            return;
        }
        
        // Delete member
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $result = $stmt->execute([$member_id]);
        
        if ($result) {
            // Update equb member count and RECALCULATE ALL VALUES if member was assigned to an equb
            if ($member_data['equb_settings_id']) {
                // Include the enhanced calculator for automatic recalculation
                require_once '../../includes/enhanced_equb_calculator.php';
                $calculator = getEnhancedEqubCalculator();
                
                // Recalculate the affected equb
                $calculation_result = $calculator->calculateEqubPositions($member_data['equb_settings_id']);
                
                if ($calculation_result['success']) {
                    $monthly_pool = $calculation_result['total_monthly_pool'];
                    $stmt = $pdo->prepare("SELECT duration_months FROM equb_settings WHERE id = ?");
                    $stmt->execute([$member_data['equb_settings_id']]);
                    $duration = $stmt->fetchColumn();
                    $new_total_pool = $monthly_pool * $duration;
                    
                    // Update equb settings with recalculated values
                    $stmt = $pdo->prepare("
                        UPDATE equb_settings 
                        SET 
                            current_members = (SELECT COUNT(*) FROM members WHERE equb_settings_id = ? AND is_active = 1),
                            total_pool_amount = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$member_data['equb_settings_id'], $new_total_pool, $member_data['equb_settings_id']]);
                    
                    // Update remaining members' expected payouts
                    foreach ($calculation_result['position_analysis'] as $member_analysis) {
                        $stmt = $pdo->prepare("
                            UPDATE members 
                            SET 
                                expected_payout = ?,
                                position_coefficient = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $member_analysis['expected_payout'],
                            $member_analysis['position_coefficient'],
                            $member_analysis['member_id']
                        ]);
                    }
                    
                    // Update payout positions if they exist
                    $stmt = $pdo->prepare("
                        UPDATE payout_positions pp
                        JOIN members m ON pp.member_id = m.id
                        SET 
                            pp.expected_payout = m.expected_payout,
                            pp.updated_at = NOW()
                        WHERE m.equb_settings_id = ?
                    ");
                    $stmt->execute([$member_data['equb_settings_id']]);
                }
            }
            
            // Commit transaction
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Member deleted successfully and equb values recalculated for remaining members']);
        } else {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete member']);
        }
        
    } catch (PDOException $e) {
        $pdo->rollback();
        error_log("Delete member error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Toggle member status (active/inactive)
 */
function toggleMemberStatus() {
    global $pdo, $admin_id;
    
    $member_id = intval($_POST['member_id'] ?? 0);
    $status = intval($_POST['status'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
        return;
    }
    
    try {
        // Get member's equb assignment for recalculation
        $stmt = $pdo->prepare("SELECT equb_settings_id FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $member_data = $stmt->fetch();
        
        $stmt = $pdo->prepare("UPDATE members SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$status, $member_id]);
        
        if ($result) {
            // AUTOMATIC RECALCULATION when member status changes
            if ($member_data && $member_data['equb_settings_id']) {
                // Include the enhanced calculator
                require_once '../../includes/enhanced_equb_calculator.php';
                $calculator = getEnhancedEqubCalculator();
                
                // Recalculate the affected equb
                $calculation_result = $calculator->calculateEqubPositions($member_data['equb_settings_id']);
                
                if ($calculation_result['success']) {
                    $monthly_pool = $calculation_result['total_monthly_pool'];
                    $stmt = $pdo->prepare("SELECT duration_months FROM equb_settings WHERE id = ?");
                    $stmt->execute([$member_data['equb_settings_id']]);
                    $duration = $stmt->fetchColumn();
                    $new_total_pool = $monthly_pool * $duration;
                    
                    // Update equb settings with recalculated values
                    $stmt = $pdo->prepare("
                        UPDATE equb_settings 
                        SET 
                            current_members = (SELECT COUNT(*) FROM members WHERE equb_settings_id = ? AND is_active = 1),
                            total_pool_amount = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$member_data['equb_settings_id'], $new_total_pool, $member_data['equb_settings_id']]);
                    
                    // Update all active members' expected payouts
                    foreach ($calculation_result['position_analysis'] as $member_analysis) {
                        $stmt = $pdo->prepare("
                            UPDATE members 
                            SET 
                                expected_payout = ?,
                                position_coefficient = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $member_analysis['expected_payout'],
                            $member_analysis['position_coefficient'],
                            $member_analysis['member_id']
                        ]);
                    }
                }
            }
            
            $status_text = $status ? 'activated' : 'deactivated';
            echo json_encode(['success' => true, 'message' => "Member {$status_text} successfully and equb values automatically recalculated"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update member status']);
        }
        
    } catch (PDOException $e) {
        error_log("Toggle member status error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * List members with filters
 */
function listMembers() {
    global $pdo;
    
    try {
        $where_conditions = [];
        $params = [];
        
        // Search filter
        if (!empty($_POST['search'])) {
            $search = '%' . sanitize_input($_POST['search']) . '%';
            $where_conditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR member_id LIKE ?)";
            $params = array_merge($params, [$search, $search, $search]);
        }
        
        // Status filter
        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $where_conditions[] = "is_active = ?";
            $params[] = intval($_POST['status']);
        }
        
        // Payout status filter
        if (isset($_POST['payout_status']) && $_POST['payout_status'] !== '') {
            $where_conditions[] = "has_received_payout = ?";
            $params[] = intval($_POST['payout_status']);
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "
            SELECT m.*, 
                   COUNT(p.id) as total_payments,
                   COALESCE(SUM(p.amount), 0) as total_paid
            FROM members m 
            LEFT JOIN payments p ON m.id = p.member_id AND p.status = 'completed'
            {$where_clause}
            GROUP BY m.id 
            ORDER BY m.payout_position ASC, m.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'members' => $members]);
        
    } catch (PDOException $e) {
        error_log("List members error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Generate random 6-character alphanumeric password
 */
function generateRandomPassword($length = 6) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

/**
 * Get occupied payout positions for a specific equb term
 */
function getOccupiedPositions() {
    global $pdo;
    
    try {
        $equb_term_id = $_POST['equb_term_id'] ?? '';
        
        if (empty($equb_term_id)) {
            echo json_encode(['success' => false, 'message' => 'Equb term ID required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT payout_position 
            FROM members 
            WHERE equb_settings_id = ? AND is_active = 1 AND payout_position IS NOT NULL
            ORDER BY payout_position ASC
        ");
        $stmt->execute([$equb_term_id]);
        $occupied_positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'occupied_positions' => array_map('intval', $occupied_positions)
        ]);
        
    } catch (PDOException $e) {
        error_log("Error getting occupied positions: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Get equb start date for payout month calculation
 */
function getEqubStartDate() {
    global $pdo;
    
    try {
        $equb_term_id = $_POST['equb_term_id'] ?? '';
        
        if (empty($equb_term_id)) {
            echo json_encode(['success' => false, 'message' => 'Equb term ID required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT start_date, duration_months 
            FROM equb_settings 
            WHERE id = ?
        ");
        $stmt->execute([$equb_term_id]);
        $equb_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($equb_data) {
            echo json_encode([
                'success' => true,
                'start_date' => $equb_data['start_date'],
                'duration_months' => $equb_data['duration_months']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Equb term not found']);
        }
        
    } catch (PDOException $e) {
        error_log("Error getting equb start date: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Get existing joint groups for an equb term
 */
function getExistingJointGroups() {
    global $pdo;
    
    try {
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
        
    } catch (PDOException $e) {
        error_log("Error getting existing joint groups: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Get detailed information about a specific joint group
 */
function getJointGroupDetails() {
    global $pdo;
    
    try {
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
        
    } catch (PDOException $e) {
        error_log("Error getting joint group details: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}
?> 