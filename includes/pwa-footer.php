<?php
/**
 * HabeshaEqub - PWA Footer Scripts
 * Include this file before closing </body> tag
 */

// Determine if we're in admin or user section
$isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
$isUser = strpos($_SERVER['REQUEST_URI'], '/user/') !== false;
$basePath = $isAdmin || $isUser ? '../' : '';
?>
<!-- PWA Manager Script -->
<script src="<?php echo $basePath; ?>assets/js/pwa-manager.js"></script>

