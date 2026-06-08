# Delni Account Status System Audit

Date: 2026-06-05
Focus: حالة الحساب: نشط / موقوف
Method: implementation is the source of truth

## Executive Summary

Delni currently has two account control fields that matter most:

- `is_active`: account is enabled or internally deactivated.
- `is_suspended`: account is suspended by admin action.

For frontend simplicity:

- `نشط` means `is_active = true` and `is_suspended = false`.
- `موقوف` means `is_suspended = true`.

Provider visibility is stricter than account status. A provider whose account is `نشط` is only publicly visible when profile completeness and subscription rules also pass.

## State Machine

```text
Created account
  |
  | default users.is_active = true
  | default users.is_suspended = false
  v
نشط / Active
  fields:
    is_active = true
    is_suspended = false
  behavior:
    login allowed
    provider/admin panel allowed by role
    provider profile may be visible if profile + subscription rules pass
    reviews/flags allowed if review eligibility rules pass

نشط / Active
  |
  | UserSuspensionService::suspend()
  v
موقوف / Suspended
  fields:
    is_suspended = true
    suspended_at = timestamp
    suspended_by = admin id
    suspension_reason = text
  behavior:
    login blocked
    active web sessions blocked/logged out by middleware
    provider panel blocked
    provider public profile hidden
    search/category/city/homepage discovery hidden
    review and flag routes blocked
    data retained

موقوف / Suspended
  |
  | UserSuspensionService::reinstate()
  v
نشط / Active, if is_active remains true
  fields:
    is_suspended = false
    reinstated_at = timestamp
    reinstated_by = admin id
    reinstatement_reason = text
  behavior:
    access and visibility can resume if other rules pass

Any state
  |
  | is_active = false
  v
Inactive / Deactivated internal state
  behavior:
    login blocked
    active sessions blocked/logged out by middleware
    provider profile hidden
```

Related internal states:

- Locked: `locked_until` in the future blocks login/access temporarily.
- Must change password: `must_change_password = true` allows authentication but redirects protected routes to password change.
- Security flagged: `security_flagged` exists as a field but is not the same as suspended.

## Fields Used

Primary status fields:

- `users.is_active`
- `users.is_suspended`

Suspension metadata:

- `users.suspension_reason`
- `users.suspended_at`
- `users.suspended_by`
- `users.reinstated_at`
- `users.reinstated_by`
- `users.reinstatement_reason`

Related access-control fields:

- `users.must_change_password`
- `users.password_changed_at`
- `users.failed_login_attempts`
- `users.last_failed_login_at`
- `users.locked_until`
- `users.security_flagged`

## Verified Rules

RULE-ACC-001: Active login allowed

- Status: VERIFIED
- Source: `app/Http/Controllers/Auth/AuthController.php`, `login()`
- Trigger: POST `/login`
- Result: user with valid credentials, active account, not suspended, not locked is authenticated and redirected.

RULE-ACC-002: Inactive login blocked

- Status: VERIFIED
- Source: `app/Http/Controllers/Auth/AuthController.php`, `login()`
- Trigger: POST `/login`
- Result: `is_active = false` causes logout, session invalidation, token regeneration, and validation error.

RULE-ACC-003: Suspended login blocked

- Status: VERIFIED
- Source: `app/Http/Controllers/Auth/AuthController.php`, `login()`
- Trigger: POST `/login`
- Result: `is_suspended = true` causes logout, session invalidation, token regeneration, and validation error.

RULE-ACC-004: Suspended or inactive panel access blocked

- Status: VERIFIED
- Source: `app/Models/User.php`, `canAccessPanel()`
- Trigger: Filament panel authorization
- Result: inactive or suspended users cannot access admin/provider panels even if their role matches.

RULE-ACC-005: Suspended active sessions are blocked

- Status: VERIFIED
- Source: `app/Http/Middleware/EnsureUserNotSuspended.php`, `handle()`
- Trigger: authenticated route access
- Result: suspended users are logged out, session is invalidated, and token is regenerated.

