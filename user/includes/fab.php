<style>
:root {
  --fab-bg: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
  --fab-icon: #ffffff;
  --fab-item-bg: #ffffff;
  --fab-item-text: #301934;
  --fab-item-icon: #DAA520;
}
.fab-container { position: fixed; right: 18px; bottom: 18px; z-index: 12000; pointer-events: auto; }
.fab-button {
  width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer;
  background: var(--fab-bg);
  color: var(--fab-icon); box-shadow: 0 8px 24px rgba(48,25,52,.2); display: flex; align-items: center; justify-content: center; position: relative;
  transition: transform .25s ease, box-shadow .25s ease;
}
.fab-button:focus { outline: none; box-shadow: 0 0 0 4px rgba(218,165,32,.25); }
.fab-button:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(48,25,52,.28); }
.fab-icon { font-size: 22px; transition: transform .25s ease; }
.fab-open .fab-icon { transform: rotate(45deg); }
.fab-attention .fab-button { animation: fabPulse 2.2s ease-out infinite; }
@keyframes fabPulse { 0% { box-shadow: 0 0 0 0 rgba(231,111,81,.55); transform: scale(1);} 70% { box-shadow: 0 0 0 14px rgba(231,111,81,0); transform: scale(1.03);} 100% { box-shadow: 0 0 0 0 rgba(231,111,81,0);} }
.fab-badge { position:absolute; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background:#E76F51; color:#fff; font-size: 11px; font-weight: 700; display:none; align-items:center; justify-content:center; top:-4px; right:-4px; border:2px solid #fff; line-height: 18px; }

.fab-menu { position: absolute; right: 0; bottom: 72px; display: none; flex-direction: column; align-items: flex-end; gap: 10px; }
.fab-open .fab-menu { display: flex; }
.fab-item { background: var(--fab-item-bg); color: var(--fab-item-text); border: 1px solid rgba(0,0,0,0.06); border-radius: 14px; 
  box-shadow: 0 8px 24px rgba(48,25,52,.12); padding: 8px 12px; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
.fab-item i { color: var(--fab-item-icon); }
.fab-item:hover { text-decoration: none; border-color: rgba(218,165,32,.35); box-shadow: 0 10px 28px rgba(48,25,52,.18); }
.fab-item .label { font-size: 13px; font-weight: 600; }
@media (max-width: 576px) {
  .fab-item { padding: 8px 10px; }
  .fab-item .label { display: none; }
}
</style>

<div class="fab-container" id="quickFab">
  <div class="fab-menu" id="quickFabMenu" aria-hidden="true">
    <a href="dashboard.php" class="fab-item" title="<?php echo t('member_nav.dashboard'); ?>">
      <i class="fas fa-gauge"></i><span class="label"><?php echo t('member_nav.dashboard'); ?></span>
    </a>
    <a href="contributions.php" class="fab-item" title="<?php echo t('footer.payments'); ?>">
      <i class="fas fa-wallet"></i><span class="label"><?php echo t('footer.payments'); ?></span>
    </a>
    <a href="payout-info.php" class="fab-item" title="<?php echo t('footer.payout_info'); ?>">
      <i class="fas fa-sack-dollar"></i><span class="label"><?php echo t('footer.payout_info'); ?></span>
    </a>
    <a href="notifications.php" class="fab-item" title="<?php echo t('member_nav.notifications'); ?>">
      <i class="fas fa-bell"></i><span class="label"><?php echo t('member_nav.notifications'); ?></span>
    </a>
    <a href="settings.php" class="fab-item" title="<?php echo t('footer.settings'); ?>">
      <i class="fas fa-gear"></i><span class="label"><?php echo t('footer.settings'); ?></span>
    </a>
  </div>
  <button class="fab-button" id="quickFabToggle" aria-controls="quickFabMenu" aria-expanded="false" aria-label="Quick menu">
    <span class="fab-badge" id="fabUnreadBadge">0</span>
    <i class="fas fa-plus fab-icon"></i>
  </button>
  </div>

<script>
(function(){
  const container = document.getElementById('quickFab');
  if (!container) return;
  const toggleBtn = document.getElementById('quickFabToggle');
  const menu = document.getElementById('quickFabMenu');

  async function setUnreadDot(count){
    const bell = menu ? menu.querySelector('a[href="notifications.php"] i') : null;
    const fabBadge = document.getElementById('fabUnreadBadge');
    if (count > 0) {
      if (bell) { bell.classList.add('fa-shake'); bell.style.setProperty('color','#E76F51','important'); }
      if (fabBadge) { fabBadge.style.display = 'inline-flex'; fabBadge.textContent = count > 99 ? '99+' : String(count); }
      container.classList.add('fab-attention');
    } else {
      if (bell) { bell.classList.remove('fa-shake'); bell.style.removeProperty('color'); }
      if (fabBadge) { fabBadge.style.display = 'none'; fabBadge.textContent = '0'; }
      container.classList.remove('fab-attention');
    }
  }

  async function loadUnread(){
    try {
      const r = await fetch('api/notifications.php?action=count_unread');
      const d = await r.json();
      const u = d && d.success ? Number(d.unread) : 0;
      setUnreadDot(u);
    } catch(e) { /* silent */ }
  }

  function closeMenu(){ if(!container||!toggleBtn||!menu) return; container.classList.remove('fab-open'); toggleBtn.setAttribute('aria-expanded','false'); menu.setAttribute('aria-hidden','true'); }
  function openMenu(){ if(!container||!toggleBtn||!menu) return; container.classList.add('fab-open'); toggleBtn.setAttribute('aria-expanded','true'); menu.setAttribute('aria-hidden','false'); }

  let justToggled = false;
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function(e){
      e.stopPropagation();
      if (container.classList.contains('fab-open')) { closeMenu(); } else { openMenu(); }
      justToggled = true; setTimeout(()=>{ justToggled = false; }, 150);
    }, true);
  }
  document.addEventListener('click', function(e){ if (justToggled) return; if (container && !container.contains(e.target)) closeMenu(); }, true);
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });
  if (menu) { menu.querySelectorAll('a').forEach(a=>a.addEventListener('click', closeMenu)); }

  // Initial unread and periodic refresh
  loadUnread();
  setInterval(loadUnread, 60000);
})();
</script>


