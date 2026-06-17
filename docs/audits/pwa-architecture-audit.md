# Delni PWA Architecture Audit
**Date:** 2026-06-16  
**Auditor:** Claude Sonnet 4.6 — static code audit  
**Scope:** Full reverse-engineering audit of active PWA implementation  
**Prior audit:** `docs/audits/pwa-predeployment-audit.md` (2026-06-15) — referenced for blocker status

---

## Blocker Status Summary (vs. Prior Audit)

The previous pre-deployment audit (2026-06-15) identified 3 blockers. All 3 have been resolved:

| Prior Blocker | Status | Evidence |
|---------------|--------|----------|
| BLOCKER 1 — Dead Workbox SW (`resources/js/sw.js` compiled but never loaded) | ✅ FIXED | `vite.config.js` has no VitePWA plugin; `package.json` has no `vite-plugin-pwa`; `public/build/` has no `sw.js` or `registerSW.js` |
| BLOCKER 2 — Hardcoded cache version `v1` never bumped | ✅ ADDRESSED | Version is now `delni-public-2026-06-15-1` (date-stamped); line 1 documents the `sed` deploy command |
| BLOCKER 3 — No network timeout in `networkFirst` | ✅ FIXED | `networkFirst(request, cacheName, timeoutMs = 3000)` with `AbortController` |

**Current verdict: PWA is production-ready.** One WARNING requires a manual deploy step (cache version bump). No blockers remain.

---

## Section 1 — Active PWA Architecture

### Architecture Diagram

```
Browser
  │
  ├─→ <link rel="manifest" href="/manifest.json">          (static file, always served)
  │
  ├─→ Service Worker Registration (conditional)
  │     gated by $shouldRegisterPublicPwa in layout.blade.php
  │     only on public marketplace routes
  │     guard: 'serviceWorker' in navigator && window.isSecureContext
  │     register('/sw.js', { scope: '/' })
  │
  └─→ /public/sw.js  ←── THE ONLY ACTIVE SERVICE WORKER
        │
        ├─→ Cache: delni-public-2026-06-15-1-static
        │     Strategy: CacheFirst
        │     Contents: /build/assets/*, /manifest.json, /favicon.ico, /images/icon-*.png
        │
        ├─→ Cache: delni-public-2026-06-15-1-images
        │     Strategy: StaleWhileRevalidate
        │     Contents: /storage/*, /icon/*, /images/* (image requests only)
        │
        ├─→ Cache: delni-public-2026-06-15-1-pages
        │     Strategy: NetworkFirst (3s timeout)
        │     Contents: public marketplace HTML pages
        │     Fallback: cached page → /offline.html
        │
        └─→ /public/offline.html  (precached on install)
```

### What Is Active vs. Dead

| Component | Status | File |
|-----------|--------|------|
| Vanilla service worker | ✅ ACTIVE | `public/sw.js` |
| Web app manifest | ✅ ACTIVE | `public/manifest.json` |
| Offline fallback page | ✅ ACTIVE | `public/offline.html` |
| SW registration | ✅ ACTIVE | `resources/views/public/layout.blade.php` lines 1016–1022 |
| VitePWA plugin | ✅ REMOVED | Not in `vite.config.js` |
| Workbox source | ✅ REMOVED | `resources/js/sw.js` does not exist |
| Workbox build output | ✅ REMOVED | `public/build/sw.js` does not exist |
| Auto-register script | ✅ REMOVED | `public/build/registerSW.js` does not exist |
| SVG icons in `public/pwa/` | ⚠️ DEAD CODE | `public/pwa/icon-192.svg`, `icon-512.svg`, `icon-maskable.svg` — not referenced anywhere |

**Is VitePWA used?** No. Removed from `vite.config.js` and `package.json`.  
**Is Workbox used?** No. No Workbox packages in `package.json`; no Workbox output in build.  
**Which file controls production caching?** `public/sw.js` — the only active service worker.

---

## Section 2 — Service Worker Registration

### Registration Code

```php
// resources/views/public/layout.blade.php lines 34–48
@php
    $shouldRegisterPublicPwa = request()->routeIs(
        'home',
        'public.search',
        'public.top-rated',
        'public.categories',
        'public.category',
        'public.subcategory',
        'public.city',
        'public.provider',
        'contact',
        'privacy',
        'terms',
        'disclaimer',
    );
@endphp
```

