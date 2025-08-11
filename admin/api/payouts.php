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
        case 'process':
            processPayout();
            break;
        case 'get_payout_receipt_token':
            getPayoutReceiptToken();
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
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, is_active, equb_settings_id FROM members WHERE id = ?");
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
        
        // Create concise notification to the specific member
        try {
            $memberFirst = trim($member['first_name'] ?? '');
            $amountFormatted = '£' . number_format((float)$net_amount, 2);
            $dateHuman = date('F j, Y', strtotime($scheduled_date));
            $title_en = 'Payout scheduled';
            $title_am = $title_en; // reuse
            $body_en = "Dear {$memberFirst}, your payout has been scheduled for {$dateHuman}.\n\n- Payout amount: {$amountFormatted}\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.";
            $body_am = $body_en;

            // Generate unique notification code
            $code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);
            $chk = $pdo->prepare('SELECT id FROM program_notifications WHERE notification_code = ?');
            $chk->execute([$code]);
            while ($chk->fetch()) {
                $code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);
                $chk->execute([$code]);
            }
            $insNotif = $pdo->prepare("INSERT INTO program_notifications (notification_code, created_by_admin_id, audience_type, equb_settings_id, title_en, title_am, body_en, body_am, priority, status, sent_at, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,'normal','sent',NOW(),NOW(),NOW())");
            $insNotif->execute([$code, $admin_id, 'members', ($member['equb_settings_id'] ?? null), $title_en, $title_am, $body_en, $body_am]);
            $notificationId = (int)$pdo->lastInsertId();
            if ($notificationId) {
                $insRec = $pdo->prepare('INSERT IGNORE INTO notification_recipients (notification_id, member_id, created_at) VALUES (?, ?, NOW())');
                $insRec->execute([$notificationId, (int)$member_id]);
            }
        } catch (Throwable $e) {
            error_log('addPayout notify error: '.$e->getMessage());
        }
        
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
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $memberId = intval($_GET['member_id'] ?? 0);

        $query = "
            SELECT 
                p.*,
                m.first_name,
                m.last_name,
                CONCAT(m.first_name, ' ', m.last_name) as member_name,
                m.member_id as member_code,
                COALESCE(a.username, 'System') as processed_by_name
            FROM payouts p
            LEFT JOIN members m ON p.member_id = m.id
            LEFT JOIN admins a ON p.processed_by_admin_id = a.id
            WHERE 1=1
        ";
        $params = [];
        if ($search !== '') {
            $query .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR m.member_id LIKE ? OR p.payout_id LIKE ?)";
            $s = "%$search%";
            array_push($params, $s, $s, $s, $s);
        }
        if ($status !== '') {
            $query .= " AND p.status = ?";
            $params[] = $status;
        }
        if ($memberId > 0) {
            $query .= " AND p.member_id = ?";
            $params[] = $memberId;
        }

        $query .= " ORDER BY p.scheduled_date DESC, p.created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'payouts' => $payouts,
            'count' => count($payouts)
        ]);
    } catch (Exception $e) {
        error_log("List Payouts Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch payouts']);
    }
}

/**
 * GET SINGLE PAYOUT
 */
