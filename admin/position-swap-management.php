<?php
/**
 * HabeshaEqub - Position Swap Management
 * COMPLETELY NEW - SIMPLE AND FUNCTIONAL
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
    <title>Position Swap Management - HabeshaEqub Admin</title>
    
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
        }
        
        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 8px 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
            text-align: center;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--color-teal);
        }
        
        .stat-label {
            color: var(--color-purple);
            font-size: 0.9rem;
            margin: 0;
            font-weight: 600;
        }
        
        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.08);
            border: 1px solid var(--border-light);
            overflow: hidden;
        }
        
        .content-header {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            padding: 30px 40px;
            border-bottom: 1px solid var(--border-light);
        }
        
        .content-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0;
        }
        
        .table-container {
            padding: 0;
        }
        
        .swap-table {
            margin: 0;
        }
        
        .swap-table thead th {
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
        
        .swap-table tbody td {
            padding: 20px 24px;
            border-color: var(--border-light);
            vertical-align: middle;
            border-bottom: 1px solid var(--border-light);
        }
        
        .swap-table tbody tr:hover {
            background-color: rgba(19, 102, 92, 0.02);
        }
        
        .member-info h6 {
            font-weight: 600;
            color: var(--color-purple);
            margin: 0;
        }
        
        .member-info small {
            color: var(--text-secondary);
        }
        
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
        
        .requested-badge {
            background: var(--color-light-gold);
            color: var(--color-purple);
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        .btn-approve {
            background: var(--color-green);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-reject {
            background: var(--color-red);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-approve:hover, .btn-reject:hover {
            transform: translateY(-1px);
        }
        
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
    </style>
</head>

<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-exchange-alt me-3"></i>
                Position Swap Management
            </h1>
            <p class="mb-0">Manage and process member position swap requests</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
                    <p class="stat-label">Total Requests</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stat-value" style="color: #f59e0b;"><?php echo $stats['pending_count']; ?></div>
                    <p class="stat-label">Pending</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stat-value" style="color: #10b981;"><?php echo $stats['approved_count']; ?></div>
                    <p class="stat-label">Approved</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stat-value" style="color: #ef4444;"><?php echo $stats['rejected_count']; ?></div>
                    <p class="stat-label">Rejected</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stat-value" style="color: #3b82f6;"><?php echo $stats['completed_count']; ?></div>
                    <p class="stat-label">Completed</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stat-value"><?php echo $stats['this_month_count']; ?></div>
                    <p class="stat-label">This Month</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-card">
            <div class="content-header">
                <h2 class="content-title">
                    <i class="fas fa-list me-3"></i>
                    All Swap Requests
                </h2>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table swap-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>Request ID</th>
                                <th><i class="fas fa-user me-2"></i>Member</th>
                                <th><i class="fas fa-arrows-alt-h me-2"></i>Position Change</th>
                                <th><i class="fas fa-user-friends me-2"></i>Target Member</th>
                                <th><i class="fas fa-info-circle me-2"></i>Status</th>
                                <th><i class="fas fa-calendar me-2"></i>Date</th>
                                <th><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($swap_requests)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h3>No Swap Requests Found</h3>
                                            <p>When members submit position swap requests, they will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($swap_requests as $request): ?>
                                <tr>
                                    <td>
                                        <code style="color: var(--color-teal); font-weight: 600;">
                                            <?php echo htmlspecialchars($request['request_id']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <div class="member-info">
                                            <h6><?php echo htmlspecialchars($request['member_name']); ?></h6>
                                            <small>
                                                <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['member_code']); ?>
                                                <?php if ($request['member_email']): ?>
                                                <br><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($request['member_email']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="position-change">
                                            <span class="position-badge"><?php echo $request['current_position']; ?></span>
                                            <i class="fas fa-arrow-right" style="color: var(--color-teal);"></i>
                                            <span class="position-badge requested-badge"><?php echo $request['requested_position']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($request['target_member_name']): ?>
                                            <div class="member-info">
                                                <h6><?php echo htmlspecialchars($request['target_member_name']); ?></h6>
                                                <small>
                                                    <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($request['target_member_code']); ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i>Available Position
                                            </span>
                                        <?php endif; ?>
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
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <div class="d-flex gap-2">
                                                <button class="btn-approve" onclick="processRequest('<?php echo $request['request_id']; ?>', 'approve')">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                                <button class="btn-reject" onclick="processRequest('<?php echo $request['request_id']; ?>', 'reject')">
                                                    <i class="fas fa-times me-1"></i>Reject
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No actions</span>
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
                            <label for="adminNotes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3" placeholder="Add notes about your decision..."></textarea>
                        </div>
                        
                        <div class="alert alert-info" id="confirmText"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function processRequest(requestId, action) {
            document.getElementById('requestId').value = requestId;
            document.getElementById('action').value = action;
            
            const title = document.getElementById('processTitle');
            const confirmText = document.getElementById('confirmText');
            const confirmBtn = document.getElementById('confirmBtn');
            
            if (action === 'approve') {
                title.textContent = 'Approve Swap Request';
                confirmText.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Are you sure you want to approve this swap request?';
                confirmBtn.className = 'btn btn-success';
                confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Approve';
            } else {
                title.textContent = 'Reject Swap Request';
                confirmText.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i>Are you sure you want to reject this swap request?';
                confirmBtn.className = 'btn btn-danger';
                confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i>Reject';
            }
            
            new bootstrap.Modal(document.getElementById('processModal')).show();
        }
        
        document.getElementById('confirmBtn').addEventListener('click', async function() {
            const form = document.getElementById('processForm');
            const formData = new FormData(form);
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            try {
                const response = await fetch('api/position-swap-management.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Request processed successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error processing request');
            } finally {
                this.disabled = false;
                this.innerHTML = 'Confirm';
            }
        });
    </script>
</body>
</html>