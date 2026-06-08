# Delni Reverse-Engineering Audit

Source of truth: implementation only. Existing docs, comments, and audit notes were not treated as authoritative. Status labels mean:

- VERIFIED: enforced by executable code, schema, route, policy, observer, command, or resource query.
- UNVERIFIED: present as code but not wired/reachable, or described by code comments without an active enforcement path.

## Domain Map

- Identity and access: `User`, Spatie roles, auth controllers, account state middleware, Filament panel access.
- Provider marketplace: `Profile`, `ProfileStats`, `ProviderType`, provider links, credentials, portfolio items/images.
- Taxonomy and geography: `Category`, `Subcategory`, `City`.
- Billing: `SubscriptionPlan`, `Subscription`, subscription approval and expiry.
- Community: `Review`, moderation, flagging, review-derived stats.
- Audit/security: `ActivityLog`, failed-login lockout, suspension, scheduled lock clearing.
- Public frontend/search: public browse routes, API search route, marketplace ranking service.

## Entity Map

- `User` (`app/Models/User.php`): authenticatable account, roles `super_admin`, `provider`, `user`; has one `Profile`, many `Subscription`, many `Review`, many `ActivityLog`.
- `Profile` (`app/Models/Profile.php`): provider-facing marketplace record; belongs to user/city/category; many subcategories, portfolio items, links, credentials, reviews; one stats record.
- `ProfileStats` (`app/Models/ProfileStats.php`): primary key `profile_id`; rating aggregates and placement flags.
- `Subscription` (`app/Models/Subscription.php`): belongs to user and plan; helper states `isApproved`, `isActive`, `isExpired`.
- `Review` (`app/Models/Review.php`): belongs to profile/user; status enum `pending|approved|rejected`; soft deletable.
- `Category`, `Subcategory`, `City`: active/soft-deletable browse dimensions.
- `PortfolioItem`, `PortfolioImage`, `ProviderLink`, `ProviderCredential`: provider-owned public assets.
- `ActivityLog`: polymorphic immutable audit log.

## Service Map

- `ProfileVisibilityService::isDiscoverable`: central public visibility rule.
- `ProfileSearchService::search`: discoverable-profile query and filters.
- `MarketplaceRankingService`: ranking buckets for home/search/category/subcategory.
- `PublicFrontendService`: public page data, discoverable query, active taxonomy.
- `ProfileCompletenessService`: computes `profiles.is_complete`.
- `ProfileStatsService`: initializes stats and recalculates approved-review aggregates.
- `ReviewModerationService`: approve/reject review.
- `SubscriptionValidationService`: provider ownership, dates, overlap prevention.
- `SubscriptionApprovalService`: approval metadata and activation.
- `AccountSecurityService`: failed-login counters, lockout, security flag.
- `UserSuspensionService`: suspend/reinstate state changes.
- `ProfileImageService`: image validation, resizing, WebP storage/deletion.
- `ActivityLogService`: append audit entries.

## User Journey Map

- Guest browsing: `routes/web.php` -> `FrontendController` -> `PublicFrontendService`; only active taxonomy and discoverable profiles are returned.
- Public registration: `RegisterController::register` creates active `user`, assigns role `user`; `UserObserver::created` queues profile creation for non-admin users.
- Login: `AuthController::login` attempts credentials, updates lockout counters, rejects locked/inactive/suspended users, regenerates session.
- Provider panel: `ProviderPanelProvider` requires auth, not locked, active, not suspended, password changed, and provider role.
- Admin panel: `AdminPanelProvider` requires auth, not locked, active, not suspended, password changed, and super_admin role.
- Provider profile update: `ProviderProfileResource` edits only current user's profile; active city/category/subcategory/provider-type options only.
- Review creation: `ReviewController::store` uses `CreateReviewRequest`; review observer queues stats recalculation and logs creation.
- Subscription creation/approval: admin Filament resource/page creates pending subscription; observer validates; approval service/page activates and stamps metadata.
- Public search/API search: `ProfileSearchService` filters discoverable profiles and applies ranking.
- Account suspension: admin action/service sets suspension; middleware and auth events eject suspended users; visibility/search hides their profile.

## Request Lifecycle Map

