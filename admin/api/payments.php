<?php
/**
 * HabeshaEqub - Payments API
 * Handle all payment-related CRUD operations and management
 */

require_once '../../includes/db.php';

// Set JSON header
header('Content-Type: application/json');

// SECURITY FIX: Use standardized admin authentication
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
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
            addPayment();
            break;
        case 'get':
            getPayment();
            break;
        case 'update':
            updatePayment();
            break;
        case 'delete':
            deletePayment();
            break;
        case 'verify':
            verifyPayment();
            break;
        case 'list':
            listPayments();
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
    error_log("Payment API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}

/**
 * Add new payment
 */
function addPayment() {
    global $pdo, $admin_id;
    
    // SECURITY FIX: Validate required fields with strict financial validation
    $member_id = intval($_POST['member_id'] ?? 0);
    $amount_input = $_POST['amount'] ?? '';
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_month = $_POST['payment_month'] ?? '';
    
    if (!$member_id || !$amount_input || !$payment_month) {
        echo json_encode(['success' => false, 'message' => 'Member, amount, and payment month are required']);
        return;
    }
    
    // Payment date is optional - use current date if not provided
    if (empty($payment_date)) {
        $payment_date = date('Y-m-d');
    }
    
    // SECURITY FIX: Strict financial validation
    if (!is_numeric($amount_input)) {
        echo json_encode(['success' => false, 'message' => 'Amount must be a valid number']);
        return;
    }
    
    $amount = round(floatval($amount_input), 2); // Round to 2 decimal places
    
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        return;
    }
    
    if ($amount > 999999.99) {
        echo json_encode(['success' => false, 'message' => 'Amount exceeds maximum allowed limit']);
        return;
    }
    
    // Validate member exists and is active
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM members WHERE id = ? AND is_active = 1");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive member selected']);
        return;
    }
    
    // ENHANCED: Ensure payment_month is in proper YYYY-MM-DD format for database
    if (strlen($payment_month) === 7 && preg_match('/^\d{4}-\d{2}$/', $payment_month)) {
        // Convert YYYY-MM to YYYY-MM-01 for database storage
        $payment_month = $payment_month . '-01';
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment month must be in YYYY-MM format']);
        return;
    }
    
    // Check for duplicate payment (same member and month)
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE member_id = ? AND payment_month = ?");
    $stmt->execute([$member_id, $payment_month]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Payment for this member and month already exists']);
        return;
    }
    
    // Optional fields
    $payment_method = sanitize_input($_POST['payment_method'] ?? 'cash');
    $status = sanitize_input($_POST['status'] ?? 'pending');
    $receipt_number = sanitize_input($_POST['receipt_number'] ?? '');
    $late_fee = floatval($_POST['late_fee'] ?? 0);
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    // Generate payment ID
    $payment_id = generatePaymentId();
    
    // Insert payment
    $stmt = $pdo->prepare("
        INSERT INTO payments (
            payment_id, member_id, amount, payment_date, payment_month, 
            status, payment_method, receipt_number, late_fee, notes,
            verified_by_admin, verified_by_admin_id, verification_date,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    // Convert 'completed' to 'paid' to match ENUM values
    if ($status === 'completed') {
        $status = 'paid';
    }
    
    $verified_by_admin = ($status === 'paid') ? 1 : 0;
    $verified_by_admin_id = ($status === 'paid') ? $admin_id : null;
    $verification_date = ($status === 'paid') ? date('Y-m-d H:i:s') : null;
    
    $stmt->execute([
        $payment_id, $member_id, $amount, $payment_date, $payment_month,
        $status, $payment_method, $receipt_number, $late_fee, $notes,
        $verified_by_admin, $verified_by_admin_id, $verification_date
    ]);
    
    // Update member's total contributed if payment is paid
    if ($status === 'paid') {
        $stmt = $pdo->prepare("
            UPDATE members 
            SET total_contributed = total_contributed + ? 
            WHERE id = ?
        ");
        $stmt->execute([$amount, $member_id]);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payment added successfully',
        'payment_id' => $payment_id
    ]);
}

/**
 * Get payment details
 */
function getPayment() {
    global $pdo;
    
    $payment_id = intval($_GET['payment_id'] ?? 0);
    
    if (!$payment_id) {
        echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT p.*, m.id as member_db_id, m.first_name, m.last_name, m.member_id as member_code
        FROM payments p
        LEFT JOIN members m ON p.member_id = m.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        return;
    }
    
    echo json_encode(['success' => true, 'payment' => $payment]);
}

/**
 * Update payment
 */
function updatePayment() {
    global $pdo, $admin_id;
    

    
    $payment_id = intval($_POST['payment_id'] ?? 0);
    $member_id = intval($_POST['member_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_month = $_POST['payment_month'] ?? '';
    
    // ENHANCED: Ensure payment_month is in proper YYYY-MM-DD format for database
    if (strlen($payment_month) === 7 && preg_match('/^\d{4}-\d{2}$/', $payment_month)) {
        // Convert YYYY-MM to YYYY-MM-01 for database storage
        $payment_month = $payment_month . '-01';
    } elseif (strlen($payment_month) === 10 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $payment_month)) {
        // Already in YYYY-MM-DD format, keep as is
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment month must be in YYYY-MM format']);
        return;
    }
    
    if (!$payment_id || !$member_id || !$amount || !$payment_month) {
        echo json_encode(['success' => false, 'message' => 'Payment ID, member, amount, and payment month are required']);
        return;
    }
    
    // Payment date is optional - use current date if not provided
    if (empty($payment_date)) {
        $payment_date = date('Y-m-d');
    }
    
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        return;
    }
    
    // Get current payment data
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $current_payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        return;
    }
    
    // Validate member exists and is active
    $stmt = $pdo->prepare("SELECT id FROM members WHERE id = ? AND is_active = 1");
    $stmt->execute([$member_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive member selected']);
        return;
    }
    
    // Check for duplicate payment (same member and month, excluding current payment)
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE member_id = ? AND payment_month = ? AND id != ?");
    $stmt->execute([$member_id, $payment_month, $payment_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Payment for this member and month already exists']);
        return;
    }
    
    // Optional fields
    $payment_method = sanitize_input($_POST['payment_method'] ?? 'cash');
    $status = sanitize_input($_POST['status'] ?? 'pending');
    $receipt_number = sanitize_input($_POST['receipt_number'] ?? '');
    $late_fee = floatval($_POST['late_fee'] ?? 0);
    $notes = sanitize_input($_POST['notes'] ?? '');
    

    
    // Handle verification status changes  
    // Convert 'completed' to 'paid' to match ENUM values
    if ($status === 'completed') {
        $status = 'paid';
    }
    
    $verified_by_admin = ($status === 'paid') ? 1 : 0;
    $verified_by_admin_id = ($status === 'paid') ? $admin_id : null;
    $verification_date = ($status === 'paid') ? date('Y-m-d H:i:s') : null;
    

    
    // Update payment
    $stmt = $pdo->prepare("
        UPDATE payments SET 
            member_id = ?, amount = ?, payment_date = ?, payment_month = ?,
            status = ?, payment_method = ?, receipt_number = ?, late_fee = ?, notes = ?,
            verified_by_admin = ?, verified_by_admin_id = ?, verification_date = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $member_id, $amount, $payment_date, $payment_month,
        $status, $payment_method, $receipt_number, $late_fee, $notes,
        $verified_by_admin, $verified_by_admin_id, $verification_date,
        $payment_id
    ]);
    
    // Update member's total contributed based on status changes
    $old_amount = ($current_payment['status'] === 'paid') ? $current_payment['amount'] : 0;
    $new_amount = ($status === 'paid') ? $amount : 0;
    $contribution_change = $new_amount - $old_amount;
    
    if ($contribution_change != 0) {
        $stmt = $pdo->prepare("
            UPDATE members 
            SET total_contributed = total_contributed + ? 
            WHERE id = ?
        ");
        $stmt->execute([$contribution_change, $member_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Payment updated successfully']);
}

/**
 * Delete payment
 */
function deletePayment() {
    global $pdo;
    
    $payment_id = intval($_POST['payment_id'] ?? 0);
    
    if (!$payment_id) {
        echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
        return;
    }
    
    // Get payment details before deletion
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        return;
    }
    
    // Delete payment
    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    
    // Update member's total contributed if payment was paid
    if ($payment['status'] === 'paid') {
        $stmt = $pdo->prepare("
            UPDATE members 
            SET total_contributed = total_contributed - ? 
            WHERE id = ?
        ");
        $stmt->execute([$payment['amount'], $payment['member_id']]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
}

/**
 * Verify payment (mark as completed)
 */
function verifyPayment() {
    global $pdo, $admin_id;
    
    $payment_id = intval($_POST['payment_id'] ?? 0);
    
    if (!$payment_id) {
        echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
        return;
    }
    
    // Get current payment
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        return;
    }
    
    if ($payment['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => 'Payment is already verified']);
        return;
    }
    
    // Update payment status to paid
    $stmt = $pdo->prepare("
        UPDATE payments SET 
            status = 'paid',
            verified_by_admin = 1,
            verified_by_admin_id = ?,
            verification_date = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$admin_id, $payment_id]);
    
    // Update member's total contributed
    $stmt = $pdo->prepare("
        UPDATE members 
        SET total_contributed = total_contributed + ? 
        WHERE id = ?
    ");
    $stmt->execute([$payment['amount'], $payment['member_id']]);
    // Notification options
    $send_notif = isset($_POST['send_notif']) ? (int)$_POST['send_notif'] : 0;
    $send_email_copy = isset($_POST['send_email_copy']) ? (int)$_POST['send_email_copy'] : 0;
    $export_whatsapp = isset($_POST['export_whatsapp']) ? (int)$_POST['export_whatsapp'] : 0;

    $whatsappText = '';
    $receiptUrl = '';
    if ($send_notif) {
        try {
            // Member basics
            $stmt = $pdo->prepare("SELECT m.id, m.first_name, m.last_name, m.language_preference, m.email, m.is_active, m.is_approved, m.email_notifications FROM members m WHERE m.id = ? LIMIT 1");
            $stmt->execute([$payment['member_id']]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $memberFirst = trim($member['first_name'] ?? '');
            $monthText = '';
            if (!empty($payment['payment_month']) && $payment['payment_month'] !== '0000-00-00') {
                $monthText = date('F Y', strtotime($payment['payment_month']));
            } elseif (!empty($payment['payment_date']) && $payment['payment_date'] !== '0000-00-00') {
                $monthText = date('F Y', strtotime($payment['payment_date']));
            }
            $dateText = (!empty($payment['payment_date']) && $payment['payment_date'] !== '0000-00-00') ? date('F j, Y', strtotime($payment['payment_date'])) : date('F j, Y');
            $amountFormatted = '£' . number_format((float)$payment['amount'], 2);
            // Ensure unique receipt token and URL
            try {
                $token = bin2hex(random_bytes(16));
                $pdo->prepare("CREATE TABLE IF NOT EXISTS payment_receipts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    payment_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_payment (payment_id),
                    CONSTRAINT fk_receipt_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")->execute();
                $insTok = $pdo->prepare("INSERT INTO payment_receipts (payment_id, token) VALUES (?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token)");
                $insTok->execute([$payment_id, $token]);
                $receiptUrl = 'https://habeshaequb.com/receipt.php?rt=' . $token;
            } catch (Throwable $te) {
                error_log('Receipt token generation failed: ' . $te->getMessage());
            }
            $isAmharic = (int)($member['language_preference'] ?? 0) === 1;

            // Title as month-specific confirmation
            $subject_en = ($monthText ? ($monthText . ' Payment Confirmation') : 'Payment Confirmation');
            $subject_am = ($monthText ? ('የ ' . $monthText . ' ወር ክፍያ ማረጋገጫ') : 'የክፍያ ማረጋገጫ');
            // No generic dashboard link to avoid spam flags. Include unique receipt link only.
            $body_en = "Dear {$memberFirst}, you have successfully paid this month's contribution for {$monthText} on {$dateText}.\n\nAmount paid: {$amountFormatted}.\n\nDownload your receipt: {$receiptUrl}\n\nThanks for your payment.";
            $body_am = "ውድ {$memberFirst} ሆይ፣ የዚህ ወር ክፍያዎ ለ{$monthText} በ{$dateText} ተረጋግጧል።\n\nየከፈሉት መጠን: {$amountFormatted}።\n\nደረሰኝዎን ያውርዱ፡ {$receiptUrl}\n\nእናመሰግናለን።";
            $useSubj = $isAmharic ? $subject_am : $subject_en;
            $useBody = $isAmharic ? $body_am : $body_en;

            // Insert into legacy notifications table so the member sees it in app
            $code = 'NTF-' . date('Ymd') . '-' . str_pad((string)rand(1,999),3,'0',STR_PAD_LEFT);
            $ins = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?,?,?,?,?,?,?,?, 'sent', NOW(), NOW(), NOW(), ?)");
            $ins->execute([$code, 'member', (int)$payment['member_id'], 'general', ($send_email_copy? 'both':'email'), $useSubj, $useBody, ($isAmharic ? 'am' : 'en'), $admin_id]);

            // Optional email copy
            if ($send_email_copy && (int)($member['is_active'] ?? 0) === 1 && (int)($member['is_approved'] ?? 0) === 1 && (int)($member['email_notifications'] ?? 1) === 1 && !empty($member['email'])) {
                require_once '../../includes/email/EmailService.php';
                $mailer = new EmailService($pdo);
                $mailer->sendProgramNotificationToMember($member, $subject_en, $subject_am, $body_en, $body_am);
            }

            if ($export_whatsapp) { $whatsappText = $useBody; }
        } catch (Throwable $e) {
            error_log('verifyPayment notif build error: '.$e->getMessage());
        }
    }

    echo json_encode(['success' => true, 'message' => 'Payment verified successfully', 'whatsapp_text' => $whatsappText]);
    return;
}

/**
 * List payments with filters
 */
function listPayments() {
    global $pdo;
    
    // Get filter parameters
    $search = sanitize_input($_GET['search'] ?? '');
    $status = sanitize_input($_GET['status'] ?? '');
    $member_id = intval($_GET['member_id'] ?? 0);
    $month = sanitize_input($_GET['month'] ?? '');
    
    // Build query
    $query = "
        SELECT p.*, 
               m.id as member_db_id, m.first_name, m.last_name, m.member_id as member_code, m.email,
               va.username as verified_by_name
        FROM payments p 
        LEFT JOIN members m ON p.member_id = m.id
        LEFT JOIN admins va ON p.verified_by_admin_id = va.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Apply filters
    if ($search) {
        $query .= " AND (
            m.first_name LIKE ? OR 
            m.last_name LIKE ? OR 
            p.payment_id LIKE ? OR 
            p.amount LIKE ?
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
    
    if ($month) {
        $query .= " AND p.payment_month LIKE ?";
        $params[] = "$month%";
    }
    
    $query .= " ORDER BY p.payment_date DESC, p.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    
    echo json_encode(['success' => true, 'payments' => $payments]);
}

/**
 * Generate unique payment ID
 */
function generatePaymentId() {
    global $pdo;
    
    do {
        // Format: HEP-YYYYMMDD-XXX (HabeshaEqub Payment)
        $payment_id = 'HEP-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Check if ID already exists
        $stmt = $pdo->prepare("SELECT id FROM payments WHERE payment_id = ?");
        $stmt->execute([$payment_id]);
        $exists = $stmt->fetch();
    } while ($exists);
    
    return $payment_id;
}
?> 