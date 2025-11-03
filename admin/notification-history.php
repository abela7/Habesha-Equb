<?php
/**
 * HabeshaEqub - Notification History Management Page
 * Comprehensive notification history with advanced filtering and statistics
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/admin_auth_guard.php';
require_once '../includes/db.php';
require_once '../languages/translator.php';

$admin_id = get_current_admin_id();
if (!$admin_id) {
    header('Location: login.php');
    exit;
}

$cache_buster = time();
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('admin.notification_history'); ?> - HabeshaEqub</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?php echo $cache_buster; ?>" rel="stylesheet">
    
    <style>
        :root {
            --color-cream: #F1ECE2;
            --color-dark-purple: #4D4052;
            --color-deep-purple: #301934;
            --color-gold: #DAA520;
            --color-light-gold: #CDAF56;
            --color-border: rgba(77, 64, 82, 0.15);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #fff 0%, var(--color-cream) 100%);
            border-radius: 16px;
            padding: 20px;
            border: 2px solid var(--color-gold);
            box-shadow: 0 4px 12px rgba(48, 25, 52, 0.1);
            transition: transform 0.2s;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(48, 25, 52, 0.15);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-deep-purple);
            margin: 10px 0;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: var(--color-dark-purple);
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--color-border);
            margin-bottom: 20px;
        }
        
        .filter-section {
            margin-bottom: 20px;
        }
        
        .filter-section:last-child {
            margin-bottom: 0;
        }
        
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead {
            background: linear-gradient(135deg, var(--color-deep-purple), var(--color-dark-purple));
            color: white;
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(218, 165, 32, 0.05);
        }
        
        .badge-channel {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-channel.email {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-channel.sms {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-channel.both {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            color: #1976d2;
            border: 1px solid #7b1fa2;
        }
        
        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: var(--color-cream);
            border-radius: 12px;
            font-size: 0.85rem;
            color: var(--color-deep-purple);
        }
        
        .pagination {
            margin-top: 20px;
        }
        
        .export-btn {
            background: linear-gradient(135deg, var(--color-gold), var(--color-light-gold));
            border: none;
            color: var(--color-deep-purple);
            font-weight: 600;
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, var(--color-light-gold), var(--color-gold));
            color: var(--color-deep-purple);
        }
        
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 15px;
            }
            
            .stats-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="container-fluid app-content">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-history text-primary me-2"></i>
                            <?php echo t('admin.notification_history'); ?>
                        </h2>
                        <p class="text-muted mb-0"><?php echo t('admin.notification_history_desc'); ?></p>
                    </div>
                    <a href="notifications.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i><?php echo t('admin.back_to_notifications'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4" id="statsRow">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-label"><?php echo t('admin.total_notifications'); ?></div>
                    <div class="stats-number" id="statTotal">0</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-label"><?php echo t('admin.email_notifications'); ?></div>
                    <div class="stats-number text-primary" id="statEmail">0</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-label"><?php echo t('admin.sms_notifications'); ?></div>
                    <div class="stats-number text-info" id="statSms">0</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-label"><?php echo t('admin.both_channels'); ?></div>
                    <div class="stats-number text-success" id="statBoth">0</div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filter-card">
            <h5 class="mb-3">
                <i class="fas fa-filter text-primary me-2"></i>
                <?php echo t('admin.filters'); ?>
            </h5>
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?php echo t('admin.channel'); ?></label>
                    <select class="form-select" id="filterChannel" name="channel">
                        <option value=""><?php echo t('admin.all_channels'); ?></option>
                        <option value="email"><?php echo t('admin.email_only'); ?></option>
                        <option value="sms"><?php echo t('admin.sms_only'); ?></option>
                        <option value="both"><?php echo t('admin.both_channels'); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?php echo t('admin.type'); ?></label>
                    <select class="form-select" id="filterType" name="type">
                        <option value=""><?php echo t('admin.all_types'); ?></option>
                        <option value="general"><?php echo t('admin.general'); ?></option>
                        <option value="payment_reminder"><?php echo t('admin.payment_reminder'); ?></option>
                        <option value="payout_alert"><?php echo t('admin.payout_alert'); ?></option>
                        <option value="welcome"><?php echo t('admin.welcome'); ?></option>
                        <option value="approval"><?php echo t('admin.approval'); ?></option>
                        <option value="emergency"><?php echo t('admin.emergency'); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?php echo t('admin.status'); ?></label>
                    <select class="form-select" id="filterStatus" name="status">
                        <option value=""><?php echo t('admin.all_statuses'); ?></option>
                        <option value="sent"><?php echo t('admin.sent'); ?></option>
                        <option value="delivered"><?php echo t('admin.delivered'); ?></option>
                        <option value="failed"><?php echo t('admin.failed'); ?></option>
                        <option value="pending"><?php echo t('admin.pending'); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?php echo t('admin.recipient_type'); ?></label>
                    <select class="form-select" id="filterRecipientType" name="recipient_type">
                        <option value=""><?php echo t('admin.all_recipients'); ?></option>
                        <option value="member"><?php echo t('admin.member'); ?></option>
                        <option value="all_members"><?php echo t('admin.all_members'); ?></option>
                        <option value="admin"><?php echo t('admin.admin'); ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?php echo t('admin.member'); ?></label>
                    <input type="text" class="form-control" id="filterMember" name="member" placeholder="<?php echo t('admin.search_by_name_or_code'); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?php echo t('admin.date_from'); ?></label>
                    <input type="date" class="form-control" id="filterDateFrom" name="date_from">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?php echo t('admin.date_to'); ?></label>
                    <input type="date" class="form-control" id="filterDateTo" name="date_to">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i><?php echo t('admin.apply_filters'); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnResetFilters">
                        <i class="fas fa-redo me-1"></i><?php echo t('admin.reset'); ?>
                    </button>
                    <button type="button" class="btn export-btn" id="btnExport">
                        <i class="fas fa-download me-1"></i><?php echo t('admin.export'); ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Results Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    <?php echo t('admin.notification_list'); ?>
                </h5>
                <span class="badge bg-primary" id="resultsCount">0 <?php echo t('admin.results'); ?></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="historyTable">
                        <thead>
                            <tr>
                                <th><?php echo t('admin.code'); ?></th>
                                <th><?php echo t('admin.subject'); ?></th>
                                <th><?php echo t('admin.recipient'); ?></th>
                                <th><?php echo t('admin.channel'); ?></th>
                                <th><?php echo t('admin.type'); ?></th>
                                <th><?php echo t('admin.status'); ?></th>
                                <th><?php echo t('admin.sent_at'); ?></th>
                                <th><?php echo t('admin.actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                                    <div><?php echo t('admin.loading'); ?>...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Page navigation" id="paginationNav" class="d-none">
                    <ul class="pagination justify-content-center" id="paginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo t('admin.notification_details'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('admin.close'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'api/notifications.php';
        let currentPage = 1;
        let currentFilters = {};
        
        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadHistory();
            
            // Filter form submission
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                currentPage = 1;
                loadHistory();
            });
            
            // Reset filters
            document.getElementById('btnResetFilters').addEventListener('click', function() {
                document.getElementById('filterForm').reset();
                currentPage = 1;
                currentFilters = {};
                loadHistory();
            });
            
            // Export button
            document.getElementById('btnExport').addEventListener('click', function() {
                exportData();
            });
        });
        
        async function loadHistory() {
            const formData = new FormData(document.getElementById('filterForm'));
            currentFilters = Object.fromEntries(formData.entries());
            currentFilters.page = currentPage;
            currentFilters.action = 'history';
            
            try {
                const response = await fetch(API_BASE + '?' + new URLSearchParams(currentFilters));
                const data = await response.json();
                
                if (data.success) {
                    updateStats(data.stats);
                    renderTable(data.notifications || []);
                    updatePagination(data.pagination || {});
                    document.getElementById('resultsCount').textContent = 
                        (data.pagination?.total || 0) + ' <?php echo t('admin.results'); ?>';
                } else {
                    showError(data.message || 'Failed to load history');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Network error. Please try again.');
            }
        }
        
        function updateStats(stats) {
            document.getElementById('statTotal').textContent = stats?.total || 0;
            document.getElementById('statEmail').textContent = stats?.email || 0;
            document.getElementById('statSms').textContent = stats?.sms || 0;
            document.getElementById('statBoth').textContent = stats?.both || 0;
        }
        
        function renderTable(notifications) {
            const tbody = document.getElementById('historyBody');
            
            if (notifications.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <div><?php echo t('admin.no_notifications_found'); ?></div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = notifications.map(n => {
                const channelClass = n.channel === 'email' ? 'email' : n.channel === 'sms' ? 'sms' : 'both';
                const channelText = n.channel === 'email' ? '<?php echo t('admin.email'); ?>' : 
                                   n.channel === 'sms' ? '<?php echo t('admin.sms'); ?>' : 
                                   '<?php echo t('admin.both'); ?>';
                
                const recipient = n.recipient_type === 'all_members' ? 
                    '<span class="badge bg-info"><?php echo t('admin.all_members'); ?></span>' :
                    n.member_name ? 
                    `<span class="member-badge"><i class="fas fa-user"></i>${escapeHtml(n.member_name)}</span>` :
                    '<span class="text-muted">-</span>';
                
                return `
                    <tr>
                        <td><code>${escapeHtml(n.notification_id || '')}</code></td>
                        <td>${escapeHtml(n.subject || '')}</td>
                        <td>${recipient}</td>
                        <td><span class="badge-channel ${channelClass}">${channelText}</span></td>
                        <td><span class="badge bg-secondary">${escapeHtml(n.type || '')}</span></td>
                        <td><span class="badge bg-${getStatusColor(n.status)}">${escapeHtml(n.status || '')}</span></td>
                        <td>${n.sent_at ? formatDate(n.sent_at) : '<span class="text-muted">-</span>'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${n.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        function updatePagination(pagination) {
            const nav = document.getElementById('paginationNav');
            const list = document.getElementById('paginationList');
            
            if (!pagination || pagination.total_pages <= 1) {
                nav.classList.add('d-none');
                return;
            }
            
            nav.classList.remove('d-none');
            list.innerHTML = '';
            
            // Previous button
            list.innerHTML += `
                <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
            
            // Page numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === 1 || i === pagination.total_pages || 
                    (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                    list.innerHTML += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                        </li>
                    `;
                } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                    list.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            // Next button
            list.innerHTML += `
                <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
        }
        
        function changePage(page) {
            currentPage = page;
            loadHistory();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        async function viewDetails(id) {
            try {
                const response = await fetch(`${API_BASE}?action=get_details&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    document.getElementById('detailsContent').innerHTML = formatDetails(data.notification);
                    modal.show();
                } else {
                    alert(data.message || 'Failed to load details');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error');
            }
        }
        
        function formatDetails(n) {
            return `
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.code'); ?>:</strong></div>
                    <div class="col-md-6"><code>${escapeHtml(n.notification_id || '')}</code></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.subject'); ?>:</strong></div>
                    <div class="col-md-6">${escapeHtml(n.subject || '')}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.message'); ?>:</strong></div>
                    <div class="col-md-6">${escapeHtml(n.message || '').replace(/\n/g, '<br>')}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.channel'); ?>:</strong></div>
                    <div class="col-md-6"><span class="badge-channel ${n.channel}">${n.channel}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.recipient'); ?>:</strong></div>
                    <div class="col-md-6">${n.member_name ? escapeHtml(n.member_name) : '<?php echo t('admin.all_members'); ?>'}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.status'); ?>:</strong></div>
                    <div class="col-md-6"><span class="badge bg-${getStatusColor(n.status)}">${escapeHtml(n.status || '')}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?php echo t('admin.sent_at'); ?>:</strong></div>
                    <div class="col-md-6">${n.sent_at ? formatDate(n.sent_at) : '-'}</div>
                </div>
            `;
        }
        
        function exportData() {
            const params = new URLSearchParams(currentFilters);
            params.set('action', 'export');
            window.open(API_BASE + '?' + params.toString(), '_blank');
        }
        
        function getStatusColor(status) {
            const colors = {
                'sent': 'success',
                'delivered': 'success',
                'failed': 'danger',
                'pending': 'warning',
                'cancelled': 'secondary'
            };
            return colors[status] || 'secondary';
        }
        
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleString();
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function showError(message) {
            const tbody = document.getElementById('historyBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <div>${escapeHtml(message)}</div>
                    </td>
                </tr>
            `;
        }
    </script>
</body>
</html>

