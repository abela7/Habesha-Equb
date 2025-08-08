<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../languages/translator.php';
?>
<style>
.member-footer {
  margin: 0; /* no space around */
  width: 100%; /* respect layout to avoid side-nav overlap */
  background: linear-gradient(180deg, #2a2031 0%, #1e1726 100%);
  color: rgba(255,255,255,0.9);
  border-top: 1px solid rgba(255,255,255,0.12);
  padding: 10px 12px; /* compact height */
  font-size: 12px;
  box-sizing: border-box;
  clear: both;
}
.footer-inner { width: 100%; }
.footer-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}
.footer-links-row { display:flex; flex-wrap:wrap; gap:14px; align-items:center; }
.footer-links-row a { color: rgba(255,255,255,0.9); text-decoration:none; }
.footer-links-row a:hover { color:#DAA520; }
.footer-section-title {
  font-size: 10px;
  font-weight: 700;
  color: rgba(255,255,255,0.7);
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 4px;
}
.footer-links a {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 0;
  color: rgba(255,255,255,0.9);
  text-decoration: none;
  line-height: 1.2;
}
.footer-links a i { color: #DAA520; }
.footer-links a:hover { color: #DAA520; }
.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(255,255,255,0.12);
  color: rgba(255,255,255,0.75);
}
@media (max-width: 576px) {
  .member-footer { padding: 8px 0; }
  .footer-inner { padding: 0 6px; }
  .footer-links a { padding: 3px 0; font-size: 12px; }
}
</style>
<footer class="member-footer">
  <div class="footer-inner">
    <div class="footer-grid">
    <div>
      <div class="footer-section-title">HabeshaEqub</div>
      <div class="footer-links">
        <a href="notifications.php"><i class="fas fa-bell"></i> <?php echo t('member_nav.notifications'); ?></a>
      </div>
    </div>
    <div>
      <div class="footer-section-title"><?php echo t('footer.quick_links'); ?></div>
      <div class="footer-links-row">
        <a href="dashboard.php"><?php echo t('footer.dashboard'); ?></a>
        <a href="contributions.php"><?php echo t('footer.payments'); ?></a>
        <a href="payout-info.php"><?php echo t('footer.payout_info'); ?></a>
        <a href="settings.php"><?php echo t('footer.settings'); ?></a>
      </div>
    </div>
    <div>
      <div class="footer-section-title"><?php echo t('footer.resources_support'); ?></div>
      <div class="footer-links-row">
        <a href="rules.php"><?php echo t('footer.rules'); ?></a>
        <a href="privacy-policy.php" target="_blank"><?php echo t('footer.privacy_policy'); ?></a>
        <a href="terms-of-service.php" target="_blank"><?php echo t('footer.terms_of_service'); ?></a>
        <a href="tel:07360436171"><?php echo t('footer.contact_us'); ?></a>
      </div>
    </div>
    </div>
    <div class="footer-bottom">
      <div>© <?php echo date('Y'); ?> HabeshaEqub · <?php echo t('footer.all_rights_reserved'); ?></div>
      <div></div>
    </div>
  </div>
</footer>
