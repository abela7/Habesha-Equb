<?php
// Admin Notifications - Compose and manage notifications
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

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifications - HabeshaEqub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="../assets/css/style.css" rel="stylesheet" />
    <style>
        .page-section { margin-bottom: 24px; }
        .card-modern { border: 1px solid var(--border-light); border-radius: 16px; box-shadow: 0 8px 24px rgba(48,25,52,0.06); }
        .card-modern .card-header { background: linear-gradient(135deg, var(--color-cream), #fff); border-bottom: 1px solid var(--border-light); padding: 16px 20px; }
        .badge-priority { border-radius: 8px; padding: 6px 10px; font-weight: 600; font-size: 12px; }
        .badge-high { background: linear-gradient(135deg, var(--color-coral), #e76f51); color: #fff; }
        .badge-normal { background: linear-gradient(135deg, var(--color-teal), #0F766E); color: #fff; }
        .audience-pill { border: 1px dashed var(--border-light); border-radius: 999px; padding: 4px 10px; font-size: 12px; }
        .chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: var(--secondary-bg); margin: 4px; font-size: 13px; }
        .chip .remove { cursor: pointer; color: var(--color-coral); }
    </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>
<div class="app-content">
    <div class="container-fluid">
        <div class="page-section">
            <div class="card card-modern">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-bell me-2 text-warning"></i>Create Notification</h5>
                    <span class="text-muted small">Bilingual • Dynamic • Targeted</span>
                </div>
                <div class="card-body">
                    <form id="notificationForm" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>" />
                        <input type="hidden" name="action" value="create" />
                        <div class="col-12">
                            <label class="form-label">Audience</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="audience_type" id="audAll" value="all" checked>
                                    <label class="form-check-label" for="audAll">All active members</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="audience_type" id="audEqub" value="equb">
                                    <label class="form-check-label" for="audEqub">By EQUB term</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="audience_type" id="audMembers" value="members">
                                    <label class="form-check-label" for="audMembers">Specific members</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="equbSelectWrap">
                            <label class="form-label">Select EQUB term</label>
                            <select class="form-select" id="equbSelect" name="equb_settings_id">
                                <option value="">Loading...</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="memberPickerWrap">
                            <label class="form-label">Add Members</label>
                            <div class="input-group mb-2">
                                <input type="text" id="memberSearch" class="form-control" placeholder="Search by name or member code" />
                                <button class="btn btn-outline-primary" type="button" id="btnSearchMembers"><i class="fas fa-search me-1"></i>Search</button>
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

                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Notification</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="page-section">
            <div class="card card-modern">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-inbox me-2 text-primary"></i>Recent Notifications</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="btnRefresh"><i class="fas fa-rotate"></i></button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Audience</th>
                                    <th>Priority</th>
                                    <th>Recipients</th>
                                    <th>Sent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="notificationsTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-pen-to-square me-2"></i>Edit Notification</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>" />
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="id" id="editId" />
            <div class="col-md-6">
                <label class="form-label">Title (English)</label>
                <input type="text" class="form-control" name="title_en" id="editTitleEn" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Title (Amharic)</label>
                <input type="text" class="form-control" name="title_am" id="editTitleAm" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Detail (English)</label>
                <textarea class="form-control" name="body_en" id="editBodyEn" rows="4" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Detail (Amharic)</label>
                <textarea class="form-control" name="body_am" id="editBodyAm" rows="4" required></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority" id="editPriority">
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="btnSaveEdit"><i class="fas fa-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const apiBase = 'api/notifications.php';
    const csrfToken = '<?php echo htmlspecialchars($csrf_token); ?>';
    let editModal, editEl;

    document.addEventListener('DOMContentLoaded', () => {
        initAudienceControls();
        loadEqubTerms();
        loadNotifications();
        document.getElementById('btnRefresh').addEventListener('click', loadNotifications);
        document.getElementById('notificationForm').addEventListener('submit', onSubmit);
        document.getElementById('btnSearchMembers').addEventListener('click', searchMembers);
        editEl = document.getElementById('editModal');
        editModal = new bootstrap.Modal(editEl);
        document.getElementById('btnSaveEdit').addEventListener('click', saveEdit);
    });

    function initAudienceControls() {
        const radios = document.querySelectorAll('input[name="audience_type"]');
        const equbWrap = document.getElementById('equbSelectWrap');
        const membersWrap = document.getElementById('memberPickerWrap');
        radios.forEach(r => r.addEventListener('change', () => {
            const val = document.querySelector('input[name="audience_type"]:checked').value;
            equbWrap.classList.toggle('d-none', val !== 'equb');
            membersWrap.classList.toggle('d-none', val !== 'members');
        }));
    }

    async function loadEqubTerms() {
        try {
            const resp = await fetch(`${apiBase}?action=get_equb_terms`);
            const data = await resp.json();
            const sel = document.getElementById('equbSelect');
            sel.innerHTML = '<option value="">Select term</option>';
            if (data.success) {
                data.terms.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = `${t.equb_name} (${t.start_date})`;
                    sel.appendChild(opt);
                });
            }
        } catch (e) { console.error(e); }
    }

    async function loadNotifications() {
        try {
            const resp = await fetch(`${apiBase}?action=list`);
            const data = await resp.json();
            const tbody = document.getElementById('notificationsTable');
            tbody.innerHTML = '';
            if (data.success && Array.isArray(data.notifications)) {
                data.notifications.forEach(n => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><code>${n.notification_code}</code></td>
                        <td>${escapeHtml(n.title_en)}</td>
                        <td><span class="audience-pill">${n.audience_type}</span></td>
                        <td><span class="badge-priority ${n.priority === 'high' ? 'badge-high' : 'badge-normal'}">${n.priority}</span></td>
                        <td>${n.recipients_count || 0}</td>
                        <td>${n.sent_at ? n.sent_at : ''}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" title="Edit" onclick="openEdit(${n.id})"><i class="fas fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteNotification(${n.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } catch (e) {
            console.error(e);
        }
    }

    function showToast(message, type='success'){
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type==='success'?'success':'danger'} border-0 position-fixed`;
        toast.style.cssText = 'right:20px; top:20px; z-index:20000;';
        toast.setAttribute('role','alert');
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${escapeHtml(message)}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        document.body.appendChild(toast);
        const t = new bootstrap.Toast(toast, { delay: 4000 });
        t.show();
        toast.addEventListener('hidden.bs.toast', ()=>toast.remove());
    }

    async function onSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const memberIds = Array.from(document.querySelectorAll('#selectedMembers .chip')).map(ch => ch.dataset.id);
        if (memberIds.length) formData.set('member_ids', JSON.stringify(memberIds));
        try {
            const resp = await fetch(apiBase, { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) {
                const stats = data.email_result ? `Email: sent ${data.email_result.sent || 0}, failed ${data.email_result.failed || 0}` : 'Sent';
                showToast(`Notification sent successfully. ${stats}`, 'success');
                form.reset();
                document.getElementById('selectedMembers').innerHTML = '';
                loadNotifications();
            } else {
                showToast(data.message || 'Failed to send', 'danger');
            }
        } catch (e) { showToast('Network error', 'danger'); }
    }

    async function searchMembers() {
        const q = document.getElementById('memberSearch').value.trim();
        const equbId = document.getElementById('equbSelect').value;
        const url = new URL(window.location.origin + '/admin/api/notifications.php');
        url.searchParams.set('action', 'search_members');
        if (q) url.searchParams.set('q', q);
        if (equbId) url.searchParams.set('equb_settings_id', equbId);
        try {
            const resp = await fetch(url.toString());
            const data = await resp.json();
            const res = document.getElementById('memberResults');
            res.innerHTML = '';
            if (data.success && Array.isArray(data.members)) {
                data.members.forEach(m => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-outline-secondary me-2 mb-2';
                    btn.textContent = `${m.first_name} ${m.last_name} (${m.code})`;
                    btn.addEventListener('click', () => addMemberChip(m.id, `${m.first_name} ${m.last_name}`));
                    res.appendChild(btn);
                });
            } else {
                res.textContent = 'No results';
            }
        } catch (e) { console.error(e); }
    }

    function addMemberChip(id, name) {
        const wrap = document.getElementById('selectedMembers');
        if (wrap.querySelector(`.chip[data-id="${id}"]`)) return;
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.dataset.id = id;
        chip.innerHTML = `${escapeHtml(name)} <i class="fas fa-times remove" title="Remove"></i>`;
        chip.querySelector('.remove').addEventListener('click', () => chip.remove());
        wrap.appendChild(chip);
    }

    function escapeHtml(s) { return (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

    async function openEdit(id) {
        try {
            const resp = await fetch(`${apiBase}?action=get&id=${id}`);
            const data = await resp.json();
            if (!data.success) return alert(data.message || 'Load failed');
            const n = data.notification;
            document.getElementById('editId').value = n.id;
            document.getElementById('editTitleEn').value = n.title_en || '';
            document.getElementById('editTitleAm').value = n.title_am || '';
            document.getElementById('editBodyEn').value = n.body_en || '';
            document.getElementById('editBodyAm').value = n.body_am || '';
            document.getElementById('editPriority').value = n.priority || 'normal';
            editModal.show();
        } catch (e) { alert('Network error'); }
    }

    async function saveEdit() {
        const form = document.getElementById('editForm');
        const fd = new FormData(form);
        try {
            const resp = await fetch(apiBase, { method: 'POST', body: fd });
            const data = await resp.json();
            if (data.success) {
                editModal.hide();
                loadNotifications();
                alert('Notification updated');
            } else {
                alert(data.message || 'Update failed');
            }
        } catch (e) { alert('Network error'); }
    }

    async function deleteNotification(id) {
        if (!confirm('Are you sure you want to delete this notification? This will remove it from members as well.')) return;
        const fd = new FormData();
        fd.append('action','delete');
        fd.append('csrf_token', csrfToken);
        fd.append('id', String(id));
        try {
            const resp = await fetch(apiBase, { method:'POST', body: fd });
            const data = await resp.json();
            if (data.success) {
                loadNotifications();
            } else {
                alert(data.message || 'Delete failed');
            }
        } catch (e) { alert('Network error'); }
    }
</script>
</body>
</html>
