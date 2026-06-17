# Delni — Frontend Business Rules Contract

**Date:** 2026-06-16  
**Purpose:** What the frontend can rely on, what it must not fake, and what the backend exclusively controls.

---

## 1. Provider Visibility Contract

**The backend is the single source of truth for whether a provider is visible.**

A provider appears publicly **ONLY IF ALL** of the following are true:

1. User account exists and is not deleted
2. `user.is_active = true`
3. `user.is_suspended = false`
4. `profile.is_complete = true`
5. `profile.deleted_at IS NULL`
6. Subscription exists with `is_active = true` AND `ends_at >= today`

**Frontend must NOT:**
- Show a provider based on a subset of these conditions
- Cache provider visibility decisions client-side for more than a few minutes
- Display a provider detail page by constructing a URL without checking backend discoverability (the backend returns 404 for hidden profiles)

**Frontend can rely on:**
- Any profile returned by the search API or listing pages is currently discoverable
- `GET /providers/{slug}` returns 404 (not 403) for hidden profiles — handle gracefully
- PWA cached pages can become stale; always re-validate on navigation

---

## 2. Review Contract

| Rule | Frontend Behavior |
|------|-----------------|
| Only `user`-role accounts can submit reviews | Show review form only to authenticated users with `user` role |
| Cannot review own profile | Hide form if current user is the profile owner |
| One review per user per profile | Backend returns 422 if duplicate — show error, don't retry |
| Account must be >= 24 hours old | Backend returns 422/redirect if too new — show "come back later" message |
| Max 10 reviews per user per day | Backend returns 422/redirect if over limit — show daily limit message |
| Reviews go live immediately (status = APPROVED) | No need to show "pending approval" state on submission |
| Reviews are immutable after submission | No edit button |
| Flagged reviews are NOT hidden immediately | A flagged review remains visible until admin acts |

