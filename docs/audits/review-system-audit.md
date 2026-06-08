# Delni Review System Reverse-Engineering Audit

Date: 2026-06-05

Scope: review creation, validation, authorization, storage, moderation, profile statistics, search ranking, provider/admin surfaces, and review-related scheduled behavior. Source code and current schema are treated as the only authority.

## Review System Architecture

```text
Authenticated user
    |
    | POST /provider/{profile:slug}/review
    | routes/web.php -> ReviewController::store
    v
CreateReviewRequest::authorize
    |
    | ReviewPolicy::create(user, profile)
    | ProfileVisibilityService::isDiscoverable(profile)
    v
CreateReviewRequest::rules / withValidator
    |
    | rating 1..5
    | comment <= 2000
    | duplicate check with Review::withTrashed()
    v
Review::create
    |
    | status not set by controller
    | database default is currently approved
    v
reviews table
    |
    | ReviewObserver::created
    v
RecalculateProfileStatsJob(profile_id)
    |
    | ProfileStatsService::recalculate
    v
profile_stats.rating_avg
profile_stats.reviews_count
profile_stats.is_top_rated
    |
    v
PublicFrontendService / ProfileSearchService / MarketplaceRankingService
    |
    v
Public profile rating, homepage top-rated, search/category/subcategory ranking
```

## Source Inventory

Review core:

- `app/Models/Review.php` / `App\Models\Review`
- `app/Enums/ReviewStatus.php` / `App\Enums\ReviewStatus`
- `database/migrations/2026_06_02_184239_create_reviews_table.php`
- `database/migrations/2026_06_03_134733_fix_reviews_status_default.php`
- `database/migrations/2026_06_04_212345_change_reviews_default_to_approved.php`

Request and authorization:

- `routes/web.php` / `review.store`
- `app/Http/Controllers/Public/ReviewController.php` / `ReviewController::store`
- `app/Http/Requests/Review/CreateReviewRequest.php` / `authorize`, `rules`, `withValidator`
- `app/Http/Requests/Review/FlagReviewRequest.php` / `authorize`, `rules`
- `app/Http/Requests/Review/ModerateReviewRequest.php` / `authorize`, `rules`, `withValidator`
- `app/Policies/ReviewPolicy.php` / `before`, `viewAny`, `view`, `create`, `update`, `delete`, `flag`, `moderate`
- `app/Http/Middleware/EnsureReviewEligible.php` / `handle`

Stats/ranking/display:

- `app/Observers/ReviewObserver.php` / `created`, `updated`, `deleted`
- `app/Jobs/RecalculateProfileStatsJob.php` / `handle`, `uniqueId`
- `app/Services/ProfileStatsService.php` / `recalculate`
- `app/Services/ProfileVisibilityService.php` / `isDiscoverable`
- `app/Services/ProfileSearchService.php` / `search`
- `app/Services/MarketplaceRankingService.php` / `applyHomepageRanking`, `applySearchRanking`, `applyCategoryRanking`, `applySubcategoryRanking`
- `app/Services/PublicFrontendService.php` / `provider`, `homepage`, `discoverableProfilesQuery`
- `app/Console/Commands/UpdateTopRatedProfilesCommand.php` / `handle`
- `routes/console.php` scheduled `UpdateTopRatedProfilesCommand`

UI/admin:

- `resources/views/public/provider.blade.php`
- `app/Filament/Resources/ReviewResource.php`
- `app/Filament/Resources/ReviewResource/Pages/EditReview.php`
- `app/Filament/Provider/Resources/ProviderReviewResource.php`
- `app/Filament/Provider/Widgets/ReviewsSummaryWidget.php`

## Actual Business Rules