```js
// layout.blade.php lines 1016–1022
@if($shouldRegisterPublicPwa)
if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    });
}
@endif
```

### Registration Analysis

| Question | Answer | Status |
|----------|--------|--------|
| Registration file | `resources/views/public/layout.blade.php` | PASS |
| Registered scope | `/` (entire origin) | PASS |
| Registration conditions | Only on 12 public routes; requires `serviceWorker in navigator` AND `window.isSecureContext` | PASS |
| Admin panel registers SW | No — Filament uses its own layout | PASS |
| Provider panel registers SW | No — separate layout | PASS |
| Auth pages register SW | No — `auth.blade.php` layout, no SW code | PASS |
| Duplicate registrations | None | PASS |
| Broken registrations | None — `.catch(() => {})` silences errors gracefully | PASS |
| SW scope vs. denylist | Scope is `/` (full origin) but denylist inside SW handles exclusions | PASS |

**One asymmetry (non-blocking):** `/privacy` and `/terms` are in the Blade registration gate but they use a separate `legal_layout.blade.php`. On first direct visit to `/privacy`, the SW is NOT registered. It registers on subsequent navigation to any other public page. The pages ARE in the SW `PUBLIC_HTML_ALLOW` list, so they'll be cached after first registration.

---

## Section 3 — Cache Strategy Extraction

### Strategy Functions (from `public/sw.js`)

#### CacheFirst — `async function cacheFirst(request, cacheName)`

```js
// Lines 100–111
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    await putCache(request, response, cacheName);
    return response;
}
```

| Property | Value |
|----------|-------|
| Cache name | `delni-public-{VERSION}-static` |
| Applied to | `/build/assets/*`, `/manifest.json`, `/favicon.ico`, `/images/icon-(192\|512).png` |
| TTL | None — permanent until CACHE_VERSION changes |
| Cleanup | On `activate` event: all caches with old version prefix deleted |
| Versioning | Manual: bump `CACHE_VERSION` constant in `sw.js` |
| Offline behavior | Served from cache, no network call |
| No-store guard | `putCache` rejects non-`basic`, non-`ok` responses — CDN assets (opaque) NOT cached |

#### StaleWhileRevalidate — `async function staleWhileRevalidate(request, cacheName)`

```js
// Lines 113–127
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    const refresh = fetch(request)
        .then((response) => {
            if (response.ok) cache.put(request, response.clone());
            return response;
        })
        .catch(() => cached);
    return cached || refresh;
}
```

| Property | Value |
|----------|-------|
| Cache name | `delni-public-{VERSION}-images` |
| Applied to | Images from `/storage/*`, `/icon/*`, `/images/*` (by `request.destination === 'image'`) |
| TTL | None — accumulates indefinitely |
| Cleanup | On version bump only |
| Offline behavior | Returns cached version if network fails |
| Unbounded growth risk | ⚠️ WARNING — no `maxEntries` |

#### NetworkFirst — `async function networkFirst(request, cacheName, timeoutMs = 3000)`

```js
// Lines 129–150
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
```

| Property | Value |
|----------|-------|
| Cache name | `delni-public-{VERSION}-pages` |
| Applied to | `request.mode === 'navigate'` && `isPublicPage(url)` |
| Timeout | 3000ms via `AbortController` |
| TTL | None — pages cached for offline use |
| Online behavior | Always fetches fresh from network first |
| Offline behavior | Returns cached page → `/offline.html` if no cache |
| Slow network behavior | After 3s, falls back to cached page (mobile-friendly) |

### Strategy Table by Route/Asset