- Web middleware: `SetLocale` appended globally in `bootstrap/app.php`.
- Authenticated route group: `auth`, `account.locked`, `user.active`, `user.not_suspended`, `password.changed`.
- Filament admin auth middleware: `Authenticate`, `CheckSuspensionAfterAuth`, lock/active/suspended/password/admin checks.
- Filament provider auth middleware: same, but `provider` role check.
- API `/api/profiles/search`: `SearchProfilesRequest` plus `throttle:search`; exceptions render JSON for `api/*`.

## Business Rules Catalog

### RULE-001 Provider profile discoverability
- Status: VERIFIED
- Description: A profile is public only when owner exists, owner active, owner not suspended, profile complete, and owner has active approved non-expired subscription.
- Source: `ProfileVisibilityService::isDiscoverable`, `ProfileSearchService::search`, `PublicFrontendService::discoverableProfilesQuery`.
- Trigger: public profile/search/home/category/city/subcategory visibility.
- Result: hidden or 404 when not discoverable.
- Severity: Critical

### RULE-002 Public provider page 404s hidden profiles
- Status: VERIFIED
- Source: `PublicFrontendService::provider`.
- Trigger: GET `/provider/{profile:slug}`.
- Result: `abort_unless(..., 404)`.
- Severity: Critical

### RULE-003 Only visible profiles appear in public search
- Status: VERIFIED
- Source: `ProfileSearchService::search`.
- Trigger: web/API search.
- Result: query joins users/stats and requires active, not suspended, complete, active approved subscription.
- Severity: Critical

### RULE-004 Public taxonomy must be active
- Status: VERIFIED
- Source: `FrontendController::category`, `subcategory`, `city`; `PublicFrontendService::activeCategories`, `activeSubcategories`, `activeCities`.
- Trigger: browsing/search filters.
- Result: inactive category/subcategory/city 404s or is excluded.
- Severity: High

### RULE-005 Subcategory must match category on profile update
- Status: VERIFIED
- Source: `UpdateProfileRequest::subcategoryBelongsToCategory`.
- Trigger: provider profile update.
- Result: validation error when subcategory is inactive or outside category.
- Severity: High

### RULE-006 Search subcategory/category consistency
- Status: VERIFIED
- Source: `SearchProfilesRequest::subcategoryBelongsToCategory`.
- Trigger: public/API search.
- Result: invalid subcategory rejected; subcategory without category allowed if active.
- Severity: Medium

### RULE-007 Provider type must be active
- Status: VERIFIED
- Source: `UpdateProfileRequest::rules`, `SearchProfilesRequest::rules`, `ProviderType::options`.
- Trigger: profile update/search filters.
- Result: inactive provider types rejected/excluded.
- Severity: Medium

### RULE-008 Profile completeness formula
- Status: VERIFIED
- Source: `ProfileCompletenessService::meetsAllConditions`, `ProfileObserver::updated`, `UserObserver::updated`.
- Trigger: profile fields or user name change.
- Result: `is_complete` requires business name or user name, bio, city, category, whatsapp, phone.
- Severity: Critical

### RULE-009 Profiles are system-created, not policy-created
- Status: VERIFIED
- Source: `ProfilePolicy::create`, `ProfilePolicy::before`, `CreateUserProfileJob::handle`.
- Trigger: policy authorization/profile creation.
- Result: policy create denied even for super_admin; profiles are auto-created for non-admin users by queued job.
- Severity: High

### RULE-010 Profiles are not directly deleted
- Status: VERIFIED
- Source: `ProfilePolicy::delete`, `SoftDeleteUserProfileJob::handle`.
- Trigger: policy delete/user delete.
- Result: direct profile delete denied even for super_admin policy bypass; user deletion queues profile soft delete.
- Severity: High

### RULE-011 Public registration creates only `user` role
- Status: VERIFIED
- Source: `RegisterController::register`.
- Trigger: POST `/register`.
- Result: active account assigned role `user`.
- Severity: High

### RULE-012 Admin/provider panel access is role-gated
- Status: VERIFIED
- Source: `User::canAccessPanel`, `AdminPanelProvider::panel`, `ProviderPanelProvider::panel`, `EnsureAdminRole`, `EnsureProviderRole`.
- Trigger: Filament panel access.
- Result: admin panel only `super_admin`; provider panel only `provider`.
- Severity: Critical