| Rule ID | Rule | Status | Source file | Class::method | Result |
|---|---|---|---|---|---|
| REVIEW-RULE-001 | Only authenticated users can call public review creation. | VERIFIED | `routes/web.php:16` | route middleware | Guest requests are blocked by `auth`. |
| REVIEW-RULE-002 | Only role `user` can create public reviews. | VERIFIED | `app/Policies/ReviewPolicy.php:73` | `ReviewPolicy::create` | Providers and non-admin roles are denied; super admins bypass via `before`. |
| REVIEW-RULE-003 | Public reviewer cannot review own profile. | VERIFIED | `app/Policies/ReviewPolicy.php:73` | `ReviewPolicy::create` | Denied when `profile.user_id === user.id`. |
| REVIEW-RULE-004 | Target profile must be discoverable to receive a review. | VERIFIED | `app/Policies/ReviewPolicy.php:73`; `app/Http/Requests/Review/CreateReviewRequest.php:53` | `ReviewPolicy::create`; `CreateReviewRequest::withValidator` | Hidden/incomplete/inactive/suspended/unsubscribed profiles are blocked. |
| REVIEW-RULE-005 | Discoverable profile requires owner, active user, unsuspended user, complete profile, active approved non-expired subscription. | VERIFIED | `app/Services/ProfileVisibilityService.php:11` | `ProfileVisibilityService::isDiscoverable` | Non-discoverable profiles cannot be reviewed through the public request. |
| REVIEW-RULE-006 | Rating must be integer 1 through 5. | VERIFIED | `app/Http/Requests/Review/CreateReviewRequest.php:45` | `CreateReviewRequest::rules` | Invalid rating fails validation. |
| REVIEW-RULE-007 | Comment is optional and limited to 2000 characters. | VERIFIED | `app/Http/Requests/Review/CreateReviewRequest.php:45` | `CreateReviewRequest::rules` | Overlong comment fails validation. |
| REVIEW-RULE-008 | One review per user/profile, including soft-deleted reviews. | VERIFIED | `app/Http/Requests/Review/CreateReviewRequest.php:72`; `database/migrations/2026_06_02_184239_create_reviews_table.php:30` | `CreateReviewRequest::withValidator`; unique index | Re-review is blocked by app validation and DB unique key. |
| REVIEW-RULE-009 | Public review creation currently publishes as approved. | VERIFIED | `app/Http/Controllers/Public/ReviewController.php:17`; `database/migrations/2026_06_04_212345_change_reviews_default_to_approved.php:12`; current DB schema | `ReviewController::store` | Controller omits `status`; DB default is `approved`. |
| REVIEW-RULE-010 | Review status values are `pending`, `approved`, `rejected`. | VERIFIED | `app/Enums/ReviewStatus.php`; current DB schema | `ReviewStatus` enum | No hidden/archived status exists. |
| REVIEW-RULE-011 | Reviewer cannot update own review through policy. | VERIFIED | `app/Policies/ReviewPolicy.php:92` | `ReviewPolicy::update` | Always false except super-admin `before`. |
| REVIEW-RULE-012 | Reviewer cannot delete own review through policy. | VERIFIED | `app/Policies/ReviewPolicy.php:102` | `ReviewPolicy::delete` | Always false except super-admin `before`. |
| REVIEW-RULE-013 | Admin can view/edit/delete/moderate reviews through policy bypass. | VERIFIED | `app/Policies/ReviewPolicy.php:22`; `app/Filament/Resources/ReviewResource.php`; `app/Filament/Resources/ReviewResource/Pages/EditReview.php:16` | `ReviewPolicy::before`; Filament resource | Super admin gets true for all review abilities. |
| REVIEW-RULE-014 | Moderation service can approve or reject and stamps moderator metadata. | VERIFIED | `app/Services/ReviewModerationService.php:11` | `approve`, `reject` | Updates `status`, `moderated_by`, `moderated_at`, `moderation_note`. |
| REVIEW-RULE-015 | Moderation request disallows setting status to pending. | VERIFIED BUT UNUSED | `app/Http/Requests/Review/ModerateReviewRequest.php:27` | `ModerateReviewRequest::rules` | No route uses this request; Filament edit form allows pending. |
| REVIEW-RULE-016 | Flag policy exists. | VERIFIED BUT UNUSED | `app/Policies/ReviewPolicy.php:119`; `app/Http/Requests/Review/FlagReviewRequest.php` | `ReviewPolicy::flag`; `FlagReviewRequest` | No route/controller uses it. |
| REVIEW-RULE-017 | Provider can only see approved reviews on own profile in provider panel. | VERIFIED | `app/Filament/Provider/Resources/ProviderReviewResource.php:80` | `ProviderReviewResource::getEloquentQuery` | Filters by own profile and `status = approved`. |
| REVIEW-RULE-018 | Public profile page shows only approved reviews. | VERIFIED | `app/Services/PublicFrontendService.php:181`; `resources/views/public/provider.blade.php:161` | `PublicFrontendService::provider` | Loads `approvedReviews.user`; view iterates supplied reviews. |
| REVIEW-RULE-019 | Review creation is not throttled by `reviews.create`. | MISSING | `routes/web.php:16`; `app/Providers/AppServiceProvider.php:123` | route vs rate limiter registration | Limiter exists but is not attached. |
| REVIEW-RULE-020 | Account age >= 24h is not enforced on active route. | MISSING | `app/Http/Middleware/EnsureReviewEligible.php:13`; `routes/web.php:16` | `EnsureReviewEligible::handle` | Middleware alias exists but route does not use `review.eligible`. |
| REVIEW-RULE-021 | Max 10 reviews/day is not enforced on active route. | MISSING | `app/Http/Middleware/EnsureReviewEligible.php:28`; `routes/web.php:16` | `EnsureReviewEligible::handle` | Middleware and rate limiter exist but neither is attached. |
| REVIEW-RULE-022 | Suspended reviewer is not blocked at review route middleware. | MISSING | `routes/web.php:16`; `routes/web.php:33` | route middleware comparison | `user.not_suspended` is used on dashboard group, not review creation. |
| REVIEW-RULE-023 | Inactive reviewer is not blocked at review route middleware. | MISSING | `routes/web.php:16`; `routes/web.php:33` | route middleware comparison | `user.active` is used on dashboard group, not review creation. |
| REVIEW-RULE-024 | Locked reviewer is not blocked at review route middleware. | MISSING | `routes/web.php:16`; `routes/web.php:33` | route middleware comparison | `account.locked` is used on dashboard group, not review creation. |
| REVIEW-RULE-025 | Must-change-password reviewer is not blocked at review route middleware. | MISSING | `routes/web.php:16`; `routes/web.php:33` | route middleware comparison | `password.changed` is used on dashboard group, not review creation. |
| REVIEW-RULE-026 | Flagged approved reviews still count in rating. | VERIFIED | `app/Services/ProfileStatsService.php:32`; `app/Models/Profile.php:86` | `ProfileStatsService::recalculate`; `Profile::approvedReviews` | `is_flagged` is not filtered out. |
| REVIEW-RULE-027 | Reviews from suspended/inactive/locked reviewers still count. | VERIFIED | `app/Services/ProfileStatsService.php:32`; `app/Models/Profile.php:86` | `ProfileStatsService::recalculate`; `Profile::approvedReviews` | No join/filter on reviewer account state. |

