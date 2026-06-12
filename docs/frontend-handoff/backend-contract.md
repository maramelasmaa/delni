# Backend Contract — Frontend Handoff
**Delni Marketplace · v1.0 · 2026-06-12**

This document is written for frontend developers. It describes exactly what the backend guarantees, what URLs are available, what data shapes to expect, and what rules the backend already enforces so the frontend does not duplicate them.

---

## 1. Public Pages and Their URLs

All pages below are publicly accessible without authentication.

| Page | URL Pattern | Query Params Available |
|------|-------------|----------------------|
| Homepage | `/` | — |
| Search | `/search` | `city_id`, `category_id`, `subcategory_id`, `provider_type`, `remote`, `keyword`, `sort`, `per_page`, `page` |
| Top-Rated Providers | `/top-rated` | `city_id`, `category_id`, `keyword`, `per_page`, `page` |
| All Categories | `/categories` | — |
| Category Archive | `/category/{slug}` | `city_id`, `sort`, `per_page`, `page` |
| Subcategory Archive | `/subcategory/{slug}` | `city_id`, `sort`, `per_page`, `page` |
| City Archive | `/city/{slug}` | `sort`, `per_page`, `page` |
| Provider Profile | `/providers/{slug}` | — |
| Privacy Policy | `/privacy` | — |
| Terms of Service | `/terms` | — |
| Disclaimer | `/disclaimer` | — |
| Contact | `/contact` | — |

**Authenticated-only pages:**
| Page | URL |
|------|-----|
| Dashboard | `/dashboard` |
| Account Edit | `/account/edit` |

**Auth pages (guest only):**
| Page | URL |
|------|-----|
| Login | `/login` |
| Register | `/register` |
| Forgot Password | `/forgot-password` |
| Reset Password | `/reset-password/{token}` |
| Google OAuth | `/auth/google` |

---

## 2. Provider Card Data Fields

When a provider appears in listings (homepage, search, category, city), the following fields are available on each profile object:

### Core Profile Fields
| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | Internal ID |
| `slug` | string | URL-safe unique identifier. Use for links: `/providers/{slug}` |
| `business_name` | string\|null | May be null; fall back to `user.name` |
| `type` | string | Provider type string |
| `provider_type` | string\|null | Code from `provider_types` table |
| `bio` | string\|null | Short description |
| `offers_remote_work` | boolean | Whether provider accepts remote work |
| `city_id` | integer\|null | Foreign key |
| `category_id` | integer\|null | Foreign key |
| `phone` | string | Always present on visible profiles (required for completeness) |
| `whatsapp` | string | Always present on visible profiles (required for completeness) |
| `logo` | string\|null | Relative storage path (see Image URLs section) |
| `cover_image` | string\|null | Relative storage path |
| `experience_years` | integer\|null | Years of experience |
| `map_url` | string\|null | Google Maps embed URL or similar |
| `service_area_note` | string\|null | Text description of service area |
| `website` | string\|null | External URL |
| `instagram` | string\|null | External URL |
| `facebook` | string\|null | External URL |
| `linkedin` | string\|null | External URL |
| `is_complete` | boolean | Backend-guaranteed true on all visible profiles |

### Stats (always loaded with cards)
The `stats` object is present on every profile card:
```json
{
  "profile_id": 1,
  "rating_avg": "4.5",
  "reviews_count": 12,
  "is_top_rated": true,
  "is_featured": false,
  "featured_until": "2026-07-01",
  "is_homepage_featured": false,
  "homepage_featured_until": null,
  "is_top_search": false,
  "top_search_until": null,
  "is_top_category": false,
  "top_category_until": null,
  "is_top_subcategory": false,
  "top_subcategory_until": null
}
```

### Relations loaded on card listings
- `city` — `{ id, name, slug, is_active, ... }`
- `category` — `{ id, name, slug, is_active, ... }`
- `subcategories` — array of subcategory objects (loaded on category/subcategory/search pages)
- `stats` — always loaded

### Additional relations on provider profile page only
- `activeLinks` — array of `{ id, type, label, url, sort_order, is_active }`
- `credentials` — array of `{ id, title, issuer, verification_url, issue_date, notes }`
- `portfolioItems` — array (max 2, active only, sorted by sort_order):
  ```json
  {
    "id": 1,
    "title": "string",
    "short_description": "string|null",
    "description": "string|null",
    "main_url": "string|null",
    "link": "string|null",
    "sort_order": 0,
    "is_active": true,
    "images": [
      { "id": 1, "path": "portfolio/images/uuid.webp", "alt": "string|null", "sort_order": 0 }
    ]
  }
  ```
