/**
 * HabeshaEqub - Progressive Web App Service Worker
 * Smart caching and update mechanism
 */

// Update this version number when you deploy updates
const CACHE_VERSION = '1.0.0';
const CACHE_NAME = `habeshaequb-v${CACHE_VERSION}`;
const OFFLINE_PAGE = '/offline.html';

// Assets to cache immediately on install
const STATIC_ASSETS = [
  '/index.php',
  '/assets/css/style.css',
  '/Pictures/Main Logo.png',
  '/Pictures/Icon/favicon.ico',
  '/Pictures/Icon/android-icon-192x192.png',
  '/Pictures/Icon/apple-icon-180x180.png',
  '/languages/en.json',
  '/languages/am.json',
  '/offline.html',
  '/No Connection.lottie'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...', CACHE_VERSION);
  console.log('[Service Worker] CACHE_NAME:', CACHE_NAME);
  console.log('[Service Worker] STATIC_ASSETS:', STATIC_ASSETS);
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Cache opened:', CACHE_NAME);
        console.log('[Service Worker] Caching static assets...');
        // Use Promise.allSettled to continue even if some assets fail
        return Promise.allSettled(
          STATIC_ASSETS.map(url => {
            console.log('[Service Worker] Caching:', url);
            return cache.add(new Request(url, { cache: 'reload' }))
              .catch(err => {
                console.error('[Service Worker] Failed to cache:', url, err);
                // Re-throw the error so Promise.allSettled correctly marks it as 'rejected'
                throw err;
              });
          })
        );
      })
      .then((results) => {
        const successCount = results.filter(r => r.status === 'fulfilled').length;
        const failCount = results.filter(r => r.status === 'rejected').length;
        const failedUrls = results
          .filter(r => r.status === 'rejected')
          .map((r, i) => STATIC_ASSETS[i])
          .filter(Boolean);
        
        console.log(`[Service Worker] Caching complete: ${successCount} succeeded, ${failCount} failed`);
        if (failCount > 0) {
          console.warn('[Service Worker] Failed to cache:', failedUrls);
        }
        // Force the waiting service worker to become the active service worker
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[Service Worker] Install failed:', error);
        // Still skip waiting to activate even if caching fails
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...', CACHE_VERSION);
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          // Delete old caches that don't match current version
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      // Take control of all pages immediately
      return self.clients.claim();
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Log all fetch requests for debugging
  console.log('[Service Worker] Fetch:', request.method, url.href, 'mode:', request.mode);
  
  // Skip non-GET requests (let them pass through)
  if (request.method !== 'GET') {
    console.log('[Service Worker] Skipping non-GET request');
    return;
  }
  
  // Skip API calls (they should always go to network)
  if (url.pathname.includes('/api/')) {
    console.log('[Service Worker] Skipping API call');
    return;
  }
  
  // Skip admin API calls
  if (url.pathname.includes('/admin/api/') || url.pathname.includes('/user/api/')) {
    console.log('[Service Worker] Skipping admin/user API call');
    return;
  }
  
  // For navigation requests (page loads), always try network first
  if (request.mode === 'navigate') {
    console.log('[Service Worker] Navigation request - network first');
    event.respondWith(
      fetch(request, { 
        cache: 'no-store',
        credentials: 'same-origin'
      })
        .then((response) => {
          console.log('[Service Worker] Network response:', response.status, url.href);
          // If network succeeds, cache it and return
          if (response && response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseToCache);
              console.log('[Service Worker] Cached:', url.href);
            }).catch(err => {
              console.error('[Service Worker] Cache error:', err);
            });
          }
          return response;
        })
        .catch((error) => {
          console.error('[Service Worker] Network failed:', error.message, url.href);
          // Network failed, try cache
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
              console.log('[Service Worker] Serving from cache:', url.href);
              return cachedResponse;
            }
            // No cache, return offline page
            console.log('[Service Worker] No cache, returning offline page');
            return caches.match(OFFLINE_PAGE);
          });
        })
    );
    return;
  }
  
  // For other requests (assets, images, etc.), try network first then cache
  console.log('[Service Worker] Asset request - network first:', url.href);
  event.respondWith(
    fetch(request, {
      cache: 'no-store',
      credentials: 'same-origin'
    })
      .then((networkResponse) => {
        console.log('[Service Worker] Asset network response:', networkResponse.status, url.href);
        // Don't cache non-successful responses
        if (!networkResponse || networkResponse.status !== 200) {
          console.log('[Service Worker] Non-200 response, not caching');
          return networkResponse;
        }
        
        // Cache successful responses
        const responseToCache = networkResponse.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(request, responseToCache);
          console.log('[Service Worker] Cached asset:', url.href);
        }).catch(err => {
          console.error('[Service Worker] Cache error:', err);
        });
        
        return networkResponse;
      })
      .catch((error) => {
        console.error('[Service Worker] Network failed for asset:', error.message, url.href);
        // Network failed, try cache
        return caches.match(request).then((cachedResponse) => {
          if (cachedResponse) {
            console.log('[Service Worker] Serving asset from cache:', url.href);
            return cachedResponse;
          }
          // No cache available
          console.log('[Service Worker] No cache for asset:', url.href);
          return new Response('Resource not available offline', { 
            status: 503,
            statusText: 'Service Unavailable'
          });
        });
      })
  );
});

// Listen for messages from the main thread
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CHECK_UPDATE') {
    checkForUpdate();
  }
});

// Check for app updates
async function checkForUpdate() {
  try {
    // Fetch the service worker file to check for changes
    const response = await fetch('/service-worker.js', { cache: 'no-store' });
    const newScript = await response.text();
    
    // Get current script from cache
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match('/service-worker.js');
    
    if (cachedResponse) {
      const oldScript = await cachedResponse.text();
      
      // Compare scripts (simple version check)
      if (newScript !== oldScript) {
        // New version detected
        notifyClients({
          type: 'UPDATE_AVAILABLE',
          version: CACHE_VERSION
        });
      }
    }
  } catch (error) {
    console.error('[Service Worker] Update check failed:', error);
  }
}

// Notify all clients about updates
function notifyClients(message) {
  self.clients.matchAll().then((clients) => {
    clients.forEach((client) => {
      client.postMessage(message);
    });
  });
}

// Periodic update check (every hour)
setInterval(() => {
  checkForUpdate();
}, 3600000); // 1 hour

