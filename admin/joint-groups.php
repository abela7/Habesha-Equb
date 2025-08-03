<?php
/**
 * HabeshaEqub - Joint Membership Groups Management
 * Comprehensive management interface for joint EQUB memberships
 */

require_once '../includes/db.php';
require_once '../includes/equb_payout_calculator.php';
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
            COUNT(m.id) as current_members,
            GROUP_CONCAT(
                CONCAT(m.first_name, ' ', m.last_name,
                       CASE WHEN m.primary_joint_member = 1 THEN ' (Primary)' ELSE '' END)
                ORDER BY m.primary_joint_member DESC, m.created_at ASC
                SEPARATOR ', '
            ) as member_names,
            SUM(m.individual_contribution) as total_individual_contributions
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
                    <p class="text-muted">Joint groups will appear here when they are created through member registration.</p>
                    <a href="members.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
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
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewGroupDetails('<?php echo $group['joint_group_id']; ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm ms-1" onclick="calculateGroupPayout('<?php echo $group['joint_group_id']; ?>')">
                                            <i class="fas fa-calculator"></i>
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
                                            <h6 class="mb-1">Monthly Payment</h6>
                                            <div style="font-size: 1.2rem; font-weight: 600;">
                                                £<?php echo number_format($group['total_monthly_payment'], 2); ?>
                                            </div>
                                            <?php if ($group['total_individual_contributions'] > 0): ?>
                                                <small>Individual: £<?php echo number_format($group['total_individual_contributions'], 2); ?></small>
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

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Filter functionality
        document.getElementById('equbFilter').addEventListener('change', filterGroups);
        document.getElementById('searchFilter').addEventListener('input', filterGroups);

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
                if (data.success) {
                    document.getElementById('groupDetailsContent').innerHTML = generateGroupDetailsHTML(data);
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
                if (data.success) {
                    document.getElementById('payoutCalculationContent').innerHTML = generatePayoutCalculationHTML(data);
                } else {
                    document.getElementById('payoutCalculationContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error calculating payout: ${data.error}
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
            return `
                <div class="group-details">
                    <h6>Group Information</h6>
                    <p><strong>Group ID:</strong> ${data.group.joint_group_id}</p>
                    <p><strong>Group Name:</strong> ${data.group.group_name || 'Unnamed'}</p>
                    <p><strong>EQUB:</strong> ${data.group.equb_name}</p>
                    <p><strong>Total Monthly Payment:</strong> £${parseFloat(data.group.total_monthly_payment).toLocaleString()}</p>
                    <p><strong>Split Method:</strong> ${data.group.payout_split_method}</p>
                    <p><strong>Payout Position:</strong> ${data.group.payout_position}</p>
                    
                    <h6 class="mt-4">Members</h6>
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
                                ${data.members.map(member => `
                                    <tr>
                                        <td>${member.first_name} ${member.last_name}</td>
                                        <td>${member.role}</td>
                                        <td>£${parseFloat(member.individual_contribution || 0).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        function generatePayoutCalculationHTML(data) {
            // This function would generate the payout calculation details
            return `
                <div class="payout-calculation">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>Calculation Complete</h6>
                        <p class="mb-0">Traditional EQUB calculation method applied.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Group Payout</h6>
                            <p><strong>Gross Amount:</strong> £${parseFloat(data.gross_payout || 0).toLocaleString()}</p>
                            <p><strong>Admin Fee:</strong> £${parseFloat(data.admin_fee || 0).toLocaleString()}</p>
                            <p><strong>Net Amount:</strong> £${parseFloat(data.net_payout || 0).toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Details</h6>
                            <p><strong>Monthly Payment:</strong> £${parseFloat(data.monthly_payment || 0).toLocaleString()}</p>
                            <p><strong>Duration:</strong> ${data.duration_months || 0} months</p>
                            <p><strong>Calculation:</strong> ${data.calculation_method || 'Traditional EQUB'}</p>
                        </div>
                    </div>
                    
                    ${data.joint_split_details ? `
                        <h6 class="mt-4">Individual Splits</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Share %</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${Object.values(data.joint_split_details).map(split => `
                                        <tr>
                                            <td>${split.member_name}</td>
                                            <td>${split.share_percentage}%</td>
                                            <td>£${parseFloat(split.net_amount).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : ''}
                </div>
            `;
        }
    </script>
</body>
</html>