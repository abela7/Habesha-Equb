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
  '/',
  '/assets/css/style.css',
  '/Pictures/Main Logo.png',
  '/Pictures/Icon/favicon.ico',
  '/Pictures/Icon/android-icon-192x192.png',
  '/Pictures/Icon/apple-icon-180x180.png',
  '/languages/en.json',
  '/languages/am.json',
  '/offline.html'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...', CACHE_VERSION);
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Caching static assets');
        return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
      })
      .then(() => {
        // Force the waiting service worker to become the active service worker
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[Service Worker] Cache failed:', error);
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
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip API calls (they should always go to network)
  if (url.pathname.includes('/api/')) {
    return;
  }
  
  // Skip admin API calls
  if (url.pathname.includes('/admin/api/') || url.pathname.includes('/user/api/')) {
    return;
  }
  
  event.respondWith(
    caches.match(request)
      .then((cachedResponse) => {
        // Return cached version if available
        if (cachedResponse) {
          // Also fetch from network in background to update cache
          fetch(request)
            .then((networkResponse) => {
              if (networkResponse && networkResponse.status === 200) {
                const responseClone = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => {
                  cache.put(request, responseClone);
                });
              }
            })
            .catch(() => {
              // Network fetch failed, cached version is fine
            });
          
          return cachedResponse;
        }
        
        // No cache, fetch from network
        return fetch(request)
          .then((networkResponse) => {
            // Don't cache non-successful responses
            if (!networkResponse || networkResponse.status !== 200) {
              return networkResponse;
            }
            
            // Cache successful responses
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseToCache);
            });
            
            return networkResponse;
          })
          .catch(() => {
            // Network failed, try to serve offline page for navigation requests
            if (request.mode === 'navigate') {
              return caches.match(OFFLINE_PAGE);
            }
            return new Response('Offline', { status: 503 });
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

