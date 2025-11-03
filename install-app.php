<?php
/**
 * HabeshaEqub - PWA Install Page
 * Simple install page with just an install button
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
    <title>Install HabeshaEqub App</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="Pictures/Icon/favicon-32x32.png">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PWA Support -->
    <?php include 'includes/pwa-head.php'; ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #F1ECE2 0%, #FAF8F5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .install-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(48, 25, 52, 0.1);
            text-align: center;
        }
        
        .app-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(48, 25, 52, 0.2);
        }
        
        .app-icon img {
            width: 100%;
            height: 100%;
            border-radius: 24px;
            object-fit: contain;
        }
        
        h1 {
            color: #4D4052;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 40px;
        }
        
        .install-btn {
            background: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
            color: #4D4052;
            border: none;
            padding: 18px 48px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            box-shadow: 0 4px 16px rgba(218, 165, 32, 0.3);
        }
        
        .install-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 165, 32, 0.4);
        }
        
        .install-btn:active:not(:disabled) {
            transform: translateY(0);
        }
        
        .install-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .status-message {
            padding: 14px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 14px;
            font-weight: 500;
            display: none;
        }
        
        .status-message.show {
            display: block;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .instructions {
            margin-top: 30px;
            padding: 20px;
            background: #F1ECE2;
            border-radius: 12px;
            text-align: left;
            display: none;
        }
        
        .instructions.show {
            display: block;
        }
        
        .instructions h3 {
            color: #4D4052;
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .instructions ol {
            margin: 0;
            padding-left: 20px;
            color: #666;
        }
        
        .instructions li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .instructions strong {
            color: #4D4052;
        }
        
        @media (max-width: 768px) {
            .install-container {
                padding: 40px 30px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .app-icon {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="app-icon">
            <img src="Pictures/Main Logo.png" alt="HabeshaEqub">
        </div>
        
        <h1>Install HabeshaEqub</h1>
        <p class="subtitle">Tap the button below to install</p>
        
        <!-- Install Button -->
        <button id="installBtn" class="install-btn">
            <i class="fas fa-download"></i>
            <span id="installBtnText">Install App</span>
        </button>
        
        <!-- Status Message -->
        <div id="statusMessage" class="status-message"></div>
        
        <!-- Manual Instructions (shown when automatic install not available) -->
        <div id="instructions" class="instructions">
            <h3>
                <i class="fas fa-info-circle"></i>
                <span>Installation Instructions</span>
            </h3>
            <ol id="instructionsContent"></ol>
        </div>
    </div>
    
    <script src="assets/js/pwa-manager.js"></script>
    
    <script>
        let deferredPrompt = null;
        const installBtn = document.getElementById('installBtn');
        const installBtnText = document.getElementById('installBtnText');
        const statusMessage = document.getElementById('statusMessage');
        const instructions = document.getElementById('instructions');
        const instructionsContent = document.getElementById('instructionsContent');
        
        // Detect browser and OS
        function detectBrowser() {
            const ua = navigator.userAgent;
            const isIOS = /iPad|iPhone|iPod/.test(ua);
            const isAndroid = /Android/.test(ua);
            const isChrome = /Chrome/.test(ua) && !/Edg/.test(ua);
            const isEdge = /Edg/.test(ua);
            const isSafari = /Safari/.test(ua) && !/Chrome/.test(ua);
            const isFirefox = /Firefox/.test(ua);
            
            return {
                iOS: isIOS,
                Android: isAndroid,
                Chrome: isChrome,
                Edge: isEdge,
                Safari: isSafari,
                Firefox: isFirefox,
                mobile: isIOS || isAndroid
            };
        }
        
        // Check if app is already installed
        function isInstalled() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true ||
                   document.referrer.includes('android-app://');
        }
        
        // Show status message
        function showStatus(message, type) {
            statusMessage.className = `status-message status-${type} show`;
            statusMessage.innerHTML = message;
            setTimeout(() => {
                statusMessage.classList.remove('show');
            }, 5000);
        }
        
        // Show manual installation instructions based on browser
        function showManualInstructions() {
            const browser = detectBrowser();
            let html = '';
            
            if (browser.iOS && browser.Safari) {
                html = `
                    <li>Tap the <strong>Share</strong> button <i class="fas fa-share"></i> at the bottom of the screen</li>
                    <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                    <li>Tap <strong>"Add"</strong> in the top right corner</li>
                `;
            } else if (browser.Android && browser.Chrome) {
                html = `
                    <li>Tap the <strong>menu</strong> button <i class="fas fa-ellipsis-vertical"></i> (three dots) in the top right</li>
                    <li>Tap <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                    <li>Tap <strong>"Install"</strong> or <strong>"Add"</strong> to confirm</li>
                `;
            } else if (browser.Android) {
                html = `
                    <li>Tap the <strong>menu</strong> button <i class="fas fa-ellipsis-vertical"></i> in your browser</li>
                    <li>Look for <strong>"Add to Home screen"</strong> or <strong>"Install"</strong> option</li>
                    <li>Follow the prompts to install</li>
                `;
            } else if (browser.Chrome || browser.Edge) {
                html = `
                    <li>Look for the <strong>install icon</strong> <i class="fas fa-download"></i> in the address bar</li>
                    <li>Or click the <strong>menu</strong> button <i class="fas fa-ellipsis-vertical"></i> (three dots) → <strong>"Install HabeshaEqub"</strong></li>
                    <li>Click <strong>"Install"</strong> in the popup</li>
                `;
            } else if (browser.Safari) {
                html = `
                    <li>Click <strong>File</strong> → <strong>"Add to Dock"</strong> (Mac)</li>
                    <li>Or use <strong>Bookmarks</strong> → <strong>"Add to Home Screen"</strong></li>
                `;
            } else if (browser.Firefox) {
                html = `
                    <li>Look for the <strong>install icon</strong> in the address bar</li>
                    <li>Or use <strong>Menu</strong> → <strong>"Install"</strong></li>
                    <li>Click <strong>"Install"</strong> to confirm</li>
                `;
            } else {
                html = `
                    <li>Look for an <strong>"Install"</strong> button or icon in your browser</li>
                    <li>It may appear in the address bar or browser menu</li>
                    <li>Follow your browser's prompts to install the app</li>
                `;
            }
            
            instructionsContent.innerHTML = html;
            instructions.classList.add('show');
        }
        
        // Track installation - ensures every visit to /install is tracked
        async function trackInstallation(installationCompleted = false) {
            try {
                // Track both page visits and actual installations
                await fetch('admin/api/pwa-installations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'record_installation',
                        platform: navigator.platform,
                        screen: {
                            width: window.screen.width,
                            height: window.screen.height
                        },
                        is_standalone: window.matchMedia('(display-mode: standalone)').matches,
                        browser: getBrowserName(),
                        version: getBrowserVersion(),
                        os: getOSName(),
                        installation_completed: installationCompleted,
                        visit_only: !installationCompleted
                    })
                });
            } catch (error) {
                console.error('Failed to track installation:', error);
            }
        }
        
        // Browser detection helpers
        function getBrowserName() {
            const browser = detectBrowser();
            if (browser.Chrome) return 'Chrome';
            if (browser.Firefox) return 'Firefox';
            if (browser.Safari) return 'Safari';
            if (browser.Edge) return 'Edge';
            return 'Unknown';
        }
        
        function getBrowserVersion() {
            const ua = navigator.userAgent;
            const match = ua.match(/(Chrome|Firefox|Safari|Edg)\/(\d+)/);
            return match ? match[2] : 'Unknown';
        }
        
        function getOSName() {
            const ua = navigator.userAgent;
            if (ua.includes('Windows')) return 'Windows';
            if (ua.includes('Mac')) return 'macOS';
            if (ua.includes('Linux')) return 'Linux';
            if (ua.includes('Android')) return 'Android';
            if (ua.includes('iOS') || ua.includes('iPhone') || ua.includes('iPad')) return 'iOS';
            return 'Unknown';
        }
        
        // Check installation status
        function checkInstallStatus() {
            if (isInstalled()) {
                installBtn.disabled = true;
                installBtnText.textContent = 'App Installed';
                showStatus('✓ App is already installed!', 'success');
                return;
            }
            
            // Listen for beforeinstallprompt event (Chrome, Edge, Firefox)
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                installBtn.style.display = 'inline-flex';
                instructions.classList.remove('show'); // Hide manual instructions if automatic works
            });
            
            // Listen for appinstalled event
            window.addEventListener('appinstalled', () => {
                installBtn.disabled = true;
                installBtnText.textContent = 'App Installed';
                showStatus('✓ App installed successfully!', 'success');
                deferredPrompt = null;
                instructions.classList.remove('show');
                trackInstallation(true); // Track as completed installation
            });
            
            // If no automatic prompt after 2 seconds, show manual instructions
            setTimeout(() => {
                if (!deferredPrompt && !isInstalled()) {
                    // Show manual instructions for browsers that don't support automatic install
                    showManualInstructions();
                }
            }, 2000);
        }
        
        // Install button click handler - works for both automatic and manual installs
        installBtn.addEventListener('click', async () => {
            // If automatic install prompt is available, use it
            if (deferredPrompt) {
                installBtn.disabled = true;
                installBtnText.textContent = 'Installing...';
                
                try {
                    // Show the install prompt
                    deferredPrompt.prompt();
                    
                    // Wait for user response
                    const { outcome } = await deferredPrompt.userChoice;
                    
                    if (outcome === 'accepted') {
                        showStatus('✓ Installation started!', 'success');
                        await trackInstallation(true);
                    } else {
                        showStatus('Installation cancelled. Try the manual instructions below.', 'info');
                        installBtn.disabled = false;
                        installBtnText.textContent = 'Install App';
                        showManualInstructions();
                    }
                    
                    deferredPrompt = null;
                } catch (error) {
                    console.error('Install prompt error:', error);
                    showStatus('Automatic installation failed. Use the instructions below.', 'info');
                    installBtn.disabled = false;
                    installBtnText.textContent = 'Install App';
                    showManualInstructions();
                }
            } else {
                // No automatic prompt available - show manual instructions
                showManualInstructions();
                installBtn.blur(); // Remove focus
                
                // Scroll instructions into view on mobile
                if (window.innerWidth <= 768) {
                    setTimeout(() => {
                        instructions.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
            }
        });
        
        // Initialize on page load
        window.addEventListener('load', () => {
            checkInstallStatus();
            // Track that someone visited the install page
            trackInstallation(false);
        });
    </script>
    
    <!-- PWA Footer -->
    <?php include 'includes/pwa-footer.php'; ?>
</body>
</html>