## Rating Formula

Primary runtime formula:

```text
eligible_reviews =
    reviews
    WHERE profile_id = target profile
    AND status = 'approved'
    AND deleted_at IS NULL   -- implicit Eloquent SoftDeletes scope

reviews_count = COUNT(eligible_reviews)
rating_avg = ROUND(AVG(eligible_reviews.rating), 1)
is_top_rated = rating_avg >= 4.5 AND reviews_count >= 5
```

Source:

- `app/Models/Profile.php:86` / `Profile::approvedReviews`: `hasMany(Review::class)->where('status', 'approved')`.
- `app/Services/ProfileStatsService.php:32` / `ProfileStatsService::recalculate`: `COUNT(*)`, `COALESCE(AVG(rating), 0)`.
- `app/Services/ProfileStatsService.php:37` / `ProfileStatsService::recalculate`: `round(..., 1)`.
- `app/Services/ProfileStatsService.php:40` / `ProfileStatsService::recalculate`: threshold `avg >= 4.5 && count >= 5`.

Count behavior:

| Review condition | Counted by `ProfileStatsService::recalculate`? | Evidence |
|---|---:|---|
| Approved | Yes | `Profile::approvedReviews`, `status = approved` |
| Pending | No | `Profile::approvedReviews`, `status = approved` |
| Rejected | No | `Profile::approvedReviews`, `status = approved` |
| Soft-deleted | No | `Review` uses `SoftDeletes`; relationship does not call `withTrashed` |
| Flagged approved | Yes | No `is_flagged = false` filter |
| Reviewer suspended | Yes | No user join/filter |
| Reviewer inactive | Yes | No user join/filter |
| Reviewer soft-deleted | Yes | `reviews.user_id` is retained; no reviewer join/filter |
| Profile owner suspended after review | Stats still stored; profile visibility hides provider elsewhere | Visibility services filter provider owner state, not stats recalculation |

