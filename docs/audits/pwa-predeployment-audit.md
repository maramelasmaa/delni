# Delni PWA Pre-Deployment Audit
**Date:** 2026-06-15  
**Auditor:** Claude Sonnet 4.6 — static code audit  
**Deployment target:** ~3 days  
**Status: ⚠️ CONDITIONAL — 3 blockers must be fixed before go-live**

---

## Executive Summary

The Delni PWA is architecturally sound for a marketplace deployment. Route safety, auth/panel exclusion, and offline experience are all correct. However, three issues must be resolved before deployment:

1. **Dead Workbox SW creates permanent maintenance trap** — any future change to `resources/js/sw.js` has zero effect in production
2. **SW cache version is hardcoded** — deploys will not bust stale caches
3. **No network timeout in NetworkFirst** — slow connections degrade UX silently

---

## 1. Installability Status

| Check | Status | Notes |
|-------|--------|-------|
| Manifest linked in layout | ✅ | `/manifest.json` via `<link rel="manifest">` |
| Manifest icons exist | ✅ | `/images/icon-192.png`, `/images/icon-512.png` (PNG, correct) |
| `display: standalone` | ✅ | Set in `/manifest.json` |
| `start_url` | ✅ | `/` |
| `scope` | ✅ | `/` |
| `lang` + `dir` | ✅ | `"lang": "ar"`, `"dir": "rtl"` |
| Apple meta tags | ✅ | `apple-mobile-web-app-capable`, `apple-mobile-web-app-status-bar-style` |
| Apple touch icon | ✅ | `/images/icon-192.png` |
| HTTPS required | ⚠️ | Not verifiable locally — must confirm on Laravel Cloud |
| Maskable icon | ⚠️ | `/manifest.json` uses `icon-512.png` as maskable — check it has safe zone padding |
| `theme_color` inconsistency | ⚠️ | `/manifest.json` uses `#0B1A34` (navy), `<meta name="theme-color">` also navy, but `offline.html` + Vite manifest use `#F1620F` (orange). Inconsistent but non-breaking. |

**Verdict:** Installable. iOS Safari add-to-home-screen will work. Android Chrome install prompt will trigger.

---

## 2. Service Worker Architecture — CRITICAL FINDING

### Two SWs exist. Only one is active.

| File | Size | Status |
|------|------|--------|
| `/public/sw.js` | 4 KB | ✅ **ACTIVE** — registered by Blade layout at scope `/` |
| `/public/build/sw.js` | 25 KB | ❌ **DEAD CODE** — Workbox-compiled, scope `/build/`, never loaded |
| `/public/build/registerSW.js` | 146 B | ❌ **NOT LOADED** — not imported anywhere in app bundle |

**Root cause:** `resources/js/app.js` is empty (`//`). VitePWA injects auto-registration into the entry point, but since the entry point is empty and Vite tree-shakes the virtual import, `registerSW.js` is never bundled into the page. The layout registers `/sw.js` manually.

**Consequence:** `resources/js/sw.js` (the Workbox source) is compiled on every `npm run build` but produces a dead artifact. Any developer who edits `resources/js/sw.js` expecting to change SW behavior will see zero effect in production.

### BLOCKER 1 — Dead Workbox SW must be resolved

**Option A (Recommended):** Remove VitePWA plugin from `vite.config.js` entirely since the vanilla `/public/sw.js` is the real SW. The Vite manifest is already unused.

**Option B:** Wire VitePWA properly — import the virtual module in `app.js`, change layout to not register `/sw.js` manually, and migrate caching logic into `resources/js/sw.js`.

Do not ship with both files present and the Workbox one silently dead.

### Workbox SW has a route conflict bug (dead code but must be fixed if activated)

`resources/js/sw.js` denylist has `/^\/provider\//` (singular) which would match `/provider/slug` public profiles. The allow list also has `/^\/provider\/[^\/]+(\?.*)?$/` (singular). The denylist check fires first — public profiles would never be cached. This doesn't affect production today because this SW is inactive.

---

## 3. Route Safety Verification — ACTIVE SW (`/public/sw.js`)

### Denylist (network-passthrough, never cached)

