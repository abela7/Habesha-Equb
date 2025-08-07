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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" crossorigin="anonymous" />
    <link href="../assets/css/style.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
    <style>
        .notif-header { background: linear-gradient(135deg, var(--color-cream), #fff); border-radius: 16px; border:1px solid var(--border-light); padding: 20px; box-shadow: 0 8px 24px rgba(48,25,52,0.06); }
        .notif-actions { display:flex; gap:10px; flex-wrap:wrap; }
        .notif-card { border:1px solid var(--border-light); border-radius: 16px; padding: 16px; background: #fff; box-shadow: 0 4px 16px rgba(48,25,52,0.06); display:flex; gap:16px; align-items:flex-start; }
        .notif-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0; box-shadow: 0 8px 20px rgba(42,157,143,0.25); background: linear-gradient(135deg, var(--color-teal), #0F766E); }
        .notif-meta { font-size: 12px; color: var(--text-secondary); }
        .notif-title { font-weight:700; color: var(--text-primary); margin:0; }
        .notif-body { color: var(--text-primary); margin: 8px 0 0; white-space: pre-wrap; }
        .notif-unread { border-left: 4px solid var(--color-gold); }
        .badge-unread { background: var(--color-coral); color:#fff; border-radius: 999px; font-size: 11px; padding:4px 8px; }
        .priority-high { background: linear-gradient(135deg, var(--color-coral), #e76f51); color:#fff; border-radius:8px; padding:2px 8px; font-size:11px; font-weight:700; }
        .list-wrap { display:grid; gap:12px; }
        @media (max-width: 768px) { .notif-card { padding:14px; border-radius:14px; } }
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
                    <button id="btnMarkAll" class="btn btn-sm btn-outline-primary"><i class="fas fa-check-double me-1"></i><?php echo t('common.mark_all_read') ?: 'Mark all as read'; ?></button>
                    <button id="btnRefresh" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate me-1"></i><?php echo t('common.refresh') ?: 'Refresh'; ?></button>
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

<script>
const apiBase = 'api/notifications.php';

function currentLang() { return '<?php echo $lang; ?>'; }

function renderNotifications(items) {
    const wrap = document.getElementById('notificationsList');
    wrap.innerHTML = '';
    if (!Array.isArray(items) || !items.length) {
        wrap.innerHTML = `<div class="alert alert-info"><i class="fas fa-info-circle me-1"></i><?php echo t('common.no_data') ?: 'No notifications yet.'; ?></div>`;
        return;
    }
    items.forEach(n => {
        const isUnread = String(n.read_flag) === '0';
        const title = currentLang()==='am' ? (n.title_am || n.title_en) : (n.title_en || n.title_am);
        const body = currentLang()==='am' ? (n.body_am || n.body_en) : (n.body_en || n.body_am);
        const created = n.sent_at || n.created_at || '';
        const el = document.createElement('div');
        el.className = 'notif-card ' + (isUnread ? 'notif-unread':'' );
        el.innerHTML = `
            <div class="notif-icon"><i class="fas fa-bell"></i></div>
            <div class="flex-fill">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="notif-title">${escapeHtml(title)}</h5>
                    ${n.priority==='high' ? '<span class="priority-high">HIGH</span>' : ''}
                    ${isUnread ? '<span class="badge-unread"><?php echo t('common.unread') ?: 'Unread'; ?></span>' : ''}
                </div>
                <div class="notif-meta">${created ? escapeHtml(created) : ''}</div>
                <div class="notif-body">${escapeHtml(body)}</div>
                ${isUnread ? `<div class="mt-2">
                    <button class="btn btn-sm btn-primary" onclick="markRead(${Number(n.notification_id)})"><i class="fas fa-check me-1"></i><?php echo t('common.mark_read') ?: 'Mark as read'; ?></button>
                </div>`: ''}
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
        document.getElementById('notificationsList').innerHTML = `<div class="alert alert-danger">${escapeHtml(e.message)}</div>`;
    }
}

async function markRead(id) {
    try {
        const fd = new FormData();
        fd.append('action','mark_read');
        fd.append('notification_id', String(id));
        const r = await fetch(apiBase, { method:'POST', body: fd });
        const data = await r.json();
        if (data.success) {
            await loadNotifications();
        }
    } catch (e) { console.error(e); }
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
        if (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        if (bell) {
            bell.classList.toggle('text-warning', count > 0);
        }
    } catch (e) { console.error(e); }
}

function escapeHtml(s){return (s||'').replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btnRefresh').addEventListener('click', loadNotifications);
    document.getElementById('btnMarkAll').addEventListener('click', markAll);
    loadNotifications();
});
</script>
</body>
</html>
