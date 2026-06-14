# Account Deletion — Provider Impact Audit

**Date:** 2026-06-13
**Status:** Read-only audit. No code changed.
**Verdict:** See §10.

---

## 1. Current Delete Flow

```
User clicks "حذف الحساب" in /settings
  → confirmation modal (no password required)
  → POST DELETE /account (CSRF-protected)
  → SettingsController@destroy
      1. Auth::logout()          ← session ends BEFORE delete
      2. $user->delete()         ← soft-deletes User row
      3. session()->invalidate()
      4. session()->regenerateToken()
      5. redirect('home')
```

**UserObserver::deleted() fires synchronously inside step 2:**
```
$user->profile?->delete()           ← soft-deletes Profile row
SoftDeleteUserProfileJob::dispatch() ← redundant async safety net
ActivityLog::create(...)             ← immutable audit log entry
```

---

## 2. Exact Routes & Controllers

| Item | Value |
|------|-------|
| Route | `DELETE /account` |
| Name | `account.destroy` |
| Controller | `App\Http\Controllers\Public\SettingsController@destroy` |
| File | `app/Http/Controllers/Public/SettingsController.php:22` |
| Middleware | `auth`, `account.locked`, `user.active`, `user.not_suspended` |
| Password required | **No** |
| CSRF protected | Yes (`@csrf` + `@method('DELETE')`) |

---

## 3. Deletion Behavior — What Actually Happens

> **Critical concept:** `$user->delete()` is a **soft delete** (sets `deleted_at`). It does NOT remove the row. Database-level `CASCADE ON DELETE` foreign keys only fire on actual `DELETE FROM` — so **all DB cascades are bypassed by soft delete**.

### What IS soft-deleted

| Record | How | Visible via Eloquent |
|--------|-----|----------------------|
| `users` row | `deleted_at` set directly | No (global scope) |
| `profiles` row | `deleted_at` set via UserObserver | No (global scope) |

### What is NOT touched (cascade bypassed)

These FK constraints say `cascadeOnDelete()` but soft delete never triggers them:

| Table | FK constraint | Status after delete |
|-------|--------------|---------------------|
| `subscriptions` | `user_id → cascadeOnDelete` | **Retained in DB** |
| `portfolio_items` | `profile_id → cascadeOnDelete` | Retained in DB |
| `portfolio_images` | `portfolio_item_id → cascadeOnDelete` | Retained in DB |
| `provider_links` | `profile_id → cascadeOnDelete` | Retained in DB |
| `provider_credentials` | `profile_id → cascadeOnDelete` | Retained in DB |
| `reviews` (received) | `profile_id → cascadeOnDelete` | Retained in DB |
| `reviews` (written) | `user_id → restrictOnDelete` | Retained in DB |
| `user_favorites` | `user_id → cascadeOnDelete` | Retained in DB |
| `onboarding_tokens` | `user_id → cascadeOnDelete` | Retained in DB |
| `activity_logs` | `user_id → nullOnDelete` | Retained (immutable) |

### What is guaranteed deleted

Nothing is hard-deleted. Everything is either soft-deleted or silently orphaned in place.

---

## 4. Provider Impact

### Profile visibility

`ProfileVisibilityService::applyVisibleQuery()` (line 186) applies:
```php
->whereNull('users.deleted_at')
```

Any Eloquent query joining users automatically excludes the deleted provider.
The `Profile` model also has `SoftDeletes`, so direct Profile queries exclude it too.

**Verdict:** Provider profile is invisible in all Eloquent-driven queries immediately.

### Provider panel access

After `Auth::logout()` + `session()->invalidate()`, the session is gone.
Even if a session somehow survived, the `user.active` middleware would block it.

**Edge case:** `Auth::logout()` is called *before* `$user->delete()`. If deletion throws an exception, the user is already logged out but their account still exists — they cannot log back in until manually recovered.

---

## 5. Subscription Impact

**This is the most significant gap.**

`subscriptions.user_id` has `cascadeOnDelete()` in the migration, but soft delete bypasses this.

**After a provider deletes their account:**
- `subscriptions` row remains with original `is_active` and `ends_at` values
- No cancellation signal is sent
- No admin notification
- Admin sees active subscription records pointing to deleted users
- Any billing auto-renewal logic querying active subscriptions will find these orphaned records

**Scenario D (provider with active paid subscription):**
Provider disappears publicly but their subscription record is fully orphaned with no cleanup and no refund trigger.

---

## 6. Review Impact

### Reviews written by the deleted user (as reviewer)

`reviews.user_id` has `restrictOnDelete()` — but soft delete bypasses FK constraints entirely. Reviews written by this user remain with `user_id` still set to the soft-deleted user. They are **not anonymized** and **not soft-deleted**.

If review display queries join `users` without filtering `deleted_at`, the reviewer's name/avatar either renders (via `withTrashed`) or the join returns null and breaks display.

### Reviews received by the provider (as provider)

`reviews.profile_id` has `cascadeOnDelete()` — also bypassed. Reviews received remain in DB. The profile is soft-deleted so Eloquent won't return it by default, but the review rows with `profile_id` still exist and can be queried with `withTrashed`.

---

## 7. Storage & Image Impact

`PortfolioImage` uses an observer to delete physical files from disk/S3 when the model is deleted. Because `PortfolioImage` rows are never deleted (DB cascade is bypassed by soft delete), **the observer never fires**.

**Result:** All uploaded portfolio images remain in storage forever. Every deleted provider leaks their full portfolio to storage.

---

