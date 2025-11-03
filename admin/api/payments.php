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
        case 'verify_with_notification':
            verifyWithNotification();
            break;
        case 'list':
            listPayments();
            break;
        case 'get_receipt_token':
            getReceiptToken();
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
            } else {
                $monthText = date('F Y');
            }
            $dateText = (!empty($payment['payment_date']) && $payment['payment_date'] !== '0000-00-00') ? date('F j, Y', strtotime($payment['payment_date'])) : date('F j, Y', strtotime($payment['created_at'] ?? 'now'));
            $amountFormatted = '£' . number_format((float)$payment['amount'], 2);
            // Ensure unique receipt token and URL
            $receiptUrl = '';
            try {
                // Create table if not exists (only once)
                $createTableSQL = "CREATE TABLE IF NOT EXISTS payment_receipts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    payment_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_payment (payment_id),
                    CONSTRAINT fk_receipt_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                $pdo->exec($createTableSQL);
                
                // Delete any existing receipt for this payment to avoid duplicates
                $delStmt = $pdo->prepare("DELETE FROM payment_receipts WHERE payment_id = ?");
                $delStmt->execute([$payment_id]);
                
                // Generate new token and insert
                $token = bin2hex(random_bytes(16));
                error_log("Generated token: $token (length: " . strlen($token) . ", encoding: " . mb_detect_encoding($token) . ")");
                
                $insStmt = $pdo->prepare("INSERT INTO payment_receipts (payment_id, token) VALUES (?, ?)");
                $insStmt->execute([$payment_id, $token]);
                
                // Verify the token was inserted
                $verifyStmt = $pdo->prepare("SELECT token FROM payment_receipts WHERE payment_id = ? AND token = ?");
                $verifyStmt->execute([$payment_id, $token]);
                $verifiedToken = $verifyStmt->fetchColumn();
                
                if ($verifiedToken) {
                    $receiptUrl = 'https://habeshaequb.com/receipt.php?rt=' . $token;
                    error_log("✓ Receipt token VERIFIED in DB: $token for payment $payment_id");
                    error_log("Receipt URL: $receiptUrl");
                } else {
                    error_log("✗ Receipt token NOT FOUND in DB for payment $payment_id");
                    $receiptUrl = 'https://habeshaequb.com/user/dashboard.php';
                }
                
            } catch (Throwable $te) {
                error_log('Receipt token generation failed: ' . $te->getMessage() . ' - ' . $te->getTraceAsString());
                $receiptUrl = 'https://habeshaequb.com/user/dashboard.php'; // Fallback URL
            }
            $isAmharic = (int)($member['language_preference'] ?? 0) === 1;

            // Title as month-specific confirmation
            $subject_en = 'Payment Confirmation - ' . $monthText;
            $subject_am = 'Payment Confirmation - ' . $monthText; // Keep English for deliverability
            
            // Clean, simple email body in English only to avoid spam filters
            $body_en = "Dear {$memberFirst},\n\n";
            $body_en .= "Your payment for {$monthText} has been successfully confirmed.\n\n";
            $body_en .= "Payment Date: {$dateText}\n";
            $body_en .= "Amount: {$amountFormatted}\n\n";
            $body_en .= "View your receipt: {$receiptUrl}\n\n";
            $body_en .= "Thank you,\nHabeshaEqub Team\n\n";
            $body_en .= "---\n\n";
            $body_en .= "Need more information about the equb?\n";
            $body_en .= "Login to your portal to view:\n";
            $body_en .= "• Your payment history\n";
            $body_en .= "• Payout schedule and positions\n";
            $body_en .= "• Member contributions\n";
            $body_en .= "• Equb rules and updates\n\n";
            $body_en .= "Member Portal: https://habeshaequb.com/user/login.php";
            
            // Same clean format for Amharic
            $body_am = "Dear {$memberFirst},\n\n";
            $body_am .= "Your payment for {$monthText} has been successfully confirmed.\n\n";
            $body_am .= "Payment Date: {$dateText}\n";
            $body_am .= "Amount: {$amountFormatted}\n\n";
            $body_am .= "View your receipt: {$receiptUrl}\n\n";
            $body_am .= "Thank you,\nHabeshaEqub Team\n\n";
            $body_am .= "---\n\n";
            $body_am .= "Need more information about the equb?\n";
            $body_am .= "Login to your portal to view:\n";
            $body_am .= "• Your payment history\n";
            $body_am .= "• Payout schedule and positions\n";
            $body_am .= "• Member contributions\n";
            $body_am .= "• Equb rules and updates\n\n";
            $body_am .= "Member Portal: https://habeshaequb.com/user/login.php";
            
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
 * Verify payment with enhanced multi-channel notifications
 */