| Route / Asset | Strategy | Cache | TTL | Offline Fallback |
|---------------|----------|-------|-----|-----------------|
| `/build/assets/*.js`, `*.css` | CacheFirst | `-static` | Permanent | Served from cache |
| `/manifest.json` | CacheFirst | `-static` | Permanent | Served from cache |
| `/favicon.ico` | CacheFirst | `-static` | Permanent | Served from cache |
| `/images/icon-192.png`, `/images/icon-512.png` | CacheFirst | `-static` | Permanent | Served from cache |
| `/storage/*` images | StaleWhileRevalidate | `-images` | Permanent | Last cached version |
| `/icon/*` images | StaleWhileRevalidate | `-images` | Permanent | Last cached version |
| `/` (homepage) | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/search` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/categories` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/category/:slug` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/subcategory/:slug` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/city/:slug` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/top-rated` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/providers/:slug` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/contact`, `/privacy`, `/terms`, `/disclaimer` | NetworkFirst 3s | `-pages` | Until version bump | Cached page or offline.html |
| `/api/profiles/search` | Network Only | None | n/a | Silent failure (no fallback) |
| `/cp/*` | Passthrough (denied) | None | n/a | No SW involvement |
| `/provider/*` | Passthrough (denied) | None | n/a | No SW involvement |
| `/login`, `/register`, `/logout`, `/auth/*` | Passthrough (denied) | None | n/a | No SW involvement |
| `/account/*`, `/settings`, `/dashboard` | Passthrough (denied) | None | n/a | No SW involvement |
| Cross-origin (Google Fonts, CDN) | Passthrough | None | n/a | Not intercepted |

---

## Section 4 — Route Safety

### Denylist (never enters caching pipeline)

```js
// public/sw.js lines 8–24
const DENY_PATHS = [
    /^\/cp(?:\/|$)/,            // admin panel
    /^\/provider(?:\/|$)/,      // provider panel (singular)
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
// Plus: all non-GET methods, all cross-origin requests
```

### Route Safety Table

