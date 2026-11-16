const CACHE_NAME = 'chetele-static-v147';
const STATIC_FILES = ['/', '/index.html', '/manifest.webmanifest', '/assets/icon-180.png', '/assets/icon-192.png', '/assets/icon-512.png', '/assets/logo-light.png', '/assets/logo-mark.png', '/assets/i18n-ky.js?v=7', '/assets/deeds.js?v=2', '/assets/hadiths.js?v=1', '/assets/fasting.js?v=1', '/assets/onboarding.js?v=2', '/assets/zikr-sync.js?v=3', '/assets/prayer-times.js?v=2', '/assets/quizzes.js?v=8', '/assets/app.js?v=2c958700', '/assets/app.css?v=0f0a6ae8'];

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

/* Launch: the files with ?v= open from the phone at once. The page itself (one small file)
   is asked from the network first, for at most PAGE_WAIT ms, so a launch after a deploy
   opens the new version straight away instead of the old one. With a slow or no network
   the saved page opens and the network keeps refreshing it in the background. */
const PAGE_WAIT = 2500;
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
  if (!saved) return (await fresh.catch(() => null)) || Response.error();
  event.waitUntil(fresh.catch(() => {}));
  const late = new Promise(resolve => setTimeout(resolve, PAGE_WAIT, null));
  const first = await Promise.race([fresh.then(r => r && r.ok && isHtml(r) ? r : null, () => null), late]);
  return first || saved;
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
  const title = data.title || 'Istikama';
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
