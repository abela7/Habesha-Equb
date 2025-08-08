<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../languages/translator.php';
?>
<style>
.member-footer {
  margin: 0;
  width: 100%;
  background: linear-gradient(180deg, #2a2031 0%, #1e1726 100%);
  color: rgba(255,255,255,0.9);
  border-top: 1px solid rgba(255,255,255,0.12);
  padding: 10px 16px;
  font-size: 12px;
  box-sizing: border-box;
  clear: both;
}
.footer-row { display:flex; justify-content: space-between; align-items:center; gap:12px; flex-wrap:wrap; }
.footer-links-inline { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
.footer-links-inline a { color: rgba(255,255,255,0.9); text-decoration:none; }
.footer-links-inline a:hover { color:#DAA520; }
.footer-links-inline .sep { opacity:.4; }
.footer-legal { color: rgba(255,255,255,0.75); }
@media (max-width: 576px) {
  .member-footer { padding: 8px 12px; }
}
</style>
<footer class="member-footer">
  <div class="footer-row">
    <nav class="footer-links-inline">
      <a href="dashboard.php"><?php echo t('footer.dashboard'); ?></a>
      <span class="sep">•</span>
      <a href="contributions.php"><?php echo t('footer.payments'); ?></a>
      <span class="sep">•</span>
      <a href="payout-info.php"><?php echo t('footer.payout_info'); ?></a>
      <span class="sep">•</span>
      <a href="settings.php"><?php echo t('footer.settings'); ?></a>
    </nav>
    <div class="footer-legal">© <?php echo date('Y'); ?> HabeshaEqub · <?php echo t('footer.all_rights_reserved'); ?></div>
  </div>
</footer>
