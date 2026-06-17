# Delni — Business Rules & Invariants Audit

**Date:** 2026-06-16  
**Scope:** Full codebase reverse-engineering for pre-deployment validation  
**Source of truth:** App code (models, services, observers, policies, migrations, controllers)  
**Purpose:** Verify business logic is correct, internally consistent, and deployment-safe

---

## Legend

| Status | Meaning |
|--------|---------|
| PASS | Rule exists, enforced, UI aligned |
| WARNING | Rule exists but has a gap, edge case, or production risk |
| BLOCKER | Must be fixed before deployment |
| MISMATCH | Code and UI/docs disagree |
| BACKEND ONLY | Enforced in code, no UI surface |
| UI ONLY | UI shows it, backend does not enforce |

Enforcement layers: `model` / `policy` / `request` / `controller` / `service` / `observer` / `db` / `middleware` / `ui`

---

## Section 1 — Authentication Rules

### Session vs Token Auth

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| AUTH-01 | All user sessions use web guard (cookie/session) | `config/auth.php` default guard: `web` | config | ✓ | ✓ | PASS |
| AUTH-02 | No token/API auth for user-facing routes; API search is public (no auth required) | `routes/api.php` | middleware | ✓ | ✓ | PASS |
| AUTH-03 | Session driver: database (`sessions` table) | `config/session.php` | config | ✓ | — | PASS |
| AUTH-04 | Session lifetime: 120 minutes (env: SESSION_LIFETIME) | `config/session.php` | config | ✓ | — | PASS |
| AUTH-05 | Sessions: HTTP-only cookies, same-site: lax | `config/session.php` | config | ✓ | — | PASS |
| AUTH-06 | SESSION_SECURE_COOKIE must be `true` in production (forces HTTPS-only cookies) | `config/session.php` | config | ✗ (env dependent) | — | WARNING |
| AUTH-07 | Session serialization: `json` (not PHP, prevents gadget-chain attacks) | `config/session.php` | config | ✓ | — | PASS |

### Login Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| AUTH-10 | Login rate limit: 10 attempts per 15 min per email+IP | `AppServiceProvider::configureRateLimiters()` | middleware | ✓ | — | BACKEND ONLY |
| AUTH-11 | Suspended users blocked at login via `Attempting` event listener; active session logged out via `Login` event | `AppServiceProvider`, `EnsureUserNotSuspended` | service / middleware | ✓ | ✓ | PASS |
| AUTH-12 | Inactive users (is_active=false) blocked by `EnsureUserIsActive` middleware on all auth routes | `EnsureUserIsActive` | middleware | ✓ | ✓ | PASS |
| AUTH-13 | Locked accounts (locked_until in future) blocked by `EnsureAccountNotLocked` middleware; logged out and redirected | `EnsureAccountNotLocked` | middleware | ✓ | — | BACKEND ONLY |
| AUTH-14 | Admin panel protected by `EnsureAdminRole` (requires `super_admin` role) | `AdminPanelProvider` middleware stack | middleware | ✓ | ✓ | PASS |
| AUTH-15 | Provider panel protected by `EnsureProviderRole` (requires `provider` role) | `ProviderPanelProvider` middleware stack | middleware | ✓ | ✓ | PASS |

### Google OAuth

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| AUTH-20 | Google OAuth via `SocialiteController::handleGoogleCallback()` | `SocialiteController` | controller | ✓ | ✓ | PASS |
| AUTH-21 | Google login blocks suspended users (`is_suspended` check) | `SocialiteController`, `GoogleAuthService` | service | ✓ | — | PASS |
| AUTH-22 | Google login blocks inactive users (`is_active` check) | `SocialiteController`, `GoogleAuthService` | service | ✓ | — | PASS |
| AUTH-23 | Google login: remember=true (persistent session) | `SocialiteController` | controller | ✓ | — | BACKEND ONLY |
| AUTH-24 | google_id and oauth_provider stored on User; google_id is UNIQUE | `users` migration | db | ✓ | — | PASS |
| AUTH-25 | Google-only accounts (no local password): account deletion flow does not require password confirmation | `SettingsController::destroy()` | controller | ✗ | — | WARNING |

### Onboarding (Provider Setup Link)

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| AUTH-30 | Onboarding token: 72-hour expiry, single-use | `OnboardingLinkService`, `OnboardingToken::isValid()` | service / model | ✓ | — | PASS |
| AUTH-31 | Token validated server-side: checks `used_at === null` AND `expires_at.isFuture()` | `OnboardingController::showSetPasswordForm()` | controller | ✓ | — | PASS |
| AUTH-32 | Set-password rate limit: 5 attempts/min per IP | `AppServiceProvider`, `throttle:onboarding.set-password` | middleware | ✓ | — | BACKEND ONLY |
| AUTH-33 | Password requirements: min 8 chars, letters, numbers, mixed case, symbols | `SetPasswordRequest` | request | ✓ | ✓ | PASS |
| AUTH-34 | After password set: token marked used_at=now(), redirect to provider login | `OnboardingController::setPassword()` | controller | ✓ | — | PASS |
| AUTH-35 | Debug onboarding route (`/onboarding-test/{token}`) exists — must be removed or guarded before production | `routes/web.php` | route | ✗ | — | WARNING |

### Password Reset

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| AUTH-40 | Password reset token expires in 60 minutes | `config/auth.php` expire: 60 | config | ✓ | — | PASS |
| AUTH-41 | Password reset throttle: 60-second cooldown per email | `config/auth.php` throttle: 60 | config | ✓ | — | PASS |

### Account Deletion

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| AUTH-50 | Account deletion (`DELETE /account`) requires no password confirmation | `SettingsController::destroy()` | controller | ✗ | — | WARNING |
| AUTH-51 | Account deletion: user soft-deleted (deleted_at), session invalidated | `SettingsController::destroy()` | controller | ✓ | ✓ | PASS |
| AUTH-52 | Account deletion: provider with active subscription — subscription remains (no cascade) | `subscriptions` FK: `cascadeOnDelete` on user_id — BUT subscriptions have no soft-delete | db / model | ✓ (records kept) | — | PASS |

---

## Section 2 — Roles & Panel Access

### Roles (Spatie permissions)

Three roles: `super_admin`, `provider`, `user`

| Role | Panel | Route Prefix | Middleware Stack |
|------|-------|-------------|-----------------|
| super_admin | Admin | `/cp/admin` | auth, account.locked, user.active, user.not_suspended, admin (EnsureAdminRole) |
| provider | Provider | `/provider` | auth, account.locked, user.active, user.not_suspended, provider (EnsureProviderRole) |
| user | None | `/` (public) | auth (on review/favorites/account routes) |
| guest | None | `/` (public) | none |

### Permission Matrix

