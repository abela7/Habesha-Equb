<?php
/**
 * HabeshaEqub - Members Directory (Simplified)
 * Clean member directory focused on navigation to individual profiles
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Get members data - SIMPLIFIED and CLEAN
try {
    $stmt = $pdo->query("
        SELECT m.id, m.member_id, m.first_name, m.last_name, m.email, m.phone, 
               m.membership_type, m.is_active, m.created_at, m.monthly_payment,
               m.payout_position, m.received_payout,
               jmg.group_name,
               COUNT(p.id) as payment_count,
               MAX(p.payment_date) as last_payment,
               COUNT(po.id) as payout_count,
               MAX(po.actual_payout_date) as last_payout,
               dt.last_login
        FROM members m 
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        LEFT JOIN payments p ON m.id = p.member_id AND p.status = 'completed'
        LEFT JOIN payouts po ON m.id = po.member_id AND po.status = 'completed'
        LEFT JOIN (
            SELECT user_id, MAX(last_login) as last_login 
            FROM device_tracking 
            WHERE user_type = 'member' AND is_active = 1 
            GROUP BY user_id
        ) dt ON m.id = dt.user_id
        GROUP BY m.id 
        ORDER BY m.is_active DESC, m.payout_position ASC, m.created_at DESC
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching members: " . $e->getMessage());
    $members = [];
}

// Get summary stats
$total_members = count($members);
$active_members = count(array_filter($members, fn($m) => $m['is_active']));
$joint_members = count(array_filter($members, fn($m) => $m['membership_type'] === 'joint'));
$individual_members = $total_members - $joint_members;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Directory - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
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
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .member-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .member-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
            text-decoration: none;
            color: inherit;
        }

        .member-card.inactive {
            opacity: 0.7;
            background-color: #f9fafb;
        }

        .member-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .member-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--gold-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .member-info h5 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
        }

        .member-id {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .member-badges {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }

        .badge-active { background-color: #dcfce7; color: #166534; }
        .badge-inactive { background-color: #fef2f2; color: #991b1b; }
        .badge-joint { background-color: #dbeafe; color: #1e40af; }
        .badge-individual { background-color: #f3e8ff; color: #7c3aed; }
        .badge-received { background-color: #ecfdf5; color: #059669; }

        .member-details {
            padding: 0 1.5rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .detail-label {
            color: var(--secondary-color);
            font-size: 0.85rem;
        }

        .detail-value {
            font-weight: 500;
            color: #1f2937;
        }

        .search-filters {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .btn-profile {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .member-card:hover .btn-profile {
            opacity: 1;
        }

        .no-members {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="mb-0"><i class="fas fa-users me-3"></i>Members Directory</h1>
                    <p class="mb-0 opacity-75">Navigate to individual member profiles for detailed information</p>
                </div>
                <div class="col-lg-4 text-end">
                    <a href="register.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Add New Member
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Summary Statistics -->
        <div class="row stats-row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-primary"><?php echo $total_members; ?></div>
                    <div class="stat-label">Total Members</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-success"><?php echo $active_members; ?></div>
                    <div class="stat-label">Active Members</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-info"><?php echo $individual_members; ?></div>
                    <div class="stat-label">Individual</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-warning"><?php echo $joint_members; ?></div>
                    <div class="stat-label">Joint Members</div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filters">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="memberSearch" placeholder="Search by name, ID, email...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="individual">Individual</option>
                        <option value="joint">Joint</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="payoutFilter">
                        <option value="">All</option>
                        <option value="received">Received Payout</option>
                        <option value="pending">Pending Payout</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Members Grid -->
        <div class="members-grid" id="membersGrid">
            <?php if (empty($members)): ?>
                <div class="no-members">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <h4>No Members Found</h4>
                    <p>Start by adding your first member to the EQUB.</p>
                    <a href="register.php" class="btn btn-primary">Add First Member</a>
                </div>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <a href="member-profile.php?id=<?php echo $member['id']; ?>" class="member-card <?php echo !$member['is_active'] ? 'inactive' : ''; ?>">
                        <div class="member-header">
                            <div class="member-avatar">
                                <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                            </div>
                            <div class="member-info">
                                <h5><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h5>
                                <div class="member-id"><?php echo htmlspecialchars($member['member_id']); ?></div>
                                <div class="member-badges">
                                    <span class="badge <?php echo $member['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <span class="badge <?php echo $member['membership_type'] === 'joint' ? 'badge-joint' : 'badge-individual'; ?>">
                                        <?php echo ucfirst($member['membership_type']); ?>
                                    </span>
                                    <?php if ($member['received_payout']): ?>
                                        <span class="badge badge-received">Received Payout</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button class="btn-profile" onclick="event.preventDefault(); window.location.href='member-profile.php?id=<?php echo $member['id']; ?>'">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        
                        <div class="member-details">
                            <div class="detail-row">
                                <span class="detail-label">Position</span>
                                <span class="detail-value">#<?php echo $member['payout_position']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Monthly Payment</span>
                                <span class="detail-value">£<?php echo number_format($member['monthly_payment'], 0); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Payments</span>
                                <span class="detail-value"><?php echo $member['payment_count']; ?></span>
                            </div>
                            <?php if ($member['membership_type'] === 'joint' && $member['group_name']): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Group</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($member['group_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="detail-row">
                                <span class="detail-label">Last Login</span>
                                <span class="detail-value">
                                    <?php echo $member['last_login'] ? date('M d, Y', strtotime($member['last_login'])) : 'Never'; ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Simple search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('memberSearch');
            const statusFilter = document.getElementById('statusFilter');
            const typeFilter = document.getElementById('typeFilter');
            const payoutFilter = document.getElementById('payoutFilter');
            const membersGrid = document.getElementById('membersGrid');
            const memberCards = Array.from(membersGrid.querySelectorAll('.member-card'));

            function filterMembers() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const typeValue = typeFilter.value;
                const payoutValue = payoutFilter.value;

                memberCards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    const isActive = !card.classList.contains('inactive');
                    const isJoint = text.includes('joint');
                    const hasReceivedPayout = text.includes('received payout');

                    let show = true;

                    // Search filter
                    if (searchTerm && !text.includes(searchTerm)) {
                        show = false;
                    }

                    // Status filter
                    if (statusValue === 'active' && !isActive) show = false;
                    if (statusValue === 'inactive' && isActive) show = false;

                    // Type filter
                    if (typeValue === 'joint' && !isJoint) show = false;
                    if (typeValue === 'individual' && isJoint) show = false;

                    // Payout filter
                    if (payoutValue === 'received' && !hasReceivedPayout) show = false;
                    if (payoutValue === 'pending' && hasReceivedPayout) show = false;

                    card.style.display = show ? 'block' : 'none';
                });
            }

            searchInput.addEventListener('input', filterMembers);
            statusFilter.addEventListener('change', filterMembers);
            typeFilter.addEventListener('change', filterMembers);
            payoutFilter.addEventListener('change', filterMembers);
        });
    </script>
</body>
</html>