Secondary scheduled formula:

```text
qualifying_profiles =
    SELECT profile_id
    FROM reviews
    WHERE status = 'approved'
    GROUP BY profile_id
    HAVING COUNT(*) >= 5
    HAVING AVG(rating) >= 4.5
```

Source: `app/Console/Commands/UpdateTopRatedProfilesCommand.php:21`.

Important difference: this command uses `DB::table('reviews')`, so it does not apply Eloquent soft-delete filtering. Soft-deleted approved reviews can still qualify a profile for `is_top_rated` in the scheduled command.

## Abuse Scenario Matrix

| Scenario | Allowed? | How blocked or allowed | Source |
|---|---:|---|---|
| Guest reviews provider | Blocked | `auth` middleware on route | `routes/web.php:16` |
| Public user reviews visible provider | Allowed | Policy permits role `user`; request creates row | `ReviewPolicy::create`; `ReviewController::store` |
| Provider reviews any provider | Blocked | Policy requires role `user` | `ReviewPolicy::create` |
| Super admin reviews through public route | Allowed by policy, but route uses public controller | `ReviewPolicy::before` returns true | `ReviewPolicy::before`; `CreateReviewRequest::authorize` |
| User reviews same provider twice | Blocked | `withTrashed()` duplicate validation and DB unique key | `CreateReviewRequest::withValidator`; reviews unique index |
| User reviews, admin soft-deletes, user reviews again | Blocked | Duplicate check includes trashed; DB unique key also includes trashed row | `CreateReviewRequest::withValidator`; reviews unique index |
| User reviews self | Blocked | `profile.user_id === user.id` denied | `ReviewPolicy::create` |
| User reviews own company held under another account | Allowed if target profile owner is a different user | No ownership/affiliation model exists | UNVERIFIED business identity; no code path found |
| Suspended reviewer creates review while still authenticated | Allowed by route/policy if role is `user` | Route lacks `user.not_suspended`; policy only checks role and target profile | `routes/web.php:16`; `ReviewPolicy::create` |
| Inactive reviewer creates review while still authenticated | Allowed by route/policy if role is `user` | Route lacks `user.active`; policy only checks role and target profile | `routes/web.php:16`; `ReviewPolicy::create` |
| Locked reviewer creates review while still authenticated | Allowed by route/policy if role is `user` | Route lacks `account.locked`; policy does not check `locked_until` | `routes/web.php:16`; `ReviewPolicy::create` |
| Must-change-password reviewer creates review | Allowed by route/policy if role is `user` | Route lacks `password.changed`; policy does not check it | `routes/web.php:16`; `ReviewPolicy::create` |
| Newly registered account reviews before 24h | Allowed | `EnsureReviewEligible` is not attached | `EnsureReviewEligible::handle`; `routes/web.php:16` |
| User submits more than 10 reviews/day across different profiles | Allowed until duplicate/target limits stop them | `reviews.create` limiter and `review.eligible` are not attached | `AppServiceProvider::configureRateLimiters`; `routes/web.php:16` |
| Review hidden provider | Blocked | Target must be discoverable | `ReviewPolicy::create`; `ProfileVisibilityService::isDiscoverable` |
| Review expired provider | Blocked | Discoverability requires active approved subscription ending today or later | `ProfileVisibilityService::isDiscoverable` |
| Review deleted provider | Blocked by route binding/default Eloquent behavior | `Profile` uses `SoftDeletes`; route model binding does not use trashed records | `Profile` model; route binding |
| Review through direct API request | No review API route exists | Only `/api/profiles/search` exists | `routes/api.php` |
| Review through direct web POST without form | Allowed if authenticated and passes request/policy | Route exists independent of visible form | `routes/web.php:16`; `ReviewController::store` |
| Two simultaneous duplicate review requests | One row succeeds; second should fail at DB unique key | App pre-check has race, DB unique key is final guard; controller does not catch `QueryException` | reviews unique index; `ReviewController::store` |
| Mass-assign status/is_flagged in public review POST | Blocked by controller shape | Controller passes explicit fields only | `ReviewController::store` |

