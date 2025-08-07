<?php
/**
 * HabeshaEqub - Member Position Swap Request Page
 * Allow members to request payout position swaps
 */

// FORCE NO CACHING
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure authentication check
require_once 'includes/auth_guard.php';
$user_id = get_current_user_id();

// Strong cache buster for assets
$cache_buster = time() . '_' . rand(1000, 9999);

try {
    // Get current member data and EQUB settings - 100% DATABASE DRIVEN
    $stmt = $pdo->prepare("
        SELECT m.*, es.* 
        FROM members m
        JOIN equb_settings es ON m.equb_settings_id = es.id
        WHERE m.id = ? AND m.is_active = 1
    ");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        header('Location: dashboard.php');
        exit;
    }

    // Check if member is allowed to request swaps - USE DATABASE COLUMN
    if (!$member['swap_terms_allowed']) {
        $swap_disabled = true;
        $disable_reason = "Your swap permission is disabled. Contact admin to enable position swaps.";
    }

    // Check if member has pending swap requests
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_count 
        FROM position_swap_requests 
        WHERE member_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $pending_count = $stmt->fetchColumn();

    if ($pending_count > 0) {
        $swap_disabled = true;
        $disable_reason = "You have pending swap requests. Please wait for admin approval.";
    }

    // Check cooldown period
    if ($member['swap_cooldown_until'] && strtotime($member['swap_cooldown_until']) > time()) {
        $swap_disabled = true;
        $cooldown_date = date('M j, Y', strtotime($member['swap_cooldown_until']));
        $disable_reason = "You cannot request swaps until {$cooldown_date}.";
    }

    // GET TOTAL POSITIONS FROM DATABASE - NO HARDCODING!
    $total_positions = (int)$member['calculated_positions']; // Dynamic from database
    if ($total_positions <= 0) {
        // Fallback to max_members if calculated_positions not set
        $total_positions = (int)$member['max_members'];
    }

    // Get all position occupants with swap permissions
    $stmt = $pdo->prepare("
        SELECT 
            m.id, 
            m.first_name, 
            m.last_name, 
            m.payout_position, 
            m.payout_month, 
            m.go_public,
            m.swap_terms_allowed,
            m.membership_type,
            m.joint_group_id,
            CASE 
                WHEN m.membership_type = 'joint' THEN jmg.group_name
                ELSE CONCAT(m.first_name, ' ', m.last_name)
            END as display_name
        FROM members m
        LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
        WHERE m.equb_settings_id = ? AND m.is_active = 1 AND m.id != ?
        ORDER BY m.payout_position ASC
    ");
    $stmt->execute([$member['equb_settings_id'], $user_id]);
    $all_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate available positions (only future positions)
    $current_date = new DateTime();
    $equb_start = new DateTime($member['start_date']);
    $available_positions = [];
    
    // Loop through ACTUAL database positions, not hardcoded numbers
    for ($pos = 1; $pos <= $total_positions; $pos++) {
        if ($pos == $member['payout_position']) continue; // Skip current position
        
        // Calculate position date based on EQUB settings
        $position_date = clone $equb_start;
        $position_date->add(new DateInterval('P' . ($pos - 1) . 'M'));
        $position_date->setDate(
            $position_date->format('Y'),
            $position_date->format('n'),
            $member['payout_day']
        );
        
        // Only show future positions
        if ($position_date > $current_date) {
            // Find occupant(s) of this position
            $position_occupants = [];
            $position_locked = false;
            $position_available = true;
            
            foreach ($all_members as $other_member) {
                if ($other_member['payout_position'] == $pos) {
                    $position_occupants[] = $other_member;
                    $position_available = false;
                    
                    // JOINT MEMBERSHIP LOGIC: If ANY member in joint position has swap disabled, LOCK THE POSITION
                    if (!$other_member['swap_terms_allowed']) {
                        $position_locked = true;
                    }
                }
            }
            
            // For joint positions, check if ALL joint members have swap enabled
            if (!empty($position_occupants) && $position_occupants[0]['membership_type'] === 'joint') {
                $joint_group_id = $position_occupants[0]['joint_group_id'];
                
                // Get all members in this joint group
                $stmt = $pdo->prepare("
                    SELECT swap_terms_allowed 
                    FROM members 
                    WHERE joint_group_id = ? AND is_active = 1
                ");
                $stmt->execute([$joint_group_id]);
                $joint_members_permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // If ANY joint member has swap disabled, lock the position
                foreach ($joint_members_permissions as $permission) {
                    if (!$permission) {
                        $position_locked = true;
                        break;
                    }
                }
            }
            
            // Determine occupant display name
            $occupant_name = null;
            if (!empty($position_occupants)) {
                if (count($position_occupants) > 1 || $position_occupants[0]['membership_type'] === 'joint') {
                    // Joint position
                    $occupant_name = $position_occupants[0]['display_name'] ?: 'Joint Members';
                } else {
                    // Individual position
                    $occupant = $position_occupants[0];
                    if ($occupant['go_public']) {
                        $occupant_name = $occupant['first_name'] . ' ' . $occupant['last_name'];
                    } else {
                        $occupant_name = t('position_swap.anonymous');
                    }
                }
            }
            
            $available_positions[] = [
                'position' => $pos,
                'date' => $position_date,
                'month_name' => $position_date->format('M Y'),
                'is_available' => $position_available && !$position_locked,
                'is_locked' => $position_locked,
                'is_occupied' => !$position_available,
                'can_select' => !$position_locked, // Can select if not locked (even if occupied)
                'occupants' => $position_occupants,
                'occupant_name' => $occupant_name,
                'lock_reason' => $position_locked ? 'Position locked - swap permission disabled' : null
            ];
            
            // Position analysis complete
        }
    }

    // Get member's swap request history
    $stmt = $pdo->prepare("
        SELECT psr.*, 
               CONCAT(tm.first_name, ' ', tm.last_name) as target_member_name
        FROM position_swap_requests psr
        LEFT JOIN members tm ON psr.target_member_id = tm.id
        WHERE psr.member_id = ?
        ORDER BY psr.requested_date DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $swap_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Position swap page error: " . $e->getMessage());
    $error_message = "Database error occurred. Please try again later.";
}

// Calculate current payout date
$current_payout_date = new DateTime($member['start_date']);
$current_payout_date->add(new DateInterval('P' . ($member['payout_position'] - 1) . 'M'));
$current_payout_date->setDate(
    $current_payout_date->format('Y'),
    $current_payout_date->format('n'),
    $member['payout_day']
);

?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('position_swap.page_title'); ?> - HabeshaEqub</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo $cache_buster; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    
    <style>
    /* Position Swap Specific Styles */
    .position-card {
        background: var(--color-white);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid var(--color-border);
        box-shadow: 0 4px 16px rgba(48, 25, 52, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .position-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(48, 25, 52, 0.12);
    }

    .current-position {
        background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-light-gold) 100%);
        color: var(--color-white);
        border: none;
        box-shadow: 0 8px 24px rgba(218, 165, 32, 0.25);
        position: relative;
        overflow: hidden;
    }

    .current-position::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }

    .current-position .position-number {
        background: rgba(255, 255, 255, 0.25);
        color: var(--color-white);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .position-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .position-item {
        background: var(--color-white);
        border: 1px solid rgba(48, 25, 52, 0.1);
        border-radius: 16px;
        padding: 24px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        box-shadow: 0 4px 12px rgba(48, 25, 52, 0.08);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .position-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--color-gold), var(--color-teal));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .position-item:hover {
        border-color: var(--color-gold);
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(48, 25, 52, 0.15);
    }

    .position-item:hover::before {
        opacity: 1;
    }

    .position-item.selected {
        border-color: var(--color-gold);
        background: rgba(218, 165, 32, 0.05);
    }

    .position-item.unavailable {
        background: #f8f9fa;
        border-color: #e9ecef;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .position-item.locked {
        background: var(--color-light-purple);
        border-color: var(--color-dark-purple);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .position-item.locked:hover {
        border-color: var(--color-dark-purple);
        transform: none;
    }

    .position-item.selectable {
        cursor: pointer;
    }

    .position-item.selectable:hover {
        border-color: var(--color-gold);
        transform: translateY(-2px);
    }

    .position-item.occupied {
        background: var(--color-light-teal);
        border-color: var(--color-teal);
    }

    .position-number {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--color-gold), #e6b800);
        color: var(--color-white);
        width: 48px;
        height: 48px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 20px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(218, 165, 32, 0.25);
        transition: all 0.3s ease;
    }

    .position-month {
        font-size: 18px;
        font-weight: 600;
        color: var(--color-deep-purple);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .position-status {
        font-size: 13px;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.25px;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .status-available {
        background: var(--color-light-green);
        color: var(--color-dark-green);
    }

    .status-taken {
        background: var(--color-light-teal);
        color: var(--color-dark-purple);
    }

    .status-your {
        background: var(--color-gold);
        color: var(--color-white);
    }

    .status-locked {
        background: var(--color-dark-purple);
        color: var(--color-light-purple);
    }

    .swap-rules {
        background: var(--color-light-teal);
        border: 1px solid var(--color-teal);
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }

    .swap-rules h4 {
        color: var(--color-deep-purple);
        margin-bottom: 15px;
    }

    .swap-rules ul {
        margin: 0;
        padding-left: 20px;
    }

    .swap-rules li {
        margin-bottom: 8px;
        color: var(--color-dark-purple);
    }

    .request-form {
        background: var(--color-white);
        border-radius: 16px;
        padding: 32px;
        border: 1px solid rgba(48, 25, 52, 0.08);
        margin-top: 24px;
        box-shadow: 0 6px 20px rgba(48, 25, 52, 0.1);
        position: relative;
        overflow: hidden;
    }

    .request-form::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--color-gold), var(--color-teal));
    }

    .history-table {
        background: var(--color-white);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(48, 25, 52, 0.12);
        border: 1px solid rgba(48, 25, 52, 0.08);
    }

    .history-table .table {
        margin-bottom: 0;
    }

    .history-table .table thead th {
        background: linear-gradient(135deg, var(--color-light-gold), var(--color-gold));
        color: var(--color-deep-purple);
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding: 20px 16px;
    }

    .history-table .table tbody td {
        padding: 16px;
        border-color: rgba(48, 25, 52, 0.05);
        vertical-align: middle;
    }

    .history-table .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .history-table .table tbody tr:hover {
        background-color: rgba(48, 25, 52, 0.02);
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .status-completed {
        background: #d1ecf1;
        color: #0c5460;
    }

    .disabled-notice {
        background: var(--color-light-purple);
        color: var(--color-dark-purple);
        border: 1px solid var(--color-dark-purple);
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: center;
    }

    @media (max-width: 768px) {
        .position-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
        }
        
        .position-card {
            padding: 16px;
        }
    }
    </style>
