<?php
/**
 * HabeshaEqub - Joint Membership Groups Management
 * Comprehensive management interface for joint EQUB memberships
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get all joint groups with member information
try {
    $stmt = $pdo->query("
        SELECT 
            jmg.*,
            es.equb_name, es.equb_id, es.status as equb_status, 
            es.regular_payment_tier, es.admin_fee, es.duration_months,
            COUNT(m.id) as current_members,
            GROUP_CONCAT(
                CONCAT(m.first_name, ' ', m.last_name,
                       CASE WHEN m.primary_joint_member = 1 THEN ' (Primary)' ELSE '' END)
                ORDER BY m.primary_joint_member DESC, m.created_at ASC
                SEPARATOR ', '
            ) as member_names,
            SUM(m.individual_contribution) as total_individual_contributions,
            COALESCE(jmg.position_coefficient, 1.0) as position_coefficient
        FROM joint_membership_groups jmg
        JOIN equb_settings es ON jmg.equb_settings_id = es.id
        LEFT JOIN members m ON jmg.joint_group_id = m.joint_group_id AND m.is_active = 1
        WHERE jmg.is_active = 1
        GROUP BY jmg.id
        ORDER BY es.status DESC, jmg.equb_settings_id, jmg.payout_position ASC
    ");
    $joint_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching joint groups: " . $e->getMessage());
    $joint_groups = [];
}

// Get summary statistics
$total_groups = count($joint_groups);
$active_groups = count(array_filter($joint_groups, fn($g) => $g['equb_status'] === 'active'));
$total_joint_members = array_sum(array_column($joint_groups, 'current_members'));
$total_monthly_contributions = array_sum(array_column($joint_groups, 'total_monthly_payment'));

// Get all equbs for filtering
try {
    $stmt = $pdo->query("
        SELECT id, equb_id, equb_name, status
        FROM equb_settings 
        ORDER BY status DESC, created_at DESC
    ");
    $all_equbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_equbs = [];
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('joint_membership.manage_joint_groups'); ?> - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
        }
        
        .joint-group-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            transition: all 0.3s ease;
        }
        
        .joint-group-card:hover {
            box-shadow: 0 12px 48px rgba(48, 25, 67, 0.12);
            transform: translateY(-2px);
        }
        
        .group-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .group-id {
            background: linear-gradient(135deg, var(--purple) 0%, var(--light-purple) 100%);
            color: var(--white);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .split-method-badge {
            background: var(--gold);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .member-list {
            background: linear-gradient(135deg, var(--light-purple) 0%, #F8F6FF 100%);
            border-radius: 12px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .payout-info {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            color: var(--white);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            color: var(--white);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(218, 165, 32, 0.2);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 16px;
            border: 2px dashed var(--border-light);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--border-light);
            margin-bottom: 20px;
        }
        
        /* ========================================= */
        /* MOBILE RESPONSIVENESS */
        /* ========================================= */
        
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }
            
            .page-header .row {
                flex-direction: column;
            }
            
            .page-header .col-md-4 {
                text-align: left !important;
                margin-top: 20px;
            }
            
            .page-header .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .stat-card {
                margin-bottom: 15px;
            }
            
            .joint-group-card {
                padding: 20px;
            }
            
            .group-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }
            
            .group-header .text-end {
                text-align: left !important;
                width: 100%;
            }
            
            .group-header .btn {
                margin: 5px 5px 5px 0;
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            
            .joint-group-card .row {
                flex-direction: column;
            }
            
            .member-list {
                margin-bottom: 15px;
            }
            
            .payout-info {
                text-align: left !important;
            }
            
            .group-id {
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            
            .split-method-badge {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-container {
                padding: 10px;
            }
            
            .joint-group-card {
                padding: 15px;
            }
            
            .group-header .btn {
                font-size: 0.75rem;
                padding: 5px 8px;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .empty-state {
                padding: 40px 15px;
            }
            
            .empty-state i {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-users text-info me-3"></i><?php echo t('joint_membership.manage_joint_groups'); ?></h1>
                    <p class="mb-0 text-muted"><?php echo t('joint_membership.description'); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="equb-management.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to EQUB Management
                    </a>
                    <button class="btn btn-success me-2" onclick="openCreateGroupModal()">
                        <i class="fas fa-plus me-1"></i>
                        Create Joint Group
                    </button>
                    <a href="financial-analytics.php" class="btn btn-primary">
                        <i class="fas fa-chart-line me-1"></i>
                        Financial Analytics
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_groups; ?></div>
                    <div class="stat-label">Total Joint Groups</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $active_groups; ?></div>
                    <div class="stat-label">Active Groups</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_joint_members; ?></div>
                    <div class="stat-label">Joint Members</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">£<?php echo number_format($total_monthly_contributions, 0); ?></div>
                    <div class="stat-label">Monthly Contributions</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <select class="form-select" id="equbFilter">
                    <option value="">All EQUB Terms</option>
                    <?php foreach ($all_equbs as $equb): ?>
                        <option value="<?php echo $equb['id']; ?>">
                            <?php echo htmlspecialchars($equb['equb_name'] . ' (' . $equb['equb_id'] . ')'); ?>
                            - <?php echo ucfirst($equb['status']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" id="searchFilter" placeholder="Search groups, members, or group names...">
            </div>
        </div>

        <!-- Joint Groups Listing -->
        <div id="jointGroupsContainer">
            <?php if (empty($joint_groups)): ?>
                                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h4>No Joint Groups Found</h4>
                        <p class="text-muted">Create your first joint group to manage shared EQUB positions.</p>
                        <button class="btn btn-primary" onclick="openCreateGroupModal()">
                            <i class="fas fa-plus me-1"></i>
                            Create Joint Group
                        </button>
                        <a href="members.php" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-user-plus me-1"></i>
                            Add Members
                        </a>
                    </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($joint_groups as $group): ?>
                        <div class="col-12 group-item" 
                             data-equb-id="<?php echo $group['equb_settings_id']; ?>"
                             data-search-terms="<?php echo htmlspecialchars(strtolower($group['joint_group_id'] . ' ' . $group['group_name'] . ' ' . $group['member_names'] . ' ' . $group['equb_name'])); ?>">
                            <div class="joint-group-card">
                                <div class="group-header">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="group-id me-3"><?php echo htmlspecialchars($group['joint_group_id']); ?></span>
                                            <span class="split-method-badge"><?php echo ucfirst($group['payout_split_method']); ?> Split</span>
                                            <span class="badge bg-secondary ms-2">Position <?php echo $group['payout_position']; ?></span>
                                            <span class="badge" style="background: var(--color-gold); color: white;"><?php echo number_format($group['position_coefficient'], 2); ?> Positions</span>
                                        </div>
                                        <h5 class="mb-1">
                                            <?php echo htmlspecialchars($group['group_name'] ?: 'Unnamed Group'); ?>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            <strong>EQUB:</strong> <?php echo htmlspecialchars($group['equb_name']); ?> 
                                            <span class="badge bg-<?php echo $group['equb_status'] === 'active' ? 'success' : 'warning'; ?> ms-2">
                                                <?php echo ucfirst($group['equb_status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewGroupDetails('<?php echo $group['joint_group_id']; ?>')" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-info btn-sm ms-1" onclick="manageMembers('<?php echo $group['joint_group_id']; ?>', <?php echo $group['equb_settings_id']; ?>)" title="Manage Members">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm ms-1" onclick="editGroup('<?php echo $group['joint_group_id']; ?>')" title="Edit Group">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm ms-1" onclick="managePosition('<?php echo $group['joint_group_id']; ?>', <?php echo $group['payout_position']; ?>, <?php echo $group['equb_settings_id']; ?>)" title="Manage Position">
                                            <i class="fas fa-sort-numeric-down"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm ms-1" onclick="calculateGroupPayout('<?php echo $group['joint_group_id']; ?>')" title="Calculate Payout">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm ms-1" onclick="deleteGroup('<?php echo $group['joint_group_id']; ?>')" title="Delete Group">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="member-list">
                                            <h6 class="mb-2">
                                                <i class="fas fa-users text-purple me-2"></i>
                                                Members (<?php echo $group['current_members']; ?>)
                                            </h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($group['member_names'] ?: 'No members assigned'); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="payout-info">
                                            <h6 class="mb-1">Financial Overview</h6>
                                            <div style="font-size: 1.1rem; font-weight: 600;">
                                                <div>Monthly: £<?php echo number_format($group['total_monthly_payment'], 2); ?></div>
                                                <?php if ($group['duration_months']): ?>
                                                    <div style="font-size: 0.9rem;">
                                                        <?php 
                                                        $gross_payout = $group['total_monthly_payment'] * $group['duration_months'];
                                                        $net_payout = $gross_payout - ($group['admin_fee'] ?? 20);
                                                        ?>
                                                        Group Payout: £<?php echo number_format($net_payout, 2); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($group['total_individual_contributions'] > 0): ?>
                                                <small class="text-muted">Individual Total: £<?php echo number_format($group['total_individual_contributions'], 2); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Group Details Modal -->
    <div class="modal fade" id="groupDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users text-info me-2"></i>
                        Joint Group Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="groupDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Joint Group Modal -->
    <div class="modal fade" id="jointGroupModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-users text-info me-2"></i>
                        Create Joint Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="jointGroupForm">
                        <input type="hidden" id="editGroupId">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">EQUB Term *</label>
                                <select class="form-select" id="modalEqubSettings" required>
                                    <option value="">Select EQUB Term...</option>
                                    <?php foreach ($all_equbs as $equb): ?>
                                        <option value="<?php echo $equb['id']; ?>">
                                            <?php echo htmlspecialchars($equb['equb_name'] . ' (' . $equb['equb_id'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Group Name</label>
                                <input type="text" class="form-control" id="modalGroupName" placeholder="Optional group name">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Total Monthly Payment (£) *</label>
                                <input type="number" class="form-control" id="modalTotalPayment" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payout Position *</label>
                                <input type="number" class="form-control" id="modalPayoutPosition" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Split Method *</label>
                                <select class="form-select" id="modalSplitMethod" required>
                                    <option value="equal">Equal Split</option>
                                    <option value="proportional">Proportional Split</option>
                                    <option value="custom">Custom Split</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> After creating the joint group, you can add members to it through the Members page.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveJointGroup()">
                        <i class="fas fa-save me-1"></i>
                        Save Group
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Calculation Modal -->
    <div class="modal fade" id="payoutCalculationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calculator text-success me-2"></i>
                        Joint Group Payout Calculation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="payoutCalculationContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Calculating...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Management Modal -->
    <div class="modal fade" id="memberManagementModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus text-info me-2"></i>
                        Manage Joint Group Members
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Available Members -->
                        <div class="col-md-6">
                            <h6><i class="fas fa-users me-2"></i>Available Members</h6>
                            <div id="availableMembersList" class="member-assignment-list">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Assigned Members -->
                        <div class="col-md-6">
                            <h6><i class="fas fa-user-check me-2"></i>Assigned Members</h6>
                            <div id="assignedMembersList" class="member-assignment-list">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="refreshMemberAssignments()">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Position Management Modal -->
    <div class="modal fade" id="positionManagementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sort-numeric-down text-secondary me-2"></i>
                        Manage Joint Group Position
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="positionManagementForm">
                        <input type="hidden" id="positionJointGroupId">
                        <input type="hidden" id="positionEqubId">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Changing the position will affect when this joint group receives their payout.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Position</label>
                            <input type="number" class="form-control" id="currentPosition" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Position *</label>
                            <input type="number" class="form-control" id="newPosition" min="1" required>
                            <div class="form-text">Position must be between 1 and the EQUB duration (in months)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Available Positions</label>
                            <div id="availablePositions" class="small text-muted">
                                Loading available positions...
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="savePositionChange()">
                        <i class="fas fa-save me-1"></i>
                        Update Position
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .member-assignment-list {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
    }
    
    .member-assignment-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .member-assignment-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    </style>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Filter functionality
        document.getElementById('equbFilter').addEventListener('change', filterGroups);
        document.getElementById('searchFilter').addEventListener('input', filterGroups);

        // Open create group modal
        function openCreateGroupModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-users text-info me-2"></i>Create Joint Group';
            document.getElementById('jointGroupForm').reset();
            document.getElementById('editGroupId').value = '';
            new bootstrap.Modal(document.getElementById('jointGroupModal')).show();
        }

        // Edit group
        function editGroup(jointGroupId) {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-warning me-2"></i>Edit Joint Group';
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_joint_group_details&joint_group_id=${jointGroupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.group) {
                    const group = data.data.group;
                    document.getElementById('editGroupId').value = jointGroupId;
                    document.getElementById('modalEqubSettings').value = group.equb_settings_id;
                    document.getElementById('modalGroupName').value = group.group_name || '';
                    document.getElementById('modalTotalPayment').value = group.total_monthly_payment;
                    document.getElementById('modalPayoutPosition').value = group.payout_position;
                    document.getElementById('modalSplitMethod').value = group.payout_split_method;
                    
                    new bootstrap.Modal(document.getElementById('jointGroupModal')).show();
                } else {
                    alert('Error loading group details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading group details.');
            });
        }

        // Save joint group
        function saveJointGroup() {
            const editGroupId = document.getElementById('editGroupId').value;
            const action = editGroupId ? 'update_joint_group' : 'create_joint_group';
            
            const formData = new FormData();
            formData.append('action', action);
            if (editGroupId) formData.append('joint_group_id', editGroupId);
            formData.append('equb_settings_id', document.getElementById('modalEqubSettings').value);
            formData.append('group_name', document.getElementById('modalGroupName').value);
            formData.append('total_monthly_payment', document.getElementById('modalTotalPayment').value);
            formData.append('payout_position', document.getElementById('modalPayoutPosition').value);
            formData.append('payout_split_method', document.getElementById('modalSplitMethod').value);
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(editGroupId ? 'Joint group updated successfully!' : 'Joint group created successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('jointGroupModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }

        // Delete group
        function deleteGroup(jointGroupId) {
            if (confirm('Are you sure you want to delete this joint group? This action cannot be undone.')) {
                fetch('api/joint-membership.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_joint_group&joint_group_id=${jointGroupId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Joint group deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                });
            }
        }

        function filterGroups() {
            const equbFilter = document.getElementById('equbFilter').value;
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
            const groups = document.querySelectorAll('.group-item');

            groups.forEach(group => {
                const equbId = group.getAttribute('data-equb-id');
                const searchTerms = group.getAttribute('data-search-terms');
                
                let showGroup = true;
                
                // Filter by EQUB
                if (equbFilter && equbId !== equbFilter) {
                    showGroup = false;
                }
                
                // Filter by search terms
                if (searchFilter && !searchTerms.includes(searchFilter)) {
                    showGroup = false;
                }
                
                group.style.display = showGroup ? 'block' : 'none';
            });
        }

        // View group details
        function viewGroupDetails(jointGroupId) {
            const modal = new bootstrap.Modal(document.getElementById('groupDetailsModal'));
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_joint_group_details&joint_group_id=${jointGroupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    document.getElementById('groupDetailsContent').innerHTML = generateGroupDetailsHTML(data.data);
                } else {
                    document.getElementById('groupDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading group details: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('groupDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading group details.
                    </div>
                `;
            });
            
            modal.show();
        }

        // Calculate group payout
        function calculateGroupPayout(jointGroupId) {
            const modal = new bootstrap.Modal(document.getElementById('payoutCalculationModal'));
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=calculate_joint_payout&joint_group_id=${jointGroupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    document.getElementById('payoutCalculationContent').innerHTML = generatePayoutCalculationHTML(data.data);
                } else {
                    document.getElementById('payoutCalculationContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error calculating payout: ${data.message || 'Unknown error'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('payoutCalculationContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error calculating payout.
                    </div>
                `;
            });
            
            modal.show();
        }

        function generateGroupDetailsHTML(data) {
            // This function would generate the detailed HTML for group information
            const group = data.group || {};
            const members = data.members || [];
            
            return `
                <div class="group-details">
                    <h6>Group Information</h6>
                    <p><strong>Group ID:</strong> ${group.joint_group_id || 'N/A'}</p>
                    <p><strong>Group Name:</strong> ${group.group_name || 'Unnamed'}</p>
                    <p><strong>EQUB:</strong> ${group.equb_name || 'N/A'}</p>
                    <p><strong>Total Monthly Payment:</strong> £${parseFloat(group.total_monthly_payment || 0).toLocaleString()}</p>
                    <p><strong>Split Method:</strong> ${group.payout_split_method || 'equal'}</p>
                    <p><strong>Shared Payout Position:</strong> ${group.payout_position || 'N/A'}</p>
                    
                    <h6 class="mt-4">Members (${members.length})</h6>
                    ${members.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Individual Contribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${members.map(member => `
                                        <tr>
                                            <td>${member.first_name} ${member.last_name}</td>
                                            <td>${member.role || 'Member'}</td>
                                            <td>£${parseFloat(member.individual_contribution || 0).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : '<p class="text-muted">No members assigned to this group yet.</p>'}
                </div>
            `;
        }

        function generatePayoutCalculationHTML(data) {
            // This function would generate the payout calculation details
            const group = data.group || {};
            const members = data.members || [];
            
            return `
                <div class="payout-calculation">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>Joint Payout Calculation Complete</h6>
                        <p class="mb-0">Traditional EQUB method: Members share ONE position and split the payout.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Shared Group Payout</h6>
                            <p><strong>Total Contribution:</strong> £${parseFloat(data.total_contribution || 0).toLocaleString()}</p>
                            <p><strong>Admin Fee:</strong> £${parseFloat(data.admin_fee || 0).toLocaleString()}</p>
                            <p><strong>Net Payout:</strong> £${parseFloat(data.net_payout || 0).toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Position Details</h6>
                            <p><strong>Monthly Payment:</strong> £${parseFloat(group.total_monthly_payment || 0).toLocaleString()}</p>
                            <p><strong>Position Number:</strong> ${group.payout_position || 'N/A'}</p>
                            <p><strong>Position Coefficient:</strong> ${parseFloat(group.position_coefficient || 1).toFixed(2)} positions</p>
                            <p><strong>Split Method:</strong> <span class="badge bg-info">${(group.payout_split_method || 'equal').toUpperCase()}</span></p>
                        </div>
                    </div>
                    
                    ${members.length > 0 ? `
                        <h6 class="mt-4">Individual Member Splits</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Monthly Contribution</th>
                                        <th>Share %</th>
                                        <th>Individual Payout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${members.map(member => `
                                        <tr>
                                            <td>
                                                ${member.first_name} ${member.last_name}
                                                ${member.primary_joint_member == 1 ? '<span class="badge bg-primary ms-1">Primary</span>' : ''}
                                            </td>
                                            <td>£${parseFloat(member.individual_contribution || 0).toFixed(2)}</td>
                                            <td>${parseFloat(member.payout_amount / data.net_payout * 100).toFixed(1)}%</td>
                                            <td><strong>£${parseFloat(member.payout_amount || 0).toFixed(2)}</strong></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : '<p class="text-muted">No members found for payout calculation.</p>'}
                </div>
            `;
        }
        
        // Member Management Functions
        let currentJointGroupId = null;
        let currentEqubSettingsId = null;
        
        function manageMembers(jointGroupId, equbSettingsId) {
            currentJointGroupId = jointGroupId;
            currentEqubSettingsId = equbSettingsId;
            
            const modal = new bootstrap.Modal(document.getElementById('memberManagementModal'));
            loadAvailableMembers();
            loadAssignedMembers();
            modal.show();
        }
        
        function loadAvailableMembers() {
            document.getElementById('availableMembersList').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_available_members&equb_settings_id=${currentEqubSettingsId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAvailableMembers(data.data);
                } else {
                    document.getElementById('availableMembersList').innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('availableMembersList').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading available members
                    </div>
                `;
            });
        }
        
        function loadAssignedMembers() {
            document.getElementById('assignedMembersList').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_joint_group_details&joint_group_id=${currentJointGroupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.members) {
                    displayAssignedMembers(data.data.members);
                } else {
                    document.getElementById('assignedMembersList').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No members assigned to this group yet
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('assignedMembersList').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading assigned members
                    </div>
                `;
            });
        }
        
        function displayAvailableMembers(members) {
            if (members.length === 0) {
                document.getElementById('availableMembersList').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No available members found
                    </div>
                `;
                return;
            }
            
            const html = members.map(member => `
                <div class="member-assignment-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${member.first_name} ${member.last_name}</h6>
                            <small class="text-muted">${member.member_id} • £${parseFloat(member.monthly_payment).toFixed(2)}/month</small>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="assignMember(${member.id}, '${member.first_name} ${member.last_name}')">
                            <i class="fas fa-plus me-1"></i>Assign
                        </button>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('availableMembersList').innerHTML = html;
        }
        
        function displayAssignedMembers(members) {
            if (members.length === 0) {
                document.getElementById('assignedMembersList').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No members assigned to this group yet
                    </div>
                `;
                return;
            }
            
            const html = members.map(member => `
                <div class="member-assignment-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${member.first_name} ${member.last_name}</h6>
                            <small class="text-muted">
                                ${member.role} • 
                                £${parseFloat(member.individual_contribution || 0).toFixed(2)} contribution
                            </small>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="removeMember(${member.id}, '${member.first_name} ${member.last_name}')">
                            <i class="fas fa-minus me-1"></i>Remove
                        </button>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('assignedMembersList').innerHTML = html;
        }
        
        function assignMember(memberId, memberName) {
            const contribution = prompt(`Enter individual contribution amount for ${memberName}:`, '500');
            if (!contribution || isNaN(contribution) || parseFloat(contribution) <= 0) {
                alert('Please enter a valid contribution amount.');
                return;
            }
            
            const isPrimary = confirm(`Make ${memberName} the primary contact for this joint group?`);
            
            fetch('api/joint-membership.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=assign_member_to_group&member_id=${memberId}&joint_group_id=${currentJointGroupId}&individual_contribution=${contribution}&joint_position_share=0.5&is_primary=${isPrimary ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Member assigned successfully!');
                    refreshMemberAssignments();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }
        
        function removeMember(memberId, memberName) {
            if (confirm(`Remove ${memberName} from this joint group?`)) {
                fetch('api/joint-membership.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=remove_member_from_group&member_id=${memberId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Member removed successfully!');
                        refreshMemberAssignments();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                });
            }
        }
        
        function refreshMemberAssignments() {
            loadAvailableMembers();
            loadAssignedMembers();
            // Refresh the page to update the joint group listing
            setTimeout(() => location.reload(), 1000);
        }
        
        // ========================================
        // POSITION MANAGEMENT FUNCTIONS
        // ========================================
        
        function managePosition(jointGroupId, currentPosition, equbId) {
            document.getElementById('positionJointGroupId').value = jointGroupId;
            document.getElementById('positionEqubId').value = equbId;
            document.getElementById('currentPosition').value = currentPosition;
            document.getElementById('newPosition').value = currentPosition;
            
            // Load available positions
            loadAvailablePositions(equbId, currentPosition);
            
            const modal = new bootstrap.Modal(document.getElementById('positionManagementModal'));
            modal.show();
        }
        
        function loadAvailablePositions(equbId, currentPosition) {
            document.getElementById('availablePositions').innerHTML = 'Loading available positions...';
            
            fetch('api/payout-positions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_positions&equb_id=${equbId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.members) {
                    const positions = data.data.members;
                    const occupiedPositions = positions.map(p => p.payout_position).filter(p => p > 0 && p !== parseInt(currentPosition));
                    
                    // Get EQUB duration
                    fetch('api/joint-membership.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=get_equb_info&equb_id=${equbId}`
                    })
                    .then(response => response.json())
                    .then(equbData => {
                        if (equbData.success) {
                            const duration = equbData.data.duration_months;
                            const availablePositions = [];
                            
                            for (let i = 1; i <= duration; i++) {
                                if (!occupiedPositions.includes(i)) {
                                    availablePositions.push(i);
                                }
                            }
                            
                            let html = `<strong>Duration:</strong> ${duration} months<br>`;
                            html += `<strong>Occupied positions:</strong> ${occupiedPositions.length > 0 ? occupiedPositions.join(', ') : 'None'}<br>`;
                            html += `<strong>Available positions:</strong> ${availablePositions.length > 0 ? availablePositions.join(', ') : 'All positions occupied'}`;
                            
                            document.getElementById('availablePositions').innerHTML = html;
                            
                            // Update input constraints
                            const newPositionInput = document.getElementById('newPosition');
                            newPositionInput.max = duration;
                        }
                    });
                } else {
                    document.getElementById('availablePositions').innerHTML = 'Error loading position data';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('availablePositions').innerHTML = 'Error loading position data';
            });
        }
        
        function savePositionChange() {
            const jointGroupId = document.getElementById('positionJointGroupId').value;
            const newPosition = document.getElementById('newPosition').value;
            
            if (!newPosition || parseInt(newPosition) < 1) {
                alert('Please enter a valid position number');
                return;
            }
            
            if (confirm(`Are you sure you want to change this joint group's position to ${newPosition}?`)) {
                fetch('api/joint-membership.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_group_position&joint_group_id=${jointGroupId}&new_position=${newPosition}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Position updated successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('positionManagementModal')).hide();
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                });
            }
        }
    </script>
</body>
</html>