## Rating and Marketplace Impact

Review creation:

- `ReviewController::store` creates the review.
- `ReviewObserver::created` dispatches `RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit()`.
- `RecalculateProfileStatsJob::handle` calls `ProfileStatsService::recalculate`.
- `ProfileStatsService::recalculate` updates `reviews_count`, `rating_avg`, `is_top_rated`.

Review update:

- `ReviewObserver::updated` recalculates only when `status` changes.
- Rating changes, comment changes, flag changes, reviewer changes, or profile changes do not trigger stats recalculation unless `status` also changes.
- Admin edit form exposes `status` and `moderation_note`, not rating/comment, so ordinary admin edits mostly match this observer. Direct model updates can stale stats.

Review delete:

- `ReviewObserver::deleted` dispatches stats recalculation.
- No `restored` observer exists, so restoring a soft-deleted review can leave stats stale until another recalculation path runs.

Review flagged:

- `ReviewObserver::updated` logs when `is_flagged` changes to true.
- Flagging does not change status.
- Flagging does not recalculate stats.
- Flagged approved reviews remain public and continue affecting ratings/search.
- No route/controller exists to flag a review in the active route table.

Search/ranking effects:

- `PublicFrontendService::homepage` top-rated providers use `profile_stats.is_top_rated`, `rating_avg`, and `reviews_count`.
- `ProfileSearchService::search` joins `profile_stats` and delegates ordering to `MarketplaceRankingService::applySearchRanking`.
- `MarketplaceRankingService` uses `rating_avg` and `reviews_count` as tie-breakers in homepage, search, category, and subcategory ranking.
- `MarketplaceRankingService` promotes `is_top_rated` as a ranking bucket below paid/placement buckets.

Provider visibility:

- Reviews do not directly change discoverability.
- Review-derived `is_top_rated`, `rating_avg`, and `reviews_count` affect ranking after visibility filters have already allowed the provider into result sets.

## Public, Provider, and Admin Behavior

Public profile page:

- Ratings visible: yes, from `$profile->stats?->rating_avg`.
- Counts visible: yes, from `$profile->stats?->reviews_count`.
- Reviews visible: yes, but only approved reviews supplied by `PublicFrontendService::provider`.
- Review form visible: no form found in `resources/views/public/provider.blade.php`; the POST route remains callable directly.

Provider panel:

- Provider can see approved reviews on own profile.
- Provider cannot reply; no reply model/field/action exists.
- Provider cannot moderate; provider resource has only `ViewAction`, no edit/delete/bulk actions.
- Provider review summary recalculates directly from approved reviews in memory, not from cached `profile_stats`.

Admin panel:

- Admin can list reviews.
- Admin can edit status and moderation note.
- Admin can bulk approve/reject through `ReviewModerationService`.
- Admin can soft-delete reviews via edit page delete action.
- Admin edit form allows `pending`, even though `ModerateReviewRequest` says pending should not be a moderation target.
- No restore action found for reviews.
- Flagged reviews can be filtered, but no admin action was found to clear a flag or convert flag to rejection beyond general edit/bulk status changes.