</head>

<body>
    <!-- Include Member Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Page Content -->
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header">
                    <h1>
                        <i class="fas fa-exchange-alt text-primary"></i>
                        <?php echo t('position_swap.page_title'); ?>
                    </h1>
                    <p class="page-subtitle"><?php echo t('position_swap.page_subtitle'); ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($swap_disabled) && $swap_disabled): ?>
            <div class="disabled-notice">
                <i class="fas fa-lock me-2"></i>
                <?php echo $disable_reason; ?>
            </div>
        <?php endif; ?>

        <!-- Current Position -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="position-card current-position">
                    <h3 class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo t('position_swap.current_position'); ?>
                    </h3>
                    <div class="d-flex align-items-center mb-3">
                        <div class="position-number"><?php echo $member['payout_position']; ?></div>
                        <div class="ms-3">
                            <div class="h4 mb-1"><?php echo t('position_swap.your_position'); ?></div>
                            <div><?php echo $current_payout_date->format('M j, Y'); ?></div>
                        </div>
                    </div>
                    <p class="mb-0 opacity-90">
                        <?php echo t('position_swap.current_position_desc'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Swap Rules -->
        <div class="swap-rules">
            <h4>
                <i class="fas fa-info-circle me-2"></i>
                <?php echo t('position_swap.swap_rules'); ?>
            </h4>
            <ul>
                <li><?php echo t('position_swap.rule_1'); ?></li>
                <li><?php echo t('position_swap.rule_2'); ?></li>
                <li><?php echo t('position_swap.rule_4'); ?></li>
            </ul>
        </div>

        <!-- Available Positions -->
        <?php if (!isset($swap_disabled) || !$swap_disabled): ?>
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="fas fa-calendar-alt text-success me-2"></i>
                    <?php echo t('position_swap.available_positions'); ?>
                </h3>
                
                <?php if (empty($available_positions)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo t('position_swap.no_available_positions'); ?>
                    </div>
                <?php else: ?>
                    <div class="position-grid">
                        <?php foreach ($available_positions as $pos): ?>
                            <?php 
                            $item_class = 'position-item';
                            
                            // Determine the main class based on position state
                            if ($pos['is_locked']) {
                                $item_class .= ' locked';
                            } elseif ($pos['can_select']) {
                                $item_class .= ' selectable'; // Can be selected for swap
                                if ($pos['is_available']) {
                                    $item_class .= ' available'; // Empty position
                                } else {
                                    $item_class .= ' occupied'; // Has occupant but can swap
                                }
                            } else {
                                $item_class .= ' unavailable';
                            }
                            ?>
                            <div class="<?php echo $item_class; ?>" 
                                 data-position="<?php echo $pos['position']; ?>"
                                 data-month="<?php echo $pos['month_name']; ?>"
                                 data-date="<?php echo $pos['date']->format('Y-m-d'); ?>"
                                 data-can-select="<?php echo $pos['can_select'] ? 'true' : 'false'; ?>"
                                 <?php if ($pos['is_locked']): ?>
                                    title="<?php echo htmlspecialchars($pos['lock_reason']); ?>"
                                 <?php endif; ?>>
                                <div class="position-number"><?php echo $pos['position']; ?></div>
                                <div class="position-month"><?php echo $pos['month_name']; ?></div>
                                <div class="position-status <?php 
                                    if ($pos['is_locked']) {
                                        echo 'status-locked';
                                    } elseif ($pos['is_available']) {
                                        echo 'status-available';
                                    } else {
                                        echo 'status-taken';
                                    }
                                ?>">
                                    <?php if ($pos['is_locked']): ?>
                                        <i class="fas fa-ban me-1"></i>
                                        No Swapping
                                    <?php elseif ($pos['is_available']): ?>
                                        <i class="fas fa-check-circle me-1"></i>
                                        <?php echo t('position_swap.available'); ?>
                                    <?php else: ?>
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo $pos['occupant_name']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Request Form -->
        <?php if (!empty($available_positions)): ?>
        <div class="request-form" id="requestForm" style="display: none;">
            <h4 class="mb-3">
                <i class="fas fa-paper-plane text-primary me-2"></i>
                <?php echo t('position_swap.request_new_position'); ?>
            </h4>
            
            <form id="swapRequestForm">
                <input type="hidden" id="selectedPosition" name="requested_position">
                <input type="hidden" id="selectedMonth" name="requested_month">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <?php echo t('position_swap.current_position'); ?>
                            </label>
                            <div class="p-3 bg-light rounded">
                                <?php echo t('position_swap.position_month', [
                                    'position' => $member['payout_position'],
                                    'month' => $current_payout_date->format('M Y')
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <?php echo t('position_swap.requested_position'); ?>
                            </label>
                            <div class="p-3 bg-primary text-white rounded" id="selectedPositionDisplay">
                                <?php echo t('position_swap.select_position'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="reason" class="form-label">
                        <?php echo t('position_swap.reason_optional'); ?>
                    </label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" 
                              placeholder="<?php echo t('position_swap.reason_placeholder'); ?>"></textarea>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>
                        <?php echo t('position_swap.submit_request'); ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelRequest()">
                        <i class="fas fa-times me-2"></i>
                        <?php echo t('position_swap.cancel_request'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- My Requests History -->
        <?php if (!empty($swap_history)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="fas fa-history text-info me-2"></i>
                    <?php echo t('position_swap.my_requests'); ?>
                </h3>
                
                <div class="history-table">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo t('position_swap.request_id'); ?></th>
                                    <th><?php echo t('position_swap.current'); ?></th>
                                    <th><?php echo t('position_swap.requested_pos'); ?></th>
                                    <th><?php echo t('position_swap.status'); ?></th>
                                    <th><?php echo t('position_swap.date_requested'); ?></th>
                                    <th><?php echo t('position_swap.actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($swap_history as $request): ?>
                                <tr>
                                    <td>
                                        <code class="small"><?php echo htmlspecialchars($request['request_id']); ?></code>
                                    </td>
                                    <td><?php echo $request['current_position']; ?></td>
                                    <td><?php echo $request['requested_position']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <?php echo t('position_swap.' . $request['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($request['requested_date'])); ?></td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelSwapRequest('<?php echo $request['request_id']; ?>')">
                                                <i class="fas fa-times me-1"></i>
                                                <?php echo t('position_swap.cancel_pending_request'); ?>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewRequestDetails('<?php echo $request['request_id']; ?>')">
                                                <i class="fas fa-eye me-1"></i>
                                                <?php echo t('position_swap.view_details'); ?>
                                            </button>
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
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Position selection handling - ALL SELECTABLE POSITIONS
        const selectableItems = document.querySelectorAll('.position-item.selectable');
        const requestForm = document.getElementById('requestForm');
        const selectedPositionInput = document.getElementById('selectedPosition');
        const selectedMonthInput = document.getElementById('selectedMonth');
        const selectedPositionDisplay = document.getElementById('selectedPositionDisplay');
        
        console.log('Found', selectableItems.length, 'selectable positions');
        
        selectableItems.forEach(item => {
            item.addEventListener('click', function() {
                // Double check if position can be selected
                if (this.dataset.canSelect !== 'true') {
                    console.log('Position cannot be selected:', this.dataset.position);
                    return;
                }
                
                console.log('Position selected:', this.dataset.position);
                
                // Remove previous selection from all position items
                document.querySelectorAll('.position-item').forEach(p => p.classList.remove('selected'));
                
                // Select current item
                this.classList.add('selected');
                
                // Get data
                const position = this.dataset.position;
                const month = this.dataset.month;
                
                // Update form
                selectedPositionInput.value = position;
                selectedMonthInput.value = month;
                selectedPositionDisplay.textContent = `Position ${position} - ${month}`;
                
                // Show form
                requestForm.style.display = 'block';
                requestForm.scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Add click handlers for locked positions to show message
        const lockedItems = document.querySelectorAll('.position-item.locked');
        lockedItems.forEach(item => {
            item.addEventListener('click', function() {
                console.log('Locked position clicked:', this.dataset.position);
                showAlert('This position is not available for swapping because one or more members have disabled their swap permissions.', 'warning');
            });
        });
        
        // Form submission
        const swapForm = document.getElementById('swapRequestForm');
        if (swapForm) {
            swapForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                
                try {
                    const formData = new FormData(this);
                    
                    const response = await fetch('api/position-swap.php?action=submit', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Show success message with confirmation
                        showAlert('✅ Swap request submitted successfully! Your request is now pending admin approval. You will receive an email notification once it\'s processed.', 'success');
                        
                        // Hide the form and clear selection
                        cancelRequest();
                        
                        // Reload page after delay to show updated request history
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else {
                        showAlert(result.message, 'danger');
                    }
                } catch (error) {
                    showAlert('<?php echo t("position_swap.error_message"); ?>', 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        }
    });
    
    function cancelRequest() {
        const requestForm = document.getElementById('requestForm');
        const positionItems = document.querySelectorAll('.position-item');
        
        console.log('Cancelling request and clearing selections');
        
        // Hide form
        requestForm.style.display = 'none';
        
        // Clear selections from all position items
        positionItems.forEach(p => p.classList.remove('selected'));
        
        // Clear form
        document.getElementById('selectedPosition').value = '';
        document.getElementById('selectedMonth').value = '';
        document.getElementById('reason').value = '';
        document.getElementById('selectedPositionDisplay').textContent = '<?php echo t("position_swap.select_position"); ?>';
    }
    
    async function cancelSwapRequest(requestId) {
        if (!confirm('Are you sure you want to cancel this swap request?')) {
            return;
        }
        
        try {
            const response = await fetch('api/position-swap.php?action=cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ request_id: requestId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            showAlert('Error cancelling request', 'danger');
        }
    }
    
    function viewRequestDetails(requestId) {
        // This could open a modal or navigate to a details page
        alert('Request details functionality coming soon!');
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
