<?php
/**
 * HabeshaEqub - Member Profile Page
 * Comprehensive member information and management
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Get member ID from URL
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$member_id) {
    header('Location: members.php');
    exit;
}

// Get member details with all related information
try {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               es.equb_name, es.equb_id, es.start_date, es.duration_months, es.payout_day,
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.payout_position
                   ELSE m.payout_position
               END as actual_payout_position,
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                   ELSE m.monthly_payment
               END as effective_monthly_payment,
               jmg.group_name, jmg.payout_split_method, jmg.member_count as joint_member_count_actual,
               DATE_FORMAT(m.payout_month, '%Y-%m') as formatted_payout_month
        FROM members m 
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        WHERE m.id = ?
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        header('Location: members.php');
        exit;
    }
    
    // Calculate dynamic payout
    $calculator = getEnhancedEqubCalculator();
    $payout_calc = $calculator->calculateMemberFriendlyPayout($member['id']);
    if ($payout_calc['success']) {
        $member['expected_payout'] = $payout_calc['calculation']['display_payout'];
        $member['gross_payout'] = $payout_calc['calculation']['gross_payout'];
        $member['admin_fee'] = $payout_calc['calculation']['admin_fee'];
        $member['net_payout'] = $payout_calc['calculation']['real_net_payout'];
    } else {
        $member['expected_payout'] = 0;
        $member['gross_payout'] = 0;
        $member['admin_fee'] = 0;
        $member['net_payout'] = 0;
    }
    
} catch (PDOException $e) {
    error_log("Error fetching member: " . $e->getMessage());
    header('Location: members.php');
    exit;
}

// Get payment history
try {
    $stmt = $pdo->prepare("
        SELECT p.*, a.username as verified_by_name
        FROM payments p
        LEFT JOIN admins a ON p.verified_by_admin_id = a.id
        WHERE p.member_id = ?
        ORDER BY p.payment_month DESC
        LIMIT 10
    ");
    $stmt->execute([$member_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching payments: " . $e->getMessage());
    $payments = [];
}

// Get payout history
try {
    $stmt = $pdo->prepare("
        SELECT p.*, a.username as processed_by_name
        FROM payouts p
        LEFT JOIN admins a ON p.processed_by_admin_id = a.id
        WHERE p.member_id = ?
        ORDER BY p.scheduled_date DESC
    ");
    $stmt->execute([$member_id]);
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching payouts: " . $e->getMessage());
    $payouts = [];
}

// Get swap requests
try {
    $stmt = $pdo->prepare("
        SELECT psr.*, 
               tm.first_name as target_first_name, tm.last_name as target_last_name,
               a.username as processed_by_name
        FROM position_swap_requests psr
        LEFT JOIN members tm ON psr.target_member_id = tm.id
        LEFT JOIN admins a ON psr.processed_by_admin_id = a.id
        WHERE psr.member_id = ?
        ORDER BY psr.requested_date DESC
        LIMIT 10
    ");
    $stmt->execute([$member_id]);
    $swap_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching swap requests: " . $e->getMessage());
    $swap_requests = [];
}

// Get available equb terms for editing
try {
    $stmt = $pdo->query("
        SELECT id, equb_name, equb_id, max_members, current_members, status
        FROM equb_settings
        WHERE status IN ('planning', 'active')
        ORDER BY start_date DESC
    ");
    $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $equb_terms = [];
}

// Calculate statistics
$total_payments = count($payments);
$completed_payments = count(array_filter($payments, fn($p) => $p['status'] === 'paid'));
$total_paid = array_sum(array_column($payments, 'amount'));
$total_payouts = count($payouts);
$completed_payouts = count(array_filter($payouts, fn($p) => $p['status'] === 'completed'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?> - Member Profile</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* === MEMBER PROFILE PAGE DESIGN === */
        
        /* Main Content Wrapper */
        .app-main {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .app-content {
            max-width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        
        /* Page Header */
        .profile-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 40px;
            box-shadow: 0 8px 24px rgba(19, 102, 92, 0.3);
            flex-shrink: 0;
        }
        
        .profile-info {
            flex: 1;
            min-width: 0;
        }
        
        .profile-info h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            word-break: break-word;
        }
        
        .profile-meta {
            display: flex;
            gap: 16px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .meta-item i {
            color: var(--color-teal);
        }
        
        .profile-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            white-space: nowrap;
        }
        
        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #059669;
        }
        
        .status-inactive {
            background: rgba(107, 114, 128, 0.1);
            color: #6B7280;
        }
        
        .badge-individual {
            background: rgba(59, 130, 246, 0.1);
            color: #2563EB;
        }
        
        .badge-joint {
            background: rgba(139, 92, 246, 0.1);
            color: #7C3AED;
        }
        
        /* Statistics Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            min-width: 0;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 18px;
        }
        
        .stat-icon.payments { background: rgba(19, 102, 92, 0.1); color: var(--color-teal); }
        .stat-icon.contributed { background: rgba(233, 196, 106, 0.1); color: var(--color-gold); }
        .stat-icon.payout { background: rgba(139, 92, 246, 0.1); color: #7C3AED; }
        
        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 4px 0;
            word-break: break-word;
        }
        
        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-light);
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--color-teal);
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 15px;
            color: var(--color-purple);
            font-weight: 500;
            word-break: break-word;
        }
        
        .info-value.highlight {
            font-size: 18px;
            font-weight: 700;
            color: var(--color-gold);
        }
        
        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }
        
        .data-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }
        
        .data-table thead th {
            background: var(--color-cream);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: var(--color-purple);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-light);
            white-space: nowrap;
        }
        
        .data-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }
        
        .data-table tbody tr:hover {
            background: rgba(233, 196, 106, 0.02);
        }
        
        /* Buttons */
        .btn-edit {
            background: linear-gradient(135deg, var(--color-gold) 0%, #D4A72C 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(233, 196, 106, 0.4);
            color: white;
        }
        
        .btn-back {
            background: white;
            color: var(--color-purple);
            border: 2px solid var(--border-light);
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-back:hover {
            border-color: var(--color-teal);
            color: var(--color-teal);
        }
        
        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid var(--border-light);
            flex-wrap: wrap;
        }
        
        .nav-tabs .nav-link {
            color: var(--text-secondary);
            border: none;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--color-teal);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--color-teal);
            border-bottom: 3px solid var(--color-teal);
            background: transparent;
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .profile-header {
                padding: 20px;
            }
            
            .profile-header-content {
                gap: 16px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            
            .profile-info h1 {
                font-size: 24px;
            }
            
            .info-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .app-content {
                padding: 15px;
            }
            
            .profile-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-actions {
                width: 100%;
                justify-content: center;
            }
            
            .profile-actions .btn {
                flex: 1;
            }
            
            .profile-meta {
                justify-content: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .section-card {
                padding: 16px;
            }
            
            .section-title {
                font-size: 16px;
            }
            
            .nav-tabs .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .data-table {
                min-width: 700px;
            }
        }
        
        @media (max-width: 480px) {
            .profile-meta {
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            
            .nav-tabs .nav-link {
                padding: 8px 10px;
                font-size: 12px;
            }
            
            .nav-tabs .nav-link i {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <!-- Include Navigation -->
        <?php include 'includes/navigation.php'; ?>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h1>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-id-card"></i>
                            <span><?php echo htmlspecialchars($member['member_id']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($member['email']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($member['phone']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="status-badge status-<?php echo $member['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="status-badge badge-<?php echo $member['membership_type']; ?>">
                                <?php echo ucfirst($member['membership_type']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="profile-actions">
                    <button class="btn btn-edit" onclick="showEditModal()">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                    <a href="members.php" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Back to Members
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon payments">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?php echo $completed_payments; ?>/<?php echo $total_payments; ?></div>
                <div class="stat-label">Payments Made</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon contributed">
                    <i class="fas fa-pound-sign"></i>
                </div>
                <div class="stat-value">£<?php echo number_format($total_paid, 0); ?></div>
                <div class="stat-label">Total Contributed</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon payout">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-value">£<?php echo number_format($member['expected_payout'], 0); ?></div>
                <div class="stat-label">Expected Payout</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon payout">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?php echo $completed_payouts; ?>/<?php echo $total_payouts; ?></div>
                <div class="stat-label">Payouts Received</div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                    <i class="fas fa-info-circle me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button">
                    <i class="fas fa-money-bill-wave me-2"></i>Payments
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payouts-tab" data-bs-toggle="tab" data-bs-target="#payouts" type="button">
                    <i class="fas fa-hand-holding-usd me-2"></i>Payouts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="swaps-tab" data-bs-toggle="tab" data-bs-target="#swaps" type="button">
                    <i class="fas fa-exchange-alt me-2"></i>Position Swaps
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="profileTabsContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <!-- Personal Information -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Member ID</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['member_id']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['username'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Join Date</span>
                            <span class="info-value"><?php echo date('M d, Y', strtotime($member['join_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Language Preference</span>
                            <span class="info-value"><?php echo $member['language_preference'] ? 'Amharic' : 'English'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Verified</span>
                            <span class="info-value"><?php echo $member['email_verified'] ? 'Yes' : 'No'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Equb Information -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-chart-line"></i>
                            Equb Information
                        </h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Equb Term</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['equb_name'] ?? 'Not Assigned'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Equb ID</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['equb_id'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Membership Type</span>
                            <span class="info-value"><?php echo ucfirst($member['membership_type']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Monthly Payment</span>
                            <span class="info-value highlight">£<?php echo number_format($member['monthly_payment'], 0); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payout Position</span>
                            <span class="info-value">#<?php echo $member['actual_payout_position']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Position Coefficient</span>
                            <span class="info-value"><?php echo $member['position_coefficient']; ?>x</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Expected Payout</span>
                            <span class="info-value highlight">£<?php echo number_format($member['expected_payout'], 0); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payout Month</span>
                            <span class="info-value"><?php echo $member['payout_month'] ? date('M Y', strtotime($member['payout_month'])) : 'Not Set'; ?></span>
                        </div>
                        <?php if ($member['membership_type'] === 'joint'): ?>
                        <div class="info-item">
                            <span class="info-label">Joint Group</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['group_name'] ?? $member['joint_group_id']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Individual Contribution</span>
                            <span class="info-value">£<?php echo number_format($member['individual_contribution'], 0); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Primary Member</span>
                            <span class="info-value"><?php echo $member['primary_joint_member'] ? 'Yes' : 'No'; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Guarantor Information -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-user-shield"></i>
                            Guarantor Information
                        </h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Guarantor Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['guarantor_first_name'] . ' ' . $member['guarantor_last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Guarantor Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['guarantor_phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Guarantor Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['guarantor_email'] ?: 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Relationship</span>
                            <span class="info-value"><?php echo htmlspecialchars($member['guarantor_relationship'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Settings & Preferences -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-cog"></i>
                            Settings & Preferences
                        </h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Profile Visibility</span>
                            <span class="info-value"><?php echo $member['go_public'] ? 'Public' : 'Private'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Notifications</span>
                            <span class="info-value"><?php echo $member['email_notifications'] ? 'Enabled' : 'Disabled'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Reminders</span>
                            <span class="info-value"><?php echo $member['payment_reminders'] ? 'Enabled' : 'Disabled'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Position Swaps Allowed</span>
                            <span class="info-value"><?php echo $member['swap_terms_allowed'] ? 'Yes' : 'No'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rules Agreed</span>
                            <span class="info-value"><?php echo $member['rules_agreed'] ? 'Yes' : 'No'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Status</span>
                            <span class="info-value"><?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Approval Status</span>
                            <span class="info-value"><?php echo $member['is_approved'] ? 'Approved' : 'Pending'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Login</span>
                            <span class="info-value"><?php echo $member['last_login'] ? date('M d, Y H:i', strtotime($member['last_login'])) : 'Never'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Admin Notes -->
                <?php if ($member['notes']): ?>
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-sticky-note"></i>
                            Admin Notes
                        </h2>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($member['notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Payments Tab -->
            <div class="tab-pane fade" id="payments" role="tabpanel">
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-history"></i>
                            Payment History
                        </h2>
                        <a href="payments.php?member=<?php echo $member_id; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add Payment
                        </a>
                    </div>
                    
                    <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                    <th>Verified By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                    <td><?php echo date('M Y', strtotime($payment['payment_month'])); ?></td>
                                    <td class="fw-bold">£<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo $payment['payment_date'] ? date('M d, Y', strtotime($payment['payment_date'])) : 'Pending'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['verified_by_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="payments.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Payments Found</h5>
                        <p class="text-muted">This member has not made any payments yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payouts Tab -->
            <div class="tab-pane fade" id="payouts" role="tabpanel">
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-money-check-alt"></i>
                            Payout History
                        </h2>
                        <a href="payouts.php?member=<?php echo $member_id; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Schedule Payout
                        </a>
                    </div>
                    
                    <?php if (!empty($payouts)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Payout ID</th>
                                    <th>Gross Amount</th>
                                    <th>Admin Fee</th>
                                    <th>Net Amount</th>
                                    <th>Scheduled Date</th>
                                    <th>Actual Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payouts as $payout): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payout['payout_id']); ?></td>
                                    <td class="fw-bold">£<?php echo number_format($payout['gross_payout'], 2); ?></td>
                                    <td>£<?php echo number_format($payout['admin_fee'], 2); ?></td>
                                    <td class="fw-bold text-success">£<?php echo number_format($payout['net_amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($payout['scheduled_date'])); ?></td>
                                    <td><?php echo $payout['actual_payout_date'] ? date('M d, Y', strtotime($payout['actual_payout_date'])) : 'Pending'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $payout['status']; ?>">
                                            <?php echo ucfirst($payout['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="payouts.php?id=<?php echo $payout['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Payouts Found</h5>
                        <p class="text-muted">This member has not received any payouts yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Position Swaps Tab -->
            <div class="tab-pane fade" id="swaps" role="tabpanel">
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-exchange-alt"></i>
                            Position Swap Requests
                        </h2>
                    </div>
                    
                    <?php if (!empty($swap_requests)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Current Position</th>
                                    <th>Requested Position</th>
                                    <th>Target Member</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Processed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($swap_requests as $swap): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($swap['request_id']); ?></td>
                                    <td>#<?php echo $swap['current_position']; ?></td>
                                    <td>#<?php echo $swap['requested_position']; ?></td>
                                    <td><?php echo $swap['target_first_name'] ? htmlspecialchars($swap['target_first_name'] . ' ' . $swap['target_last_name']) : 'Any'; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($swap['requested_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $swap['status']; ?>">
                                            <?php echo ucfirst($swap['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($swap['processed_by_name'] ?? 'Pending'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Swap Requests Found</h5>
                        <p class="text-muted">This member has not requested any position swaps.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div> <!-- End app-content -->
</main> <!-- End app-main -->
</div> <!-- End app-layout -->

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMemberModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Member Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMemberForm">
                <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="modal-body">
                    <!-- Personal Information -->
                    <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Personal Information</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="editFirstName" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="editFirstName" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editLastName" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="editLastName" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="editEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editPhone" class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>" required>
                        </div>
                    </div>

                    <!-- Equb Assignment -->
                    <h6 class="mb-3 text-primary mt-4"><i class="fas fa-chart-line me-2"></i>Equb Assignment</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="editEqubTerm" class="form-label">Equb Term *</label>
                            <select class="form-select" id="editEqubTerm" name="equb_settings_id" required>
                                <option value="">Select Equb Term</option>
                                <?php foreach ($equb_terms as $term): ?>
                                    <option value="<?php echo $term['id']; ?>" <?php echo $member['equb_settings_id'] == $term['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($term['equb_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="editMonthlyPayment" class="form-label">Monthly Payment (£) *</label>
                            <input type="number" class="form-control" id="editMonthlyPayment" name="monthly_payment" value="<?php echo $member['monthly_payment']; ?>" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="editPayoutPosition" class="form-label">Payout Position *</label>
                            <input type="number" class="form-control" id="editPayoutPosition" name="payout_position" value="<?php echo $member['payout_position']; ?>" min="1" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="editPayoutMonth" class="form-label">Payout Month</label>
                            <input type="month" class="form-control" id="editPayoutMonth" name="payout_month" value="<?php echo $member['formatted_payout_month']; ?>">
                        </div>
                    </div>

                    <!-- Guarantor Information -->
                    <h6 class="mb-3 text-primary mt-4"><i class="fas fa-user-shield me-2"></i>Guarantor Information</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="editGuarantorFirstName" class="form-label">Guarantor First Name</label>
                            <input type="text" class="form-control" id="editGuarantorFirstName" name="guarantor_first_name" value="<?php echo htmlspecialchars($member['guarantor_first_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="editGuarantorLastName" class="form-label">Guarantor Last Name</label>
                            <input type="text" class="form-control" id="editGuarantorLastName" name="guarantor_last_name" value="<?php echo htmlspecialchars($member['guarantor_last_name']); ?>">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="editGuarantorPhone" class="form-label">Guarantor Phone</label>
                            <input type="tel" class="form-control" id="editGuarantorPhone" name="guarantor_phone" value="<?php echo htmlspecialchars($member['guarantor_phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="editGuarantorEmail" class="form-label">Guarantor Email</label>
                            <input type="email" class="form-control" id="editGuarantorEmail" name="guarantor_email" value="<?php echo htmlspecialchars($member['guarantor_email']); ?>">
                        </div>
                    </div>

                    <!-- Settings -->
                    <h6 class="mb-3 text-primary mt-4"><i class="fas fa-cog me-2"></i>Settings</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editGoPublic" name="go_public" value="1" <?php echo $member['go_public'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="editGoPublic">
                                    Profile Visibility (Go Public)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editSwapTerms" name="swap_terms_allowed" value="1" <?php echo $member['swap_terms_allowed'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="editSwapTerms">
                                    Allow Position Swapping
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="editNotes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="editNotes" name="notes" rows="3"><?php echo htmlspecialchars($member['notes']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="../assets/js/auth.js"></script>

<script>
    // Show edit modal
    function showEditModal() {
        new bootstrap.Modal(document.getElementById('editMemberModal')).show();
    }

    // Handle form submission
    document.getElementById('editMemberForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'update');
        
        try {
            const response = await fetch('api/members.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while updating member');
        }
    });

    // Show alert
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
</script>
</body>
</html>
