// Update this version string on every deploy (or use: sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js)
const CACHE_VERSION = 'delni-public-tguo5q';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const IMAGE_CACHE = `${CACHE_VERSION}-images`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const OFFLINE_URL = '/offline.html';
const IMAGE_CACHE_MAX_ENTRIES = 200;

const DENY_PATHS = [
    /^\/cp(?:\/|$)/,
    /^\/provider(?:\/|$)/,
    /^\/login$/,
    /^\/register$/,
    /^\/logout$/,
    /^\/auth(?:\/|$)/,
    /^\/forgot-password$/,
    /^\/reset-password(?:\/|$)/,
    /^\/onboarding(?:\/|$)/,
    /^\/onboarding-test(?:\/|$)/,
    /^\/account(?:\/|$)/,
    /^\/dashboard$/,
    /^\/settings$/,
    /^\/favorites(?:\/|$)/,
    /^\/api\/private(?:\/|$)/,
];

const PUBLIC_HTML_ALLOW = [
    /^\/$/,
    /^\/search$/,
    /^\/categories$/,
    /^\/category\/[^/]+(?:\/in\/[^/]+)?$/,
    /^\/subcategory\/[^/]+(?:\/in\/[^/]+)?$/,
    /^\/city\/[^/]+$/,
    /^\/top-rated(?:\/in\/[^/]+)?$/,
    /^\/providers\/[^/]+$/,
    /^\/contact$/,
    /^\/privacy$/,
    /^\/terms$/,
    /^\/disclaimer$/,
];

const isDenied = (url) => DENY_PATHS.some((pattern) => pattern.test(url.pathname));
const isPublicPage = (url) => PUBLIC_HTML_ALLOW.some((pattern) => pattern.test(url.pathname));
const isStaticAsset = (url) => url.pathname.startsWith('/build/assets/')
    || url.pathname === '/manifest.json'
    || url.pathname === '/favicon.ico'
    || /^\/images\/icon-(192|512)\.png$/.test(url.pathname);
const isPublicImage = (request, url) => request.destination === 'image'
    && (url.pathname.startsWith('/storage/')
        || url.pathname.startsWith('/icon/')
        || url.pathname.startsWith('/images/'));

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll([OFFLINE_URL, '/manifest.json', '/images/icon-192.png', '/images/icon-512.png']))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => ![STATIC_CACHE, IMAGE_CACHE, PAGE_CACHE].includes(key))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin || isDenied(url)) {
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    if (isPublicImage(request, url)) {
        event.respondWith(staleWhileRevalidate(request, IMAGE_CACHE));
        return;
    }

    if (url.pathname === '/api/profiles/search') {
        event.respondWith(fetch(request));
        return;
    }

    if (request.mode === 'navigate' && isPublicPage(url)) {
        event.respondWith(networkFirst(request, PAGE_CACHE, 10000));
    }
});

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    await putCache(request, response, cacheName);

    return response;
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    const refresh = fetch(request)
        .then((response) => {
            if (response.ok) {
                cache.put(request, response.clone()).then(() => trimCache(cache, IMAGE_CACHE_MAX_ENTRIES));
            }

            return response;
        })
        .catch(() => cached);

    return cached || refresh;
}

async function networkFirst(request, cacheName, timeoutMs = 3000) {
    const cache = await caches.open(cacheName);
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeoutMs);

    try {
        const response = await fetch(request, { signal: controller.signal });
        clearTimeout(timer);
        await putCache(request, response, cacheName);

        return response;
    } catch {
        clearTimeout(timer);

        const cached = await cache.match(request);
        if (cached) return cached;

        if (request.mode === 'navigate') return caches.match(OFFLINE_URL);

        throw new Error('Network failed and no cache available');
    }
}

async function putCache(request, response, cacheName) {
    if (! response || ! response.ok || response.type !== 'basic') {
        return;
    }

    const cache = await caches.open(cacheName);
    await cache.put(request, response.clone());
}

async function trimCache(cache, maxEntries) {
    const keys = await cache.keys();
    if (keys.length <= maxEntries) return;
    await Promise.all(keys.slice(0, keys.length - maxEntries).map((key) => cache.delete(key)));
}