- `approvedReviews` — array of approved reviews (newest first):
  ```json
  {
    "id": 1,
    "profile_id": 1,
    "user_id": 5,
    "rating": 5,
    "status": "approved",
    "comment": "string|null",
    "is_flagged": false,
    "created_at": "2026-06-01T00:00:00.000000Z",
    "user": { "id": 5, "name": "User Name" }
  }
  ```

---

## 3. Search and Filter Parameters

### GET /api/profiles/search (JSON API)
Also used by Blade: GET /search (returns HTML)

| Parameter | Type | Constraints | Description |
|-----------|------|------------|-------------|
| `city_id` | integer | Must be a valid active city ID | Filter by city |
| `category_id` | integer | Must be a valid active category ID | Filter by category |
| `subcategory_id` | integer | Must be active; if `category_id` also sent, must belong to it | Filter by subcategory |
| `provider_type` | string | Must be a valid active `provider_types.code` | Filter by provider type |
| `remote` | boolean | `0`/`1`/`true`/`false` | Remote-capable providers only |
| `keyword` | string | Min 2 chars, max 100 chars; HTML stripped | Search in name and bio |
| `sort` | string | One of: `rating`, `reviews`, `featured`, `newest` | Sort order (default: marketplace ranking) |
| `per_page` | integer | Min 5, max 50; default 15 | Results per page (web: min 5, max 50) |
| `page` | integer | Min 1; default 1 | Page number |

**Keyword search:** Supports Arabic normalization. Searching "احمد" matches "أحمد" and "أحمد". Backend strips HTML from the keyword — it is safe to pass raw user input.

**Sort options:**
- `rating` — highest average rating first, then review count
- `reviews` — most reviews first, then average rating
- `newest` — most recently created profile first
- `featured` — featured flag first, then rating (API only)
- Default (no sort) — full marketplace ranking (see Ranking section)

---

## 4. API Endpoints — Full Request/Response

### GET /api/profiles/search

Request:
```
GET /api/profiles/search?keyword=تصوير&category_id=3&per_page=10&page=1
Accept: application/json
```

Response 200:
```json
{
  "data": [
    {
      "id": 42,
      "slug": "studio-laila",
      "business_name": "استوديو ليلى",
      "bio": "تصوير احترافي للمناسبات",
      "offers_remote_work": false,
      "city_id": 2,
      "category_id": 3,
      "logo": "profiles/avatars/550e8400-e29b-41d4-a716-446655440000.webp",
      "cover_image": null,
      "is_complete": true,
      "stats": {
        "profile_id": 42,
        "rating_avg": "4.8",
        "reviews_count": 23,
        "is_top_rated": true,
        "is_featured": false
      },
      "city": { "id": 2, "name": "جدة", "slug": "jeddah" },
      "category": { "id": 3, "name": "تصوير", "slug": "photography" }
    }
  ],
  "pagination": {
    "total": 47,
    "per_page": 10,
    "current_page": 1,
    "last_page": 5,
    "from": 1,
    "to": 10
  }
}
```

Response 422 (validation error):
```json
{
  "message": "The selected city id is invalid.",
  "errors": {
    "city_id": ["The selected city id is invalid."]
  }
}
```

Response 429 (rate limited):
```json
{
  "message": "Too Many Attempts."
}
```

---

## 5. Provider Visibility Rules — What Backend Guarantees

**The backend guarantees:** Any provider that appears in a search result, category listing, city listing, or top-rated page, AND any provider profile that returns a 200 response at `/providers/{slug}`, is a provider that has passed ALL of these checks:

1. The user account exists and is not deleted
2. The user account is active (`is_active = true`)
3. The user account is not suspended (`is_suspended = false`)
4. The profile is marked complete (`is_complete = true`)
5. The profile has an active, non-expired subscription

**Frontend must NOT:** Re-check or display any of these conditions. The backend enforces them at the query level. A 404 is returned if any condition fails on the profile page.

**What visibility-based 404 means:** If `/providers/{slug}` returns 404, it may be because the provider exists but is hidden (expired subscription, suspended, etc.), or it genuinely doesn't exist. The backend intentionally returns the same 404 in both cases to avoid information disclosure.

### Placement/Ranking Tiers
When displaying provider cards, you may optionally show badges based on `profile_stats`:

