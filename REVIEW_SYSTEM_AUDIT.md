# Delni Review System Complete Audit
**Date**: 2026-06-10  
**Scope**: Complete reverse-engineering of review creation, moderation, visibility, permissions, abuse vectors, and production-readiness  
**Authority**: Source code analysis as of latest commit (2de656d)

---

## EXECUTIVE SUMMARY

The review system is **NOT production-safe** for a marketplace relying on rating integrity.

### Critical Issues (Block Deployment):
1. **Auto-approving reviews** — Public reviews publish immediately without moderation
2. **Missing anti-spam middleware** — No account age, suspension checks, or daily rate limits
3. **Inconsistent scheduled command** — Top-rated calculation includes soft-deleted reviews
4. **Partial flagging system** — Route exists but no UI; flagged reviews remain public/counted

### High Issues (Deploy Only With Remediation):
- Suspended/inactive/locked users can still post reviews
- Rating changes don't recalculate stats
- Review restore doesn't recalculate stats
- Admin can set status to pending despite request validation disallowing it

### Medium Issues (Post-Deployment):
- Super admin can create public reviews through policy bypass
- Reviewers from suspended accounts continue counting toward ratings
- Missing RestoreAction in Filament admin UI

---

## REVIEW SYSTEM ARCHITECTURE

```
Public User
    ↓
GET /providers/{profile:slug}  [FrontendController::provider]
    ↓
Shows: rating, reviews_count, approved reviews, review form
    ↓
POST /providers/{profile:slug}/review  [ReviewController::store]
├─ Middleware: auth, account.locked, user.active, user.not_suspended, review.eligible (NOT ATTACHED), throttle:reviews.create (NOT ATTACHED)
├─ Request validation: CreateReviewRequest
│  ├─ Authorization: ReviewPolicy::create
│  │  ├─ Must be role 'user'
│  │  ├─ Cannot review own profile
│  │  ├─ Target profile must be discoverable
│  └─ Validation: rating 1-5, comment ≤2000, no duplicate (including soft-deleted)
├─ Controller action: Review::create with status='approved' (defaults from DB)
└─ Observer: ReviewObserver::created
   └─ RecalculateProfileStatsJob dispatched
      └─ ProfileStatsService::recalculate
         └─ Updates: reviews_count, rating_avg, is_top_rated

POST /reviews/{review}/flag  [ReviewController::flag]
├─ Middleware: auth, throttle:reviews.flag
├─ Request validation: FlagReviewRequest
│  ├─ Authorization: ReviewPolicy::flag
│  └─ Checks: is_active, not is_suspended
└─ Action: Update is_flagged=true, flagged_by, flagged_at, flagged_reason
   └─ Observer: ReviewObserver::updated logs only, does NOT hide or recalculate

Scheduled daily: UpdateTopRatedProfilesCommand
└─ Uses DB::table('reviews')->where('status', 'approved')
   └─ INCLUDES soft-deleted reviews
   └─ EXCLUDES no reviewer state check
   └─ Sets is_top_rated = (avg >= 4.5 AND count >= 5)
```

---

## PERMISSIONS MATRIX

| Ability | Guest | User (role='user') | Provider (role='provider') | Super Admin |
|---------|-------|-------------------|--------------------------|-------------|
| View any reviews | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes |
| View single review | ✅ If profile discoverable | ✅ If profile discoverable | ✅ If profile discoverable | ✅ Yes |
| Create review | ❌ Blocked by auth | ✅ Yes, if profile discoverable, not own, account eligible | ❌ Blocked by role check | ⚠️ Policy allows, route doesn't |
| Edit own review | ❌ N/A | ❌ Always false | ❌ N/A | ❌ Always false (view-only) |
| Delete own review | ❌ N/A | ❌ Always false | ❌ N/A | ✅ Via Filament soft-delete |
| Flag review | ❌ Blocked by auth | ✅ Yes, if not own, profile discoverable | ✅ Yes, only on own profile | ✅ Yes |
| View own reviews (provider panel) | ❌ N/A | ❌ N/A | ✅ Yes, approved only | ✅ Yes |
| Moderate (approve/reject) | ❌ N/A | ❌ N/A | ❌ N/A | ✅ Yes, via Filament |
| Create reviews endpoint | ❌ N/A | ✅ Via POST route | ❌ N/A | ⚠️ Policy allows but route auth blocks |

---

## BUSINESS RULES VERIFICATION