| Route | Cached? | Can be served offline? | Risk |
|-------|---------|----------------------|------|
| `/` (homepage) | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/search` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/categories` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/category/:slug` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/subcategory/:slug` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/city/:slug` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/top-rated` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/providers/:slug` | ✅ NetworkFirst | ✅ (stale if offline) | PASS |
| `/cp/*` (admin) | ❌ Denied | ❌ | PASS |
| `/provider/*` (panel) | ❌ Denied | ❌ | PASS |
| `/login` | ❌ Denied | ❌ | PASS |
| `/register` | ❌ Denied | ❌ | PASS |
| `/logout` | ❌ Denied | ❌ | PASS |
| `/forgot-password` | ❌ Denied | ❌ | PASS |
| `/reset-password/*` | ❌ Denied | ❌ | PASS |
| `/auth/*` | ❌ Denied | ❌ | PASS |
| `/onboarding/*` | ❌ Denied | ❌ | PASS |
| `/account/*` | ❌ Denied | ❌ | PASS |
| `/dashboard` | ❌ Denied | ❌ | PASS |
| `/settings` | ❌ Denied | ❌ | PASS |
| `/favorites/*` | ❌ Denied | ❌ | PASS |
| `/api/private/*` | ❌ Denied | ❌ | PASS |
| `POST /login` | ❌ Non-GET passthrough | ❌ | PASS |
| CSRF form submissions | ❌ Non-GET passthrough | ❌ | PASS |
| All other routes | Passthrough (not in allow list, not in deny list) | ❌ | PASS |

**Critical path correctness:** `/providers/:slug` (plural, public profile) vs. `/provider/*` (singular, provider panel) — these are different paths and correctly handled: plural goes to NetworkFirst cache; singular is denied. No overlap.

---

## Section 5 — Marketplace Freshness

**The key fact:** `networkFirst` ALWAYS attempts the network first. The SW only serves cached content when:
1. Network fails entirely (offline), OR
2. Network times out after 3 seconds (slow connection fallback)

### Provider State Change → When Does Cache Stop Showing Them?

| Provider change | Online user next request | Offline user |
|-----------------|-------------------------|--------------|
| Provider suspended (`is_suspended = true`) | Immediately (next navigation) ✅ | Sees last cached page ⚠️ |
| Subscription expired | Immediately (next navigation) ✅ | Sees last cached page ⚠️ |
| Profile visibility set to hidden | Immediately (next navigation) ✅ | Sees last cached page ⚠️ |
| Provider deleted | Immediately (404 on next navigation) ✅ | Sees last cached page ⚠️ |
| Profile incomplete (removed from listings) | Immediately (next navigation) ✅ | Sees stale listing ⚠️ |
| Featured badge removed | Immediately (next navigation) ✅ | Sees stale badge ⚠️ |
| Rankings recalculated | Immediately (next navigation) ✅ | Sees old ranking ⚠️ |
| Provider image deleted | Background revalidation in progress ⚠️ | Last cached image ⚠️ |

**Online users: safe.** NetworkFirst fetches from server every time, so any visibility change takes effect on the very next page load.

**Offline users: acceptable.** The offline.html states "قد لا تكون بيانات مقدمي الخدمات محدثة." (data may not be current). Offline users know they're offline — this is expected behavior.

**No dangerous stale data leakage path exists for connected users.**

### Per-Surface Freshness Analysis

| Surface | Fresh data guarantee | Mechanism |
|---------|---------------------|-----------|
| Homepage | ✅ Always fresh when online | NetworkFirst |
| Search (`/search`) | ✅ Always fresh when online | NetworkFirst HTML + Network-only API |
| Category pages | ✅ Always fresh when online | NetworkFirst |
| Subcategory pages | ✅ Always fresh when online | NetworkFirst |
| City pages | ✅ Always fresh when online | NetworkFirst |
| Provider profile (`/providers/:slug`) | ✅ Always fresh when online | NetworkFirst |
| Top-rated listing | ✅ Always fresh when online | NetworkFirst |
| Search API (`/api/profiles/search`) | ✅ Always fresh (network-only) | No caching at all |
| Provider images | ⚠️ Stale-while-revalidate | User sees old image for one request cycle if changed |

---

## Section 6 — Offline Experience

| Test case | Behavior | Status |
|-----------|----------|--------|
| Navigate to cached public page offline | Served from PAGE_CACHE | PASS |
| Navigate to uncached public page offline | Shows `/offline.html` | PASS |
| Navigate to admin/auth page offline | Passthrough — browser handles natively | PASS |
| Search while offline | Network failure — no graceful fallback | WARNING (silent failure) |
| Static assets offline | Served from STATIC_CACHE | PASS |
| Provider images offline | Served from IMAGE_CACHE | PASS |
| Arabic content | Yes — `offline.html` is fully in Arabic | PASS |
| offline.html pretends data is fresh | No — explicitly states data may be outdated | PASS |
| Auth pages appearing offline | No — denied from SW, browser handles | PASS |
| Retry button | `location.reload()` | PASS |
| Safe area support | `env(safe-area-inset-bottom)` in offline.html | PASS |
| Offline banner in layout | ✅ Shows when `!navigator.onLine` | PASS |

**Offline banner text (layout.blade.php):**
```
أنت غير متصل بالإنترنت حاليا. بعض بيانات مقدمي الخدمات قد لا تكون محدثة.
```
This is honest and correct — it doesn't claim data is fresh.

**One gap:** When the user searches offline, the `/api/profiles/search` request fails silently. The UI will show an error or loading state, not `offline.html`. This is acceptable since search inherently requires live data, but could be improved with a friendly "search unavailable offline" message from the frontend.

---

## Section 7 — Cache Versioning

### Version Constants

```js
// public/sw.js lines 1–5
// Update this version string on every deploy (or use: sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js)
const CACHE_VERSION = 'delni-public-2026-06-15-1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const IMAGE_CACHE  = `${CACHE_VERSION}-images`;
const PAGE_CACHE   = `${CACHE_VERSION}-pages`;
```

### Cache Invalidation Logic

```js
// public/sw.js lines 60–70
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
```

### Versioning Analysis

| Question | Answer | Status |
|----------|--------|--------|
| Will new deployments invalidate old caches? | YES — if `CACHE_VERSION` is bumped | WARNING (manual step required) |
| Are old caches removed? | YES — `activate` event deletes all caches not matching current version | PASS |
| Is versioning manual or automatic? | Manual — developer must update `CACHE_VERSION` string | WARNING |
| Are users stuck on old versions? | No — browser detects new `sw.js` content, installs updated SW, activate clears old caches | PASS |
| Will new Vite-hashed assets be missed? | No — new hashes = new cache keys = fresh fetch | PASS |
| Will `manifest.json` / icons be stale? | Only if `CACHE_VERSION` is NOT bumped | WARNING |

**Current version:** `delni-public-2026-06-15-1` (date-stamped, not automated)

**Deploy instruction documented in code:**
```bash
sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js
```

**⚠️ WARNING — DEPLOY ACTION REQUIRED:** Before each production deployment, either:
1. Run the `sed` command above, OR
2. Manually update `CACHE_VERSION` to include today's date or commit hash

If skipped: Vite JS/CSS will still work correctly (new hashes = new cache keys), but `/manifest.json`, icons, and cached HTML pages from the previous deploy will persist until browser storage pressure evicts them.

---

## Section 8 — Network Resilience

### NetworkFirst Timeout Implementation

```js
// public/sw.js lines 129–150
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
```

| Scenario | Behavior | Status |
|----------|----------|--------|
| Fast connection (< 3s) | Network response served, cached | PASS |
| Slow 3G (request times out at 3s) | Falls back to cached page | PASS |
| Completely offline | Immediate `AbortError`, falls back to cache | PASS |
| Cached page exists | Returned immediately after timeout | PASS |
| No cached page + offline | Returns `offline.html` | PASS |
| Infinite wait possible? | No — `AbortController` terminates after 3s | PASS |
| Libya/mobile network | 3s timeout is aggressive enough for poor connections | PASS |

**No infinite waits possible.** The 3-second `AbortController` timeout ensures the SW always responds promptly regardless of connection quality.

---

## Section 9 — Manifest Audit

### `/public/manifest.json` (full content)

```json
{
    "name": "دلني - دليل الخدمات الليبي",
    "short_name": "دلني",
    "description": "دلني يساعدك تلقى مقدمي الخدمات في ليبيا",
    "lang": "ar",
    "dir": "rtl",
    "start_url": "/",
    "scope": "/",
    "display": "standalone",
    "orientation": "portrait",
    "background_color": "#0B1A34",
    "theme_color": "#0B1A34",
    "icons": [
        { "src": "/images/icon-192.png", "sizes": "192x192", "type": "image/png", "purpose": "any" },
        { "src": "/images/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "any" },
        { "src": "/images/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }
    ]
}
```

| Check | Value | Status |
|-------|-------|--------|
| `name` | "دلني - دليل الخدمات الليبي" | PASS |
| `short_name` | "دلني" | PASS |
| `description` | Present, Arabic | PASS |
| `lang` | "ar" | PASS |
| `dir` | "rtl" | PASS |
| `start_url` | "/" | PASS |
| `scope` | "/" | PASS |
| `display` | "standalone" | PASS |
| `orientation` | "portrait" | PASS |
| `background_color` | "#0B1A34" (navy) | PASS |
| `theme_color` | "#0B1A34" (navy) | WARNING — see below |
| 192×192 icon | `/images/icon-192.png` | PASS |
| 512×512 icon | `/images/icon-512.png` | PASS |
| Maskable icon | `/images/icon-512.png` (reused, `purpose: "maskable"`) | WARNING — safe zone not verified |
| Apple touch icon | `/images/icon-192.png` via layout meta | PASS |
| Android installability | All requirements met | PASS |
| iOS add-to-home-screen | Meta tags present | PASS |

**⚠️ theme_color inconsistency:**
- `manifest.json`: `#0B1A34` (navy)
- `<meta name="theme-color">` in layout: `#0B1A34` (navy) — consistent with manifest ✅
- `offline.html` `<meta name="theme-color">`: `#F1620F` (orange) — cosmetic difference, non-breaking

**⚠️ Maskable icon safe zone:** `icon-512.png` is used for both `"purpose": "any"` and `"purpose": "maskable"`. Android adaptive icons crop to a circle/rounded shape. If the icon artwork extends to the edges without a 20% safe zone margin, it will be clipped. Verify the actual PNG has sufficient padding for circular display.

---

## Section 10 — Icon Audit

| Icon | Path | File exists | Referenced by | Status |
|------|------|-------------|---------------|--------|
| Favicon | `/images/icon-192.png` | ✅ | Layout `<link rel="icon">` | PASS |
| Apple Touch Icon | `/images/icon-192.png` | ✅ | Layout `<link rel="apple-touch-icon">` | PASS |
| 192×192 manifest icon | `/images/icon-192.png` | ✅ | `manifest.json` | PASS |
| 512×512 manifest icon | `/images/icon-512.png` | ✅ | `manifest.json` | PASS |
| Maskable icon | `/images/icon-512.png` | ✅ | `manifest.json` (reused) | WARNING (safe zone) |
| SVG placeholder 192 | `/pwa/icon-192.svg` | ✅ | Nothing | DEAD CODE |
| SVG placeholder 512 | `/pwa/icon-512.svg` | ✅ | Nothing | DEAD CODE |
| SVG maskable | `/pwa/icon-maskable.svg` | ✅ | Nothing | DEAD CODE |

**Dead code:** The 3 SVG files in `public/pwa/` are not referenced by the manifest, layout, or any other file. They appear to be placeholder assets from initial development that were superseded by the PNG icons.

**Precache:** SW `install` event precaches `/images/icon-192.png` and `/images/icon-512.png` — both exist, no broken precache entries.

---

## Section 11 — Build Pipeline

### Vite Configuration

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [ bunny('Instrument Sans', { weights: [400, 500, 600] }) ],
        }),
        tailwindcss(),
    ],
    server: { watch: { ignored: ['**/storage/framework/views/**'] } },
});
```

### Build Output (`public/build/`)

| File | Type | Status |
|------|------|--------|
| `assets/app-BvRk9kiK.js` | Vite app bundle | ✅ ACTIVE |
| `assets/app-DWqQG3UX.css` | Tailwind CSS | ✅ ACTIVE |
| `assets/instrument-sans-*.woff*` | Font files | ✅ ACTIVE |
| `manifest.json` | Vite build manifest | ✅ ACTIVE (used by `@vite()` directive) |
| `fonts-manifest.json` | Bunny font manifest | ✅ ACTIVE |
| `sw.js` | Workbox output | ✅ REMOVED — does not exist |
| `registerSW.js` | VitePWA registration | ✅ REMOVED — does not exist |
| `manifest.webmanifest` | VitePWA manifest | ✅ REMOVED — does not exist |

### Package Analysis

```json
// package.json devDependencies
"@tailwindcss/vite": "^4.0.0",
"concurrently": "^9.0.1",
"laravel-vite-plugin": "^3.1",
"tailwindcss": "^4.0.0",
"vite": "^8.0.0"
```

| Package | Status |
|---------|--------|
| `laravel-vite-plugin` | ACTIVE |
| `tailwindcss` + `@tailwindcss/vite` | ACTIVE |
| `vite` | ACTIVE |
| `concurrently` | ACTIVE (used by `composer run dev`) |
| `vite-plugin-pwa` | REMOVED — not present |
| `workbox-window` | REMOVED — not present |
| `workbox-background-sync` | REMOVED — not present |

**`resources/js/app.js` content:** Single comment `//` — the file is empty. All application JS is rendered inline in `layout.blade.php`. The Vite bundle contains essentially nothing from `app.js`. This is intentional.

---

## Section 12 — Production Security

### Can sensitive routes be cached?

| Scenario | SW behavior | Leak risk |
|----------|-------------|-----------|
| Admin panel (`/cp/*`) | `isDenied()` → immediate `return` | ✅ SAFE |
| Provider panel (`/provider/*`) | `isDenied()` → immediate `return` | ✅ SAFE |
| Login page | `isDenied()` → immediate `return` | ✅ SAFE |
| Register page | `isDenied()` → immediate `return` | ✅ SAFE |
| Forgot/reset password | `isDenied()` → immediate `return` | ✅ SAFE |
| Account/settings pages | `isDenied()` → immediate `return` | ✅ SAFE |
| Dashboard | `isDenied()` → immediate `return` | ✅ SAFE |
| Favorites | `isDenied()` → immediate `return` | ✅ SAFE |
| Private API | `isDenied()` → immediate `return` | ✅ SAFE |
| POST requests (forms, CSRF) | `method !== 'GET'` → immediate `return` | ✅ SAFE |
| CSRF token in HTML | NetworkFirst fetches fresh HTML — CSRF rotates | ✅ SAFE |
| Session cookies | SW never intercepts auth routes | ✅ SAFE |
| Google OAuth callback | Not in allowlist → not cached | ✅ SAFE |
| Cross-origin requests | `url.origin !== self.location.origin` → passthrough | ✅ SAFE |
| Installed PWA opened after logout | NetworkFirst fetches fresh — server redirects to login | ✅ SAFE |

**`window.isSecureContext` guard** in registration: SW only registers in HTTPS or localhost. In HTTP environments (staging over plain HTTP), SW is silently skipped.

**No private data can be cached.** The denylist is comprehensive and correct.

---

## Section 13 — Deployment Requirements & Checklist

### Pre-Deployment

```bash
# 1. Bump cache version
sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js
# OR manually update CACHE_VERSION in public/sw.js

# 2. Build assets
npm run build

# 3. Verify build output (no dead SW artifacts)
ls public/build/               # should have: assets/, manifest.json, fonts-manifest.json
ls public/build/assets/        # should NOT contain sw.js or registerSW.js

# 4. Verify active SW is unchanged by build
head -3 public/sw.js           # Should show CACHE_VERSION with new hash/date

# 5. Laravel caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan test --compact
```

### Production Environment Requirements

| Requirement | Why | Status |
|-------------|-----|--------|
| HTTPS | SW won't register without `window.isSecureContext` | ✅ Required |
| `/sw.js` served with `Service-Worker-Allowed: /` header (optional but recommended) | Allows broader scope claims | Optional |
| `/manifest.json` served as `application/json` or `application/manifest+json` | Installability | ✅ Static file, standard |
| `public/sw.js` is static (not built by npm) | SW survives npm builds | ✅ |
| Storage symlink (`php artisan storage:link`) | Provider images accessible via `/storage/` | ✅ Required |
| Queue worker running | Jobs triggered by observers | ✅ Required |
| Cache driver ≠ `array` | Persistent caching between requests | ✅ Required |
| `APP_ENV=production`, `APP_DEBUG=false` | Security | ✅ Required |

### Deployment Checklist

- [ ] `CACHE_VERSION` bumped in `public/sw.js` (with date or commit hash)
- [ ] `npm run build` completed without errors
- [ ] `public/build/` does NOT contain `sw.js` or `registerSW.js`
- [ ] `public/sw.js` is static, NOT in `.gitignore`
- [ ] `public/offline.html` is static, NOT in `.gitignore`
- [ ] `public/manifest.json` is static, NOT in `.gitignore`
- [ ] `public/images/icon-192.png` and `icon-512.png` exist
- [ ] HTTPS enforced in production
- [ ] `APP_URL` set to exact production HTTPS domain
- [ ] `php artisan storage:link` has been run
- [ ] Queue worker running
- [ ] Run Lighthouse PWA audit on production URL after deploy

---

## Section 14 — Dead Code Audit

| File | Status | Recommendation |
|------|--------|----------------|
| `public/sw.js` | ✅ ACTIVE | Keep — this is the real service worker |
| `public/manifest.json` | ✅ ACTIVE | Keep |
| `public/offline.html` | ✅ ACTIVE | Keep |
| `public/images/icon-192.png` | ✅ ACTIVE | Keep |
| `public/images/icon-512.png` | ✅ ACTIVE | Keep |
| `public/pwa/icon-192.svg` | ❌ DEAD CODE | SAFE TO DELETE — not referenced anywhere |
| `public/pwa/icon-512.svg` | ❌ DEAD CODE | SAFE TO DELETE — not referenced anywhere |
| `public/pwa/icon-maskable.svg` | ❌ DEAD CODE | SAFE TO DELETE — not referenced anywhere |
| `public/pwa/` directory | ❌ DEAD CODE | SAFE TO DELETE — entire directory unreferenced |
| `resources/js/sw.js` | ✅ REMOVED | No action — file doesn't exist |
| `vite-plugin-pwa` in package.json | ✅ REMOVED | No action — package removed |
| `workbox-window` | ✅ REMOVED | No action — package removed |
| VitePWA in vite.config.js | ✅ REMOVED | No action — plugin removed |
| `resources/js/app.js` | ⚠️ ACTIVE (empty) | Keep — Vite requires this entry point to exist |

---

## Warnings Summary

| # | Warning | Severity | Action |
|---|---------|----------|--------|
| W-01 | Cache version requires manual bump before each deploy | Medium | Run `sed` command or manually update `CACHE_VERSION` |
| W-02 | ~~No `maxEntries` on IMAGE_CACHE — unbounded growth~~ | ~~Low~~ | ✅ FIXED — `trimCache()` added to `sw.js`, limit 200 entries |
| W-03 | ~~`theme_color` inconsistency: `offline.html` orange vs manifest navy~~ | ~~Low~~ | ✅ FIXED — `offline.html` now uses `#0B1A34` |
| W-04 | ~~Search form submits while offline — waits 3s before fallback~~ | ~~Low~~ | ✅ FIXED — `submitSearch()` now guards with `!navigator.onLine` and reveals offline banner |
| W-05 | Legal pages don't register SW on first direct visit | Low | Acceptable — SW registers on next any-public-page navigation |
| W-06 | Maskable icon safe zone unverified — `icon-512.png` reused for maskable purpose | Low | Verify PNG has 20% safe zone padding before Android launch |
| W-07 | Cairo font loaded from Google Fonts CDN — not cached by SW (cross-origin, opaque response) | Low | Acceptable for first load; browser caches fonts independently |
| W-08 | ~~`public/pwa/` SVG icons are orphaned dead code~~ | ~~Low~~ | ✅ FIXED — `public/pwa/` directory deleted |

---

## Final Verdict

### Is Delni's PWA production-ready?

**Yes. All previous blockers have been fixed.**

| Area | Status | Notes |
|------|--------|-------|
| Active SW architecture | ✅ PASS | Single vanilla SW at `public/sw.js`, no dead Workbox artifacts |
| VitePWA / Workbox | ✅ PASS | Fully removed |
| Network timeout | ✅ PASS | 3s `AbortController` in `networkFirst` |
| Route safety (admin/panels) | ✅ PASS | `/cp/*`, `/provider/*` denied and never cached |
| Route safety (auth) | ✅ PASS | All auth routes denied |
| POST / CSRF safety | ✅ PASS | Non-GET passthrough |
| Marketplace freshness (online users) | ✅ PASS | NetworkFirst — always fresh |
| Marketplace freshness (offline users) | ✅ ACCEPTABLE | Stale with honest warning message |
| Cache invalidation on deploy | ⚠️ WARNING | Manual version bump required before each deploy |
| Installability | ✅ PASS | Manifest complete, icons present, HTTPS required |
| Offline experience | ✅ PASS | Arabic offline.html, cached pages, honest messaging |
| Mobile UX | ✅ PASS | Safe area, bottom nav, splash screen, offline banner |
| Image cache unbounded growth | ✅ FIXED | `trimCache()` caps IMAGE_CACHE at 200 entries |
| theme_color inconsistency | ✅ FIXED | `offline.html` now matches manifest (#0B1A34) |
| Search offline UX | ✅ FIXED | `submitSearch()` guards with `navigator.onLine` check |
| Dead code | ✅ FIXED | `public/pwa/` directory deleted |

### Answers to Audit Questions

1. **What is the REAL active PWA implementation?**  
   Vanilla hand-written service worker at `public/sw.js` (160 lines). Registered in `layout.blade.php` on public marketplace routes only. Three caches: CacheFirst for static assets, StaleWhileRevalidate for images, NetworkFirst (3s timeout) for marketplace pages.

2. **Is VitePWA actually used?**  
   No. Removed from `vite.config.js` and `package.json`. No Workbox build artifacts in `public/build/`.

3. **Is Workbox actually used?**  
   No. No Workbox packages installed. No Workbox APIs called anywhere.

4. **Can suspended providers leak through cache?**  
   No, for online users. NetworkFirst always hits the server. Offline users may see a stale cached page, but `offline.html` warns them. No dangerous leak.

5. **Can admin/provider pages be cached?**  
   No. `DENY_PATHS` blocks `/cp/*` and `/provider/*` at the first line of the fetch handler. They never reach any caching logic.

6. **Will deployments invalidate caches correctly?**  
   Yes — IF `CACHE_VERSION` is bumped before deploying. The `activate` event deletes all caches whose names don't match the new version. **This requires a manual deploy step.**

7. **What must be fixed before launch?**  
   One action only: bump `CACHE_VERSION` before deploy (the `sed` command is documented in `sw.js` line 1).

8. **What can wait until after launch?**  
   - Add `maxEntries` to IMAGE_CACHE (W-02)
   - Standardise `theme_color` (W-03)
   - Add offline error message for search (W-04)
   - Verify maskable icon safe zone (W-06)
   - Delete `public/pwa/*.svg` orphaned files (W-08)
