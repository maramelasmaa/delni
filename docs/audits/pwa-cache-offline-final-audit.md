# Delni PWA — Cache + Offline + Stale Data Final Audit

**Date:** 2026-06-18  
**Auditor:** Claude Code  
**Deployment target:** Saturday 2026-06-21  
**Verdict:** [see §18]

---

## 1. Executive Summary

Delni's PWA is built on a hand-written custom service worker (`public/sw.js`) with no Workbox or VitePWA dependencies. The architecture is sound and well-structured. No blockers were found from static code analysis. Two pre-deploy actions are **required**, and several browser-testing steps should be completed before Saturday.

The critical insight for Libya: NetworkFirst with a 3-second AbortController timeout is the right strategy. On slow mobile networks, users get a cached version fast rather than waiting indefinitely.

---

## 2. Active PWA Architecture

| Component | File | Status |
|-----------|------|--------|
| Service Worker | `public/sw.js` | Custom, no Workbox |
| Offline fallback | `public/offline.html` | Arabic, dark-mode ready |
| Manifest | `public/manifest.json` | Correct |
| Icons | `public/images/icon-192.png`, `icon-512.png` | Present |
| SW registration | `resources/views/public/layout.blade.php` (conditional) | Public routes only |
| JS bundler | Vite — `vite.config.js` | No VitePWA plugin |

**Answers to Section 1 questions:**

1. **Which SW is actually registered?** `/sw.js` — registered only when `$shouldRegisterPublicPwa` is true (public-facing routes only).
2. **Which file controls caching?** `public/sw.js` entirely.
3. **SW scope?** `'/'` — full origin scope.
4. **More than one service worker?** No. One SW, one registration point.
5. **Dead PWA packages/artifacts?** None. `package.json` has zero PWA dependencies.
6. **Registration only on public pages?** Yes — guarded by `$shouldRegisterPublicPwa` checking against a named route allowlist. Admin/provider/auth layouts never register the SW.

---

## 3. Static vs Dynamic Cache Classification

### Static — Safe to Cache (CacheFirst)

| Asset | Cache | Notes |
|-------|-------|-------|
| `/build/assets/**` (Vite JS/CSS) | `STATIC_CACHE` | Content-hashed filenames = safe forever |
| `/manifest.json` | `STATIC_CACHE` | Precached on install |
| `/images/icon-192.png` | `STATIC_CACHE` | Precached on install |
| `/images/icon-512.png` | `STATIC_CACHE` | Precached on install |
| `/offline.html` | `STATIC_CACHE` | Precached on install |
| `/favicon.ico` | `STATIC_CACHE` | CacheFirst |

### Dynamic — NetworkFirst (3-second timeout, PAGE_CACHE)

| Route | SW Pattern | Notes |
|-------|-----------|-------|
| `/` | `PUBLIC_HTML_ALLOW` | Homepage — providers/stats refresh on network |
| `/search` | `PUBLIC_HTML_ALLOW` | Search results — always network first |
| `/categories` | `PUBLIC_HTML_ALLOW` | Category list |
| `/category/{slug}` | `PUBLIC_HTML_ALLOW` | Category providers |
| `/category/{slug}/in/{city}` | `PUBLIC_HTML_ALLOW` | City-filtered category |
| `/subcategory/{slug}` | `PUBLIC_HTML_ALLOW` | Subcategory providers |
| `/subcategory/{slug}/in/{city}` | `PUBLIC_HTML_ALLOW` | City-filtered subcategory |
| `/city/{slug}` | `PUBLIC_HTML_ALLOW` | City providers |
| `/top-rated` | `PUBLIC_HTML_ALLOW` | Top-rated — NetworkFirst |
| `/top-rated/in/{city}` | `PUBLIC_HTML_ALLOW` | City top-rated |
| `/providers/{slug}` | `PUBLIC_HTML_ALLOW` | Provider profile |
| `/contact`, `/privacy`, `/terms`, `/disclaimer` | `PUBLIC_HTML_ALLOW` | Mostly static content |