### RULE-013 Suspended users cannot continue authenticated sessions
- Status: VERIFIED
- Source: `AuthController::login`, `EnsureUserNotSuspended`, `CheckSuspensionAfterAuth`, `AppServiceProvider::boot` auth event listeners.
- Trigger: login or authenticated request.
- Result: logout/session invalidation/redirect.
- Severity: Critical

### RULE-014 Inactive users cannot continue authenticated sessions
- Status: VERIFIED
- Source: `AuthController::login`, `EnsureUserIsActive`.
- Trigger: login or authenticated request.
- Result: logout/session invalidation/redirect.
- Severity: Critical

### RULE-015 Must-change-password blocks normal authenticated routes
- Status: VERIFIED
- Source: `EnsurePasswordChanged::handle`, `PasswordController::change`.
- Trigger: authenticated route/panel access.
- Result: redirect to `/password/change`; logout and change routes are exempt.
- Severity: High

### RULE-016 Password changes require strong uncompromised password
- Status: VERIFIED
- Source: `ChangePasswordRequest::rules`, `RegisterUserRequest::rules`, `ResetPasswordRequest::rules`.
- Trigger: password create/change/reset requests.
- Result: min 8, letters, numbers, mixed case, symbols; uncompromised for public/register/change/reset.
- Severity: High

### RULE-017 Failed logins lock account progressively
- Status: VERIFIED
- Source: `AccountSecurityService::recordFailedAttempt`, `lockoutUpdates`, `EnsureAccountNotLocked`, `ClearExpiredLocksCommand`.
- Trigger: failed login, authenticated request, scheduled command.
- Result: 5 failures = 15 min lock; 10 = 1 hour; 20 = 24 hours + security flag; 50 = 72 hours + security flag.
- Severity: Critical

### RULE-018 Successful login clears lock counters
- Status: VERIFIED
- Source: `AccountSecurityService::recordSuccessfulLogin`, `AuthController::login`.
- Trigger: valid login and not locked/inactive/suspended.
- Result: failed attempts, last failure, lock cleared.
- Severity: Medium

### RULE-019 Users cannot self-assign protected account fields
- Status: VERIFIED
- Source: `UpdateOwnAccountRequest::prepareForValidation`, `CreateProviderRequest::prepareForValidation`, `CreateAdminRequest::prepareForValidation`.
- Trigger: account create/update requests.
- Result: role/status/security/password fields stripped as applicable.
- Severity: High

### RULE-020 User suspension cannot target self via request
- Status: VERIFIED
- Source: `SuspendUserRequest::withValidator`.
- Trigger: suspension request with route user.
- Result: validation error if current user is target.
- Severity: High

### RULE-021 Reinstate requires existing suspension
- Status: VERIFIED
- Source: `ReinstateUserRequest::withValidator`.
- Trigger: reinstate request.
- Result: validation error if user is not suspended.
- Severity: Medium

### RULE-022 Subscription owner must be provider
- Status: VERIFIED
- Source: `CreateSubscriptionRequest::userMustBeProvider`, `SubscriptionValidationService::validateOwnership`, `SubscriptionObserver::creating`.
- Trigger: subscription creation.
- Result: validation exception for non-provider owner.
- Severity: Critical

### RULE-023 Subscription dates must be ordered
- Status: VERIFIED
- Source: `CreateSubscriptionRequest::rules`, `SubscriptionValidationService::validateDates`.
- Trigger: subscription creation.
- Result: end date must be after start date.
- Severity: Critical

### RULE-024 Provider subscriptions cannot overlap
- Status: VERIFIED
- Source: `SubscriptionValidationService::validateDates`, `SubscriptionObserver::creating`.
- Trigger: subscription creation.
- Result: locked transaction checks overlap and rejects.
- Severity: Critical

### RULE-025 Subscription financial fields are immutable
- Status: VERIFIED
- Source: `SubscriptionObserver::updating`, `UpdateSubscriptionRequest::prepareForValidation`.
- Trigger: subscription update.
- Result: changing user, plan, start, or end throws validation exception.
- Severity: Critical

### RULE-026 Subscriptions are permanent records
- Status: VERIFIED
- Source: `SubscriptionPolicy::delete`, `SubscriptionPolicy::before`.
- Trigger: policy delete.
- Result: delete denied even for super_admin.
- Severity: Critical

