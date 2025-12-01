// Service Worker for Bible PWA
const CACHE_VERSION = 'v1';
const STATIC_CACHE = `bible-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `bible-dynamic-${CACHE_VERSION}`;
const CHAPTERS_CACHE = `bible-chapters-${CACHE_VERSION}`;

// Static assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/manifest.webmanifest',
    '/favicon.svg',
    '/favicon.ico',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            console.log('[SW] Caching static assets');
            return cache.addAll(STATIC_ASSETS);
        }).then(() => {
            return self.skipWaiting();
        })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => {
                        return name.startsWith('bible-') && 
                               name !== STATIC_CACHE && 
                               name !== DYNAMIC_CACHE &&
                               name !== CHAPTERS_CACHE;
                    })
                    .map((name) => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== self.location.origin) {
        return;
    }

    // Skip POST, PUT, DELETE requests for now
    if (request.method !== 'GET') {
        return;
    }

    // API requests - network first, cache fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone the response before caching
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(request);
                })
        );
        return;
    }

    // Static assets and pages - cache first, network fallback
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((response) => {
                // Don't cache if not successful
                if (!response || response.status !== 200) {
                    return response;
                }

                const responseClone = response.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => {
                    cache.put(request, responseClone);
                });

                return response;
            });
        })
    );
});

// Message event - handle messages from the app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CACHE_CHAPTER') {
        const { url, data } = event.data;
        // Store chapter data in IndexedDB via the app
        event.ports[0].postMessage({ success: true });
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name.startsWith('bible-'))
                        .map((name) => caches.delete(name))
                );
            })
        );
    }
});

// Background sync for queued mutations
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync event:', event.tag);
    
    if (event.tag === 'sync-mutations') {
        event.waitUntil(
            self.clients.matchAll().then((clients) => {
                // Notify all clients to sync their mutations
                clients.forEach((client) => {
                    client.postMessage({
                        type: 'SYNC_MUTATIONS'
                    });
                });
            })
        );
    }
});
