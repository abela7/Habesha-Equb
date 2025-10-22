<?php
/**
 * HabeshaEqub - Equb Management System
 * Simplified, clean, and fully translated interface
 */

require_once '../includes/db.php';
require_once '../includes/enhanced_equb_calculator.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get equb statistics
try {
    $stmt = $pdo->query("
        SELECT 
            es.*,
            COUNT(DISTINCT m.id) as current_members,
            COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) as collected_amount,
            COALESCE(SUM(CASE WHEN po.status = 'completed' THEN po.net_amount ELSE 0 END), 0) as distributed_amount
        FROM equb_settings es
        LEFT JOIN members m ON m.equb_settings_id = es.id AND m.is_active = 1
        LEFT JOIN payments p ON p.member_id = m.id
        LEFT JOIN payouts po ON po.member_id = m.id
        GROUP BY es.id
        ORDER BY es.created_at DESC
    ");
    $equbs_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate correct pool amounts
    $calculator = getEnhancedEqubCalculator();
    $equbs = [];

    foreach ($equbs_raw as $equb) {
        $equb_calculation = $calculator->calculateEqubPositions($equb['id']);
        if ($equb_calculation['success']) {
            $monthly_pool = $equb_calculation['total_monthly_pool'];
            $duration = $equb['duration_months'];
            $equb['calculated_pool_amount'] = $monthly_pool * $duration;
        } else {
            $equb['calculated_pool_amount'] = 0;
        }
        $equbs[] = $equb;
    }
    
    // Calculate statistics
    $total_equbs = count($equbs);
    $active_equbs = count(array_filter($equbs, fn($e) => $e['status'] === 'active'));
    $total_pool = array_sum(array_column($equbs, 'calculated_pool_amount'));
    $total_members = array_sum(array_column($equbs, 'current_members'));
    
} catch (PDOException $e) {
    error_log("Error fetching equb data: " . $e->getMessage());
    $equbs = [];
    $total_equbs = 0;
    $active_equbs = 0;
    $total_pool = 0;
    $total_members = 0;
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('admin.equb_management.page_title'); ?> - HabeshaEqub</title>
    
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
        /* === SIMPLIFIED EQUB MANAGEMENT DESIGN === */
        
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
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 16px rgba(48, 25, 67, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-title-section h1 {
            font-size: 26px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 4px 0;
        }
        
        .page-title-section p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 14px;
        }
        
        .create-btn {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F766E 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(19, 102, 92, 0.3);
            color: white;
        }
        
        /* Statistics Cards - Compact */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 8px rgba(48, 25, 67, 0.04);
            transition: all 0.3s ease;
            min-width: 0;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(48, 25, 67, 0.08);
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
        
        .stat-icon.teal { background: rgba(19, 102, 92, 0.1); color: var(--color-teal); }
        .stat-icon.gold { background: rgba(233, 196, 106, 0.1); color: var(--color-gold); }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #7C3AED; }
        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #2563EB; }
        
        .stat-value {
            font-size: 24px;
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
        
        /* Quick Actions - Compact */
        .quick-actions {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 8px rgba(48, 25, 67, 0.04);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--border-light);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }
        
        .action-btn {
            background: var(--color-cream);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            background: white;
            border-color: var(--color-teal);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(19, 102, 92, 0.1);
        }
        
        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .action-icon.teal { background: rgba(19, 102, 92, 0.1); color: var(--color-teal); }
        .action-icon.gold { background: rgba(233, 196, 106, 0.1); color: var(--color-gold); }
        .action-icon.purple { background: rgba(139, 92, 246, 0.1); color: #7C3AED; }
        .action-icon.coral { background: rgba(239, 68, 68, 0.1); color: #DC2626; }
        
        .action-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--color-purple);
        }
        
        /* Equb List */
        .equb-list {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 8px rgba(48, 25, 67, 0.04);
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            margin: 0;
            min-width: 800px;
        }
        
        .table thead th {
            background: var(--color-cream);
            color: var(--color-purple);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 12px;
            border-bottom: 2px solid var(--border-light);
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            font-size: 14px;
        }
        
        .table tbody tr:hover {
            background: rgba(233, 196, 106, 0.02);
        }
        
        /* Status Badges */
        .badge-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .badge-active { background: rgba(34, 197, 94, 0.1); color: #059669; }
        .badge-planning { background: rgba(59, 130, 246, 0.1); color: #2563EB; }
        .badge-completed { background: rgba(107, 114, 128, 0.1); color: #6B7280; }
        .badge-suspended { background: rgba(251, 191, 36, 0.1); color: #D97706; }
        
        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 13px;
        }
        
        .btn-action:hover {
            transform: scale(1.1);
        }
        
        .btn-view {
            background: rgba(19, 102, 92, 0.1);
            color: var(--color-teal);
        }
        
        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: #2563EB;
        }
        
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .app-content {
                padding: 15px;
            }
            
            .page-header {
                padding: 20px;
            }
            
            .page-title-section h1 {
                font-size: 22px;
            }
            
            .create-btn {
                width: 100%;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .stat-card {
                padding: 16px;
            }
            
            .stat-value {
                font-size: 20px;
            }
            
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions, .equb-list {
                padding: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .page-title-section h1 {
                font-size: 20px;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                min-width: 700px;
            }
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
                <h1><?php echo t('admin.equb_management.title'); ?></h1>
                <p><?php echo t('admin.equb_management.subtitle'); ?></p>
            </div>
            <button class="create-btn" onclick="window.location.href='equb-management.php?action=create'">
                <i class="fas fa-plus me-2"></i><?php echo t('admin.equb_management.create_new'); ?>
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-value"><?php echo $total_equbs; ?></div>
                <div class="stat-label"><?php echo t('admin.equb_management.stats.total_equbs'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-value"><?php echo $active_equbs; ?></div>
                <div class="stat-label"><?php echo t('admin.equb_management.stats.active_equbs'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-pound-sign"></i>
                </div>
                <div class="stat-value">£<?php echo number_format($total_pool, 0); ?></div>
                <div class="stat-label"><?php echo t('admin.equb_management.stats.total_pool'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $total_members; ?></div>
                <div class="stat-label"><?php echo t('admin.equb_management.stats.total_members'); ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title"><?php echo t('admin.equb_management.quick_actions'); ?></h2>
            <div class="actions-grid">
                <div class="action-btn" onclick="window.location.href='members.php'">
                    <div class="action-icon teal">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="action-title"><?php echo t('admin.equb_management.actions.members'); ?></div>
                </div>
                <div class="action-btn" onclick="window.location.href='payments.php'">
                    <div class="action-icon gold">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="action-title"><?php echo t('admin.equb_management.actions.payments'); ?></div>
                </div>
                <div class="action-btn" onclick="window.location.href='payouts.php'">
                    <div class="action-icon purple">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="action-title"><?php echo t('admin.equb_management.actions.payouts'); ?></div>
                </div>
                <div class="action-btn" onclick="window.location.href='reports.php'">
                    <div class="action-icon coral">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-title"><?php echo t('admin.equb_management.actions.reports'); ?></div>
                </div>
            </div>
        </div>

        <!-- Equb List -->
        <div class="equb-list">
            <h2 class="section-title"><?php echo t('admin.equb_management.equb_terms'); ?></h2>
            
            <?php if (!empty($equbs)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo t('admin.equb_management.table.name'); ?></th>
                                <th><?php echo t('admin.equb_management.table.status'); ?></th>
                                <th><?php echo t('admin.equb_management.table.members'); ?></th>
                                <th><?php echo t('admin.equb_management.table.duration'); ?></th>
                                <th><?php echo t('admin.equb_management.table.pool_amount'); ?></th>
                                <th><?php echo t('admin.equb_management.table.start_date'); ?></th>
                                <th><?php echo t('admin.equb_management.table.actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equbs as $equb): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($equb['equb_name']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($equb['equb_id']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?php echo $equb['status']; ?>">
                                            <?php echo t('admin.equb_management.status.' . $equb['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $equb['current_members']; ?>/<?php echo $equb['max_members']; ?></td>
                                    <td><?php echo $equb['duration_months']; ?> <?php echo t('admin.equb_management.months'); ?></td>
                                    <td class="fw-bold">£<?php echo number_format($equb['calculated_pool_amount'], 0); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($equb['start_date'])); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn-action btn-view" onclick="viewEqub(<?php echo $equb['id']; ?>)" title="<?php echo t('admin.equb_management.actions.view'); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" onclick="editEqub(<?php echo $equb['id']; ?>)" title="<?php echo t('admin.equb_management.actions.edit'); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteEqub(<?php echo $equb['id']; ?>)" title="<?php echo t('admin.equb_management.actions.delete'); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h4 style="color: var(--text-secondary);"><?php echo t('admin.equb_management.no_equbs'); ?></h4>
                    <p style="color: var(--text-secondary);"><?php echo t('admin.equb_management.create_first'); ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div> <!-- End app-content -->
</main> <!-- End app-main -->
</div> <!-- End app-layout -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="../assets/js/auth.js"></script>

<script>
    function viewEqub(id) {
        window.location.href = `equb-management.php?id=${id}&action=view`;
    }

    function editEqub(id) {
        window.location.href = `equb-management.php?id=${id}&action=edit`;
    }

    function deleteEqub(id) {
        if (confirm('<?php echo t('admin.equb_management.confirm_delete'); ?>')) {
            // Implement delete logic
        }
    }
</script>
</body>
</html>