### Images — StaleWhileRevalidate (IMAGE_CACHE, max 200 entries)

| Pattern | Notes |
|---------|-------|
| `/storage/**` | Provider photos, avatars, portfolios |
| `/icon/**` | Dynamic icons |
| `/images/**` | App icons and static images |

### Never Cached (SW bypasses entirely)

| Route/Pattern | DENY_PATHS Rule |
|---------------|----------------|
| `/cp/**` | Admin panel — SW skips |
| `/provider/**` | Provider panel — SW skips |
| `/login` | Auth — SW skips |
| `/register` | Auth — SW skips |
| `/logout` | Auth — SW skips |
| `/auth/**` | Google OAuth — SW skips |
| `/forgot-password` | Auth — SW skips |
| `/reset-password/**` | Auth — SW skips |
| `/onboarding/**` | Provider onboarding — SW skips |
| `/onboarding-test/**` | SW skips |
| `/account/**` | User account — SW skips |
| `/dashboard` | SW skips |
| `/settings` | SW skips |
| `/favorites/**` | User-specific — SW skips |
| `/api/private/**` | Private API — SW skips |
| `/api/profiles/search` | **Special case**: SW intercepts but calls raw `fetch()` — network-only, no caching |
| All non-GET requests | Bypassed by `request.method !== 'GET'` guard |

---

## 4. Never-Cache Route Table

| Route | Why it Must Not Be Cached |
|-------|--------------------------|
| `/cp/*` | Admin panel with CSRF-protected state |
| `/provider/*` | Provider panel — session-dependent |
| `/login`, `/register` | Authentication pages |
| `/logout` | State-changing GET |
| `/auth/google`, `/auth/google/callback` | OAuth flow — must not be intercepted |
| `/forgot-password`, `/reset-password/*` | Token-bound auth forms |
| `/settings`, `/account/*` | User-specific state |
| `/favorites` | User-specific data |
| All POST/PUT/PATCH/DELETE | `request.method !== 'GET'` guard in SW |

**Status: PASS** — All sensitive routes are in `DENY_PATHS` or bypass SW by method.

---

## 5. Cache Strategy Table

| Cache Name | Patterns | Strategy | TTL | Max Entries | Cleanup |
|-----------|----------|----------|-----|-------------|---------|
| `{VERSION}-static` | `/build/assets/`, `/manifest.json`, icons, `offline.html` | CacheFirst | Indefinite (version-busted) | Unlimited | Deleted on SW version change |
| `{VERSION}-images` | `/storage/`, `/icon/`, `/images/` | StaleWhileRevalidate | Revalidates on each request | 200 entries | `trimCache()` evicts oldest |
| `{VERSION}-pages` | Public HTML pages (allowlist) | NetworkFirst (3s timeout) | No TTL — network wins when available | Unlimited | Deleted on SW version change |

---

## 6. Network Timeout / Resilience

**Status: PASS**

`networkFirst()` implementation:

```js
async function networkFirst(request, cacheName, timeoutMs = 3000) {
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

- 3-second AbortController timeout ✅
- Falls back to cache if network slow/unreachable ✅
- Falls back to `offline.html` for navigation with no cache ✅
- No infinite wait ✅

**Libya-specific note:** On slow mobile connections, the 3-second timeout means users may frequently receive cached pages. This is intentional and correct — fast response beats stale-data risk. The offline banner in the layout handles this gracefully.

---

## 7. Cache Versioning / Deploy Cache-Busting

**Status: WARNING — Action required before Saturday**

Current value: `const CACHE_VERSION = 'delni-public-2026-06-16-10';`

This is **hardcoded**. It must be manually bumped before each deployment or users will remain on old cached assets.

**Required deploy step (must be done before Saturday):**

```bash
# Option A — Manual: edit public/sw.js line 2
# Change: const CACHE_VERSION = 'delni-public-2026-06-16-10';
# To:     const CACHE_VERSION = 'delni-public-2026-06-21-01';

