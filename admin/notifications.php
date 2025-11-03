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
  <title>Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet" />
  <style>
    .card-modern{border:1px solid var(--border-light);border-radius:16px;box-shadow:0 8px 24px rgba(48,25,52,0.06)}
    .card-modern .card-header{background:linear-gradient(135deg, var(--color-cream), #fff);border-bottom:1px solid var(--border-light);padding:16px 20px}
    .chip{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--secondary-bg);margin:4px;font-size:13px}
    .chip .remove{cursor:pointer;color:var(--color-coral)}
    .aud-pill{border:1px dashed var(--border-light);border-radius:999px;padding:4px 10px;font-size:12px}
    .table td, .table th{vertical-align:middle}
    .delivery-stats .card{border-radius:8px;overflow:hidden}
    .delivery-stats h3{font-weight:700;font-size:2rem}
    .nav-tabs .nav-link{color:var(--text-muted);border:none;border-bottom:2px solid transparent;padding:12px 20px}
    .nav-tabs .nav-link.active{color:var(--primary);border-bottom-color:var(--primary);font-weight:600}
    .nav-tabs .nav-link:hover{border-bottom-color:var(--border-light);color:var(--primary)}
    .tab-content{padding-top:20px}
    .member-info-card{background:var(--secondary-bg);border-radius:8px;padding:15px;margin-bottom:15px}
    .template-card{border:1px solid var(--border-light);border-radius:8px;padding:12px;margin-bottom:10px;cursor:pointer;transition:all 0.2s}
    .template-card:hover{background:var(--secondary-bg);border-color:var(--primary)}
    .template-card.selected{background:var(--primary-light);border-color:var(--primary)}
  </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>
<div class="app-content container-fluid">
  <div class="row g-3">
    <div class="col-12">
      <div class="card card-modern">
        <div class="card-header">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications-pane" type="button" role="tab">
                <i class="fas fa-bell me-2"></i>Notifications
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms-pane" type="button" role="tab">
                <i class="fas fa-sms me-2"></i>Quick SMS
              </button>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <!-- Notifications Tab -->
            <div class="tab-pane fade show active" id="notifications-pane" role="tabpanel">
          <form id="composeForm" class="row g-3">
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>" />
            <div class="col-12">
              <label class="form-label">Audience</label>
              <div class="d-flex gap-3 flex-wrap">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="audience" id="audAll" value="all_members" checked>
                  <label class="form-check-label" for="audAll">All active members</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="audience" id="audMembers" value="member">
                  <label class="form-check-label" for="audMembers">Specific members</label>
                </div>
              </div>
            </div>

            <div class="col-12 d-none" id="memberPickerWrap">
              <label class="form-label">Add Members</label>
              <div class="input-group mb-2">
                <input type="text" id="memberSearch" class="form-control" placeholder="Search by name, code, or email" />
                <button class="btn btn-outline-primary" type="button" id="btnSearch"><i class="fas fa-search me-1"></i>Search</button>
              </div>
              <div id="memberResults" class="mb-2"></div>
              <div id="selectedMembers" class="d-flex flex-wrap"></div>
              <input type="hidden" name="member_ids" id="memberIds" />
            </div>

            <div class="col-md-6">
              <label class="form-label">Title (English)</label>
              <input type="text" class="form-control" name="title_en" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Title (Amharic)</label>
              <input type="text" class="form-control" name="title_am" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Detail (English)</label>
              <textarea class="form-control" name="body_en" rows="4" required></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Detail (Amharic)</label>
              <textarea class="form-control" name="body_am" rows="4" required></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Send via</label>
              <select class="form-select" name="send_channel" id="sendChannel" required>
                <option value="email">Email only</option>
                <option value="sms">SMS only</option>
                <option value="both" selected>Both (Email + SMS)</option>
              </select>
              <small class="text-muted">Choose how to send this notification</small>
            </div>
            <div class="col-md-6">
              <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="exportWhatsapp" name="export_whatsapp" value="1">
                <label class="form-check-label" for="exportWhatsapp">Export WhatsApp text (copy after sending)</label>
              </div>
            </div>

            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane me-1"></i>Send</button>
            </div>
          </form>
            </div>
            <!-- SMS Tab -->
            <div class="tab-pane fade" id="sms-pane" role="tabpanel">
              <div class="row g-3">
                <!-- Step 1: Select Member -->
                <div class="col-md-6">
                  <div class="card border">
                    <div class="card-header bg-light">
                      <h6 class="mb-0"><i class="fas fa-user me-2"></i>Step 1: Select Member</h6>
                    </div>
                    <div class="card-body">
                      <div class="input-group mb-3">
                        <input type="text" id="smsMemberSearch" class="form-control" placeholder="Search by name, code, email, or phone (leave empty to see all)" />
                        <button class="btn btn-outline-primary" type="button" id="btnSmsSearch" title="Click to search or view all members">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                      <small class="text-muted d-block mb-2">
                        <i class="fas fa-lightbulb me-1"></i>Tip: Leave search empty and click search to view all active members
                      </small>
                      <div id="smsMemberResults" class="mb-2"></div>
                      <div id="smsSelectedMember" class="member-info-card d-none">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <div>
                            <h6 class="mb-1" id="smsMemberName"></h6>
                            <small class="text-muted" id="smsMemberCode"></small>
                          </div>
                          <button class="btn btn-sm btn-outline-danger" id="btnClearMember"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="row g-2 small">
                          <div class="col-6"><strong>Phone:</strong> <span id="smsMemberPhone"></span></div>
                          <div class="col-6"><strong>Email:</strong> <span id="smsMemberEmail"></span></div>
                          <div class="col-6"><strong>Language:</strong> <span id="smsMemberLang"></span></div>
                          <div class="col-6"><strong>Status:</strong> <span id="smsMemberStatus"></span></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Step 2: Select Template -->
                <div class="col-md-6">
                  <div class="card border">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                      <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Step 2: Select Template</h6>
                      <button class="btn btn-sm btn-outline-primary" id="btnNewTemplate" data-bs-toggle="modal" data-bs-target="#templateModal">
                        <i class="fas fa-plus"></i> New
                      </button>
                    </div>
                    <div class="card-body">
                      <div id="templateList" class="mb-3" style="max-height:300px;overflow-y:auto;">
                        <div class="text-center text-muted py-3">
                          <i class="fas fa-spinner fa-spin"></i> Loading templates...
                        </div>
                      </div>
                      <button class="btn btn-sm btn-outline-secondary w-100" id="btnManageTemplates">
                        <i class="fas fa-cog me-1"></i>Manage Templates
                      </button>
                    </div>
                  </div>
                </div>
                <!-- Step 3: Preview & Send -->
                <div class="col-12">
                  <div class="card border">
                    <div class="card-header bg-light">
                      <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Step 3: Preview & Send</h6>
                    </div>
                    <div class="card-body">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Title (English)</label>
                          <input type="text" class="form-control" id="smsTitleEn" placeholder="Will be filled from template" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Title (Amharic)</label>
                          <input type="text" class="form-control" id="smsTitleAm" placeholder="Will be filled from template" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Message (English)</label>
                          <textarea class="form-control" id="smsBodyEn" rows="4" placeholder="Will be filled from template"></textarea>
                          <small class="text-muted" id="smsCharCountEn">0 characters</small>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Message (Amharic)</label>
                          <textarea class="form-control" id="smsBodyAm" rows="4" placeholder="Will be filled from template"></textarea>
                          <small class="text-muted" id="smsCharCountAm">0 characters</small>
                        </div>
                        <div class="col-12">
                          <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Template Variables:</strong> Use <code>{first_name}</code>, <code>{last_name}</code>, <code>{member_id}</code>, <code>{amount}</code>, <code>{due_date}</code> - they will be replaced automatically
                          </div>
                        </div>
                        <div class="col-12">
                          <button class="btn btn-success" id="btnSendSms" disabled>
                            <i class="fas fa-paper-plane me-1"></i>Send SMS
                          </button>
                          <button class="btn btn-outline-secondary" id="btnPreviewSms">
                            <i class="fas fa-eye me-1"></i>Preview
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-modern">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="fas fa-inbox me-2 text-primary"></i>Recent Notifications</h5>
          <div class="d-flex gap-2">
            <a href="notification-history.php" class="btn btn-sm btn-primary">
              <i class="fas fa-history me-1"></i>View Full History
            </a>
            <button class="btn btn-sm btn-outline-secondary" id="btnRefresh"><i class="fas fa-rotate"></i></button>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-2">
            <button class="btn btn-sm btn-outline-primary" id="btnMarkAll"><i class="fas fa-check-double me-1"></i>Mark all as read</button>
            <button class="btn btn-sm btn-outline-danger" id="btnDeleteAll"><i class="fas fa-trash me-1"></i>Delete all</button>
          </div>
          <div class="table-responsive">
            <table class="table align-middle" id="notifTable">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Subject</th>
                  <th>Audience</th>
                  <th>Sent</th>
                  <th>Email</th>
                  <th style="width:110px;">Actions</th>
                </tr>
              </thead>
              <tbody id="listBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i><span id="templateModalTitle">New Template</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="templateForm">
          <input type="hidden" id="templateId" name="template_id" />
          <div class="mb-3">
            <label class="form-label">Template Name</label>
            <input type="text" class="form-control" id="templateName" name="template_name" required placeholder="e.g., Payment Reminder" />
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" id="templateCategory" name="category">
              <option value="general">General</option>
              <option value="payment">Payment</option>
              <option value="welcome">Welcome</option>
              <option value="reminder">Reminder</option>
              <option value="alert">Alert</option>
            </select>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title (English)</label>
              <input type="text" class="form-control" id="templateTitleEn" name="title_en" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Title (Amharic)</label>
              <input type="text" class="form-control" id="templateTitleAm" name="title_am" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Message (English)</label>
              <textarea class="form-control" id="templateBodyEn" name="body_en" rows="5" required></textarea>
              <small class="text-muted">Use {first_name}, {last_name}, {member_id}, {amount}, {due_date} as variables</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Message (Amharic)</label>
              <textarea class="form-control" id="templateBodyAm" name="body_am" rows="5" required></textarea>
              <small class="text-muted">Use same variables as English</small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="btnDeleteTemplate" style="display:none;">Delete</button>
        <button type="button" class="btn btn-primary" id="btnSaveTemplate">Save Template</button>
      </div>
    </div>
  </div>
</div>

<script>
const api = 'api/notifications.php';
const smsApi = 'api/sms-templates.php';

function audience(){ return document.querySelector('input[name="audience"]:checked').value; }

function setAudienceUI(){
  const wrap = document.getElementById('memberPickerWrap');
  wrap.classList.toggle('d-none', audience() !== 'member');
}

async function searchMembers(){
  const q = document.getElementById('memberSearch').value.trim();
  const url = new URL(window.location.origin + '/admin/api/notifications.php');
  url.searchParams.set('action','search_members');
  if (q) url.searchParams.set('q', q);
  const resp = await fetch(url.toString());
  const data = await resp.json();
  const res = document.getElementById('memberResults');
  res.innerHTML = '';
  if (data.success && Array.isArray(data.members)){
    data.members.forEach(m=>{
      const btn = document.createElement('button');
      btn.type='button';
      btn.className='btn btn-sm btn-outline-secondary me-2 mb-2';
      btn.textContent = `${m.first_name} ${m.last_name} (${m.code})`;
      btn.addEventListener('click',()=>addChip(m.id, `${m.first_name} ${m.last_name}`));
      res.appendChild(btn);
    });
  } else {
    res.textContent = 'No results';
  }
}

function addChip(id, name){
  const wrap = document.getElementById('selectedMembers');
  if (wrap.querySelector(`.chip[data-id="${id}"]`)) return;
  const chip = document.createElement('span');
  chip.className='chip'; chip.dataset.id=id; chip.innerHTML = `${name} <i class='fas fa-times remove'></i>`;
  chip.querySelector('.remove').addEventListener('click',()=>chip.remove());
  wrap.appendChild(chip);
  syncMemberIds();
}

function syncMemberIds(){
  const ids = Array.from(document.querySelectorAll('#selectedMembers .chip')).map(c=>c.dataset.id);
  document.getElementById('memberIds').value = ids.length ? JSON.stringify(ids) : '';
}

async function loadList(){
  const resp = await fetch(`${api}?action=list`);
  const data = await resp.json();
  const tbody = document.getElementById('listBody');
  tbody.innerHTML = '';
  if (data.success && Array.isArray(data.notifications)){
    data.notifications.forEach(n=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><code>${n.notification_id || ''}</code></td>
        <td>${escapeHtml(n.subject || '')}</td>
        <td><span class="aud-pill">${n.recipient_type}</span></td>
        <td>${n.sent_at ? n.sent_at : ''}</td>
        <td>${n.email_provider_response ? '<span class="text-success">logged</span>' : '-'}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${n.id}"><i class="fas fa-pen"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${n.id}"><i class="fas fa-trash"></i></button>
        </td>`;
      tbody.appendChild(tr);
    });
    document.querySelectorAll('#notifTable [data-action="delete"]').forEach(btn=>btn.addEventListener('click', ()=>deleteOne(btn.dataset.id)));
    document.querySelectorAll('#notifTable [data-action="edit"]').forEach(btn=>btn.addEventListener('click', ()=>editOne(btn.dataset.id)));
  }
}

function escapeHtml(s){return (s||'').replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c]));}

document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('input[name="audience"]').forEach(r=>r.addEventListener('change', setAudienceUI));
  setAudienceUI();
  document.getElementById('btnSearch').addEventListener('click', searchMembers);
  document.getElementById('btnRefresh').addEventListener('click', loadList);
  loadList();

  document.getElementById('composeForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    syncMemberIds();
    const form = e.target;
    const fd = new FormData(form);
    const resp = await fetch(api, { method:'POST', body: fd });
    const data = await resp.json();
    if (data.success){
      // Show detailed delivery report modal
      showDeliveryReportModal(data);
      form.reset();
      document.getElementById('selectedMembers').innerHTML='';
      setAudienceUI();
      loadList();
    } else {
      alert(data.message || 'Failed');
    }
  });

  // Show detailed delivery report modal
  function showDeliveryReportModal(data) {
    let modal = document.getElementById('deliveryReportModal');
    if (!modal) {
      const html = `
      <div class="modal fade" id="deliveryReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Notification Delivery Report</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deliveryReportBody"></div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" id="exportWhatsappBtn" style="display:none;">
                <i class="fab fa-whatsapp me-1"></i>Export WhatsApp
              </button>
            </div>
          </div>
        </div>
      </div>`;
      document.body.insertAdjacentHTML('beforeend', html);
      modal = document.getElementById('deliveryReportModal');
    }
    
    const body = modal.querySelector('#deliveryReportBody');
    const exportBtn = modal.querySelector('#exportWhatsappBtn');
    
    // Build delivery report
    const totalEmails = (data.email_result?.sent||0) + (data.email_result?.failed||0);
    const totalSMS = (data.sms_result?.sent||0) + (data.sms_result?.failed||0);
    
    let html = '<div class="delivery-stats">';
    
    // Overall status
    html += `<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><strong>Notification sent successfully!</strong></div>`;
    
    html += `<div class="row mb-3">`;
    html += `<div class="col-md-6"><strong>Channel:</strong> ${data.send_channel ? data.send_channel.toUpperCase() : 'N/A'}</div>`;
    html += `<div class="col-md-6"><strong>Notification ID:</strong> <code>${data.notification_code || 'N/A'}</code></div>`;
    html += `</div>`;
    
    // Email stats
    if (data.send_channel === 'email' || data.send_channel === 'both') {
      html += `<div class="card mb-3">
        <div class="card-header bg-primary text-white">
          <i class="fas fa-envelope me-2"></i>Email Delivery
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-4">
              <h3 class="text-success mb-0">${data.email_result?.sent||0}</h3>
              <small class="text-muted">Sent</small>
            </div>
            <div class="col-4">
              <h3 class="text-danger mb-0">${data.email_result?.failed||0}</h3>
              <small class="text-muted">Failed</small>
            </div>
            <div class="col-4">
              <h3 class="text-info mb-0">${totalEmails}</h3>
              <small class="text-muted">Total</small>
            </div>
          </div>
          ${data.email_result?.failed > 0 ? `<div class="alert alert-warning mt-2 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>${data.email_result.failed} emails failed. Check error logs for details.</div>` : ''}
        </div>
      </div>`;
    }
    
    // SMS stats
    if (data.send_channel === 'sms' || data.send_channel === 'both') {
      html += `<div class="card mb-3">
        <div class="card-header bg-success text-white">
          <i class="fas fa-sms me-2"></i>SMS Delivery
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-4">
              <h3 class="text-success mb-0">${data.sms_result?.sent||0}</h3>
              <small class="text-muted">Sent</small>
            </div>
            <div class="col-4">
              <h3 class="text-danger mb-0">${data.sms_result?.failed||0}</h3>
              <small class="text-muted">Failed</small>
            </div>
            <div class="col-4">
              <h3 class="text-info mb-0">${totalSMS}</h3>
              <small class="text-muted">Total</small>
            </div>
          </div>
          ${data.sms_result?.failed > 0 ? `<div class="alert alert-warning mt-2 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>${data.sms_result.failed} SMS failed. Common reasons: invalid phone numbers, insufficient credits, or rate limits.</div>` : ''}
        </div>
      </div>`;
    }
    
    // Message preview
    if (data.preview) {
      html += `<details class="mb-3">
        <summary style="cursor:pointer;" class="fw-bold mb-2"><i class="fas fa-eye me-2"></i>Message Preview</summary>
        <div class="card">
          <div class="card-body">
            <h6>English</h6>
            <p class="mb-2"><strong>${data.preview.title_en}</strong></p>
            <p class="text-muted">${data.preview.body_en}</p>
            <hr>
            <h6>Amharic</h6>
            <p class="mb-2"><strong>${data.preview.title_am}</strong></p>
            <p class="text-muted">${data.preview.body_am}</p>
          </div>
        </div>
      </details>`;
    }
    
    html += '</div>';
    
    body.innerHTML = html;
    
    // Show/hide WhatsApp export button
    if (data.whatsapp_texts || data.whatsapp_broadcast) {
      exportBtn.style.display = 'inline-block';
      exportBtn.onclick = () => showWhatsappModal(data);
    } else {
      exportBtn.style.display = 'none';
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }

  // Build a professional WhatsApp modal with proper newlines and copy buttons
  function showWhatsappModal(data){
    let modal = document.getElementById('waExportModal');
    if (!modal) {
      const html = `
      <div class="modal fade" id="waExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fas fa-paper-plane text-success me-2"></i>WhatsApp Export</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="waExportBody"></div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>`;
      document.body.insertAdjacentHTML('beforeend', html);
      modal = document.getElementById('waExportModal');
    }
    const body = modal.querySelector('#waExportBody');
    const blocks = [];
    const esc = s => (s||'');
    const normalize = s => (s||'').replace(/\r\n/g,'\n').replace(/\n/g,'\n');
    const toTextarea = (label, txt) => {
      const clean = normalize(txt).replace(/\\n/g,'\n');
      const url = 'https://wa.me/?text=' + encodeURIComponent(clean);
      return `
      <div class="mb-3 p-2 border rounded">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>${label}</strong>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-copy="1">Copy</button>
            <a class="btn btn-sm btn-success" target="_blank" href="${url}"><i class="fab fa-whatsapp me-1"></i>Open in WhatsApp</a>
          </div>
        </div>
        <textarea class="form-control" rows="5">${clean}</textarea>
      </div>`;
    };
    blocks.push(`<div class="alert alert-info">${summary}</div>`);
    if (Array.isArray(data.whatsapp_texts) && data.whatsapp_texts.length){
      blocks.push(`<h6 class="mb-2">Per Member</h6>`);
      data.whatsapp_texts.forEach(w=>{
        const label = `${esc(w.name)} [${String(w.language||'').toUpperCase()}]`;
        blocks.push(toTextarea(label, w.text||''));
      });
    }
    if (data.whatsapp_broadcast){
      blocks.push(`<h6 class="mt-3 mb-2">Broadcast</h6>`);
      if (data.whatsapp_broadcast.en){ blocks.push(toTextarea('Broadcast (EN)', data.whatsapp_broadcast.en)); }
      if (data.whatsapp_broadcast.am){ blocks.push(toTextarea('Broadcast (AM)', data.whatsapp_broadcast.am)); }
    }
    body.innerHTML = blocks.join('');
    // wire copy buttons
    body.querySelectorAll('[data-copy]')?.forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const ta = btn.closest('.mb-3')?.querySelector('textarea');
        if (!ta) return;
        ta.select(); ta.setSelectionRange(0, 99999);
        document.execCommand('copy');
        btn.textContent = 'Copied';
        setTimeout(()=> btn.textContent='Copy', 1200);
      });
    });
    const bsModal = new bootstrap.Modal(modal); bsModal.show();
  }
  document.getElementById('btnMarkAll').addEventListener('click', async ()=>{
    if (!confirm('Mark all notifications as read for all members?')) return;
    const fd = new FormData(); fd.append('action','mark_all_read'); fd.append('csrf_token','<?php echo htmlspecialchars($csrf); ?>');
    const r = await fetch(api, { method:'POST', body: fd }); const d = await r.json(); if (d && d.success){ loadList(); }
  });
  document.getElementById('btnDeleteAll').addEventListener('click', async ()=>{
    if (!confirm('Delete all notifications? This cannot be undone.')) return;
    const fd = new FormData(); fd.append('action','delete_all'); fd.append('csrf_token','<?php echo htmlspecialchars($csrf); ?>');
    const r = await fetch(api, { method:'POST', body: fd }); const d = await r.json(); if (d && d.success){ loadList(); }
  });

  // ===== SMS TAB FUNCTIONALITY =====
  let selectedMember = null;
  let selectedTemplate = null;

  // Load templates on SMS tab open
  document.getElementById('sms-tab').addEventListener('shown.bs.tab', loadTemplates);

  // Member search for SMS
  document.getElementById('btnSmsSearch').addEventListener('click', searchSmsMember);
  document.getElementById('smsMemberSearch').addEventListener('keypress', (e)=>e.key==='Enter' && searchSmsMember());

  // Clear selected member
  document.getElementById('btnClearMember').addEventListener('click', ()=>{
    selectedMember = null;
    document.getElementById('smsSelectedMember').classList.add('d-none');
    document.getElementById('smsMemberSearch').value = '';
    updateSendButton();
  });

  // Character count updates
  ['smsBodyEn', 'smsBodyAm'].forEach(id=>{
    document.getElementById(id).addEventListener('input', ()=>{
      const text = document.getElementById(id).value;
      const count = text.length;
      const lang = id.includes('En') ? 'En' : 'Am';
      const limit = lang === 'En' ? 160 : 70;
      const el = document.getElementById(`smsCharCount${lang}`);
      el.textContent = `${count} characters (${limit} max)`;
      el.className = count > limit ? 'text-danger' : 'text-muted';
    });
  });

  // Template management
  document.getElementById('btnNewTemplate').addEventListener('click', ()=>{
    resetTemplateForm();
    document.getElementById('templateModalTitle').textContent = 'New Template';
    document.getElementById('btnDeleteTemplate').style.display = 'none';
  });

  document.getElementById('btnSaveTemplate').addEventListener('click', saveTemplate);
  document.getElementById('btnDeleteTemplate').addEventListener('click', deleteTemplate);
  document.getElementById('btnManageTemplates').addEventListener('click', ()=>loadTemplates(true));

  // Send SMS
  document.getElementById('btnSendSms').addEventListener('click', sendQuickSms);
  document.getElementById('btnPreviewSms').addEventListener('click', previewSms);

  // Load templates
  async function loadTemplates(showAll = false){
    try {
      const resp = await fetch(`${smsApi}?action=list`);
      const data = await resp.json();
      const container = document.getElementById('templateList');
      
      if (!data.success || !Array.isArray(data.templates) || data.templates.length === 0){
        container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-inbox me-2"></i>No templates yet. Click "New" to create one.</div>';
        return;
      }

      let html = '';
      data.templates.forEach(t => {
        if (!showAll && !t.is_active) return;
        html += `
          <div class="template-card ${selectedTemplate?.id === t.id ? 'selected' : ''}" data-id="${t.id}">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <strong>${escapeHtml(t.template_name)}</strong>
                <span class="badge bg-secondary ms-2">${t.category}</span>
                <br><small class="text-muted">${escapeHtml(t.title_en)}</small>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary" onclick="editTemplate(${t.id})" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="selectTemplate(${t.id})" title="Use">
                  <i class="fas fa-check"></i>
                </button>
              </div>
            </div>
            <small class="text-muted">Used ${t.usage_count} times</small>
          </div>`;
      });
      container.innerHTML = html || '<div class="text-center text-muted py-3">No active templates</div>';
    } catch(e){
      console.error('Load templates error:', e);
      document.getElementById('templateList').innerHTML = '<div class="text-danger">Error loading templates</div>';
    }
  }

  // Select template
  window.selectTemplate = async function(templateId){
    try {
      const resp = await fetch(`${smsApi}?action=get&id=${templateId}`);
      const data = await resp.json();
      if (!data.success || !data.template) return alert('Template not found');
      
      selectedTemplate = data.template;
      document.getElementById('smsTitleEn').value = data.template.title_en || '';
      document.getElementById('smsTitleAm').value = data.template.title_am || '';
      document.getElementById('smsBodyEn').value = data.template.body_en || '';
      document.getElementById('smsBodyAm').value = data.template.body_am || '';
      
      // Update character counts
      document.getElementById('smsBodyEn').dispatchEvent(new Event('input'));
      document.getElementById('smsBodyAm').dispatchEvent(new Event('input'));
      
      // Highlight selected template
      document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('selected'));
      document.querySelector(`.template-card[data-id="${templateId}"]`)?.classList.add('selected');
      
      // Replace variables if member is already selected
      if (selectedMember) {
        replaceTemplateVariables();
      }
      
      updateSendButton();
    } catch(e){
      console.error('Select template error:', e);
      alert('Error loading template');
    }
  };

  // Edit template
  window.editTemplate = async function(templateId){
    try {
      const resp = await fetch(`${smsApi}?action=get&id=${templateId}`);
      const data = await resp.json();
      if (!data.success || !data.template) return alert('Template not found');
      
      const t = data.template;
      document.getElementById('templateId').value = t.id;
      document.getElementById('templateName').value = t.template_name || '';
      document.getElementById('templateCategory').value = t.category || 'general';
      document.getElementById('templateTitleEn').value = t.title_en || '';
      document.getElementById('templateTitleAm').value = t.title_am || '';
      document.getElementById('templateBodyEn').value = t.body_en || '';
      document.getElementById('templateBodyAm').value = t.body_am || '';
      
      document.getElementById('templateModalTitle').textContent = 'Edit Template';
      document.getElementById('btnDeleteTemplate').style.display = 'inline-block';
      document.getElementById('btnDeleteTemplate').onclick = ()=>deleteTemplate(t.id);
      
      const modal = new bootstrap.Modal(document.getElementById('templateModal'));
      modal.show();
    } catch(e){
      console.error('Edit template error:', e);
      alert('Error loading template');
    }
  };

  // Search SMS member (shows all members if search is empty)
  async function searchSmsMember(){
    const q = document.getElementById('smsMemberSearch').value.trim();
    const res = document.getElementById('smsMemberResults');
    
    // Show loading state
    res.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading members...</div>';
    
    try {
      const url = new URL(window.location.origin + '/admin/api/notifications.php');
      url.searchParams.set('action','search_members');
      if (q) {
        url.searchParams.set('q', q);
      }
      const resp = await fetch(url.toString());
      const data = await resp.json();
      res.innerHTML = '';
      
      if (data.success && Array.isArray(data.members) && data.members.length > 0){
        // Show count info
        const info = document.createElement('div');
        info.className = 'mb-2 small text-muted';
        info.innerHTML = `<i class="fas fa-info-circle me-1"></i>Found ${data.members.length} ${q ? 'matching' : 'active'} member${data.members.length !== 1 ? 's' : ''}`;
        res.appendChild(info);
        
        // Show members
        data.members.forEach(m => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-sm btn-outline-primary me-2 mb-2';
          btn.innerHTML = `<i class="fas fa-user me-1"></i>${m.first_name} ${m.last_name} <small>(${m.member_id || m.code})</small>`;
          btn.addEventListener('click', ()=>selectSmsMember(m));
          res.appendChild(btn);
        });
      } else {
        res.innerHTML = '<div class="text-muted"><i class="fas fa-inbox me-2"></i>No members found</div>';
      }
    } catch(e){
      console.error('Search error:', e);
      res.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading members</div>';
    }
  }

  // Select SMS member
  function selectSmsMember(member){
    selectedMember = member;
    document.getElementById('smsMemberName').textContent = `${member.first_name} ${member.last_name}`;
    document.getElementById('smsMemberCode').textContent = member.member_id || member.code || 'N/A';
    document.getElementById('smsMemberPhone').textContent = member.phone || 'N/A';
    document.getElementById('smsMemberEmail').textContent = member.email || 'N/A';
    document.getElementById('smsMemberLang').textContent = member.language_preference == 1 ? 'Amharic' : 'English';
    document.getElementById('smsMemberStatus').innerHTML = member.is_active == 1 
      ? '<span class="badge bg-success">Active</span>' 
      : '<span class="badge bg-danger">Inactive</span>';
    
    document.getElementById('smsSelectedMember').classList.remove('d-none');
    document.getElementById('smsMemberResults').innerHTML = '';
    document.getElementById('smsMemberSearch').value = '';
    
    // Replace variables if template is selected
    if (selectedTemplate) {
      replaceTemplateVariables();
    }
    
    updateSendButton();
  }

  // Replace template variables
  function replaceTemplateVariables(){
    if (!selectedMember || !selectedTemplate) return;
    
    const vars = {
      '{first_name}': selectedMember.first_name || '',
      '{last_name}': selectedMember.last_name || '',
      '{member_id}': selectedMember.member_id || selectedMember.code || '',
      '{amount}': 'N/A', // Can be customized
      '{due_date}': 'N/A' // Can be customized
    };
    
    // Replace variables in title and body fields
    const replacements = [
      {field: 'smsTitleEn', templateKey: 'title_en'},
      {field: 'smsTitleAm', templateKey: 'title_am'},
      {field: 'smsBodyEn', templateKey: 'body_en'},
      {field: 'smsBodyAm', templateKey: 'body_am'}
    ];
    
    replacements.forEach(({field, templateKey}) => {
      const fieldEl = document.getElementById(field);
      if (fieldEl && selectedTemplate[templateKey]) {
        let text = selectedTemplate[templateKey] || '';
        Object.keys(vars).forEach(key => {
          text = text.replace(new RegExp(key.replace(/[{}]/g, '\\$&'), 'g'), vars[key]);
        });
        fieldEl.value = text;
        fieldEl.dispatchEvent(new Event('input'));
      }
    });
  }

  // Update send button state
  function updateSendButton(){
    const btn = document.getElementById('btnSendSms');
    btn.disabled = !selectedMember || !selectedTemplate || 
                   !document.getElementById('smsTitleEn').value.trim() ||
                   !document.getElementById('smsBodyEn').value.trim();
  }

  // Save template
  async function saveTemplate(){
    const form = document.getElementById('templateForm');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    
    const fd = new FormData(form);
    fd.append('action', document.getElementById('templateId').value ? 'update' : 'create');
    fd.append('csrf_token', '<?php echo htmlspecialchars($csrf); ?>');
    
    try {
      const resp = await fetch(smsApi, { method: 'POST', body: fd });
      const data = await resp.json();
      if (data.success){
        bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
        resetTemplateForm();
        loadTemplates();
        alert('Template saved successfully!');
      } else {
        alert(data.message || 'Failed to save template');
      }
    } catch(e){
      console.error('Save template error:', e);
      alert('Error saving template');
    }
  }

  // Delete template
  async function deleteTemplate(templateId){
    if (!confirm('Delete this template? This cannot be undone.')) return;
    
    const template_id = templateId || document.getElementById('templateId').value;
    if (!template_id) return;
    
    try {
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('id', template_id);
      fd.append('csrf_token', '<?php echo htmlspecialchars($csrf); ?>');
      
      const resp = await fetch(smsApi, { method: 'POST', body: fd });
      const data = await resp.json();
      if (data.success){
        bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
        resetTemplateForm();
        loadTemplates();
        alert('Template deleted');
      } else {
        alert(data.message || 'Failed to delete');
      }
    } catch(e){
      console.error('Delete template error:', e);
      alert('Error deleting template');
    }
  }

  // Reset template form
  function resetTemplateForm(){
    document.getElementById('templateForm').reset();
    document.getElementById('templateId').value = '';
    document.getElementById('templateModalTitle').textContent = 'New Template';
    document.getElementById('btnDeleteTemplate').style.display = 'none';
  }

  // Preview SMS
  function previewSms(){
    if (!selectedMember) return alert('Please select a member first');
    
    const lang = selectedMember.language_preference == 1 ? 'Amharic' : 'English';
    const title = lang === 'Amharic' ? document.getElementById('smsTitleAm').value : document.getElementById('smsTitleEn').value;
    const body = lang === 'Amharic' ? document.getElementById('smsBodyAm').value : document.getElementById('smsBodyEn').value;
    const phone = selectedMember.phone || 'N/A';
    
    alert(`SMS Preview (${lang}):\n\nTo: ${phone}\n\n${title}\n\n${body}\n\n---\nCharacters: ${body.length} (Limit: ${lang === 'Amharic' ? 70 : 160})`);
  }

  // Send quick SMS
  async function sendQuickSms(){
    if (!selectedMember || !selectedTemplate) return alert('Please select member and template');
    
    if (!confirm(`Send SMS to ${selectedMember.first_name} ${selectedMember.last_name}?\n\nPhone: ${selectedMember.phone || 'N/A'}`)) return;
    
    const fd = new FormData();
    fd.append('action', 'send_quick_sms');
    fd.append('member_id', selectedMember.id);
    fd.append('title_en', document.getElementById('smsTitleEn').value);
    fd.append('title_am', document.getElementById('smsTitleAm').value);
    fd.append('body_en', document.getElementById('smsBodyEn').value);
    fd.append('body_am', document.getElementById('smsBodyAm').value);
    fd.append('csrf_token', '<?php echo htmlspecialchars($csrf); ?>');
    
    try {
      const resp = await fetch(api, { method: 'POST', body: fd });
      const data = await resp.json();
      
      if (data.success){
        // Update template usage count
        if (selectedTemplate?.id) {
          const updateFd = new FormData();
          updateFd.append('action', 'increment_usage');
          updateFd.append('id', selectedTemplate.id);
          updateFd.append('csrf_token', '<?php echo htmlspecialchars($csrf); ?>');
          fetch(smsApi, { method: 'POST', body: updateFd });
        }
        
        // Show delivery report
        showDeliveryReportModal(data);
        
        // Reset form
        selectedMember = null;
        document.getElementById('smsSelectedMember').classList.add('d-none');
        document.getElementById('smsMemberSearch').value = '';
        document.getElementById('smsTitleEn').value = '';
        document.getElementById('smsTitleAm').value = '';
        document.getElementById('smsBodyEn').value = '';
        document.getElementById('smsBodyAm').value = '';
        document.getElementById('smsBodyEn').dispatchEvent(new Event('input'));
        document.getElementById('smsBodyAm').dispatchEvent(new Event('input'));
        selectedTemplate = null;
        document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('selected'));
        updateSendButton();
      } else {
        alert(data.message || 'Failed to send SMS');
      }
    } catch(e){
      console.error('Send SMS error:', e);
      alert('Error sending SMS');
    }
  }

  // Update send button on input
  ['smsTitleEn', 'smsTitleAm', 'smsBodyEn', 'smsBodyAm'].forEach(id=>{
    document.getElementById(id).addEventListener('input', updateSendButton);
  });
});

async function deleteOne(id){
  if (!confirm('Delete this notification?')) return;
  const fd = new FormData(); fd.append('action','delete'); fd.append('id', String(id)); fd.append('csrf_token','<?php echo htmlspecialchars($csrf); ?>');
  const r = await fetch(api, { method:'POST', body: fd }); const d = await r.json(); if (d && d.success){ loadList(); }
}
async function editOne(id){
  const resp = await fetch(`${api}?action=get&id=${id}`); const d = await resp.json();
  if (!d || !d.success) return alert('Load failed');
  const subject = prompt('Edit subject', d.notification.subject || ''); if (subject===null) return;
  const message = prompt('Edit message', d.notification.message || ''); if (message===null) return;
  const fd = new FormData(); fd.append('action','update'); fd.append('id', String(id)); fd.append('subject', subject); fd.append('message', message); fd.append('csrf_token','<?php echo htmlspecialchars($csrf); ?>');
  const r = await fetch(api, { method:'POST', body: fd }); const j = await r.json(); if (j && j.success){ loadList(); }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