| Tier | Condition | Badge Suggestion |
|------|-----------|----------------|
| Homepage Featured | `is_homepage_featured=true AND homepage_featured_until >= today` | Gold/priority badge |
| Top Search | `is_top_search=true AND top_search_until >= today` | Search boost badge |
| Top Category | `is_top_category=true AND top_category_until >= today` | Category badge |
| Featured | `is_featured=true AND featured_until >= today` | Featured badge |
| Top Rated | `is_top_rated=true` OR (`reviews_count >= 5 AND rating_avg >= 4.5`) | Top rated badge |
| Normal | All others | No badge |

Note: `is_top_rated` in the stats table is a cached flag. The live check in ranking queries is `reviews_count >= 5 AND rating_avg >= 4.5`. Use the live check for display accuracy.

---

## 6. Review Behavior

### Submitting a Review
- **Method:** `POST /providers/{slug}/review`
- **Auth required:** Yes (`user` role only)
- **Content type:** `application/x-www-form-urlencoded` (web form)
- **Fields:**
  - `rating` — required, integer 1–5
  - `comment` — optional, string max 2000 chars

**What the backend enforces (frontend does NOT need to re-check):**
- User must have `user` role (not provider, not admin)
- User account must be active and not suspended
- User account must be at least 24 hours old
- User can submit max 10 reviews per day
- User cannot review a profile they own
- Profile must be discoverable (visible)
- One review per user per profile, including soft-deleted reviews

**What happens on success:**
- Review is created with `status = approved` (goes live immediately)
- Profile stats are recalculated asynchronously
- Session flash: `success`

**What happens on failure:**
- 422 with field errors (profile not visible, already reviewed, rate limit)
- 403 if authorization fails (wrong role, own profile)
- 429 if rate limit hit

### Flagging a Review
- **Method:** `POST /reviews/{review_id}/flag`
- **Auth required:** Yes
- **Fields:**
  - `reason` — string, the flag reason

**Rules:**
- Cannot flag your own review
- Rate limit: 20 flags per day per user
- Providers can only flag reviews on their own profile
- Users can flag any review on any visible profile

### Review Statuses
| Status | Meaning |
|--------|---------|
| `approved` | Visible to public, counts in rating |
| `rejected` | Hidden from public, excluded from rating |
| `pending` | Not currently used at submission; may be set by admin |

**Frontend only shows reviews with `status = approved` and `deleted_at = null`.** The backend already filters this in the `approvedReviews` relationship loaded on the provider page.

---

## 7. Image URL Patterns

All images are stored on the Laravel `public` storage disk. To construct a full URL, use:
```
{APP_URL}/storage/{path}
```

Where `{path}` is the value stored in the database field.

### Image Types
| Type | DB Field | Path Pattern | Dimensions |
|------|----------|-------------|-----------|
| Provider avatar/logo | `profiles.logo` | `profiles/avatars/uuid.webp` | 600×600 (square crop) |
| Provider cover image | `profiles.cover_image` | `profiles/covers/uuid.webp` | Max 1600px, aspect preserved |
| Portfolio image | `portfolio_images.path` | `portfolio/images/uuid.webp` | Max 1600px, aspect preserved |
| Icon (categories, etc.) | Served via route | `/icon/{icon_id}` | As stored |

**All images are converted to WebP** by the backend. You can safely use `<img>` with WebP — no JPEG/PNG will ever be at these paths.

### Null handling
- `logo` may be null — show a placeholder avatar
- `cover_image` may be null — show a default or no cover
- Portfolio images have `alt` text; may be null

### Image limits (backend-enforced)
- Max 1 avatar per profile
- Max 1 cover per profile
- Max 2 portfolio items per profile
- Max 4 images per portfolio item

---

## 8. Link Safety Rules

Provider links (`activeLinks` array on profile page) are user-submitted URLs. **The current backend does NOT validate these URLs** (this is a known warning). Frontend should:

1. Always open external links with `target="_blank" rel="noopener noreferrer"`
2. Never iframe provider-submitted URLs
3. Consider displaying the domain name rather than the raw URL

Provider credentials have a `verification_url` field — same caution applies.

Portfolio items have `link` and `main_url` fields — same caution applies.

Profile `website`, `instagram`, `facebook`, `linkedin` fields are also user-submitted.

**Backend will enforce URL safety in a future update.** For now, frontend is responsible for safe rendering of these fields.

---

## 9. Error Response Format