## 8. Visibility After Deletion — Per Surface

| Surface | Safe? | Mechanism |
|---------|-------|-----------|
| Homepage (featured providers) | ✅ | `whereNull('users.deleted_at')` in visibility query |
| Search results | ✅ | Same join filter |
| Category page | ✅ | Same join filter |
| City page | ✅ | Same join filter |
| `/provider/{slug}` public URL | ✅ | Profile SoftDeletes → Eloquent throws ModelNotFoundException → 404 |
| Filament admin provider list | ⚠️ Risk | If admin resource does not use `withTrashed()`, deleted providers disappear from admin too (audit gap) |
| Reviews showing reviewer name | ⚠️ Risk | `reviews.user_id` still set to soft-deleted user; display behavior untested |

---

## 9. Security Risks

| Risk | Severity | Detail |
|------|----------|--------|
| No password confirmation | **Medium** | CSRF + one modal click deletes account. XSS or CSRF bypass sufficient. |
| Logout before delete | Low | If `$user->delete()` throws, user is logged out but account still exists |
| Session invalidation | ✅ Safe | `session()->invalidate()` + `regenerateToken()` called correctly |
| API tokens (Sanctum) | ⚠️ Unknown | No `$user->tokens()->delete()` found in controller or observer. If Sanctum tokens exist, they may remain valid after deletion. |
| Provider panel access | ✅ Safe | Session invalidated; middleware blocks re-auth |
| Orphaned active subscriptions | **Medium** | Billing risk if any auto-renewal queries the subscriptions table |
| Storage leak | **Medium** | Portfolio images never cleaned up |
| GDPR / right-to-erasure gap | **Medium** | `activity_logs` are immutable (model prevents delete), contains user email and PII in `description` field |

---

## 10. Recommended Product Decision

### Current state summary

> If a provider deletes their account today: they are **hidden from all public-facing pages immediately** (visibility service works correctly). However, **subscriptions are not cancelled**, **portfolio images remain in storage**, **reviews are orphaned without anonymization**, and **no password is required** to confirm deletion.

### Options

**Option A — Fix soft-delete path (patch current behavior)**
Keep soft delete. Add: block if active subscription OR explicitly deactivate it, queue storage cleanup, anonymize reviews, add password confirmation.
Best for MVP. Fast to implement.

**Option B — Block deletion if active paid subscription exists**
In `SettingsController@destroy`, check for active subscription and return an error:
"يرجى إلغاء اشتراكك أولاً أو التواصل مع الدعم."
Simple guard, no billing complexity. User must resolve subscription manually before deleting.

**Option C — Deactivation request requiring admin approval**
Provider submits deletion request → admin reviews and handles subscription/refund → approves deletion.
More accountability. Higher implementation cost.

**Option D — Deactivate first, hard-purge after 30 days**
Set `is_active = false` immediately (provider hidden). Schedule `forceDelete` after 30 days.
Best for GDPR compliance and recovery window. Requires a scheduled purge command.

### Recommendation for Delni MVP

**Option B as a guard + Option A as the deletion path.**

1. If user has an active paid subscription → **block deletion**, show error message
2. Otherwise, proceed with soft delete and:
   - Explicitly deactivate/null the subscription record
   - Dispatch a job to clean up portfolio images from storage
   - Nullify `reviews.user_id` (anonymize reviews written by this user)
   - Add `current_password` confirmation to the delete form
   - Call `$user->tokens()->delete()` if Sanctum is in use

---

## 11. Fixes Needed (Priority Order)

| # | Fix | Priority | File |
|---|-----|----------|------|
| 1 | Add `current_password` confirmation to delete form and controller | **High** | `SettingsController@destroy`, settings view |
| 2 | Block deletion if active subscription exists, or explicitly cancel subscription on delete | **High** | `SettingsController@destroy` |
| 3 | Dispatch job to delete portfolio images from storage on user deletion | **High** | `UserObserver::deleted()` |
| 4 | Nullify `reviews.user_id` for reviews written by deleted user (anonymization) | **Medium** | `UserObserver::deleted()` |
| 5 | Revoke Sanctum/API tokens: `$user->tokens()->delete()` in observer or controller | **Medium** | `UserObserver::deleted()` |
| 6 | Ensure Filament admin resource uses `withTrashed()` for deleted provider audit trail | **Medium** | Provider Filament Resource |
| 7 | Verify review display queries filter by `users.deleted_at` | Low | Review blade/query |
| 8 | Assess activity log GDPR exposure (immutable rows contain email/PII) | Low | `ActivityLog` model, policy |

---

## Data Retention Classification

| Data | Classification |
|------|----------------|
| `users` row | Soft-deleted — retained, hidden from queries |
| `profiles` row | Soft-deleted — retained, hidden from queries |
| `subscriptions` rows | **Orphaned — retained in DB, not cancelled** |
| Reviews received by provider | Orphaned — retained, profile hidden but rows exist |
| Reviews written by user | Orphaned — retained, `user_id` still set, not anonymized |
| Review flags | Orphaned — retained |
| `portfolio_items` rows | Orphaned — retained in DB |
| Portfolio images (files) | **Orphaned — retained in storage, never cleaned up** |
| `provider_links` rows | Orphaned — retained in DB |
| `provider_credentials` rows | Orphaned — retained in DB |
| `user_favorites` rows | Orphaned — retained in DB |
| `onboarding_tokens` rows | Orphaned — retained in DB |
| `activity_logs` rows | Retained — immutable, cannot delete, contains email/PII |
