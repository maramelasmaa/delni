Delni (دلني) — Business Rules Document
Version 3.0 | Final — Approved for Implementation

1. USER JOURNEY
Homepage → Search bar + Category grid    → Select Category      → Select Subcategory (optional)        → Filter by City (optional)            → Browse visible provider profiles              → View full profile + portfolio + reviews              → Tap WhatsApp or Call to contact              → Register/Login to leave a review

2. ROLES
Three roles exist. A user has exactly one role, forever. Role never changes via any normal operation.

Role | Created By | Purpose
--- | --- | ---
super_admin | Existing admin only | Full platform control via Filament
provider | Admin only | Paid listed professional
user | Self-registration | Free public reviewer

3. ACCESS BY ROLE
Guest (not logged in)
- Browse, search, filter profiles ✅
- View full profile + portfolio ✅
- See reviews and ratings ✅
- Tap WhatsApp / Call ✅
- Leave reviews ❌
- See hidden/suspended/expired profiles ❌

Public User (user role)
- Everything a guest can do ✅
- Leave reviews on visible provider profiles ✅
- Flag any review on any visible profile ✅
- Cannot appear in directory ❌
- Cannot review invisible profiles ❌
- Cannot flag own review ❌
- Cannot access admin panel ❌

Provider (provider role)
- Everything a public user can do ✅
- Edit own profile only ✅
- View own profile view count ✅
- Flag reviews on own profile only ✅
- Cannot flag own review ❌
- Cannot delete or edit any review ❌
- Cannot access admin panel ❌
- Cannot manage subscriptions ❌
- Cannot create users ❌

Super Admin (super_admin role)
- Full Filament panel ✅
- Create provider accounts only ✅
- Manage all subscriptions ✅
- Add/edit/delete categories + subcategories ✅
- Add/edit/delete cities ✅
- Feature/unfeature profiles ✅
- Suspend/unsuspend any user ✅
- Moderate all reviews ✅
- View all activity logs ✅
- Cannot own a subscription ❌
- Cannot be suspended by non-admin ❌
- Cannot have a profile ❌

4. AUTHENTICATION RULES
Login blocked if:
- User is suspended
- User is soft-deleted
- User is inactive

Soft-deleted user email:
- Cannot be reused for new registration
- Email uniqueness check includes soft-deleted records

Account can only be restored by admin

Provider first login:
- Forced to change password before accessing any page
- Cannot bypass — every route redirects to password change until done
- After change: must_change_password = false, password_changed_at recorded
- Redirected to profile edit page after completion

Public registration:
- Fields: name, email, password, phone, city, account type
- Profile auto-created as hidden and incomplete
- Profile stats auto-created with default values
- Can log in immediately after registration

5. USER RULES
Create
- Public users register themselves only
- Providers created by admin only in Filament — never via public form
- Admins created by existing admins only
- On provider creation: must_change_password = true, temp password set
- Admin sends credentials to provider via WhatsApp manually
- Profile auto-created for every new provider and user account on creations
- super_admin accounts never have profiles created

Update
- Users update own basic info only
- Admin updates any user via Filament
- Role never changes via normal update — ever
- Email must be unique across all users including soft-deleted records

Suspend
- Admin only
- Required fields: suspension_reason, suspended_at, suspended_by
- Immediate effect: user locked out of all access
- Provider profile becomes hidden immediately
- Active subscription stays on record but profile hides
- Payment cannot override or remove suspension
- Suspension overrides everything: active subscription, featured status, positive reviews

Unsuspend
- Admin only
- Required fields: reinstated_by, reinstated_at, reinstatement_reason
- Profile visibility restores automatically only if all four visibility conditions are met

Delete
- Soft-delete only, admin only
- Reviews preserved permanently — never removed
- Subscription history preserved permanently — never removed
- Activity logs preserved permanently — never removed
- Profile soft-deleted with user automatically
- No permanent delete via UI — ever

6. PROFILE RULES
Create
- Auto-created by system when a provider or user account is created
- Never created manually
- Created as hidden and incomplete by default
- One profile per user — enforced at DB level
- Only provider role profiles are ever publicly discoverable
- user role profiles are permanently hidden — always
- super_admin accounts never have profiles