| Route | Pattern | Status |
|-------|---------|--------|
| `/cp/*` (admin panel) | `/^\/cp(?:\/\|$)/` | ✅ |
| `/provider/*` (provider panel) | `/^\/provider(?:\/\|$)/` | ✅ |
| `/login` | `/^\/login$/` | ✅ |
| `/register` | `/^\/register$/` | ✅ |
| `/logout` | `/^\/logout$/` | ✅ |
| `/forgot-password` | `/^\/forgot-password$/` | ✅ |
| `/reset-password/*` | `/^\/reset-password(?:\/\|$)/` | ✅ |
| `/auth/*` | `/^\/auth(?:\/\|$)/` | ✅ |
| `/onboarding/*` | `/^\/onboarding(?:\/\|$)/` | ✅ |
| `/account/*` | `/^\/account(?:\/\|$)/` | ✅ |
| `/dashboard` | `/^\/dashboard$/` | ✅ |
| `/settings` | `/^\/settings$/` | ✅ |
| `/favorites/*` | `/^\/favorites(?:\/\|$)/` | ✅ |
| `/api/private/*` | `/^\/api\/private(?:\/\|$)/` | ✅ |
| All non-GET | `request.method !== 'GET'` | ✅ |
| Cross-origin requests | `url.origin !== self.location.origin` | ✅ |

**Important**: The `isDenied()` check is the FIRST guard in the fetch handler. Denied routes return immediately without entering any caching branch.

### Allowlist (marketplace-pages cache)

| Route | Pattern | Matches Laravel route |
|-------|---------|----------------------|
| `/` | `/^\/$/` | `home` ✅ |
| `/search` | `/^\/search$/` | `public.search` ✅ |
| `/categories` | `/^\/categories$/` | `public.categories` ✅ |
| `/category/:slug` | `/^\/category\/[^/]+$/` | `public.category` ✅ |
| `/subcategory/:slug` | `/^\/subcategory\/[^/]+$/` | `public.subcategory` ✅ |
| `/city/:slug` | `/^\/city\/[^/]+$/` | `public.city` ✅ |
| `/top-rated` | `/^\/top-rated$/` | `public.top-rated` ✅ |
| `/providers/:slug` | `/^\/providers\/[^/]+$/` | `public.provider` ✅ (plural matches route) |
| `/contact` | `/^\/contact$/` | `contact` ✅ |
| `/privacy` | `/^\/privacy$/` | `privacy` ✅ |
| `/terms` | `/^\/terms$/` | `terms` ✅ |
| `/disclaimer` | `/^\/disclaimer$/` | `disclaimer` ✅ |

**Key correctness note:** Public provider profiles are `/providers/{slug}` (plural) while the provider panel is `/provider/*` (singular). The denylist correctly uses singular to block the panel; the allowlist correctly uses plural for public profiles. No overlap.

**Missing from denylist** (but also not in allowlist, so not cached — passthrough):
- `/billing/*`, `/subscription/*` — not cached, which is correct
- Review moderation routes — not cached ✅

### SW Registration Gate

```php
// layout.blade.php line 35-48
$shouldRegisterPublicPwa = request()->routeIs(
    'home', 'public.search', 'public.top-rated', 'public.categories',
    'public.category', 'public.subcategory', 'public.city',
    'public.provider', 'contact', 'privacy', 'terms', 'disclaimer'
);
```

SW does NOT register on:
- Filament admin (`/cp/*`) — Filament has its own layout ✅
- Provider panel (`/provider/*`) — separate layout ✅
- Auth pages — `auth.blade.php` layout, no SW registration ✅
- Legal pages via `legal_layout.blade.php` — ⚠️ SW not registered on legal pages even though privacy/terms are in the allowlist. This means first-visit to `/privacy` won't trigger SW registration. After navigating from any other public page, the SW will be active. Acceptable but asymmetric.

---

## 4. Caching Strategy Verification

### Static Assets — CacheFirst

```js
isStaticAsset: /build/assets/* | /manifest.json | /favicon.ico | /images/icon-*.png
```

- ✅ Correct strategy for Vite-hashed assets
- ⚠️ **No TTL / no maxEntries** — cache grows indefinitely. Old Vite-hashed assets accumulate. Mitigated by Vite content hashing (old files unused), but storage grows.
- ⚠️ **No cache version bump on deploy** → see Blocker 2

### Provider Images — StaleWhileRevalidate

```js
isPublicImage: request.destination === 'image' && (storage/ | icon/ | images/)
```