### RULE-027 Subscription approval activates and stamps metadata
- Status: VERIFIED
- Source: `SubscriptionApprovalService::approve`, `SubscriptionResource\Pages\CreateSubscription::mutateFormDataBeforeCreate`, `ProviderResource::saveProviderSubscription`.
- Trigger: approval action / active subscription save.
- Result: `is_active`, `approved_by`, `approved_at`, `processed_by`, `processed_at` set.
- Severity: High

### RULE-028 Expired active subscriptions are deactivated automatically
- Status: VERIFIED
- Source: `ExpireSubscriptionsCommand::handle`, `routes/console.php`.
- Trigger: daily schedule.
- Result: active subscriptions with `ends_at < now()` set inactive.
- Severity: Critical

### RULE-029 Reviews can be created only by public users
- Status: VERIFIED
- Source: `ReviewPolicy::create`, `CreateReviewRequest::authorize`.
- Trigger: review submission.
- Result: only role `user`; providers/admins denied except admin bypass does not apply to create call with public route unless admin role reaches policy before.
- Severity: High

### RULE-030 Users cannot review own profile
- Status: VERIFIED
- Source: `ReviewPolicy::create`.
- Trigger: review submission.
- Result: denied when `profile.user_id === user.id`.
- Severity: High

### RULE-031 Reviews can target only discoverable profiles
- Status: VERIFIED
- Source: `ReviewPolicy::create`, `CreateReviewRequest::withValidator`.
- Trigger: review submission.
- Result: authorization/validation failure.
- Severity: Critical

### RULE-032 One review per user per profile, including soft-deleted reviews
- Status: VERIFIED
- Source: migration `2026_06_02_184239_create_reviews_table.php` unique index; `CreateReviewRequest::withValidator` uses `Review::withTrashed`.
- Trigger: review submission.
- Result: duplicate blocked.
- Severity: Critical

### RULE-033 Review rating range
- Status: VERIFIED
- Source: `CreateReviewRequest::rules`.
- Trigger: review submission.
- Result: integer 1..5 required.
- Severity: Medium

### RULE-034 Reviewer cannot update/delete review after submission
- Status: VERIFIED
- Source: `ReviewPolicy::update`, `ReviewPolicy::delete`.
- Trigger: policy update/delete.
- Result: denied; super_admin bypass permits admin moderation/deletion.
- Severity: High

### RULE-035 Review moderation targets approved/rejected only
- Status: VERIFIED
- Source: `ModerateReviewRequest::rules`, `ReviewModerationService`.
- Trigger: moderation request/service.
- Result: status can be approved or rejected; request blocks pending as target.
- Severity: High

### RULE-036 Re-moderating to the same status is blocked in request layer
- Status: VERIFIED
- Source: `ModerateReviewRequest::withValidator`.
- Trigger: moderation request.
- Result: validation error if target status equals current status.
- Severity: Low

### RULE-037 Review flagging rules
- Status: VERIFIED
- Source: `ReviewPolicy::flag`, `FlagReviewRequest`.
- Trigger: flag action.
- Result: cannot flag own review; profile must be discoverable; provider only own profile reviews; public user can flag visible non-own reviews; admin bypass.
- Severity: High

### RULE-038 Approved reviews drive stats only
- Status: VERIFIED
- Source: `ProfileStatsService::recalculate`, `ReviewObserver`, `UpdateTopRatedProfilesCommand`.
- Trigger: review created/status change/delete and daily command.
- Result: average/count use `approved` reviews only.
- Severity: High

### RULE-039 Top-rated threshold
- Status: VERIFIED
- Source: `ProfileStatsService::recalculate`, `UpdateTopRatedProfilesCommand`.
- Trigger: stats recalculation/daily command.
- Result: top-rated when average >= 4.5 and count >= 5 approved reviews.
- Severity: Medium

### RULE-040 Provider sees only approved own-profile reviews
- Status: VERIFIED
- Source: `ProviderReviewResource::getEloquentQuery`.
- Trigger: provider review panel.
- Result: `whereHas(profile.user_id = auth id)` and `status = approved`.
- Severity: Medium