Profile Stats Create
- Auto-created by system immediately when a profile is created
- Never created manually
- Exactly one profile_stats record exists per profile — enforced at DB level
- Initial values on creation:
  - rating_avg = 0.0
  - reviews_count = 0
  - is_top_rated = false
  - is_featured = false
  - featured_until = null

Completeness
- Profile is automatically marked is_complete = true when ALL of these are filled:
  - Business name or user name
  - Bio
  - City
  - Category
  - WhatsApp or phone number
  - Logo / profile photo
- Rechecked and updated automatically on every profile save

Visibility (derived — never stored as a column)
Provider profile is publicly visible only when ALL four conditions are true simultaneously:
- User is_active = true
- User is_suspended = false
- Subscription is_active = true AND ends_at >= today
- Profile is_complete = true

One condition failing hides the profile immediately and automatically.

New providers are never publicly visible immediately after admin creation — profile starts incomplete and subscription starts inactive.

Featured and Visibility Independence
- is_featured is marketing state — stored independently from visibility
- Visibility is publication state — always derived from the four conditions above
- If a featured provider gets suspended or their subscription expires: profile hides but is_featured value is preserved in DB
- When visibility is restored: featured status resumes automatically if featured_until is still in the future
- Admin cannot feature a currently invisible profile
- featured_until must be a future date at the time of setting — past or today is rejected
- Scheduled job auto-sets is_featured = false when featured_until passes

Update
- Provider edits own profile only
- Admin edits any profile via Filament
- Subcategory must belong to selected category — validated on every save
- Changing category automatically clears subcategory selection

Category / City Deactivation Effect on Profiles
- Profiles assigned to inactive categories remain assigned
- Profiles assigned to inactive cities remain assigned
- Inactive categories and cities are hidden from public filters only
- Profile visibility is not affected by category or city deactivation alone
- The four visibility conditions govern visibility entirely
- This protects providers from losing visibility due to admin category or city changes

Delete
- Soft-deleted automatically when user is soft-deleted
- Portfolio items and images cascade delete with profile
- Profile stats cascade delete with profile
- Reviews remain permanently — not deleted with profile

7. SUBSCRIPTION RULES
Ownership
- Only provider role accounts can own subscriptions
- user role accounts can never own subscriptions
- super_admin accounts can never own subscriptions
- Enforced at Filament form level and observer level

Create
- Admin only in Filament
- Provider accounts only
- Starts as is_active = false — profile not visible until approved
- Must have starts_at and ends_at
- ends_at must be strictly later than starts_at — same date is rejected
- Date ranges for the same provider must never overlap — regardless of active status
- Validated on create and update

Approve / Activate
- Admin confirms bank transfer manually
- Sets is_active = true
- Records: approved_by, approved_at, processed_by, processed_at
- Profile becomes visible automatically if all four visibility conditions are met

Expiry
- Daily scheduled job runs automatically
- Sets is_active = false where ends_at < today
- Profile auto-hides as a result
- Idempotent — running multiple times produces identical result

Extend
- Admin updates ends_at on current subscription record
- Or admin creates a new subscription record for the next period
- New record must not overlap with any existing subscription period for that provider regardless of status
- New ends_at must be strictly later than new starts_at

Payment Immutability
- Once subscription is approved: payment_reference, payment_date, payment_method cannot be modified
- Paid amounts are permanent financial record

Suspension Override
- Suspended provider with active subscription remains hidden
- Removing suspension restores visibility only if all other three visibility conditions are also met

History
- Soft-deleting a provider does not affect subscription records
- Subscriptions are never soft-deleted or hard-deleted via any UI action
- All subscription records are permanent financial history

8. REVIEW RULES
Create
- Must be logged in
- Cannot review own profile
- Cannot review a hidden or invisible profile
- One review per user per profile — enforced at DB unique constraint and validation
- Rating: integer 1 to 5 only — no decimals, no values outside range
- Comment: optional
- New reviews default to is_approved = true — publicly visible immediately

Flagging
- Any logged-in user can flag any review — except their own
- Provider can flag reviews on their own profile only
- Provider cannot flag reviews on other profiles
- Reviewer cannot flag their own review
- Records on flag: flagged_by, flagged_at, flagged_reason
- Flagged review remains visible until admin takes action

