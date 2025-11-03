<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/admin_auth_guard.php';
require_once '../includes/db.php';
require_once '../languages/translator.php';

$admin_id = get_current_admin_id();
if (!$admin_id) { header('Location: login.php'); exit; }
$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notification History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet" />
  <style>
    .card-modern{border:1px solid var(--border-light);border-radius:16px;box-shadow:0 8px 24px rgba(48,25,52,0.06)}
    .card-modern .card-header{background:linear-gradient(135deg, var(--color-cream), #fff);border-bottom:1px solid var(--border-light);padding:16px 20px}
    .stat-card{border-radius:12px;padding:20px;text-align:center;transition:transform 0.2s}
    .stat-card:hover{transform:translateY(-2px)}
    .stat-card .stat-icon{font-size:2.5rem;margin-bottom:10px}
    .stat-card .stat-value{font-size:2rem;font-weight:700;margin:10px 0}
    .stat-card .stat-label{color:var(--text-muted);font-size:0.9rem}
    .stat-card.email{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white}
    .stat-card.sms{background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);color:white}
    .stat-card.both{background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);color:white}
    .stat-card.total{background:linear-gradient(135deg,#43e97b 0%,#38f9d7 100%);color:white}
    .filter-section{background:var(--secondary-bg);border-radius:12px;padding:20px;margin-bottom:20px}
    .badge-channel{font-size:0.85rem;padding:6px 12px;border-radius:20px}
    .badge-channel.email{background:#667eea;color:white}
    .badge-channel.sms{background:#f5576c;color:white}
    .badge-channel.both{background:#4facfe;color:white}
    .badge-status{font-size:0.85rem;padding:6px 12px;border-radius:20px}
    .badge-status.sent{background:#28a745;color:white}
    .badge-status.delivered{background:#17a2b8;color:white}
    .badge-status.failed{background:#dc3545;color:white}
    .badge-status.pending{background:#ffc107;color:#000}
    .table-responsive{overflow-x:auto}
    .member-link{cursor:pointer;color:var(--primary);text-decoration:none}
    .member-link:hover{text-decoration:underline}
    .pagination-container{display:flex;justify-content:between;align-items:center;margin-top:20px}
    .loading-spinner{text-align:center;padding:40px}
    .empty-state{text-align:center;padding:60px 20px;color:var(--text-muted)}
    .empty-state i{font-size:4rem;margin-bottom:20px;opacity:0.3}
  </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>
<div class="app-content container-fluid">
  <div class="row g-3">
    <!-- Page Header -->
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="mb-1"><i class="fas fa-history me-2 text-primary"></i>Notification History</h2>
          <p class="text-muted mb-0">View and manage all notification records</p>
        </div>
        <a href="notifications.php" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-1"></i>Back to Notifications
        </a>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="col-12" id="statisticsSection">
      <div class="row g-3">
        <div class="col-lg-3 col-md-6">
          <div class="stat-card total">
            <div class="stat-icon"><i class="fas fa-bell"></i></div>
            <div class="stat-value" id="statTotal">-</div>
            <div class="stat-label">Total Notifications</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card email">
            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-value" id="statEmail">-</div>
            <div class="stat-label">Email Notifications</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card sms">
            <div class="stat-icon"><i class="fas fa-sms"></i></div>
            <div class="stat-value" id="statSms">-</div>
            <div class="stat-label">SMS Notifications</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card both">
            <div class="stat-icon"><i class="fas fa-comments"></i></div>
            <div class="stat-value" id="statBoth">-</div>
            <div class="stat-label">Both Channels</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="col-12">
      <div class="card card-modern">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Channel</label>
              <select class="form-select" id="filterChannel">
                <option value="">All Channels</option>
                <option value="email">Email Only</option>
                <option value="sms">SMS Only</option>
                <option value="both">Both</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="filterStatus">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="delivered">Delivered</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date From</label>
              <input type="date" class="form-control" id="filterDateFrom" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Date To</label>
              <input type="date" class="form-control" id="filterDateTo" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Member</label>
              <div class="input-group">
                <input type="text" class="form-control" id="memberSearch" placeholder="Search member by name, code, or email..." />
                <button class="btn btn-outline-primary" type="button" id="btnSearchMember">
                  <i class="fas fa-search"></i>
                </button>
              </div>
              <div id="memberSearchResults" class="mt-2"></div>
              <input type="hidden" id="selectedMemberId" />
              <div id="selectedMemberDisplay" class="mt-2 d-none">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-user me-2"></i><span id="selectedMemberName"></span></span>
                  <button class="btn btn-sm btn-outline-danger" id="btnClearMember">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <button class="btn btn-primary me-2" id="btnApplyFilters">
                <i class="fas fa-filter me-1"></i>Apply Filters
              </button>
              <button class="btn btn-outline-secondary" id="btnResetFilters">
                <i class="fas fa-redo me-1"></i>Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications Table -->
    <div class="col-12">
      <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-list me-2"></i>Notifications</h5>
          <button class="btn btn-sm btn-outline-secondary" id="btnRefresh">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
        </div>
        <div class="card-body">
          <div id="loadingSpinner" class="loading-spinner">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3 text-muted">Loading notifications...</p>
          </div>
          <div id="notificationsContainer" class="d-none">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Notification Code</th>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Channel</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th>Created At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="notificationsTableBody">
                </tbody>
              </table>
            </div>
            <div class="pagination-container">
              <div class="text-muted" id="paginationInfo">Showing 0-0 of 0</div>
              <nav>
                <ul class="pagination mb-0" id="pagination">
                </ul>
              </nav>
            </div>
          </div>
          <div id="emptyState" class="empty-state d-none">
            <i class="fas fa-inbox"></i>
            <h4>No notifications found</h4>
            <p>Try adjusting your filters to see more results.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Member Notifications Modal -->
    <div class="modal fade" id="memberNotificationsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-user me-2"></i>Notifications for <span id="modalMemberName"></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div id="memberNotificationsLoading" class="loading-spinner">
              <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            </div>
            <div id="memberNotificationsContent" class="d-none">
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Notification Code</th>
                      <th>Subject</th>
                      <th>Channel</th>
                      <th>Status</th>
                      <th>Sent At</th>
                      <th>Created At</th>
                    </tr>
                  </thead>
                  <tbody id="memberNotificationsTableBody">
                  </tbody>
                </table>
              </div>
              <div class="pagination-container mt-3">
                <div class="text-muted" id="memberPaginationInfo">Showing 0-0 of 0</div>
                <nav>
                  <ul class="pagination mb-0" id="memberPagination">
                  </ul>
                </nav>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Notification Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="notificationDetailContent">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const api = 'api/notifications.php';
let currentPage = 1;
let currentFilters = {};
let memberNotificationsPage = 1;
let currentMemberId = null;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  loadStatistics();
  loadNotifications();
  
  // Filter events
  document.getElementById('btnApplyFilters').addEventListener('click', applyFilters);
  document.getElementById('btnResetFilters').addEventListener('click', resetFilters);
  document.getElementById('btnRefresh').addEventListener('click', () => {
    loadStatistics();
    loadNotifications();
  });
  
  // Member search
  document.getElementById('btnSearchMember').addEventListener('click', searchMember);
  document.getElementById('memberSearch').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') searchMember();
  });
  document.getElementById('btnClearMember').addEventListener('click', clearMember);
  
  // Modal events
  const modal = document.getElementById('memberNotificationsModal');
  modal.addEventListener('hidden.bs.modal', () => {
    currentMemberId = null;
    memberNotificationsPage = 1;
  });
});

async function loadStatistics() {
  try {
    const params = new URLSearchParams();
    params.append('action', 'get_statistics');
    if (currentFilters.channel) params.append('channel', currentFilters.channel);
    if (currentFilters.recipient_type) params.append('recipient_type', currentFilters.recipient_type);
    if (currentFilters.status) params.append('status', currentFilters.status);
    if (currentFilters.type) params.append('type', currentFilters.type);
    if (currentFilters.date_from) params.append('date_from', currentFilters.date_from);
    if (currentFilters.date_to) params.append('date_to', currentFilters.date_to);
    
    const resp = await fetch(`${api}?${params.toString()}`);
    const data = await resp.json();
    
    if (data.success) {
      const stats = data.statistics || {};
      const total = (stats.email || 0) + (stats.sms || 0) + (stats.both || 0);
      
      document.getElementById('statTotal').textContent = total;
      document.getElementById('statEmail').textContent = stats.email || 0;
      document.getElementById('statSms').textContent = stats.sms || 0;
      document.getElementById('statBoth').textContent = stats.both || 0;
    }
  } catch (e) {
    console.error('Load statistics error:', e);
  }
}

async function loadNotifications(page = 1) {
  currentPage = page;
  const spinner = document.getElementById('loadingSpinner');
  const container = document.getElementById('notificationsContainer');
  const emptyState = document.getElementById('emptyState');
  
  spinner.classList.remove('d-none');
  container.classList.add('d-none');
  emptyState.classList.add('d-none');
  
  try {
    const params = new URLSearchParams({
      action: 'get_history',
      page: page.toString(),
      per_page: '50'
    });
    
    if (currentFilters.channel) params.append('channel', currentFilters.channel);
    if (currentFilters.status) params.append('status', currentFilters.status);
    if (currentFilters.member_id) params.append('member_id', currentFilters.member_id);
    if (currentFilters.date_from) params.append('date_from', currentFilters.date_from);
    if (currentFilters.date_to) params.append('date_to', currentFilters.date_to);
    
    const resp = await fetch(`${api}?${params.toString()}`);
    const data = await resp.json();
    
    if (data.success) {
      displayNotifications(data.notifications, data.pagination);
      spinner.classList.add('d-none');
      container.classList.remove('d-none');
      
      if (data.notifications.length === 0) {
        container.classList.add('d-none');
        emptyState.classList.remove('d-none');
      }
    } else {
      alert(data.message || 'Failed to load notifications');
      spinner.classList.add('d-none');
    }
  } catch (e) {
    console.error('Load notifications error:', e);
    alert('Error loading notifications');
    spinner.classList.add('d-none');
  }
}

function displayNotifications(notifications, pagination) {
  const tbody = document.getElementById('notificationsTableBody');
  tbody.innerHTML = '';
  
  notifications.forEach(n => {
    const tr = document.createElement('tr');
    
    const recipientName = n.recipient_type === 'all_members' 
      ? '<span class="badge bg-secondary">All Members</span>'
      : (n.first_name && n.last_name 
          ? `<a href="#" class="member-link" onclick="viewMemberNotifications(${n.recipient_id}, '${escapeHtml(n.first_name + ' ' + n.last_name)}'); return false;">${escapeHtml(n.first_name + ' ' + n.last_name)}</a>`
          : '<span class="text-muted">N/A</span>');
    
    const channelBadge = `<span class="badge badge-channel ${n.channel}">${n.channel.toUpperCase()}</span>`;
    const statusBadge = `<span class="badge badge-status ${n.status}">${n.status.charAt(0).toUpperCase() + n.status.slice(1)}</span>`;
    
    tr.innerHTML = `
      <td>${n.id}</td>
      <td><code>${escapeHtml(n.notification_id || 'N/A')}</code></td>
      <td>${recipientName}</td>
      <td>${escapeHtml(n.subject || 'N/A')}</td>
      <td>${channelBadge}</td>
      <td>${statusBadge}</td>
      <td>${n.sent_at ? formatDateTime(n.sent_at) : '<span class="text-muted">-</span>'}</td>
      <td>${formatDateTime(n.created_at)}</td>
      <td>
        <button class="btn btn-sm btn-outline-primary" onclick="viewNotificationDetails(${n.id})" title="View Details">
          <i class="fas fa-eye"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
  
  // Pagination
  displayPagination(pagination, 'pagination', 'paginationInfo');
}

function displayPagination(pagination, containerId, infoId) {
  const container = document.getElementById(containerId);
  const info = document.getElementById(infoId);
  container.innerHTML = '';
  
  const start = (pagination.page - 1) * pagination.per_page + 1;
  const end = Math.min(pagination.page * pagination.per_page, pagination.total);
  info.textContent = `Showing ${start}-${end} of ${pagination.total}`;
  
  if (pagination.total_pages <= 1) return;
  
  // Previous button
  const prevLi = document.createElement('li');
  prevLi.className = `page-item ${pagination.page === 1 ? 'disabled' : ''}`;
  prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadNotifications(${pagination.page - 1}); return false;">Previous</a>`;
  container.appendChild(prevLi);
  
  // Page numbers
  const startPage = Math.max(1, pagination.page - 2);
  const endPage = Math.min(pagination.total_pages, pagination.page + 2);
  
  if (startPage > 1) {
    const li = document.createElement('li');
    li.className = 'page-item';
    li.innerHTML = `<a class="page-link" href="#" onclick="loadNotifications(1); return false;">1</a>`;
    container.appendChild(li);
    if (startPage > 2) {
      const ellipsis = document.createElement('li');
      ellipsis.className = 'page-item disabled';
      ellipsis.innerHTML = `<span class="page-link">...</span>`;
      container.appendChild(ellipsis);
    }
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const li = document.createElement('li');
    li.className = `page-item ${i === pagination.page ? 'active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#" onclick="loadNotifications(${i}); return false;">${i}</a>`;
    container.appendChild(li);
  }
  
  if (endPage < pagination.total_pages) {
    if (endPage < pagination.total_pages - 1) {
      const ellipsis = document.createElement('li');
      ellipsis.className = 'page-item disabled';
      ellipsis.innerHTML = `<span class="page-link">...</span>`;
      container.appendChild(ellipsis);
    }
    const li = document.createElement('li');
    li.className = 'page-item';
    li.innerHTML = `<a class="page-link" href="#" onclick="loadNotifications(${pagination.total_pages}); return false;">${pagination.total_pages}</a>`;
    container.appendChild(li);
  }
  
  // Next button
  const nextLi = document.createElement('li');
  nextLi.className = `page-item ${pagination.page === pagination.total_pages ? 'disabled' : ''}`;
  nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadNotifications(${pagination.page + 1}); return false;">Next</a>`;
  container.appendChild(nextLi);
}

function applyFilters() {
  currentFilters = {
    channel: document.getElementById('filterChannel').value,
    status: document.getElementById('filterStatus').value,
    date_from: document.getElementById('filterDateFrom').value,
    date_to: document.getElementById('filterDateTo').value,
    member_id: document.getElementById('selectedMemberId').value || null
  };
  
  loadStatistics();
  loadNotifications(1);
}

function resetFilters() {
  document.getElementById('filterChannel').value = '';
  document.getElementById('filterStatus').value = '';
  document.getElementById('filterDateFrom').value = '';
  document.getElementById('filterDateTo').value = '';
  document.getElementById('memberSearch').value = '';
  clearMember();
  currentFilters = {};
  loadStatistics();
  loadNotifications(1);
}

async function searchMember() {
  const query = document.getElementById('memberSearch').value.trim();
  const resultsDiv = document.getElementById('memberSearchResults');
  
  if (!query) {
    resultsDiv.innerHTML = '';
    return;
  }
  
  try {
    const resp = await fetch(`${api}?action=search_members&q=${encodeURIComponent(query)}`);
    const data = await resp.json();
    
    if (data.success && data.members && data.members.length > 0) {
      let html = '<div class="list-group">';
      data.members.forEach(m => {
        html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectMember(${m.id}, '${escapeHtml(m.first_name + ' ' + m.last_name)}'); return false;">
          <strong>${escapeHtml(m.first_name + ' ' + m.last_name)}</strong> 
          <small class="text-muted">(${escapeHtml(m.member_id || m.code || 'N/A')})</small>
        </a>`;
      });
      html += '</div>';
      resultsDiv.innerHTML = html;
    } else {
      resultsDiv.innerHTML = '<div class="text-muted">No members found</div>';
    }
  } catch (e) {
    console.error('Search member error:', e);
    resultsDiv.innerHTML = '<div class="text-danger">Error searching members</div>';
  }
}

function selectMember(memberId, memberName) {
  document.getElementById('selectedMemberId').value = memberId;
  document.getElementById('selectedMemberName').textContent = memberName;
  document.getElementById('selectedMemberDisplay').classList.remove('d-none');
  document.getElementById('memberSearchResults').innerHTML = '';
  document.getElementById('memberSearch').value = '';
}

function clearMember() {
  document.getElementById('selectedMemberId').value = '';
  document.getElementById('selectedMemberDisplay').classList.add('d-none');
  document.getElementById('memberSearchResults').innerHTML = '';
}

function viewMemberNotifications(memberId, memberName) {
  currentMemberId = memberId;
  memberNotificationsPage = 1;
  document.getElementById('modalMemberName').textContent = memberName;
  const modal = new bootstrap.Modal(document.getElementById('memberNotificationsModal'));
  modal.show();
  loadMemberNotifications();
}

async function loadMemberNotifications(page = 1) {
  memberNotificationsPage = page;
  const loading = document.getElementById('memberNotificationsLoading');
  const content = document.getElementById('memberNotificationsContent');
  
  loading.classList.remove('d-none');
  content.classList.add('d-none');
  
  try {
    const resp = await fetch(`${api}?action=get_member_notifications&member_id=${currentMemberId}&page=${page}&per_page=20`);
    const data = await resp.json();
    
    if (data.success) {
      const tbody = document.getElementById('memberNotificationsTableBody');
      tbody.innerHTML = '';
      
      data.notifications.forEach(n => {
        const tr = document.createElement('tr');
        const channelBadge = `<span class="badge badge-channel ${n.channel}">${n.channel.toUpperCase()}</span>`;
        const statusBadge = `<span class="badge badge-status ${n.status}">${n.status.charAt(0).toUpperCase() + n.status.slice(1)}</span>`;
        
        tr.innerHTML = `
          <td><code>${escapeHtml(n.notification_id || 'N/A')}</code></td>
          <td>${escapeHtml(n.subject || 'N/A')}</td>
          <td>${channelBadge}</td>
          <td>${statusBadge}</td>
          <td>${n.sent_at ? formatDateTime(n.sent_at) : '<span class="text-muted">-</span>'}</td>
          <td>${formatDateTime(n.created_at)}</td>
        `;
        tbody.appendChild(tr);
      });
      
      displayMemberPagination(data.pagination);
      
      loading.classList.add('d-none');
      content.classList.remove('d-none');
    } else {
      alert(data.message || 'Failed to load member notifications');
      loading.classList.add('d-none');
    }
  } catch (e) {
    console.error('Load member notifications error:', e);
    alert('Error loading member notifications');
    loading.classList.add('d-none');
  }
}

async function viewNotificationDetails(id) {
  try {
    const resp = await fetch(`${api}?action=get&id=${id}`);
    const data = await resp.json();
    
    if (!data.success || !data.notification) {
      alert('Failed to load notification details');
      return;
    }
    
    const n = data.notification;
    const modal = document.getElementById('notificationDetailModal');
    const content = document.getElementById('notificationDetailContent');
    
    const channelBadge = `<span class="badge badge-channel ${n.channel}">${n.channel.toUpperCase()}</span>`;
    const statusBadge = `<span class="badge badge-status ${n.status}">${n.status.charAt(0).toUpperCase() + n.status.slice(1)}</span>`;
    
    content.innerHTML = `
      <div class="row mb-3">
        <div class="col-md-6"><strong>Notification ID:</strong></div>
        <div class="col-md-6"><code>${escapeHtml(n.notification_id || 'N/A')}</code></div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Subject:</strong></div>
        <div class="col-md-6">${escapeHtml(n.subject || 'N/A')}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Message:</strong></div>
        <div class="col-md-6"><div style="white-space:pre-wrap;word-wrap:break-word;">${escapeHtml(n.message || 'N/A')}</div></div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Channel:</strong></div>
        <div class="col-md-6">${channelBadge}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Type:</strong></div>
        <div class="col-md-6"><span class="badge bg-secondary">${escapeHtml(n.type || 'N/A')}</span></div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Status:</strong></div>
        <div class="col-md-6">${statusBadge}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Recipient Type:</strong></div>
        <div class="col-md-6">${escapeHtml(n.recipient_type || 'N/A')}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Recipient Email:</strong></div>
        <div class="col-md-6">${escapeHtml(n.recipient_email || 'N/A')}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Recipient Phone:</strong></div>
        <div class="col-md-6">${escapeHtml(n.recipient_phone || 'N/A')}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Language:</strong></div>
        <div class="col-md-6">${escapeHtml(n.language || 'N/A')}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Created At:</strong></div>
        <div class="col-md-6">${formatDateTime(n.created_at)}</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6"><strong>Sent At:</strong></div>
        <div class="col-md-6">${formatDateTime(n.sent_at)}</div>
      </div>
      ${n.delivered_at ? `
      <div class="row mb-3">
        <div class="col-md-6"><strong>Delivered At:</strong></div>
        <div class="col-md-6">${formatDateTime(n.delivered_at)}</div>
      </div>
      ` : ''}
      ${n.email_provider_response ? `
      <div class="row mb-3">
        <div class="col-md-6"><strong>Email Response:</strong></div>
        <div class="col-md-6"><small class="text-muted">${escapeHtml(n.email_provider_response)}</small></div>
      </div>
      ` : ''}
      ${n.sms_provider_response ? `
      <div class="row mb-3">
        <div class="col-md-6"><strong>SMS Response:</strong></div>
        <div class="col-md-6"><small class="text-muted">${escapeHtml(n.sms_provider_response)}</small></div>
      </div>
      ` : ''}
      ${n.notes ? `
      <div class="row mb-3">
        <div class="col-md-6"><strong>Notes:</strong></div>
        <div class="col-md-6">${escapeHtml(n.notes)}</div>
      </div>
      ` : ''}
    `;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  } catch (e) {
    console.error('View notification details error:', e);
    alert('Error loading notification details');
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function displayMemberPagination(pagination) {
  const container = document.getElementById('memberPagination');
  const info = document.getElementById('memberPaginationInfo');
  container.innerHTML = '';
  
  const start = (pagination.page - 1) * pagination.per_page + 1;
  const end = Math.min(pagination.page * pagination.per_page, pagination.total);
  info.textContent = `Showing ${start}-${end} of ${pagination.total}`;
  
  if (pagination.total_pages <= 1) return;
  
  // Previous button
  const prevLi = document.createElement('li');
  prevLi.className = `page-item ${pagination.page === 1 ? 'disabled' : ''}`;
  prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadMemberNotifications(${pagination.page - 1}); return false;">Previous</a>`;
  container.appendChild(prevLi);
  
  // Page numbers
  const startPage = Math.max(1, pagination.page - 2);
  const endPage = Math.min(pagination.total_pages, pagination.page + 2);
  
  if (startPage > 1) {
    const li = document.createElement('li');
    li.className = 'page-item';
    li.innerHTML = `<a class="page-link" href="#" onclick="loadMemberNotifications(1); return false;">1</a>`;
    container.appendChild(li);
    if (startPage > 2) {
      const ellipsis = document.createElement('li');
      ellipsis.className = 'page-item disabled';
      ellipsis.innerHTML = `<span class="page-link">...</span>`;
      container.appendChild(ellipsis);
    }
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const li = document.createElement('li');
    li.className = `page-item ${i === pagination.page ? 'active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#" onclick="loadMemberNotifications(${i}); return false;">${i}</a>`;
    container.appendChild(li);
  }
  
  if (endPage < pagination.total_pages) {
    if (endPage < pagination.total_pages - 1) {
      const ellipsis = document.createElement('li');
      ellipsis.className = 'page-item disabled';
      ellipsis.innerHTML = `<span class="page-link">...</span>`;
      container.appendChild(ellipsis);
    }
    const li = document.createElement('li');
    li.className = 'page-item';
    li.innerHTML = `<a class="page-link" href="#" onclick="loadMemberNotifications(${pagination.total_pages}); return false;">${pagination.total_pages}</a>`;
    container.appendChild(li);
  }
  
  // Next button
  const nextLi = document.createElement('li');
  nextLi.className = `page-item ${pagination.page === pagination.total_pages ? 'disabled' : ''}`;
  nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadMemberNotifications(${pagination.page + 1}); return false;">Next</a>`;
  container.appendChild(nextLi);
}

function formatDateTime(datetime) {
  if (!datetime) return '-';
  const date = new Date(datetime);
  return date.toLocaleString();
}
</script>
</body>
</html>