- ✅ Correct strategy — serves fast from cache, updates in background
- ⚠️ **No maxEntries** — for a marketplace with many providers, this cache is unbounded
- ⚠️ **`response.type !== 'basic'` check in `putCache`** — if provider images are served from a CDN (non-same-origin), they won't be cached at all. Verify all `/storage/` images serve from the same origin.
- 7-day stale risk: A deleted/suspended provider's profile image may appear for one request cycle (stale then revalidate). Acceptable for images; the HTML page will correctly 404 or exclude the provider.

### API Search — Network Only

```js
if (url.pathname === '/api/profiles/search') {
    event.respondWith(fetch(request));
    return;
}
```

- ✅ Always fresh, never cached
- ✅ No stale search results possible
- ⚠️ **No offline fallback** — search fails silently when offline. User sees a broken state rather than offline.html. Acceptable given fresh-data requirement.

### Public Marketplace HTML — NetworkFirst

```js
if (request.mode === 'navigate' && isPublicPage(url))
    networkFirst(request, PAGE_CACHE)
```

- ✅ `request.mode === 'navigate'` ensures only HTML navigation requests are cached, not subrequests
- ✅ Always tries network first — provider suspension, expiry, removal reflected immediately when online
- ⚠️ **No `networkTimeoutSeconds`** → see Blocker 3
- **No TTL on cached pages** — pages are only served from cache when offline. When online, always fresh. This is CORRECT behavior for marketplace data freshness.

### Admin/Provider/Auth — Not handled (passthrough)

- ✅ Denied routes exit the fetch handler immediately via `return` — no caching branch reached

---

## 5. Stale Data Analysis

The active SW uses **pure NetworkFirst with no timeout** for public pages. This means:

| Scenario | Behavior when ONLINE | Behavior when OFFLINE |
|----------|---------------------|----------------------|
| Provider suspended | Fresh data on next request ✅ | May see cached page ⚠️ |
| Subscription expired | Fresh data on next request ✅ | May see cached page ⚠️ |
| Featured badge removed | Fresh data on next request ✅ | May see cached page ⚠️ |
| Provider deleted | 404 on next request ✅ | May see cached page ⚠️ |
| Profile marked incomplete | Removed from listings on next request ✅ | May see stale listing ⚠️ |

**Online users always see fresh data.** The SW never serves stale HTML to online users.

**Offline users** see whatever was last cached — the offline.html is shown when no cache exists. When a cached page exists (from prior navigation), they see it. This is acceptable offline behavior with the caveat message already in `offline.html`: "قد لا تكون بيانات مقدمي الخدمات محدثة."

### Cache Invalidation (Laravel side)

`ProfilePublicCacheObserver` clears count caches (category, city, subcategory counts) on create/update/delete. This correctly invalidates the Laravel-side count cache, but does NOT send a push message to service workers to purge specific cached pages.

This is acceptable because the SW's NetworkFirst strategy always hits the network first anyway. No SW-side invalidation is needed for online users.

---

## 6. Offline Testing Results (Code Review)

| Test Case | Expected | Implementation |
|-----------|----------|---------------|
| Navigate to cached public page offline | Serve from PAGE_CACHE | ✅ `networkFirst` fallback |
| Navigate to uncached page offline | Show `offline.html` | ✅ `caches.match(OFFLINE_URL)` |
| Search while offline | Network error (not gracefully handled) | ⚠️ Silent failure |
| Static assets offline | Served from cache | ✅ CacheFirst |
| Images offline | Served from image cache | ✅ StaleWhileRevalidate has sync fallback |
| Arabic message visible | Yes | ✅ `offline.html` is full Arabic |
| Retry button | `location.reload()` | ✅ |
| White screen crash | No — offline.html shown | ✅ |
| Brand styling | Minimal but correct | ✅ |
| Safe area support | `env(safe-area-inset-bottom)` | ✅ |

---

## 7. Lighthouse / Performance Notes (Static Analysis)

Cannot run Lighthouse from local dev. Manual pre-deployment checklist:

**Run before deployment:**
```bash
npx lighthouse https://your-production-url.com --only-categories=pwa,performance,accessibility,best-practices --output=html --output-path=docs/audits/lighthouse-report.html
```

**Expected issues to check:**

| Area | Risk | Action |
|------|------|--------|
| Images | `herobackground.png`, `herobackground2.png` in `/public/images/` — check dimensions | Compress to WebP |
| LCP | Hero image may not be preloaded | Add `<link rel="preload">` for hero |
| CLS | Ensure `font-display: swap` for Cairo font | Check Tailwind CSS |
| PWA manifest | Vite-generated manifest not linked — Lighthouse may flag it | Acceptable if `/manifest.json` passes |
| Accessibility | RTL + Arabic must pass axe-core | Run axe in browser on prod |

