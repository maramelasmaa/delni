# Provider Panel — PHASE 0 Architecture Plan

**Status:** Planning only. No code changes yet.

---

## 1. Routes & Path Structure

**Base:** `/provider` (distinct from `/cp/admin` and public routes)

```
/provider                           → redirect to /provider/dashboard or login
/provider/login                     → provider login (already exists at /login but with provider-specific context)
/provider/dashboard                 → overview (no CRUD)
/provider/profile/edit              → PHASE 2
/provider/portfolio                 → PHASE 3
/provider/credentials               → PHASE 4
/provider/subscription              → PHASE 5 (read-only)
/provider/reviews                   → PHASE 5 (read-only)
```

**Prefix middleware:** All `/provider` routes require `auth`, `provider.role`, `user.active`, `user.not_suspended`.

---

## 2. Authentication & Middleware

**Current auth state:**
- Users authenticate via `/login` with role assignment
- Roles: `super_admin`, `provider`, `user` (Spatie)
- Providers are users with `provider` role

**New middleware to create:**
1. `provider.role` — Check `auth()->user()->hasRole('provider')`
2. `provider.owns-profile` — Verify `auth()->user()->profile_id === request()->route('profile')->id`
3. `provider.owns-resource` — Generic ownership check for any resource

**Auth flow:**
```
GET /provider          → isGuest? redirect /login : show dashboard
POST /login (existing) → if provider: redirect /provider/dashboard
                         if user: redirect /dashboard
                         if admin: redirect /cp/admin
```

**No new login page:** Reuse existing `/login`, route after login based on role.

---

## 3. Resources & Pages (Filament v5)

**Do NOT copy admin resources.** Build provider resources from scratch.

**New directory:** `app/Filament/Provider/`

Structure:
```
app/Filament/Provider/
├── Pages/
│   ├── Dashboard.php              (PHASE 1)
│   ├── EditProfile.php            (PHASE 2)
│   ├── ManagePortfolio.php        (PHASE 3)
│   ├── ManageCredentials.php      (PHASE 4)
│   └── ViewSubscription.php       (PHASE 5)
├── Resources/
│   ├── PortfolioProjectResource.php (PHASE 3)
│   └── CredentialResource.php      (PHASE 4)
└── PanelProvider.php              (register panel)
```

**Filament panel config:**
```php
// app/Filament/Provider/PanelProvider.php
Panel::make()
    ->id('provider')
    ->path('provider')
    ->login()
    ->colors([...])
    ->discoverPages(in: app_path('Filament/Provider/Pages'), for: 'App\\Filament\\Provider\\Pages')
    ->discoverResources(in: app_path('Filament/Provider/Resources'), for: 'App\\Filament\\Provider\\Resources')
    ->middleware([...])
    ->authGuard('web') // reuse default guard
```

**Important:** Filament auto-enforces Policies. Provider pages/resources must use `ProviderProfilePolicy` (new) that checks ownership.

---

## 4. Ownership & Authorization Rules

**Core principle:** A provider can only access/edit their own profile, portfolio, and credentials.

**Policy: `ProviderProfilePolicy`**
```php
namespace App\Policies;

class ProviderProfilePolicy {
    public function view(User $user, Profile $profile): bool {
        return $user->profile_id === $profile->id;
    }

    public function update(User $user, Profile $profile): bool {
        return $user->profile_id === $profile->id && 
               $user->hasRole('provider') &&
               !$user->suspended;
    }
    
    // viewAny, delete, etc. → false for provider panel
}
```

**Applied to:** Profile, PortfolioProject, Credential models in provider panel.

**Enforcement points:**
1. Route middleware `provider.owns-profile`
2. Filament Policy checks in Pages/Resources
3. Query scopes on models (e.g., `Profile::whereProfileIdOf(auth()->user())`)
4. Database constraints (foreign keys prevent orphans)

---

## 5. Data Access Matrix

### Provider CAN edit:
- `Profile.business_name`
- `Profile.bio`
- `Profile.city_id`
- `Profile.category_id` (single)
- `Profile.subcategories` (many-to-many)
- `Profile.provider_type_id`
- `Profile.phone`
- `Profile.whatsapp`
- `Profile.website`
- `Profile.social_links` (JSON)
- `Profile.avatar` (file, ≤1)
- `Profile.cover_image` (file, ≤1)
- `PortfolioProject` (create/update/delete, max 2, max 4 images each)
- `Credential` (username, type, verification_link — max 3 per type)

### Provider CAN view:
- `Profile` (own only)
- `ProfileStats` (own only) — views, rating (read-only)
- `Subscription` (own only) — status, plan, expiry (read-only)
- `Review` (own, where reviewed_profile_id matches) (read-only)

