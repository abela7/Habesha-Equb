<?php
/**
 * HabeshaEqub - Admin Position Swap Management
 * Manage and process member position swap requests
 */

require_once 'includes/admin_auth_guard.php';

// Get current admin info
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Include language handler
require_once '../languages/translator.php';

// Strong cache buster
$cache_buster = time() . '_' . rand(1000, 9999);

try {
    // Get swap request statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN DATE(requested_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAYS) THEN 1 ELSE 0 END) as this_month_count
        FROM position_swap_requests
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all swap requests with member information
    $stmt = $pdo->prepare("
        SELECT 
            psr.*,
            CONCAT(m.first_name, ' ', m.last_name) as member_name,
            m.email as member_email,
            m.member_id,
            CONCAT(tm.first_name, ' ', tm.last_name) as target_member_name,
            CONCAT(admin.username) as processed_by_name
        FROM position_swap_requests psr
        LEFT JOIN members m ON psr.member_id = m.id
        LEFT JOIN members tm ON psr.target_member_id = tm.id
        LEFT JOIN admins admin ON psr.processed_by_admin_id = admin.id
        ORDER BY 
            CASE psr.status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'rejected' THEN 3 
                WHEN 'completed' THEN 4 
                WHEN 'cancelled' THEN 5 
            END,
            psr.requested_date DESC
    ");
    $stmt->execute();
    $swap_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the number of requests found
    error_log("DEBUG: Found " . count($swap_requests) . " swap requests");
    
    // Debug: If no requests, check if table exists
    if (empty($swap_requests)) {
        $test_stmt = $pdo->prepare("SHOW TABLES LIKE 'position_swap_requests'");
        $test_stmt->execute();
        $table_exists = $test_stmt->fetch();
        error_log("DEBUG: Table exists check: " . ($table_exists ? "YES" : "NO"));
        
        if ($table_exists) {
            // Check total records in table
            $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM position_swap_requests");
            $count_stmt->execute();
            $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC);
            error_log("DEBUG: Total records in position_swap_requests: " . $total_records['total']);
        }
    }

    // Get recent swap history
    $stmt = $pdo->prepare("
        SELECT 
            psh.*,
            CONCAT(ma.first_name, ' ', ma.last_name) as member_a_name,
            CONCAT(mb.first_name, ' ', mb.last_name) as member_b_name,
            CONCAT(admin.username) as processed_by_name
        FROM position_swap_history psh
        LEFT JOIN members ma ON psh.member_a_id = ma.id
        LEFT JOIN members mb ON psh.member_b_id = mb.id
        LEFT JOIN admins admin ON psh.processed_by_admin_id = admin.id
        ORDER BY psh.swap_date DESC
        LIMIT 20
    ");
    $stmt->execute();
    $swap_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Swap management page error: " . $e->getMessage());
    $error_message = "Database error occurred: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('swap_management.page_title'); ?> - HabeshaEqub Admin</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo $cache_buster; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <style>
    /* Swap Management Specific Styles */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--color-white);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid var(--color-border);
        box-shadow: 0 4px 16px rgba(48, 25, 52, 0.08);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--color-deep-purple);
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 14px;
        color: var(--color-dark-purple);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card.pending .stat-number {
        color: #856404;
    }

    .stat-card.approved .stat-number {
        color: #155724;
    }

    .stat-card.rejected .stat-number {
        color: #721c24;
    }

    .stat-card.completed .stat-number {
        color: #0c5460;
    }

    .requests-table {
        background: var(--color-white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(48, 25, 52, 0.08);
    }

    .table th {
        background: var(--color-light-gold);
        color: var(--color-deep-purple);
        font-weight: 600;
        border: none;
        padding: 15px 12px;
    }

    .table td {
        padding: 12px;
        vertical-align: middle;
        border-color: rgba(0,0,0,0.05);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-completed {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .status-cancelled {
        background: #e2e3e5;
        color: #495057;
        border: 1px solid #d1d3d4;
    }

    .priority-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .priority-low {
        background: #e3f2fd;
        color: #1976d2;
    }

    .priority-medium {
        background: #fff3e0;
        color: #f57c00;
    }

    .priority-high {
        background: #ffebee;
        color: #d32f2f;
    }

    .priority-urgent {
        background: #ff1744;
        color: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-approve {
        background: #28a745;
        color: white;
    }

    .btn-approve:hover {
        background: #218838;
        color: white;
        transform: translateY(-1px);
    }

    .btn-reject {
        background: #dc3545;
        color: white;
    }

    .btn-reject:hover {
        background: #c82333;
        color: white;
        transform: translateY(-1px);
    }

    .btn-view {
        background: #007bff;
        color: white;
    }

    .btn-view:hover {
        background: #0056b3;
        color: white;
        transform: translateY(-1px);
    }

    .filters-section {
        background: var(--color-white);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid var(--color-border);
    }

    .filter-group {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-item label {
        font-size: 12px;
        font-weight: 600;
        color: var(--color-dark-purple);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-item select {
        border: 1px solid var(--color-border);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 14px;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 14px;
    }

    .tabs-container {
        margin-bottom: 20px;
    }

    .nav-tabs .nav-link {
        border: none;
        background: none;
        color: var(--color-dark-purple);
        font-weight: 500;
        padding: 12px 20px;
        margin-right: 5px;
        border-radius: 8px 8px 0 0;
    }

    .nav-tabs .nav-link.active {
        background: var(--color-white);
        color: var(--color-deep-purple);
        border-bottom: 3px solid var(--color-gold);
    }

    .member-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .member-name {
        font-weight: 600;
        color: var(--color-deep-purple);
    }

    .member-id {
        font-size: 11px;
        color: var(--color-dark-purple);
        opacity: 0.7;
    }

    .position-change {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .position-arrow {
        color: var(--color-gold);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 4px;
        }
        
        .filter-group {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-item {
            width: 100%;
        }
    }
    </style>
</head>

<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>
                <i class="fas fa-exchange-alt text-primary me-2"></i>
                <?php echo t('swap_management.page_title'); ?>
            </h1>
            <p><?php echo t('swap_management.page_subtitle'); ?></p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Debug Info -->
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            Total requests found: <?php echo count($swap_requests); ?><br>
            Database connection: <?php echo $pdo ? "✅ Connected" : "❌ Failed"; ?><br>
            <?php if (empty($swap_requests)): ?>
                <em>No swap requests found. This could mean:</em><br>
                • No requests have been submitted yet<br>
                • Database tables haven't been created<br>
                • Database connection issue<br>
                <strong>First, submit a test request from the member portal to verify the system.</strong>
            <?php endif; ?>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_requests']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.total_requests'); ?></div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $stats['pending_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.pending_count'); ?></div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?php echo $stats['approved_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.approved_count'); ?></div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-number"><?php echo $stats['rejected_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.rejected_count'); ?></div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number"><?php echo $stats['completed_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.completed_count'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['this_month_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.this_month'); ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="filter-group">
                <div class="filter-item">
                    <label><?php echo t('swap_management.filter_by_status'); ?></label>
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending"><?php echo t('position_swap.pending'); ?></option>
                        <option value="approved"><?php echo t('position_swap.approved'); ?></option>
                        <option value="rejected"><?php echo t('position_swap.rejected'); ?></option>
                        <option value="completed"><?php echo t('position_swap.completed'); ?></option>
                        <option value="cancelled"><?php echo t('position_swap.cancelled'); ?></option>
                    </select>
                </div>
                <div class="filter-item">
                    <label><?php echo t('swap_management.priority'); ?></label>
                    <select id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent"><?php echo t('swap_management.urgent'); ?></option>
                        <option value="high"><?php echo t('swap_management.high'); ?></option>
                        <option value="medium"><?php echo t('swap_management.medium'); ?></option>
                        <option value="low"><?php echo t('swap_management.low'); ?></option>
                    </select>
                </div>
                <div class="search-box">
                    <label><?php echo t('swap_management.search_requests'); ?></label>
                    <input type="text" id="searchBox" placeholder="Search by member name, request ID...">
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <ul class="nav nav-tabs" id="swapTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-requests-tab" data-bs-toggle="tab" 
                            data-bs-target="#all-requests" type="button" role="tab">
                        <i class="fas fa-list me-2"></i>
                        <?php echo t('swap_management.all_requests'); ?>
                        <span class="badge bg-secondary ms-2"><?php echo count($swap_requests); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-requests-tab" data-bs-toggle="tab" 
                            data-bs-target="#pending-requests" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>
                        <?php echo t('swap_management.pending_requests'); ?>
                        <span class="badge bg-warning ms-2"><?php echo $stats['pending_count']; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="swap-history-tab" data-bs-toggle="tab" 
                            data-bs-target="#swap-history" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>
                        <?php echo t('swap_management.swap_history'); ?>
                        <span class="badge bg-info ms-2"><?php echo count($swap_history); ?></span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="swapTabsContent">
            <!-- All Requests Tab -->
            <div class="tab-pane fade show active" id="all-requests" role="tabpanel">
                <div class="requests-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="requestsTable">
                            <thead>
                                <tr>
                                    <th><?php echo t('position_swap.request_id'); ?></th>
                                    <th><?php echo t('swap_management.member_name'); ?></th>
                                    <th><?php echo t('swap_management.current_pos'); ?> → <?php echo t('swap_management.requested_pos'); ?></th>
                                    <th><?php echo t('swap_management.target_member'); ?></th>
                                    <th><?php echo t('swap_management.priority'); ?></th>
                                    <th><?php echo t('position_swap.status'); ?></th>
                                    <th><?php echo t('position_swap.date_requested'); ?></th>
                                    <th><?php echo t('swap_management.actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($swap_requests)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No swap requests found</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($swap_requests as $request): ?>
                                    <tr data-status="<?php echo $request['status']; ?>" 
                                        data-priority="<?php echo $request['priority_level']; ?>"
                                        data-member="<?php echo strtolower($request['member_name']); ?>"
                                        data-request-id="<?php echo strtolower($request['request_id']); ?>">
                                        <td>
                                            <code class="small"><?php echo htmlspecialchars($request['request_id']); ?></code>
                                        </td>
                                        <td>
                                            <div class="member-info">
                                                <div class="member-name"><?php echo htmlspecialchars($request['member_name']); ?></div>
                                                <div class="member-id"><?php echo htmlspecialchars($request['member_id']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="position-change">
                                                <span class="badge bg-secondary"><?php echo $request['current_position']; ?></span>
                                                <i class="fas fa-arrow-right position-arrow"></i>
                                                <span class="badge bg-primary"><?php echo $request['requested_position']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($request['target_member_name']): ?>
                                                <?php echo htmlspecialchars($request['target_member_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Available</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $request['priority_level']; ?>">
                                                <?php echo t('swap_management.' . $request['priority_level']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $request['status']; ?>">
                                                <?php echo t('position_swap.' . $request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($request['requested_date'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button class="btn-action btn-approve" 
                                                            onclick="processSwapRequest('<?php echo $request['request_id']; ?>', 'approve')">
                                                        <i class="fas fa-check"></i>
                                                        <?php echo t('swap_management.approve'); ?>
                                                    </button>
                                                    <button class="btn-action btn-reject" 
                                                            onclick="processSwapRequest('<?php echo $request['request_id']; ?>', 'reject')">
                                                        <i class="fas fa-times"></i>
                                                        <?php echo t('swap_management.reject'); ?>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn-action btn-view" 
                                                        onclick="viewSwapDetails('<?php echo $request['request_id']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                    <?php echo t('swap_management.view_details'); ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Tab -->
            <div class="tab-pane fade" id="pending-requests" role="tabpanel">
                <div class="requests-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo t('position_swap.request_id'); ?></th>
                                    <th><?php echo t('swap_management.member_name'); ?></th>
                                    <th><?php echo t('swap_management.current_pos'); ?> → <?php echo t('swap_management.requested_pos'); ?></th>
                                    <th><?php echo t('swap_management.reason'); ?></th>
                                    <th><?php echo t('position_swap.date_requested'); ?></th>
                                    <th><?php echo t('swap_management.actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $pending_requests = array_filter($swap_requests, function($r) { 
                                    return $r['status'] === 'pending'; 
                                });
                                ?>
                                <?php if (empty($pending_requests)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p class="text-muted">No pending requests</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($pending_requests as $request): ?>
                                    <tr>
                                        <td>
                                            <code class="small"><?php echo htmlspecialchars($request['request_id']); ?></code>
                                        </td>
                                        <td>
                                            <div class="member-info">
                                                <div class="member-name"><?php echo htmlspecialchars($request['member_name']); ?></div>
                                                <div class="member-id"><?php echo htmlspecialchars($request['member_id']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="position-change">
                                                <span class="badge bg-secondary"><?php echo $request['current_position']; ?></span>
                                                <i class="fas fa-arrow-right position-arrow"></i>
                                                <span class="badge bg-primary"><?php echo $request['requested_position']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($request['reason']): ?>
                                                <span title="<?php echo htmlspecialchars($request['reason']); ?>">
                                                    <?php echo substr(htmlspecialchars($request['reason']), 0, 50) . (strlen($request['reason']) > 50 ? '...' : ''); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No reason provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($request['requested_date'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-approve" 
                                                        onclick="processSwapRequest('<?php echo $request['request_id']; ?>', 'approve')">
                                                    <i class="fas fa-check"></i>
                                                    <?php echo t('swap_management.approve'); ?>
                                                </button>
                                                <button class="btn-action btn-reject" 
                                                        onclick="processSwapRequest('<?php echo $request['request_id']; ?>', 'reject')">
                                                    <i class="fas fa-times"></i>
                                                    <?php echo t('swap_management.reject'); ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Swap History Tab -->
            <div class="tab-pane fade" id="swap-history" role="tabpanel">
                <div class="requests-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo t('swap_management.member_name'); ?> A</th>
                                    <th><?php echo t('swap_management.member_name'); ?> B</th>
                                    <th>Position Change A</th>
                                    <th>Position Change B</th>
                                    <th><?php echo t('swap_management.processed_by'); ?></th>
                                    <th><?php echo t('swap_management.completion_date'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($swap_history)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No swap history found</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($swap_history as $history): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($history['member_a_name']); ?></td>
                                        <td><?php echo htmlspecialchars($history['member_b_name']); ?></td>
                                        <td>
                                            <div class="position-change">
                                                <span class="badge bg-secondary"><?php echo $history['position_a_before']; ?></span>
                                                <i class="fas fa-arrow-right position-arrow"></i>
                                                <span class="badge bg-success"><?php echo $history['position_a_after']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="position-change">
                                                <span class="badge bg-secondary"><?php echo $history['position_b_before']; ?></span>
                                                <i class="fas fa-arrow-right position-arrow"></i>
                                                <span class="badge bg-success"><?php echo $history['position_b_after']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($history['processed_by_name']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($history['swap_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Request Modal -->
    <div class="modal fade" id="processRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalTitle">Process Swap Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="processRequestForm">
                        <input type="hidden" id="processRequestId" name="request_id">
                        <input type="hidden" id="processAction" name="action">
                        
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label">
                                <?php echo t('swap_management.admin_notes'); ?>
                            </label>
                            <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"
                                      placeholder="<?php echo t('swap_management.admin_notes_placeholder'); ?>"></textarea>
                        </div>
                        
                        <div class="alert alert-info" id="processConfirmation">
                            <!-- Dynamic content based on action -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmProcessBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        const statusFilter = document.getElementById('statusFilter');
        const priorityFilter = document.getElementById('priorityFilter');
        const searchBox = document.getElementById('searchBox');
        const requestsTable = document.getElementById('requestsTable');
        
        function filterTable() {
            const statusValue = statusFilter.value.toLowerCase();
            const priorityValue = priorityFilter.value.toLowerCase();
            const searchValue = searchBox.value.toLowerCase();
            const rows = requestsTable.querySelectorAll('tbody tr[data-status]');
            
            rows.forEach(row => {
                const status = row.dataset.status;
                const priority = row.dataset.priority;
                const member = row.dataset.member;
                const requestId = row.dataset.requestId;
                
                const statusMatch = !statusValue || status === statusValue;
                const priorityMatch = !priorityValue || priority === priorityValue;
                const searchMatch = !searchValue || 
                    member.includes(searchValue) || 
                    requestId.includes(searchValue);
                
                if (statusMatch && priorityMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        statusFilter.addEventListener('change', filterTable);
        priorityFilter.addEventListener('change', filterTable);
        searchBox.addEventListener('input', filterTable);
        
        // Process request form
        const processModal = new bootstrap.Modal(document.getElementById('processRequestModal'));
        const processForm = document.getElementById('processRequestForm');
        
        processForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const confirmBtn = document.getElementById('confirmProcessBtn');
            const originalText = confirmBtn.textContent;
            
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Processing...';
            
            try {
                const response = await fetch('api/position-swap-management.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    processModal.hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(result.message, 'danger');
                }
            } catch (error) {
                showAlert('<?php echo t("swap_management.error_processing"); ?>', 'danger');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }
        });
        
        // Confirm process button click
        document.getElementById('confirmProcessBtn').addEventListener('click', function() {
            processForm.dispatchEvent(new Event('submit'));
        });
    });
    
    function processSwapRequest(requestId, action) {
        document.getElementById('processRequestId').value = requestId;
        document.getElementById('processAction').value = action;
        
        const modal = document.getElementById('processRequestModal');
        const title = document.getElementById('processModalTitle');
        const confirmation = document.getElementById('processConfirmation');
        const confirmBtn = document.getElementById('confirmProcessBtn');
        
        if (action === 'approve') {
            title.textContent = '<?php echo t("swap_management.approve_request"); ?>';
            confirmation.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i><?php echo t("swap_management.confirm_approval"); ?>';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.textContent = '<?php echo t("swap_management.approve"); ?>';
        } else if (action === 'reject') {
            title.textContent = '<?php echo t("swap_management.reject_request"); ?>';
            confirmation.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i><?php echo t("swap_management.confirm_rejection"); ?>';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.textContent = '<?php echo t("swap_management.reject"); ?>';
        }
        
        const processModal = new bootstrap.Modal(modal);
        processModal.show();
    }
    
    function viewSwapDetails(requestId) {
        // This could open a detailed modal or navigate to details page
        alert('Detailed view functionality coming soon for request: ' + requestId);
    }
    
    // Alert system
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[alerts.length - 1].remove();
            }
        }, 5000);
    }
    </script>
</body>
</html>
