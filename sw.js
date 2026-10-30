const CACHE_NAME = 'chetele-static-v70';
const STATIC_FILES = ['/', '/index.html', '/manifest.webmanifest', '/assets/icon-180.png', '/assets/icon-192.png', '/assets/icon-512.png', '/img/logo.png', '/assets/lottie.min.js', '/assets/deeds.js', '/assets/zikr-sync.js?v=3', '/assets/prayer-times.js?v=2', '/assets/preloader-animation.json'];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_FILES)));
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))));
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin || url.pathname.startsWith('/api/')) return;

  if (event.request.mode === 'navigate') {
    event.respondWith(fetch(event.request).then(response => {
      const copy = response.clone();
      caches.open(CACHE_NAME).then(cache => cache.put('/index.html', copy));
      return response;
    }).catch(() => caches.match('/index.html')));
    return;
  }

  /* Online users always get the newest application files. The cache is only a
     fallback for offline launch from the Home Screen. */
  event.respondWith(fetch(event.request).then(response => {
    if (response && response.ok) {
      const copy = response.clone();
      caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
    }
    return response;
  }).catch(() => caches.match(event.request)));
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil((async () => {
    const target = new URL(event.notification.data?.url || '/', self.location.origin);
    const openClients = await clients.matchAll({ type: 'window', includeUncontrolled: true });
    const existing = openClients.find(client => new URL(client.url).origin === self.location.origin);
    /* An open app is told which screen to show; a closed one starts with ?open= in the address. */
    if (existing) {
      existing.postMessage({ type: 'open', target: target.searchParams.get('open') || '' });
      return existing.focus();
    }
    return clients.openWindow(target.href);
  })());
});

self.addEventListener('push', event => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch { data = { body: event.data ? event.data.text() : '' }; }
  const title = data.title || 'Chetele';
  event.waitUntil(self.registration.showNotification(title, {
    body: data.body || 'Пора отметить сегодняшний путь ✦',
    icon: '/assets/icon-192.png',
    badge: '/assets/icon-180.png',
    tag: data.tag || 'niet-daily-reminder',
    renotify: true,
    actions: Array.isArray(data.actions) ? data.actions : [],
    data: { url: data.url || '/' },
  }));
});
