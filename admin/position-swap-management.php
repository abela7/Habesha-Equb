<?php
/**
 * HabeshaEqub - Admin Position Swap Management
 * Complete rebuild - fully functional page
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
            m.phone as member_phone,
            CONCAT(tm.first_name, ' ', tm.last_name) as target_member_name,
            tm.member_id as target_member_id_code,
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
    /* Modern Swap Management Styles */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--color-white);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid rgba(48, 25, 52, 0.08);
        box-shadow: 0 8px 32px rgba(48, 25, 52, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--color-gold), var(--color-teal));
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(48, 25, 52, 0.15);
    }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--color-white);
        background: linear-gradient(135deg, var(--color-gold), var(--color-teal));
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--color-deep-purple);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 14px;
        color: var(--color-dark-purple);
        font-weight: 500;
    }

    .stat-card.pending .stat-icon {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
    }

    .stat-card.approved .stat-icon {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .stat-card.rejected .stat-icon {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .stat-card.completed .stat-icon {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .requests-section {
        background: var(--color-white);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(48, 25, 52, 0.1);
        border: 1px solid rgba(48, 25, 52, 0.08);
    }

    .section-header {
        background: linear-gradient(135deg, var(--color-light-gold), var(--color-light-teal));
        padding: 24px 32px;
        border-bottom: 1px solid rgba(48, 25, 52, 0.08);
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--color-deep-purple);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title i {
        font-size: 28px;
        color: var(--color-gold);
    }

    .filters-bar {
        padding: 20px 32px;
        background: var(--color-light-background);
        border-bottom: 1px solid rgba(48, 25, 52, 0.08);
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-group label {
        font-size: 14px;
        font-weight: 600;
        color: var(--color-dark-purple);
    }

    .filter-group select,
    .filter-group input {
        border: 1px solid rgba(48, 25, 52, 0.2);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 14px;
        background: var(--color-white);
        transition: border-color 0.3s ease;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: var(--color-gold);
        outline: none;
        box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
    }

    .requests-table {
        width: 100%;
        background: var(--color-white);
    }

    .requests-table .table {
        margin: 0;
    }

    .requests-table .table thead th {
        background: var(--color-light-background);
        color: var(--color-deep-purple);
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding: 20px 16px;
        border-bottom: 2px solid rgba(48, 25, 52, 0.08);
    }

    .requests-table .table tbody td {
        padding: 20px 16px;
        border-color: rgba(48, 25, 52, 0.05);
        vertical-align: middle;
        font-size: 14px;
    }

    .requests-table .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .requests-table .table tbody tr:hover {
        background-color: rgba(48, 25, 52, 0.02);
    }

    .member-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .member-name {
        font-weight: 600;
        color: var(--color-deep-purple);
        font-size: 15px;
    }

    .member-details {
        font-size: 12px;
        color: var(--color-dark-purple);
        opacity: 0.8;
    }

    .position-change {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
    }

    .position-badge {
        background: var(--color-light-purple);
        color: var(--color-deep-purple);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        min-width: 32px;
        text-align: center;
    }

    .position-arrow {
        color: var(--color-gold);
        font-size: 16px;
    }

    .requested-badge {
        background: var(--color-light-teal);
        color: var(--color-dark-purple);
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-completed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-cancelled {
        background: #f3f4f6;
        color: #374151;
    }

    .priority-badge {
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-low {
        background: #e0f2fe;
        color: #0277bd;
    }

    .priority-medium {
        background: #fff3e0;
        color: #ef6c00;
    }

    .priority-high {
        background: #ffebee;
        color: #c62828;
    }

    .priority-urgent {
        background: #d32f2f;
        color: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-action {
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-approve {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .btn-approve:hover {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-reject {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .btn-reject:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-view {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .btn-view:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--color-dark-purple);
    }

    .empty-state i {
        font-size: 64px;
        color: var(--color-light-purple);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--color-deep-purple);
    }

    .empty-state p {
        font-size: 16px;
        opacity: 0.7;
        margin-bottom: 0;
    }

    .process-modal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .process-modal .modal-header {
        background: linear-gradient(135deg, var(--color-light-gold), var(--color-light-teal));
        border-bottom: 1px solid rgba(48, 25, 52, 0.08);
        border-radius: 16px 16px 0 0;
        padding: 24px 32px;
    }

    .process-modal .modal-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--color-deep-purple);
        margin: 0;
    }

    .process-modal .modal-body {
        padding: 32px;
    }

    .process-modal .modal-footer {
        border-top: 1px solid rgba(48, 25, 52, 0.08);
        padding: 24px 32px;
    }

    .alert-info {
        background: linear-gradient(135deg, var(--color-light-teal), rgba(72, 187, 120, 0.1));
        border: 1px solid var(--color-teal);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    .alert-info strong {
        color: var(--color-deep-purple);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        
        .section-header {
            padding: 20px 24px;
        }
        
        .section-title {
            font-size: 20px;
        }
        
        .filters-bar {
            padding: 16px 24px;
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 4px;
        }
        
        .btn-action {
            width: 100%;
            justify-content: center;
        }
        
        .requests-table .table thead th {
            font-size: 12px;
            padding: 12px 8px;
        }
        
        .requests-table .table tbody td {
            padding: 12px 8px;
            font-size: 13px;
        }
    }
    </style>
</head>

<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
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

        <!-- Statistics Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $stats['total_requests']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.total_requests'); ?></div>
            </div>
            
            <div class="stat-card pending">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $stats['pending_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.pending_count'); ?></div>
            </div>
            
            <div class="stat-card approved">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $stats['approved_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.approved_count'); ?></div>
            </div>
            
            <div class="stat-card rejected">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $stats['rejected_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.rejected_count'); ?></div>
            </div>
            
            <div class="stat-card completed">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $stats['completed_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.completed_count'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $stats['this_month_count']; ?></div>
                <div class="stat-label"><?php echo t('swap_management.this_month'); ?></div>
            </div>
        </div>

        <!-- Main Requests Section -->
        <div class="requests-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-exchange-alt"></i>
                    <?php echo t('swap_management.all_requests'); ?>
                </h2>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> <?php echo t('swap_management.filter_by_status'); ?></label>
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending"><?php echo t('position_swap.pending'); ?></option>
                        <option value="approved"><?php echo t('position_swap.approved'); ?></option>
                        <option value="rejected"><?php echo t('position_swap.rejected'); ?></option>
                        <option value="completed"><?php echo t('position_swap.completed'); ?></option>
                        <option value="cancelled"><?php echo t('position_swap.cancelled'); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-exclamation-triangle"></i> <?php echo t('swap_management.priority'); ?></label>
                    <select id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent"><?php echo t('swap_management.urgent'); ?></option>
                        <option value="high"><?php echo t('swap_management.high'); ?></option>
                        <option value="medium"><?php echo t('swap_management.medium'); ?></option>
                        <option value="low"><?php echo t('swap_management.low'); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> <?php echo t('swap_management.search_requests'); ?></label>
                    <input type="text" id="searchBox" placeholder="Search by member name, request ID...">
                </div>
            </div>

            <!-- Requests Table -->
            <div class="requests-table">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="requestsTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i><?php echo t('position_swap.request_id'); ?></th>
                                <th><i class="fas fa-user me-2"></i><?php echo t('swap_management.member_name'); ?></th>
                                <th><i class="fas fa-arrows-alt-h me-2"></i><?php echo t('swap_management.current_pos'); ?> → <?php echo t('swap_management.requested_pos'); ?></th>
                                <th><i class="fas fa-user-friends me-2"></i><?php echo t('swap_management.target_member'); ?></th>
                                <th><i class="fas fa-exclamation-triangle me-2"></i><?php echo t('swap_management.priority'); ?></th>
                                <th><i class="fas fa-info-circle me-2"></i><?php echo t('position_swap.status'); ?></th>
                                <th><i class="fas fa-calendar me-2"></i><?php echo t('position_swap.date_requested'); ?></th>
                                <th><i class="fas fa-cogs me-2"></i><?php echo t('swap_management.actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($swap_requests)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>No Swap Requests Found</h3>
                                        <p>When members submit position swap requests, they will appear here for your review.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($swap_requests as $request): ?>
                                <tr data-status="<?php echo $request['status']; ?>" 
                                    data-priority="<?php echo $request['priority_level']; ?>"
                                    data-member="<?php echo strtolower($request['member_name']); ?>"
                                    data-request-id="<?php echo strtolower($request['request_id']); ?>">
                                    <td>
                                        <code class="text-primary fw-bold"><?php echo htmlspecialchars($request['request_id']); ?></code>
                                    </td>
                                    <td>
                                        <div class="member-info">
                                            <div class="member-name"><?php echo htmlspecialchars($request['member_name']); ?></div>
                                            <div class="member-details">
                                                <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['member_id']); ?>
                                                <br><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($request['member_email']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="position-change">
                                            <span class="position-badge"><?php echo $request['current_position']; ?></span>
                                            <i class="fas fa-arrow-right position-arrow"></i>
                                            <span class="position-badge requested-badge"><?php echo $request['requested_position']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($request['target_member_name']): ?>
                                            <div class="member-info">
                                                <div class="member-name"><?php echo htmlspecialchars($request['target_member_name']); ?></div>
                                                <div class="member-details">
                                                    <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['target_member_id_code']); ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i>Available Position
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo $request['priority_level']; ?>">
                                            <i class="fas fa-<?php echo $request['priority_level'] === 'urgent' ? 'exclamation-triangle' : 'flag'; ?> me-1"></i>
                                            <?php echo t('swap_management.' . $request['priority_level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <i class="fas fa-<?php echo $request['status'] === 'pending' ? 'clock' : ($request['status'] === 'approved' ? 'check' : ($request['status'] === 'rejected' ? 'times' : 'trophy')); ?>"></i>
                                            <?php echo t('position_swap.' . $request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M j, Y', strtotime($request['requested_date'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('g:i A', strtotime($request['requested_date'])); ?>
                                        </small>
                                    </td>
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
    </div>

    <!-- Process Request Modal -->
    <div class="modal fade process-modal" id="processRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalTitle">
                        <i class="fas fa-cogs me-2"></i>Process Swap Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="processRequestForm">
                        <input type="hidden" id="processRequestId" name="request_id">
                        <input type="hidden" id="processAction" name="action">
                        
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmProcessBtn">
                        <i class="fas fa-check me-2"></i>Confirm
                    </button>
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
            const originalText = confirmBtn.innerHTML;
            
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
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
                confirmBtn.innerHTML = originalText;
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
            title.innerHTML = '<i class="fas fa-check-circle me-2"></i><?php echo t("swap_management.approve_request"); ?>';
            confirmation.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i><?php echo t("swap_management.confirm_approval"); ?>';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i><?php echo t("swap_management.approve"); ?>';
        } else if (action === 'reject') {
            title.innerHTML = '<i class="fas fa-times-circle me-2"></i><?php echo t("swap_management.reject_request"); ?>';
            confirmation.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i><?php echo t("swap_management.confirm_rejection"); ?>';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i><?php echo t("swap_management.reject"); ?>';
        }
        
        const processModal = new bootstrap.Modal(modal);
        processModal.show();
    }
    
    function viewSwapDetails(requestId) {
        // This could open a detailed modal or navigate to details page
        showAlert('Detailed view functionality will be implemented soon for request: ' + requestId, 'info');
    }
    
    // Alert system
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.15);" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
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