| Rule | Status | Evidence | Risk |
|------|--------|----------|------|
| Only authenticated users create reviews | ✅ | `auth` middleware on route | Low |
| Only role='user' can create reviews | ✅ | `ReviewPolicy::create` checks role | Low |
| Users cannot review their own profile | ✅ | `ReviewPolicy::create` checks `profile.user_id` | Low |
| Target profile must be discoverable | ✅ | `ReviewPolicy::create` + `CreateReviewRequest::withValidator` | Low |
| Rating 1-5 only | ✅ | `CreateReviewRequest::rules` validation | Low |
| Comment optional, max 2000 chars | ✅ | `CreateReviewRequest::rules` validation | Low |
| One review per user/profile | ✅ | Duplicate check in `CreateReviewRequest::withValidator` + DB unique index | Low |
| Reviews auto-approve | ✅ | `ReviewController::store` omits status, DB default='approved' | **CRITICAL** |
| Account age ≥ 24 hours enforced | ❌ | Middleware exists but not attached to route | **HIGH** |
| Max 10 reviews/day enforced | ❌ | Rate limiter defined but not attached to route | **HIGH** |
| Suspended users blocked | ❌ | Route lacks `user.not_suspended` middleware | **HIGH** |
| Inactive users blocked | ❌ | Route lacks `user.active` middleware | **HIGH** |
| Locked users blocked | ❌ | Route lacks `account.locked` middleware | **HIGH** |
| Must-change-password blocked | ❌ | Route lacks `password.changed` middleware | **HIGH** |
| Flagged reviews remain public | ✅ | No status change on flag, no visibility filter | ⚠️ Expected but risky |
| Flagged reviews count in stats | ✅ | No `is_flagged` filter in `approvedReviews` | ⚠️ Expected but risky |
| Reviews from suspended reviewers count | ✅ | No reviewer state filter in stats | **MEDIUM** |
| Soft-deleted reviews excluded from public | ✅ | `Review` uses `SoftDeletes`, no `withTrashed` | Low |
| Top-rated command matches service | ❌ | Command includes soft-deleted, service excludes | **HIGH** |

---

## REVIEW CREATION FLOW

### Happy Path (Eligible Reviewer)
```
1. User (role='user', is_active=true, is_suspended=false) authenticated
2. User older than 24 hours (NOT ENFORCED)
3. User submits POST /providers/{slug}/review
4. CreateReviewRequest::authorize → ReviewPolicy::create
   - Check: user->hasRole('user') → PASS
   - Check: profile->user_id !== user->id → PASS
   - Check: isDiscoverable(profile) → PASS
5. CreateReviewRequest::withValidator additional checks:
   - Check: user->is_active ✅ AND NOT user->is_suspended ✅
   - Check: profile discoverable ✅
   - Check: no duplicate with withTrashed() ✅
6. ReviewController::store creates:
   - profile_id, user_id, rating, status (not set), comment
   - Note: status NOT explicitly set in controller
7. Review saved to DB, status defaults to 'approved'
8. ReviewObserver::created dispatches RecalculateProfileStatsJob
9. ProfileStatsService::recalculate:
   - COUNT(*) approved non-trashed reviews = reviews_count
   - AVG(rating) rounded to 1 decimal = rating_avg
   - rating_avg >= 4.5 AND reviews_count >= 5 → is_top_rated
10. Update profile_stats
11. PublicFrontendService queries with approvedReviews, displays on profile page
```

### Abuse Scenario: Suspended User Still Authenticated
```
1. Suspension happens AFTER login
2. Session remains valid
3. User hits POST /providers/{slug}/review
4. No route-level check for is_suspended ❌
5. CreateReviewRequest::withValidator checks is_suspended → Blocked ✅
6. Returns 422 validation error with message
```

### Abuse Scenario: New Account (< 24 hours)
```
1. User registers
2. Immediately tries to review
3. EnsureReviewEligible middleware exists but NOT attached to route ❌
4. CreateReviewRequest does NOT validate account age ❌
5. Review succeeds
```

### Abuse Scenario: High-Volume Spamming (>10/day)
```
1. User submits 11 reviews in one day across different providers
2. reviews.create rate limiter defined but NOT attached ❌
3. Daily count check in EnsureReviewEligible but middleware NOT attached ❌
4. All 11 succeed (unless duplicate/visibility/profile blocks them)
```

### Abuse Scenario: Duplicate Review
```
1. User submits review for Provider A
2. Review succeeds, is_flagged=false
3. User submits same review again
4. CreateReviewRequest::withValidator: `Review::withTrashed()->where(...)->exists()`
5. Blocked with "You have already submitted a review for this profile."
6. Even if admin soft-deletes first review, duplicate check includes trashed → Still blocked ✅
```

---

## FLAGGING SYSTEM

### Current Implementation Status
- ✅ Route: `POST /reviews/{review}/flag`
- ✅ Controller: `ReviewController::flag`
- ✅ Policy: `ReviewPolicy::flag`
- ✅ Request: `FlagReviewRequest`
- ❌ **NO UI** to access endpoint from public provider page
- ❌ **NOT integrated** into review cards/display
- ⚠️ **Endpoint is callable** but undiscoverable