function verifyWithNotification() {
    global $pdo, $admin_id;
    
    $payment_id = intval($_POST['payment_id'] ?? 0);
    $channels_json = $_POST['channels'] ?? '[]';
    $title_en = trim($_POST['title_en'] ?? '');
    $title_am = trim($_POST['title_am'] ?? '');
    $body_en = trim($_POST['body_en'] ?? '');
    $body_am = trim($_POST['body_am'] ?? '');
    
    if (!$payment_id) {
        echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
        return;
    }
    
    if (empty($title_en) || empty($body_en)) {
        echo json_encode(['success' => false, 'message' => 'Title and message are required']);
        return;
    }
    
    $channels = json_decode($channels_json, true);
    if (!is_array($channels) || empty($channels)) {
        echo json_encode(['success' => false, 'message' => 'At least one channel must be selected']);
        return;
    }
    
    // Get payment data
    $stmt = $pdo->prepare("SELECT p.*, m.id as member_db_id, m.first_name, m.last_name, m.member_id, m.email, m.phone, m.language_preference, m.is_active, m.is_approved, m.email_notifications, m.sms_notifications FROM payments p LEFT JOIN members m ON p.member_id = m.id WHERE p.id = ?");
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
    
    // Verify payment
    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', verified_by_admin = 1, verified_by_admin_id = ?, verification_date = NOW(), updated_at = NOW() WHERE id = ?");
    $stmt->execute([$admin_id, $payment_id]);
    
    // Update member's total contributed (use p.member_id which is the database ID)
    $stmt = $pdo->prepare("UPDATE members SET total_contributed = total_contributed + ? WHERE id = ?");
    $stmt->execute([$payment['amount'], $payment['member_db_id']]);
    
    // Generate receipt link (same method as user portal - reuse existing token or create new)
    $receiptUrl = '';
    try {
        // Ensure table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS payment_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_payment (payment_id),
            CONSTRAINT fk_receipt_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Check if token already exists (reuse it instead of deleting)
        $selStmt = $pdo->prepare("SELECT token FROM payment_receipts WHERE payment_id = ? LIMIT 1");
        $selStmt->execute([$payment_id]);
        $token = $selStmt->fetchColumn();
        
        if (!$token) {
            // Create new token if none exists
            $token = bin2hex(random_bytes(16));
            $insStmt = $pdo->prepare("INSERT INTO payment_receipts (payment_id, token) VALUES (?, ?)");
            $insStmt->execute([$payment_id, $token]);
            error_log("Created new receipt token for payment $payment_id: $token");
        } else {
            error_log("Reusing existing receipt token for payment $payment_id: $token");
        }
        
        // Verify token was saved correctly
        $verifyStmt = $pdo->prepare("SELECT token FROM payment_receipts WHERE payment_id = ? AND token = ? LIMIT 1");
        $verifyStmt->execute([$payment_id, $token]);
        $verifiedToken = $verifyStmt->fetchColumn();
        
        if ($verifiedToken && $verifiedToken === $token) {
            // Use shorter URL format (receipt.php handles token lookup)
            $receiptUrl = 'https://habeshaequb.com/receipt.php?rt=' . $token;
            error_log("✓ Receipt URL generated successfully for payment $payment_id: $receiptUrl");
        } else {
            // If verification fails, try one more time to get/create token
            error_log("⚠ Token verification failed, retrying for payment $payment_id");
            $retrySel = $pdo->prepare("SELECT token FROM payment_receipts WHERE payment_id = ? LIMIT 1");
            $retrySel->execute([$payment_id]);
            $retryToken = $retrySel->fetchColumn();
            
            if ($retryToken) {
                $receiptUrl = 'https://habeshaequb.com/receipt.php?rt=' . $retryToken;
                error_log("✓ Receipt URL generated on retry: $receiptUrl");
            } else {
                error_log("✗ Failed to generate receipt token for payment $payment_id");
                // Fallback: direct to contributions page (they can access receipt there)
                $receiptUrl = 'https://habeshaequb.com/user/contributions.php';
            }
        }
    } catch (Throwable $e) {
        error_log('Receipt generation error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        $receiptUrl = 'https://habeshaequb.com/user/contributions.php';
    }
    
    // Replace variables in messages
    $vars = [
        '{first_name}' => $payment['first_name'] ?? '',
        '{last_name}' => $payment['last_name'] ?? '',
        '{member_id}' => $payment['member_id'] ?? '',
        '{amount}' => '£' . number_format((float)$payment['amount'], 2),
        '{payment_date}' => (!empty($payment['payment_date']) && $payment['payment_date'] !== '0000-00-00') 
            ? date('F j, Y', strtotime($payment['payment_date'])) 
            : date('F j, Y'),
        '{receipt_link}' => $receiptUrl
    ];
    
    foreach ($vars as $key => $value) {
        $title_en = str_replace($key, $value, $title_en);
        $title_am = str_replace($key, $value, $title_am);
        $body_en = str_replace($key, $value, $body_en);
        $body_am = str_replace($key, $value, $body_am);
    }
    
    // Prepare member data
    $member = [
        'id' => $payment['member_db_id'], // Use database ID, not member code
        'first_name' => $payment['first_name'],
        'last_name' => $payment['last_name'],
        'email' => $payment['email'],
        'phone' => $payment['phone'],
        'language_preference' => $payment['language_preference'],
        'is_active' => $payment['is_active'],
        'is_approved' => $payment['is_approved'],
        'email_notifications' => $payment['email_notifications'],
        'sms_notifications' => $payment['sms_notifications'] ?? 1,
        'member_id' => $payment['member_id'] // Member code (e.g., "HEM-001")
    ];
    
    // Track results
    $results = [
        'sms' => ['sent' => 0, 'failed' => 0],
        'email' => ['sent' => 0, 'failed' => 0],
        'in_app' => ['sent' => 0, 'failed' => 0]
    ];
    
    // Send notifications based on selected channels
    $notification_code = 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $now = date('Y-m-d H:i:s');
    
    // SMS
    if (in_array('sms', $channels) && !empty($member['phone']) && 
        (int)$member['is_active'] === 1 && (int)$member['is_approved'] === 1 && 
        (int)($member['sms_notifications'] ?? 1) === 1) {
        try {
            require_once '../../includes/sms/SmsService.php';
            $smsService = new SmsService($pdo);
            $smsResult = $smsService->sendProgramNotificationToMember($member, $title_en, $title_am, $body_en, $body_am);
            if (!empty($smsResult['success'])) {
                $results['sms']['sent'] = 1;
            } else {
                $results['sms']['failed'] = 1;
            }
        } catch (Throwable $e) {
            error_log('Payment SMS error: ' . $e->getMessage());
            $results['sms']['failed'] = 1;
        }
    }
    
    // Email
    if (in_array('email', $channels) && !empty($member['email']) && 
        (int)$member['is_active'] === 1 && (int)$member['is_approved'] === 1 && 
        (int)$member['email_notifications'] === 1) {
        try {
            require_once '../../includes/email/EmailService.php';
            $emailService = new EmailService($pdo);
            $emailResult = $emailService->sendProgramNotificationToMember($member, $title_en, $title_am, $body_en, $body_am);
            if (!empty($emailResult['success'])) {
                $results['email']['sent'] = 1;
            } else {
                $results['email']['failed'] = 1;
            }
        } catch (Throwable $e) {
            error_log('Payment Email error: ' . $e->getMessage());
            $results['email']['failed'] = 1;
        }
    }
    
    // In-app notification
    if (in_array('in_app', $channels)) {
        try {
            $isAmharic = (int)($member['language_preference'] ?? 0) === 1;
            $useTitle = $isAmharic ? $title_am : $title_en;
            $useBody = $isAmharic ? $body_am : $body_en;
            
            // Build channel list - always include 'in_app' since we're in this block
            $channelList = ['in_app'];
            if (in_array('sms', $channels)) $channelList[] = 'sms';
            if (in_array('email', $channels)) $channelList[] = 'email';
            $channelStr = implode(',', $channelList);
            
            $ins = $pdo->prepare("INSERT INTO notifications (notification_id, recipient_type, recipient_id, type, channel, subject, message, language, status, sent_at, created_at, updated_at, sent_by_admin_id) VALUES (?, 'member', ?, 'payment_reminder', ?, ?, ?, ?, 'sent', ?, ?, ?, ?)");
            $ins->execute([$notification_code, $member['id'], $channelStr, $useTitle, $useBody, ($isAmharic ? 'am' : 'en'), $now, $now, $now, $admin_id]);
            $results['in_app']['sent'] = 1;
        } catch (Throwable $e) {
            error_log('Payment In-app notification error: ' . $e->getMessage());
            $results['in_app']['failed'] = 1;
        }
    }
    
    // Build delivery report
    $report = [];
    if (in_array('sms', $channels)) {
        $report[] = "SMS: {$results['sms']['sent']} sent, {$results['sms']['failed']} failed";
    }
    if (in_array('email', $channels)) {
        $report[] = "Email: {$results['email']['sent']} sent, {$results['email']['failed']} failed";
    }
    if (in_array('in_app', $channels)) {
        $report[] = "In-App: {$results['in_app']['sent']} sent, {$results['in_app']['failed']} failed";
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified and notifications sent',
        'delivery_report' => implode("\n", $report),
        'results' => $results,
        'receipt_url' => $receiptUrl
    ]);
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
    
    // Get active equb settings to filter payments within equb period
    $equbStmt = $pdo->query("SELECT start_date, end_date FROM equb_settings WHERE status = 'active' LIMIT 1");
    $equbSettings = $equbStmt->fetch(PDO::FETCH_ASSOC);
    
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
    
    // Filter by equb period if active equb exists
    if ($equbSettings) {
        $query .= " AND p.payment_month >= ? AND p.payment_month <= ?";
        $params[] = $equbSettings['start_date'];
        $params[] = $equbSettings['end_date'];
    }
    
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
    
    $query .= " ORDER BY p.payment_month DESC, p.payment_date DESC, p.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unpaid members per month
    $unpaidMembers = getUnpaidMembersByMonth($month, $member_id);
    
    echo json_encode([
        'success' => true, 
        'payments' => $payments,
        'unpaid_members' => $unpaidMembers
    ]);
}