Moderation (admin only)
- Approve: sets is_approved = true
- Reject: sets is_approved = false
- Delete: soft-delete
- Every moderation action records: moderated_by, moderated_at, moderation_note

Update
- Reviewer cannot edit their own review after submission — ever
- Admin can edit any review via Filament moderation panel

Delete
- Soft-delete only
- Admin only
- Reviewer cannot delete own review

Rating automatically recalculated after deletion
- Triggers automatically when a review is created, approved, rejected, or soft-deleted.
- Calculates from approved non-deleted reviews only.
- Updates:
  - profile_stats.rating_avg
  - profile_stats.reviews_count
  - profile_stats.is_top_rated → true if rating_avg >= 4.5 AND reviews_count >= 5

DISCOVERY & SEARCH RULES
- Visibility filter always runs first
- No hidden, suspended, inactive, expired, or incomplete profile ever appears in any result under any circumstance.

Filter options
- All filter data fed from admin-managed records:
  - Cities: active only
  - Categories: active only
  - Subcategories: active only, belonging to selected category only

Ranking order (always applied in this exact order)
Bucket 1 — Featured (is_featured = true + featured_until in future):
- Sorted by featured_until DESC, then rating_avg DESC
Bucket 2 — Top Rated (rating_avg >= 4.5 AND reviews_count >= 5):
- Sorted by rating_avg DESC, then reviews_count DESC
Bucket 3 — Normal (all other visible profiles):
- Sorted by created_at DESC

- Featured always overrides rating regardless of score. A featured profile with zero reviews ranks above a top-rated profile.
- Subcategory precedence: When filtering by subcategory, exact subcategory matches rank above profiles that only match the parent category. Within each ranking bucket, this ordering is preserved.
- Keyword search searches across: business_name, bio, user name
- Visibility filter always applies before any results are returned.

10. CATEGORY & SUBCATEGORY RULES
Create
- Admin only
- Slug auto-generated from name, must be unique globally
- Subcategory must be assigned to a parent category on creation

Update
- Admin only
- Deactivating a category hides it and all its subcategories from public filters
- Slug must not change after creation — used in URLs and filters

Delete (soft)
- Admin only
- Deleting a category cascades soft-delete to all its subcategories
- Profiles linked to deleted category: category_id set to null automatically
- Profiles linked to deleted subcategory: subcategory_id set to null automatically
- Profiles remain — visibility unaffected by category deletion alone

11. CITY RULES
Create
- Admin only
- Slug auto-generated, must be unique

Update
- Admin only
- Deactivating hides city from all public filters

Delete (soft)
- Admin only
- Profiles linked to deleted city: city_id set to null automatically
- Profiles remain — visibility unaffected by city deletion alone

12. ACTIVITY LOG RULES
- Append-only — no updates, no deletes, ever, under any circumstance
- Events logged:
  - User created
  - User suspended / unsuspended
  - Password changed
  - Profile created / updated
  - Subscription created / activated / expired
  - Review created / flagged / moderated / deleted
  - Profile featured / unfeatured
  - Category / subcategory created / updated / deleted
  - City created / updated / deleted
- If acting user is later deleted: log record remains, user_id set to null

13. SCHEDULED JOBS (run daily)
Job | Action
--- | ---
Expire Subscriptions | Sets is_active = false where ends_at < today ✅
Expire Featured Profiles | Sets is_featured = false where featured_until < today ✅
Update Top Rated Profiles | Recalculates is_top_rated for all profiles ✅

All three jobs produce identical results if run multiple times on the same day.

14. DATABASE CONSTRAINTS
Constraint | Table | Enforced At
--- | --- | ---
Email unique including soft-deleted | users | DB + app
One profile per user | profiles.user_id UNIQUE | DB
One profile stats per profile | profile_stats.profile_id PRIMARY | DB
One review per user per profile | (user_id, profile_id) UNIQUE | DB + validation
Category slug unique | categories.slug | DB
City slug unique | cities.slug | DB
Subcategory slug unique | subcategories.slug | DB
Subcategory belongs to correct category | Validation on every save | App
No overlapping subscription date ranges | Validation on create and update | App
Subscription ends_at strictly after starts_at | Validation on create and update | App
featured_until must be future date when setting | Validation on feature action | App
Subscription owned by provider only | Filament + observer | App

