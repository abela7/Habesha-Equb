<?php
/**
 * HabeshaEqub - Advanced Financial Analytics Dashboard
 * Top-tier financial monitoring and analytics for EQUB management
 */

require_once '../includes/db.php';
require_once '../includes/equb_payout_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get selected equb or default to first active equb
$selected_equb_id = intval($_GET['equb_id'] ?? 0);

// Get all equbs for selection
try {
    $stmt = $pdo->query("
        SELECT id, equb_id, equb_name, status, start_date, end_date, total_pool_amount
        FROM equb_settings 
        ORDER BY 
            CASE WHEN status = 'active' THEN 1 WHEN status = 'planning' THEN 2 ELSE 3 END,
            created_at DESC
    ");
    $all_equbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$selected_equb_id && !empty($all_equbs)) {
        $selected_equb_id = $all_equbs[0]['id'];
    }
} catch (PDOException $e) {
    $all_equbs = [];
    error_log("Error fetching equbs: " . $e->getMessage());
}

// Get comprehensive financial data for selected equb
$financial_data = [];
$joint_groups_summary = [];
$member_calculations = [];

if ($selected_equb_id) {
    try {
        // Initialize calculator
        $calculator = getEqubPayoutCalculator();
        
        // Get equb financial summary
        $equb_summary = $calculator->getEqubPoolSummary($selected_equb_id);
        
        // Get joint groups summary
        $stmt = $pdo->prepare("
            SELECT 
                jmg.*,
                COUNT(m.id) as current_members,
                GROUP_CONCAT(
                    CONCAT(m.first_name, ' ', m.last_name,
                           CASE WHEN m.primary_joint_member = 1 THEN ' (Primary)' ELSE '' END)
                    SEPARATOR ', '
                ) as member_names
            FROM joint_membership_groups jmg
            LEFT JOIN members m ON jmg.joint_group_id = m.joint_group_id AND m.is_active = 1
            WHERE jmg.equb_settings_id = ? AND jmg.is_active = 1
            GROUP BY jmg.id
            ORDER BY jmg.payout_position ASC
        ");
        $stmt->execute([$selected_equb_id]);
        $joint_groups_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get POSITION-BASED calculations (joint groups as single entities)
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN m.membership_type = 'joint' THEN CONCAT('JOINT_', m.joint_group_id)
                    ELSE CONCAT('IND_', m.id)
                END as position_id,
                CASE 
                    WHEN m.membership_type = 'joint' THEN COALESCE(jmg.group_name, 'Joint Group')
                    ELSE CONCAT(m.first_name, ' ', m.last_name)
                END as display_name,
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.joint_group_id
                    ELSE CONCAT('IND_', m.member_id)
                END as identifier,
                m.membership_type,
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
                    ELSE m.monthly_payment
                END as monthly_payment,
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.payout_position
                    ELSE m.payout_position
                END as payout_position,
                m.joint_group_id, jmg.group_name,
                CASE 
                    WHEN m.membership_type = 'joint' THEN SUM(m.total_contributed)
                    ELSE m.total_contributed
                END as total_contributed,
                MAX(m.has_received_payout) as has_received_payout,
                MIN(m.id) as primary_member_id
            FROM members m
            LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
            WHERE m.equb_settings_id = ? AND m.is_active = 1
            GROUP BY 
                CASE 
                    WHEN m.membership_type = 'joint' THEN m.joint_group_id
                    ELSE m.id
                END
            ORDER BY 
                CASE 
                    WHEN m.membership_type = 'joint' THEN jmg.payout_position
                    ELSE m.payout_position
                END ASC
        ");
        $stmt->execute([$selected_equb_id]);
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate payouts for each POSITION (individual or joint group)
        foreach ($positions as $position) {
            if ($position['membership_type'] === 'joint') {
                // For joint groups, calculate based on the joint group
                $calculation = $calculator->calculateJointGroupPayout($position['joint_group_id']);
            } else {
                // For individuals, calculate normally
                $calculation = $calculator->calculateMemberPayoutAmount($position['primary_member_id']);
            }
            
            if ($calculation['success']) {
                $member_calculations[] = array_merge($position, [
                    'calculated_payout' => $calculation['net_payout'],
                    'gross_payout' => $calculation['gross_payout'],
                    'admin_fee' => $calculation['admin_fee'],
                    'calculation_verified' => true
                ]);
            } else {
                $member_calculations[] = array_merge($position, [
                    'calculated_payout' => 0,
                    'gross_payout' => 0,
                    'admin_fee' => 0,
                    'calculation_verified' => false,
                    'error' => $calculation['error'] ?? 'Calculation failed'
                ]);
            }
        }
        
        // Generate comprehensive financial audit
        $financial_audit = $calculator->generateFinancialAudit($selected_equb_id);
        
    } catch (Exception $e) {
        error_log("Error in financial analytics: " . $e->getMessage());
        $equb_summary = ['success' => false, 'error' => $e->getMessage()];
    }
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('financial_audit.comprehensive_audit'); ?> - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .analytics-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
        }
        
        .analytics-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            transition: all 0.3s ease;
        }
        
        .analytics-card:hover {
            box-shadow: 0 12px 48px rgba(48, 25, 67, 0.12);
            transform: translateY(-2px);
        }
        
        .metric-card {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            color: var(--white);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(218, 165, 32, 0.2);
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .joint-group-card {
            background: linear-gradient(135deg, var(--light-purple) 0%, #F8F6FF 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--purple);
        }
        
        .member-row {
            background: var(--white);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--gold);
            box-shadow: 0 2px 8px rgba(48, 25, 67, 0.05);
        }
        
        .status-verified {
            color: var(--success);
            font-weight: 600;
        }
        
        .status-error {
            color: var(--danger);
            font-weight: 600;
        }
        
        .chart-container {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <div class="analytics-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-chart-line text-gold me-3"></i><?php echo t('financial_audit.comprehensive_audit'); ?></h1>
                    <p class="mb-0 text-muted">Real-time financial monitoring and analytics</p>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="equbSelector" onchange="window.location.href='financial-analytics.php?equb_id='+this.value">
                        <option value="">Select EQUB Term...</option>
                        <?php foreach ($all_equbs as $equb): ?>
                            <option value="<?php echo $equb['id']; ?>" <?php echo $equb['id'] == $selected_equb_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($equb['equb_name'] . ' (' . $equb['equb_id'] . ')'); ?>
                                - <?php echo ucfirst($equb['status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php if ($selected_equb_id && isset($equb_summary) && $equb_summary['success']): ?>
            <!-- Financial Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value">£<?php echo number_format($equb_summary['financial_projections']['projected_total_collection'], 0); ?></div>
                        <div class="metric-label"><?php echo t('financial_audit.projected_collections'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value">£<?php echo number_format($equb_summary['financial_projections']['projected_net_distribution'], 0); ?></div>
                        <div class="metric-label"><?php echo t('financial_audit.projected_distributions'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value"><?php echo $equb_summary['member_statistics']['total_active_members']; ?></div>
                        <div class="metric-label">Total Members</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value"><?php echo count($joint_groups_summary); ?></div>
                        <div class="metric-label"><?php echo t('joint_membership.title'); ?> Groups</div>
                    </div>
                </div>
            </div>

            <!-- Financial Health Chart -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h4><i class="fas fa-chart-pie text-primary me-2"></i><?php echo t('financial_audit.financial_health'); ?></h4>
                        <canvas id="financialHealthChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="analytics-card">
                        <h5><i class="fas fa-heartbeat text-success me-2"></i>Health Indicators</h5>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?php echo t('financial_audit.collection_rate'); ?>:</span>
                                <strong class="text-success"><?php echo $equb_summary['financial_health']['collected_percentage']; ?>%</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?php echo t('financial_audit.distribution_rate'); ?>:</span>
                                <strong class="text-info"><?php echo $equb_summary['financial_health']['distribution_percentage']; ?>%</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?php echo t('financial_audit.outstanding_balance'); ?>:</span>
                                <strong class="text-warning">£<?php echo number_format($equb_summary['financial_health']['outstanding_balance'], 0); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Joint Groups Summary -->
            <?php if (!empty($joint_groups_summary)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="analytics-card">
                        <h4><i class="fas fa-users text-purple me-2"></i><?php echo t('joint_membership.joint_group_summary'); ?></h4>
                        <div class="row">
                            <?php foreach ($joint_groups_summary as $group): ?>
                            <div class="col-md-6 mb-3">
                                <div class="joint-group-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($group['group_name'] ?: $group['joint_group_id']); ?></h6>
                                        <span class="badge bg-primary">Position <?php echo $group['payout_position']; ?></span>
                                    </div>
                                    <p class="mb-2"><strong>Members:</strong> <?php echo htmlspecialchars($group['member_names']); ?></p>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Monthly Payment:</small><br>
                                            <strong>£<?php echo number_format($group['total_monthly_payment'], 2); ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Split Method:</small><br>
                                            <strong><?php echo ucfirst($group['payout_split_method']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Member Calculations -->
            <div class="row">
                <div class="col-12">
                    <div class="analytics-card">
                        <h4><i class="fas fa-calculator text-gold me-2"></i><?php echo t('financial_audit.member_calculations'); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Position</th>
                                        <th>Type</th>
                                        <th>Monthly Payment</th>
                                        <th>Calculated Payout</th>
                                        <th>Admin Fee</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($member_calculations as $position): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($position['display_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($position['identifier']); ?></small>
                                        </td>
                                        <td><span class="badge bg-info"><?php echo $position['payout_position']; ?></span></td>
                                        <td>
                                            <?php if ($position['membership_type'] === 'joint'): ?>
                                                <span class="badge bg-purple">Joint Position</span><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($position['joint_group_id']); ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Individual Position</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>£<?php echo number_format($position['monthly_payment'], 2); ?></td>
                                        <td>
                                            <strong>£<?php echo number_format($position['calculated_payout'], 2); ?></strong><br>
                                            <small class="text-muted">Gross: £<?php echo number_format($position['gross_payout'], 2); ?></small>
                                        </td>
                                        <td>£<?php echo number_format($position['admin_fee'], 2); ?></td>
                                        <td>
                                            <?php if ($position['calculation_verified']): ?>
                                                <span class="status-verified"><i class="fas fa-check-circle"></i> Verified</span>
                                            <?php else: ?>
                                                <span class="status-error"><i class="fas fa-exclamation-triangle"></i> Error</span>
                                                <?php if (isset($position['error'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($position['error']); ?></small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selected_equb_id): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Analysis Error</h5>
                <p class="mb-0">Unable to generate financial analysis for selected EQUB. Please check the EQUB configuration.</p>
                <?php if (isset($equb_summary['error'])): ?>
                    <small class="text-muted">Error: <?php echo htmlspecialchars($equb_summary['error']); ?></small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>No EQUB Selected</h5>
                <p class="mb-0">Please select an EQUB term from the dropdown above to view financial analytics.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Financial Health Chart
        <?php if ($selected_equb_id && isset($equb_summary) && $equb_summary['success']): ?>
        const ctx = document.getElementById('financialHealthChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Collected', 'Outstanding', 'Distributed'],
                datasets: [{
                    data: [
                        <?php echo $equb_summary['equb_info']['collected_amount']; ?>,
                        <?php echo $equb_summary['financial_health']['outstanding_balance']; ?>,
                        <?php echo $equb_summary['equb_info']['distributed_amount']; ?>
                    ],
                    backgroundColor: [
                        'var(--success)',
                        'var(--warning)', 
                        'var(--info)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>