/**
 * Get members who haven't paid for each month
 */
function getUnpaidMembersByMonth($filterMonth = '', $filterMemberId = 0) {
    global $pdo;
    
    // Get active equb settings to determine payment months
    $equbStmt = $pdo->query("SELECT start_date, duration_months, end_date FROM equb_settings WHERE status = 'active' LIMIT 1");
    $equbSettings = $equbStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all active members
    $memberQuery = "SELECT id, member_id, first_name, last_name, monthly_payment, email FROM members WHERE is_active = 1";
    $memberParams = [];
    
    if ($filterMemberId) {
        $memberQuery .= " AND id = ?";
        $memberParams[] = $filterMemberId;
    }
    
    $memberStmt = $pdo->prepare($memberQuery);
    $memberStmt->execute($memberParams);
    $allMembers = $memberStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate months to check based on equb settings
    $monthsToCheck = [];
    
    if ($filterMonth) {
        // User filtered by specific month
        $monthsToCheck[] = $filterMonth;
    } else if ($equbSettings) {
        // Calculate months from equb start date to current date (or end date if passed)
        $startDate = new DateTime($equbSettings['start_date']);
        $endDate = new DateTime($equbSettings['end_date']);
        $currentDate = new DateTime();
        
        // Use the earlier of current date or end date
        $lastMonth = ($currentDate < $endDate) ? $currentDate : $endDate;
        
        // Generate all months from start to current/end
        $checkDate = clone $startDate;
        while ($checkDate <= $lastMonth) {
            $monthsToCheck[] = $checkDate->format('Y-m');
            $checkDate->modify('+1 month');
        }
        
        // Reverse to show most recent first
        $monthsToCheck = array_reverse($monthsToCheck);
    } else {
        // Fallback: if no active equb, show last 12 months
        for ($i = 0; $i < 12; $i++) {
            $monthsToCheck[] = date('Y-m', strtotime("-$i months"));
        }
    }
    
    $unpaidByMonth = [];
    
    foreach ($monthsToCheck as $monthKey) {
        $monthDate = $monthKey . '-01'; // Convert YYYY-MM to YYYY-MM-01
        
        // Get members who have paid for this month
        $paidQuery = "SELECT DISTINCT member_id FROM payments WHERE payment_month = ?";
        $paidStmt = $pdo->prepare($paidQuery);
        $paidStmt->execute([$monthDate]);
        $paidMemberIds = $paidStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Find unpaid members (members not in paid list)
        $unpaidForMonth = [];
        foreach ($allMembers as $member) {
            if (!in_array($member['id'], $paidMemberIds)) {
                $unpaidForMonth[] = [
                    'member_id' => $member['id'],
                    'member_code' => $member['member_id'],
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                    'monthly_payment' => $member['monthly_payment'],
                    'email' => $member['email'],
                    'month' => $monthKey
                ];
            }
        }
        
        if (!empty($unpaidForMonth)) {
            $unpaidByMonth[$monthKey] = $unpaidForMonth;
        }
    }
    
    return $unpaidByMonth;
}