### Web Routes (HTML responses)
- `404` — rendered with Laravel error page
- `403` — rendered with Laravel error page
- `429` — rendered with `Too Many Attempts` page
- Validation errors — redirect back with `$errors` bag and `old()` input

### API Routes (`/api/*`)
All API errors return JSON automatically.

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message here."]
  }
}
```

**429 Rate Limit:**
```json
{
  "message": "Too Many Attempts."
}
```

**500 Server Error (API):**
```json
{
  "message": "An error occurred."
}
```
No stack trace is ever exposed on API routes.

---

## 10. What Frontend Must NOT Enforce as Source of Truth

| Do NOT | Why |
|--------|-----|
| Check if profile has active subscription | Backend 404s hidden profiles; query-level filter is the source of truth |
| Check `is_complete` to decide whether to show a profile | Backend already excludes incomplete profiles from all listings |
| Check `user.is_active` or `user.is_suspended` | These are never exposed in public API responses; backend filters them |
| Deduplicate reviews by user | Backend enforces one-review-per-user at DB level |
| Enforce portfolio limit of 2 items / 4 images | Backend enforces this with DB locks in observer |
| Enforce max 10 active links | Backend enforces this with DB locks in observer |
| Validate URLs for external links | Currently a gap — see Link Safety section |
| Count daily reviews per user | Backend middleware enforces this |

---

## 11. What Backend Already Guarantees

| Guarantee | Mechanism |
|-----------|-----------|
| Only discoverable profiles appear in listings | `ProfileVisibilityService::applyVisibleQuery()` applied at query level, not post-filter |
| All images are WebP format | `ProfileImageService` converts on upload |
| Avatar is square 600×600 | `ProfileImageService::storeAvatar()` forces cover crop |
| Portfolio images max 4 per item | `ProviderAssetLimitObserver` with SELECT FOR UPDATE |
| Portfolio items max 2 per profile | `ProviderAssetLimitObserver` with SELECT FOR UPDATE |
| Active links max 10 per profile | `ProviderAssetLimitObserver` with SELECT FOR UPDATE |
| Reviews only show `approved` status | `approvedReviews` relationship filters by `status = 'approved'` |
| Rating stats are eventually consistent | `RecalculateProfileStatsJob` runs async after review changes |
| Subscriptions auto-expire daily | `ExpireSubscriptionsCommand` scheduled daily |
| Ranking is deterministic | `MarketplaceRankingService` provides single source of truth |
| Arabic keyword search is normalized | `ArabicNormalizationService` applied on index and query |
| Search keyword is HTML-stripped | `SearchProfilesRequest::prepareForValidation()` |
| Password reset tokens expire in 60 minutes | Standard Laravel password broker configuration |
| Account lockout prevents brute force | `AccountSecurityService` progressive lockout |

---

## 12. Known Limitations and Warnings

| # | Limitation | Impact on Frontend |
|---|-----------|-------------------|
| 1 | Reviews go live immediately (no moderation queue) | A bad review appears instantly after submission. Consider warning users that reviews are public. |
| 2 | Provider-submitted URLs not HTTPS-validated | External links, portfolio links, credential URLs may be HTTP or contain unexpected domains. Always use `rel="noopener noreferrer"`. |
| 3 | Rating average in `profile_stats.rating_avg` is cached | There can be a brief delay between a review being submitted and the rating updating. The `reviews_count` field may also lag briefly. Show a "loading" state if real-time rating is critical. |
| 4 | Only Arabic locale supported | The locale switcher always sets `ar` regardless of the `{locale}` param. Build your UI for Arabic (RTL) as the only supported language. |
| 5 | No paginated reviews on profile page | All approved reviews are loaded at once. If a provider has hundreds of reviews, this will be a large payload. Consider lazy-loading or limiting client-side. |
| 6 | Subcategory search requires knowing the IDs | The search API uses integer IDs for `subcategory_id` and `category_id`. Your frontend must fetch the categories/subcategories list first to build filter UI. The homepage response includes these. |
| 7 | No public endpoint for a single category/subcategory/city metadata | Only the Blade views return this. If you need metadata (name, icon URL, count) for a specific category, you must hit the Blade page or use the listings that include it. |
| 8 | `is_top_rated` stats flag may lag | The `UpdateTopRatedProfilesCommand` runs daily. The live ranking query computes top-rated eligibility in real-time. For badges, use the live formula: `reviews_count >= 5 AND rating_avg >= 4.5` rather than `is_top_rated`. |
