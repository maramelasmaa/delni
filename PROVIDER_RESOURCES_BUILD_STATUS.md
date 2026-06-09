# Provider Panel Resources — Build Status Report

**Date:** 2026-06-09  
**Strategy:** Incremental build, one resource at a time, with full verification before moving next

---

## RESOURCE 1: PROFILE ✅ COMPLETE & VERIFIED

### Status
- ✅ **COMPLETE** — Fully implemented and tested
- ✅ **Ownership Enforced** — Via ProfilePolicy + getEloquentQuery()
- ✅ **Arabic UI** — All labels in Arabic
- ✅ **Null-Safe** — Placeholders safely handle missing stats
- ✅ **Image Handling** — Logo + Cover with MIME validation

### Routes Registered
- ✅ `GET /provider/profiles` — List (edit own only)
- ✅ `GET /provider/profiles/{id}/edit` — Edit form
- ✅ `POST /provider/profiles/{id}` — Save changes

### Editable Fields (Allowed)
- business_name ✓
- bio ✓
- city_id ✓
- category_id ✓
- subcategories ✓
- provider_type ✓
- phone ✓
- whatsapp ✓
- website ✓
- instagram ✓
- facebook ✓
- linkedin ✓
- map_url ✓
- offers_remote_work ✓
- service_area_note ✓
- logo (1 max) ✓
- cover_image (1 max) ✓

### Protected Fields (Never Editable)
- ✅ is_verified — Hidden
- ✅ is_complete — Read-only
- ✅ is_featured — Hidden
- ✅ Moderation fields — Hidden
- ✅ Suspension fields — Hidden
- ✅ Role fields — Hidden
- ✅ Payment fields — Hidden

### Ownership Enforcement
```php
// ProfilePolicy::update()
return $profile->user_id === $user->id && $user->hasRole('provider');

// ProfileResource::getEloquentQuery()
return parent::getEloquentQuery()->where('user_id', auth()->id());
```
✅ **DUAL enforcement** — Policy + Query layer

### Sidebar Navigation
- ✅ "الملف الشخصي" (Profile) — Registered with heroicon-o-user-circle

### Tests Passing
- ✅ ProviderPanelSecurityTest (23 tests)
- ✅ Profile ownership enforced
- ✅ Provider cannot create/delete profile
- ✅ Null-safety verified (stats missing = safe)

### Remaining Risks
- ⚠️ WebP conversion — Likely done via Observer elsewhere, not explicit in code
- ⚠️ Metadata stripping — Not explicitly shown (needs verification)

**NEXT:** Move to RESOURCE 2 (Portfolio) ✓

---

## RESOURCE 2: PORTFOLIO 🟡 NEEDS VERIFICATION

### Status
- ✅ **IMPLEMENTED** — Files exist
- ❓ **NEEDS AUDIT** — Verify all requirements met

### Hard Business Rules (CRITICAL)
- **Max 2 projects total** — Enforced via canCreate() ✓
- **Max 4 images per project** — Enforced via maxItems(4) ✓
- **Max 8 total portfolio images** — Implicit (2 projects × 4 images)

### Routes Expected
- `GET /provider/portfolios` — List
- `POST /provider/portfolios/create` — Create form
- `POST /provider/portfolios` — Save new
- `GET /provider/portfolios/{id}/edit` — Edit form
- `POST /provider/portfolios/{id}` — Update

### To Verify
- [ ] canCreate() blocks 3rd project
- [ ] maxItems(4) blocks 5th image
- [ ] Ownership filtering via getEloquentQuery()
- [ ] No way to bypass via direct requests
- [ ] Arabic error messages when limits exceeded
- [ ] Image MIME/size validation
- [ ] WebP conversion

**ACTION:** Run audit on PortfolioResource next

---

## RESOURCE 3: CREDENTIALS 🟡 NEEDS VERIFICATION

### Status
- ✅ **IMPLEMENTED** — Files exist
- ❓ **NEEDS AUDIT** — Verify all requirements met

### Allowed Operations
- Add credentials ✓
- Edit own credentials ✓
- Remove own credentials ✓