/**
 * Ensure a public receipt token exists for the given payment and return its URL
 */
function getReceiptToken() {
    global $pdo;
    $payment_id = intval($_GET['payment_id'] ?? $_POST['payment_id'] ?? 0);
    if (!$payment_id) { echo json_encode(['success'=>false,'message'=>'Payment ID is required']); return; }
    // Validate payment exists
    $chk = $pdo->prepare("SELECT id FROM payments WHERE id = ? LIMIT 1");
    $chk->execute([$payment_id]);
    if (!$chk->fetchColumn()) { echo json_encode(['success'=>false,'message'=>'Payment not found']); return; }
    try {
        // Ensure table
        $pdo->prepare("CREATE TABLE IF NOT EXISTS payment_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_payment (payment_id),
            CONSTRAINT fk_receipt_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")->execute();
        // Try get existing
        $sel = $pdo->prepare("SELECT token FROM payment_receipts WHERE payment_id = ? LIMIT 1");
        $sel->execute([$payment_id]);
        $token = $sel->fetchColumn();
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            // Insert new mapping
            $ins = $pdo->prepare("INSERT INTO payment_receipts (payment_id, token) VALUES (?, ?)");
            $ins->execute([$payment_id, $token]);
        }
        echo json_encode(['success'=>true,'token'=>$token,'receipt_url'=>'/receipt.php?rt='.$token]);
    } catch (Throwable $e) {
        error_log('getReceiptToken error: '.$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to generate receipt link']);
    }
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