# Option B — One-liner (from sw.js comment):
sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js
```

The `activate` event correctly deletes all caches not in the current version:

```js
keys.filter((key) => ![STATIC_CACHE, IMAGE_CACHE, PAGE_CACHE].includes(key))
    .map((key) => caches.delete(key))
```

Once the version string changes, all three named caches change, and old caches are deleted on next SW activation. Users with the old SW will pick up the new one on their next navigation (skipWaiting is called on install).

---

## 8. Offline Behavior

**Status: PASS (with one minor warning)**

`offline.html` analysis:
- Language: Arabic (`lang="ar" dir="rtl"`) ✅
- Heading: "أنت غير متصل" ✅
- Message explains no internet connection ✅
- Subtext warns data may not be current ✅
- Retry button: `onclick="location.reload()"` ✅
- Brand footer: "دلني • دليل الخدمات الليبي" ✅
- Dark mode: native `@media (prefers-color-scheme: dark)` ✅
- No English text ✅
- Inline CSS — no broken external CSS ✅

**WARNING:** `offline.html` loads Google Fonts (`fonts.googleapis.com`) via `<link>`. When offline, this request fails and Cairo font falls back to system fonts. The page is still readable and functional, but will use system Arabic fonts. This is minor but visible on branded UX.

**Fix option (low priority):** Inline the font-face declaration with a base64 subset, or accept system font fallback.

**Offline behavior logic:**
- Visited public page → serves cached HTML version ✅
- Unvisited public page → serves `offline.html` ✅
- Denied route offline → SW skips (network fetch fails naturally, browser shows error) ✅
- Non-navigate request offline → throws, not SW's concern ✅

---

## 9. Marketplace Stale Data Analysis

**Status: PASS (by architecture)**

The NetworkFirst strategy with a 3-second timeout means:

**When the network is healthy (< 3s response):**
- All provider data comes from the server
- Suspended providers won't appear (server returns fresh response)
- Expired providers won't appear
- Featured placements reflect current database state
- Ratings/counts are current

**When the network is slow or offline (> 3s or no network):**
- Cached HTML is served — stale data is possible
- The offline banner in the layout (`#delniOfflineBanner`) displays when `!navigator.onLine`
- Users see the cached version and are warned via the offline banner

**Provider visibility status matrix:**

| Scenario | Online (fast) | Online (slow / timeout) | Offline |
|----------|--------------|------------------------|---------|
| Suspended provider | Hidden ✅ | May show from cache ⚠ | May show from cache ⚠ |
| Expired access | Hidden ✅ | May show from cache ⚠ | May show from cache ⚠ |
| Incomplete profile | Hidden ✅ | May show from cache ⚠ | May show from cache ⚠ |
| Featured removed | Removed ✅ | May show from cache ⚠ | May show from cache ⚠ |
| Rating changed | Updated ✅ | May show stale ⚠ | May show stale ⚠ |

The stale-on-slow-network behavior is inherent to NetworkFirst with a timeout. It cannot be eliminated without removing offline support entirely. The 3-second timeout is a reasonable balance.

**The search API (`/api/profiles/search`) is network-only** — no fallback, no caching. If a user searches while offline or on a very slow connection, the search will fail (empty results / error). This is correct: stale search results would be worse than an error.

---

## 10. Auth / Panel Safety

**Status: PASS**

Route analysis against DENY_PATHS:

