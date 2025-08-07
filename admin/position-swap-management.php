<?php
/**
 * HabeshaEqub - Position Swap Management
 * Professional admin interface following EXACT system design patterns
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
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* === FOLLOWING EXACT SYSTEM DESIGN PATTERNS === */
        
        /* Page Header - EXACT match with payouts.php */
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

        /* Statistics Cards - EXACT match */
        .stats-container {
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.12);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .stat-icon.total { background: rgba(233, 196, 106, 0.1); color: var(--color-gold); }
        .stat-icon.pending { background: rgba(233, 196, 106, 0.15); color: #B8860B; }
        .stat-icon.approved { background: rgba(19, 102, 92, 0.1); color: var(--color-teal); }
        .stat-icon.rejected { background: rgba(231, 111, 81, 0.1); color: var(--color-coral); }
        .stat-icon.completed { background: rgba(48, 25, 67, 0.1); color: var(--color-purple); }
        .stat-icon.month { background: rgba(205, 175, 86, 0.1); color: var(--color-light-gold); }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 4px 0;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }

        /* Table Styling - EXACT match */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.06);
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: var(--color-cream);
            border-bottom: 2px solid var(--border-light);
            color: var(--color-purple);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 20px 16px;
            border-top: none;
        }

        .table tbody td {
            padding: 20px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: rgba(241, 236, 226, 0.3);
        }

        /* Member Info */
        .member-info h6 {
            font-weight: 600;
            color: var(--color-purple);
            margin: 0 0 4px 0;
            font-size: 15px;
        }

        .member-details {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Position Display */
        .position-change {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .position-badge {
            background: var(--color-teal);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            min-width: 32px;
            text-align: center;
        }

        .position-badge.requested {
            background: var(--color-gold);
            color: var(--color-purple);
        }

        .position-arrow {
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(233, 196, 106, 0.15);
            color: #8B6914;
        }

        .status-approved {
            background: rgba(19, 102, 92, 0.15);
            color: var(--color-teal);
        }

        .status-rejected {
            background: rgba(231, 111, 81, 0.15);
            color: var(--color-coral);
        }

        .status-completed {
            background: rgba(48, 25, 67, 0.15);
            color: var(--color-purple);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-approve {
            background: var(--color-teal);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-approve:hover {
            background: #0F5147;
            transform: translateY(-1px);
            color: white;
        }

        .btn-reject {
            background: var(--color-coral);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-reject:hover {
            background: #D6492C;
            transform: translateY(-1px);
            color: white;
        }

        /* Request ID */
        .request-id {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background: rgba(19, 102, 92, 0.1);
            color: var(--color-teal);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Date Display */
        .date-info {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .date-primary {
            color: var(--color-purple);
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 48px;
            color: rgba(48, 25, 67, 0.2);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--color-purple);
        }

        /* Modal Styling */
        .modal-header {
            background: linear-gradient(135deg, var(--color-teal) 0%, #0F5147 100%);
            color: white;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: invert(1);
        }

        .alert-info {
            background: rgba(19, 102, 92, 0.1);
            border: 1px solid rgba(19, 102, 92, 0.2);
            color: var(--color-teal);
            border-radius: var(--radius-sm);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .position-change {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .stats-container .col-md-2 {
                margin-bottom: 20px;
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="row">
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_requests']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.total_requests'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['pending_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.pending'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon approved">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['approved_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.approved'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon rejected">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['rejected_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.rejected'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['completed_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.completed_count'); ?></p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon month">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['this_month_count']; ?></div>
                        <p class="stat-label"><?php echo t('swap_management.this_month'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i><?php echo t('swap_management.request_id'); ?></th>
                        <th><i class="fas fa-user me-2"></i><?php echo t('swap_management.member'); ?></th>
                        <th><i class="fas fa-arrows-alt-h me-2"></i><?php echo t('swap_management.position_change'); ?></th>
                        <th><i class="fas fa-user-friends me-2"></i><?php echo t('swap_management.target_member'); ?></th>
                        <th><i class="fas fa-info-circle me-2"></i><?php echo t('swap_management.status'); ?></th>
                        <th><i class="fas fa-calendar me-2"></i><?php echo t('swap_management.date'); ?></th>
                        <th><i class="fas fa-cogs me-2"></i><?php echo t('swap_management.actions'); ?></th>
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
                                    <h6><?php echo htmlspecialchars($request['member_name']); ?></h6>
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
                                    <span class="position-badge requested"><?php echo $request['requested_position']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($request['target_member_name']): ?>
                                    <div class="member-info">
                                        <h6><?php echo htmlspecialchars($request['target_member_name']); ?></h6>
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
                                    <div class="date-primary">
                                        <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($request['requested_date'])); ?>
                                    </div>
                                    <small>
                                        <i class="fas fa-clock me-1"></i><?php echo date('g:i A', strtotime($request['requested_date'])); ?>
                                    </small>
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
                                        <button class="btn-approve" onclick="processRequest('<?php echo $request['request_id']; ?>', 'approve')">
                                            <i class="fas fa-check me-1"></i><?php echo t('swap_management.approve'); ?>
                                        </button>
                                        <button class="btn-reject" onclick="processRequest('<?php echo $request['request_id']; ?>', 'reject')">
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