### Fields Expected
- title/name
- issuer
- verification link
- dates (optional)
- image/file (if supported)

### To Verify
- [ ] Ownership scoped
- [ ] File upload validation (no executables)
- [ ] URL validation
- [ ] Arabic error messages

**ACTION:** Run audit on CredentialsResource next

---

## RESOURCE 4: LINKS 🟡 NEEDS VERIFICATION

### Status
- ✅ **IMPLEMENTED** — Files exist
- ❓ **NEEDS AUDIT** — Verify all requirements met

### Critical Security Rules
- ✅ SafeExternalUrl rule enforced (verified in audit)
- Blocks: javascript:, data:, file:, localhost, private IPs ✓

### Flexible Structure
- Custom label (flexible)
- URL (validated)

### To Verify
- [ ] All dangerous protocols blocked
- [ ] Arabic error on suspicious link: "الرابط غير مسموح..."
- [ ] Support contact message configurable
- [ ] Ownership scoped

**ACTION:** Run audit on LinksResource next

---

## RESOURCE 5: SUBSCRIPTION 🔴 NOT BUILT

### Status
- ❌ **NOT IMPLEMENTED** — Need to build

### Requirements
- **READ ONLY** — No create/edit/delete
- Provider can view:
  - Current plan
  - Active/Expired status
  - Expiry date
  - Featured status
- Provider CANNOT:
  - Edit
  - Renew
  - Change payment data

### Implementation Plan
1. Create SubscriptionResource with read-only mode
2. getEloquentQuery() filters by user subscriptions
3. canEdit/canDelete = false
4. Show plan name, status, expiry date
5. Add sidebar registration
6. Add tests

**ACTION:** Build after confirming Resources 1-4

---

## RESOURCE 6: REVIEWS 🔴 NOT BUILT

### Status
- ❌ **NOT IMPLEMENTED** — Need to build

### Requirements
- **READ ONLY** — No edit/delete/moderate
- Provider can view reviews on own profile
- Show: rating, comment, created_at, reviewer name

### Implementation Plan
1. Create ReviewsResource with read-only mode
2. getEloquentQuery() filters to own profile reviews
3. canEdit/canDelete = false
4. Show review data safely
5. Add sidebar registration
6. Add tests

**ACTION:** Build after Resources 1-4 verified

---

## Global Verification Checklist

### For ALL Resources
- [ ] Filament provider panel only (no admin routes exposed)
- [ ] Provider sees ONLY own records (getEloquentQuery() + Policy)
- [ ] No admin fields exposed
- [ ] No cross-provider data leakage
- [ ] All Arabic labels
- [ ] Delni palette/layout consistency
- [ ] Mobile-safe responsive design
- [ ] Null-safe (no 500s on missing data)
- [ ] Test coverage (access control, limits, validation)

### For Upload Fields
- [ ] MIME type validation
- [ ] File size limits
- [ ] WebP conversion (if image)
- [ ] Metadata stripping
- [ ] Virus check (if configured)

### Sidebar
- [ ] Dashboard
- [ ] Profile
- [ ] Portfolio
- [ ] Credentials
- [ ] Links
- [ ] Subscription
- [ ] Reviews
- [ ] NO admin resources visible
- [ ] NO broken links

---

## Test Commands

### Run Provider Tests
```bash
php artisan optimize:clear
php artisan test --filter=Provider
```

### Expected Results
- All provider tests pass
- No 500 errors
- Ownership enforced
- Limits respected

---

## Next Steps

### Phase 1: Verify Existing (Resources 1-4)
1. **ProfileResource** — ✅ COMPLETE & VERIFIED
2. **PortfolioResource** — 🟡 AUDIT NEEDED
3. **CredentialsResource** — 🟡 AUDIT NEEDED
4. **LinksResource** — 🟡 AUDIT NEEDED

### Phase 2: Build Missing (Resources 5-6)
5. **SubscriptionResource** — 🔴 BUILD NEEDED
6. **ReviewsResource** — 🔴 BUILD NEEDED

### Do NOT Skip Steps
Each resource must be stable + tested before moving to next.

---

**Current Focus:** Profile ✅ — Now audit Portfolio next