### RULE-041 Active portfolio items only appear publicly
- Status: VERIFIED
- Source: `PublicFrontendService::provider`, `PortfolioItemPolicy::view`.
- Trigger: public provider page / policy view.
- Result: inactive portfolio items are not loaded for public visitors.
- Severity: Medium

### RULE-042 Provider portfolio item cap is two
- Status: VERIFIED
- Source: `ProviderAssetLimitObserver::enforcePortfolioItems`, `PortfolioItemResource::providerPortfolioCount`, provider list/create pages.
- Trigger: portfolio item save/create UI.
- Result: creating third item fails; create button hidden at 2.
- Severity: Medium

### RULE-043 Portfolio image cap is four per item
- Status: VERIFIED
- Source: `ProviderAssetLimitObserver::enforcePortfolioImages`, `PortfolioItemResource` repeater `maxItems(4)`.
- Trigger: portfolio image save.
- Result: creating fifth image fails.
- Severity: Medium

### RULE-044 Active provider links cap is ten
- Status: VERIFIED
- Source: `ProviderAssetLimitObserver::enforceProviderLinks`, `ProviderProfileResource` repeater `maxItems(10)`.
- Trigger: provider link save.
- Result: more than 10 active links rejected; inactive links are exempt in observer.
- Severity: Medium

### RULE-045 Public provider links must be active
- Status: VERIFIED
- Source: `Profile::activeLinks`, `PublicFrontendService::provider`, `ProviderLinkPolicy::view`.
- Trigger: public provider page/policy view.
- Result: inactive links hidden from public visitors.
- Severity: Medium

### RULE-046 Image upload constraints
- Status: VERIFIED
- Source: `ProfileImageService`, `ProviderProfileResource`, `PortfolioItemResource`.
- Trigger: avatar/cover/portfolio upload.
- Result: JPEG/PNG/WebP only; avatar <=2MB cropped 600x600; cover/portfolio <=4MB scaled max 1600px; stored as WebP on public disk.
- Severity: Medium

### RULE-047 Marketplace ranking hierarchy
- Status: VERIFIED
- Source: `MarketplaceRankingService`.
- Trigger: home/search/category/subcategory queries.
- Result: expirable placement buckets outrank top-rated/normal; then rating desc, review count desc, created desc.
- Severity: High

### RULE-048 Search featured tie-breaker
- Status: VERIFIED
- Source: `MarketplaceRankingService::applySearchRanking`.
- Trigger: search query.
- Result: active featured providers sort by `featured_until` desc inside ranking.
- Severity: Low

### RULE-049 Expired placements are cleared automatically
- Status: VERIFIED
- Source: `ExpirePlacementsCommand::handle`, `routes/console.php`.
- Trigger: daily schedule.
- Result: expired homepage/top-search/top-category/top-subcategory/featured flags false and dates null.
- Severity: Medium

### RULE-050 Provider panel locale forced to Arabic
- Status: VERIFIED
- Source: `ForceProviderPanelLocale`, `AdminPanelProvider`, `ProviderPanelProvider`.
- Trigger: Filament panel request.
- Result: app/session locale set to `ar`.
- Severity: Low

### RULE-051 Public locale limited to English or Arabic
- Status: VERIFIED
- Source: `SetLocale::handle`, `FrontendController::switchLocale`.
- Trigger: web request/locale route.
- Result: invalid locale falls back to English or 404 on switch route.
- Severity: Low

### RULE-052 Activity logs are append-only
- Status: VERIFIED
- Source: `ActivityLog::boot`, `ActivityLogService::log`.
- Trigger: activity log update/delete/create.
- Result: update/delete callbacks return false; service only creates.
- Severity: High

### RULE-053 Provider subscription resource unreachable in nav
- Status: VERIFIED
- Source: `ProviderSubscriptionResource::$shouldRegisterNavigation = false`, `ProviderSubscriptionResource::canAccess`.
- Trigger: provider panel navigation/access.
- Result: resource routes exist but resource-level access returns false.
- Severity: Medium

### RULE-054 Review eligibility middleware is not attached
- Status: UNVERIFIED
- Source: `EnsureReviewEligible`, `routes/web.php`.
- Trigger: intended review submission.
- Result: middleware implements 24-hour account age and 10/day limit, but `/provider/{slug}/review` only has `auth`; rule is not enforced by current routes.
- Severity: High

