<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/auth_guard.php';
require_once '../includes/db.php';
require_once '../languages/translator.php';

$user_id = get_current_user_id();
if (!$user_id) { header('Location: login.php'); exit; }
$lang = getCurrentLanguage();
$cache_buster = time().'_'.rand(1000,9999);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo t('member_nav.notifications') ?: 'Notifications'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
  <link href="../assets/css/style.css?v=<?php echo $cache_buster; ?>" rel="stylesheet" />
  <style>
    .page-wrap { padding: 16px; }
    .notif-list { display: grid; gap: 10px; }
    .notif-card { background: #fff; border:1px solid var(--border-light); border-radius: 14px; padding: 14px; display:flex; gap:12px; align-items:flex-start; }
    .notif-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; background: var(--color-teal); color:#fff; flex-shrink:0; }
    .notif-title { font-weight:700; margin:0; color: var(--text-primary); }
    .notif-meta { font-size:12px; color: var(--text-secondary); }
    .notif-body { margin-top:6px; white-space: pre-wrap; color: var(--text-primary); }
    .notif-unread { border-left:4px solid var(--color-gold); }
    .chip { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; background: var(--secondary-bg); border-radius:999px; font-size:11px; font-weight:600; }
    @media (max-width: 576px) { .page-wrap { padding: 12px; } .notif-card { padding: 12px; } }
  </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>

<div class="container-fluid page-wrap">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="m-0" style="font-weight:700; color:var(--text-primary)"><i class="fas fa-bell text-warning"></i> <?php echo t('member_nav.notifications') ?: 'Notifications'; ?></h2>
    <div class="d-none d-sm-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary" id="btnRefresh"><i class="fas fa-rotate"></i></button>
      <button class="btn btn-sm btn-outline-primary" id="btnMarkAll"><?php echo t('common.mark_all_read') ?: 'Mark all as read'; ?></button>
    </div>
  </div>
  <div class="d-flex d-sm-none gap-2 mb-2">
    <button class="btn btn-outline-secondary w-50" id="btnRefreshMobile"><i class="fas fa-rotate me-2"></i><?php echo t('common.refresh') ?: 'Refresh'; ?></button>
    <button class="btn btn-outline-primary w-50" id="btnMarkAllMobile"><i class="fas fa-check-double me-2"></i><?php echo t('common.mark_all_read') ?: 'Mark all as read'; ?></button>
  </div>
  <div id="list" class="notif-list"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="mTitle"></h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body"><div id="mMeta" class="text-muted mb-2" style="font-size:12px"></div><div id="mBody"></div></div>
    </div>
  </div>
  </div>

<script>
const api = 'api/notifications.php';
let modal, modalEl;
function currentLang(){ return '<?php echo $lang; ?>'; }

function esc(s){ return (s||'').replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); }

function linkify(text){
  const urlRegex = /(https?:\/\/[\w\-._~:/?#[\]@!$&'()*+,;=%]+)/gi;
  return esc(text).replace(urlRegex, (url)=>`<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`);
}

function render(items){
  const wrap = document.getElementById('list');
  wrap.innerHTML = '';
  if (!Array.isArray(items) || !items.length){
    wrap.innerHTML = '<div class="alert alert-info"><?php echo t('common.no_data') ?: 'No notifications'; ?></div>';
    return;
  }
  items.forEach(n=>{
    const isUnread = String(n.read_flag) === '0';
    const title = currentLang()==='am' ? (n.title_am || n.title_en) : (n.title_en || n.title_am);
    const body = currentLang()==='am' ? (n.body_am || n.body_en) : (n.body_en || n.body_am);
    const created = n.sent_at || n.created_at || '';
    const card = document.createElement('div');
    card.className = 'notif-card ' + (isUnread ? 'notif-unread' : '');
    card.innerHTML = `
      <div class="notif-icon"><i class="fas fa-bell"></i></div>
      <div class="flex-fill">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h5 class="notif-title m-0">${esc(title)}</h5>
          ${n.priority==='high' ? '<span class="chip text-danger">High</span>' : ''}
          ${isUnread ? '<span class="chip" style="background:#FFE8E0;color:#C2410C">Unread</span>' : ''}
        </div>
        <div class="notif-meta">${esc(created)}</div>
        <div class="notif-body" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;white-space:normal;">${linkify(body)}</div>
      </div>`;
    card.addEventListener('click', ()=> openNotif(n, isUnread, card));
    wrap.appendChild(card);
  });
}

async function loadList(){
  const r = await fetch(`${api}?action=list`, { cache: 'no-store' });
  const d = await r.json();
  if (d && d.success){ render(d.notifications || []); refreshTopBadge(); }
}

function ensureModal(){ if (!modal){ modalEl = document.getElementById('notifModal'); if (window.bootstrap) modal = new bootstrap.Modal(modalEl); } }

async function markRead(id){
  const fd = new FormData(); fd.append('action','mark_read'); fd.append('notification_id', String(id));
  const r = await fetch(api, { method:'POST', body: fd });
  return !!(await r.json()).success;
}

async function markAll(){
  const fd = new FormData(); fd.append('action','mark_all_read');
  await fetch(api, { method:'POST', body: fd });
  await loadList();
}

async function refreshTopBadge(){
  try{
    const r = await fetch(`${api}?action=count_unread`, { cache:'no-store' });
    const d = await r.json();
    const c = d && d.success ? Number(d.unread) : 0;
    const topBadge = document.getElementById('memberNotifBadgeTop');
    if (topBadge){
      topBadge.textContent = c > 99 ? '99+' : String(c);
      topBadge.style.display = c > 0 ? 'inline-flex' : 'none';
    }
  }catch(e){}
}

async function openNotif(n, wasUnread, cardEl){
  ensureModal();
  const t = currentLang()==='am' ? (n.title_am || n.title_en) : (n.title_en || n.title_am);
  const b = currentLang()==='am' ? (n.body_am || n.body_en) : (n.body_en || n.body_am);
  const created = n.sent_at || n.created_at || '';
  document.getElementById('mTitle').textContent = t;
  document.getElementById('mBody').textContent = b;
  document.getElementById('mMeta').textContent = created;
  if (modal) modal.show();
  if (wasUnread){
    const ok = await markRead(n.notification_id);
    if (ok && cardEl){
      cardEl.classList.remove('notif-unread');
      const pill = cardEl.querySelector('.chip'); if (pill) pill.remove();
      // Immediately update FAB and top badges via events
      try { window.dispatchEvent(new CustomEvent('notifications-updated')); } catch(e){}
    }
    refreshTopBadge();
  }
}

document.addEventListener('DOMContentLoaded', ()=>{
  document.getElementById('btnRefresh').addEventListener('click', loadList);
  document.getElementById('btnMarkAll').addEventListener('click', markAll);
  const rM = document.getElementById('btnRefreshMobile'); if (rM) rM.addEventListener('click', loadList);
  const mM = document.getElementById('btnMarkAllMobile'); if (mM) mM.addEventListener('click', markAll);
  loadList();
  setInterval(loadList, 60000);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?v=<?php echo $cache_buster; ?>"></script>
</body>
</html>


