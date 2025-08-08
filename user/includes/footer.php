<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../languages/translator.php';
?>
<style>
.member-footer {
  background: #fff;
  border-top: 1px solid var(--border-light);
  box-shadow: 0 -6px 24px rgba(48,25,52,0.06);
  padding: 28px 24px;
}
.footer-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap: 24px;
}
.footer-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--text-primary);
  font-weight: 700;
}
.footer-section-title {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: .6px;
  margin-bottom: 10px;
}
.footer-links a { 
  display: flex; align-items:center; gap:10px; 
  padding: 8px 0; color: var(--text-primary); text-decoration: none; 
}
.footer-links a:hover { color: var(--color-teal); }
.footer-bottom {
  display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border-light);
  font-size: 13px; color: var(--text-secondary);
}
.footer-badges { display:flex; gap:8px; align-items:center; }
.footer-badge { background: var(--secondary-bg); border:1px solid var(--border-light); padding: 4px 8px; border-radius: 999px; font-size: 12px; }
@media (max-width: 992px) {
  .footer-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 576px) {
  .member-footer { padding: 20px 16px; }
  .footer-grid { grid-template-columns: 1fr; }
  .footer-bottom { flex-direction: column; gap: 8px; align-items:flex-start; }
}
</style>
<footer class="member-footer">
  <div class="footer-grid container-fluid">
    <div>
      <div class="footer-brand">
        <img src="../assets/img/logo.png" alt="Logo" style="height:28px;"/>
        <span>HabeshaEqub</span>
      </div>
      <div class="footer-badges" style="margin-top:10px;">
        <span class="footer-badge"><i class="fas fa-lock"></i> Secure</span>
        <span class="footer-badge"><i class="fas fa-mobile-alt"></i> Mobile</span>
        <span class="footer-badge"><i class="fas fa-language"></i> <?php echo strtoupper(getCurrentLanguage()); ?></span>
      </div>
    </div>
    <div>
      <div class="footer-section-title"><?php echo t('footer.quick_links'); ?></div>
      <div class="footer-links">
        <a href="dashboard.php"><i class="fas fa-gauge"></i> <?php echo t('footer.dashboard'); ?></a>
        <a href="contributions.php"><i class="fas fa-file-invoice-dollar"></i> <?php echo t('footer.payments'); ?></a>
        <a href="payout-info.php"><i class="fas fa-money-bill-wave"></i> <?php echo t('footer.payout_info'); ?></a>
        <a href="position-swap.php"><i class="fas fa-exchange-alt"></i> <?php echo t('footer.position_swap'); ?></a>
        <a href="notifications.php"><i class="fas fa-bell"></i> <?php echo t('footer.notifications'); ?></a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> <?php echo t('footer.settings'); ?></a>
      </div>
    </div>
    <div>
      <div class="footer-section-title"><?php echo t('footer.resources'); ?></div>
      <div class="footer-links">
        <a href="../rules.php"><i class="fas fa-scroll"></i> <?php echo t('footer.equb_rules'); ?></a>
        <a href="tel:07360436171"><i class="fas fa-circle-question"></i> <?php echo t('footer.help_center'); ?></a>
        <a href="tel:07360436171"><i class="fas fa-headset"></i> <?php echo t('footer.contact_support'); ?></a>
        <a href="../privacy-policy.php" target="_blank"><i class="fas fa-user-shield"></i> <?php echo t('footer.privacy'); ?></a>
        <a href="../terms-of-service.php" target="_blank"><i class="fas fa-file-contract"></i> <?php echo t('footer.terms'); ?></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom container-fluid">
    <div>© <?php echo date('Y'); ?> HabeshaEqub · <?php echo t('footer.copyright'); ?></div>
    <div>
      <a href="notifications.php" class="footer-links" style="gap:10px;">
        <span style="display:flex; align-items:center; gap:8px; text-decoration:none;"><i class="fas fa-bell text-warning"></i> <?php echo t('member_nav.notifications'); ?></span>
      </a>
    </div>
  </div>
</footer>
