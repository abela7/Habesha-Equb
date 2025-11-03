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
      const registration = await navigator.serviceWorker.register('/service-worker.js', {
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
        <button class="pwa-install-banner-btn" onclick="pwaManager.installApp()">
          <i class="fas fa-download me-1"></i>Install
        </button>
        <button class="pwa-install-dismiss-btn" onclick="pwaManager.dismissInstallBanner()" title="Dismiss">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
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
      installBtn.onclick = () => this.installApp();
      
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
        <button class="pwa-update-btn" onclick="pwaManager.updateApp()">
          <i class="fas fa-download me-1"></i>Update
        </button>
        <button class="pwa-dismiss-btn" onclick="pwaManager.dismissUpdate()">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
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
}

// Initialize PWA Manager when DOM is ready
let pwaManager;
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    pwaManager = new PWAManager();
  });
} else {
  pwaManager = new PWAManager();
}

// Make it globally available
window.pwaManager = pwaManager;