### Provider MUST NEVER access:
- `User` fields: `role`, `suspended`, `is_active`, `locked_until`, `password`
- `Profile` fields: `is_complete`, `is_verified`, `rejection_reason`, `is_homepage_featured`
- `ProfileStats` fields: `homepage_featured_until`, `created_at`, `updated_at`
- `Subscription` — any create/update/delete, only view
- `Review` — any modification or flagging
- `ActivityLog`, `MarketplacePlacement`, any admin data

---

## 6. Tests Required (PHASE 1)

**File:** `tests/Feature/ProviderPanelPhase1Test.php`

```php
class ProviderPanelPhase1Test {
    // Auth & access
    public function guest_redirects_to_login_when_accessing_provider_dashboard()
    public function normal_user_cannot_access_provider_panel()
    public function provider_allowed_on_provider_dashboard()
    public function suspended_provider_blocked()
    public function inactive_provider_blocked()
    public function provider_cannot_access_admin()
    
    // Route safety
    public function provider_dashboard_loads_without_500()
    public function config_cache_does_not_break_provider_routes()
    public function route_cache_does_not_break_provider_routes()
    
    // Locale & UI
    public function dashboard_respects_locale_ar()
    public function dashboard_respects_locale_en()
    public function empty_profile_shows_safe_empty_state_ar()
}
```

**Key assertions:**
- `assertSuccessful()` on allowed routes
- `assertTrue(in_array(..., [302, 401, 403]))` on denied routes
- `assertDatabaseHas()` to verify ownership in subsequent phases

---

## 7. Critical Guardrails

### Hard constraints enforced at DB + app layer:

1. **Ownership verification on every query**
   - Never `Profile::find($id)`, always `Profile::whereProfileIdOf(auth()->user())->findOrFail($id)`
   - Filament policies auto-check ownership

2. **Image limits (portfolio)**
   - PHASE 3: Model validation + form validation + resource action validation (3 layers)
   - Example: `PortfolioProject.images` is JSON array, size validated on create/update
   - Test concurrency: two simultaneous POST requests to add 3rd image → only one succeeds

3. **No admin field exposure**
   - Provider form schemas explicitly exclude: `is_complete`, `is_verified`, `suspension` fields
   - Hidden fields not in `$fillable` or form schema

4. **Role-based page visibility**
   - Filament pages check `canAccess()` → `auth()->user()->hasRole('provider')`
   - No listing providers except own; no "view all" endpoints

5. **Suspension/deactivation gates**
   - Middleware checks before every request: `user.active`, `user.not_suspended`
   - If provider becomes suspended mid-session, next request 403s

---

## 8. File Changes Summary (PHASE 0 only)

**No code changes.** Only this architecture document.

**PHASE 1 will add:**
- `app/Filament/Provider/PanelProvider.php`
- `app/Filament/Provider/Pages/Dashboard.php`
- `app/Http/Middleware/ProviderRole.php`
- `app/Policies/ProviderProfilePolicy.php`
- `tests/Feature/ProviderPanelPhase1Test.php`
- Update `app/Filament/PanelProvider.php` to register provider panel
- Update `.env.example` (no secrets, just path docs)

**PHASE 1 will NOT add:**
- Edit forms, CRUD pages
- Image upload
- Validation beyond empty states
- Analytics or payment integration

---

## 9. Validation Strategy per Phase

| Phase | Form Validation | DB Validation | Policy | Middleware | Test Count |
|-------|-----------------|---------------|--------|------------|------------|
| 0     | —               | —             | —      | —          | 0          |
| 1     | N/A (view)      | —             | Check role | check active/suspended | 9 |
| 2     | Filament fields | Model rules   | owns profile | ✓ | 15 |
| 3     | Image count     | Portfolio limit query | owns portfolio | ✓ | 12 |
| 4     | Type validation | —             | owns credential | ✓ | 8 |
| 5     | —               | —             | read-only | ✓ | 6 |

---

## 10. Rejection/Rollback Plan

**If any phase fails tests:**
1. Do not proceed to next phase
2. Revert phase-specific files
3. Keep database migrations (they're cumulative)
4. Fix test failures in isolation
5. Re-run phase test suite to 100% before proceeding

**Never skip a phase.** Each phase adds guardrails the next depends on.

---

## Summary

| Aspect | Decision |
|--------|----------|
| **Base path** | `/provider` |
| **Auth** | Existing login + role check |
| **Ownership** | ProviderProfilePolicy enforced in Filament |
| **PHASE 1 scope** | Shell + dashboard only, no CRUD |
| **Image limits** | 2 projects, 4 images each, 1 avatar, 1 cover, 10 total |
| **Admin fields hidden** | Explicit exclusion in forms |
| **Test-first** | Each phase has specific test suite |
| **Concurrency safe** | DB constraints + application locks |

**Ready to build PHASE 1?** Confirm and I'll start with dashboard shell + tests.