### RULE-055 Review rate limiters are configured but not attached
- Status: UNVERIFIED
- Source: `AppServiceProvider::configureRateLimiters`, `routes/web.php`.
- Trigger: intended review create/flag.
- Result: `reviews.create` and `reviews.flag` limiters exist but no route uses them in inspected code.
- Severity: Medium

### RULE-056 Forgot/reset password requests exist without visible routes/controllers
- Status: UNVERIFIED
- Source: `ForgotPasswordRequest`, `ResetPasswordRequest`, `routes/web.php`.
- Trigger: password reset flow.
- Result: request rules exist but no non-vendor route found in `route:list`.
- Severity: Medium

## Permission Matrix

| Entity | Guest | User | Provider | Super Admin |
|---|---|---|---|---|
| Profile | View discoverable only; no create/edit/delete | View discoverable; no create/edit/delete | View own always, discoverable others; edit own; no policy create/delete | View/edit/moderate via bypass except create/delete denied |
| User | No | View/update own only | View/update own only | Full via policy bypass; can create provider/admin via custom abilities |
| Subscription | No | No | View own list/detail | View/create/update/approve; delete denied |
| Review | View visible-profile reviews | View visible; create on visible non-own provider; flag visible non-own; cannot edit/delete | View approved own-profile reviews; flag own-profile non-own reviews; cannot create public review | Full moderation/delete via bypass |
| Category | Public active browse | Public active browse | Public active browse | Full Filament management |
| Subcategory | Public active browse | Public active browse | Public active browse | Full Filament management |
| City | Public active browse | Public active browse | Public active browse | Full Filament management |
| PortfolioItem | View active item on discoverable profile | Same | CRUD own profile portfolio | Full via bypass |
| PortfolioImage | View active parent item on discoverable profile | Same | CRUD own portfolio images | Full via bypass |
| ProviderLink | View active link on discoverable profile | Same | CRUD own profile links | Full via bypass |
| ActivityLog | No | No | No | View/create/update/delete allowed by policy bypass, but model blocks update/delete |
| ProviderType | Public active options | Public active options | Public active options | Managed by admin resource |

## State Machines

### User
- Active: `is_active=true`.
- Inactive: `is_active=false` -> login/authenticated access rejected.
- Suspended: `is_suspended=true` via `UserSuspensionService::suspend`; transition back via `reinstate`.
- Locked: `locked_until` future via `AccountSecurityService::recordFailedAttempt`; cleared by successful login or `ClearExpiredLocksCommand`.
- Security flagged: `security_flagged=true` at 20+ failed attempts; manually cleared by ProviderResource action.
- Must change password: `must_change_password=true`; transition false via `PasswordController::change` or `User::updatePassword`.

### Provider/Profile
- Incomplete -> Complete: `ProfileCompletenessService` sets `is_complete` based on required fields.
- Hidden -> Visible: visible when user active, not suspended, profile complete, active approved non-expired subscription exists.
- Visible -> Hidden: subscription expiry/deactivation, suspension, inactive user, incomplete profile.
- Placement states: normal, top-rated, featured, homepage featured, top search, top category, top subcategory; placement expiry command clears expirable flags.

### Subscription
- Pending: `is_active=false` or `approved_at=null`.
- Active: `is_active=true`, `approved_at!=null`, `ends_at>=today`.
- Expired/deactivated: command sets `is_active=false` when `ends_at < now()`.
- Immutable: user/plan/start/end cannot transition after create.
- Deleted: no transition; policy denies delete.

### Review
- Pending, Approved, Rejected: enum `ReviewStatus`.
- Pending -> Approved/Rejected: moderation service or admin resource.
- Approved/Rejected -> other statuses: admin resource permits status select including pending; `ModerateReviewRequest` would not, but Filament edit form does not visibly use that request.
- Flagged: `is_flagged=true` with metadata.
- Deleted: soft delete; only admin via bypass/resource.

### ActivityLog
- Created only.
- Update/delete blocked by model events.

## Automation Inventory