**Frontend must NOT:**
- Implement its own duplicate-review prevention as source of truth (backend enforces via DB UNIQUE)
- Show "pending" status for newly submitted reviews (they're immediately approved)
- Assume a flag hides the review immediately

---

## 3. Profile Completeness Contract

**`is_complete` is computed by the backend, not by the frontend.**

Required fields for `is_complete = true`:

| Field | Notes |
|-------|-------|
| business_name (or user's name) | At least one must be filled |
| city_id | Must be selected |
| category_id | Must be selected |
| phone | Must be filled |
| whatsapp | Must be filled |
| subcategories | At least 1 subcategory must be selected |

**Frontend can rely on:**
- `is_complete` flag is recalculated server-side on every relevant profile update
- A profile showing `is_complete = false` will never appear in public listings

**Frontend must NOT:**
- Calculate or display completion percentage using only 5 fields — subcategories are required. The backend `ProfileCompletenessService` uses 6 fields as the authoritative list.

---

## 4. Subscription Contract

| Rule | Frontend Behavior |
|------|-----------------|
| Subscription creation is admin-only | Provider panel shows subscription as read-only |
| Subscription is never deleted | Historical subscriptions remain in provider panel list |
| Active subscription required for visibility | Profile disappears from public when subscription expires |
| Expiry runs daily (not real-time) | Up to ~24 hours of stale visibility is possible |

**Frontend can rely on:**
- Provider panel subscription view is read-only (create/edit/delete all disabled)
- Subscription status badges: active (is_active=true AND ends_at >= today), expired (ends_at < today), inactive (is_active=false)

---

## 5. Marketplace Placement Contract

| Placement | Effect | Expiry |
|-----------|--------|--------|
| Homepage Featured | Appears in homepage featured section; ranking bucket 6 | `homepage_featured_until` date |
| Top Search | Ranked at top of search results; bucket 5 | `top_search_until` date |
| Top Category | Ranked at top of category page; bucket 4 | `top_category_until` date |
| Top Subcategory | Ranked at top of subcategory page; bucket 3 | `top_subcategory_until` date |

Ranking within each placement tier: `rating_avg DESC, reviews_count DESC, created_at DESC`

**Frontend can rely on:**
- Placement flags are cleared by a daily server-side command — they are reliable
- `is_top_rated` (bucket 2) requires: reviews_count >= 5 AND rating_avg >= 4.5
- Old `is_featured` / `featured_until` fields DO NOT EXIST anymore — do not reference them

---

## 6. Category / Subcategory / City Contract

| Rule | Frontend Behavior |
|------|-----------------|
| Inactive categories/subcategories/cities do not appear in public listings | Do not filter these client-side |
| Subcategory must belong to selected category | Backend rejects cross-category assignments with 422 |
| Category pages render empty if no visible providers | Show empty state, not an error |
| Slugs are unique and URL-safe | Safe to use in `<a href>` construction |

---

## 7. Image URL Contract

| Asset | Source | URL Pattern | Fallback |
|-------|--------|------------|---------|
| Profile logo | `profile.logo` | `/storage/{path}` | Show placeholder/initials |
| Cover image | `profile.cover_image` | `/storage/{path}` | Show default cover |
| Portfolio image | `portfolio_image.path` | `/storage/{path}` | Skip/hide image |
| Category/Subcategory icon | Icon served via `/icon/{slug}` | `/icon/{slug}` | Omit icon |

**Frontend must NOT:**
- Construct storage URLs without using the `path` value from the API
- Assume a specific image format — all profile/portfolio images are stored as WebP
- Display broken images without a fallback — storage paths can become stale after re-upload

---

## 8. Pagination Contract

| Context | Default per_page | Max per_page |
|---------|-----------------|-------------|
| API search (`/api/profiles/search`) | — | 50 (web), 100 (API request param) |
| Admin panel listings | 15 (Filament default) | — |
| Provider panel listings | 15 (Filament default) | — |

API response shape for search:
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": N,
  "per_page": 12,
  "total": N
}
```

---

## 9. Error Handling Contract

| HTTP Code | When | Frontend Action |
|-----------|------|----------------|
| 404 | Provider profile not discoverable | Show "provider not found" page, not 403 |
| 403 | Policy violation | Show "access denied" page |
| 422 | Validation error (review duplicate, daily limit, etc.) | Show inline validation errors from `errors` key |
| 429 | Rate limit exceeded | Show "too many requests, try later" message |
| 401 | Not authenticated | Redirect to login |

All validation errors follow standard Laravel format:
```json
{
  "message": "...",
  "errors": {
    "field": ["Error message"]
  }
}
```

---

## 10. Authentication Contract

| Rule | Frontend Behavior |
|------|-----------------|
| Admin panel: `/cp/admin` — `super_admin` role required | Do not link non-admins here |
| Provider panel: `/provider` — `provider` role required | Do not link non-providers here |
| Suspended accounts: logged out on next request | Handle session expiry gracefully |
| Locked accounts (locked_until): redirected to provider login | Handle 401/redirect gracefully |
| Onboarding token: 72-hour expiry, single-use | Show "link expired" if invalid |

---

## 11. What Frontend Must NOT Fake or Override

The following rules are **backend-enforced only**. Frontend must never circumvent or replicate these as source of truth:

1. **Provider visibility** — Always comes from the API. Never compute from client data.
2. **Review eligibility** — 24-hour account age, daily limit, one per profile. Backend enforces.
3. **Portfolio limits** — 2 items, 4 images each. Backend enforces via observer. Hide UI buttons when limit reached, but don't rely on UI alone.
4. **Subscription validity** — is_active + ends_at check. Never show provider as "active" based on cached subscription data.
5. **Rating/review counts** — Computed asynchronously by server jobs. Short delay after review submission is expected.
6. **Role access** — Backend enforces via policies. Do not show admin/provider features to wrong role based on client state alone.
7. **SafeExternalUrl** — Backend validates external URLs. Do not render user-submitted URLs without going through backend.
