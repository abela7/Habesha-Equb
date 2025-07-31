<?php
/**
 * HabeshaEqub - Members Management API
 * AJAX endpoint for all member CRUD operations
 */

require_once '../../includes/db.php';
require_once '../../includes/payout_sync_service.php';

// Set JSON header
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? 1;

// Get the action from POST data
$action = $_POST['action'] ?? '';

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
        
        // Check if payout position is already taken in this equb term
        $stmt = $pdo->prepare("SELECT id FROM members WHERE equb_settings_id = ? AND payout_position = ? AND is_active = 1");
        $stmt->execute([$equb_settings_id, $payout_position]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Payout position already taken in this equb term']);
            $pdo->rollback();
            return;
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
        
        // Generate random 6-character password
        $password = generateRandomPassword();
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Convert payout_month to proper date format
        if ($payout_month) {
            $payout_month_date = $payout_month . '-05'; // Set to 5th of the month (payout day)
        } else {
            $payout_month_date = null;
        }
        
        // Insert member
        $stmt = $pdo->prepare("
            INSERT INTO members (
                equb_settings_id, member_id, first_name, last_name, email, phone, password, 
                monthly_payment, payout_position, payout_month, total_contributed, 
                has_received_payout, guarantor_first_name, guarantor_last_name, 
                guarantor_phone, guarantor_email, guarantor_relationship, 
                is_active, is_approved, email_verified, join_date, 
                notification_preferences, notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, 1, 1, 0, CURDATE(), 'email,sms', ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $equb_settings_id, $member_id, $first_name, $last_name, $email, $phone, $hashed_password,
            $monthly_payment, $payout_position, $payout_month_date,
            $guarantor_first_name, $guarantor_last_name, $guarantor_phone, 
            $guarantor_email, $guarantor_relationship, $notes
        ]);
        
        if ($result) {
            // Update current_members count in equb_settings
            $stmt = $pdo->prepare("
                UPDATE equb_settings 
                SET current_members = current_members + 1,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$equb_settings_id]);
            
            // Commit transaction
            $pdo->commit();
            
            // Auto-sync payout date for the new member
            try {
                $payout_service = getPayoutSyncService();
                $new_member_result = $pdo->lastInsertId();
                $payout_sync_result = $payout_service->calculateMemberPayoutDate($new_member_result, true);
                
                $message = 'Member added successfully and assigned to equb term';
                if (isset($payout_sync_result['calculated_payout_date'])) {
                    $message .= '. Payout date: ' . date('M j, Y', strtotime($payout_sync_result['calculated_payout_date']));
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'member_id' => $member_id,
                    'password' => $password,
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
                notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $new_equb_settings_id, $first_name, $last_name, $email, $phone, 
            $monthly_payment, $payout_position, $payout_month_date,
            $guarantor_first_name, $guarantor_last_name, $guarantor_phone, 
            $guarantor_email, $guarantor_relationship, $notes, $member_id
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
            // Update equb member count if member was assigned to an equb
            if ($member_data['equb_settings_id']) {
                $stmt = $pdo->prepare("
                    UPDATE equb_settings 
                    SET current_members = current_members - 1,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$member_data['equb_settings_id']]);
            }
            
            // Commit transaction
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Member deleted successfully']);
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
        $stmt = $pdo->prepare("UPDATE members SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$status, $member_id]);
        
        if ($result) {
            $status_text = $status ? 'activated' : 'deactivated';
            echo json_encode(['success' => true, 'message' => "Member {$status_text} successfully"]);
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
?> 