## Vulnerabilities and Logic Flaws

### ISSUE-REV-001: Public reviews publish immediately

Severity: Critical

Impact: Reputation manipulation, search manipulation, marketplace abuse

Evidence:

- `ReviewController::store` does not set `status`.
- Current schema default is `approved`.
- `CreateReviewRequest` comment says reviews default to pending, but `2026_06_04_212345_change_reviews_default_to_approved.php` changes the default back to approved.

Result: A user review becomes approved immediately, updates stats asynchronously, appears on public profiles, and can affect ranking/top-rated status without moderation.

### ISSUE-REV-002: Review route bypasses account-state middleware

Severity: High

Impact: Security issue, reputation manipulation

Evidence:

- Review route uses only `auth`.
- Dashboard group uses `account.locked`, `user.active`, `user.not_suspended`, `password.changed`.
- `ReviewPolicy::create` checks role and target profile only, not reviewer account state.

Result: An already-authenticated inactive, suspended, locked, or must-change-password public user can submit reviews if their session remains valid.

### ISSUE-REV-003: Anti-spam review controls are defined but not attached

Severity: High

Impact: Reputation manipulation, search manipulation

Evidence:

- `EnsureReviewEligible::handle` enforces account age and daily count.
- `bootstrap/app.php` aliases `review.eligible`.
- `AppServiceProvider` defines `reviews.create`.
- `routes/web.php` does not attach either to review creation.

Result: New accounts and high-volume accounts can submit reviews across many providers without the intended rate/account-age rules.

### ISSUE-REV-004: Top-rated scheduled command counts soft-deleted reviews

Severity: High

Impact: Rating manipulation, search manipulation, stale reputation

Evidence:

- `ProfileStatsService::recalculate` uses Eloquent `approvedReviews`, so soft-deleted reviews are excluded.
- `UpdateTopRatedProfilesCommand::handle` uses `DB::table('reviews')->where('status', 'approved')` with no `deleted_at IS NULL`.

Result: Daily scheduled top-rated recalculation can set `profile_stats.is_top_rated = true` from deleted reviews, contradicting live stats formula.

### ISSUE-REV-005: Flagging has no active endpoint and no rating effect

Severity: Medium

Impact: Missing moderation feature, reputation manipulation

Evidence:

- `FlagReviewRequest` and `ReviewPolicy::flag` exist.
- Route search found no flag route/controller.
- `ProfileStatsService::recalculate` does not exclude flagged reviews.
- `ReviewObserver::updated` logs flagging but does not recalculate or hide.

Result: Flagged reviews remain approved/public/counting. The flag mechanism appears partially implemented and not user-accessible through current routes.

### ISSUE-REV-006: Review restore does not recalculate stats

Severity: Medium

Impact: Data consistency issue

Evidence:

- `ReviewObserver` implements `created`, `updated`, and `deleted`.
- No `restored` observer method found.

Result: If a soft-deleted review is restored by Filament or code, `rating_avg`, `reviews_count`, and `is_top_rated` can stay stale until another recalculation occurs.

### ISSUE-REV-007: Rating changes do not recalculate stats

Severity: Medium

Impact: Rating inaccuracy, data consistency issue

Evidence:

- `ReviewObserver::updated` recalculates only when `status` changes.
- `Review` has `rating` fillable.

Result: Any direct model/admin/future endpoint update to `rating` can leave cached stats stale.

### ISSUE-REV-008: Super admin can create public reviews through policy bypass

Severity: Medium

Impact: Reputation manipulation

Evidence:

- `ReviewPolicy::before` returns true for super admin for every ability.
- `ReviewPolicy::create` itself says admins cannot create public reviews, but `before` bypasses it.

Result: The comment and implementation disagree. A super admin authenticated through the web guard can pass `CreateReviewRequest::authorize`.

### ISSUE-REV-009: Filament review edit allows pending despite moderation request disallowing pending

