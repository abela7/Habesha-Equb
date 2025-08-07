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
                                    <th></th>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const apiBase = 'api/notifications.php';
    const csrfToken = '<?php echo htmlspecialchars($csrf_token); ?>';

    document.addEventListener('DOMContentLoaded', () => {
        initAudienceControls();
        loadEqubTerms();
        loadNotifications();
        document.getElementById('btnRefresh').addEventListener('click', loadNotifications);
        document.getElementById('notificationForm').addEventListener('submit', onSubmit);
        document.getElementById('btnSearchMembers').addEventListener('click', searchMembers);
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
                        <td><button class="btn btn-sm btn-outline-primary" onclick="viewNotification(${n.id})"><i class="fas fa-eye"></i></button></td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function onSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        // collect selected members
        const memberIds = Array.from(document.querySelectorAll('#selectedMembers .chip')).map(ch => ch.dataset.id);
        if (memberIds.length) formData.set('member_ids', JSON.stringify(memberIds));
        try {
            const resp = await fetch(apiBase, { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) {
                alert('Notification sent successfully');
                form.reset();
                document.getElementById('selectedMembers').innerHTML = '';
                loadNotifications();
            } else {
                alert(data.message || 'Failed to send');
            }
        } catch (e) {
            alert('Network error');
        }
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
        if (wrap.querySelector(`.chip[data-id="${id}"]`)) return; // avoid duplicates
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.dataset.id = id;
        chip.innerHTML = `${escapeHtml(name)} <i class="fas fa-times remove" title="Remove"></i>`;
        chip.querySelector('.remove').addEventListener('click', () => chip.remove());
        wrap.appendChild(chip);
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    async function viewNotification(id) {
        try {
            const resp = await fetch(`${apiBase}?action=get&id=${id}`);
            const data = await resp.json();
            if (data.success) {
                const n = data.notification;
                const rec = data.recipients || [];
                const list = rec.map(r => `${escapeHtml(r.first_name)} ${escapeHtml(r.last_name)} (${r.member_code || ''})`).join('\n');
                alert(`${n.notification_code}\n\nEN: ${n.title_en}\n${n.body_en}\n\nAM: ${n.title_am}\n${n.body_am}\n\nRecipients (${rec.length}):\n${list}`);
            } else {
                alert(data.message || 'Failed to load');
            }
        } catch (e) {
            alert('Network error');
        }
    }
</script>
</body>
</html>