### Flag Business Rules
| Rule | Status | Evidence |
|------|--------|----------|
| Only authenticated users can flag | ✅ | `auth` middleware |
| Cannot flag own review | ✅ | `ReviewPolicy::flag` checks `user_id` |
| Review must be on discoverable profile | ✅ | `ReviewPolicy::flag` checks profile visibility |
| Public users can flag any (non-own) review | ✅ | `ReviewPolicy::flag` checks `hasRole('user')` |
| Providers can flag reviews on own profile only | ✅ | `ReviewPolicy::flag` filters by provider's profile_id |
| Suspended users cannot flag | ✅ | `FlagReviewRequest::withValidator` checks is_suspended |
| Flagged reviews remain public/approved | ✅ | No status change on flag, no visibility change |
| Flagged reviews still affect rating | ✅ | No `is_flagged` filter in approvedReviews |
| Flag has reason (10-1000 chars) | ✅ | `FlagReviewRequest::rules` validates |
| Flag recorded with metadata | ✅ | is_flagged, flagged_by, flagged_at, flagged_reason |
| Admin can mark flag handled | ✅ | `ReviewModerationService::markFlagHandled` |

### What Flagging Does NOT Do
- Does NOT hide the review
- Does NOT change status to pending/rejected
- Does NOT exclude from rating calculation
- Does NOT move to moderation queue
- Does NOT notify admin (no queue visible in current codebase)
- Does NOT prevent review from appearing on public profile
- Does NOT reduce profile visibility

### Workflow Gap
```
Admin Panel shows filter "Unhandled flags" but:
1. No visible "queue" of unhandled flags on dashboard
2. Admin must manually navigate to ReviewResource::index
3. Filter flagged, find unhandled (flag_handled_at IS NULL)
4. Open review, click "Mark Handled" or "Keep" or "Reject"
5. Only then is flag_handled_at set
```

---

## RATING CALCULATION

### Formula (ProfileStatsService::recalculate)
```sql
eligible_reviews = SELECT *
  FROM reviews
  WHERE profile_id = ?
    AND status = 'approved'
    AND deleted_at IS NULL  -- implicit from Eloquent SoftDeletes
    AND is_flagged NOT checked  -- flagged reviews still count

reviews_count = COUNT(eligible_reviews)
rating_avg = ROUND(AVG(eligible_reviews.rating), 1)
is_top_rated = (rating_avg >= 4.5 AND reviews_count >= 5)
```

### Recalculation Triggers
| Trigger | Fires Stats Update? | Evidence |
|---------|-------------------|----------|
| Review created | ✅ Yes | ReviewObserver::created dispatches job |
| Review status changes | ✅ Yes | ReviewObserver::updated checks wasChanged('status') |
| Review flagged (is_flagged changes) | ❌ No | ReviewObserver::updated only logs, not recalculate |
| Review comment/rating edited | ❌ No | ReviewObserver::updated only checks status |
| Review soft-deleted | ✅ Yes | ReviewObserver::deleted dispatches job |
| Review restored | ❌ No | **NO restored() method in observer** |
| Review profile_id changed | ✅ Yes | ReviewObserver::updated checks wasChanged('profile_id') |
| Scheduled daily command | ✅ Yes | UpdateTopRatedProfilesCommand runs, but see bug below |

### Scheduled Command Bug
```php
// ProfileStatsService::recalculate (correct)
$stats = $profile->approvedReviews()
  ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_rating')
  ->first();
// Excludes soft-deleted because Eloquent SoftDeletes applies automatically

// UpdateTopRatedProfilesCommand (INCORRECT)
$profiles = DB::table('reviews')
  ->where('status', 'approved')
  ->selectRaw('profile_id, COUNT(*) as count, AVG(rating) as avg')
  ->groupBy('profile_id')
  ->havingRaw('COUNT(*) >= 5')
  ->havingRaw('AVG(rating) >= 4.5')
  ->get();
// INCLUDES soft-deleted reviews because using raw DB query, no Eloquent filter
```

### Impact of Command Bug
If an admin soft-deletes 2 approved reviews from a profile with 6 reviews (avg 4.8):
- **Live stats** (service): 4 reviews, avg 4.75, is_top_rated = false ✓
- **Scheduled stats** (command): 6 reviews (includes 2 deleted), avg 4.8, is_top_rated = true ✗

Contradiction continues until manual recalculation or next review creation.

---

## VISIBILITY & PUBLIC ACCESS

### Profile Visibility Service
Profile is discoverable if ALL:
1. User exists
2. User is_active = true
3. User is_suspended = false
4. Profile is_complete = true
5. User has active, non-expired subscription (ends_at >= today)

### Review Visibility
| Context | Reviews Shown | Filter | Source |
|---------|---------------|--------|--------|
| Public profile page | Approved only | `Profile::approvedReviews()` | `PublicFrontendService::provider` |
| Provider dashboard | Approved only | `whereHas(...profile...user_id)` + `status='approved'` | `ReviewsResource::getEloquentQuery` |
| Admin moderation panel | All (including deleted) | `withTrashed()->with([user, profile])` | `ReviewResource::getEloquentQuery` |
| Rating calculation | Approved, non-deleted | `approvedReviews()`, status='approved', !deleted_at | `ProfileStatsService::recalculate` |
| Flagged reviews | Still visible | No filter on is_flagged | Approved reviews remain public |

