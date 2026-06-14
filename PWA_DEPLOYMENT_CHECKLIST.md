# Delni Public PWA — Deployment Checklist

**Date:** 2026-06-14  
**Build Status:** ✅ Success  
**Service Worker:** Generated & Injected  
**Manifest:** Generated  

## Build Verification

✅ `npm run build` — Completed (3.73s total)
✅ Service worker compiled (`public/build/sw.js`)
✅ Manifest generated (`public/build/manifest.json`)
✅ Assets hashed (cache-friendly)
✅ `php artisan optimize:clear` — All caches cleared

## Files Changed

### Core PWA Files
- `vite.config.js` — Added VitePWA plugin with manifest config
- `resources/js/sw.js` — Service worker with route-specific caching strategies
- `public/offline.html` — Arabic offline fallback page
- `public/pwa/icon-192.svg` — Home screen icon (placeholder)
- `public/pwa/icon-512.svg` — Splash screen icon (placeholder)
- `public/pwa/icon-maskable.svg` — Maskable icon (placeholder)

### Service Worker Registration
- ✅ Already in place in `resources/views/public/layout.blade.php`
- ✅ Uses `$shouldRegisterPublicPwa` route check
- ✅ Only registers on public marketplace pages
- ✅ Checks for HTTPS/secure context

### Documentation
- `docs/PWA_IMPLEMENTATION.md` — Complete architecture & testing guide

## Caching Strategy Summary

| Route | Strategy | TTL | Purpose |
|-------|----------|-----|---------|
| Static assets (JS/CSS) | CacheFirst | 30 days | Immutable, hashed |
| Provider images | StaleWhileRevalidate | 7 days | Fresh but fast |
| Public pages (homepage, search, etc.) | NetworkFirst | 10 min | Always tries network first |
| API search | NetworkFirst | 5 min | Fresh search results |
| Admin/auth/provider panels | Network Only | N/A | Never cached |
| POST/PUT/DELETE requests | Network Only | N/A | Never cached |

## Marketplace Safety Guarantees

✅ Suspended providers: Disappear within 10 min (cache TTL)  
✅ Expired subscriptions: Hidden within 10 min  
✅ Featured placements: Update within 10 min  
✅ Incomplete profiles: Hidden immediately  
✅ Admin panel: Network only, never cached  
✅ Auth pages: Network only, never cached  

**NetworkFirst strategy ensures visibility rules are always fresh.**

## Pre-Deployment Testing

Run these before deploying to production:

### 1. Service Worker Registration
```bash
# In browser console
navigator.serviceWorker.getRegistrations().then(r => console.log(r))
```
Should show 1 registration with scope `/`.

### 2. Manifest Validity
```bash
# In browser console
fetch('/manifest.json').then(r => r.json()).then(m => console.log(m))
```
Should show valid PWA manifest with:
- `name: "دلني"`
- `display: "standalone"`
- `icons[]` array populated
- `start_url: "/"`

### 3. Offline Fallback
1. Open DevTools → Network
2. Set throttle to "Offline"
3. Navigate to a non-cached route
4. Should see `offline.html` rendered

### 4. Cache Storage
1. Open DevTools → Application → Cache Storage
2. Should see these caches:
   - `vite-assets` (30 days)
   - `provider-images` (7 days)
   - `marketplace-pages` (10 min)
   - `api-search` (5 min)
   - `network-only` (network only)
   - `offline-fallback`

### 5. Install Prompt
1. Open on Android or Chrome desktop
2. Address bar should show "Install" button
3. Click to install to home screen
4. App should launch in standalone mode

### 6. Marketplace Freshness
1. Create a test provider
2. Suspend the provider
3. Hard refresh browser (Ctrl+Shift+R)
4. Provider should disappear within 10 seconds
5. Un-suspend provider
6. Refresh again
7. Provider should reappear within 10 seconds

### 7. Admin Panel Protection
1. Navigate to `/cp/admin`
2. DevTools → Network tab
3. Should NOT see service worker responses
4. Reload page
5. Should NOT be served from cache