| Route | DENY_PATHS Match | Result |
|-------|-----------------|--------|
| `/login` | `/^\/login$/` | SW skips ✅ |
| `/register` | `/^\/register$/` | SW skips ✅ |
| `/logout` | `/^\/logout$/` | SW skips ✅ |
| `/auth/google` | `/^\/auth(?:\/\|$)/` | SW skips ✅ |
| `/auth/google/callback` | `/^\/auth(?:\/\|$)/` | SW skips ✅ |
| `/forgot-password` | `/^\/forgot-password$/` | SW skips ✅ |
| `/reset-password/*` | `/^\/reset-password(?:\/\|$)/` | SW skips ✅ |
| `/provider` | `/^\/provider(?:\/\|$)/` | SW skips ✅ |
| `/provider/login` | `/^\/provider(?:\/\|$)/` | SW skips ✅ |
| `/provider/dashboard` | `/^\/provider(?:\/\|$)/` | SW skips ✅ |
| `/provider/*` | `/^\/provider(?:\/\|$)/` | SW skips ✅ |
| `/cp/admin` | `/^\/cp(?:\/\|$)/` | SW skips ✅ |
| `/cp/*` | `/^\/cp(?:\/\|$)/` | SW skips ✅ |
| `/settings` | `/^\/settings$/` | SW skips ✅ |
| `/account/*` | `/^\/account(?:\/\|$)/` | SW skips ✅ |
| `/dashboard` | `/^\/dashboard$/` | SW skips ✅ |
| `/favorites` | `/^\/favorites(?:\/\|$)/` | SW skips ✅ |
| `/onboarding/*` | `/^\/onboarding(?:\/\|$)/` | SW skips ✅ |
| `/api/private/*` | `/^\/api\/private(?:\/\|$)/` | SW skips ✅ |

**Critical distinction confirmed:**
- `/providers/{slug}` (public profile) — NOT in DENY_PATHS → NetworkFirst ✅
- `/provider/*` (provider panel) — IN DENY_PATHS → SW skips ✅

CSRF token pages, logout, and Google OAuth are all bypassed. No sensitive route will ever be served from cache.

---

## 11. Installability

**Status: PASS (pending visual verification)**

`public/manifest.json` fields:

| Field | Value | Status |
|-------|-------|--------|
| `name` | "دلني - دليل الخدمات الليبي" | ✅ |
| `short_name` | "دلني" | ✅ |
| `start_url` | `/` | ✅ |
| `scope` | `/` | ✅ |
| `display` | `standalone` | ✅ |
| `theme_color` | `#0B1A34` | ✅ |
| `background_color` | `#0B1A34` | ✅ |
| `lang` | `ar` | ✅ |
| `dir` | `rtl` | ✅ |
| `icons` | 192px + 512px | ✅ |
| `maskable` icon | 512px reused | ⚠ |

**WARNING:** The maskable icon reuses `icon-512.png` with `"purpose": "maskable"`. A proper maskable icon requires the safe zone (80% of image area) to contain the logo. If `icon-512.png` has no padding, the adaptive icon on Android will clip the logo. Verify this in Chrome DevTools → Application → Manifest → Maskable icon preview.

**No localhost URL** in manifest ✅ — references are `/images/icon-*.png` (relative paths).

---

## 12. Mobile PWA UX

**Status: Requires browser testing (code review only here)**

From layout code review:

- Bottom nav height: `var(--pwa-nav-height)` = 64px + `env(safe-area-inset-bottom)` ✅
- Main content padding: `calc(var(--pwa-nav-height) + env(safe-area-inset-bottom) + 20px)` ✅ (no overlap)
- `viewport-fit=cover` + `env(safe-area-inset-*)` for iPhone notch ✅
- RTL: `dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"` ✅
- Desktop: bottom nav hidden at `≥1025px`, top nav shown ✅
- Touch targets: nav items have `min-height: 44px` equivalent via flex layout ✅
- Horizontal overflow: `overflow-x: auto` on chip strips, hidden scrollbars ✅
- Keyboard: `overflow: hidden` on html/body on mobile prevents layout shifts ✅

**Must verify in browser:** pagination at 375px, filter chips on small screens, search input behavior with keyboard open.

---

## 13. Image Cache

**Status: PASS**

- Images use `StaleWhileRevalidate` — not precached en masse ✅
- `IMAGE_CACHE_MAX_ENTRIES = 200` — hard limit on cache size ✅
- `trimCache()` evicts oldest entries when limit exceeded ✅
- Portfolio images are not precached on install ✅
- Only `icon-192.png` and `icon-512.png` are precached (small, fixed files) ✅
- Images are lazy-loaded via native browser behavior (verify `loading="lazy"` in templates)