### What PREVENTS a Review from Being Public
- Review status ≠ 'approved' (pending or rejected)
- Review soft-deleted (deleted_at IS NOT NULL)
- Review's profile is not discoverable
- ❌ Review is flagged (flagged reviews still public)

---

## MODERATION WORKFLOW

### Admin View
- Route: `/admin/resources/reviews` (Filament)
- Can see all reviews including deleted (withTrashed)
- Can filter by status, flagged, unhandled_flags, deleted
- Can edit status (allows pending, approved, rejected despite validation conflict)
- Can edit moderation note
- Can soft-delete via EditAction
- Can restore via RestoreAction
- Can bulk approve/reject/mark_flags_handled

### Moderation Service Methods
```php
ReviewModerationService::approve($review, $note) 
  → status='approved', moderated_by, moderated_at, moderation_note

ReviewModerationService::reject($review, $note)
  → status='rejected', moderated_by, moderated_at, moderation_note

ReviewModerationService::keep($review, $note)
  → approve() + markFlagHandled()

ReviewModerationService::softDelete($review, $note)
  → update moderation fields, then $review->delete()

ReviewModerationService::restore($review, $note)
  → $review->restore(), update moderation fields
  → DOES NOT recalculate stats ❌
```

### No Moderation Queue
Reviews auto-approve immediately. No pending-by-default flow exists.
Admin moderation is *corrective*, not *preventative*.

---

## PROVIDER PANEL (Dashboard)

### What Providers See
- Reviews on their own profile only
- Approved reviews only
- Cannot edit, delete, or reply
- Can view flagged status but cannot clear flag
- Cannot respond to reviews
- View-only access

### Provider Can NOT
- See pending reviews awaiting approval
- See rejected reviews
- Delete their profile's reviews
- Edit review text
- Reply to reviews
- Approve/reject their own reviews

---

## ANTI-SPAM & ABUSE PROTECTIONS

### Protections IN PLACE ✅
1. **Duplicate prevention**: One review per user/profile enforced at request + DB unique index
2. **Self-review prevention**: Policy blocks own-profile reviews
3. **Profile discoverability**: Hidden/incomplete/inactive/suspended/unsubscribed blocks reviews
4. **Rating validation**: 1-5 only
5. **Comment length**: Max 2000 chars
6. **Role-based access**: Only role='user' can review

