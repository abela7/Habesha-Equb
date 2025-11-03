/**
 * HabeshaEqub - PWA Manager
 * Handles installation, updates, and PWA functionality
 */

class PWAManager {
  constructor() {
    this.deferredPrompt = null;
    this.updateAvailable = false;
    this.serviceWorkerRegistration = null;
    this.updateCheckInterval = null;
    
    this.init();
  }
  
  async init() {
    // Register service worker
    if ('serviceWorker' in navigator) {
      await this.registerServiceWorker();
    }
    
    // Listen for install prompt
    this.setupInstallPrompt();
    
    // Check for updates periodically
    this.startUpdateCheck();
    
    // Listen for update messages from service worker
    this.setupUpdateListener();
  }
  
  async registerServiceWorker() {
    try {
      // Use absolute URL for service worker registration
      const swUrl = window.location.origin + '/service-worker.js';
      const registration = await navigator.serviceWorker.register(swUrl, {
        scope: '/'
      });
      
      this.serviceWorkerRegistration = registration;
      
      console.log('[PWA] Service Worker registered:', registration.scope);
      
      // Check for updates immediately (but don't show notification yet)
      registration.update().catch(err => console.log('[PWA] Initial update check:', err));
      
      // Listen for updates
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        
        if (newWorker) {
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New service worker is ready
              this.showUpdateNotification();
            }
          });
        }
      });
      
      // Check if page is being controlled by a service worker
      if (navigator.serviceWorker.controller) {
        console.log('[PWA] Page is controlled by service worker');
      }
      
    } catch (error) {
      console.error('[PWA] Service Worker registration failed:', error);
    }
  }
  
  setupInstallPrompt() {
    // Check if already installed
    if (this.isInstalled()) {
      return;
    }
    
    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
      // Prevent the mini-infobar from appearing
      e.preventDefault();
      
      // Save the event for later
      this.deferredPrompt = e;
      
      // Show custom install notification after a delay
      setTimeout(() => {
        this.showInstallButton();
      }, 2000); // Show after 2 seconds
    });
    
    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
      console.log('[PWA] App installed successfully');
      this.hideInstallBanner();
      this.hideInstallButton();
      this.deferredPrompt = null;
      
      // Clear dismissal flag
      localStorage.removeItem('pwa-install-dismissed');
      
      // Track installation (only once per session)
      // Check localStorage to prevent duplicate tracking if page was loaded
      const tracked = sessionStorage.getItem('pwa-installation-tracked');
      if (!tracked) {
        sessionStorage.setItem('pwa-installation-tracked', 'true');
        this.trackInstallation();
      }
      
      // Show success message
      this.showToast('App installed successfully!', 'success');
    });
    
    // Also check periodically if install prompt is available but not shown
    // (for cases where beforeinstallprompt didn't fire)
    setTimeout(() => {
      if (!this.isInstalled() && !this.deferredPrompt) {
        // Check if we can still show install prompt
        // Some browsers may not fire beforeinstallprompt immediately
        const dismissedTime = localStorage.getItem('pwa-install-dismissed');
        if (!dismissedTime || (Date.now() - parseInt(dismissedTime)) > (7 * 24 * 60 * 60 * 1000)) {
          // Show floating button as fallback
          this.showFloatingInstallButton();
        }
      }
    }, 5000); // Check after 5 seconds
  }
  
  showInstallButton() {
    // Check if already installed
    if (this.isInstalled()) {
      return;
    }
    
    // Check if user dismissed the prompt recently (within 7 days)
    const dismissedTime = localStorage.getItem('pwa-install-dismissed');
    if (dismissedTime) {
      const daysSinceDismissed = (Date.now() - parseInt(dismissedTime)) / (1000 * 60 * 60 * 24);
      if (daysSinceDismissed < 7) {
        // Show floating button instead of banner
        this.showFloatingInstallButton();
        return;
      }
    }
    
    // Show prominent install banner
    this.showInstallBanner();
  }
  
  showInstallBanner() {
    // Check if banner already exists
    if (document.getElementById('pwa-install-banner')) {
      return;
    }
    
    // Check if already installed
    if (this.isInstalled()) {
      return;
    }
    
    // Create install banner
    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.className = 'pwa-install-banner';
    banner.innerHTML = `
      <div class="pwa-install-banner-content">
        <div class="pwa-install-icon">
          <i class="fas fa-mobile-alt"></i>
        </div>
        <div class="pwa-install-text">
          <strong>Install HabeshaEqub App</strong>
          <span>Get quick access and a better experience</span>
        </div>
      </div>
      <div class="pwa-install-banner-actions">
        <button class="pwa-install-banner-btn" data-action="install">
          <i class="fas fa-download me-1"></i>Install
        </button>
        <button class="pwa-install-dismiss-btn" data-action="dismiss" title="Dismiss">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
    // Add event listeners instead of inline handlers
    const installBtn = banner.querySelector('[data-action="install"]');
    const dismissBtn = banner.querySelector('[data-action="dismiss"]');
    
    if (installBtn) {
      installBtn.addEventListener('click', () => this.installApp());
    }
    if (dismissBtn) {
      dismissBtn.addEventListener('click', () => this.dismissInstallBanner());
    }
    
    document.body.appendChild(banner);
    
    // Animate in
    setTimeout(() => {
      banner.classList.add('show');
    }, 500); // Show after 500ms delay
  }
  
  showFloatingInstallButton() {
    // Check if already installed
    if (this.isInstalled()) {
      return;
    }
    
    // Create floating install button if it doesn't exist
    let installBtn = document.getElementById('pwa-install-btn');
    
    if (!installBtn) {
      installBtn = document.createElement('button');
      installBtn.id = 'pwa-install-btn';
      installBtn.className = 'pwa-install-btn';
      installBtn.innerHTML = '<i class="fas fa-download"></i><span>Install App</span>';
      installBtn.addEventListener('click', () => this.installApp());
      
      const installContainer = document.createElement('div');
      installContainer.className = 'pwa-install-container';
      installContainer.appendChild(installBtn);
      document.body.appendChild(installContainer);
      
      // Animate in
      setTimeout(() => {
        installBtn.classList.add('show');
      }, 1000);
    }
    
    installBtn.style.display = 'flex';
  }
  
  dismissInstallBanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
      banner.classList.remove('show');
      setTimeout(() => {
        banner.remove();
      }, 300);
      
      // Remember dismissal for 7 days
      localStorage.setItem('pwa-install-dismissed', Date.now().toString());
      
      // Show floating button instead
      setTimeout(() => {
        this.showFloatingInstallButton();
      }, 500);
    }
  }
  
  hideInstallButton() {
    const installBtn = document.getElementById('pwa-install-btn');
    if (installBtn) {
      installBtn.style.display = 'none';
    }
  }
  
  async installApp() {
    if (!this.deferredPrompt) {
      return;
    }
    
    // Show the install prompt
    this.deferredPrompt.prompt();
    
    // Wait for user response
    const { outcome } = await this.deferredPrompt.userChoice;
    
    console.log('[PWA] User choice:', outcome);
    
    if (outcome === 'accepted') {
      this.showToast('Installing app...', 'info');
    }
    
    // Clear the deferred prompt
    this.deferredPrompt = null;
    this.hideInstallBanner();
    this.hideInstallButton();
  }
  
  hideInstallBanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
      banner.classList.remove('show');
      setTimeout(() => {
        banner.remove();
      }, 300);
    }
  }
  
  startUpdateCheck() {
    // Check for updates every 30 minutes
    this.updateCheckInterval = setInterval(() => {
      this.checkForUpdate();
    }, 1800000); // 30 minutes
    
    // Also check immediately
    this.checkForUpdate();
  }
  
  async checkForUpdate() {
    if (!this.serviceWorkerRegistration) {
      return;
    }
    
    try {
      // Force update check by fetching service worker with no-cache
      const response = await fetch('/service-worker.js', {
        cache: 'no-store',
        headers: {
          'Cache-Control': 'no-cache'
        }
      });
      
      if (response.ok) {
        const newScript = await response.text();
        
        // Check current service worker script
        // Get all cache names and find the current one
        const cacheNames = await caches.keys();
        const currentCacheName = cacheNames.find(name => name.startsWith('habeshaequb-v'));
        
        if (currentCacheName) {
          const cache = await caches.open(currentCacheName);
          const cachedResponse = await cache.match('/service-worker.js');
          
          if (cachedResponse) {
            const oldScript = await cachedResponse.text();
            
            // Compare scripts (check if version changed)
            const newVersionMatch = newScript.match(/CACHE_VERSION\s*=\s*['"]([^'"]+)['"]/);
            const oldVersionMatch = oldScript.match(/CACHE_VERSION\s*=\s*['"]([^'"]+)['"]/);
            
            if (newVersionMatch && oldVersionMatch && newVersionMatch[1] !== oldVersionMatch[1]) {
              // Version changed, update available
              await this.serviceWorkerRegistration.update();
              
              // Wait a bit for the new worker to install
              setTimeout(() => {
                if (this.serviceWorkerRegistration.waiting) {
                  this.showUpdateNotification();
                }
              }, 1000);
            } else if (newScript !== oldScript) {
              // Script changed but version might be same (hotfix)
              await this.serviceWorkerRegistration.update();
              
              setTimeout(() => {
                if (this.serviceWorkerRegistration.waiting) {
                  this.showUpdateNotification();
                }
              }, 1000);
            }
          }
        }
      }
      
      // Also do regular update check
      await this.serviceWorkerRegistration.update();
      
      // Check if there's a waiting service worker
      if (this.serviceWorkerRegistration.waiting) {
        this.showUpdateNotification();
      }
    } catch (error) {
      console.error('[PWA] Update check failed:', error);
    }
  }
  
  setupUpdateListener() {
    // Listen for messages from service worker
    navigator.serviceWorker.addEventListener('message', (event) => {
      if (event.data && event.data.type === 'UPDATE_AVAILABLE') {
        this.showUpdateNotification();
      }
    });
  }
  
  showUpdateNotification() {
    // Don't show multiple notifications
    if (this.updateAvailable) {
      return;
    }
    
    this.updateAvailable = true;
    
    // Create update notification
    const notification = document.createElement('div');
    notification.id = 'pwa-update-notification';
    notification.className = 'pwa-update-notification';
    notification.innerHTML = `
      <div class="pwa-update-content">
        <i class="fas fa-sync-alt me-2"></i>
        <span>New version available! Update now to get the latest features.</span>
      </div>
      <div class="pwa-update-actions">
        <button class="pwa-update-btn" data-action="update">
          <i class="fas fa-download me-1"></i>Update
        </button>
        <button class="pwa-dismiss-btn" data-action="dismiss-update">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
    // Add event listeners instead of inline handlers
    const updateBtn = notification.querySelector('[data-action="update"]');
    const dismissUpdateBtn = notification.querySelector('[data-action="dismiss-update"]');
    
    if (updateBtn) {
      updateBtn.addEventListener('click', () => this.updateApp());
    }
    if (dismissUpdateBtn) {
      dismissUpdateBtn.addEventListener('click', () => this.dismissUpdate());
    }
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
      notification.classList.add('show');
    }, 100);
  }
  
  async updateApp() {
    if (!this.serviceWorkerRegistration || !this.serviceWorkerRegistration.waiting) {
      this.showToast('No update available', 'info');
      return;
    }
    
    // Send message to waiting service worker to skip waiting
    this.serviceWorkerRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
    
    // Reload the page
    window.location.reload();
  }
  
  dismissUpdate() {
    const notification = document.getElementById('pwa-update-notification');
    if (notification) {
      notification.classList.remove('show');
      setTimeout(() => {
        notification.remove();
      }, 300);
    }
    this.updateAvailable = false;
  }
  
  isInstalled() {
    // Check if app is installed (standalone mode)
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true ||
           document.referrer.includes('android-app://');
  }
  
  showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `pwa-toast pwa-toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
      toast.classList.add('show');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 3000);
  }
  
  async trackInstallation(installationCompleted = true) {
    try {
      const deviceInfo = {
        platform: navigator.platform,
        screen: {
          width: window.screen.width,
          height: window.screen.height
        },
        is_standalone: window.matchMedia('(display-mode: standalone)').matches
      };
      
      const browserInfo = {
        browser: this.getBrowserName(),
        version: this.getBrowserVersion(),
        os: this.getOSName()
      };
      
      const response = await fetch('/admin/api/pwa-installations.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'record_installation',
          platform: deviceInfo.platform,
          screen: deviceInfo.screen,
          is_standalone: deviceInfo.is_standalone,
          browser: browserInfo.browser,
          version: browserInfo.version,
          os: browserInfo.os,
          installation_completed: installationCompleted,
          visit_only: !installationCompleted
        })
      });
      
      const data = await response.json();
      if (data.success) {
        console.log('[PWA] Installation tracked:', data);
      }
    } catch (error) {
      console.error('[PWA] Failed to track installation:', error);
    }
  }
  
  getBrowserName() {
    const ua = navigator.userAgent;
    if (ua.includes('Chrome')) return 'Chrome';
    if (ua.includes('Firefox')) return 'Firefox';
    if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
    if (ua.includes('Edge')) return 'Edge';
    return 'Unknown';
  }
  
  getBrowserVersion() {
    const ua = navigator.userAgent;
    const match = ua.match(/(Chrome|Firefox|Safari|Edge)\/(\d+)/);
    return match ? match[2] : 'Unknown';
  }
  
  getOSName() {
    const ua = navigator.userAgent;
    if (ua.includes('Windows')) return 'Windows';
    if (ua.includes('Mac')) return 'macOS';
    if (ua.includes('Linux')) return 'Linux';
    if (ua.includes('Android')) return 'Android';
    if (ua.includes('iOS') || ua.includes('iPhone') || ua.includes('iPad')) return 'iOS';
    return 'Unknown';
  }
}

// Initialize PWA Manager when DOM is ready
let pwaManager;

function initializePWAManager() {
  if (!pwaManager) {
    pwaManager = new PWAManager();
    // Make it globally available after initialization
    window.pwaManager = pwaManager;
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializePWAManager);
} else {
  initializePWAManager();
}

