const CACHE_NAME = 'chetele-static-v138';
const STATIC_FILES = ['/', '/index.html', '/manifest.webmanifest', '/assets/icon-180.png', '/assets/icon-192.png', '/assets/icon-512.png', '/img/logo.png', '/assets/i18n-ky.js?v=5', '/assets/deeds.js?v=2', '/assets/hadiths.js?v=1', '/assets/fasting.js?v=1', '/assets/onboarding.js?v=1', '/assets/zikr-sync.js?v=3', '/assets/prayer-times.js?v=2', '/assets/quizzes.js?v=8'];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_FILES.map(url => new Request(url, { cache: 'reload' })))));
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
  /* Videos are fetched in ranges by Safari; a whole cached file breaks playback, so they go straight to the network. */
  if (url.pathname.endsWith('.mp4')) return;

  if (event.request.mode === 'navigate') {
    if (url.pathname === '/' || url.pathname === '/index.html') event.respondWith(openPage(event));
    return;
  }
  event.respondWith(openFile(event, url));
});

/* Launch speed: a Home Screen launch used to wait for the network for every file (about
   2 MB). Now the saved copy opens at once and the network refreshes it in the background,
   so the next launch has the newest page. Each deploy also installs a new service worker,
   which takes over and reloads the open app (see index.html), so updates still arrive. */
const MAINTENANCE_FLAG = '/__maintenance';
const isHtml = response => (response.headers.get('content-type') || '').includes('text/html');

async function openFile(event, url) {
  const cache = await caches.open(CACHE_NAME);
  const saved = await cache.match(event.request);
  /* Files with ?v= never change under the same address: no need to ask the network. */
  if (saved && url.searchParams.has('v')) return saved;
  const fresh = fetch(event.request).then(response => {
    if (response.ok && !isHtml(response)) cache.put(event.request, response.clone());
    return response;
  });
  if (saved) { event.waitUntil(fresh.catch(() => {})); return saved; }
  return fresh.catch(() => Response.error());
}

async function openPage(event) {
  const cache = await caches.open(CACHE_NAME);
  const saved = await cache.match('/index.html');
  const fresh = refreshPage(event.request, cache);
  if (saved) { event.waitUntil(fresh.catch(() => {})); return saved; }
  return (await fresh.catch(() => null)) || Response.error();
}

/* Only the real app page is kept. When the hosting answers with a page of its own (account
   switched off, gateway error), the saved app stays and is told to show the maintenance screen. */
async function refreshPage(request, cache) {
  const response = await fetch(request.url, { cache: 'no-store', credentials: 'same-origin' });
  const text = response.ok && isHtml(response) ? await response.clone().text() : '';
  if (text.includes('id="today-view"')) {
    await cache.put('/index.html', response.clone());
    await cache.delete(MAINTENANCE_FLAG);
    return response;
  }
  if (response.status === 404) return response;
  await cache.put(MAINTENANCE_FLAG, new Response('1'));
  const open = await self.clients.matchAll({ type: 'window' });
  open.forEach(client => client.postMessage({ type: 'maintenance' }));
  return (await cache.match('/index.html')) || response;
}

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