15. FORBIDDEN STATES
These must never exist in the system under any circumstance:
- Suspended user with active access to any feature
- Provider visible publicly without an active subscription
- Provider with overlapping subscription date ranges regardless of status
- Subscription with ends_at equal to or before starts_at
- Subscription owned by a user or super_admin account
- Approved subscription with no approved_by or approved_at recorded
- User reviewing their own profile
- Review with rating outside integer range 1 to 5
- Anonymous review
- User with more than one role
- User with zero roles
- Subcategory assigned to wrong parent category
- Profile belonging to a super_admin account
- user role profile appearing in public discovery
- Soft-deleted user email reused by new registration
- New provider publicly visible immediately after admin creation
- Profile stats record not existing for a profile
- is_featured = true with featured_until in the past or null
- Activity log record modified or deleted
- Payment fields modified after subscription approval

End of document — Version 3.0 Final — Approved for Implementation

If you're building Delni as a real SaaS and not a university CRUD project, security should be part of the architecture from day one, not something added before launch.

Laravel already gives you a lot of the tools, including login throttling/rate limiting and lockout mechanisms.

For Delni, I'd add a dedicated Security Layer to your SRS.

Priority 1 — Authentication Security
- Login Rate Limiting
  - After: 5 failed attempts
  - within: 5 minutes
  - lock login for: 15 minutes
  - Track by: email + IP not IP alone.
- Escalating Lockouts
  - Failed Attempts 1–5: 15 min
  - 10: 1 hour
  - 20: 24 hours
  - Store: failed_login_attempts, last_failed_login_at, locked_until on users.
- Suspicious Activity Auto-Suspension
  - If: 50 failed logins within: 24 hours
  - flag account: security_flagged = true
  - Admin sees it in Filament.
  - Do NOT automatically ban forever.

Priority 2 — Registration Protection
- Prevent: bot registrations
- Use: Cloudflare Turnstile or hCaptcha
- Registration Rate Limit: 3 accounts per IP per hour
- Nobody legitimately creates 20 accounts.
- Use Laravel RateLimiter.

Priority 3 — Review Abuse Protection
- Prevent fake reviews.
- Add: reviews.created_at rule: Account must be 24 hours old before reviewing
- And: Maximum 10 reviews/day
- Review Spam Detection
- Auto-flag if: same comment posted repeatedly.

Priority 4 — Upload Security
- Every uploaded image: validate mime, validate size, re-encode to WebP, strip metadata, generate random filename
- Never trust extension.
- Never do: $image->getClientOriginalName() for storage.

Priority 5 — Authorization
- Never trust Filament.
- Even if Filament hides buttons.
- Still create: UserPolicy, ProfilePolicy, ReviewPolicy, SubscriptionPolicy
- Example: ProfilePolicy return $user->id === $profile->user_id;

Priority 6 — Database Hardening
- Add indexes for: email, slug, city_id, category_id, subcategory_id, user_id, profile_id, is_active, ends_at
- Especially: subscriptions(user_id, ends_at), reviews(profile_id, is_approved)

Priority 7 — Activity Logging
- Log: Login success, Login failure, Password change, Suspension, Unsuspension, Review creation, Review deletion, Subscription approval, Feature profile
- This becomes invaluable later.

Priority 8 — Public Search Protection
- Rate limit: search endpoint
- Example: 60 requests/minute per IP
- Prevents scraping.

Priority 9 — Admin Panel Protection
- For /admin: 2FA mandatory even if normal users don't get 2FA.
- Admins are the crown jewels.
- Laravel Fortify supports two-factor authentication.

Priority 10 — Infrastructure
- Before production: HTTPS only
- URL::forceScheme('https');
- Security Headers: CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy
- Cloudflare: put Delni behind Cloudflare
- Immediately.
- You get: DDoS protection, bot protection, rate limiting, WAF

For Delni specifically
If I were implementing your project today, after migrations and models my order would be:
- Relationships
- Spatie Roles
- Policies
- Observers
- Services
- Security middleware
- Filament Resources
- Auth flow
- Public pages
- Scheduled jobs
- Security audit pass

The biggest mistake Laravel beginners make is jumping straight into Filament resources and pages before enforcing the invariants and security rules. For Delni, your observers, policies, services, and security middleware are actually the foundation; Filament and Blade pages sit on top of that foundation.
