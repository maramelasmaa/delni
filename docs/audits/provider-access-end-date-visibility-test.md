# Provider Access End Date — Visibility Audit & Test Results

**Date:** 2026-06-18  
**Status:** 29/29 tests passing ✅  
**Verdict:** [see §10]

---

## 1. Exact End Date Field

**Column:** `profiles.provider_access_ends_at`  
**Type:** `DATETIME`, nullable  
**Model cast:** `'provider_access_ends_at' => 'datetime'` (Carbon instance)  
**Null means:** hidden — treated identically to expired  

**Datetime comparison rule (documented):**

```php
// ProfileVisibilityService.php line 60–62
if (
    ! $profile->provider_access_ends_at
    || $profile->provider_access_ends_at->isPast()
)
```

Carbon `isPast()` compares against `now()` at **datetime precision** (not date-only). A date/time 1 second in the past is hidden. A date/time 1 second in the future is visible. There is no ambiguity around "today" — the field is `DATETIME` not `DATE`.

---

## 2. Visibility Rule Source

Single source of truth: `ProfileVisibilityService` (`app/Services/ProfileVisibilityService.php`)

**Full rule chain (evaluated in order):**

| # | Condition | Hidden reason |
|---|-----------|---------------|
| 1 | `user` missing | `NO_USER` |
| 2 | `user.is_active = false` | `USER_INACTIVE` |
| 3 | `user.is_suspended = true` | `USER_SUSPENDED` |
| 4 | `profile.is_complete = false` | `PROFILE_INCOMPLETE` |
| 5 | `provider_access_ends_at IS NULL` | `ACCESS_EXPIRED` |
| 6 | `provider_access_ends_at < now()` | `ACCESS_EXPIRED` |
| — | All pass | visible ✅ |

**SQL query method** (`applyVisibleQuery`):

```php
return $query
    ->whereNull('users.deleted_at')
    ->where('users.is_active', true)
    ->where('users.is_suspended', false)
    ->where('profiles.is_complete', true)
    ->whereNotNull('profiles.provider_access_ends_at')
    ->where('profiles.provider_access_ends_at', '>=', Carbon::now());
```

---

## 3. Pages Tested — Results

| Surface | Route | Valid (A) | Expired (B) | Null (C) | Suspended (D) | Incomplete (E) |
|---------|-------|-----------|------------|----------|--------------|----------------|
| Provider profile | `/providers/{slug}` | 200 ✅ | 404 ✅ | 404 ✅ | 404 ✅ | 404 ✅ |
| Category listing | `/category/{slug}` | visible ✅ | hidden ✅ | hidden ✅ | hidden ✅ | hidden ✅ |
| Subcategory listing | `/subcategory/{slug}` | visible ✅ | hidden ✅ | hidden ✅ | — | — |
| Top-rated | `/top-rated` | visible ✅ | hidden ✅ | hidden ✅ | — | — |
| Search | `/search` | visible ✅ | hidden ✅ | hidden ✅ | hidden ✅ | — |
| City listing | `/city/{slug}` | visible ✅ | hidden ✅ | — | — | — |
| Homepage featured | `/` | visible ✅ | hidden ✅ | — | hidden ✅ | — |

All 29 tests pass with no failures.

**How featured and visibility interact:**  
The homepage featured section uses `applyHomepageFeaturedOnly()` on top of `discoverableProfilesQuery()`. The base query already applies `applyVisibleQuery()`. An expired provider with an active featured placement is excluded at the base query level — the featured flag does not override visibility.

---

## 4. Cache Behavior

**Server-side cache (`Cache::flexible`):**

| Cache key | TTL | Behaviour |
|-----------|-----|-----------|
| `frontend.profile_counts.*` | 60s min, 300s max | Provider counts per category/city may lag up to 5 min |
| `homepage.stats.*` | 180s min, 600s max | Stats counts (provider count etc.) lag up to 10 min |

After an admin changes `provider_access_ends_at` to a past date, **listing pages will still show the provider for up to 5 minutes** while the cached counts expire. The individual provider profile page (`/providers/{slug}`) is NOT cached server-side — it calls `isDiscoverable()` on every request and returns 404 immediately.

**WARNING:** No observer fires to invalidate the `Cache::flexible` entries when `provider_access_ends_at` changes. Manual cache bust: `php artisan cache:clear` or `php artisan public-cache:clear`.

---

## 5. PWA / Browser Cache Behavior

Service worker uses **NetworkFirst with 3-second timeout** for all public HTML pages.

- **Online (healthy connection):** server response always wins. An expired provider's profile returns 404 from the server immediately — the SW caches this 404 and the stale 200 is evicted.
- **Online (slow connection > 3s timeout):** SW falls back to cached page. A cached profile page for a provider who just expired may be served. The offline banner is shown only when `!navigator.onLine`, not for SW cache fallbacks.
- **Offline:** SW serves cached page. Stale data possible.

**Assessment:** Online stale serving is only possible during the 3-second timeout window on slow connections. This is acceptable for a directory. The server-side cache (5 min lag) is a larger practical risk than the SW cache (3s window).

---

## 6. Database Indexes Verified

**Before this audit:**

| Index | Existed |
|-------|---------|
| `profiles.is_complete` | ✅ |
| `profiles(is_complete, category_id)` | ✅ |
| `profiles(is_complete, city_id)` | ✅ |
| `profiles.provider_access_ends_at` | ❌ MISSING |
| `users.is_active` | ✅ |
| `users.is_suspended` | ✅ |

**Added in this audit:**

Migration `2026_06_18_161527_add_index_to_profiles_provider_access_ends_at.php`:

```php
$table->index(['is_complete', 'provider_access_ends_at'], 'profiles_is_complete_access_ends_at_index');
```