| Trigger | Location | Action | Side Effects |
|---|---|---|---|
| User created | `UserObserver::created` | Dispatch `CreateUserProfileJob` | Logs user creation |
| Create profile job | `CreateUserProfileJob::handle` | Create profile for non-admin user; initialize stats | Logs profile_created; idempotent on duplicate |
| User deleted | `UserObserver::deleted` | Dispatch `SoftDeleteUserProfileJob` | Logs user_deleted |
| Soft delete profile job | `SoftDeleteUserProfileJob::handle` | Soft-deletes user profile | ProfileObserver logs profile_deleted |
| User suspended flag changed | `UserObserver::updated` | Log suspend/reinstate | Activity log |
| User password changed | `UserObserver::updated` | Log password change | Activity log |
| Profile updated | `ProfileObserver::updated` | Reevaluate completeness on key fields | Activity log |
| Review created | `ReviewObserver::created` | Queue stats recalc | Activity log |
| Review status updated | `ReviewObserver::updated` | Queue stats recalc | Activity log |
| Review flagged | `ReviewObserver::updated` | Log flag | Activity log |
| Review deleted | `ReviewObserver::deleted` | Queue stats recalc | Activity log |
| Subscription creating | `SubscriptionObserver::creating` | Validate owner/dates/overlap | Throws validation exception |
| Subscription updating | `SubscriptionObserver::updating` | Block immutable field changes | Throws validation exception |
| Subscription active changed | `SubscriptionObserver::updated` | Log activated/deactivated | Activity log |
| Provider asset save | `ProviderAssetLimitObserver::saving` | Enforce link/image/item caps | Validation exception |
| Daily schedule | `routes/console.php` | Expire subscriptions/placements, recalc top-rated | Mutates subscriptions/profile_stats |
| Every 5 minutes | `routes/console.php` | Clear expired account locks | Mutates users.locked_until |
| Auth attempt/login | `AppServiceProvider::boot` | Reject/eject suspended users | Session invalidation |

## Data Flow Diagrams

### Provider Creation
`Admin Filament ProviderResource CreateProvider` -> `CreateProvider::handleRecordCreation` -> `User::create` -> `assignRole('provider')` -> `UserObserver::created` -> `CreateUserProfileJob` -> `Profile::create` -> `ProfileStatsService::initializeForProfile` -> activity logs.

### Subscription Creation
`SubscriptionResource/CreateSubscription` or provider admin tab -> `Subscription::save` -> `SubscriptionObserver::creating` -> `SubscriptionValidationService` -> pending `Subscription` -> activity log. Approval -> `SubscriptionApprovalService` or `ProviderResource::saveProviderSubscription` metadata -> active subscription -> visibility may become true.

### Login
`AuthController::login` -> `LoginRequest` -> `Auth::attempt` -> failure: `AccountSecurityService::recordFailedAttempt`; success: lock/inactive/suspended checks -> `recordSuccessfulLogin` -> session regenerate -> intended dashboard.

### Password Change
Authenticated request -> `EnsurePasswordChanged` redirects -> `PasswordController::change` -> strong `ChangePasswordRequest` -> password hash set, `must_change_password=false`, `password_changed_at=now`.

### Profile Update
Provider panel -> `ProviderProfileResource` scoped query -> Filament save -> `ProfileObserver::updated` -> `ProfileCompletenessService::evaluate` -> public visibility changes indirectly.

### Review Creation
POST `/provider/{slug}/review` -> `CreateReviewRequest` -> `ReviewPolicy::create` -> duplicate/visibility validation -> `Review::create` -> `ReviewObserver::created` -> `RecalculateProfileStatsJob` -> activity log.

### Search/Profile Visibility
Request -> `SearchProfilesRequest` -> `ProfileSearchService` -> discoverability SQL filters -> `MarketplaceRankingService` -> paginated profiles with relations.

### Account Suspension
Admin action -> `UserSuspensionService::suspend` -> `UserObserver::updated` activity log -> middleware/auth events logout suspended user -> visibility/search hides profile.

## Hidden Rules

- `Profile::$with = ['user']` means profile queries load user by default unless `without('user')` is used.
- `PublicFrontendService::provider` loads active portfolio items and active links only.
- `ProviderReviewResource::getEloquentQuery` hides pending/rejected reviews from providers.
- `ProviderProfileResource::getNavigationItems` hides profile edit nav if no profile exists.
- `ProviderType::options` falls back to hard-coded defaults if table missing, empty, or throws.
- `ActivityLog::boot` blocks updates/deletes at model event level, beyond policy.
- `SubscriptionObserver::updating` blocks immutable financial field changes even if UI/resource sends them.
- `ProfileSearchFilters::fromArray` ignores keywords shorter than 2 characters.
- `SearchProfilesRequest::prepareForValidation` strips HTML tags from keyword.
- `ProfileStats::isFeaturedActive` uses `isFuture()`, while ranking uses `>= today`; same-day featured status may differ depending on method.