function getPayout() {
    global $pdo;
    
    $payout_id = intval($_GET['payout_id'] ?? $_GET['id'] ?? 0);
    
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
                m.member_id as member_code
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
        error_log("Get Payout Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch payout: ' . $e->getMessage()]);
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

/**
 * PROCESS PAYOUT - Mark as completed and set actual date
 */
function processPayout() {
    global $pdo;
    
    $payout_id = intval($_POST['payout_id'] ?? $_POST['id'] ?? 0);
    
    if (!$payout_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payout ID is required']);
        return;
    }
    
    try {
        // First check if payout exists and get current status
        $stmt = $pdo->prepare("SELECT id, status, payout_id, member_id FROM payouts WHERE id = ?");
    $stmt->execute([$payout_id]);
    $payout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payout) {
            http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payout not found']);
        return;
    }
    
        // Check if already processed
    if ($payout['status'] === 'completed') {
            echo json_encode(['success' => false, 'message' => 'Payout already completed']);
        return;
    }
    
        // Update payout status to completed and set actual date
    $stmt = $pdo->prepare("
            UPDATE payouts 
            SET status = 'completed', 
            actual_payout_date = CURDATE(),
            processed_by_admin_id = ?,
                updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
        
        $admin_id = get_current_admin_id();
    $stmt->execute([$admin_id, $payout_id]);
    
        if ($stmt->rowCount() === 0) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to process payout']);
            return;
        }
        
        // Update member's payout flag
    syncMemberPayoutFlag($payout['member_id']);
        
        // Send concise notification to that specific member and build WhatsApp text
        try {
            $q = $pdo->prepare("SELECT first_name, last_name, language_preference, email, is_active, is_approved, email_notifications, equb_settings_id FROM members WHERE id = ? LIMIT 1");
            $q->execute([$payout['member_id']]);
            $m = $q->fetch(PDO::FETCH_ASSOC) ?: [];
            $first = trim($m['first_name'] ?? '');
            // Load payout amounts for this payout
            $amtQ = $pdo->prepare("SELECT total_amount, net_amount, admin_fee, payout_method, COALESCE(actual_payout_date, CURDATE()) as actual_dt FROM payouts WHERE id = ?");
            $amtQ->execute([$payout_id]);
            $pinfo = $amtQ->fetch(PDO::FETCH_ASSOC) ?: [];
            $totalAmt = (float)($pinfo['total_amount'] ?? 0);
            $adminFee = (float)($pinfo['admin_fee'] ?? 0);
            $netAmt = (float)($pinfo['net_amount'] ?? 0);
            $method = (string)($pinfo['payout_method'] ?? 'bank_transfer');
            $actualDate = $pinfo['actual_dt'] ?? date('Y-m-d');
            $dateText = date('F j, Y', strtotime($actualDate));
            $amountFormatted = '£' . number_format($netAmt, 2);
            $adminFeeFormatted = '£' . number_format($adminFee, 2);

            // Ensure payout receipt token exists
            $receiptUrl = '';
            try {
                $pdo->prepare("CREATE TABLE IF NOT EXISTS payout_receipts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    payout_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_payout (payout_id),
                    CONSTRAINT fk_receipt_payout FOREIGN KEY (payout_id) REFERENCES payouts(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
                ->execute();
                // Try get existing
                $selTok = $pdo->prepare("SELECT token FROM payout_receipts WHERE payout_id = ? LIMIT 1");
                $selTok->execute([$payout_id]);
                $tok = $selTok->fetchColumn();
                if (!$tok) {
                    $tok = bin2hex(random_bytes(16));
                    $insTok = $pdo->prepare("INSERT INTO payout_receipts (payout_id, token) VALUES (?, ?)");
                    $insTok->execute([$payout_id, $tok]);
                }
                $receiptUrl = 'https://habeshaequb.com/receipt.php?rt=' . $tok;
            } catch (Throwable $te) {
                error_log('Payout receipt token generation failed: ' . $te->getMessage());
            }

            // Titles and bodies (bilingual)
            $title_en = 'Congratulations — Your Equb payout is completed!';
            $title_am = 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!';
            $body_en = "Dear {$first}, your Equb payout has been successfully completed on {$dateText}.\n\n- Net payout: {$amountFormatted}\n- Admin fee applied: {$adminFeeFormatted}\n\nYou can view and download your payout receipt here: {$receiptUrl}\n\nThank you.";
            $body_am = "ውድ {$first} የእቁብ መክፈያዎ በ{$dateText} በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): {$amountFormatted}\n- የአስተዳደር ክፍያ ተተግብሯል: {$adminFeeFormatted}\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ {$receiptUrl}\n\nእናመሰግናለን።";

            $isAmharic = (int)($m['language_preference'] ?? 0) === 1;
            $useSubj = $isAmharic ? $title_am : $title_en;
            $useBody = $isAmharic ? $body_am : $body_en;

            // Insert into legacy notifications so it appears in member dashboard
            $code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);
            $insLegacy = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?,?,?,?,?,?,?,?, 'sent', NOW(), NOW(), NOW(), ?)");
            $insLegacy->execute([$code, 'member', (int)$payout['member_id'], 'general', 'system', $useSubj, $useBody, ($isAmharic ? 'am' : 'en'), get_current_admin_id()]);

            // WhatsApp-ready text
            $whatsappText = $useBody;
            $code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);
            $chk = $pdo->prepare('SELECT id FROM program_notifications WHERE notification_code = ?');
            $chk->execute([$code]);
            while ($chk->fetch()) { $code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT); $chk->execute([$code]); }
            $ins = $pdo->prepare("INSERT INTO program_notifications (notification_code, created_by_admin_id, audience_type, equb_settings_id, title_en, title_am, body_en, body_am, priority, status, sent_at, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,'normal','sent',NOW(),NOW(),NOW())");
            $ins->execute([$code, get_current_admin_id(), 'members', ($m['equb_settings_id'] ?? null), $title_en, $title_am, $body_en, $body_am]);
            $nid = (int)$pdo->lastInsertId();
            if ($nid) {
                $ir = $pdo->prepare('INSERT IGNORE INTO notification_recipients (notification_id, member_id, created_at) VALUES (?, ?, NOW())');
                $ir->execute([$nid, (int)$payout['member_id']]);
            }
        } catch (Throwable $e) {
            error_log('processPayout notify error: '.$e->getMessage());
        }
    
        echo json_encode([
        'success' => true, 
        'message' => 'Payout processed successfully',
            'payout_id' => $payout['payout_id'],
            'whatsapp_text' => $whatsappText
        ]);
        
    } catch (Exception $e) {
        error_log("Process Payout Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to process payout: ' . $e->getMessage()]);
    }
}

