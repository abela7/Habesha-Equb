<?php
/**
 * HabeshaEqub - PWA Head Tags
 * Include this file in the <head> section of all pages
 */

// Determine if we're in admin or user section
$isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
$isUser = strpos($_SERVER['REQUEST_URI'], '/user/') !== false;
$basePath = $isAdmin || $isUser ? '../' : '';
?>
<!-- PWA Meta Tags -->
<meta name="theme-color" content="#4D4052">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="HabeshaEqub">

<!-- PWA Manifest -->
<link rel="manifest" href="<?php echo $basePath; ?>manifest.json">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-180x180.png">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $basePath; ?>Pictures/Icon/apple-icon-57x57.png">

<!-- PWA Styles -->
<link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/pwa.css">

<!-- PWA Manager Script (loaded at end of body) -->
<script>
// PWA configuration
window.PWA_CONFIG = {
  updateCheckInterval: 1800000, // 30 minutes
  cacheVersion: '1.0.0'
};
</script>

