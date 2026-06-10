// sw.js - Service Worker for Adopta PWA
const CACHE_NAME = 'adopta-cache-v1';
const ASSETS_TO_CACHE = [
  './',
  './favicon.ico',
  './images/pwa-icon.png',
  './manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Only handle GET requests from the same origin
  if (event.request.origin !== self.location.origin || event.request.method !== 'GET') {
    return;
  }

  // Skip API, private pages, or Inertia specific requests
  if (
    event.request.url.includes('/api/') || 
    event.request.url.includes('/adopter/') || 
    event.request.url.includes('/backoffice/') || 
    event.request.headers.get('X-Inertia')
  ) {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      
      return fetch(event.request).then((networkResponse) => {
        // Dynamically cache static build assets and images
        if (
          networkResponse && 
          networkResponse.status === 200 && 
          (event.request.url.includes('/build/assets/') || event.request.url.includes('/images/'))
        ) {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        return networkResponse;
      }).catch(() => {
        // Network fail, silently pass
      });
    })
  );
});

// Push Notification Event Listener
self.addEventListener('push', (event) => {
  let data = { title: 'Adopta', body: 'Tienes un nuevo mensaje en el ecosistema.' };
  
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data = { title: 'Adopta', body: event.data.text() };
    }
  }

  const options = {
    body: data.body,
    icon: './images/pwa-icon.png',
    badge: './images/pwa-icon.png',
    data: data.url || './',
    vibrate: [100, 50, 100],
    actions: [
      { action: 'open', title: 'Ver Detalle' }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Notification Click Event Listener
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  let url = event.notification.data || './';
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (let client of windowClients) {
        if (client.url === url && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});