RULE-ACC-006: Inactive active sessions are blocked

- Status: VERIFIED
- Source: `app/Http/Middleware/EnsureUserIsActive.php`, `handle()`
- Trigger: authenticated route access
- Result: inactive users are logged out, session is invalidated, and token is regenerated.

RULE-ACC-007: Provider public profile hidden while suspended

- Status: VERIFIED
- Source: `app/Services/ProfileVisibilityService.php`, `isDiscoverable()` and `applyVisibleQuery()`
- Trigger: public search/detail visibility checks
- Result: profiles whose owner has `is_suspended = true` are hidden.

RULE-ACC-008: Provider public profile hidden while inactive

- Status: VERIFIED
- Source: `app/Services/ProfileVisibilityService.php`, `isDiscoverable()` and `applyVisibleQuery()`
- Trigger: public search/detail visibility checks
- Result: profiles whose owner has `is_active = false` are hidden.

RULE-ACC-009: Suspended users cannot review or flag

- Status: VERIFIED
- Source: `routes/web.php`, review route middleware group; `app/Http/Middleware/EnsureUserNotSuspended.php`
- Trigger: POST `/provider/{profile:slug}/review`, POST `/reviews/{review}/flag`
- Result: request is blocked before controller logic.

RULE-ACC-010: Suspension and reinstatement preserve data

- Status: VERIFIED
- Source: `app/Services/UserSuspensionService.php`, `suspend()` and `reinstate()`
- Trigger: admin suspension/reinstatement
- Result: only user status metadata is updated. Profile, reviews, portfolio, and subscriptions remain.

RULE-ACC-011: Suspension is activity logged

- Status: VERIFIED
- Source: `app/Observers/UserObserver.php`, `updated()` and `logSuspensionChange()`
- Trigger: `is_suspended` changes to true
- Result: `ActivityLog` action `user_suspended`.

RULE-ACC-012: Reinstatement is activity logged

- Status: VERIFIED
- Source: `app/Observers/UserObserver.php`, `updated()` and `logSuspensionChange()`
- Trigger: `is_suspended` changes to false
- Result: `ActivityLog` action `user_unsuspended`.

RULE-ACC-013: Admin self-suspension blocked at service layer

- Status: VERIFIED
- Source: `app/Services/UserSuspensionService.php`, `suspend()`
- Trigger: authenticated admin attempts to suspend their own user record
- Result: validation exception; no status change.

## Files Checked

- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/2026_06_02_190550_update_schema_integrity_indexes_and_security_columns.php`
- `app/Models/User.php`
- `app/Policies/UserPolicy.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Middleware/EnsureUserIsActive.php`
- `app/Http/Middleware/EnsureUserNotSuspended.php`
- `app/Http/Middleware/EnsureAccountNotLocked.php`
- `app/Http/Middleware/EnsurePasswordChanged.php`
- `app/Http/Middleware/CheckSuspensionAfterAuth.php`
- `app/Http/Middleware/EnsureAdminRole.php`
- `app/Http/Middleware/EnsureProviderRole.php`
- `app/Http/Middleware/EnsureReviewEligible.php`
- `bootstrap/app.php`
- `routes/web.php`
- `routes/api.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Providers/Filament/ProviderPanelProvider.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/ProviderResource.php`
- `app/Services/UserSuspensionService.php`
- `app/Services/ProfileVisibilityService.php`
- `app/Services/ProfileSearchService.php`
- `app/Services/PublicFrontendService.php`
- `app/Observers/UserObserver.php`
- `app/Http/Controllers/Public/ReviewController.php`
- `app/Http/Requests/Review/CreateReviewRequest.php`
- `app/Http/Requests/Review/FlagReviewRequest.php`
- `tests/Feature/AccountStatusSystemTest.php`
- `tests/Feature/ProfileVisibilityConsolidationTest.php`
- `tests/Feature/ReviewSystemHardeningTest.php`
- `tests/Feature/BackendBusinessRulesTest.php`

## Issues Found

ISSUE-ACC-001: Duplicate status concepts exposed

- Severity: Medium
- Impact: frontend/admin confusion
- Evidence: `is_active` and `is_suspended` both exist and are shown in admin resources.
- Recommendation: expose one derived frontend status for the two-state UI. Use `نشط` for `is_active && ! is_suspended`, and `موقوف` for `is_suspended`. Treat inactive as an internal admin state.

ISSUE-ACC-002: Admin resource forms can still expose raw status toggles

- Severity: Medium
- Impact: suspension metadata can be bypassed if admins directly toggle `is_suspended` instead of using the suspend/reinstate actions.
- Evidence: `app/Filament/Resources/UserResource.php` and `app/Filament/Resources/ProviderResource.php` include account status fields and separate suspend/reinstate actions.
- Recommendation: make suspension changes action-only, or make raw suspension fields read-only.

ISSUE-ACC-003: Inactive changes are not activity logged

- Severity: Low
- Impact: audit trail gap for deactivation/reactivation separate from suspension.
- Evidence: `app/Observers/UserObserver.php` logs `is_suspended` and password/name changes, not `is_active`.
- Recommendation: log `is_active` transitions if inactive remains an operational state.

ISSUE-ACC-004: Suspension middleware is duplicated

- Severity: Low
- Impact: extra complexity and possible divergent future behavior.
- Evidence: Filament panels include `CheckSuspensionAfterAuth` and `user.not_suspended`.
- Recommendation: consolidate on one suspension middleware path.

ISSUE-ACC-005: Generic web blocked redirects may point to Filament login

- Severity: Low
- Impact: confusing UX for public users.
- Evidence: active/suspended/locked middleware redirect to Filament login routes for some non-panel paths.
- Recommendation: route ordinary web users to `/login`; keep Filament login redirects for panel paths.

## Fixes Applied

- Hardened `User::canAccessPanel()` so inactive and suspended users cannot enter Filament panels by role alone.
- Hardened `UserSuspensionService::suspend()` so an authenticated admin cannot suspend their own account through the service layer.
- Added focused account-status tests covering login, session blocking, profile visibility, provider panel blocking, review/flag blocking, data retention, reinstatement, activity logs, and self-suspension prevention.

## Test Coverage Added

Added `tests/Feature/AccountStatusSystemTest.php`:

- active user can log in
- inactive user cannot log in
- suspended user cannot log in
- logged-in user is blocked after suspension
- suspended provider profile hidden from search
- suspended provider profile hidden from direct URL
- suspended provider cannot access provider panel
- suspended user cannot review
- suspended user cannot flag
- admin can suspend provider
- admin can reinstate provider
- suspension does not delete profile, reviews, portfolio, or subscriptions
- reinstatement restores visibility when profile and subscription rules pass
- admin cannot suspend themselves through the service

## Verification

Commands run:

```bash
php artisan test --compact tests\Feature\AccountStatusSystemTest.php
php artisan test --compact tests\Feature\ReviewSystemHardeningTest.php
php artisan test --compact tests\Feature\ProfileVisibilityConsolidationTest.php
vendor\bin\pint --dirty --format agent
php artisan test --compact tests\Feature\AccountStatusSystemTest.php
```

Results:

- Account status tests: 8 passed, 38 assertions.
- Review hardening tests: 8 passed, 48 assertions.
- Profile visibility tests: 13 passed, 32 assertions.
- Pint passed.

## Frontend Readiness

Frontend can safely use a derived two-state account status if the backend exposes it consistently:

```php
$status = $user->is_suspended
    ? 'suspended'
    : ($user->is_active ? 'active' : 'inactive');
```

Recommended display:

- `active` => `نشط`
- `suspended` => `موقوف`

Do not expose raw `is_active` and `is_suspended` as independent public controls. If `inactive` must remain, keep it admin-only or map it to a blocked/internal label rather than mixing it into the simple frontend status.
