<?php
/**
 * HabeshaEqub - Members Directory & Management
 * Clean and professional member management interface
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Generate CSRF token for form security
$csrf_token = generate_csrf_token();

// Get members data with calculations
try {
    $stmt = $pdo->query("
        SELECT m.*, 
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.payout_position
                   ELSE m.payout_position
               END as actual_payout_position,
               CASE 
                   WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                   ELSE m.monthly_payment
               END as effective_monthly_payment,
               jmg.group_name, jmg.payout_split_method,
               COUNT(p.id) as total_payments,
               COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END), 0) as total_paid,
               es.equb_name, es.equb_id
        FROM members m 
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        LEFT JOIN payments p ON m.id = p.member_id
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
        GROUP BY m.id 
        ORDER BY 
            CASE 
                WHEN m.membership_type = 'joint' THEN jmg.payout_position
                ELSE m.payout_position
            END ASC, m.created_at DESC
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate dynamic payouts
    $calculator = getEnhancedEqubCalculator();
    foreach ($members as &$member) {
        $payout_calc = $calculator->calculateMemberFriendlyPayout($member['id']);
        if ($payout_calc['success']) {
            $member['expected_payout'] = $payout_calc['calculation']['display_payout'];
        } else {
            $member['expected_payout'] = 0;
        }
    }
    unset($member);
    
} catch (PDOException $e) {
    error_log("Error fetching members: " . $e->getMessage());
    $members = [];
}

// Get available equb terms for dropdown
try {
    $stmt = $pdo->query("
        SELECT id, equb_name, equb_id, max_members, current_members, status
        FROM equb_settings
        WHERE status IN ('planning', 'active')
        ORDER BY start_date DESC
    ");
    $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching equb terms: " . $e->getMessage());
    $equb_terms = [];
}

// Calculate statistics
$total_members = count($members);
$active_members = count(array_filter($members, fn($m) => $m['is_active']));
$inactive_members = $total_members - $active_members;
$pending_approval = count(array_filter($members, fn($m) => !$m['is_approved']));
$completed_payouts = count(array_filter($members, fn($m) => $m['has_received_payout']));
$total_contributions = array_sum(array_column($members, 'total_paid'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Directory - HabeshaEqub Admin</title>
    
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
        /* === MEMBERS DIRECTORY PAGE DESIGN === */
        
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
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .page-title-section {
            flex: 1;
            min-width: 0;
        }
        
        .page-title-section h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
            word-break: break-word;
        }
        
        .page-title-section p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 15px;
        }

        .add-member-btn {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(19, 102, 92, 0.3);
            white-space: nowrap;
        }
        
        .add-member-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(19, 102, 92, 0.4);
            color: white;
        }

        /* Statistics Cards */
        .stats-container {
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            transition: all 0.3s ease;
            height: 100%;
            min-width: 0;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.12);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-size: 18px;
        }

        .stat-icon.total { background: rgba(19, 102, 92, 0.1); color: var(--color-teal); }
        .stat-icon.active { background: rgba(34, 197, 94, 0.1); color: #059669; }
        .stat-icon.pending { background: rgba(251, 191, 36, 0.1); color: #D97706; }
        .stat-icon.completed { background: rgba(139, 92, 246, 0.1); color: #7C3AED; }
        .stat-icon.contributions { background: rgba(233, 196, 106, 0.1); color: var(--color-gold); }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 4px 0;
            line-height: 1;
            word-break: break-word;
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            max-width: 100%;
            box-sizing: border-box;
        }

        .search-bar {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: var(--color-cream);
            box-sizing: border-box;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--color-gold);
            box-shadow: 0 0 0 3px rgba(233, 196, 106, 0.1);
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
        }

        .filter-group {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            min-width: 140px;
            padding: 10px 14px;
            border: 2px solid var(--border-light);
            border-radius: 8px;
            background: white;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--color-gold);
            box-shadow: 0 0 0 3px rgba(233, 196, 106, 0.1);
        }

        /* Table Styling */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            max-width: 100%;
        }

        .table {
            margin: 0;
            min-width: 600px;
            width: 100%;
        }

        .table thead th {
            background: var(--color-cream);
            border-bottom: 2px solid var(--border-light);
            color: var(--color-purple);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 12px;
            border-top: none;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background: rgba(233, 196, 106, 0.02);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Member Info */
        .member-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .member-details {
            min-width: 0;
            flex: 1;
        }

        .member-details .member-name {
            font-weight: 600;
            color: var(--color-purple);
            margin: 0 0 4px 0;
            font-size: 15px;
            word-break: break-word;
        }

        .member-name-link {
            color: var(--color-teal);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .member-name-link:hover {
            color: var(--color-gold);
            text-decoration: underline;
        }

        .member-id {
            font-size: 13px;
            color: var(--text-secondary);
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
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

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-action i {
            font-size: 14px;
        }

        .btn-view {
            background: rgba(19, 102, 92, 0.1);
            color: var(--color-teal);
        }

        .btn-view:hover {
            background: rgba(19, 102, 92, 0.2);
            transform: scale(1.1);
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: #2563EB;
        }

        .btn-edit:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: scale(1.1);
        }

        .btn-toggle {
            background: rgba(251, 191, 36, 0.1);
            color: #D97706;
        }

        .btn-toggle:hover {
            background: rgba(251, 191, 36, 0.2);
            transform: scale(1.1);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: scale(1.1);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }
            
            .page-title-section h1 {
            font-size: 24px;
            }
        }
        
        @media (max-width: 992px) {
            .app-content {
                padding: 15px;
            }
            
            .page-header {
                padding: 24px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 22px;
            }
        }

        @media (max-width: 768px) {
            .app-content {
                padding: 12px;
            }
            
            .page-header {
                padding: 20px;
            }
            
            .page-title-section h1 {
                font-size: 22px;
            }
            
            .page-title-section p {
                font-size: 14px;
            }
            
            .add-member-btn {
                width: 100%;
            padding: 12px 20px;
            }

            .search-filter-section {
                padding: 16px;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            gap: 10px;
        }
        
            .filter-select {
                width: 100%;
                min-width: unset;
            }

            .stat-card {
                padding: 16px;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .stat-number {
                font-size: 20px;
            }
            
            .stat-label {
                font-size: 12px;
            }

            .table {
                min-width: 550px;
            }
            
            .table thead th {
                padding: 12px 10px;
                font-size: 11px;
            }
            
            .table tbody td {
                padding: 12px 10px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 480px) {
            .app-content {
                padding: 10px;
            }
            
            .page-header {
                padding: 16px;
            }
            
            .page-title-section h1 {
                font-size: 20px;
            }
            
            .table {
                min-width: 480px;
            }
            
            .member-avatar {
                width: 36px;
                height: 36px;
            font-size: 12px;
            }
            
            .action-buttons {
            gap: 4px;
                flex-wrap: wrap;
            }
            
            .btn-action {
                width: 32px;
                height: 32px;
            }
            
            .btn-action i {
                font-size: 12px;
            }
        }

        /* Modal Styling */
        .modal-header {
            background: var(--color-cream);
            border-bottom: 2px solid var(--border-light);
        }

        .modal-title {
            color: var(--color-purple);
            font-weight: 700;
        }

        .form-label {
            font-weight: 600;
            color: var(--color-purple);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-light);
            border-radius: 8px;
            padding: 10px 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-gold);
            box-shadow: 0 0 0 3px rgba(233, 196, 106, 0.1);
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <!-- Include Navigation -->
        <?php include 'includes/navigation.php'; ?>

        <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-section">
                <h1><i class="fas fa-users me-3"></i>Members Directory</h1>
                <p>Manage and monitor all equb members</p>
                </div>
            <button class="add-member-btn" onclick="showAddMemberModal()">
                <i class="fas fa-user-plus me-2"></i>
                Add New Member
                    </button>
            </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $active_members; ?></div>
                        <div class="stat-label">Active Members</div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $pending_approval; ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="stat-number"><?php echo $completed_payouts; ?></div>
                        <div class="stat-label">Completed Payouts</div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon contributions">
                            <i class="fas fa-pound-sign"></i>
                        </div>
                        <div class="stat-number">£<?php echo number_format($total_contributions, 0); ?></div>
                        <div class="stat-label">Total Contributions</div>
                    </div>
                </div>
            </div>
</div>
            
        <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row align-items-center">
                <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="memberSearch" placeholder="Search by name, email, or member ID...">
                        </div>
                    </div>
                <div class="col-lg-6">
                        <div class="filter-group">
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                            </select>
                        <select class="filter-select" id="payoutFilter">
                            <option value="">All Payouts</option>
                            <option value="0">Pending Payout</option>
                            <option value="1">Completed Payout</option>
                            </select>
                        <select class="filter-select" id="membershipFilter">
                                <option value="">All Types</option>
                                <option value="individual">Individual</option>
                                <option value="joint">Joint</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
        <!-- Members Table -->
        <div class="table-container">
            <table class="table">
                            <thead>
                                <tr>
                        <th>Member</th>
                        <th>Monthly Contribution</th>
                        <th>Expected Payout</th>
                        <th>Status</th>
                        <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                    <?php foreach ($members as $member): ?>
                        <tr data-member-id="<?php echo $member['id']; ?>">
                                        <td>
                                            <div class="member-info">
                                                <div class="member-avatar">
                                                    <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                                                </div>
                                                <div class="member-details">
                                                    <div class="member-name">
                                                        <a href="member-profile.php?id=<?php echo $member['id']; ?>" class="member-name-link">
                                                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                                        </a>
                                                    </div>
                                                    <div class="member-id"><?php echo htmlspecialchars($member['member_id']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                <div class="fw-bold text-primary" style="font-size: 16px;">£<?php echo number_format($member['effective_monthly_payment'], 0); ?></div>
                                <div class="text-muted small"><?php echo $member['total_payments']; ?> payments made</div>
                                        </td>
                                        <td>
                                <div class="fw-bold text-success" style="font-size: 16px;">£<?php echo number_format($member['expected_payout'], 0); ?></div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $member['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                                            </span>
                                <?php if (!$member['is_approved']): ?>
                                    <div class="mt-1">
                                        <span class="status-badge" style="background: rgba(251, 191, 36, 0.1); color: #D97706;">Pending</span>
                                                    </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewMember(<?php echo $member['id']; ?>)" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editMember(<?php echo $member['id']; ?>)" title="Edit Member">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                    <button class="btn-action btn-toggle" onclick="toggleMemberStatus(<?php echo $member['id']; ?>, <?php echo $member['is_active'] ? 0 : 1; ?>)" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                                </button>
                                    <button class="btn-action btn-delete" onclick="deleteMember(<?php echo $member['id']; ?>)" title="Delete Member">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

            <?php if (empty($members)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 style="color: var(--text-secondary);">No Members Found</h4>
                    <p style="color: var(--text-secondary);">Start by adding your first member.</p>
                    <button class="btn btn-primary mt-3" onclick="showAddMemberModal()">
                        <i class="fas fa-user-plus me-2"></i>Add New Member
                    </button>
                    </div>
            <?php endif; ?>
                </div>

        </div> <!-- End app-content -->
    </main> <!-- End app-main -->
</div> <!-- End app-layout -->

    <!-- Add/Edit Member Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="memberModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Member
                </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="memberForm">
                        <input type="hidden" id="memberId" name="member_id">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                <div class="modal-body">
                        <!-- Personal Information -->
                    <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Personal Information</h6>
                    <div class="row mb-4">
                            <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>
                        
                    <div class="row mb-4">
                            <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                            <label for="phone" class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                    <!-- Equb Assignment -->
                    <h6 class="mb-3 text-primary mt-4"><i class="fas fa-chart-line me-2"></i>Equb Assignment</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="equbTerm" class="form-label">Equb Term *</label>
                            <select class="form-select" id="equbTerm" name="equb_settings_id" required onchange="updateAvailablePositions()">
                                <option value="">Select Equb Term</option>
                                <?php foreach ($equb_terms as $term): ?>
                                    <option value="<?php echo $term['id']; ?>" 
                                            data-max="<?php echo $term['max_members']; ?>"
                                            data-current="<?php echo $term['current_members']; ?>">
                                        <?php echo htmlspecialchars($term['equb_name']); ?> 
                                        (<?php echo $term['current_members']; ?>/<?php echo $term['max_members']; ?> members)
                                    </option>
                                <?php endforeach; ?>
                    </select>
                </div>
                        <div class="col-md-4">
                            <label for="membershipType" class="form-label">Membership Type *</label>
                            <select class="form-select" id="membershipType" name="membership_type" required onchange="toggleJointFields()">
                                <option value="individual">Individual</option>
                                <option value="joint">Joint</option>
                    </select>
                </div>
                <div class="col-md-4">
                            <label for="payoutPosition" class="form-label">Payout Position *</label>
                            <input type="number" class="form-control" id="payoutPosition" name="payout_position" min="1" required>
                            <small class="text-muted">Position in payout queue</small>
                </div>
            </div>
            
                    <div class="row mb-4">
                <div class="col-md-6">
                            <label for="monthlyPayment" class="form-label">Monthly Payment (£) *</label>
                            <input type="number" class="form-control" id="monthlyPayment" name="monthly_payment" step="0.01" min="0" required>
                </div>
                <div class="col-md-6">
                            <label for="payoutMonth" class="form-label">Payout Month</label>
                            <input type="month" class="form-control" id="payoutMonth" name="payout_month">
                            <small class="text-muted">Leave empty for auto-calculation</small>
            </div>
        </div>

                    <!-- Joint Membership Fields (Initially Hidden) -->
                    <div id="jointFields" style="display: none;">
                        <h6 class="mb-3 text-primary"><i class="fas fa-users me-2"></i>Joint Membership Details</h6>
                        <div class="row mb-4">
            <div class="col-md-6">
                                <label for="jointGroupName" class="form-label">Joint Group Name</label>
                                <input type="text" class="form-control" id="jointGroupName" name="joint_group_name">
            </div>
            <div class="col-md-6">
                                <label for="individualContribution" class="form-label">Individual Contribution (£)</label>
                                <input type="number" class="form-control" id="individualContribution" name="individual_contribution" step="0.01" min="0">
                </div>
            </div>
        </div>
                        
                        <!-- Guarantor Information -->
                    <h6 class="mb-3 text-primary mt-4"><i class="fas fa-user-shield me-2"></i>Guarantor Information</h6>
                    <div class="row mb-4">
                            <div class="col-md-6">
                            <label for="guarantorFirstName" class="form-label">Guarantor First Name</label>
                            <input type="text" class="form-control" id="guarantorFirstName" name="guarantor_first_name" value="Pending">
                            </div>
                            <div class="col-md-6">
                            <label for="guarantorLastName" class="form-label">Guarantor Last Name</label>
                            <input type="text" class="form-control" id="guarantorLastName" name="guarantor_last_name" value="Pending">
                            </div>
                        </div>
                        
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="guarantorPhone" class="form-label">Guarantor Phone</label>
                            <input type="tel" class="form-control" id="guarantorPhone" name="guarantor_phone" value="Pending">
                                </div>
                        <div class="col-md-6">
                            <label for="guarantorEmail" class="form-label">Guarantor Email</label>
                                    <input type="email" class="form-control" id="guarantorEmail" name="guarantor_email">
                            </div>
                        </div>
                        
                    <!-- Additional Settings -->
                    <h6 class="mb-3 text-primary mt-4"><i class="fas fa-cog me-2"></i>Additional Settings</h6>
                    <div class="row mb-4">
                            <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="goPublic" name="go_public" value="1" checked>
                                <label class="form-check-label" for="goPublic">
                                    Profile Visibility (Go Public)
                                </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="swapTerms" name="swap_terms_allowed" value="1">
                                <label class="form-check-label" for="swapTerms">
                                    Allow Position Swapping
                                </label>
                                </div>
                            </div>
                        </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes about this member..."></textarea>
            </div>
        </div>
    </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Member
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
    // Global variables
    let isEditMode = false;
    let currentMemberId = null;

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        setupFilters();
        setupSearch();
    });

    // Setup filters
    function setupFilters() {
        document.getElementById('memberSearch').addEventListener('input', filterMembers);
        document.getElementById('statusFilter').addEventListener('change', filterMembers);
        document.getElementById('payoutFilter').addEventListener('change', filterMembers);
        document.getElementById('membershipFilter').addEventListener('change', filterMembers);
    }

    // Setup search
    function setupSearch() {
        const searchInput = document.getElementById('memberSearch');
        searchInput.addEventListener('input', debounce(filterMembers, 300));
    }

    // Filter members
        function filterMembers() {
        const search = document.getElementById('memberSearch').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const payout = document.getElementById('payoutFilter').value;
        const membership = document.getElementById('membershipFilter').value;
            
            const rows = document.querySelectorAll('#membersTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
            const matchesSearch = !search || text.includes(search);
            
            let matchesStatus = true;
            if (status !== '') {
                const statusBadge = row.querySelector('.status-badge');
                matchesStatus = statusBadge && 
                    ((status === '1' && statusBadge.classList.contains('status-active')) ||
                     (status === '0' && statusBadge.classList.contains('status-inactive')));
            }

            let matchesMembership = true;
            if (membership !== '') {
                const membershipBadge = row.querySelector('.badge-individual, .badge-joint');
                matchesMembership = membershipBadge && membershipBadge.classList.contains('badge-' + membership);
            }

            row.style.display = matchesSearch && matchesStatus && matchesMembership ? '' : 'none';
        });
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Show add member modal
    function showAddMemberModal() {
        isEditMode = false;
        currentMemberId = null;
        document.getElementById('memberModalLabel').innerHTML = '<i class="fas fa-user-plus me-2"></i>Add New Member';
        document.getElementById('memberForm').reset();
        document.getElementById('memberId').value = '';
        toggleJointFields();
        
        new bootstrap.Modal(document.getElementById('memberModal')).show();
    }

    // Toggle joint membership fields
    function toggleJointFields() {
        const membershipType = document.getElementById('membershipType').value;
        const jointFields = document.getElementById('jointFields');
        jointFields.style.display = membershipType === 'joint' ? 'block' : 'none';
    }

    // Update available positions based on selected equb term
    function updateAvailablePositions() {
        const equbSelect = document.getElementById('equbTerm');
        const selectedOption = equbSelect.options[equbSelect.selectedIndex];
        const maxMembers = selectedOption.dataset.max;
        const positionInput = document.getElementById('payoutPosition');
        
        if (maxMembers) {
            positionInput.max = maxMembers;
        }
    }

    // View member details
    function viewMember(id) {
        window.location.href = `member-profile.php?id=${id}`;
    }

    // Edit member
    async function editMember(id) {
        isEditMode = true;
        currentMemberId = id;
        document.getElementById('memberModalLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Member';
        
        try {
            const formData = new FormData();
            formData.append('action', 'get_member');
            formData.append('member_id', id);
            
            const response = await fetch('api/members.php', {
                    method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
                if (data.success) {
                    const member = data.member;
                    
                // Fill form with member data
                    document.getElementById('memberId').value = member.id;
                    document.getElementById('firstName').value = member.first_name;
                    document.getElementById('lastName').value = member.last_name;
                    document.getElementById('email').value = member.email;
                    document.getElementById('phone').value = member.phone;
                document.getElementById('equbTerm').value = member.equb_settings_id;
                document.getElementById('membershipType').value = member.membership_type || 'individual';
                document.getElementById('payoutPosition').value = member.payout_position;
                document.getElementById('monthlyPayment').value = member.monthly_payment;
                            document.getElementById('payoutMonth').value = member.formatted_payout_month || '';
                document.getElementById('guarantorFirstName').value = member.guarantor_first_name || 'Pending';
                document.getElementById('guarantorLastName').value = member.guarantor_last_name || 'Pending';
                document.getElementById('guarantorPhone').value = member.guarantor_phone || 'Pending';
                document.getElementById('guarantorEmail').value = member.guarantor_email || '';
                document.getElementById('goPublic').checked = member.go_public == 1;
                document.getElementById('swapTerms').checked = member.swap_terms_allowed == 1;
                document.getElementById('notes').value = member.notes || '';
                
                // Toggle joint fields if needed
                toggleJointFields();
                
                // If joint membership, populate joint fields
                    if (member.membership_type === 'joint') {
                    document.getElementById('jointGroupName').value = member.joint_group_id || '';
                        document.getElementById('individualContribution').value = member.individual_contribution || '';
                }
                    
                    new bootstrap.Modal(document.getElementById('memberModal')).show();
                } else {
                showAlert('error', data.message);
                }
        } catch (error) {
                console.error('Error:', error);
            showAlert('error', 'An error occurred while fetching member data');
        }
    }

    // Toggle member status
    async function toggleMemberStatus(id, status) {
        const statusText = status ? 'activate' : 'deactivate';
        
        if (!confirm(`Are you sure you want to ${statusText} this member?`)) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('member_id', id);
            formData.append('status', status);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            
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
            showAlert('error', 'An error occurred while updating member status');
        }
    }

    // Delete member
    async function deleteMember(id) {
        if (!confirm('Are you sure you want to delete this member? This action cannot be undone.')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('member_id', id);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            
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
            showAlert('error', 'An error occurred while deleting member');
        }
    }

    // Form submission
    document.getElementById('memberForm').addEventListener('submit', async function(e) {
                e.preventDefault();
        
        const formData = new FormData(this);
        const action = isEditMode ? 'update' : 'add';
        formData.append('action', action);
        
        try {
            const response = await fetch('api/members.php', {
                    method: 'POST',
                body: formData
            });
            
            const data = await response.json();
                    
                    if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('memberModal')).hide();
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
                    } else {
                showAlert('error', data.message);
                    }
        } catch (error) {
                    console.error('Error:', error);
            showAlert('error', 'An error occurred while saving member');
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
