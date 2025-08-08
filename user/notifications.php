<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth_guard.php';
require_once '../includes/db.php';
require_once '../languages/translator.php';

$user_id = get_current_user_id();
if (!$user_id) {
    header('Location: login.php');
    exit;
}

$cache_buster = time() . '_' . rand(1000,9999);
$lang = getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo t('member_nav.notifications') ?: 'Notifications'; ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32x32.png" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" crossorigin="anonymous" />
    <link href="../assets/css/style.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
    <style>
        .notif-header { background: linear-gradient(135deg, var(--color-cream), #fff); border-radius: 16px; border:1px solid var(--border-light); padding: 20px; box-shadow: 0 8px 24px rgba(48,25,52,0.06); }
        .notif-actions { display:flex; gap:10px; flex-wrap:wrap; }
        .notif-card { border:1px solid var(--border-light); border-radius: 16px; padding: 16px; background: #fff; box-shadow: 0 4px 16px rgba(48,25,52,0.06); display:flex; gap:16px; align-items:flex-start; cursor: pointer; transition: transform .15s ease, box-shadow .2s ease; }
        .notif-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(48,25,52,0.12); }
        .notif-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0; box-shadow: 0 8px 20px rgba(42,157,143,0.25); background: linear-gradient(135deg, var(--color-teal), #0F766E); }
        .notif-meta { font-size: 12px; color: var(--text-secondary); }
        .notif-title { font-weight:700; color: var(--text-primary); margin:0; word-break: break-word; overflow-wrap: anywhere; }
        .notif-body { color: var(--text-primary); margin: 8px 0 0; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; }
        .notif-unread { border-left: 4px solid var(--color-gold); }
        .badge-unread { background: var(--color-coral); color:#fff; border-radius: 999px; font-size: 11px; padding:4px 8px; }
        .priority-high { background: linear-gradient(135deg, var(--color-coral), #e76f51); color:#fff; border-radius:8px; padding:2px 8px; font-size:11px; font-weight:700; }
        .list-wrap { display:grid; gap:12px; }
        /* Prevent flex children from overflowing the card */
        .notif-card .flex-fill { min-width: 0; }
        /* Allow the title/badges row to wrap on small screens */
        .notif-card .d-flex.align-items-center { flex-wrap: wrap; }
        /* Two-line clamped preview inside list */
        .notif-preview { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; white-space: normal; }
        /* Modal styling */
        .modal-content { border-radius: 16px; border: 1px solid var(--border-light); }
        .modal-header { border-bottom: 1px solid var(--border-light); background: linear-gradient(135deg, var(--color-cream), #fff); }
        .modal-title { font-weight: 700; color: var(--text-primary); }
        .modal-body { color: var(--text-primary); font-size: 15px; line-height: 1.6; }
        .modal-priority { font-size: 12px; margin-left: 8px; }
        /* Responsive: stack header and icons-only on mobile */
        @media (max-width: 576px) {
            .notif-header { display:flex; flex-direction: column; align-items: flex-start; }
            .notif-actions { margin-top: 10px; }
            .btn-label { display: none; }
        }
        @media (max-width: 768px) { 
            .notif-card { padding:14px; border-radius:14px; }
            .modal-body { font-size: 16px; }
            .modal-title { font-size: 18px; }
        }
        @media (max-width: 480px) {
            .modal-dialog { margin: 12px; }
            .modal-title { font-size: 17px; }
            .modal-body { font-size: 15px; }
        }
    </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="notif-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-bell text-warning"></i>
                    <h2 class="m-0" style="font-weight:700; color:var(--text-primary)"><?php echo t('member_nav.notifications') ?: 'Notifications'; ?></h2>
                </div>
                <div class="notif-actions">
                    <button id="btnMarkAll" class="btn btn-sm btn-outline-primary" title="<?php echo t('common.mark_all_read') ?: 'Mark all as read'; ?>">
                        <i class="fas fa-check-double"></i>
                        <span class="btn-label ms-1"><?php echo t('common.mark_all_read') ?: 'Mark all as read'; ?></span>
                    </button>
                    <button id="btnRefresh" class="btn btn-sm btn-outline-secondary" title="<?php echo t('common.refresh') ?: 'Refresh'; ?>">
                        <i class="fas fa-rotate"></i>
                        <span class="btn-label ms-1"><?php echo t('common.refresh') ?: 'Refresh'; ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div id="notificationsList" class="list-wrap"></div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center" id="notifModalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="notifModalMeta" class="text-muted mb-2" style="font-size:12px"></div>
        <div id="notifModalBody"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('common.close') ?: 'Close'; ?></button>
      </div>
    </div>
  </div>
</div>

<script>
const apiBase = 'api/notifications.php';
let notifModal, notifModalEl;

function currentLang() { return '<?php echo $lang; ?>'; }

function renderNotifications(items) {
    const wrap = document.getElementById('notificationsList');
    wrap.innerHTML = '';
    if (!Array.isArray(items) || !items.length) {
        wrap.innerHTML = `<div class=\"alert alert-info\"><i class=\"fas fa-info-circle me-1\"></i><?php echo t('notifications.no_notifications') ?: (t('common.no_data') ?: 'No notifications yet.'); ?></div>`;
        return;
    }
    items.forEach(n => {
        const isUnread = String(n.read_flag) === '0';
        const title = currentLang()==='am' ? (n.title_am || n.title_en) : (n.title_en || n.title_am);
        const body = currentLang()==='am' ? (n.body_am || n.body_en) : (n.body_en || n.body_am);
        const created = n.sent_at || n.created_at || '';
        const el = document.createElement('div');
        el.className = 'notif-card ' + (isUnread ? 'notif-unread':'' );
        el.setAttribute('role','button');
        el.setAttribute('tabindex','0');
        el.addEventListener('click', () => openNotification({
            id: Number(n.notification_id),
            title,
            body,
            priority: n.priority,
            created
        }, isUnread, el));
        el.innerHTML = `
            <div class=\"notif-icon\"><i class=\"fas fa-bell\"></i></div>
            <div class=\"flex-fill\">
                <div class=\"d-flex align-items-center gap-2\">
                    <h5 class=\"notif-title\">${escapeHtml(title)}</h5>
                    ${n.priority==='high' ? '<span class=\"priority-high modal-priority\"><?php echo t('notifications.high') ?: 'High'; ?></span>' : ''}
                    ${isUnread ? '<span class=\"badge-unread\"><?php echo t('common.unread') ?: 'Unread'; ?></span>' : ''}
                </div>
                <div class=\"notif-meta\">${created ? escapeHtml(created) : ''}</div>
                <div class=\"notif-body notif-preview\">${escapeHtml(body)}</div>
            </div>
        `;
        wrap.appendChild(el);
    });
}

async function loadNotifications() {
    try {
        const r = await fetch(`${apiBase}?action=list`);
        const data = await r.json();
        if (data.success) {
            renderNotifications(data.notifications);
            updateUnreadBadge();
        } else {
            throw new Error(data.message || 'Failed to load');
        }
    } catch (e) {
        console.error(e);
        document.getElementById('notificationsList').innerHTML = `<div class=\"alert alert-danger\">${escapeHtml(e.message)}</div>`;
    }
}

async function markRead(id) {
    try {
        const fd = new FormData();
        fd.append('action','mark_read');
        fd.append('notification_id', String(id));
        const r = await fetch(apiBase, { method:'POST', body: fd });
        const data = await r.json();
        return !!data.success;
    } catch (e) { console.error(e); return false; }
}

async function markAll() {
    try {
        const fd = new FormData();
        fd.append('action','mark_all_read');
        const r = await fetch(apiBase, { method:'POST', body: fd });
        await loadNotifications();
    } catch (e) { console.error(e); }
}

async function updateUnreadBadge() {
    try {
        const r = await fetch(`${apiBase}?action=count_unread`);
        const data = await r.json();
        const count = data.success ? Number(data.unread) : 0;
        const badge = document.getElementById('memberNotifBadge');
        const bell = document.getElementById('memberNotifBell');
        const badgeTop = document.getElementById('memberNotifBadgeTop');
        if (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        if (badgeTop) {
            badgeTop.textContent = count > 99 ? '99+' : String(count);
            badgeTop.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        if (bell) {
            bell.classList.toggle('text-warning', count > 0);
        }
    } catch (e) { console.error(e); }
}

function escapeHtml(s){return (s||'').replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c]));}

function initModal() {
    notifModalEl = document.getElementById('notifModal');
    if (window.bootstrap && notifModalEl) {
        notifModal = new bootstrap.Modal(notifModalEl);
    }
}

async function openNotification(n, wasUnread, cardEl) {
    // Fill modal content
    const titleEl = document.getElementById('notifModalTitle');
    const bodyEl = document.getElementById('notifModalBody');
    const metaEl = document.getElementById('notifModalMeta');
    titleEl.innerHTML = `${escapeHtml(n.title)} ${n.priority==='high' ? '<span class=\'priority-high modal-priority\'>HIGH</span>' : ''}`;
    bodyEl.textContent = n.body || '';
    metaEl.textContent = n.created || '';

    if (!notifModal) initModal();
    if (notifModal) notifModal.show();

    // Mark as read when opened
    if (wasUnread) {
        const ok = await markRead(n.id);
        if (ok) {
            // Update visuals inline without full reload
            if (cardEl) {
                cardEl.classList.remove('notif-unread');
                const pill = cardEl.querySelector('.badge-unread');
                if (pill) pill.remove();
            }
            updateUnreadBadge();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btnRefresh').addEventListener('click', loadNotifications);
    document.getElementById('btnMarkAll').addEventListener('click', markAll);
    initModal();
    loadNotifications();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
</body>
</html>
