<?php
/**
 * HabeshaEqub - Payment Details Page
 * Detailed view of a single payment with receipt download
 */

// FORCE NO CACHING
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure authentication check
require_once 'includes/auth_guard.php';
$user_id = get_current_user_id();

// Get payment ID from URL
$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$payment_id) {
    header('Location: dashboard.php');
    exit;
}

// Fetch payment details - verify it belongs to the logged-in user
try {
    $stmt = $pdo->prepare("
        SELECT p.*,
               m.first_name,
               m.last_name,
               m.member_id as member_code,
               CASE 
                   WHEN p.payment_date IS NOT NULL AND p.payment_date != '0000-00-00' 
                   THEN DATE_FORMAT(p.payment_date, '%M %d, %Y') 
                   ELSE DATE_FORMAT(p.created_at, '%M %d, %Y')
               END as formatted_date,
               CASE 
                   WHEN p.payment_month IS NOT NULL AND p.payment_month != '0000-00-00' 
                   THEN DATE_FORMAT(p.payment_month, '%M %Y') 
                   WHEN p.payment_date IS NOT NULL AND p.payment_date != '0000-00-00'
                   THEN DATE_FORMAT(p.payment_date, '%M %Y')
                   ELSE DATE_FORMAT(p.created_at, '%M %Y')
               END as payment_month_name,
               CASE 
                   WHEN p.verified_by_admin = 1 THEN 'verified'
                   WHEN p.verified_by_admin = 0 AND p.status = 'paid' THEN 'pending_verification'
                   ELSE 'not_verified'
               END as verification_status,
               DATE_FORMAT(p.created_at, '%M %d, %Y at %h:%i %p') as created_at_formatted,
               DATE_FORMAT(COALESCE(p.verification_date, p.updated_at), '%M %d, %Y at %h:%i %p') as verification_date_formatted
        FROM payments p
        LEFT JOIN members m ON p.member_id = m.id
        WHERE p.id = ? AND p.member_id = ?
    ");
    $stmt->execute([$payment_id, $user_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        header('Location: dashboard.php?error=payment_not_found');
        exit;
    }
} catch (PDOException $e) {
    error_log("Payment details error: " . $e->getMessage());
    header('Location: dashboard.php?error=payment_error');
    exit;
}

// Get receipt token if available
$receipt_url = '';
try {
    $stmt = $pdo->prepare("SELECT token FROM payment_receipts WHERE payment_id = ? LIMIT 1");
    $stmt->execute([$payment_id]);
    $token = $stmt->fetchColumn();
    if ($token) {
        $receipt_url = 'https://habeshaequb.com/receipt.php?rt=' . $token;
    }
} catch (PDOException $e) {
    error_log("Receipt token error: " . $e->getMessage());
}

$cache_buster = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('member_dashboard.payment_details'); ?> - HabeshaEqub</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* === PROFESSIONAL MOBILE-FIRST PAYMENT DETAILS DESIGN === */
        
        :root {
            --palette-cream: #F1ECE2;
            --palette-dark-purple: #4D4052;
            --palette-deep-purple: #301934;
            --palette-gold: #DAA520;
            --palette-light-gold: #CDAF56;
            --palette-success: #2A9D8F;
            --palette-light-bg: #FAFAFA;
            --palette-border: rgba(77, 64, 82, 0.1);
        }
        
        body {
            background: var(--palette-light-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--palette-dark-purple);
            padding-bottom: 80px;
        }
        
        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--palette-cream) 0%, #FAF8F5 100%);
            border-radius: 0 0 20px 20px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(48, 25, 52, 0.08);
        }
        
        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--palette-deep-purple);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .back-btn {
            background: white;
            border: 1px solid var(--palette-border);
            border-radius: 10px;
            padding: 8px 16px;
            color: var(--palette-dark-purple);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .back-btn:hover {
            background: var(--palette-cream);
            transform: translateX(-2px);
            color: var(--palette-deep-purple);
        }
        
        /* Payment Card */
        .payment-detail-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(48, 25, 52, 0.06);
            border: 1px solid var(--palette-border);
        }
        
        .payment-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--palette-light-bg);
        }
        
        .payment-amount {
            font-size: 36px;
            font-weight: 700;
            color: var(--palette-success);
            margin: 0;
        }
        
        .payment-status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Detail Rows */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--palette-light-bg);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--palette-dark-purple);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-value {
            color: var(--palette-dark-purple);
            font-size: 14px;
            text-align: right;
            flex: 1;
            margin-left: 16px;
        }
        
        .detail-value code {
            background: var(--palette-light-bg);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
            color: var(--palette-deep-purple);
        }
        
        /* Receipt Section */
        .receipt-section {
            background: linear-gradient(135deg, rgba(42, 157, 143, 0.05) 0%, rgba(42, 157, 143, 0.02) 100%);
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            border: 2px dashed rgba(42, 157, 143, 0.2);
            text-align: center;
        }
        
        .receipt-btn {
            background: var(--palette-success);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .receipt-btn:hover {
            background: #238a7d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 157, 143, 0.3);
            color: white;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 20px;
            }
            
            .payment-amount {
                font-size: 28px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .detail-value {
                text-align: left;
                margin-left: 0;
                width: 100%;
            }
            
            .payment-detail-header {
                flex-direction: column;
                gap: 16px;
            }
        }
        
        /* Status Colors */
        .status-verified {
            background: rgba(42, 157, 143, 0.1);
            color: var(--palette-success);
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ff9800;
        }
        
        .status-paid {
            background: rgba(42, 157, 143, 0.1);
            color: var(--palette-success);
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <a href="dashboard.php" class="back-btn mb-3">
                <i class="fas fa-arrow-left"></i>
                <?php echo t('common.back'); ?>
            </a>
            <h1>
                <i class="fas fa-receipt text-primary"></i>
                <?php echo t('member_dashboard.payment_details'); ?>
            </h1>
        </div>
    </div>
    
    <div class="container">
        <!-- Payment Details Card -->
        <div class="payment-detail-card">
            <div class="payment-detail-header">
                <div>
                    <p class="payment-amount">£<?php echo number_format((float)$payment['amount'], 2); ?></p>
                    <p class="text-muted mb-0" style="font-size: 14px;"><?php echo htmlspecialchars($payment['payment_month_name']); ?></p>
                </div>
                <div>
                    <span class="payment-status-badge status-<?php echo $payment['status'] === 'paid' ? 'paid' : 'pending'; ?>">
                        <?php if ($payment['status'] === 'paid'): ?>
                            <i class="fas fa-check-circle me-1"></i><?php echo t('member_dashboard.paid'); ?>
                        <?php else: ?>
                            <i class="fas fa-clock me-1"></i><?php echo t('member_dashboard.pending'); ?>
                        <?php endif; ?>
                    </span>
                    <?php if ($payment['verification_status'] === 'verified'): ?>
                        <br><small class="status-verified payment-status-badge mt-2 d-inline-block">
                            <i class="fas fa-check-circle me-1"></i><?php echo t('member_dashboard.verified'); ?>
                        </small>
                    <?php elseif ($payment['verification_status'] === 'pending_verification'): ?>
                        <br><small class="status-pending payment-status-badge mt-2 d-inline-block">
                            <i class="fas fa-clock me-1"></i><?php echo t('member_dashboard.pending'); ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Payment Details -->
            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-hashtag text-muted"></i>
                        <?php echo t('member_dashboard.payment_id'); ?>
                    </span>
                    <span class="detail-value">
                        <code><?php echo htmlspecialchars($payment['payment_id']); ?></code>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        <?php echo t('member_dashboard.payment_month'); ?>
                    </span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($payment['payment_month_name']); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar-check text-muted"></i>
                        <?php echo t('member_dashboard.date_paid'); ?>
                    </span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($payment['formatted_date']); ?>
                    </span>
                </div>
                
                <?php if (!empty($payment['payment_method'])): ?>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-credit-card text-muted"></i>
                        <?php echo t('contributions.payment_method'); ?>
                    </span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-pound-sign text-muted"></i>
                        <?php echo t('member_dashboard.amount'); ?>
                    </span>
                    <span class="detail-value text-success fw-bold">
                        £<?php echo number_format((float)$payment['amount'], 2); ?>
                    </span>
                </div>
                
                <?php if ((float)$payment['late_fee'] > 0): ?>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <?php echo t('contributions.late_fee'); ?>
                    </span>
                    <span class="detail-value text-warning fw-bold">
                        £<?php echo number_format((float)$payment['late_fee'], 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-info-circle text-muted"></i>
                        <?php echo t('contributions.status'); ?>
                    </span>
                    <span class="detail-value">
                        <span class="payment-status-badge status-<?php echo $payment['status'] === 'paid' ? 'paid' : 'pending'; ?>">
                            <?php echo $payment['status'] === 'paid' ? t('member_dashboard.paid') : t('member_dashboard.pending'); ?>
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-shield-check text-muted"></i>
                        <?php echo t('contributions.verification'); ?>
                    </span>
                    <span class="detail-value">
                        <?php if ($payment['verification_status'] === 'verified'): ?>
                            <span class="status-verified payment-status-badge">
                                <i class="fas fa-check-circle me-1"></i><?php echo t('member_dashboard.verified'); ?>
                            </span>
                        <?php elseif ($payment['verification_status'] === 'pending_verification'): ?>
                            <span class="status-pending payment-status-badge">
                                <i class="fas fa-clock me-1"></i><?php echo t('member_dashboard.pending'); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Not Verified</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <?php if (!empty($payment['created_at_formatted'])): ?>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-clock text-muted"></i>
                        Created At
                    </span>
                    <span class="detail-value text-muted">
                        <?php echo htmlspecialchars($payment['created_at_formatted']); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($payment['verification_status'] === 'verified' && !empty($payment['verification_date_formatted'])): ?>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-check-double text-success"></i>
                        Verified At
                    </span>
                    <span class="detail-value text-success">
                        <?php echo htmlspecialchars($payment['verification_date_formatted']); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Receipt Section -->
            <?php if ($receipt_url && $payment['status'] === 'paid'): ?>
            <div class="receipt-section">
                <h5 class="mb-3">
                    <i class="fas fa-file-invoice text-success me-2"></i>
                    Receipt Available
                </h5>
                <p class="text-muted mb-4">Click the button below to view and download your payment receipt.</p>
                <a href="<?php echo htmlspecialchars($receipt_url); ?>" target="_blank" class="receipt-btn">
                    <i class="fas fa-download"></i>
                    <?php echo t('contributions.view_receipt'); ?>
                </a>
            </div>
            <?php elseif ($payment['status'] === 'paid'): ?>
            <div class="receipt-section">
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Receipt will be available once payment is verified.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

