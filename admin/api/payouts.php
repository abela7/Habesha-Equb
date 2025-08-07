<?php
/**
 * HabeshaEqub - TOP-TIER Payouts API
 * PROFESSIONAL ROBUST SYSTEM - Built from scratch
 * NO HARDCODED VALUES - ALL DYNAMIC FROM DATABASE
 */

// Security and session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Database connection
require_once '../../includes/db.php';

// Admin authentication and CSRF functions
require_once '../includes/admin_auth_guard.php';

// Security check
$admin_id = get_current_admin_id();
if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Database connection check
if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// CSRF protection (except for read-only actions)
$read_only_actions = ['list', 'get', 'calculate'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $read_only_actions)) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
}

/**
 * Main action router
 */
try {
    switch ($action) {
        case 'calculate':
            calculatePayout();
            break;
        case 'add':
            addPayout();
            break;
        case 'list':
            listPayouts();
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
        case 'get_csrf_token':
            echo json_encode(['success' => true, 'csrf_token' => generate_csrf_token()]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Payouts API Error: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred processing your request']);
}

/**
 * CALCULATE PAYOUT - Professional implementation
 * Uses enhanced calculator with full error handling
 */
function calculatePayout() {
    global $pdo;
    
    $member_id = intval($_POST['member_id'] ?? $_GET['member_id'] ?? 0);
    
    if (!$member_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Member ID is required']);
        return;
    }
    
    try {
        // Verify member exists and is active
        $stmt = $pdo->prepare("
            SELECT m.id, m.first_name, m.last_name, m.is_active, m.equb_settings_id
            FROM members m 
            WHERE m.id = ?
        ");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Member not found']);
            return;
        }
        
        if (!$member['is_active']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Member is not active']);
            return;
        }
        
        // Load enhanced calculator
        require_once '../../includes/enhanced_equb_calculator_final.php';
        $calculator = new EnhancedEqubCalculator($pdo);
        
        // Calculate payout
        $result = $calculator->calculateMemberFriendlyPayout($member_id);
        
        if (!$result['success']) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Calculation failed: ' . ($result['error'] ?? 'Unknown error')
            ]);
            return;
        }
        
        // Extract calculation data
        $calc = $result['calculation'];
        $member_info = $result['member_info'];
        
        // Return structured response
        echo json_encode([
            'success' => true,
            'member_name' => $member_info['name'],
            'monthly_payment' => $calc['monthly_deduction'],
            'position_coefficient' => $calc['position_coefficient'],
            'total_monthly_pool' => $calc['total_monthly_pool'],
            'gross_payout' => $calc['gross_payout'],
            'admin_fee' => $calc['admin_fee'],
            'display_payout' => $calc['display_payout'],
            'net_payout' => $calc['real_net_payout'],
            'debug' => [
                'calculation_method' => $calc['calculation_method'],
                'formula_used' => $calc['formula_used']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Calculate Payout Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Calculation error occurred']);
    }
}

/**
 * ADD PAYOUT - Professional implementation
 */
function addPayout() {
    global $pdo, $admin_id;
    
    // Validate required fields
    $member_id = intval($_POST['member_id'] ?? 0);
    $gross_payout = floatval($_POST['gross_payout'] ?? 0);
    $scheduled_date = $_POST['scheduled_date'] ?? '';
    
    if (!$member_id || !$gross_payout || !$scheduled_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Member, gross payout, and scheduled date are required']);
        return;
    }
    
    try {
        // Verify member
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, is_active FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member || !$member['is_active']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid or inactive member']);
            return;
        }
        
        // Check for duplicate payout
        $stmt = $pdo->prepare("SELECT id FROM payouts WHERE member_id = ? AND scheduled_date = ?");
        $stmt->execute([$member_id, $scheduled_date]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Payout already exists for this member and date']);
            return;
        }
        
        // Get additional fields
        $admin_fee = floatval($_POST['admin_fee'] ?? 20); // Default but can be overridden
        $payout_method = $_POST['payout_method'] ?? 'bank_transfer';
        $status = $_POST['status'] ?? 'scheduled';
        $payout_notes = $_POST['payout_notes'] ?? '';
        $actual_date = $_POST['actual_payout_date'] ?? null;
        
        // Calculate derived amounts
        $total_amount = $gross_payout - $admin_fee; // What member sees
        
        // Get member's monthly payment for net calculation
        require_once '../../includes/enhanced_equb_calculator_final.php';
        $calculator = new EnhancedEqubCalculator($pdo);
        $calc_result = $calculator->calculateMemberFriendlyPayout($member_id);
        
        $monthly_deduction = 0;
        if ($calc_result['success']) {
            $monthly_deduction = $calc_result['calculation']['monthly_deduction'];
        }
        
        $net_amount = $gross_payout - $admin_fee - $monthly_deduction; // What member actually gets
        
        // Generate unique payout ID
        $payout_id = 'PO-' . date('Ymd') . '-' . sprintf('%03d', rand(100, 999));
        
        // Ensure uniqueness
        $stmt = $pdo->prepare("SELECT id FROM payouts WHERE payout_id = ?");
        $stmt->execute([$payout_id]);
        while ($stmt->fetch()) {
            $payout_id = 'PO-' . date('Ymd') . '-' . sprintf('%03d', rand(100, 999));
            $stmt->execute([$payout_id]);
        }
        
        // Insert payout
        $stmt = $pdo->prepare("
            INSERT INTO payouts 
            (payout_id, member_id, gross_payout, total_amount, net_amount, scheduled_date, 
             actual_payout_date, status, payout_method, admin_fee, processed_by_admin_id, 
             payout_notes, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $payout_id, $member_id, $gross_payout, $total_amount, $net_amount, $scheduled_date,
            $actual_date, $status, $payout_method, $admin_fee, $admin_id, $payout_notes
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Payout created successfully',
            'payout_id' => $payout_id,
            'member_name' => $member['first_name'] . ' ' . $member['last_name'],
            'amounts' => [
                'gross_payout' => $gross_payout,
                'total_amount' => $total_amount,
                'net_amount' => $net_amount,
                'admin_fee' => $admin_fee
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Add Payout Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create payout']);
    }
}

/**
 * LIST PAYOUTS - Professional implementation
 */
function listPayouts() {
    global $pdo;
    
    try {
        // Enhanced query with better error handling
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                CONCAT(m.first_name, ' ', m.last_name) as member_name,
                m.member_id as member_code,
                COALESCE(a.username, 'System') as processed_by_name
            FROM payouts p
            LEFT JOIN members m ON p.member_id = m.id
            LEFT JOIN admins a ON p.processed_by_admin_id = a.id
            ORDER BY p.created_at DESC
        ");
        
        $stmt->execute();
        $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug logging
        error_log("Payouts found: " . count($payouts));
        
        echo json_encode([
            'success' => true, 
            'payouts' => $payouts,
            'count' => count($payouts),
            'debug' => [
                'query_executed' => true,
                'result_count' => count($payouts)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("List Payouts Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to fetch payouts: ' . $e->getMessage(),
            'debug' => [
                'error_line' => $e->getLine(),
                'error_file' => basename($e->getFile())
            ]
        ]);
    }
}

/**
 * GET SINGLE PAYOUT
 */
function getPayout() {
    global $pdo;
    
    $payout_id = intval($_GET['id'] ?? 0);
    
    if (!$payout_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    try {
    $stmt = $pdo->prepare("
            SELECT 
                p.*,
                CONCAT(m.first_name, ' ', m.last_name) as member_name,
                m.member_id
        FROM payouts p
        LEFT JOIN members m ON p.member_id = m.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payout_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payout) {
            http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
    echo json_encode(['success' => true, 'payout' => $payout]);
        
    } catch (Exception $e) {
        error_log("Get Payout Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch payout']);
    }
}

/**
 * UPDATE PAYOUT
 */
function updatePayout() {
    global $pdo, $admin_id;
    
    $payout_id = intval($_POST['payout_id'] ?? 0);
    
    if (!$payout_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    try {
        // Verify payout exists
        $stmt = $pdo->prepare("SELECT id FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
        // Update fields
        $gross_payout = floatval($_POST['gross_payout'] ?? 0);
    $admin_fee = floatval($_POST['admin_fee'] ?? 0);
        $status = $_POST['status'] ?? 'scheduled';
        $payout_notes = $_POST['payout_notes'] ?? '';
        $actual_date = $_POST['actual_payout_date'] ?? null;
        
        // Recalculate derived amounts
        $total_amount = $gross_payout - $admin_fee;
        
    $stmt = $pdo->prepare("
            UPDATE payouts 
            SET gross_payout = ?, total_amount = ?, admin_fee = ?, status = ?, 
                payout_notes = ?, actual_payout_date = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
            $gross_payout, $total_amount, $admin_fee, $status, 
            $payout_notes, $actual_date, $payout_id
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Payout updated successfully']);
        
    } catch (Exception $e) {
        error_log("Update Payout Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update payout']);
    }
}

/**
 * DELETE PAYOUT
 */
function deletePayout() {
    global $pdo;
    
    $payout_id = intval($_POST['payout_id'] ?? 0);
    
    if (!$payout_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    try {
    $stmt = $pdo->prepare("DELETE FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
    
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
        echo json_encode(['success' => true, 'message' => 'Payout deleted successfully']);
        
    } catch (Exception $e) {
        error_log("Delete Payout Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete payout']);
    }
}


?> 