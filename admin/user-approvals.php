<?php
/**
 * HabeshaEqub - User Approval Management Page
 * Admin interface for approving/declining member registrations
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get pending and recent approvals
try {
    // Pending users (not approved yet)
    $pending_stmt = $db->prepare("
        SELECT m.*, 
               DATE(m.created_at) as registration_date,
               TIMESTAMPDIFF(HOUR, m.created_at, NOW()) as hours_waiting
        FROM members m 
        WHERE m.is_approved = 0 AND m.is_active = 1
        ORDER BY m.created_at ASC
    ");
    $pending_stmt->execute();
    $pending_users = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recently approved/declined users (last 7 days)
    $recent_stmt = $db->prepare("
        SELECT m.*, 
               DATE(m.created_at) as registration_date,
               DATE(m.updated_at) as action_date,
               CASE WHEN m.is_approved = 1 THEN 'approved' ELSE 'declined' END as action_status
        FROM members m 
        WHERE (m.is_approved = 1 OR m.is_active = 0) 
        AND m.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY m.updated_at DESC
        LIMIT 20
    ");
    $recent_stmt->execute();
    $recent_actions = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching approval data: " . $e->getMessage());
    $pending_users = [];
    $recent_actions = [];
}

$total_pending = count($pending_users);
$total_recent = count($recent_actions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Approvals - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --color-cream: #F1ECE2;
            --color-dark-purple: #4D4052;
            --color-navy: #301934;
            --color-gold: #DAA520;
            --color-light-cream: #CDAF56;
            --color-brown: #5D4225;
            --gradient-primary: linear-gradient(135deg, var(--color-navy) 0%, var(--color-dark-purple) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-cream) 100%);
            --shadow-elegant: 0 20px 40px rgba(48, 25, 52, 0.1);
            --shadow-card: 0 8px 32px rgba(48, 25, 52, 0.08);
        }

        body {
            background-color: var(--color-cream);
            color: var(--color-navy);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: var(--gradient-primary) !important;
            box-shadow: var(--shadow-elegant);
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .main-content {
            padding: 30px 0;
            min-height: calc(100vh - 76px);
        }

        .page-header {
            background: linear-gradient(135deg, rgba(48, 25, 52, 0.05) 0%, rgba(77, 64, 82, 0.05) 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(218, 165, 32, 0.2);
        }

        .page-title {
            color: var(--color-navy);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: var(--color-gold);
        }

        .page-subtitle {
            color: var(--color-brown);
            font-size: 1.1rem;
            margin-bottom: 0;
            opacity: 0.9;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(218, 165, 32, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-secondary);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 0;
        }

        .stat-card:hover::before {
            left: 0;
            opacity: 0.1;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-card);
        }

        .stat-card * {
            position: relative;
            z-index: 1;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-navy);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--color-brown);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .pending-urgent {
            color: #dc3545 !important;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .content-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(218, 165, 32, 0.2);
            box-shadow: var(--shadow-card);
        }

        .section-title {
            color: var(--color-navy);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--color-gold);
        }

        .user-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(218, 165, 32, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .user-card:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-card);
            border-color: var(--color-gold);
        }

        .user-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 15px;
        }

        .user-info {
            flex-grow: 1;
        }

        .user-name {
            color: var(--color-navy);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-details {
            color: var(--color-brown);
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .user-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--color-brown);
            font-size: 0.85rem;
        }

        .meta-item i {
            color: var(--color-gold);
            width: 16px;
        }

        .waiting-time {
            color: #dc3545;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-approve {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-decline {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-decline:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            color: white;
        }

        .btn-view {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(48, 25, 52, 0.3);
            color: white;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-approved {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .status-declined {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--color-brown);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--color-gold);
            margin-bottom: 20px;
        }

        .loading-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px 0;
            }

            .page-header {
                padding: 20px;
                margin-bottom: 20px;
            }

            .page-title {
                font-size: 1.6rem;
            }

            .content-card {
                padding: 20px;
                margin-bottom: 20px;
            }

            .user-header {
                flex-direction: column;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-number {
                font-size: 2rem;
            }
        }

        /* Toast Notifications */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--gradient-primary);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-elegant);
            z-index: 1000;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }

        .toast-notification.show {
            transform: translateX(0);
        }

        .toast-notification.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .toast-notification.error {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php require_once 'includes/navigation.php'; ?>

    <div class="container-fluid main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-user-check"></i>
                User Approval Management
            </h1>
            <p class="page-subtitle">Review and approve new member registrations</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number <?php echo $total_pending > 0 ? 'pending-urgent' : ''; ?>">
                    <?php echo $total_pending; ?>
                </div>
                <div class="stat-label">Pending Approvals</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_recent; ?></div>
                <div class="stat-label">Recent Actions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php echo count(array_filter($recent_actions, fn($a) => $a['action_status'] === 'approved')); ?>
                </div>
                <div class="stat-label">Approved Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php echo count(array_filter($recent_actions, fn($a) => $a['action_status'] === 'declined')); ?>
                </div>
                <div class="stat-label">Declined Today</div>
            </div>
        </div>

        <!-- Pending Approvals Section -->
        <div class="content-card">
            <h2 class="section-title">
                <i class="fas fa-clock"></i>
                Pending Approvals
                <?php if ($total_pending > 0): ?>
                    <span class="status-badge status-pending">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $total_pending; ?> waiting
                    </span>
                <?php endif; ?>
            </h2>

            <?php if (empty($pending_users)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h4>All Caught Up!</h4>
                    <p>No pending user approvals at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pending_users as $user): ?>
                    <div class="user-card" data-user-id="<?php echo $user['id']; ?>">
                        <div class="user-header">
                            <div class="user-info">
                                <div class="user-name">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </div>
                                <div class="user-details">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                                <div class="user-details">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($user['phone']); ?>
                                </div>
                                <div class="user-details">
                                    <i class="fas fa-pound-sign"></i>
                                    Monthly: £<?php echo number_format($user['monthly_payment'], 2); ?>
                                </div>
                            </div>
                        </div>

                        <div class="user-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                Registered: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </div>
                            <div class="meta-item waiting-time">
                                <i class="fas fa-hourglass-half"></i>
                                Waiting: <?php echo $user['hours_waiting']; ?> hours
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-id-card"></i>
                                Member ID: <?php echo htmlspecialchars($user['member_id']); ?>
                            </div>
                        </div>

                        <div class="user-meta">
                            <div class="meta-item">
                                <i class="fas fa-user-shield"></i>
                                Guarantor: <?php echo htmlspecialchars($user['guarantor_first_name'] . ' ' . $user['guarantor_last_name']); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-phone-alt"></i>
                                <?php echo htmlspecialchars($user['guarantor_phone']); ?>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="button" class="btn btn-approve" onclick="approveUser(<?php echo $user['id']; ?>)">
                                <span class="loading-spinner"></span>
                                <i class="fas fa-check"></i>
                                Approve
                            </button>
                            <button type="button" class="btn btn-decline" onclick="declineUser(<?php echo $user['id']; ?>)">
                                <span class="loading-spinner"></span>
                                <i class="fas fa-times"></i>
                                Decline
                            </button>
                            <button type="button" class="btn btn-view" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Recent Actions Section -->
        <div class="content-card">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Recent Actions (Last 7 Days)
            </h2>

            <?php if (empty($recent_actions)): ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>No Recent Actions</h4>
                    <p>No approval actions in the last 7 days.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_actions as $action): ?>
                    <div class="user-card">
                        <div class="user-header">
                            <div class="user-info">
                                <div class="user-name">
                                    <?php echo htmlspecialchars($action['first_name'] . ' ' . $action['last_name']); ?>
                                    <span class="status-badge status-<?php echo $action['action_status']; ?>">
                                        <i class="fas fa-<?php echo $action['action_status'] === 'approved' ? 'check' : 'times'; ?>"></i>
                                        <?php echo ucfirst($action['action_status']); ?>
                                    </span>
                                </div>
                                <div class="user-details">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($action['email']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="user-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                Registered: <?php echo date('M j, Y', strtotime($action['registration_date'])); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-check-circle"></i>
                                Action taken: <?php echo date('M j, Y', strtotime($action['action_date'])); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-id-card"></i>
                                Member ID: <?php echo htmlspecialchars($action['member_id']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // User approval functions
        async function approveUser(userId) {
            if (!confirm('Are you sure you want to approve this user?')) return;
            
            const button = event.target.closest('button');
            showLoading(button);
            
            try {
                const response = await fetch('api/user-approvals.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'approve',
                        user_id: userId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('User approved successfully!', 'success');
                    removeUserCard(userId);
                    updateStats();
                } else {
                    showToast(result.message || 'Failed to approve user', 'error');
                }
            } catch (error) {
                console.error('Approval error:', error);
                showToast('Network error. Please try again.', 'error');
            } finally {
                hideLoading(button);
            }
        }

        async function declineUser(userId) {
            const reason = prompt('Please provide a reason for declining this user (optional):');
            if (reason === null) return; // User cancelled
            
            const button = event.target.closest('button');
            showLoading(button);
            
            try {
                const response = await fetch('api/user-approvals.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'decline',
                        user_id: userId,
                        reason: reason || 'No reason provided'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('User declined successfully', 'success');
                    removeUserCard(userId);
                    updateStats();
                } else {
                    showToast(result.message || 'Failed to decline user', 'error');
                }
            } catch (error) {
                console.error('Decline error:', error);
                showToast('Network error. Please try again.', 'error');
            } finally {
                hideLoading(button);
            }
        }

        function viewUserDetails(userId) {
            // Redirect to member profile or open modal with details
            window.open(`member-profile.php?id=${userId}`, '_blank');
        }

        // Utility functions
        function showLoading(button) {
            const spinner = button.querySelector('.loading-spinner');
            spinner.style.display = 'inline-block';
            button.disabled = true;
        }

        function hideLoading(button) {
            const spinner = button.querySelector('.loading-spinner');
            spinner.style.display = 'none';
            button.disabled = false;
        }

        function removeUserCard(userId) {
            const card = document.querySelector(`[data-user-id="${userId}"]`);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateX(-100px)';
                setTimeout(() => card.remove(), 300);
            }
        }

        function updateStats() {
            // Reload page to update statistics
            setTimeout(() => location.reload(), 2000);
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }



        // Auto-refresh every 5 minutes to check for new pending approvals
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html> 