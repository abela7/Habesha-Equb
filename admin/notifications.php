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
  </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>
<div class="app-content container-fluid">
  <div class="row g-3">
    <div class="col-12">
      <div class="card card-modern">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="fas fa-bell me-2 text-warning"></i>Create Notification</h5>
          <span class="text-muted small">Bilingual • Email optional • AJAX</span>
        </div>
        <div class="card-body">
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
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email" value="1">
                <label class="form-check-label" for="sendEmail">Send email copy to eligible members</label>
              </div>
            </div>

            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane me-1"></i>Send</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-modern">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="fas fa-inbox me-2 text-primary"></i>Recent Notifications</h5>
          <button class="btn btn-sm btn-outline-secondary" id="btnRefresh"><i class="fas fa-rotate"></i></button>
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

<script>
const api = 'api/notifications.php';

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
      alert(`Sent. Emails: ${data.email_result.sent||0}/${(data.email_result.sent||0)+(data.email_result.failed||0)}`);
      form.reset();
      document.getElementById('selectedMembers').innerHTML='';
      setAudienceUI();
      loadList();
    } else {
      alert(data.message || 'Failed');
    }
  });
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


