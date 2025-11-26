const CACHE_NAME = 'glintlabs-cache-v6';
const PING_DB = 'glint-nav';
const PING_STORE = 'pings';
const CORE_ASSETS = [
  '/manifest.webmanifest'
];

const CACHEABLE_DESTINATIONS = new Set(['style', 'script', 'font', 'image']);

function isCacheableResponse(response) {
  if (!response) return false;
  if (response.type === 'opaqueredirect') return false;
  if (response.redirected) return false;
  if (response.status !== 200) return false;
  return true;
}

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(CORE_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
      .then(() => self.clients.claim())
      .then(() => flushQueuedPings())
  );
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method === 'POST' && url.pathname === '/api/tracking/ping') {
    event.respondWith(handleTrackingPing(request));
    return;
  }

  if (request.method !== 'GET') {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(fetch(request));
    return;
  }

  if (url.origin !== self.location.origin) return;
  if (request.destination && !CACHEABLE_DESTINATIONS.has(request.destination)) return;

  event.respondWith(cacheFirst(request));
});

self.addEventListener('sync', event => {
  if (event.tag === 'glint-pings-sync') {
    event.waitUntil(flushQueuedPings());
  }
});

async function handleTrackingPing(request) {
  try {
    const response = await fetch(request.clone());
    return response;
  } catch (error) {
    try {
      const payload = await request.clone().json();
      await enqueuePing({
        url: request.url,
        body: payload,
        headers: Object.fromEntries(request.headers.entries()),
        ts: Date.now(),
      });
      await schedulePingSync();
    } catch (queueError) {
      console.warn('queue ping failed', queueError);
    }

    return new Response(JSON.stringify({ queued: true }), {
      status: 202,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

async function cacheFirst(request) {
  const cache = await caches.open(CACHE_NAME);
  const match = await cache.match(request);

  if (match && match.status === 200 && !match.redirected && match.type !== 'opaqueredirect') {
    return match;
  }

  try {
    const response = await fetch(request);
    if (isCacheableResponse(response)) {
      cache.put(request, response.clone());
    } else if (match) {
      cache.delete(request);
    }
    return response;
  } catch (error) {
    return match ?? Response.error();
  }
}

function openPingDb() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(PING_DB, 1);
    request.onupgradeneeded = () => {
      const db = request.result;
      if (!db.objectStoreNames.contains(PING_STORE)) {
        db.createObjectStore(PING_STORE, { autoIncrement: true });
      }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

async function enqueuePing(record) {
  const db = await openPingDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(PING_STORE, 'readwrite');
    tx.objectStore(PING_STORE).add(record);
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
}

async function dequeueAllPings() {
  const db = await openPingDb();
  const items = await new Promise((resolve, reject) => {
    const tx = db.transaction(PING_STORE, 'readonly');
    const store = tx.objectStore(PING_STORE);
    const request = store.getAll();
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
  await new Promise((resolve, reject) => {
    const tx = db.transaction(PING_STORE, 'readwrite');
    tx.objectStore(PING_STORE).clear();
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
  return items;
}

async function flushQueuedPings() {
  const queued = await dequeueAllPings();
  if (!queued.length) return;

  for (const record of queued) {
    try {
      await fetch(record.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          ...record.headers,
          'X-Queued': '1',
        },
        body: JSON.stringify(record.body),
      });
    } catch (error) {
      await enqueuePing(record);
      break;
    }
  }
}

async function schedulePingSync() {
  if ('SyncManager' in self.registration) {
    try {
      await self.registration.sync.register('glint-pings-sync');
      return;
    } catch (error) {
      console.warn('sync register failed', error);
    }
  }
  setTimeout(() => {
    flushQueuedPings();
  }, 5000);
}