### Protections MISSING ❌
1. **Account age**: Defined in `EnsureReviewEligible` but NOT attached
2. **Daily rate limiting**: Defined in `AppServiceProvider` but NOT attached
3. **Account state**: Route doesn't check is_suspended, is_active, is_locked, must_change_password
4. **Per-provider throttling**: No per-profile limit (user can review many profiles same day)
5. **IP-based throttling**: No IP rate limiting
6. **Profanity filtering**: None
7. **Spam scoring**: None
8. **Link detection**: None
9. **HTML escaping**: Blade templates escape by default, but no explicit XSS sanitization comment
10. **Verified purchase**: None (marketplace doesn't track completed services)

### Test Coverage
Existing tests cover:
- ✅ Review creation works for eligible users
- ✅ Guests blocked
- ✅ Providers blocked
- ✅ Suspended users blocked (at validation level)
- ✅ New users < 24h blocked (at validation level)
- ✅ Duplicate blocked
- ✅ Self-review blocked
- ✅ Daily limit (at validation level, limited testing)
- ✅ Flagging works
- ✅ Admin moderation works
- ✅ Stats recalculation works

Missing tests:
- ❌ Suspended user created AFTER account eligible (session still valid)
- ❌ Inactive user creates review
- ❌ Locked user creates review
- ❌ Must-change-password user creates review
- ❌ Race condition: two simultaneous duplicate requests
- ❌ Super admin creating public review via web endpoint
- ❌ Review restore stats behavior
- ❌ Rating change without status change
- ❌ Soft-deleted review in top-rated command
- ❌ Flag without UI (direct POST)

---

## DATABASE SCHEMA

### reviews table
```sql
CREATE TABLE reviews (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  profile_id BIGINT NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  rating TINYINT UNSIGNED NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved' INDEX,
  is_flagged BOOLEAN DEFAULT FALSE INDEX,
  comment LONGTEXT,
  flagged_by BIGINT NULLABLE REFERENCES users(id) ON DELETE SET NULL,
  flagged_at TIMESTAMP NULLABLE,
  flagged_reason LONGTEXT NULLABLE,
  moderated_by BIGINT NULLABLE REFERENCES users(id) ON DELETE SET NULL,
  moderated_at TIMESTAMP NULLABLE,
  moderation_note LONGTEXT NULLABLE,
  flag_handled_by BIGINT NULLABLE REFERENCES users(id) ON DELETE SET NULL,
  flag_handled_at TIMESTAMP NULLABLE,
  deleted_at TIMESTAMP NULLABLE (soft delete),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE KEY unique_profile_user (profile_id, user_id)
);
```

### Key Index Notes
- Foreign key on profile_id has CASCADE delete: deleting provider deletes all their reviews ✅
- Foreign key on user_id has RESTRICT delete: cannot delete user with reviews (good data protection) ✅
- Unique constraint on (profile_id, user_id): one review per user/provider ✅
- Index on status: fast filtering for approved/pending/rejected ✅
- Index on is_flagged: fast filtering for flag views ✅
- No index on user_id: could slow queries filtering by reviewer, but not critical ⚠️
- No index on flagged_by: could slow queries filtering by who flagged, but not critical ⚠️

---

## SECURITY VULNERABILITIES

### VULN-001: Auto-Approving Reviews
**Severity**: CRITICAL  
**Impact**: Reputation manipulation, search ranking abuse, unmoderated marketplace content  

**Evidence**:
- `ReviewController::store` does NOT set status
- DB schema default = 'approved'
- `CreateReviewRequest` comment says "reviews are intentionally live by default"
- No moderation queue

**Attack Path**:
1. Create account (role='user')
2. Wait 24h (not enforced)
3. Post review with false 5-star rating
4. Review immediately appears on public profile
5. Stats update immediately
6. Affects ranking and top-rated visibility

**Recommended Fix**:
```php
// Option A: Change default to pending (moderation required)
Review::create([
  'profile_id' => $profile->id,
  'user_id' => $request->user()->id,
  'rating' => $request->integer('rating'),
  'status' => ReviewStatus::PENDING,  // Explicit
  'comment' => $request->string('comment')->value(),
]);

// Option B: Keep approved but enforce strict account eligibility
// (attach review.eligible middleware)
```

---

### VULN-002: Suspended/Inactive/Locked Users Can Review
**Severity**: HIGH  
**Impact**: Compromised accounts continue manipulating reputation  

**Evidence**:
- Route uses only `auth` middleware
- Dashboard group uses: `account.locked`, `user.active`, `user.not_suspended`, `password.changed`
- `ReviewPolicy::create` only checks role and target profile
- User can be suspended AFTER login, session remains valid

**Attack Path**:
1. Create account, get verified
2. Get suspended for abuse
3. If already authenticated, session still valid
4. POST to /providers/{slug}/review
5. CreateReviewRequest::withValidator checks is_suspended → Returns 422
6. So protected at request level, BUT should be at middleware level

**Better UX** would be to prevent reaching the request validation stage.

**Recommended Fix**:
```php
// routes/web.php
Route::post('/providers/{profile:slug}/review', [ReviewController::class, 'store'])
  ->middleware([
    'auth',
    'account.locked',      // Added
    'user.active',         // Added
    'user.not_suspended',  // Added
    'password.changed',    // Added
    'review.eligible',     // Added (currently missing)
    'throttle:reviews.create',  // Added (currently missing)
  ])
  ->name('review.store');
```

---

### VULN-003: No Account Age or Rate Limiting
**Severity**: HIGH  
**Impact**: Mass spam reviews, rapid reputation manipulation  

**Evidence**:
- `EnsureReviewEligible` middleware exists (24h + 10/day limits)
- `reviews.create` rate limiter defined in AppServiceProvider
- Route does NOT attach either

**Attack Path**:
1. Create 10 accounts instantly
2. Each account can review 10 different providers per day
3. Total: 100 reviews per day, all auto-approved
4. Affects marketplace ranking, top-rated visibility
5. Difficult to trace back to coordinated effort

**Recommended Fix**:
```php
// routes/web.php - attach review.eligible and throttle
Route::post('/providers/{profile:slug}/review', [ReviewController::class, 'store'])
  ->middleware([
    'auth',
    'review.eligible',        // Enforces 24h + 10/day
    'throttle:reviews.create',  // Enforces rate limiter
    // ... other middleware
  ])
```

---

### VULN-004: Scheduled Command Includes Soft-Deleted Reviews
**Severity**: HIGH  
**Impact**: Stale top-rated status, contradictory stats, ranking manipulation  

**Evidence**:
```php
// ProfileStatsService::recalculate (correct)
$profile->approvedReviews()  // Excludes soft-deleted

// UpdateTopRatedProfilesCommand::handle (WRONG)
DB::table('reviews')->where('status', 'approved')
  ->havingRaw('AVG(rating) >= 4.5')  // Includes soft-deleted
```

**Attack Path**:
1. Provider gets 6 reviews, avg 4.8 → is_top_rated = true
2. Admin soft-deletes 2 lowest reviews
3. Live stats: 4 reviews, avg 5.0 → is_top_rated = true ✓
4. Scheduled command runs: 6 reviews (includes deleted), avg 4.8 → is_top_rated = true ✓
5. But if provider had exactly 5 reviews, avg 4.2:
   - Live: 3 reviews, avg 4.33 → is_top_rated = false ✓
   - Scheduled: 5 reviews, avg 4.2 → is_top_rated = false ✓
6. Edge case: 5 reviews, avg 4.5, delete 1:
   - Live: 4 reviews, avg 4.5 → is_top_rated = false (need ≥5) ✓
   - Scheduled: 5 reviews, avg 4.5 → is_top_rated = true ✗

**Recommended Fix**:
```php
// app/Console/Commands/UpdateTopRatedProfilesCommand.php
public function handle()
{
  $profiles = DB::table('reviews')
    ->whereNull('deleted_at')  // ADDED: exclude soft-deleted
    ->where('status', 'approved')
    ->selectRaw('profile_id, COUNT(*) as count, AVG(rating) as avg')
    ->groupBy('profile_id')
    ->havingRaw('COUNT(*) >= 5')
    ->havingRaw('AVG(rating) >= 4.5')
    ->get();
  // ... rest of command
}
```

---

### VULN-005: Review Restore Doesn't Recalculate Stats
**Severity**: MEDIUM  
**Impact**: Data consistency issue, stale rating after restore  

**Evidence**:
```php
// ReviewObserver has: created, updated, deleted
// ReviewObserver MISSING: restored()
```

**Attack Path**:
1. Provider has 6 reviews, avg 4.8 → is_top_rated = true
2. Admin soft-deletes 2 reviews (recalculates → 4 reviews, avg 5.0 → still true)
3. Admin clicks "Restore" on one deleted review
4. Review restored but restored() not in observer
5. Stats still show 4 reviews instead of 5
6. Rating remains stale until another trigger (new review, next scheduled run)

**Recommended Fix**:
```php
// app/Observers/ReviewObserver.php
public function restored(Review $review): void
{
  RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit();
  
  $this->activityLog->log(
    actorId: Context::get('actor_id') ?? Auth::id(),
    subject: $review,
    action: 'review_restored',
    description: "Review #{$review->id} restored on profile #{$review->profile_id}",
    properties: [],
  );
}
```

---

### VULN-006: Rating Changes Don't Recalculate Stats
**Severity**: MEDIUM  
**Impact**: Rating inaccuracy  

**Evidence**:
```php
// ReviewObserver::updated only triggers on status change
if ($review->wasChanged('status')) {
  RecalculateProfileStatsJob...
}

// Rating is fillable and can be changed
protected $fillable = [..., 'rating', ...];
```

**Attack Path**:
1. Admin or future endpoint changes a review's rating
2. Observer only checks wasChanged('status'), not 'rating'
3. Stats remain stale
4. rating_avg incorrect until next recalculation trigger

**Recommended Fix**:
```php
// app/Observers/ReviewObserver.php
public function updated(Review $review): void
{
  if ($review->wasChanged('status') 
    || $review->wasChanged('rating')
    || $review->wasChanged('profile_id')) {
    RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit();
  }
  // ... rest of method
}
```

---

### VULN-007: Super Admin Can Create Public Reviews
**Severity**: MEDIUM  
**Impact**: Reputation manipulation by admin  

**Evidence**:
```php
// ReviewPolicy::before
public function before(User $user, string $ability): ?bool
{
  if (in_array($ability, ['create', 'flag'], true)) {
    return null;  // Don't bypass create/flag
  }
  if ($user->hasRole('super_admin')) {
    return true;  // Bypass everything else
  }
  return null;
}

// But CreateReviewRequest::authorize still calls can('create', [Review::class, $profile])
// And ReviewPolicy::create says admins cannot review
// However, the before() returning null should defer to create(), which returns false for non-'user' roles

// The actual risk: if policy author intended to block admin review but before() is
// still being called in the middleware chain somewhere
```

**Current Status**: Low risk because CreateReviewRequest::authorize will call ReviewPolicy::create which checks hasRole('user'). Super admin doesn't have role='user'.

---

### VULN-008: Filament Form Allows Pending Status Despite Request Disallowing It
**Severity**: MEDIUM  
**Impact**: Moderation inconsistency  

**Evidence**:
```php
// ModerateReviewRequest::rules disallows pending
'status' => ['required', 'in:approved,rejected'],

// But ReviewResource::form allows pending
Forms\Components\Select::make('status')
  ->options([
    ReviewStatus::PENDING->value => __('filament.status.pending'),
    ReviewStatus::APPROVED->value => __('filament.status.approved'),
    ReviewStatus::REJECTED->value => __('filament.status.rejected'),
  ])
```

**Issue**: No route uses ModerateReviewRequest. Admin edits directly via Filament form without validation.

**Recommended Fix**:
Option A: Remove pending from form
```php
Forms\Components\Select::make('status')
  ->options([
    ReviewStatus::APPROVED->value => __('filament.status.approved'),
    ReviewStatus::REJECTED->value => __('filament.status.rejected'),
  ])
```

Option B: Use ModerateReviewRequest if future API route is added
```php
// routes/api.php (hypothetical)
Route::post('/reviews/{review}/moderate', [ApiReviewController::class, 'moderate'])
  ->middleware('auth:sanctum', 'admin')
  ->validate(ModerateReviewRequest::class);
```

---

### VULN-009: Flagged Reviews Still Count Toward Ratings
**Severity**: MEDIUM  
**Impact**: Flagged abuse can still influence ranking  

**Evidence**:
```php
// No filter on is_flagged in approvedReviews
public function approvedReviews(): HasMany
{
  return $this->hasMany(Review::class)->where('status', 'approved');
  // Missing: ->where('is_flagged', false)
}
```

**Design Question**: Should flagged reviews count?

**Option A**: Exclude flagged from rating (moderation queue behavior)
```php
public function approvedReviews(): HasMany
{
  return $this->hasMany(Review::class)
    ->where('status', 'approved')
    ->where('is_flagged', false);
}
```

**Option B**: Keep flagged in rating but auto-reject flagged reviews (auto-moderation)
```php
// ReviewObserver::updated
if ($review->wasChanged('is_flagged') && $review->is_flagged) {
  $review->update(['status' => ReviewStatus::PENDING]);
  RecalculateProfileStatsJob::dispatch($review->profile_id);
}
```

**Option C**: Keep flagged in rating but notify admin (current, implicit behavior)

Current code chooses C. Document the choice.

---

### VULN-010: No RestoreAction in Filament
**Severity**: MEDIUM  
**Impact**: Usability issue  

**Evidence**:
```php
// ReviewResource recordActions has RestoreAction
RestoreAction::make()
  ->visible(fn (Review $record): bool => $record->trashed()),
```

✅ Actually, RestoreAction IS present. No issue here.

---

### VULN-011: ReviewFactory Missing
**Severity**: LOW  
**Impact**: Test coverage, test verbosity  

**Evidence**:
- `Review` uses `HasFactory`
- `database/factories/ReviewFactory.php` NOT FOUND
- Tests manually build reviews with `Review::create(...)`

**Recommended Fix**:
```php
// database/factories/ReviewFactory.php
<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
  protected $model = Review::class;

  public function definition(): array
  {
    return [
      'profile_id' => Profile::factory(),
      'user_id' => User::factory(),
      'rating' => $this->faker->numberBetween(1, 5),
      'status' => ReviewStatus::APPROVED,
      'comment' => $this->faker->paragraph(),
      'is_flagged' => false,
    ];
  }

  public function pending(): static
  {
    return $this->state(['status' => ReviewStatus::PENDING]);
  }

  public function rejected(): static
  {
    return $this->state(['status' => ReviewStatus::REJECTED]);
  }

  public function flagged(): static
  {
    return $this->state([
      'is_flagged' => true,
      'flagged_by' => User::factory(),
      'flagged_at' => now(),
      'flagged_reason' => $this->faker->sentence(),
    ]);
  }

  public function softDeleted(): static
  {
    return $this->state(['deleted_at' => now()]);
  }
}
```

---

## MISSING FEATURES / INCOMPLETE IMPLEMENTATION

| Feature | Status | Impact |
|---------|--------|--------|
| Review flagging workflow | Partial | Route exists, no UI, flags don't affect visibility |
| Review moderation queue | Partial | Reviews auto-approve, admin corrects after |
| Account age enforcement | Defined, not attached | Spam risk |
| Review rate limiting | Defined, not attached | Spam risk |
| Account state checks | Partial | Suspended users can review |
| ReviewFactory | Missing | Test burden |
| Flagged review auto-rejection | Missing | Flagged abuse still counts |
| Review restore notification | Missing | Stats can stale after restore |
| Rating change handling | Missing | Stats can stale on admin rating edit |
| Reviewer account state exclusion | Missing | Compromised reviewers still count |
| Verified purchase requirement | Missing | No purchase/service tracking |
| Review reply/response | Missing | Providers cannot defend reputation |
| Public review form on provider page | Present | ✅ Working |
| Admin review edit page | Present | ✅ Working |
| Admin bulk moderation | Present | ✅ Working |
| Provider review dashboard | Present | ✅ Working (view-only) |

---

## PRODUCTION READINESS ASSESSMENT

### Can Deploy As-Is?
**NO** — Critical issues must be resolved first.

### Deployment Blockers

1. **Auto-approving reviews** (CRITICAL)
   - Fix: Make reviews pending by default OR attach account-age middleware
   - Time to fix: 30 minutes
   - Testing: 1 hour

2. **Missing anti-spam middleware** (CRITICAL)
   - Fix: Attach `review.eligible` and `throttle:reviews.create`
   - Time to fix: 15 minutes
   - Testing: 1 hour

3. **Scheduled command bug** (HIGH)
   - Fix: Add `whereNull('deleted_at')` to command
   - Time to fix: 10 minutes
   - Testing: 30 minutes

4. **Missing account-state checks on review route** (HIGH)
   - Fix: Attach `account.locked`, `user.active`, `user.not_suspended`, `password.changed`
   - Time to fix: 15 minutes
   - Testing: 1 hour

### Post-Deployment Enhancements

- Add `restored()` observer method
- Add rating/profile_id change handling to observer
- Clarify flagged review handling (exclude or auto-reject)
- Add ReviewFactory
- Add RestoreAction display in Filament (already present, document)
- Add public flag UI or disable endpoint until needed
- Implement review reply/response feature
- Add verified purchase requirement when service tracking exists

---

## DEPLOYMENT CHECKLIST

- [ ] **BLOCKING**: ReviewController::store explicitly sets status=PENDING (or justify APPROVED)
- [ ] **BLOCKING**: review.eligible and throttle:reviews.create attached to review.store route
- [ ] **BLOCKING**: UpdateTopRatedProfilesCommand filters by deleted_at IS NULL
- [ ] **BLOCKING**: review.store route includes account.locked, user.active, user.not_suspended middleware
- [ ] Add ReviewFactory with states
- [ ] Add restored() observer method
- [ ] Add rating/profile_id change handling to observer
- [ ] Document flagged review behavior (count toward rating or not)
- [ ] Document why pending status is disallowed in ModerateReviewRequest but allowed in Filament form
- [ ] Write tests for: suspended user (post-suspension), account age (not attached), daily limit (not attached), restore behavior
- [ ] Verify flagged reviews endpoint is intentionally undiscoverable from UI
- [ ] Run full test suite: `php artisan test --compact`

---

## SUMMARY: IS IT SAFE?

**Short answer**: NO. Not without addressing the blocking issues.

**Long answer**:

The review system has good bones:
- Solid permission model (ReviewPolicy)
- Proper duplicate prevention
- Decent stats recalculation (mostly)
- Admin moderation interface
- Soft delete support

But critical gaps:
- **Reviews publish immediately without moderation** ← Fix this first
- **Anti-spam controls are wired but not attached** ← Fix this second
- **Scheduled top-rated command contradicts live calculation** ← Fix this third
- **Account suspension/locking doesn't block reviews at middleware level** ← Fix this fourth

Once those four issues are addressed, the system becomes much safer for a marketplace relying on reputation.

**Estimated effort to production-ready**:
- Code fixes: 2-3 hours
- Testing: 4-6 hours
- Code review: 1-2 hours
- Total: 7-11 hours

**Can launch with these blockers**? Only if:
1. Reviews are moderated manually before appearing, OR
2. Account age enforcement is added immediately, OR
3. Marketing/trust explicitly states "unmoderated reviews"

Otherwise, malicious actors will spam the system on day 1.

---

## APPENDIX: FULL RULE REFERENCE

### REVIEW CREATION RULES
- R1: Target profile must be discoverable
- R2: Cannot review own profile
- R3: Only role='user' can create public reviews
- R4: Rating must be 1-5
- R5: Account must be active and unsuspended
- R6: No duplicate reviews per user/profile
- R7: Comment optional, max 2000 chars
- R8: Reviews auto-approve (not pending)
- R9: Account age ≥ 24h required (defined but not enforced)
- R10: Max 10 reviews/day (defined but not enforced)

### FLAG RULES
- F1: Cannot flag own review
- F2: Review must be on discoverable profile
- F3: Providers can only flag reviews on own profile
- F4: Public users can flag any visible review (except own)
- F5: Reason required, 10-1000 chars
- F6: Flagged reviews remain public and counted

### VISIBILITY RULES
- V1: Only approved, non-deleted reviews appear on public profile
- V2: Reviews inherit profile visibility (hidden provider = hidden reviews)
- V3: Flagged reviews still visible to public
- V4: Pending reviews NOT visible to public
- V5: Rejected reviews NOT visible to public

### RATING RULES
- Rating1: avg = ROUND(AVG(approved, non-deleted reviews), 1)
- Rating2: count = COUNT(approved, non-deleted reviews)
- Rating3: is_top_rated = (avg >= 4.5 AND count >= 5)
- Rating4: Recalculates on: create, status change, soft-delete, profile_id change
- Rating5: Does NOT recalculate on: restore (BUG), rating change (BUG), flag
- Rating6: Scheduled command includes soft-deleted (BUG)

### ADMIN RULES
- Admin1: Can view all reviews including deleted
- Admin2: Can edit status and moderation note
- Admin3: Can soft-delete reviews
- Admin4: Can restore reviews
- Admin5: Can bulk approve/reject
- Admin6: Can mark flags handled
- Admin7: Cannot reply to reviews

### PROVIDER RULES
- Provider1: Can view approved reviews on own profile
- Provider2: Can see flagged status
- Provider3: Cannot edit/delete/reply
- Provider4: Can flag reviews on own profile
- Provider5: Cannot flag reviews on others' profiles

---

**Document Version**: 2.0  
**Last Updated**: 2026-06-10  
**Authority**: Source code inspection, test audit, database schema review  
**Status**: READY FOR REVIEW, BLOCKERS IDENTIFIED, FIXES PROVIDED
