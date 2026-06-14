const CACHE_VERSION = 'delni-public-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const IMAGE_CACHE = `${CACHE_VERSION}-images`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const OFFLINE_URL = '/offline.html';

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
    /^\/category\/[^/]+$/,
    /^\/subcategory\/[^/]+$/,
    /^\/city\/[^/]+$/,
    /^\/top-rated$/,
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
        event.respondWith(networkFirst(request, PAGE_CACHE));
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
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cached);

    return cached || refresh;
}

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        await putCache(request, response, cacheName);

        return response;
    } catch (error) {
        const cached = await caches.match(request);

        return cached || caches.match(OFFLINE_URL);
    }
}

async function putCache(request, response, cacheName) {
    if (! response || ! response.ok || response.type !== 'basic') {
        return;
    }

    const cache = await caches.open(cacheName);
    await cache.put(request, response.clone());
}
