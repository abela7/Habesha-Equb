<?php
/**
 * HabeshaEqub - Position Swap Management
 * Following established design patterns and ensuring database connectivity
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// SIMPLE DEBUG - Check what's happening
try {
    // Test database connection
    $test_stmt = $pdo->query("SELECT 1");
    echo "<div style='background: blue; color: white; padding: 10px; margin: 10px;'>Database connection: OK</div>";
    
    // Check if position_swap_requests table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'position_swap_requests'");
    $table_exists = $table_check->rowCount() > 0;
    echo "<div style='background: blue; color: white; padding: 10px; margin: 10px;'>Table exists: " . ($table_exists ? 'YES' : 'NO') . "</div>";
    
    // Try to count records
    $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM position_swap_requests");
    $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div style='background: blue; color: white; padding: 10px; margin: 10px;'>Total records: " . $count_result['total'] . "</div>";
    
    if (!$table_exists) {
        throw new Exception("Table 'position_swap_requests' does not exist in the database.");
    }

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

    // Get all swap requests - SIMPLE QUERY FIRST
    $stmt = $pdo->prepare("SELECT * FROM position_swap_requests ORDER BY requested_date DESC");
    $stmt->execute();
    $swap_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // TEMPORARY DEBUG - REMOVE AFTER FIXING
    echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>";
    echo "DEBUG: Found " . count($swap_requests) . " requests<br>";
    if (count($swap_requests) > 0) {
        echo "First request: <pre>" . print_r($swap_requests[0], true) . "</pre>";
    } else {
        echo "NO REQUESTS IN ARRAY - But count showed 1 record exists!<br>";
        // Try direct query
        $direct_query = $pdo->query("SELECT * FROM position_swap_requests LIMIT 1");
        $direct_result = $direct_query->fetch(PDO::FETCH_ASSOC);
        if ($direct_result) {
            echo "DIRECT QUERY RESULT: <pre>" . print_r($direct_result, true) . "</pre>";
        }
    }
    echo "</div>";
    
    // Add member info separately
    foreach ($swap_requests as &$request) {
        // Get member info
        $member_stmt = $pdo->prepare("SELECT first_name, last_name, email, member_id FROM members WHERE id = ?");
        $member_stmt->execute([$request['member_id']]);
        $member = $member_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($member) {
            $request['member_name'] = $member['first_name'] . ' ' . $member['last_name'];
            $request['member_email'] = $member['email'];
            $request['member_code'] = $member['member_id'];
        } else {
            $request['member_name'] = 'Unknown Member';
            $request['member_email'] = '';
            $request['member_code'] = '';
        }
        
        // Get target member info if exists
        if ($request['target_member_id']) {
            $target_stmt = $pdo->prepare("SELECT first_name, last_name, member_id FROM members WHERE id = ?");
            $target_stmt->execute([$request['target_member_id']]);
            $target = $target_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($target) {
                $request['target_member_name'] = $target['first_name'] . ' ' . $target['last_name'];
                $request['target_member_code'] = $target['member_id'];
            }
        }
    }

    // Debug logging
    error_log("Position Swap Management: Found " . count($swap_requests) . " requests");
    
} catch (PDOException $e) {
    error_log("Database error in position swap management: " . $e->getMessage());
    $error_message = "Database connection error: " . $e->getMessage();
    $stats = ['total_requests' => 0, 'pending_count' => 0, 'approved_count' => 0, 'rejected_count' => 0, 'completed_count' => 0, 'this_month_count' => 0];
    $swap_requests = [];
} catch (Exception $e) {
    error_log("Error in position swap management: " . $e->getMessage());
    $error_message = $e->getMessage();
    $stats = ['total_requests' => 0, 'pending_count' => 0, 'approved_count' => 0, 'rejected_count' => 0, 'completed_count' => 0, 'this_month_count' => 0];
    $swap_requests = [];
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('swap_management.page_title'); ?> - HabeshaEqub Admin</title>
    
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
        /* Following established design patterns from other admin pages */
        
        /* Page Header - Following payouts.php pattern */
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
        }
        
        .page-title-section p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 16px;
        }

        /* Stats Cards - Following members.php pattern */
        .stats-row {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin: 0;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-card.pending .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-card.approved .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-card.rejected .stat-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-card.completed .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        /* Main content area */
        .content-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            border: 1px solid var(--border-light);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            padding: 30px 40px;
            border-bottom: 1px solid var(--border-light);
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
        }

        .section-subtitle {
            color: var(--text-secondary);
            margin: 0;
            font-size: 16px;
        }

        /* Filters Bar */
        .filters-bar {
            padding: 24px 40px;
            background: var(--color-light-background);
            border-bottom: 1px solid var(--border-light);
        }

        .filters-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--color-purple);
            margin: 0;
        }

        .filter-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            background: white;
            min-width: 160px;
        }

        .filter-control:focus {
            border-color: var(--color-teal);
            box-shadow: 0 0 0 3px rgba(19, 102, 92, 0.1);
            outline: none;
        }

        /* Table */
        .table-container {
            padding: 0;
        }

        .requests-table {
            margin: 0;
        }

        .requests-table thead th {
            background: var(--color-light-background);
            color: var(--color-purple);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 20px 24px;
            border-bottom: 2px solid var(--border-light);
        }

        .requests-table tbody td {
            padding: 20px 24px;
            border-color: var(--border-light);
            vertical-align: middle;
            border-bottom: 1px solid var(--border-light);
        }

        .requests-table tbody tr:hover {
            background-color: rgba(19, 102, 92, 0.02);
        }

        /* Member Info */
        .member-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .member-name {
            font-weight: 600;
            color: var(--color-purple);
            font-size: 15px;
        }

        .member-details {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Position Change */
        .position-change {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .position-badge {
            background: var(--color-light-teal);
            color: var(--color-purple);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            min-width: 32px;
            text-align: center;
        }

        .position-arrow {
            color: var(--color-teal);
            font-size: 16px;
        }

        .requested-badge {
            background: var(--color-light-gold);
            color: var(--color-purple);
        }

        /* Status Badges */
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
        }

        .status-pending {
            background: var(--color-light-gold);
            color: #92400e;
        }

        .status-approved {
            background: var(--color-light-green);
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-completed {
            background: var(--color-light-teal);
            color: var(--color-purple);
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #374151;
        }

        /* Priority Badges */
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

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-action {
            padding: 8px 16px;
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
            background: var(--color-green);
            color: white;
        }

        .btn-approve:hover {
            background: var(--color-dark-green);
            color: white;
            transform: translateY(-1px);
        }

        .btn-reject {
            background: var(--color-red);
            color: white;
        }

        .btn-reject:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-1px);
        }

        .btn-view {
            background: var(--color-teal);
            color: white;
        }

        .btn-view:hover {
            background: var(--color-dark-teal);
            color: white;
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
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
            color: var(--color-purple);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 0;
        }

        /* Debug Info */
        .debug-info {
            background: var(--color-light-teal);
            border: 1px solid var(--color-teal);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .debug-info h5 {
            color: var(--color-purple);
            margin-bottom: 12px;
        }

        .debug-info .debug-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(19, 102, 92, 0.1);
        }

        .debug-item:last-child {
            border-bottom: none;
        }

        /* Modal */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-bottom: 1px solid var(--border-light);
            border-radius: 16px 16px 0 0;
            padding: 24px 32px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0;
        }

        .modal-body {
            padding: 32px;
        }

        .modal-footer {
            border-top: 1px solid var(--border-light);
            padding: 24px 32px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
                padding: 30px 20px;
            }
            
            .filters-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
            
            .requests-table thead th,
            .requests-table tbody td {
                padding: 12px 16px;
                font-size: 13px;
            }

            .section-header {
                padding: 20px 24px;
            }

            .filters-bar {
                padding: 20px 24px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>
                    <i class="fas fa-exchange-alt me-3"></i>
                    <?php echo t('swap_management.page_title'); ?>
                </h1>
                <p><?php echo t('swap_management.page_subtitle'); ?></p>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>



        <!-- Statistics Cards -->
        <div class="row stats-row">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--color-teal) 0%, var(--color-dark-teal) 100%);">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
                    <p class="stat-label"><?php echo t('swap_management.total_requests'); ?></p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                    <p class="stat-label"><?php echo t('swap_management.pending_count'); ?></p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="stat-card approved">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['approved_count']; ?></div>
                    <p class="stat-label"><?php echo t('swap_management.approved_count'); ?></p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="stat-card rejected">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['rejected_count']; ?></div>
                    <p class="stat-label"><?php echo t('swap_management.rejected_count'); ?></p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="stat-card completed">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['completed_count']; ?></div>
                    <p class="stat-label"><?php echo t('swap_management.completed_count'); ?></p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--color-purple) 0%, var(--color-dark-purple) 100%);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['this_month_count']; ?></div>
                    <p class="stat-label"><?php echo t('swap_management.this_month'); ?></p>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-exchange-alt me-3"></i>
                    <?php echo t('swap_management.all_requests'); ?>
                </h2>
                <p class="section-subtitle">Manage and process member position swap requests</p>
            </div>

            <!-- Filters Bar -->
            <div class="filters-bar">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-filter me-2"></i><?php echo t('swap_management.filter_by_status'); ?>
                        </label>
                        <select id="statusFilter" class="filter-control">
                            <option value="">All Statuses</option>
                            <option value="pending"><?php echo t('position_swap.pending'); ?></option>
                            <option value="approved"><?php echo t('position_swap.approved'); ?></option>
                            <option value="rejected"><?php echo t('position_swap.rejected'); ?></option>
                            <option value="completed"><?php echo t('position_swap.completed'); ?></option>
                            <option value="cancelled"><?php echo t('position_swap.cancelled'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo t('swap_management.priority'); ?>
                        </label>
                        <select id="priorityFilter" class="filter-control">
                            <option value="">All Priorities</option>
                            <option value="urgent"><?php echo t('swap_management.urgent'); ?></option>
                            <option value="high"><?php echo t('swap_management.high'); ?></option>
                            <option value="medium"><?php echo t('swap_management.medium'); ?></option>
                            <option value="low"><?php echo t('swap_management.low'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-search me-2"></i><?php echo t('swap_management.search_requests'); ?>
                        </label>
                        <input type="text" id="searchBox" class="filter-control" placeholder="Search by member name, request ID...">
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table requests-table" id="requestsTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i><?php echo t('position_swap.request_id'); ?></th>
                                <th><i class="fas fa-user me-2"></i><?php echo t('swap_management.member_name'); ?></th>
                                <th><i class="fas fa-arrows-alt-h me-2"></i>Position Change</th>
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
                                        <p>When members submit position swap requests, they will appear here for review and processing.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($swap_requests as $request): ?>
                                <tr data-status="<?php echo $request['status']; ?>" 
                                    data-priority="<?php echo $request['priority_level']; ?>"
                                    data-member="<?php echo strtolower($request['member_name'] ?? ''); ?>"
                                    data-request-id="<?php echo strtolower($request['request_id']); ?>">
                                    <td>
                                        <code style="color: var(--color-teal); font-weight: 600;"><?php echo htmlspecialchars($request['request_id']); ?></code>
                                    </td>
                                    <td>
                                                                                    <div class="member-info">
                                                <div class="member-name"><?php echo htmlspecialchars($request['member_name']); ?></div>
                                                <div class="member-details">
                                                    <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['member_code']); ?>
                                                    <?php if ($request['member_email']): ?>
                                                    <br><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($request['member_email']); ?>
                                                    <?php endif; ?>
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
                                                    <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['target_member_code']); ?>
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
                                            <i class="fas fa-flag me-1"></i>
                                            <?php echo ucfirst($request['priority_level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <i class="fas fa-<?php echo $request['status'] === 'pending' ? 'clock' : ($request['status'] === 'approved' ? 'check' : ($request['status'] === 'rejected' ? 'times' : 'trophy')); ?>"></i>
                                            <?php echo ucfirst($request['status']); ?>
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
                                                    Approve
                                                </button>
                                                <button class="btn-action btn-reject" 
                                                        onclick="processSwapRequest('<?php echo $request['request_id']; ?>', 'reject')">
                                                    <i class="fas fa-times"></i>
                                                    Reject
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn-action btn-view" 
                                                    onclick="viewSwapDetails('<?php echo $request['request_id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                                View
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
    <div class="modal fade" id="processRequestModal" tabindex="-1">
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
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>
                                Admin Notes
                            </label>
                            <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"
                                      placeholder="Add notes about your decision..."></textarea>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
                console.error('Error:', error);
                showAlert('Error processing request. Please try again.', 'danger');
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
            title.innerHTML = '<i class="fas fa-check-circle me-2"></i>Approve Swap Request';
            confirmation.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Are you sure you want to approve this position swap request?';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Approve';
        } else if (action === 'reject') {
            title.innerHTML = '<i class="fas fa-times-circle me-2"></i>Reject Swap Request';
            confirmation.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i>Are you sure you want to reject this position swap request?';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i>Reject';
        }
        
        const processModal = new bootstrap.Modal(modal);
        processModal.show();
    }
    
    function viewSwapDetails(requestId) {
        showAlert('Detailed view for request: ' + requestId + ' - This feature will be implemented soon.', 'info');
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