<?php
/**
 * HabeshaEqub - Position Swap Management
 * Professional admin interface following established design patterns
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once 'includes/admin_auth_guard.php';

$admin_id = get_current_admin_id();
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Get swap requests data
$swap_requests = [];
$stats = ['total_requests' => 0, 'pending_count' => 0, 'approved_count' => 0, 'rejected_count' => 0, 'completed_count' => 0, 'this_month_count' => 0];

try {
    // Get all requests
    $stmt = $pdo->query("SELECT * FROM position_swap_requests ORDER BY requested_date DESC");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each request
    foreach ($requests as $request) {
        // Get member name
        $member_stmt = $pdo->prepare("SELECT first_name, last_name, email, member_id FROM members WHERE id = ?");
        $member_stmt->execute([$request['member_id']]);
        $member = $member_stmt->fetch(PDO::FETCH_ASSOC);
        
        $request['member_name'] = $member ? ($member['first_name'] . ' ' . $member['last_name']) : 'Unknown';
        $request['member_email'] = $member ? $member['email'] : '';
        $request['member_code'] = $member ? $member['member_id'] : '';
        
        // Get target member if exists
        if ($request['target_member_id']) {
            $target_stmt = $pdo->prepare("SELECT first_name, last_name, member_id FROM members WHERE id = ?");
            $target_stmt->execute([$request['target_member_id']]);
            $target = $target_stmt->fetch(PDO::FETCH_ASSOC);
            
            $request['target_member_name'] = $target ? ($target['first_name'] . ' ' . $target['last_name']) : 'Unknown';
            $request['target_member_code'] = $target ? $target['member_id'] : '';
        } else {
            $request['target_member_name'] = null;
            $request['target_member_code'] = null;
        }
        
        // Get processed by admin name if processed
        if ($request['processed_by_admin_id']) {
            $admin_stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
            $admin_stmt->execute([$request['processed_by_admin_id']]);
            $admin_info = $admin_stmt->fetch(PDO::FETCH_ASSOC);
            $request['processed_by_name'] = $admin_info ? $admin_info['username'] : 'Unknown Admin';
        } else {
            $request['processed_by_name'] = null;
        }
        
        $swap_requests[] = $request;
    }
    
    // Calculate stats
    $stats['total_requests'] = count($swap_requests);
    foreach ($swap_requests as $req) {
        if ($req['status'] === 'pending') $stats['pending_count']++;
        if ($req['status'] === 'approved') $stats['approved_count']++;
        if ($req['status'] === 'rejected') $stats['rejected_count']++;
        if ($req['status'] === 'completed') $stats['completed_count']++;
        if (strtotime($req['requested_date']) >= strtotime('-30 days')) $stats['this_month_count']++;
    }
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1e40af;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --gold-color: #f59e0b;
            --light-bg: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .stats-row {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-number.pending { color: var(--warning-color); }
        .stat-number.approved { color: var(--success-color); }
        .stat-number.rejected { color: var(--danger-color); }
        .stat-number.completed { color: var(--primary-color); }

        .stat-label {
            font-size: 0.875rem;
            color: var(--secondary-color);
            font-weight: 500;
            margin: 0;
        }

        .main-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        .table-container {
            overflow-x: auto;
        }

        .custom-table {
            margin: 0;
            font-size: 0.875rem;
        }

        .custom-table thead th {
            background-color: #f8fafc;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            border: none;
            border-bottom: 2px solid #e2e8f0;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .member-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .member-name {
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        .member-details {
            font-size: 0.75rem;
            color: var(--secondary-color);
        }

        .position-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .position-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 28px;
            text-align: center;
        }

        .position-arrow {
            color: var(--secondary-color);
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-completed {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--secondary-color);
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .request-id {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .date-info {
            font-size: 0.75rem;
            color: var(--secondary-color);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e3a8a 100%);
            color: white;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: invert(1);
        }

        .alert-info {
            background-color: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .stats-row .col-md-2 {
                margin-bottom: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .position-change {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container-fluid">
                <h1 class="h2 mb-1">
                    <i class="fas fa-exchange-alt me-2"></i>
                    <?php echo t('swap_management.page_title'); ?>
                </h1>
                <p class="lead mb-0 opacity-75"><?php echo t('swap_management.page_subtitle'); ?></p>
            </div>
        </div>

        <div class="container-fluid">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Row -->
            <div class="row stats-row">
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_requests']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.total_requests'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-number pending"><?php echo $stats['pending_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.pending'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-number approved"><?php echo $stats['approved_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.approved'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-number rejected"><?php echo $stats['rejected_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.rejected'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-number completed"><?php echo $stats['completed_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.completed_count'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['this_month_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.this_month'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Main Table -->
            <div class="main-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list me-2"></i>
                        <?php echo t('swap_management.all_requests'); ?>
                    </h2>
                </div>

                <div class="table-container">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i><?php echo t('swap_management.request_id'); ?></th>
                                <th><i class="fas fa-user me-1"></i><?php echo t('swap_management.member'); ?></th>
                                <th><i class="fas fa-arrows-alt-h me-1"></i><?php echo t('swap_management.position_change'); ?></th>
                                <th><i class="fas fa-user-friends me-1"></i><?php echo t('swap_management.target_member'); ?></th>
                                <th><i class="fas fa-info-circle me-1"></i><?php echo t('swap_management.status'); ?></th>
                                <th><i class="fas fa-calendar me-1"></i><?php echo t('swap_management.date'); ?></th>
                                <th><i class="fas fa-cogs me-1"></i><?php echo t('swap_management.actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($swap_requests)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h3><?php echo t('swap_management.no_requests'); ?></h3>
                                            <p><?php echo t('swap_management.no_requests_desc'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($swap_requests as $request): ?>
                                <tr>
                                    <td>
                                        <span class="request-id"><?php echo htmlspecialchars($request['request_id']); ?></span>
                                    </td>
                                    <td>
                                        <div class="member-info">
                                            <h6 class="member-name"><?php echo htmlspecialchars($request['member_name']); ?></h6>
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
                                            <span class="position-badge" style="background: var(--warning-color);"><?php echo $request['requested_position']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($request['target_member_name']): ?>
                                            <div class="member-info">
                                                <h6 class="member-name"><?php echo htmlspecialchars($request['target_member_name']); ?></h6>
                                                <div class="member-details">
                                                    <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['target_member_code']); ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i><?php echo t('swap_management.available_position'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <i class="fas fa-<?php echo $request['status'] === 'pending' ? 'clock' : ($request['status'] === 'approved' ? 'check' : ($request['status'] === 'rejected' ? 'times' : 'trophy')); ?> me-1"></i>
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($request['requested_date'])); ?>
                                            <br><i class="fas fa-clock me-1"></i><?php echo date('g:i A', strtotime($request['requested_date'])); ?>
                                            <?php if ($request['processed_date']): ?>
                                                <br><small class="text-muted">
                                                    <?php echo t('swap_management.processed'); ?>: <?php echo date('M j', strtotime($request['processed_date'])); ?>
                                                    <?php if ($request['processed_by_name']): ?>
                                                        <br>by <?php echo htmlspecialchars($request['processed_by_name']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <div class="action-buttons">
                                                <button class="btn btn-success btn-sm" onclick="processRequest('<?php echo $request['request_id']; ?>', 'approve')">
                                                    <i class="fas fa-check me-1"></i><?php echo t('swap_management.approve'); ?>
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="processRequest('<?php echo $request['request_id']; ?>', 'reject')">
                                                    <i class="fas fa-times me-1"></i><?php echo t('swap_management.reject'); ?>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i><?php echo t('swap_management.processed'); ?>
                                            </span>
                                        <?php endif; ?>
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

    <!-- Process Modal -->
    <div class="modal fade" id="processModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processTitle">Process Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="processForm">
                        <input type="hidden" id="requestId" name="request_id">
                        <input type="hidden" id="action" name="action">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label"><?php echo t('swap_management.admin_notes'); ?></label>
                            <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3" placeholder="<?php echo t('swap_management.admin_notes_placeholder'); ?>"></textarea>
                        </div>
                        
                        <div class="alert alert-info" id="confirmText"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('common.cancel'); ?></button>
                    <button type="button" class="btn btn-primary" id="confirmBtn"><?php echo t('common.confirm'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function processRequest(requestId, action) {
            document.getElementById('requestId').value = requestId;
            document.getElementById('action').value = action;
            
            const title = document.getElementById('processTitle');
            const confirmText = document.getElementById('confirmText');
            const confirmBtn = document.getElementById('confirmBtn');
            
            if (action === 'approve') {
                title.textContent = '<?php echo t("swap_management.approve_request"); ?>';
                confirmText.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i><?php echo t("swap_management.approve_confirm"); ?>';
                confirmBtn.className = 'btn btn-success';
                confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i><?php echo t("swap_management.approve"); ?>';
            } else {
                title.textContent = '<?php echo t("swap_management.reject_request"); ?>';
                confirmText.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i><?php echo t("swap_management.reject_confirm"); ?>';
                confirmBtn.className = 'btn btn-danger';
                confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i><?php echo t("swap_management.reject"); ?>';
            }
            
            new bootstrap.Modal(document.getElementById('processModal')).show();
        }
        
        document.getElementById('confirmBtn').addEventListener('click', async function() {
            const form = document.getElementById('processForm');
            const formData = new FormData(form);
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?php echo t("swap_management.processing"); ?>...';
            
            try {
                const response = await fetch('api/position-swap-management.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('<?php echo t("swap_management.success_message"); ?>');
                    window.location.reload();
                } else {
                    alert('<?php echo t("swap_management.error"); ?>: ' + result.message);
                }
            } catch (error) {
                alert('<?php echo t("swap_management.error_processing"); ?>');
            } finally {
                this.disabled = false;
                this.innerHTML = '<?php echo t("common.confirm"); ?>';
            }
        });
    </script>
</body>
</html>