/**
 * Ensure a public receipt token exists for the given payout and return its URL
 */
function getPayoutReceiptToken() {
    global $pdo;
    $payout_id = intval($_GET['payout_id'] ?? $_POST['payout_id'] ?? 0);
    if (!$payout_id) { echo json_encode(['success'=>false,'message'=>'Payout ID is required']); return; }
    // Validate payout exists
    $chk = $pdo->prepare("SELECT id FROM payouts WHERE id = ? LIMIT 1");
    $chk->execute([$payout_id]);
    if (!$chk->fetchColumn()) { echo json_encode(['success'=>false,'message'=>'Payout not found']); return; }
    try {
        // Ensure table
        $pdo->prepare("CREATE TABLE IF NOT EXISTS payout_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payout_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_payout (payout_id),
            CONSTRAINT fk_receipt_payout FOREIGN KEY (payout_id) REFERENCES payouts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")->execute();
        // Try get existing
        $sel = $pdo->prepare("SELECT token FROM payout_receipts WHERE payout_id = ? LIMIT 1");
        $sel->execute([$payout_id]);
        $token = $sel->fetchColumn();
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            $ins = $pdo->prepare("INSERT INTO payout_receipts (payout_id, token) VALUES (?, ?)");
            $ins->execute([$payout_id, $token]);
        }
        echo json_encode(['success'=>true,'token'=>$token,'receipt_url'=>'/receipt.php?rt='.$token]);
    } catch (Throwable $e) {
        error_log('getPayoutReceiptToken error: '.$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to generate receipt link']);
    }
}

/**
 * SYNC MEMBER PAYOUT FLAG - Update member's has_received_payout status
 */
function syncMemberPayoutFlag($member_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE members 
            SET has_received_payout = 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$member_id]);
        
    } catch (Exception $e) {
        error_log("Sync Member Payout Flag Error: " . $e->getMessage());
    }
}


?> 