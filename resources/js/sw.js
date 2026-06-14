import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import {
    CacheFirst,
    NetworkFirst,
    StaleWhileRevalidate,
} from 'workbox-strategies';
import { CacheExpiration } from 'workbox-expiration';
import { Queue } from 'workbox-background-sync';

precacheAndRoute(self.__WB_MANIFEST || []);

// ============================================================================
// PUBLIC MARKETPLACE ROUTE ALLOWLIST
// ============================================================================
// Only cache public marketplace pages. Deny everything else explicitly.

const publicMarketplaceRoutes = [
    /^\/$/, // homepage
    /^\/search(\?.*)?$/, // search
    /^\/categories(\?.*)?$/,
    /^\/category\/[^\/]+(\?.*)?$/,
    /^\/subcategory\/[^\/]+(\?.*)?$/,
    /^\/city\/[^\/]+(\?.*)?$/,
    /^\/top-rated(\?.*)?$/,
    /^\/provider\/[^\/]+(\?.*)?$/,
    /^\/contact(\?.*)?$/,
    /^\/privacy(\?.*)?$/,
    /^\/terms(\?.*)?$/,
    /^\/disclaimer(\?.*)?$/,
];

const deniedRoutes = [
    /^\/cp\//,         // admin panel
    /^\/provider\//,   // provider panel
    /^\/login(\?.*)?$/,
    /^\/register(\?.*)?$/,
    /^\/logout(\?.*)?$/,
    /^\/forgot-password(\?.*)?$/,
    /^\/reset-password\//,
    /^\/onboarding\//,
    /^\/settings(\?.*)?$/,
    /^\/favorites(\?.*)?$/,
    /^\/dashboard(\?.*)?$/,
    /^\/account\//,
];

function isPublicMarketplacePage(url) {
    return publicMarketplaceRoutes.some((pattern) => pattern.test(url.pathname));
}

function isDeniedRoute(url) {
    return deniedRoutes.some((pattern) => pattern.test(url.pathname));
}

// ============================================================================
// STRATEGY 1: STATIC VITE ASSETS — CacheFirst (30 days)
// ============================================================================
// JS, CSS, fonts, images with Vite hashes

registerRoute(
    ({ request }) =>
        ['style', 'script', 'worker'].includes(request.destination) ||
        /\.(png|jpg|jpeg|gif|webp|svg|eot|ttf|woff|woff2)(\?.*)?$/.test(
            request.url
        ),
    new CacheFirst({
        cacheName: 'vite-assets',
        plugins: [
            new CacheExpiration({
                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
                maxEntries: 100,
            }),
        ],
    })
);

// ============================================================================
// STRATEGY 2: PROVIDER IMAGES — StaleWhileRevalidate (7 days)
// ============================================================================
// Logos, covers, portfolio images

registerRoute(
    ({ url }) =>
        url.pathname.startsWith('/storage/profiles/') ||
        url.pathname.startsWith('/storage/portfolio/'),
    new StaleWhileRevalidate({
        cacheName: 'provider-images',
        plugins: [
            new CacheExpiration({
                maxAgeSeconds: 7 * 24 * 60 * 60, // 7 days
                maxEntries: 200,
            }),
        ],
    })
);

// ============================================================================
// STRATEGY 3: API SEARCH — NetworkFirst with short timeout
// ============================================================================
// /api/profiles/search — must stay fresh

registerRoute(
    ({ url }) => url.pathname.startsWith('/api/profiles/search'),
    new NetworkFirst({
        cacheName: 'api-search',
        networkTimeoutSeconds: 3,
        plugins: [
            new CacheExpiration({
                maxAgeSeconds: 5 * 60, // 5 minutes max
                maxEntries: 50,
            }),
        ],
    })
);

// ============================================================================
// STRATEGY 4: PUBLIC MARKETPLACE HTML — NetworkFirst (short TTL)
// ============================================================================
// Homepage, category, search, provider pages — try network first

registerRoute(
    ({ request, url }) => {
        if (request.method !== 'GET') {
            return false;
        }

        if (isDeniedRoute(url)) {
            return false;
        }

        return isPublicMarketplacePage(url);
    },
    new NetworkFirst({
        cacheName: 'marketplace-pages',
        networkTimeoutSeconds: 3,
        plugins: [
            new CacheExpiration({
                maxAgeSeconds: 10 * 60, // 10 minutes max
                maxEntries: 50,
            }),
        ],
    })
);

// ============================================================================
// NEVER CACHE — NETWORK ONLY
// ============================================================================
// Admin, provider panel, auth, account, POST requests

registerRoute(
    ({ request, url }) => {
        // Never cache POST, PUT, DELETE, PATCH
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(request.method)) {
            return true;
        }

        // Never cache denied routes
        if (isDeniedRoute(url)) {
            return true;
        }

        return false;
    },
    new NetworkFirst({
        cacheName: 'network-only',
        networkTimeoutSeconds: 10,
    })
);

// ============================================================================
// OFFLINE FALLBACK
// ============================================================================
// Serve offline.html when no cached page exists

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches
            .match(event.request)
            .then((response) => {
                if (response) {
                    return response;
                }

                return fetch(event.request).catch(() => {
                    return caches
                        .open('offline-fallback')
                        .then((cache) =>
                            cache.match(new Request('/offline.html'))
                        );
                });
            })
    );
});

// ============================================================================
// PRECACHE OFFLINE PAGE
// ============================================================================

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open('offline-fallback').then((cache) => {
            return fetch('/offline.html').then((response) => {
                if (response.ok) {
                    return cache.put('/offline.html', response);
                }
            });
        })
    );
});

// ============================================================================
// CLEANUP OLD CACHES ON UPDATE
// ============================================================================

self.addEventListener('activate', (event) => {
    const cacheNames = [
        'vite-assets',
        'provider-images',
        'api-search',
        'marketplace-pages',
        'network-only',
        'offline-fallback',
    ];

    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys
                    .filter(
                        (key) =>
                            !cacheNames.includes(key) && key !== 'workbox-cache-v1'
                    )
                    .map((key) => caches.delete(key))
            );
        })
    );
});