| Ability | super_admin | provider | user | guest |
|---------|-------------|---------|------|-------|
| Access admin panel | ✓ | ✗ | ✗ | ✗ |
| Access provider panel | ✗ | ✓ | ✗ | ✗ |
| Browse public pages | ✓ | ✓ | ✓ | ✓ |
| View own profile | ✓ | ✓ | ✗ | ✗ |
| Edit own profile | ✗ (admin bypass) | ✓ | ✗ | ✗ |
| Create profile | ✗ (excluded from before()) | ✓ (if none exists) | ✗ | ✗ |
| Delete profile | ✗ (always false) | ✗ | ✗ | ✗ |
| Create subscription | ✓ (admin only) | ✗ | ✗ | ✗ |
| View own subscription | ✓ | ✓ | ✗ | ✗ |
| Delete subscription | ✗ (always false) | ✗ | ✗ | ✗ |
| Create review | ✗ (excluded from before()) | ✗ | ✓ | ✗ |
| Moderate review | ✓ | ✗ | ✗ | ✗ |
| Flag review | ✓ (bypass) | ✓ (own profile's reviews only) | ✓ (any visible) | ✗ |
| Suspend user | ✓ | ✗ | ✗ | ✗ |
| Manage categories/cities | ✓ | ✗ | ✗ | ✗ |
| Manage portfolio/credentials | ✓ (bypass) | ✓ (own) | ✗ | ✗ |
| Delete own account | — | — | ✓ | ✗ |
| Search public profiles | ✓ | ✓ | ✓ | ✓ |

### Filament `canAccessPanel()` Rules

| Rule ID | Description | Source | Status |
|---------|-------------|--------|--------|
| ROLE-01 | Admin panel: `User::canAccessPanel()` checks `is_active=true AND is_suspended=false AND hasRole('super_admin')` | `User::canAccessPanel()` | PASS |
| ROLE-02 | Provider panel: `User::canAccessPanel()` checks `is_active=true AND is_suspended=false AND hasRole('provider')` | `User::canAccessPanel()` | PASS |
| ROLE-03 | All admin resources implement `AdminAccessOnly` trait (`canAccess()` → `hasRole('super_admin')`) | `Filament/Resources/Traits/AdminAccessOnly.php` | PASS |
| ROLE-04 | Admin cannot delete own account via admin panel (SuperAdminGuardService protects sole super_admin) | `SuperAdminGuardService`, `UserPolicy::before()` | PASS |
| ROLE-05 | ActivityLog is immutable: model `boot()` blocks update and delete events | `ActivityLog` model | PASS |

---

## Section 3 — Provider Visibility Invariant

**Single source of truth:** `ProfileVisibilityService::applyVisibleQuery()` and `::isDiscoverable()`

### Visibility Conditions (ALL must be true)

```
Provider visible publicly IF AND ONLY IF:
  1. user EXISTS (not null)
  2. user.deleted_at IS NULL (not soft-deleted)
  3. user.is_active = true
  4. user.is_suspended = false
  5. profile.is_complete = true
  6. profile.deleted_at IS NULL (not soft-deleted)
  7. subscription EXISTS with is_active=true AND ends_at >= today()
```

Note: Subscription must have `is_active=true` (admin-approved). `approved_at` IS NULL check is wrapped into `is_active` being set to `true` only after admin approval.

### Visibility per Public Surface

| Surface | Service Used | Consistent? | Status |
|---------|-------------|-------------|--------|
| Homepage | `PublicFrontendService` → `applyVisibleQuery()` | ✓ | PASS |
| Search page | `ProfileSearchService` → `applyVisibleQuery()` | ✓ | PASS |
| Category page | `PublicFrontendService` → `applyVisibleQuery()` | ✓ | PASS |
| Subcategory page | `PublicFrontendService` → `applyVisibleQuery()` | ✓ | PASS |
| City page | `PublicFrontendService` → `applyVisibleQuery()` | ✓ | PASS |
| Top-rated page | `PublicFrontendService` → `applyVisibleQuery()` + `is_top_rated=true` | ✓ | PASS |
| Provider detail (`/providers/{slug}`) | `FrontendController::provider()` → `$profile->isDiscoverable()` → 404 if false | ✓ | PASS |
| API search (`/api/profiles/search`) | `ProfileSearchController` → `ProfileSearchService` → `applyVisibleQuery()` | ✓ | PASS |
| Favorites (`/favorites`) | `FavoriteController` → visibility service | ✓ | PASS |

### Visibility Rule Table

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| VIS-01 | applyVisibleQuery() applied on ALL public listing surfaces | `ProfileVisibilityService` | service | ✓ | ✓ | PASS |
| VIS-02 | Provider detail page returns 404 (not 403) if profile not discoverable | `FrontendController::provider()` | controller | ✓ | ✓ | PASS |
| VIS-03 | Owner always sees own profile regardless of visibility | `ProfilePolicy::view()` | policy | ✓ | ✓ | PASS |
| VIS-04 | Profile hidden immediately upon user suspension (UserObserver clears cache) | `UserObserver::updated()` | observer | ✓ | — | PASS |
| VIS-05 | Profile hidden immediately upon subscription expiry (daily scheduler) | `ExpireSubscriptionsCommand` | command | ✓ (daily, not real-time) | — | WARNING |
| VIS-06 | ProfileHiddenReason enum covers all hide conditions | `ProfileHiddenReason` enum | model | ✓ | — | PASS |

**VIS-05 Note:** Subscription expiry is checked daily at midnight. A subscription expiring at 23:00 could remain visible until the next daily run. If real-time enforcement is needed, change to query-time check only (currently both used).

---

## Section 4 — Profile Completeness

### Required Fields for is_complete = true

**Source:** `ProfileCompletenessService::evaluate()` (authoritative) + `ProfileVisibilityService::getMissingRequiredFields()`

| Field | Required for is_complete | Notes |
|-------|--------------------------|-------|
| business_name (or user.name) | ✓ | Falls back to user.name if business_name empty |
| city_id | ✓ | Must be not null |
| category_id | ✓ | Must be not null |
| phone | ✓ | NOT NULL per DB (migration 2026_06_09_203846) |
| whatsapp | ✓ | NOT NULL per DB (migration 2026_06_09_203846) |
| subcategories | ✓ | Must have at least 1 subcategory assigned |

Total: **6 fields must be satisfied** (as per ProfileCompletenessService)

### Completeness Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| COMP-01 | `is_complete` set by `ProfileCompletenessService::evaluate()` via `saveQuietly()` | `ProfileCompletenessService` | service | ✓ | — | PASS |
| COMP-02 | Completeness re-evaluated on profile update (business_name, bio, city_id, category_id, whatsapp, phone changes) | `ProfileObserver::updated()` | observer | ✓ | — | PASS |
| COMP-03 | Completeness re-evaluated when user.name changes (fallback name) | `UserObserver::updated()` | observer | ✓ | — | PASS |
| COMP-04 | **MISMATCH:** `Profile::calculateCompletionPercentage()` counts only 5 fields (excludes subcategories). `ProfileCompletenessService` requires 6 (includes subcategories). Two divergent implementations. | `Profile::calculateCompletionPercentage()` vs `ProfileCompletenessService` | model vs service | ✓ (service sets is_complete) | ? | MISMATCH |
| COMP-05 | is_complete is indexed in DB for visibility query performance | `profiles` table migration | db | ✓ | — | PASS |
| COMP-06 | Subcategories are NOT marked required in `UpdateProfileRequest` (nullable array) but ARE required for is_complete | `UpdateProfileRequest` + `ProfileCompletenessService` | request vs service | service enforces | UI shows required | MISMATCH |

**COMP-04 Action Required:** Determine which is authoritative. If subcategories ARE required for completeness (as per service), `Profile::calculateCompletionPercentage()` should reflect 6 fields and use 6 as divisor. The percentage shown to providers may be misleading (showing 100% when subcategories missing).

---

## Section 5 — Subscription Rules

### Lifecycle Diagram

```
Admin creates subscription via ProviderResource form
    ↓
SubscriptionObserver::creating() fires
    → SubscriptionLifecycleService::prepareForCreation()
        → validates provider role (SubscriptionValidationService::validateOwnership)
        → validates ends_at > starts_at
        → locks provider row (lockForUpdate)
        → checks no overlapping active subscriptions
        → sets is_active=true, approved_at=now(), approved_by=auth.id, processed_at/by
    ↓
Subscription created (is_active=true immediately upon admin creation)
    ↓
ProfileVisibilityService picks up active subscription → profile becomes visible
    ↓
[Daily] ExpireSubscriptionsCommand: sets is_active=false for ends_at < now()
    ↓
Profile becomes hidden (visibility check fails)
```

### Subscription Rules Table

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| SUB-01 | Only providers can have subscriptions | `SubscriptionValidationService::validateOwnership()` | service | ✓ | ✓ | PASS |
| SUB-02 | Subscription ends_at must be after starts_at | DB check constraint + `SubscriptionValidationService` | db / service | ✓ | ✓ | PASS |
| SUB-03 | No overlapping active subscriptions for same provider | `SubscriptionValidationService::validateDates()` with row lock | service | ✓ | — | BACKEND ONLY |
| SUB-04 | Subscription creation is admin-only | `SubscriptionPolicy::create()` returns false; admin-only via before() | policy | ✓ | ✓ | PASS |
| SUB-05 | Subscriptions are permanent (no delete) | `SubscriptionPolicy::delete()` returns false always, even for admin | policy | ✓ | ✓ | PASS |
| SUB-06 | Subscription approved at creation (approved_at/by set by SubscriptionLifecycleService) | `SubscriptionLifecycleService::prepareForCreation()` | service | ✓ | — | PASS |
| SUB-07 | Core fields immutable: user_id, plan_id, starts_at, ends_at | `SubscriptionObserver::updating()` → `assertImmutableFieldsUnchanged()` | observer | ✓ | MISMATCH | MISMATCH |
| SUB-08 | **MISMATCH:** Admin Filament SubscriptionResource shows `ends_at` as editable on edit form. Observer rejects ends_at changes. Admin save will throw an exception. | `SubscriptionResource` (edit form) vs `SubscriptionObserver` | observer vs UI | observer blocks | UI allows edit | BLOCKER |
| SUB-09 | Non-financial fields editable: notes, payment_method, payment_reference, payment_date | `UpdateSubscriptionRequest::prepareForValidation()` | request | ✓ | ✓ | PASS |
| SUB-10 | Daily command deactivates expired subscriptions (ends_at < now()) | `ExpireSubscriptionsCommand` | command | ✓ | — | PASS |
| SUB-11 | Subscription expiry deactivates profile visibility (is_active=false → applyVisibleQuery fails) | `ExpireSubscriptionsCommand` + `ProfileVisibilityService` | command + service | ✓ | — | PASS |
| SUB-12 | User account deletion cascades to subscriptions table (FK: cascadeOnDelete) but subscriptions have no soft-delete | `subscriptions` migration | db | ✓ | — | WARNING |
| SUB-13 | SubscriptionPlan features (includes_homepage_featured, includes_top_search, includes_category_spotlight) are metadata only — NOT auto-applied to create placements on subscription activation | `SubscriptionPlanResource`, no auto-placement service | — | ✗ | — | UI ONLY |

**SUB-08 Action Required (BLOCKER):** Verify whether admin can actually change ends_at or if the Filament form displays it read-only. If ends_at should be extendable, the observer must be updated to allow it. If not, the form must mark ends_at as read-only/disabled.

**SUB-13 Note:** Plan features (includes_homepage_featured etc.) appear to be informational/marketing metadata. Actual placements are set manually by admin via MarketplacePlacementResource. This is expected behavior but should be documented for admin users.

---

## Section 6 — Marketplace Placements

### Placement Fields

| Field | DB Column | Admin Label | Public Effect | Expiry Field | Ranking Bucket | Status |
|-------|-----------|-------------|--------------|-------------|----------------|--------|
| Homepage Featured | `profile_stats.is_homepage_featured` | الرئيسية | Appears in featured section on homepage | `homepage_featured_until` | 6 (highest) | PASS |
| Top Search | `profile_stats.is_top_search` | البحث | Ranked at top of search results | `top_search_until` | 5 | PASS |
| Top Category | `profile_stats.is_top_category` | التصنيف | Ranked at top of category page | `top_category_until` | 4 | PASS |
| Top Subcategory | `profile_stats.is_top_subcategory` | الفئة الفرعية | Ranked at top of subcategory page | `top_subcategory_until` | 3 | PASS |
| Top Rated | `profile_stats.is_top_rated` | — | Appears on top-rated page; bucket 2 in ranking | — (precomputed daily) | 2 | PASS |
| Normal | — | — | Default tier | — | 1 | PASS |

**Note:** Old `is_featured` / `featured_until` columns were dropped in migration 2026_06_15_000001. Any references to these old columns will fail.

### Placement Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| MKT-01 | Placement flags only active if `is_xxx=true AND xxx_until >= today()` | `MarketplaceRankingService` | service | ✓ | ✓ | PASS |
| MKT-02 | Daily command clears expired placement flags | `ExpirePlacementsCommand` | command | ✓ | — | PASS |
| MKT-03 | Ranking hierarchy (6-bucket): homepage_featured > top_search > top_category > top_subcategory > top_rated > normal | `MarketplaceRankingService` | service | ✓ | — | PASS |
| MKT-04 | Within each bucket: sorted by rating_avg DESC, reviews_count DESC, created_at DESC | `MarketplaceRankingService` | service | ✓ | — | PASS |
| MKT-05 | Top-rated eligibility: reviews_count >= 5 AND rating_avg >= 4.5 (live query check, NOT relying on is_top_rated flag in bucket 2) | `MarketplaceRankingService` | service | ✓ | — | PASS |
| MKT-06 | is_top_rated flag precomputed daily by `UpdateTopRatedProfilesCommand` (for top-rated listing page) | `UpdateTopRatedProfilesCommand`, `ProfileStats` | command | ✓ | ✓ | PASS |
| MKT-07 | Admin sets placements via MarketplacePlacementResource (profile_stats relationship) | `MarketplacePlacementResource` | ui / filament | ✓ | ✓ | PASS |
| MKT-08 | Placement fields on profile_stats, not on profiles table | `ProfileStats` model | model | ✓ | — | PASS |
| MKT-09 | Old is_featured/featured_until columns removed (migration 2026_06_15_000001). Any code referencing them will fail | migration | db | ✓ | ✓ | PASS |
| MKT-10 | SubscriptionPlan plan features NOT auto-applied to placements | No automation found | ✗ | ✗ | — | UI ONLY |

---

## Section 7 — Categories / Subcategories / Cities

### Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| CAT-01 | Categories, subcategories, cities all have is_active flag (filter inactive from public) | models + `applyVisibleQuery()` scope | model | ✓ | ✓ | PASS |
| CAT-02 | Slugs unique on categories, subcategories, cities | DB UNIQUE constraint | db | ✓ | ✓ | PASS |
| CAT-03 | Subcategory must belong to its parent category (cascadeOnDelete on category_id) | `subcategories` FK | db | ✓ | — | PASS |
| CAT-04 | Subcategory cross-category assignment blocked by UpdateProfileRequest custom closure | `UpdateProfileRequest` | request | ✓ | ✓ | PASS |
| CAT-05 | Category deletion blocked if profiles exist (restrictOnDelete FK) | `profiles.category_id` FK | db | ✓ | ✓ | PASS |
| CAT-06 | City deletion blocked if profiles exist (restrictOnDelete FK) | `profiles.city_id` FK | db | ✓ | ✓ | PASS |
| CAT-07 | Admin panel shows delete guard message on bulk delete if profiles attached | `CategoryResource`, `CityResource` | ui / filament | ✓ | ✓ | PASS |
| CAT-08 | Inactive categories/subcategories: providers can still exist but profile form rejects selecting them | `UpdateProfileRequest::category_id` rule: `where is_active=true` | request | ✓ | ✓ | PASS |
| CAT-09 | sort_order: indexed, ordered in queries | DB INDEX | db | ✓ | — | PASS |
| CAT-10 | Categories/subcategories/cities: bilingual (name, name_ar) via HasLocalizedName trait | `HasLocalizedName` | model | ✓ | ✓ | PASS |
| CAT-11 | Empty category pages (no visible providers): not blocked — page renders with empty results | `PublicFrontendService` | service | ✓ | ✓ | PASS |

---

## Section 8 — Public Search & Listings

### Search Parameters (API and Web)

| Parameter | Type | Validation | Effect |
|-----------|------|-----------|--------|
| keyword | string, min:2, max:100 | strip_tags() | Searches search_business_name (normalized Arabic) |
| city_id / city (slug) | int / string | exists:cities.id/slug, is_active | Filter by city |
| category_id / category (slug) | int / string | exists:categories.id/slug, is_active | Filter by category |
| subcategory_id / service (slug) | int / string | exists, belongs to category | Filter by subcategory |
| provider_type | string | exists:provider_types.code, is_active | Filter by provider type |
| remote | boolean | nullable | Filter offers_remote_work=true |
| sort | string | in[rating, reviews, featured, newest] | Sort order |
| per_page | int | min:5, max:50 | Pagination size (API: max 100) |
| page | int | min:1 | Page number |

### Search Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| SRCH-01 | All search/listing queries apply `ProfileVisibilityService::applyVisibleQuery()` first | `ProfileSearchService` | service | ✓ | ✓ | PASS |
| SRCH-02 | API search rate limit: 60/min (auth), 20/min (guest) per IP | `AppServiceProvider`, `throttle:search` | middleware | ✓ | — | BACKEND ONLY |
| SRCH-03 | Arabic normalization on keyword (hamza, tashkeel, ta marbuta, alef/ya variants) | `ArabicNormalizationService`, stored in search_business_name/search_bio | service + observer | ✓ | — | BACKEND ONLY |
| SRCH-04 | XSS prevention on keyword: strip_tags() in `SearchProfilesRequest::prepareForValidation()` | `SearchProfilesRequest` | request | ✓ | — | PASS |
| SRCH-05 | Slug-to-ID resolution: city/category/service slugs resolved to IDs in `prepareForValidation()` | `SearchProfilesRequest` | request | ✓ | — | PASS |
| SRCH-06 | Performance: visibility query uses composite index [user_id, is_active, ends_at] on subscriptions | `subscriptions` migration | db | ✓ | — | PASS |
| SRCH-07 | Counts (providers per category/city/subcategory) cached with flexible TTL 60-300s | `PublicFrontendService` | service | ✓ | — | PASS |
| SRCH-08 | API response includes phone and whatsapp (contact data exposed publicly) | `ProfileSearchController` | controller | ✓ | ✓ | PASS |

---

## Section 9 — Review System

### Review Lifecycle

```
User attempts to submit review
    ↓
route: POST /providers/{profile:slug}/review
    ↓
Middleware: auth → account.locked → user.active → user.not_suspended → review.eligible → throttle:reviews.create
    ↓
EnsureReviewEligible checks:
    - account created >= 24 hours ago
    - daily review count < 10
    ↓
CreateReviewRequest::authorize() → ReviewPolicy::create()
    - must have 'user' role
    - cannot review own profile
    - profile must be discoverable
    ↓
CreateReviewRequest::withValidator()
    - profile exists
    - user is_active=true, is_suspended=false
    - profile discoverable
    - no existing review for this user+profile (withTrashed)
    ↓
ReviewCreationService::create() [transactional, 5 retries]
    - locks user row
    - rechecks daily limit (<= 10)
    - creates review with status=APPROVED
    ↓
ReviewObserver::created() → dispatches RecalculateProfileStatsJob (after commit)
    ↓
Review appears publicly (approved immediately)
```

### Review Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| REV-01 | Only users with 'user' role can create reviews (not providers, not admins) | `ReviewPolicy::create()` | policy | ✓ | ✓ | PASS |
| REV-02 | Cannot review own profile | `ReviewPolicy::create()` | policy | ✓ | ✓ | PASS |
| REV-03 | Profile must be discoverable to receive reviews | `ReviewPolicy::create()` + `CreateReviewRequest::withValidator()` | policy + request | ✓ | ✓ | PASS |
| REV-04 | One review per user per profile (UNIQUE DB constraint + code check) | `reviews` UNIQUE [profile_id, user_id] + `CreateReviewRequest` | db + request | ✓ | ✓ | PASS |
| REV-05 | Soft-deleted reviews still prevent new review (withTrashed check) | `CreateReviewRequest::withValidator()` | request | ✓ | — | BACKEND ONLY |
| REV-06 | Daily review limit: max 10 reviews per user per day | `EnsureReviewEligible` middleware + `ReviewCreationService` | middleware + service | ✓ | — | BACKEND ONLY |
| REV-07 | Account age requirement: >=24 hours old | `EnsureReviewEligible` middleware | middleware | ✓ | — | BACKEND ONLY |
| REV-08 | Reviews created as status=APPROVED immediately (PENDING status exists in enum but never used on creation) | `ReviewCreationService::create()` | service | ✓ | — | MISMATCH |
| REV-09 | No review edit after submission | `ReviewPolicy::update()` returns false always | policy | ✓ | ✓ | PASS |
| REV-10 | No direct review delete by anyone (soft-delete only via admin) | `ReviewPolicy::delete()` returns false always | policy | ✓ | ✓ | PASS |
| REV-11 | Admin can approve/reject reviews; rejected reviews hidden from public | `ReviewModerationService`, `ReviewResource` | service + ui | ✓ | ✓ | PASS |
| REV-12 | Deleted/soft-deleted reviewer: user_id FK is restrictOnDelete (cannot delete user with reviews authored) | `reviews.user_id` FK | db | ✓ | — | WARNING |
| REV-13 | Reviews shown publicly only if: status=APPROVED AND not soft-deleted AND not flagged (flagging doesn't auto-hide) | `approvedReviews` relationship | model | ✓ | ✓ | PASS |
| REV-14 | rating range 1-5: DB CHECK constraint + `CreateReviewRequest` validation | `reviews` check constraint + request | db + request | ✓ | ✓ | PASS |

**REV-08 Note:** `ReviewStatus::PENDING` is defined in the enum but never assigned on review creation. It appears to be reserved for future use. Current behavior: all reviews go live immediately as APPROVED. Frontend should not expect a pending state.

**REV-12 Note:** A user with reviews cannot have their account hard-deleted without first deleting reviews. Soft-delete should work fine (user.deleted_at set), but if hard-delete is ever needed, it will be blocked.

---

## Section 10 — Review Flagging

### Flagging Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| FLAG-01 | Any authenticated user (role: user) can flag any review on any visible profile | `ReviewPolicy::flag()` | policy | ✓ | ✓ | PASS |
| FLAG-02 | Provider can only flag reviews on their own profile | `ReviewPolicy::flag()` | policy | ✓ | ✓ | PASS |
| FLAG-03 | User cannot flag their own review | `ReviewPolicy::flag()` | policy | ✓ | ✓ | PASS |
| FLAG-04 | Suspended users cannot flag (422 from FlagReviewRequest::withValidator) | `FlagReviewRequest` | request | ✓ | — | BACKEND ONLY |
| FLAG-05 | Flag reason: 10-1000 chars required | `FlagReviewRequest` | request | ✓ | ✓ | PASS |
| FLAG-06 | Rate limit: max 20 flags per user per day | `throttle:reviews.flag` | middleware | ✓ | — | BACKEND ONLY |
| FLAG-07 | **Flag does NOT hide review immediately.** Review remains APPROVED and visible after flagging. Only admin action changes visibility. | `ReviewController::flag()`, `ReviewModerationService` | controller + service | ✓ | ? | BACKEND ONLY |
| FLAG-08 | Admin accept flag: sets status=REJECTED, flag_handled_at/by — review hidden | `ReviewModerationService::acceptFlag()` | service | ✓ | ✓ | PASS |
| FLAG-09 | Admin reject flag: sets status=APPROVED, is_flagged=false, flag_handled_at/by — review stays public | `ReviewModerationService::rejectFlag()` | service | ✓ | ✓ | PASS |
| FLAG-10 | Flag workflow tracked: is_flagged, flagged_by, flagged_at, flagged_reason, flag_handled_at, flag_handled_by | `reviews` migration | db + model | ✓ | ✓ | PASS |
| FLAG-11 | Provider panel shows flagged reviews; provider CAN flag; cannot edit/delete | `ReviewsResource` (provider) | ui / filament | ✓ | ✓ | PASS |
| FLAG-12 | Admin panel: filters for flagged reviews, unhandled flags | `ReviewResource` (admin) | ui / filament | ✓ | ✓ | PASS |

**FLAG-07 Action Required:** Verify public UI does not show a "pending review" state for flagged reviews. Flagged reviews remain visible until admin acts.

---

## Section 11 — Ratings & Statistics

### Stats Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| STAT-01 | rating_avg stored in `profile_stats.rating_avg` (decimal 3,1, CHECK 0-5) | `ProfileStats` model + migration | db + model | ✓ | — | PASS |
| STAT-02 | reviews_count stored in `profile_stats.reviews_count` | `ProfileStats` model | model | ✓ | — | PASS |
| STAT-03 | Stats recalculated asynchronously via `RecalculateProfileStatsJob` (ShouldBeUnique per profile) | `ReviewObserver` → `RecalculateProfileStatsJob` | observer + job | ✓ | — | PASS |
| STAT-04 | Job retries: 3 attempts, backoff [5, 15, 30] seconds | `RecalculateProfileStatsJob` | job | ✓ | — | PASS |
| STAT-05 | Only APPROVED, non-deleted reviews count toward stats | `ProfileStatsService::recalculate()` | service | ✓ | — | PASS |
| STAT-06 | is_top_rated = true if reviews_count >= 5 AND rating_avg >= 4.5 | `ProfileStatsService::recalculate()` + `UpdateTopRatedProfilesCommand` | service + command | ✓ | — | PASS |
| STAT-07 | Top-rated precomputed daily (transactional: all reset then qualifying set) | `UpdateTopRatedProfilesCommand` | command | ✓ | — | PASS |
| STAT-08 | Job is ShouldBeUnique by profile_id — prevents duplicate recalc from burst events | `RecalculateProfileStatsJob::uniqueId()` | job | ✓ | — | PASS |
| STAT-09 | Stats initialized on profile creation (ProfileStats record created atomically) | `ProviderCreationService::createProfileForUser()` | service | ✓ | — | PASS |
| STAT-10 | Orphaned profiles without stats prevented by ProviderCreationService | `ProviderCreationService` | service | ✓ | — | PASS |

---

## Section 12 — Portfolio Rules

### Limits (enforced by ProviderAssetLimitObserver)

| Asset | Limit | Observer Event | Enforcement |
|-------|-------|---------------|-------------|
| Portfolio items per profile | Max 2 | saving | lockForUpdate transaction |
| Portfolio images per item | Max 4 | saving | lockForUpdate transaction |
| Active provider links per profile | Max 10 (active only) | saving | lockForUpdate transaction |

### Portfolio Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| PORT-01 | Max 2 portfolio items per profile | `ProviderAssetLimitObserver` | observer | ✓ | ✓ | PASS |
| PORT-02 | Max 4 images per portfolio item | `ProviderAssetLimitObserver` | observer | ✓ | ✓ | PASS |
| PORT-03 | Limit enforcement uses `lockForUpdate` transaction (race-condition safe) | `ProviderAssetLimitObserver` | observer | ✓ | — | PASS |
| PORT-04 | Only provider (owner) can create/update/delete portfolio items | `PortfolioItemPolicy`, `PortfolioImagePolicy` | policy | ✓ | ✓ | PASS |
| PORT-05 | Portfolio images: on Eloquent delete, `PortfolioImageObserver` deletes file from disk | `PortfolioImageObserver::deleted()` | observer | ✓ | — | PASS |
| PORT-06 | **WARNING: File cleanup gap.** If PortfolioItem is deleted, DB cascade deletes PortfolioImages (FK cascadeOnDelete) WITHOUT firing PortfolioImageObserver. Files on disk become orphaned. | `PortfolioImageObserver`, `portfolio_images` FK | observer gap | ✗ | — | WARNING |
| PORT-07 | Portfolio items soft-deleted with profile on user deletion | `SoftDeleteUserProfileJob` via cascades | job + db | ✓ | — | PASS |

**PORT-06 Action Required:** When deleting a PortfolioItem, first call `$item->images()->each->delete()` to trigger the observer before the cascade removes the DB rows. Or add a PortfolioItemObserver that handles file cleanup on `deleting` event.

---

## Section 13 — Credentials Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| CRED-01 | Only providers (with existing profile) can manage credentials | `ProviderCredentialPolicy::create()` | policy | ✓ | ✓ | PASS |
| CRED-02 | Credentials scoped to own profile only | `ProviderCredentialPolicy::view/update/delete()` | policy | ✓ | ✓ | PASS |
| CRED-03 | Required fields: title (255), issuer (255), issue_date | `CredentialsResource` form validation | ui / filament | ✓ (Filament validation) | ✓ | PASS |
| CRED-04 | verification_url: optional, validated with SafeExternalUrl rule | `CredentialsResource` + `SafeExternalUrl` | ui / filament + rule | ✓ | ✓ | PASS |
| CRED-05 | No admin verification workflow for credentials | — | ✗ | — | — | BACKEND ONLY |
| CRED-06 | No file/image upload for credentials (URL only via verification_url) | `ProviderCredential` model | model | ✓ | ✓ | PASS |
| CRED-07 | No limit defined on number of credentials per provider | — | ✗ | — | — | WARNING |

**CRED-07 Note:** No max credential limit found in code. Consider adding if unbounded credential creation is a concern.

---

## Section 14 — Provider Links / Social Links

### Link Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| LINK-01 | Max 10 active links per profile | `ProviderAssetLimitObserver` | observer | ✓ | ✓ | PASS |
| LINK-02 | Limit only applies when `is_active=true` (inactive links unlimited) | `ProviderAssetLimitObserver` | observer | ✓ | — | BACKEND ONLY |
| LINK-03 | Link types: website, instagram, facebook, linkedin, custom ('other') | `ProviderLink` model | model | ✓ | ✓ | PASS |
| LINK-04 | Links sorted by sort_order, active links only shown publicly | `Profile::activeLinks()` | model | ✓ | ✓ | PASS |
| LINK-05 | **URL validation for links NOT using SafeExternalUrl.** Profile social fields (website, instagram, facebook, linkedin, github, map_url) validated with SafeExternalUrl in Filament form, but ProviderLink URL field may not use it. | `UpdateProfileRequest`, `ProviderLinkPolicy` | request vs ui | partial | partial | WARNING |
| LINK-06 | External links rendered with `rel="noopener noreferrer"` in views | Blade templates | ui | ✓ | — | PASS |
| LINK-07 | Links migrate from profile columns (website, instagram, facebook, linkedin) to provider_links table | migration `2026_06_05_020000` | db | ✓ | — | PASS |

---

## Section 15 — Image / Upload Rules

### Upload Matrix

| Asset | Max Size | Allowed MIME | Resize | WebP | Disk | Storage Path | Old File Cleanup | Status |
|-------|---------|-------------|--------|------|------|-------------|-----------------|--------|
| Profile logo | 2MB | jpeg, png, webp | 600×600 (cover fit) | ✓ | public | `profiles/logos/` | ✓ (replaceImage) | PASS |
| Cover image | 4MB | jpeg, png, webp | max 1600px | ✓ | public | `profiles/covers/` | ✓ (replaceImage) | PASS |
| Portfolio image | 4MB (per form) / 5MB (Filament) | jpeg, png, webp | max 1600px | ✓ | public | `portfolio/` | ✓ (observer) | MISMATCH |
| Category/Subcategory icon | SVG only | svg | — | ✗ | icons (private) | `icons/` | — | PASS |
| Provider credentials | No file upload | URL only | — | — | — | — | — | PASS |

**Notes:**
- UpdateProfileRequest specifies portfolio images max 5MB but PortfolioResource Filament form shows 4MB. Minor MISMATCH on portfolio image limit.
- Icons stored on `icons` disk (private storage), served via `/icon/{icon}` route.
- Profile images (logo, cover) stored on `public` disk.

### Upload Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| IMG-01 | Profile logo: max 2MB, mimes jpg/png/webp | `UpdateProfileRequest` + Filament form | request + ui | ✓ | ✓ | PASS |
| IMG-02 | Cover image: max 4MB, mimes jpg/png/webp | `UpdateProfileRequest` + Filament form | request + ui | ✓ | ✓ | PASS |
| IMG-03 | All images converted to WebP before storage | `ProfileImageService` | service | ✓ | — | BACKEND ONLY |
| IMG-04 | Images validated via getimagesize() (prevents fake MIME attacks) | `ProfileImageService` | service | ✓ | — | BACKEND ONLY |
| IMG-05 | Logo resized to 600×600 (cover/crop, not stretch) | `ProfileImageService` | service | ✓ | — | BACKEND ONLY |
| IMG-06 | Old image deleted when replaced (replaceImage) | `ProfileImageService::replaceImage()` | service | ✓ | — | PASS |
| IMG-07 | Portfolio image file deleted on Eloquent delete (observer) | `PortfolioImageObserver` | observer | ✓ | — | PASS |
| IMG-08 | **Gap:** Portfolio image orphaned on cascade delete (DB-level cascade skips Eloquent observer) | `PortfolioImageObserver` | observer gap | ✗ | — | WARNING |
| IMG-09 | SafeExternalUrl applied to external link fields (website, instagram, facebook, linkedin, github, map_url, verification_url) | `UpdateProfileRequest`, `CredentialsResource` | request + ui | ✓ | ✓ | PASS |
| IMG-10 | Category SVG icons: max 500KB, mimes: svg only, stored on private disk | `StoreIconRequest` | request | ✓ | ✓ | PASS |

---

## Section 16 — Account Deletion

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| DEL-01 | Account deletion: soft-delete (deleted_at) not hard-delete | `SettingsController::destroy()` | controller | ✓ | ✓ | PASS |
| DEL-02 | **WARNING: No password confirmation required before account deletion.** | `SettingsController::destroy()` | controller | ✗ | — | WARNING |
| DEL-03 | Session invalidated on deletion | `SettingsController::destroy()` | controller | ✓ | — | PASS |
| DEL-04 | Profile soft-deleted synchronously on user delete (UserObserver) | `UserObserver::deleted()` | observer | ✓ | — | PASS |
| DEL-05 | `SoftDeleteUserProfileJob` dispatched as redundant async safety net | `UserObserver::deleted()` | observer + job | ✓ | — | PASS |
| DEL-06 | Reviews authored by deleted user: user_id FK is restrictOnDelete — blocks hard deletion if reviews exist | `reviews.user_id` FK | db | ✓ (soft-delete OK) | — | WARNING |
| DEL-07 | Provider with active subscription: subscription records remain (permanent). Profile soft-deleted. Subscription deactivated by daily scheduler. | `ExpireSubscriptionsCommand`, DB cascade | command + db | ✓ | — | PASS |
| DEL-08 | Google-only account deletion: no local password, so no password confirmation possible | `SettingsController::destroy()` | controller | ✓ (no confirmation exists anyway) | — | WARNING |
| DEL-09 | Admin cannot delete sole super_admin | `SuperAdminGuardService`, `UserPolicy::before()` | policy + service | ✓ | ✓ | PASS |
| DEL-10 | Profile storage files (logo, cover_image, portfolio images) NOT deleted on account deletion | No file cleanup on user/profile delete | ✗ | — | — | WARNING |

**DEL-02 Action Required:** Add password confirmation to `DELETE /account`. For Google-only users, add explicit "confirm deletion" step. Without this, any session compromise leads to immediate account deletion.

**DEL-10 Note:** When a provider deletes their account, profile images and portfolio files remain on disk. Consider cleaning up storage on `SoftDeleteUserProfileJob` or on hard deletion.

---

## Section 17 — Analytics / Contact Clicks

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| ANA-01 | **Profile view tracking: NOT IMPLEMENTED.** No view_count or profile_views tracking. | No implementation found | ✗ | — | — | BACKEND ONLY |
| ANA-02 | **Phone click tracking: NOT IMPLEMENTED.** Phone numbers exposed in API/pages but clicks not tracked. | No implementation found | ✗ | — | — | BACKEND ONLY |
| ANA-03 | **WhatsApp click tracking: NOT IMPLEMENTED.** WhatsApp links rendered in views but clicks not tracked. | No implementation found | ✗ | — | — | BACKEND ONLY |
| ANA-04 | WhatsApp CTA built from `ContactInfo::first()->whatsapp` with non-numeric chars stripped | `FrontendController::home()` | controller | ✓ | — | PASS |
| ANA-05 | Phone/WhatsApp data returned in API search response (public) | `ProfileSearchController` | controller | ✓ | ✓ | PASS |

**Note:** Analytics/contact tracking was in the audit scope but no implementation exists. This is expected if intentionally deferred.

---

## Section 18 — Queues / Jobs / Schedule

### Jobs

| Job | Queue | Tries | Backoff | Unique | Purpose |
|-----|-------|-------|---------|--------|---------|
| `RecalculateProfileStatsJob` | default | 3 | [5, 15, 30]s | ✓ per profile | Recalculates rating_avg and reviews_count |
| `SoftDeleteUserProfileJob` | default | 3 | [5, 30, 60]s | ✗ | Soft-deletes profile when user deleted (redundant safety net) |

### Scheduled Commands

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `subscriptions:expire` | Daily | Deactivates subscriptions with ends_at < now() |
| `placements:expire` | Daily | Clears expired placement flags |
| `profiles:update-top-rated` | Daily | Recomputes is_top_rated flags |
| `users:clear-expired-locks` | Every 5 min | Nulls locked_until for expired locks |
| Scheduler heartbeat | Every minute | Cache heartbeat for SchedulerHealthCheckCommand |

### Queue / Schedule Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| QUEUE-01 | Queue connection: database (env: QUEUE_CONNECTION). Must NOT be `sync` in production. | `config/queue.php` | config | ✓ (env-dependent) | — | WARNING |
| QUEUE-02 | Queue after-commit: true (jobs dispatch after transaction commits) | `config/queue.php` afterCommit: true | config | ✓ | — | PASS |
| QUEUE-03 | queue:work must be running in production for stats recalculation | deployment requirement | — | ✗ (not self-enforcing) | — | WARNING |
| QUEUE-04 | Scheduler must be running in production for subscription/placement expiry | deployment requirement | — | ✗ (not self-enforcing) | — | WARNING |
| QUEUE-05 | RecalculateProfileStatsJob is ShouldBeUnique (one per profile, prevents burst duplicates) | `RecalculateProfileStatsJob` | job | ✓ | — | PASS |
| QUEUE-06 | SoftDeleteUserProfileJob is backup-only; profile also deleted synchronously in UserObserver | `UserObserver::deleted()` | observer | ✓ | — | PASS |
| QUEUE-07 | IntegrityAuditCommand (`integrity:audit`) available to detect orphaned records, overlapping subscriptions, duplicates | `IntegrityAuditCommand` | command | ✓ | — | PASS |
| QUEUE-08 | CheckQueueDeploymentCommand (`queue:deployment-check`) verifies production queue setup | `CheckQueueDeploymentCommand` | command | ✓ | — | PASS |
| QUEUE-09 | SchedulerHealthCheckCommand monitors daily task success timestamps (max 36h stale) | `SchedulerHealthCheckCommand` | command | ✓ | — | PASS |

### Production Deployment Worker/Scheduler Checklist

- [ ] `php artisan queue:work --sleep=3 --tries=3 --timeout=60` (or Supervisor equivalent)
- [ ] Laravel scheduler registered in cron: `* * * * * php artisan schedule:run`
- [ ] `QUEUE_CONNECTION=database` (not `sync`) in `.env`
- [ ] `failed_jobs` table exists (migration)
- [ ] Run `php artisan queue:deployment-check` before go-live
- [ ] Run `php artisan scheduler:health-check` after first scheduler run
- [ ] Run `php artisan integrity:audit` before go-live for data integrity

---

## Section 19 — Cache / PWA Invariants

### Cache Rules

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| CACHE-01 | Cache driver: database (env: CACHE_STORE) | `config/cache.php` | config | ✓ | — | PASS |
| CACHE-02 | Cache serializable classes: false (prevents gadget-chain attacks) | `config/cache.php` | config | ✓ | — | PASS |
| CACHE-03 | Profile counts cached with flexible TTL: 60s (fresh) to 300s (stale-while-revalidate) | `PublicFrontendService` | service | ✓ | — | PASS |
| CACHE-04 | Cache invalidated on profile visibility changes (ProfilePublicCacheObserver) | `ProfilePublicCacheObserver` | observer | ✓ | — | PASS |
| CACHE-05 | Cache invalidated on user suspension (UserObserver::clearPublicCacheOnSuspension) | `UserObserver` | observer | ✓ | — | PASS |
| CACHE-06 | Scheduler command results cached for 7 days (success timestamps for health checks) | `ExpireSubscriptionsCommand` etc. | command | ✓ | — | PASS |
| CACHE-07 | No caching of individual profile pages (each request is live) | `FrontendController` | controller | ✓ | — | PASS |

### PWA Rules

PWA implementation exists (see `docs/PWA_IMPLEMENTATION.md`). Key invariants:

| Rule ID | Description | Status |
|---------|-------------|--------|
| PWA-01 | Admin/provider panel routes must be excluded from PWA cache | Verify in service worker config |
| PWA-02 | Review submission and flag routes must be network-first (not cached) | Verify in service worker config |
| PWA-03 | API search results: can be stale (short TTL acceptable) | PASS |
| PWA-04 | Provider profile page: should be network-first (visibility may change) | Verify |

---

## Section 20 — Security Invariants

| Rule ID | Description | Source | Enforcement | Backend? | UI Aligned? | Status |
|---------|-------------|--------|-------------|----------|-------------|--------|
| SEC-01 | CSRF protection: all web POST/PUT/DELETE forms use @csrf | Laravel default + Filament | framework | ✓ | ✓ | PASS |
| SEC-02 | Security headers: X-Frame-Options (SAMEORIGIN), X-Content-Type-Options (nosniff), Referrer-Policy, Permissions-Policy | `SecurityHeaders` middleware | middleware | ✓ | — | PASS |
| SEC-03 | HSTS applied in production only (via SecurityHeaders middleware) | `SecurityHeaders` | middleware | ✓ | — | PASS |
| SEC-04 | SafeExternalUrl rule: blocks http, javascript, data, file, ftp, vbscript schemes; blocks private IPs | `SafeExternalUrl` | rule | ✓ | ✓ | PASS |
| SEC-05 | Upload validation: MIME + extension + getimagesize() verification | `ProfileImageService` | service | ✓ | — | PASS |
| SEC-06 | Session: HTTP-only, same-site lax, SESSION_SECURE_COOKIE (must be true in prod) | `config/session.php` | config | ✓ (env-dep) | — | WARNING |
| SEC-07 | No user enumeration: rate limiting on login by email+IP (no "email not found" leak possible via timing) | `AppServiceProvider` | middleware | ✓ | — | PASS |
| SEC-08 | Password requirements enforced: min 8, letters, numbers, mixed case, symbols | `SetPasswordRequest`, `Password::defaults()` | request | ✓ | ✓ | PASS |
| SEC-09 | Account lockout (locked_until): unlocked by `ClearExpiredLocksCommand` every 5 min | `EnsureAccountNotLocked`, `ClearExpiredLocksCommand` | middleware + command | ✓ | — | PASS |
| SEC-10 | Admin panel completely separated from provider panel (different URL prefixes, different middleware) | `AdminPanelProvider`, `ProviderPanelProvider` | middleware | ✓ | ✓ | PASS |
| SEC-11 | XSS prevention: keyword search uses strip_tags() | `SearchProfilesRequest` | request | ✓ | — | PASS |
| SEC-12 | SQL injection prevention: Eloquent ORM used throughout, no raw user-input SQL | codebase | framework | ✓ | — | PASS |
| SEC-13 | **WARNING:** Debug onboarding route `/onboarding-test/{token}` present in routes — must be removed in production | `routes/web.php` | route | ✗ | — | WARNING |
| SEC-14 | APP_DEBUG must be false in production | `.env` requirement | config | ✗ (env-dep) | — | WARNING |
| SEC-15 | Contact data (phone, whatsapp) fully exposed in API search response — no rate limit on data extraction beyond the 20/60 req/min API limit | `ProfileSearchController` | controller | partial | — | WARNING |
| SEC-16 | Trusted proxies configured for single-host VPS (127.0.0.1) — update if behind load balancer | `bootstrap/app.php` | config | ✓ (for single VPS) | — | WARNING |
| SEC-17 | ActivityLog is immutable (model boot prevents updates/deletes) | `ActivityLog::boot()` | model | ✓ | — | PASS |
| SEC-18 | No debug/test routes exposed in production beyond onboarding-test | routes inspection | route | ✓ | — | PASS |

---

## Section 21 — Admin Panel Rules Summary

| Rule ID | Description | Status |
|---------|-------------|--------|
| ADMIN-01 | All admin resources require super_admin role via AdminAccessOnly trait | PASS |
| ADMIN-02 | Admin can suspend/reinstate users with required reason (10-1000 chars) | PASS |
| ADMIN-03 | Admin cannot self-suspend | PASS |
| ADMIN-04 | Admin cannot delete sole super_admin | PASS |
| ADMIN-05 | Admin can create/manage subscriptions (provider panel read-only) | PASS |
| ADMIN-06 | Admin can moderate reviews (approve/reject, handle flags) | PASS |
| ADMIN-07 | Admin cannot create/delete profiles | PASS |
| ADMIN-08 | Admin can manage marketplace placements | PASS |
| ADMIN-09 | Admin subscription edit form shows ends_at as editable but observer blocks it | BLOCKER |
| ADMIN-10 | Admin can manage categories, subcategories, cities, provider types | PASS |
| ADMIN-11 | Activity logs: read-only, admin-only view, immutable records | PASS |

---

## Section 22 — Provider Panel Rules Summary

| Rule ID | Description | Status |
|---------|-------------|--------|
| PROV-01 | Provider can create/edit own profile (not delete) | PASS |
| PROV-02 | Provider has read-only view of own subscription | PASS |
| PROV-03 | Provider can manage portfolio (max 2 items, 4 images each) | PASS |
| PROV-04 | Provider can manage credentials | PASS |
| PROV-05 | Provider can flag reviews on own profile | PASS |
| PROV-06 | Provider CANNOT create/edit/delete reviews | PASS |
| PROV-07 | Provider CANNOT modify subscription | PASS |

---

## Section 23 — API Contracts

| Endpoint | Auth | Rate Limit | Public Visibility Filter | Response |
|---------|------|-----------|--------------------------|---------|
| `GET /api/profiles/search` | Optional | 60/min (auth), 20/min (guest) | `applyVisibleQuery()` | Paginated profiles with stats, city, category, phone, whatsapp |

---

## Section 24 — Localization

| Rule ID | Description | Status |
|---------|-------------|--------|
| L10N-01 | Bilingual support: Arabic (ar) + English (en) | PASS |
| L10N-02 | All business error messages translated in lang/ar/messages.php | PASS |
| L10N-03 | SetLocale middleware applies locale per request | PASS |
| L10N-04 | FrontendController forces locale to 'ar' (Arabic-only MVP) | PASS |
| L10N-05 | All Filament labels in lang files | PASS |

---

## Final Summary

### 1. Is business logic consistent?

**Mostly yes, with two important inconsistencies:**

- **COMP-04/COMP-06 (MISMATCH):** `Profile::calculateCompletionPercentage()` counts 5 fields; `ProfileCompletenessService` requires 6 (adds subcategories). The service is authoritative (it sets `is_complete`), but the model method is misleading and may affect any UI that shows a "% complete" progress bar.

- **SUB-07/SUB-08 (BLOCKER):** Admin Filament SubscriptionResource shows `ends_at` as editable on the edit form, but `SubscriptionObserver::updating()` calls `assertImmutableFieldsUnchanged()` which rejects changes to `ends_at`. Admin attempting to modify `ends_at` will receive an unhandled exception.

### 2. Are critical invariants backend-enforced?

**Yes.** All of the following are enforced at the backend (not just UI):
- Provider visibility (service layer, applied on every query)
- Subscription uniqueness/overlap (row-locked transaction)
- Review once-per-user (DB UNIQUE + code check)
- Portfolio limits (observer with lockForUpdate)
- Role/permission isolation (policies + middleware)
- Subscription immutability (observer)
- Auth lockout/suspension (middleware + event listeners)

### 3. Are there UI/backend mismatches?

| # | Issue | Severity |
|---|-------|---------|
| 1 | `ends_at` editable in admin subscription form but observer rejects it | BLOCKER |
| 2 | `calculateCompletionPercentage()` (5 fields) vs service (6 fields including subcategories) | WARNING |
| 3 | Portfolio image size: request says 5MB, Filament form says 4MB | Minor |
| 4 | SubscriptionPlan features not auto-applied to placements | By design (document for admin) |
| 5 | ReviewStatus::PENDING never used on creation (all go live as APPROVED) | Minor, informational |

### 4. Are there deployment blockers?

| # | Rule ID | Issue |
|---|---------|-------|
| 1 | SUB-08 | Admin subscription edit: ends_at shown as editable but observer blocks changes — will cause unhandled exceptions |
| 2 | AUTH-35 / SEC-13 | Debug route `/onboarding-test/{token}` must be removed or gated before production |
| 3 | AUTH-06 / SEC-06 | `SESSION_SECURE_COOKIE=true` must be set in production `.env` |
| 4 | SEC-14 | `APP_DEBUG=false` must be confirmed in production `.env` |
| 5 | QUEUE-01 | `QUEUE_CONNECTION` must not be `sync` in production |

### 5. What must be fixed before deploy?

**P0 — Must fix:**
1. **SUB-08:** Resolve admin SubscriptionResource `ends_at` field — either make it truly read-only in the Filament form, or remove the `ends_at` immutability from the observer if subscription extension should be allowed.
2. **SEC-13:** Remove or gate `/onboarding-test/{token}` route.
3. **Production .env:** Confirm `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `QUEUE_CONNECTION=database`.

**P1 — Should fix:**
4. **DEL-02:** Add password confirmation to `DELETE /account` (or explicit confirmation step for Google-only accounts).
5. **PORT-06/IMG-08:** Fix portfolio image file orphaning on cascade delete by adding a PortfolioItemObserver::deleting() that cleans up files before cascade.
6. **COMP-04:** Reconcile `Profile::calculateCompletionPercentage()` with `ProfileCompletenessService` (add subcategories to model method or remove the model method).

### 6. What can wait after launch?

- CRED-07: Credential count limit (no current abuse risk at MVP scale)
- ANA-01/02/03: Analytics/click tracking (deferred feature)
- DEL-10: Storage cleanup on account deletion (low risk, disk cleanup can be deferred)
- IMG-09/LINK-05: ProviderLink URL validation gap (low risk, providers are authenticated)
- SEC-15: Contact data rate limiting (acceptable for MVP)
- REV-05/REV-08: PENDING status clarification (informational only)

### Final Verdict

> **Delni's business logic is substantially safe and internally consistent for deployment, with one BLOCKER that must be resolved first (SUB-08: admin subscription ends_at edit), one security item to remove (debug route), and production `.env` configuration to confirm. Core invariants — visibility, reviews, subscriptions, portfolio limits, roles — are properly backend-enforced. The codebase demonstrates strong separation of concerns through dedicated services, policies, and observers.**
