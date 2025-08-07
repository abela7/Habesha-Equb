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
    // Get current member data and EQUB settings
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

    // Check if member is allowed to request swaps
    if (!$member['swap_requests_allowed']) {
        $swap_disabled = true;
        $disable_reason = "Your account is not eligible for position swaps.";
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

    // Get all members with their positions (excluding current user)
    $stmt = $pdo->prepare("
        SELECT m.id, m.first_name, m.last_name, m.payout_position, m.payout_month, m.go_public
        FROM members m
        WHERE m.equb_settings_id = ? AND m.is_active = 1 AND m.id != ?
        ORDER BY m.payout_position ASC
    ");
    $stmt->execute([$member['equb_settings_id'], $user_id]);
    $all_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate available positions (only future positions)
    $current_date = new DateTime();
    $equb_start = new DateTime($member['start_date']);
    $available_positions = [];
    
    for ($pos = 1; $pos <= $member['max_members']; $pos++) {
        if ($pos == $member['payout_position']) continue; // Skip current position
        
        // Calculate position date
        $position_date = clone $equb_start;
        $position_date->add(new DateInterval('P' . ($pos - 1) . 'M'));
        $position_date->setDate(
            $position_date->format('Y'),
            $position_date->format('n'),
            $member['payout_day']
        );
        
        // Only show future positions
        if ($position_date > $current_date) {
            $occupant = null;
            foreach ($all_members as $other_member) {
                if ($other_member['payout_position'] == $pos) {
                    $occupant = $other_member;
                    break;
                }
            }
            
            $available_positions[] = [
                'position' => $pos,
                'date' => $position_date,
                'month_name' => $position_date->format('M Y'),
                'is_available' => !$occupant,
                'occupant' => $occupant,
                'occupant_name' => $occupant ? 
                    ($occupant['go_public'] || $occupant['id'] == $user_id ? 
                        $occupant['first_name'] . ' ' . $occupant['last_name'] : 
                        t('position_swap.anonymous')) : null
            ];
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
    }

    .current-position .position-number {
        background: rgba(255, 255, 255, 0.2);
        color: var(--color-white);
    }

    .position-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .position-item {
        background: var(--color-white);
        border: 2px solid var(--color-border);
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .position-item:hover {
        border-color: var(--color-gold);
        transform: translateY(-2px);
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

    .position-number {
        display: inline-block;
        background: var(--color-gold);
        color: var(--color-white);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
        margin-bottom: 12px;
    }

    .position-month {
        font-size: 16px;
        font-weight: 600;
        color: var(--color-deep-purple);
        margin-bottom: 8px;
    }

    .position-status {
        font-size: 14px;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .status-available {
        background: #d4edda;
        color: #155724;
    }

    .status-taken {
        background: #f8d7da;
        color: #721c24;
    }

    .status-your {
        background: var(--color-gold);
        color: var(--color-white);
    }

    .swap-rules {
        background: #e7f3ff;
        border: 1px solid #b8daff;
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
        border-radius: 12px;
        padding: 24px;
        border: 1px solid var(--color-border);
        margin-top: 20px;
    }

    .history-table {
        background: var(--color-white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(48, 25, 52, 0.08);
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
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
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
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
                <li><?php echo t('position_swap.rule_3'); ?></li>
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
                            <div class="position-item <?php echo $pos['is_available'] ? 'available' : 'unavailable'; ?>" 
                                 data-position="<?php echo $pos['position']; ?>"
                                 data-month="<?php echo $pos['month_name']; ?>"
                                 data-date="<?php echo $pos['date']->format('Y-m-d'); ?>">
                                <div class="position-number"><?php echo $pos['position']; ?></div>
                                <div class="position-month"><?php echo $pos['month_name']; ?></div>
                                <div class="position-status <?php echo $pos['is_available'] ? 'status-available' : 'status-taken'; ?>">
                                    <?php if ($pos['is_available']): ?>
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
                                    <th><?php echo t('position_swap.current_pos'); ?></th>
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
        // Position selection handling
        const positionItems = document.querySelectorAll('.position-item.available');
        const requestForm = document.getElementById('requestForm');
        const selectedPositionInput = document.getElementById('selectedPosition');
        const selectedMonthInput = document.getElementById('selectedMonth');
        const selectedPositionDisplay = document.getElementById('selectedPositionDisplay');
        
        positionItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove previous selection
                positionItems.forEach(p => p.classList.remove('selected'));
                
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
                        showAlert(result.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
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
        
        // Hide form
        requestForm.style.display = 'none';
        
        // Clear selections
        positionItems.forEach(p => p.classList.remove('selected'));
        
        // Clear form
        document.getElementById('selectedPosition').value = '';
        document.getElementById('selectedMonth').value = '';
        document.getElementById('reason').value = '';
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