Severity: Medium

Impact: Missing moderation consistency

Evidence:

- `ModerateReviewRequest::rules` permits only `approved` and `rejected`.
- `ReviewResource::form` permits `pending`, `approved`, and `rejected`.

Result: Admin can move a review back to pending through Filament, contradicting the request-level rule.

### ISSUE-REV-010: Reviews from compromised/suspended reviewers remain counted

Severity: Medium

Impact: Reputation manipulation, trust issue

Evidence:

- `ProfileStatsService::recalculate` queries `approvedReviews` only.
- No filter on reviewer state.

Result: Suspending, deactivating, locking, or security-flagging a reviewer has no effect on their historical review contribution.

### ISSUE-REV-011: Public review POST endpoint exists without public form

Severity: Low

Impact: Hidden feature, inconsistent UX

Evidence:

- Route exists and works via direct POST.
- `resources/views/public/provider.blade.php` displays reviews but contains no review form.

Result: Review creation is discoverable only by route knowledge or custom requests, making abuse harder for normal users to notice and moderation harder to reason about.

### ISSUE-REV-012: No ReviewFactory exists

Severity: Low

Impact: Test debt

Evidence:

- `Review` uses `HasFactory`.
- `database/factories/ReviewFactory.php` was not found.
- Existing tests manually create reviews.

Result: Review tests are more verbose and less likely to cover state combinations.

## Data Consistency Risks

- Cached `profile_stats` can stale when queues are stopped because stats updates are queued through `RecalculateProfileStatsJob`.
- `RecalculateProfileStatsJob` is `ShouldBeUnique` by `profileId`; burst updates collapse, which is generally good, but if a unique job lock is held and a job fails repeatedly, stats can lag.
- Scheduled top-rated command recalculates only `is_top_rated`, not `rating_avg` or `reviews_count`.
- Scheduled top-rated command counts soft-deleted approved reviews.
- Provider widget computes average live from approved reviews, while public/search pages use cached `profile_stats`; these can disagree.

## Missing Rules / Missing Moderation Features

- No active public or provider flag route.
- No review reply model/field/action.
- No verified purchase/service-completed requirement.
- No reviewer account-age enforcement on active route.
- No review throttling on active route.
- No reviewer suspension/deactivation exclusion from rating formula.
- No explicit moderation queue in current public creation path.
- No automatic hiding of flagged reviews.
- No restore handling for stats recalculation.
- No admin restore workflow found for reviews.
- No audit rule that links multiple accounts to one company/customer identity.
- No CSRF issue found in Laravel web route context, but endpoint remains directly callable by authenticated sessions.

## Trustworthiness Assessment

Current review system is not trustworthy enough for a marketplace where ratings influence ranking or commercial reputation.

Primary reasons:

- Public review creation currently auto-approves.
- Intended anti-abuse middleware/rate limiters are not attached.
- Account-state restrictions are incomplete on the review route.
- Rating/top-rated calculations are inconsistent between service and scheduled command.
- Flagging is partial and does not affect display or scoring.

## Recommended Fixes

1. Make review creation explicitly set the intended initial status in `ReviewController::store`, rather than relying on DB default.
2. Decide whether public reviews should be pending or approved by default, then remove contradictory migrations/comments.
3. Attach account-state middleware and review-specific throttling/eligibility middleware to `review.store`.
4. Add route/controller support for flagging or remove dead flag request/policy code until implemented.
5. Update `UpdateTopRatedProfilesCommand` to exclude soft-deleted reviews and align exactly with `ProfileStatsService`.
6. Add observer handling for restored reviews and rating/profile_id/status-affecting updates.
7. Decide whether flagged reviews should remain public/counting; encode that decision in one shared review eligibility scope.
8. Add focused feature tests for direct POST review creation, suspended/inactive/locked reviewers, default status, duplicate race behavior, flagging, soft delete/restore, and top-rated recalculation.
9. Add `ReviewFactory` with states for pending, approved, rejected, flagged, and soft-deleted reviews.
