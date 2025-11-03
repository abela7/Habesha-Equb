<?php
/**
 * HabeshaEqub - PWA Install Page
 * Shareable link to install the Progressive Web App
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
    <title>Install HabeshaEqub App - PWA</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="Pictures/Icon/favicon-32x32.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PWA Support -->
    <?php include 'includes/pwa-head.php'; ?>
    
    <style>
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
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(48, 25, 52, 0.1);
            text-align: center;
        }
        
        .app-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #4D4052 0%, #301934 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(48, 25, 52, 0.2);
        }
        
        .app-icon img {
            width: 80px;
            height: 80px;
        }
        
        h1 {
            color: #4D4052;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .install-btn {
            background: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
            color: #4D4052;
            border: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(218, 165, 32, 0.3);
        }
        
        .install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 165, 32, 0.4);
        }
        
        .install-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .status-message {
            padding: 16px;
            border-radius: 12px;
            margin: 20px 0;
            font-weight: 500;
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
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .features-list {
            text-align: left;
            margin: 30px 0;
            padding: 0;
            list-style: none;
        }
        
        .features-list li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4D4052;
        }
        
        .features-list i {
            color: #DAA520;
            font-size: 20px;
        }
        
        .instructions {
            background: #F1ECE2;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }
        
        .instructions h3 {
            color: #4D4052;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
            color: #666;
        }
        
        .share-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #F1ECE2;
        }
        
        .share-btn {
            background: #4D4052;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin: 5px;
        }
        
        .share-btn:hover {
            background: #301934;
        }
        
        @media (max-width: 768px) {
            .install-container {
                padding: 30px 20px;
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
        <p class="subtitle">Get quick access to your Equb management system</p>
        
        <!-- Status Messages -->
        <div id="statusMessage"></div>
        
        <!-- Install Button -->
        <button id="installBtn" class="install-btn" style="display: none;">
            <i class="fas fa-download"></i>
            <span>Install App</span>
        </button>
        
        <!-- Features -->
        <ul class="features-list">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Quick access from your home screen</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Works offline</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Faster loading</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Native app experience</span>
            </li>
        </ul>
        
        <!-- Instructions -->
        <div class="instructions" id="instructions" style="display: none;">
            <h3><i class="fas fa-info-circle"></i> Installation Instructions</h3>
            <div id="instructionsContent"></div>
        </div>
        
        <!-- Share Section -->
        <div class="share-section">
            <p style="color: #666; margin-bottom: 15px;">
                <i class="fas fa-share-alt"></i> Share this install link:
            </p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="shareUrl" value="<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/install-app.php'; ?>" readonly>
                <button class="btn btn-outline-secondary" onclick="copyShareUrl()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <div>
                <button class="share-btn" onclick="shareOnWhatsApp()">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </button>
                <button class="share-btn" onclick="shareOnTelegram()">
                    <i class="fab fa-telegram"></i> Telegram
                </button>
                <button class="share-btn" onclick="shareViaEmail()">
                    <i class="fas fa-envelope"></i> Email
                </button>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/pwa-manager.js"></script>
    
    <script>
        let deferredPrompt = null;
        const installBtn = document.getElementById('installBtn');
        const statusMessage = document.getElementById('statusMessage');
        const instructions = document.getElementById('instructions');
        const instructionsContent = document.getElementById('instructionsContent');
        
        // Check if app is already installed
        function isInstalled() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true ||
                   document.referrer.includes('android-app://');
        }
        
        // Show status message
        function showStatus(message, type) {
            statusMessage.innerHTML = `<div class="status-${type} status-message">${message}</div>`;
        }
        
        // Check installation status
        function checkInstallStatus() {
            if (isInstalled()) {
                showStatus('<i class="fas fa-check-circle"></i> App is already installed!', 'success');
                installBtn.style.display = 'none';
                return;
            }
            
            // Check if browser supports PWA installation
            if (!('serviceWorker' in navigator)) {
                showStatus('<i class="fas fa-exclamation-triangle"></i> Your browser does not support PWA installation.', 'warning');
                showManualInstructions();
                return;
            }
            
            // Wait for beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                installBtn.style.display = 'inline-flex';
                showStatus('<i class="fas fa-info-circle"></i> Click the button above to install the app', 'info');
            });
            
            // Check if already installed via appinstalled event
            window.addEventListener('appinstalled', () => {
                showStatus('<i class="fas fa-check-circle"></i> App installed successfully! You can now close this page.', 'success');
                installBtn.style.display = 'none';
                deferredPrompt = null;
            });
            
            // If no prompt after 3 seconds, show manual instructions
            setTimeout(() => {
                if (!deferredPrompt && !isInstalled()) {
                    showManualInstructions();
                }
            }, 3000);
        }
        
        // Show manual installation instructions
        function showManualInstructions() {
            instructions.style.display = 'block';
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            const isAndroid = /Android/.test(navigator.userAgent);
            const isChrome = /Chrome/.test(navigator.userAgent);
            const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent);
            
            let html = '<ol>';
            
            if (isIOS && isSafari) {
                html += `
                    <li>Tap the <strong>Share</strong> button at the bottom of the screen</li>
                    <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                    <li>Tap <strong>"Add"</strong> to confirm</li>
                `;
            } else if (isAndroid) {
                html += `
                    <li>Look for the <strong>"Add to Home Screen"</strong> banner</li>
                    <li>Or tap the <strong>menu button</strong> (three dots) → <strong>"Add to Home Screen"</strong></li>
                    <li>Tap <strong>"Add"</strong> to confirm</li>
                `;
            } else if (isChrome) {
                html += `
                    <li>Look for the <strong>install icon</strong> in the address bar</li>
                    <li>Or click the <strong>menu button</strong> (three dots) → <strong>"Install HabeshaEqub"</strong></li>
                    <li>Click <strong>"Install"</strong> to confirm</li>
                `;
            } else {
                html += `
                    <li>Look for an <strong>"Install"</strong> button or icon in your browser</li>
                    <li>Some browsers show it in the address bar or menu</li>
                    <li>Follow your browser's prompts to install</li>
                `;
            }
            
            html += '</ol>';
            instructionsContent.innerHTML = html;
        }
        
        // Install button click handler
        installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) {
                showStatus('<i class="fas fa-exclamation-triangle"></i> Installation not available. Please use the manual instructions below.', 'warning');
                showManualInstructions();
                return;
            }
            
            installBtn.disabled = true;
            installBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Installing...';
            
            // Show the install prompt
            deferredPrompt.prompt();
            
            // Wait for user response
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                showStatus('<i class="fas fa-check-circle"></i> Installation started!', 'success');
            } else {
                showStatus('<i class="fas fa-info-circle"></i> Installation cancelled. You can try again anytime.', 'info');
                installBtn.disabled = false;
                installBtn.innerHTML = '<i class="fas fa-download"></i> <span>Install App</span>';
            }
            
            deferredPrompt = null;
        });
        
        // Copy share URL
        function copyShareUrl() {
            const urlInput = document.getElementById('shareUrl');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                showStatus('<i class="fas fa-check"></i> Link copied to clipboard!', 'success');
                setTimeout(() => {
                    statusMessage.innerHTML = '';
                }, 3000);
            } catch (err) {
                showStatus('<i class="fas fa-exclamation-triangle"></i> Failed to copy. Please copy manually.', 'warning');
            }
        }
        
        // Share functions
        function shareOnWhatsApp() {
            const url = encodeURIComponent(document.getElementById('shareUrl').value);
            const text = encodeURIComponent('Install HabeshaEqub App: ');
            window.open(`https://wa.me/?text=${text}${url}`, '_blank');
        }
        
        function shareOnTelegram() {
            const url = encodeURIComponent(document.getElementById('shareUrl').value);
            const text = encodeURIComponent('Install HabeshaEqub App: ');
            window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
        }
        
        function shareViaEmail() {
            const url = document.getElementById('shareUrl').value;
            const subject = encodeURIComponent('Install HabeshaEqub App');
            const body = encodeURIComponent(`Install the HabeshaEqub app by clicking this link:\n\n${url}`);
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
        }
        
        // Initialize on page load
        window.addEventListener('load', () => {
            checkInstallStatus();
        });
        
        // Also check if PWA manager is available
        if (window.pwaManager) {
            window.pwaManager.showInstallButton = () => {}; // Prevent duplicate buttons
        }
    </script>
    
    <!-- PWA Footer -->
    <?php include 'includes/pwa-footer.php'; ?>
</body>
</html>