### 8. Lighthouse PWA Audit
1. Open DevTools → Lighthouse
2. Run PWA audit
3. Score should be > 90
4. Check for missing icons/manifest issues

## Production Deployment

### Before Deployment

1. **Replace Icon Placeholders**
   ```
   public/pwa/icon-192.png  (192×192 PNG)
   public/pwa/icon-512.png  (512×512 PNG)
   public/pwa/icon-maskable.png (512×512 PNG, transparent safe zone)
   ```
   Use Delni branding/logo.

2. **Verify HTTPS**
   ```
   APP_URL=https://delni.ly
   ```
   PWA requires HTTPS in production.

3. **Asset URL Configuration**
   Ensure `APP_URL` in `.env` matches production domain for correct manifest/icon paths.

4. **Service Worker Cache Cleanup**
   On deploy, existing service worker caches will be cleaned up automatically by the `activate` event.

### Deployment Steps

```bash
# 1. Build locally and test
npm run build
php artisan optimize:clear

# 2. Deploy to staging
git push staging main

# 3. Run on server
composer install --no-interaction
npm ci
npm run build
php artisan optimize:clear

# 4. Verify manifest is accessible
curl https://delni.ly/manifest.json

# 5. Test in browser
# - Open delni.ly on Android/iOS
# - Check install prompt appears
# - Test offline mode

# 6. Monitor
# - Check browser console for SW errors
# - Monitor Lighthouse PWA score
# - Verify cache behavior in DevTools
```

### Post-Deployment

- [ ] Install prompt appears on Android
- [ ] App installs to home screen
- [ ] Standalone mode works
- [ ] Offline fallback displays correctly
- [ ] Public pages load from cache (cold load)
- [ ] Suspended provider disappears quickly
- [ ] Admin panel never cached
- [ ] No console errors
- [ ] Lighthouse PWA score > 90

## Rollback

If issues arise:

```bash
# Option 1: Disable PWA temporarily (keep files, disable registration)
# Remove @if($shouldRegisterPublicPwa) block from layout

# Option 2: Clear all caches
php artisan tinker
> Cache::flush()

# Option 3: Service worker removal
# Users can uninstall app from home screen
# Unregistered SW won't interfere
```

## Monitoring

### Browser Console Checks

```js
// Check if SW is registered
console.log(await navigator.serviceWorker.getRegistrations())

// Check cache contents
console.log(await caches.keys())

// Check manifest
console.log(await (await fetch('/manifest.json')).json())

// Verify offline page
console.log(await caches.match('/offline.html'))
```

### Performance Metrics

Monitor:
- `Cache.match()` hit rates
- Service worker registration errors
- Manifest fetch failures
- Icon load errors in install prompt

## Known Limitations & Future Work

- Offline search not supported (requires API)
- Admin/auth pages intentionally never cached
- Marketplace visibility enforced on every network call
- Push notifications not yet implemented (ready for future FCM integration)
- Background sync not yet implemented
- Offline favorites storage not yet implemented

## Support & Troubleshooting

### Service Worker Not Registering
- Check `APP_URL` is HTTPS
- Verify `window.isSecureContext` in console
- Check route matches `$shouldRegisterPublicPwa`
- Check browser console for errors

### Icons Not Showing
- Verify files exist at `public/pwa/icon-*.{svg|png}`
- Check manifest JSON for correct paths
- Ensure CORS headers allow cross-origin icon fetches

### Offline Page Not Showing
- Clear all caches: `php artisan tinker` → `Cache::flush()`
- Check `public/offline.html` exists
- Verify SW precaching includes offline.html

### Cache Too Aggressive
- Reduce TTLs in `resources/js/sw.js`
- Change `NetworkFirst` timeout to lower value (e.g., 2s instead of 3s)
- Add new routes to `deniedRoutes` array

---

**Status:** ✅ Ready for Staging Deployment