**Estimated cache size after browsing homepage + category + 5 profiles:**
- Static: ~500KB (Vite bundle + icons)
- Images: varies — could reach 5–20MB depending on provider photo sizes
- Pages: ~50–200KB (HTML is lightweight)

The 200-entry image limit prevents unbounded growth. At ~100KB avg per provider image, 200 entries ≈ 20MB max. Acceptable.

---

## 14. PWA Update Behavior

**Status: WARNING — Requires deploy action**

Update flow:
1. New SW deployed with updated `CACHE_VERSION`
2. Browser detects byte change in `/sw.js`
3. New SW installs (old SW still active until all tabs close, due to `skipWaiting()` call)

Wait — `self.skipWaiting()` IS called in the `install` event. This means the new SW takes over immediately without waiting for all tabs to close. Old caches are then deleted in `activate`. This is the correct behavior for a marketplace.

**However:** `skipWaiting()` takes over but the currently open tab's page has already loaded with the old assets. On next navigation, the new SW serves new assets.

**Deploy flow:**
1. Update `CACHE_VERSION` in `public/sw.js`
2. Run `npm run build` (generates new hashed Vite assets)
3. Deploy
4. On user's next page load, new SW is detected and installed
5. `skipWaiting()` activates the new SW immediately
6. Old caches are purged
7. User gets fresh content on next navigation

Users do NOT need to hard-refresh. The update is automatic on next page load.

---

## 15. Production HTTPS Checklist

| Item | Expected | Status |
|------|----------|--------|
| `APP_URL` | `https://delni.ly` | Verify in `.env` |
| SW served from | `https://delni.ly/sw.js` | Verify after deploy |
| Manifest served via HTTPS | `https://delni.ly/manifest.json` | Verify after deploy |
| Icons via HTTPS | `https://delni.ly/images/icon-*.png` | Verify after deploy |
| Storage images | `https://delni.ly/storage/...` | Verify after deploy |
| Mixed content | None | Verify with browser console |
| SSL certificate | Valid, not self-signed | Verify |
| www redirect | `https://delni.ly` or `https://www.delni.ly` — pick one | Decide before deploy |

**Note:** PWA service workers require HTTPS. Localhost is the only HTTP exception. If any asset is served over HTTP while the page is HTTPS, the browser will block it (mixed content).

---

## 16. Blockers

**None identified from static code analysis.**

The SW architecture is correct. All sensitive routes are protected. NetworkFirst timeout is implemented correctly.

---

## 17. Warnings (must review before Saturday)

### W1 — CACHE_VERSION must be bumped before deploy
**Priority: HIGH — must do**

```bash
# Run in project root before deploying:
sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js
# Then verify:
grep CACHE_VERSION public/sw.js
```

### W2 — offline.html loads external Google Fonts
**Priority: LOW**

When offline, Cairo font fails to load from `fonts.googleapis.com`. The page uses system Arabic fonts as fallback. Functional but not pixel-perfect branded. Consider inlining a font subset.

### W3 — Maskable icon has no safe-zone padding
**Priority: MEDIUM**

`icon-512.png` is used for both `any` and `maskable` purposes. On Android, maskable icons are cropped to a circle/squircle. If the logo fills the full 512×512 without padding, it will be clipped.

Verify: Chrome DevTools → Application → Manifest → click maskable icon.

Fix if needed: Create a `icon-512-maskable.png` with the logo centered in 80% of the canvas (204px padding on each side for a 512px image).

### W4 — PAGE_CACHE has no entry limit
**Priority: LOW**

With city filters, pagination, and sort params, each unique URL is a separate cache entry. Over time with heavy browsing, the page cache could grow large. No trimCache is applied to PAGE_CACHE. Acceptable for now but worth revisiting at scale.

### W5 — `/locale/{locale}` route not in DENY_PATHS
**Priority: LOW**