This composite index matches the exact visibility query pattern:
`WHERE profiles.is_complete = 1 AND profiles.provider_access_ends_at >= now()`

---

## 7. Subscription / Plan References Found

| File | Reference | Classification |
|------|-----------|----------------|
| `app/Http/Middleware/EnsureProviderHasActiveSubscription.php` | Class name + reads `provider_access_ends_at` correctly | **SAFE** — old name, correct implementation |
| `app/Providers/Filament/ProviderPanelProvider.php` | `'provider.active_subscription'` middleware alias | **SAFE** — alias name only, points to correct middleware |
| `app/Filament/Provider/Widgets/StatsOverviewWidget.php` | `__('filament.widgets.subscription_ends')` label | **UI WORDING** — shows "ينتهي الاشتراك" (subscription ends) to providers instead of "ينتهي الوصول" (access ends) |
| `app/Filament/Resources/ProviderResource.php` | `__('filament.widgets.subscription_ends')` column label | **UI WORDING** — admin sees "Subscription Expires" for the `provider_access_ends_at` column |
| `app/Filament/Provider/Pages/Auth/Login.php` | `session('subscription_expired')` flash key | **SAFE** — internal session key, not user-facing text |
| `lang/*/messages.php` | `subscription_expired_login_blocked` message string | **UI WORDING** — provider sees "Your subscription has expired" on login when access is expired |
| `database/migrations/2026_06_16_212759_...php` | Backfill from `subscriptions` table | **SAFE OLD MIGRATION** — ran once, transfers data from old table |
| `database/migrations/2026_06_10_000000_optimize_subscriptions_indexes.php` | Indexes on `subscriptions` table | **SAFE OLD MIGRATION** — historical, no active code impact |

**No old subscription/plan logic affects public visibility.** All public queries go through `ProfileVisibilityService::applyVisibleQuery()` which only reads `provider_access_ends_at`. The subscription wording is a UI label issue only.

---

## 8. Tests Created and Run

**New test file:** `tests/Feature/ProviderAccessEndDateVisibilityTest.php`

**29 tests, 29 passed:**

| # | Test | Result |
|---|------|--------|
| 1 | Valid provider profile returns 200 | ✅ |
| 2 | Expired access profile returns 404 | ✅ |
| 3 | Null access profile returns 404 | ✅ |
| 4 | Suspended profile returns 404 | ✅ |
| 5 | Incomplete profile returns 404 | ✅ |
| 6 | Valid provider visible on category page | ✅ |
| 7 | Expired provider hidden on category page | ✅ |
| 8 | Null access provider hidden on category page | ✅ |
| 9 | Suspended provider hidden on category page | ✅ |
| 10 | Incomplete provider hidden on category page | ✅ |
| 11 | Valid provider visible on subcategory page | ✅ |
| 12 | Expired provider hidden on subcategory page | ✅ |
| 13 | Null access provider hidden on subcategory page | ✅ |
| 14 | Valid top-rated provider visible on /top-rated | ✅ |
| 15 | Expired high-rated provider hidden on /top-rated | ✅ |
| 16 | Null access high-rated provider hidden on /top-rated | ✅ |
| 17 | Valid provider visible in search by category | ✅ |
| 18 | Expired provider absent from search | ✅ |
| 19 | Null access provider absent from search | ✅ |
| 20 | Suspended provider absent from search | ✅ |
| 21 | Valid provider visible on city page | ✅ |
| 22 | Expired provider hidden on city page | ✅ |
| 23 | Valid featured provider appears on homepage | ✅ |
| 24 | Expired provider with active featured not on homepage | ✅ |
| 25 | Valid provider with expired placement loads homepage | ✅ |
| 26 | Suspended provider with active featured not on homepage | ✅ |
| 27 | Access ending in future is visible | ✅ |
| 28 | Access ended 1 second ago returns 404 | ✅ |
| 29 | Only valid provider visible when expired shares category | ✅ |

**Existing tests unchanged:**  
`ProviderPageVisibilityTest`: 6/6 ✅

---

## 9. Blockers and Warnings

### BLOCKER
None.

### WARNING — Cache lag on visibility change (5 minutes)

When an admin sets `provider_access_ends_at` to a past date, the listing pages (category, homepage, search) may continue showing the provider for up to 5 minutes while `Cache::flexible` keys expire. The provider detail page (`/providers/{slug}`) returns 404 immediately.

**Mitigation options:**
1. Run `php artisan public-cache:clear` after changing an end date (manual)
2. Add a `Profile::updated` observer that calls `Cache::forget()` on affected keys (automated)

For Saturday's launch this is acceptable since provider access will be manually managed.

### WARNING — Subscription wording in UI

Three places show "subscription" wording to providers and admins when the underlying logic is now access-date-only:
- Provider dashboard widget: "ينتهي الاشتراك" → should be "ينتهي الوصول"
- Provider login error: "اشتراكك منتهي الصلاحية" → should be "انتهت صلاحية وصولك"
- Admin column header: "Subscription Expires" → "Access Expires"

Not a functional bug. Purely cosmetic/wording. Can be renamed post-launch.

---

## 10. Final Verdict

**YES — Provider visibility correctly depends on `provider_access_ends_at` everywhere.**

- Single source of truth: `ProfileVisibilityService::applyVisibleQuery()` is used by all public surfaces.
- No old subscription plan logic affects visibility. Subscription references are UI labels or middleware names — all functionally correct.
- Missing index on `provider_access_ends_at` has been added.
- 29 tests prove visibility works on every public surface: profile page, category, subcategory, city, top-rated, search, homepage featured.
- Datetime comparison uses `>= now()` at datetime precision — no ambiguity.
- Cache lag (5 min) and subscription wording are the only open items, both acceptable for Saturday deployment.