---

## 8. Mobile UX Findings (Code Review)

| Check | Status | Notes |
|-------|--------|-------|
| `viewport-fit=cover` | ✅ | In layout meta tag |
| `env(safe-area-inset-*)` | ✅ | In layout CSS and offline.html |
| RTL direction | ✅ | `dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"` |
| Bottom navigation | ✅ | Present in layout (lines 939-956) |
| Splash screen animation | ✅ | Present in layout |
| Offline banner | ✅ | Present in layout (lines 880-882) |
| Status bar style | ✅ | `black-translucent` (allows content under status bar) |
| Orientation lock | ✅ | `portrait` in manifest |
| Theme color | ⚠️ | Navy (#0B1A34) in manifest + meta, orange (#F1620F) in offline.html — pick one |

**Manual tests required on real devices:**
- [ ] iPhone Safari: Add to Home Screen → standalone launch → bottom nav renders
- [ ] Android Chrome: Install prompt → standalone launch → safe area respected
- [ ] Small screen (360px): No horizontal overflow on category chips, provider cards
- [ ] Tablet (768px): Layout doesn't break at non-mobile widths

---

## 9. Auth / Session Safety

| Scenario | SW behavior | Risk |
|----------|-------------|------|
| Login (`POST /login`) | Non-GET → passthrough | ✅ Safe |
| Logout (`GET /logout`) | Denied route → passthrough | ✅ Safe |
| Google OAuth callback | Not in allowlist → not cached | ✅ Safe |
| CSRF token in forms | POST → not intercepted | ✅ Safe |
| Session cookies | SW never intercepts auth routes | ✅ Safe |
| Admin panel (`/cp/*`) | Denied route → passthrough | ✅ Safe |
| Provider panel (`/provider/*`) | Denied route → passthrough | ✅ Safe |
| Open installed PWA after logout | NetworkFirst fetches fresh page, server redirects to login | ✅ Safe |
| `window.isSecureContext` check | SW only registers over HTTPS | ✅ Production safe |

**No auth/session issues found.** The denylist is comprehensive enough that no auth-sensitive page can be accidentally cached.

---

## 10. Build / Deployment Checklist

### Pre-deployment commands
```bash
npm run build          # Verify no build errors
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link  # If not already done
php artisan test --compact
```

### Build output verification
After `npm run build`, confirm:
- [ ] `/public/build/sw.js` exists (Workbox build)
- [ ] `/public/build/manifest.webmanifest` exists
- [ ] `/public/sw.js` is unchanged (it's static, not built)
- [ ] No `localhost` or `127.0.0.1` references in any built JS/CSS
- [ ] No dev Vite URLs (`/@vite/`) in HTML

### Laravel Cloud checklist
- [ ] `APP_ENV=production`
- [ ] `APP_URL` set to exact HTTPS domain
- [ ] `APP_DEBUG=false`
- [ ] HTTPS enforced (PWA requirement; SW won't register without it)
- [ ] Storage symlink created
- [ ] Queue worker running (for jobs triggered by observer events)
- [ ] Cache driver set to Redis/database (not `array`)

---

## Blockers (Must Fix Before Deployment)

### BLOCKER 1 — Dead Workbox SW (`resources/js/sw.js` is compiled but never loaded)

**Impact:** Any future SW maintenance on `resources/js/sw.js` has zero effect in production. Developers will be confused when their changes don't appear. Also, `resources/js/sw.js` has a route conflict bug (see §2) that would break public provider profiles if this SW were ever activated accidentally.

**Fix (Option A — recommended):** Remove VitePWA from `vite.config.js`. The vanilla `/public/sw.js` is the real SW. You don't need VitePWA.

```js
// vite.config.js — remove these lines:
import { VitePWA } from 'vite-plugin-pwa';
// ... and the VitePWA({ ... }) plugin block
```

Also remove from package.json:
```bash
npm uninstall vite-plugin-pwa workbox-window workbox-background-sync
```

Then add the manifest link to the Vite output or keep `/manifest.json` static (it's already static, so this is already handled).

**Fix (Option B):** If you want to keep VitePWA, wire it properly:
- Update `resources/js/sw.js` allowlist to use `/providers/` plural and fix the denylist
- Import VitePWA virtual module in `resources/js/app.js`
- Remove manual registration from `layout.blade.php`
- Delete `/public/sw.js` (the vanilla one)
- Change scope in `registerSW.js` to `/` (currently `/build/`)

---

### BLOCKER 2 — Hardcoded cache version will not bust on deploy

**File:** `/public/sw.js` line 1  
**Code:** `const CACHE_VERSION = 'delni-public-v1';`

**Impact:** When you deploy code changes, the SW is a new file (browser will update it). But the new SW opens the same cache names (`delni-public-v1-static`, etc.) and finds old cached assets. For Vite-hashed JS/CSS this is fine (new hashes = new cache keys). But for `/manifest.json`, `/images/icon-192.png`, and cached page HTML (`PAGE_CACHE`), the old responses remain until evicted.

**Fix:** Inject build hash into cache version at deploy time, OR bump the version string before each deploy.

**Simple approach — add to deploy process:**

```bash
# In deploy script or Makefile:
BUILD_HASH=$(git rev-parse --short HEAD)
sed -i "s/delni-public-v1/delni-public-${BUILD_HASH}/" public/sw.js
```

**Or use a constant you increment manually before each release:**
```js
const CACHE_VERSION = 'delni-public-v2'; // bump on each deploy
```

---

### BLOCKER 3 — No network timeout in NetworkFirst for public pages

**File:** `/public/sw.js` lines 128-138  
**Code:** `const response = await fetch(request);` — waits indefinitely

**Impact:** On a slow mobile connection (2G, flaky 3G), a user navigating to a public marketplace page waits for the full browser network timeout (~30s) before the SW falls back to cached content. This is particularly painful for the Libyan target market where connectivity may be unreliable.

**Fix:** Add a race between fetch and a timeout:

```js
async function networkFirst(request, cacheName, timeoutMs = 3000) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

    try {
        const response = await fetch(request, { signal: controller.signal });
        clearTimeout(timeoutId);
        await putCache(request, response, cacheName);
        return response;
    } catch {
        clearTimeout(timeoutId);
        const cached = await caches.match(request);
        return cached || caches.match(OFFLINE_URL);
    }
}
```

This falls back to cached content after 3 seconds, matching the experience a Workbox `networkTimeoutSeconds: 3` would give.

---

## Remaining Risks (Non-Blocking)

| Risk | Severity | Mitigation |
|------|----------|-----------|
| Image cache unbounded | Medium | Add `maxEntries` in a future SW update |
| CDN images not cached | Medium | Verify all `/storage/` images are same-origin |
| `theme_color` inconsistency (navy vs orange) | Low | Cosmetic only; pick a standard in a post-launch cleanup |
| Legal pages (`/privacy`, `/terms`) don't register SW on first direct visit | Low | SW registers on next navigation to any public page |
| Search fails silently when offline | Low | Acceptable; search requires live data |
| Icon maskable safe zone | Low | Verify the 192px icon has sufficient padding for circular crop |
| Workbox `Queue` import in dead `resources/js/sw.js` | Low | Dead code, but clean up if you remove VitePWA |

---

## Final Verdict

**⚠️ NOT PRODUCTION-READY AS-IS — fix 3 blockers first.**

| Area | Status |
|------|--------|
| Auth/panel safety | ✅ SAFE — admin, provider panel, all auth routes excluded |
| Stale marketplace data (online) | ✅ SAFE — NetworkFirst always fetches fresh |
| Stale marketplace data (offline) | ✅ ACCEPTABLE — offline.html warns users |
| Installability | ✅ READY |
| Offline experience | ✅ READY |
| Mobile UX | ✅ READY (verify on device) |
| SW architecture | ❌ Dead Workbox SW is a maintenance time bomb |
| Cache busting on deploy | ❌ Hardcoded version string |
| Slow network UX | ❌ No timeout in NetworkFirst |

**After fixing the 3 blockers:** Delni PWA is safe to deploy. The active service worker (`/public/sw.js`) correctly isolates marketplace caching, never touches auth or admin routes, always fetches fresh data for online users, and provides a graceful offline experience with appropriate Arabic messaging.

The marketplace data freshness guarantees are solid: a suspended provider, expired subscription, or removed featured placement will be reflected on the very next page load for any online user. No dangerous stale state is possible while online.
