# Review System Audit: Rejected Reviews, Duplicate Rules, and UX Clarity

**Date:** 2026-06-18
**Status:** COMPLETE ✅
**Tests:** 12/12 passing | Full suite: 120/120 passing

---

## Problem Statement

A user wrote a review. Admin rejected/hid it. The same user then tried to add a new review for the same provider. The system silently blocked the request without a clear explanation.

---

## Audit Findings

### 1. Duplicate Check (Before Fix)

**Location:** `app/Services/ReviewCreationService.php` and `app/Http/Requests/Review/CreateReviewRequest.php`

**Old behavior:** Used `Review::withTrashed()->...->exists()` with no status filter — any existing review (approved, rejected, soft-deleted) permanently blocked resubmission.

**Old DB constraint:** `unique(['profile_id', 'user_id'])` in `database/migrations/2026_06_02_184239_create_reviews_table.php` — at the database level, a second insert would always fail regardless of status.

### 2. Status Lifecycle

- Reviews are created with `ReviewStatus::APPROVED` immediately (no pending queue on submission)
- Admin can reject a review by accepting a flag → sets `status = REJECTED`
- Admin can keep a review by rejecting a flag → review stays `APPROVED`
- Rejected reviews are soft-deletable but remain in the table

### 3. Rating Stats

`ProfileStatsService::recalculate()` uses `$profile->approvedReviews()` — only `status = 'approved'` reviews count toward `rating_avg` and `reviews_count`. Rejected reviews never appear in public stats.

### 4. UI Before Fix

The provider page only showed the review form if `auth()->id()` had no prior review (checking session/blade var). There was no distinction between states (approved existing, rejected, own profile). Silent form disappearance was the only feedback.

### 5. JS Error Handler

The AJAX review submission handler only surfaced `errors.rating` and `errors.comment` — `errors.profile` (the key used for duplicate/eligibility errors) was silently dropped.

---

## Product Decision: Option B (Rejected → Allow Retry)

**Rule:** A user can submit a new review if they do NOT already have an active (approved or pending) review for this provider. A rejected review does not block resubmission.

**Rationale:**
- Users may legitimately fix wording that violated guidelines
- Rejecting a review should not permanently silence fair feedback
- Public pages only show approved reviews — a rejected review is invisible to other users anyway
- Anti-spam protection remains via the 10-reviews/day limit (`EnsureReviewEligible` middleware)

---

## Code Changes

### Migration: Drop unique constraint, add composite index

**File:** `database/migrations/2026_06_18_171607_drop_reviews_unique_index_allow_resubmission.php`

- Drops `reviews_profile_id_user_id_unique`
- Adds `idx_reviews_profile_user_status` on `(profile_id, user_id, status)` for query performance

### ReviewCreationService

**File:** `app/Services/ReviewCreationService.php`

Old check (blocked forever):
```php
Review::withTrashed()
    ->where('profile_id', $profile->id)
    ->where('user_id', $user->id)
    ->lockForUpdate()
    ->exists()
```

New check (only blocks active reviews):
```php
Review::query()
    ->where('profile_id', $profile->id)
    ->where('user_id', $user->id)
    ->whereIn('status', [ReviewStatus::APPROVED->value, ReviewStatus::PENDING->value])
    ->lockForUpdate()
    ->exists()
```

Removed the `QueryException` catch block (was only there to handle the DB unique constraint violation — no longer needed).

### CreateReviewRequest

**File:** `app/Http/Requests/Review/CreateReviewRequest.php`

Same duplicate-check narrowing in `withValidator()`. Added `use App\Enums\ReviewStatus;` import.

### FrontendController

**File:** `app/Http/Controllers/Public/FrontendController.php`

Added `$userHasActiveReview` variable passed to the provider view — checks if the authenticated user has an APPROVED or PENDING review (not rejected) for the current profile.

### provider.blade.php

**File:** `resources/views/public/provider.blade.php`

Added `@elseif($userHasActiveReview ?? false)` branch showing:
> "لقد أضفت تقييماً لهذا المزود من قبل."

States now handled explicitly:
- Own profile → "لا يمكنك تقييم ملفك الشخصي"
- Already has active review → "لقد أضفت تقييماً لهذا المزود من قبل."
- Otherwise → show form

Fixed AJAX error handler to surface `errors.profile` errors before rating/comment errors.

---

## Arabic Messages

All messages were already in `lang/ar/messages.php`. Key values:

| Key | Value |
|-----|-------|
| `already_reviewed` | لقد قمت بتقييم هذا الملف الشخصي مسبقاً. |
| `profile_not_discoverable` | هذا الملف الشخصي غير ظاهر حالياً للعملاء. |
| `account_not_eligible_review` | حسابك غير مؤهل لكتابة تقييمات. |
| `public.review_daily_limit_reached` | لقد تجاوزت الحد اليومي المسموح به من التقييمات. |

---

## Tests Added

**File:** `tests/Feature/ReviewCreationTest.php` (12 tests)

| # | Test | Assertion |
|---|------|-----------|
| 1 | User can submit review for visible provider | 200 + DB record |
| 2 | Approved review blocks resubmission | Session error on `profile` + count = 1 |
| 3 | Rejected review allows new submission | 200 + approved count = 1 |
| 4 | Soft-deleted review allows new submission | 200 + DB record |
| 5 | Rejected review does not count in stats | reviews_count = 0, rating_avg = 0.0 |
| 6 | Admin accept flag hides review + recalculates | status = REJECTED, reviews_count = 0 |
| 7 | Admin reject flag keeps review + stats unchanged | status = APPROVED, reviews_count = 1 |
| 8 | Provider cannot review own profile | 403 |
| 9 | Provider role cannot review other profiles | 403 |
| 10 | Guest redirected to login | redirect to login |
| 11 | Daily limit blocks 11th review | 422 |
| 12 | Flagged pending review still counts in stats | reviews_count = 1, rating_avg = 5.0 |

---

## Remaining Risks

- **Rejected review visibility:** A user with a rejected review sees the form again and can resubmit. The rejection is not explained in the UI. This is intentional (rejected reviews are moderation-internal), but if product wants to notify the user of rejection, a notification flow would be needed.
- **Multiple rejected reviews:** A user could theoretically accumulate many rejected reviews in the DB (one per submission cycle). The daily limit (10/day) prevents burst spam. Longer term, consider a cooldown after rejection.
- **No pending status:** Reviews are immediately APPROVED. If a pending status were introduced, the duplicate check already covers it (`whereIn([APPROVED, PENDING])`).

---

## Verdict

The review system now correctly handles rejected reviews, duplicate rules, rating stats, and user-facing messages:

- ✅ Rejected review allows retry
- ✅ Approved review blocks duplicate
- ✅ Rejected reviews excluded from rating stats
- ✅ UI explains why form is hidden (active review exists)
- ✅ AJAX error handler surfaces `errors.profile` messages
- ✅ DB unique constraint dropped; app-level check enforced
- ✅ 120/120 tests passing