## Dead Code / Unreachable Features

- `EnsureReviewEligible`: appears unused by routes. It enforces 24-hour account age and 10 reviews/day, but `/provider/{profile:slug}/review` only has `auth`.
- Rate limiters `reviews.create`, `reviews.flag`, `login`, `register`, `forgot-password`, `verification.resend`: configured in `AppServiceProvider`, but inspected routes only attach `throttle:search`.
- `ForgotPasswordRequest` and `ResetPasswordRequest`: request classes exist, but no non-vendor routes/controllers in `route:list`.
- `SubscriptionApprovalService`: exists and is likely intended for approval, but inspected Filament code also approves by directly setting fields. Verify page/action usage before deleting.
- `FeatureProfileRequest`: exists but no route in `route:list`; Filament placement edits do not use FormRequest.
- `ProviderSubscriptionResource`: routes are registered, but `canAccess()` returns false and navigation is disabled.
- `ProfileSearchService::bucketExpression`: private method marked as unused and no internal calls found.
- `ProviderCredential`: model/table/public load exists, but no Filament resource or mutation path was found in inspected route/resource list.
- `CheckSuspensionAfterAuth` and `EnsureUserNotSuspended` overlap in Filament panel stacks.
- `SuperAdminAuditCommand`: custom audit command creates real records and fakes queue; operational usefulness is questionable.
- `audit_test.php`, `check_admin.php`, `test.php`, `test_localization.php`, `export_migrations_to_doc.php`, and `grep.exe.stackdump` files appear outside normal Laravel app/test structure.

## Technical Debt Report

- Comments and code disagree in places: `CreateReviewRequest` says review status defaults pending, but schema summary shows default currently approved after `2026_06_04_212345_change_reviews_default_to_approved.php`.
- Filament `ReviewResource` edit form permits `pending`, while `ModerateReviewRequest` says pending should never be a moderation target.
- Public review eligibility/rate limiting is implemented but not wired.
- `ProviderResource` can create/update subscription fields directly, while `SubscriptionObserver` blocks immutable updates; editing an existing subscription’s plan/dates through this path may throw rather than present a clean UI.
- Marketplace placement resource has `DeleteBulkAction` on `Profile` model despite policy-level direct profile deletion denial; authorization should be verified in Filament runtime.
- Provider profile resource is Filament-only and does not use `UpdateProfileRequest`; validation parity should be kept in sync manually.
- Several Arabic strings appear mojibake in source output, suggesting encoding/display issues should be checked in files/editor.

## Risk Report

- Critical: Review route missing eligibility/rate-limit middleware allows new accounts and high-volume users unless other middleware is added elsewhere.
- Critical: Review default appears changed to approved in schema, meaning public reviews may affect stats immediately, despite request comment claiming pending.
- High: Filament review moderation can set pending, conflicting with request-layer moderation rule.
- High: ProviderSubscriptionResource routes exist but access false; users may be confused or links may 403.
- High: Financial subscription immutability can produce exceptions if admin edit UI includes immutable fields.
- Medium: Placement expiry uses `< today`; ranking accepts `>= today`, so same-day placements remain active through the day.
- Medium: ActivityLog policy says super_admin can update/delete via bypass, but model silently blocks update/delete; UI expectations may differ.

## Missing Documentation Report

These implementation-backed behaviors should be documented in product/admin operating docs:

- Exact discoverability requirements.
- Profile completeness required fields.
- Subscription lifecycle and immutability.
- Subscription overlap prevention.
- Review creation eligibility, duplicate semantics, and status defaults.
- Review moderation and flagging permissions.
- Failed-login lockout thresholds and security flag behavior.
- Marketplace ranking bucket order.
- Placement expiry behavior.
- Provider asset limits: 2 portfolio items, 4 images/item, 10 active links.
- Panel access by role.
- Activity log immutability.
- Hidden/unwired rules: review eligibility middleware and review rate limiters.
