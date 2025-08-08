<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../languages/translator.php';
?>
<style>
.member-footer {
  background: linear-gradient(180deg, var(--color-cream, #F1ECE2) 0%, #ffffff 100%);
  border-top: 2px solid rgba(218,165,32,0.25);
  padding: 18px 20px;
  font-size: 13px;
}
.footer-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  align-items: start;
}
.footer-brand {
  font-weight: 700;
  color: var(--color-deep-purple, #301934);
  display: flex;
  align-items: center;
  gap: 8px;
}
.footer-section-title {
  font-size: 11px;
  font-weight: 700;
  color: rgba(48,25,52,0.7);
  text-transform: uppercase;
  letter-spacing: .6px;
  margin-bottom: 6px;
}
.footer-links a {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  color: var(--color-deep-purple, #301934);
  text-decoration: none;
  line-height: 1.2;
}
.footer-links a:hover { color: var(--color-teal, #2A9D8F); }
.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid rgba(48,25,52,0.12);
  font-size: 12px;
  color: rgba(48,25,52,0.75);
}
@media (max-width: 768px) {
  .member-footer { padding: 14px 16px; }
  .footer-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
  .footer-section-title { margin-bottom: 4px; }
  .footer-links a { padding: 5px 0; font-size: 12.5px; }
  .footer-bottom { flex-direction: row; gap: 10px; align-items: center; flex-wrap: wrap; }
}
</style>
<footer class="member-footer">
  <div class="container-fluid footer-grid">
    <div>
      <div class="footer-brand">
        <i class="fas fa-hand-holding-heart" style="color: var(--color-gold, #DAA520);"></i>
        <span>HabeshaEqub</span>
      </div>
    </div>
    <div>
      <div class="footer-section-title"><?php echo t('footer.quick_links'); ?></div>
      <div class="footer-links">
        <a href="dashboard.php"><i class="fas fa-gauge"></i> <?php echo t('footer.dashboard'); ?></a>
        <a href="contributions.php"><i class="fas fa-file-invoice-dollar"></i> <?php echo t('footer.payments'); ?></a>
        <a href="payout-info.php"><i class="fas fa-money-bill-wave"></i> <?php echo t('footer.payout_info'); ?></a>
        <a href="position-swap.php"><i class="fas fa-exchange-alt"></i> <?php echo t('position_swap.page_title'); ?></a>
        <a href="notifications.php"><i class="fas fa-bell"></i> <?php echo t('member_nav.notifications'); ?></a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> <?php echo t('footer.settings'); ?></a>
      </div>
    </div>
    <div>
      <div class="footer-section-title"><?php echo t('footer.resources_support'); ?></div>
      <div class="footer-links">
        <a href="rules.php"><i class="fas fa-scroll"></i> <?php echo t('footer.rules'); ?></a>
        <a href="tel:07360436171"><i class="fas fa-headset"></i> <?php echo t('footer.contact_us'); ?></a>
        <a href="privacy-policy.php" target="_blank"><i class="fas fa-user-shield"></i> <?php echo t('footer.privacy_policy'); ?></a>
        <a href="terms-of-service.php" target="_blank"><i class="fas fa-file-contract"></i> <?php echo t('footer.terms_of_service'); ?></a>
      </div>
    </div>
  </div>
  <div class="container-fluid footer-bottom">
    <div>© <?php echo date('Y'); ?> HabeshaEqub · <?php echo t('footer.all_rights_reserved'); ?></div>
    <div style="display:flex; gap:12px; align-items:center;">
      <a href="notifications.php" style="text-decoration:none; color:inherit; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-bell" style="color: var(--color-gold, #DAA520);"></i>
        <span><?php echo t('member_nav.notifications'); ?></span>
      </a>
    </div>
  </div>
</footer>