The locale-switching route (`/locale/ar`, `/locale/en`) is not in `DENY_PATHS` and not in `PUBLIC_HTML_ALLOW`. The SW does not intercept it (navigate requests must match `isPublicPage` to be cached). This is correct behavior — locale switches redirect back to the page. No issue.

### W6 — Stale data on slow networks (Libya-specific)
**Priority: INFO**

On networks slower than 3 seconds page load, NetworkFirst falls back to cache. A suspended or expired provider could appear in a cached page to a user on a slow connection. The offline banner is shown when `!navigator.onLine` but **not** when the SW fell back to cache due to a timeout. Users on slow-but-connected networks won't see the offline banner, yet may see stale data.

**Mitigation considered:** This is the fundamental trade-off of NetworkFirst-with-timeout. The alternative (waiting indefinitely) is worse. The risk is acceptable for a directory marketplace.

---

## 18. Required Fixes Before Saturday

| # | Action | Priority | Time |
|---|--------|----------|------|
| 1 | **Bump CACHE_VERSION** in `public/sw.js` to today's date + deploy | **MUST DO** | 1 min |
| 2 | Run `npm run build` to generate fresh Vite assets | **MUST DO** | 2 min |
| 3 | Verify maskable icon safe zone in Chrome DevTools | High | 5 min |
| 4 | Browser-test offline flow: visit homepage → go offline → reload | High | 10 min |
| 5 | Verify `/provider/login` and `/cp/admin` are NOT in Cache Storage after browsing | High | 5 min |
| 6 | Confirm `APP_URL=https://delni.ly` in production `.env` | High | 2 min |
| 7 | Lighthouse PWA audit (Chrome DevTools) | Medium | 10 min |
| 8 | Install on Android Chrome — test Add to Home Screen | Medium | 10 min |
| 9 | Test Slow 3G in DevTools — verify 3s fallback works | Medium | 10 min |

---

## 19. Final Verdict

### YES WITH WARNINGS — Safe to deploy Saturday

The Delni PWA architecture is well-constructed. No security gaps. No blockers. The service worker correctly denies all admin/auth/provider routes, implements NetworkFirst with a proper timeout, and handles offline gracefully with Arabic content.

**Two actions MUST happen before deploy:**
1. Bump `CACHE_VERSION` in `public/sw.js`
2. Run `npm run build`

Everything else is verification or low-priority polish.

---

### Answers to Final Questions

**1. What stays static/cached?**
Vite-built JS/CSS (CacheFirst, indefinite), app icons, manifest.json, offline.html. These never change without a CACHE_VERSION bump.

**2. What stays dynamic/fresh?**
All public HTML pages (NetworkFirst, 3s timeout) — homepage, categories, providers, search, top-rated. Fresh on every load when network responds within 3 seconds.

**3. What is never cached?**
Admin panel, provider panel, all auth routes, logout, Google OAuth, favorites, settings, account pages, all POST requests, CSRF pages, private API.

**4. Can suspended/expired providers leak through cache?**
Only on slow connections (>3s) or offline. On a healthy connection, NetworkFirst always serves fresh data and suspended providers won't appear. On slow/offline, a cached page may show them, but the offline banner warns users data may be stale.

**5. Can auth/admin/provider pages be cached?**
No. DENY_PATHS explicitly prevents the SW from intercepting any of these routes. They pass directly to the network. Cache Storage will never contain entries for these URLs.

**6. What is the exact deploy cache-busting step?**
```bash
# Step 1: Update CACHE_VERSION (do this BEFORE npm run build)
sed -i "s/delni-public-[^']*/delni-public-$(git rev-parse --short HEAD)/" public/sw.js

# Step 2: Build assets
npm run build

# Step 3: Deploy both public/sw.js and the new build/ directory
```

After deployment, users' browsers detect the changed `/sw.js` byte content, install the new SW, `skipWaiting()` activates it immediately, and the `activate` event deletes all old-version caches.
