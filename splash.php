<?php
/**
 * HabeshaEqub - PWA Splash Screen
 * Shows the page loader instead of logo when app launches
 */

require_once 'includes/db.php';
require_once 'languages/translator.php';

// Set default language
if (!isset($_SESSION['app_language'])) {
    setLanguage('am');
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabeshaEqub</title>
    
    <!-- PWA Support -->
    <?php include 'includes/pwa-head.php'; ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #F1ECE2 0%, #FAF8F5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Preloader Container - Full Screen */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #F1ECE2 0%, #FAF8F5 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .preloader-svg {
            width: 120px;
            height: 120px;
        }
        
        /* Hide preloader when ready */
        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }
    </style>
</head>
<body>
    <!-- Preloader with animated circles -->
    <div class="preloader" id="preloader">
        <div class="preloader-svg">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="100%" height="100%" style="shape-rendering: auto; display: block; background: transparent;" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g>
                    <circle stroke-width="25" stroke="#E9C46A" fill="none" r="0" cy="50" cx="50">
                        <animate begin="0s" calcMode="spline" keySplines="0 0.2 0.8 1" keyTimes="0;1" values="0;44" dur="2s" repeatCount="indefinite" attributeName="r"></animate>
                        <animate begin="0s" calcMode="spline" keySplines="0.2 0 0.8 1" keyTimes="0;1" values="1;0" dur="2s" repeatCount="indefinite" attributeName="opacity"></animate>
                    </circle>
                    <circle stroke-width="25" stroke="#E76F51" fill="none" r="0" cy="50" cx="50">
                        <animate begin="-1s" calcMode="spline" keySplines="0 0.2 0.8 1" keyTimes="0;1" values="0;44" dur="2s" repeatCount="indefinite" attributeName="r"></animate>
                        <animate begin="-1s" calcMode="spline" keySplines="0.2 0 0.8 1" keyTimes="0;1" values="1;0" dur="2s" repeatCount="indefinite" attributeName="opacity"></animate>
                    </circle>
                </g>
            </svg>
        </div>
    </div>
    
    <script>
        // Check if app is ready, then redirect
        function redirectToApp() {
            // Wait a minimum of 1 second to show the loader
            const minDisplayTime = 1000;
            const startTime = Date.now();
            
            // Wait for page to fully load
            const checkReady = () => {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, minDisplayTime - elapsed);
                
                setTimeout(() => {
                    // Check if user is logged in
                    <?php
                    // Check authentication status
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $isUserLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
                    $isAdminLoggedIn = isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
                    ?>
                    
                    const isUserLoggedIn = <?php echo $isUserLoggedIn ? 'true' : 'false'; ?>;
                    const isAdminLoggedIn = <?php echo $isAdminLoggedIn ? 'true' : 'false'; ?>;
                    
                    // Hide loader with fade animation
                    const preloader = document.getElementById('preloader');
                    if (preloader) {
                        preloader.classList.add('hidden');
                    }
                    
                    // Redirect after fade animation completes
                    setTimeout(() => {
                        if (isUserLoggedIn) {
                            window.location.href = '/user/dashboard.php';
                        } else if (isAdminLoggedIn) {
                            window.location.href = '/admin/welcome_admin.php';
                        } else {
                            window.location.href = '/user/login.php';
                        }
                    }, 500); // Wait for fade animation
                }, remaining);
            };
            
            // Start checking once DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkReady);
            } else {
                checkReady();
            }
        }
        
        // Also check on window load (for resources)
        window.addEventListener('load', () => {
            // If redirect hasn't happened yet, ensure it does
            if (!document.getElementById('preloader').classList.contains('hidden')) {
                setTimeout(redirectToApp, 100);
            }
        });
        
        // Start redirect process
        redirectToApp();
    </script>
    
    <!-- PWA Footer -->
    <?php include 'includes/pwa-footer.php'; ?>
</body>
</html>

