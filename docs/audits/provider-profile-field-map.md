# Provider Profile Field Inventory & Connection Map

**Last Updated:** 2026-06-26  
**Status:** AUDIT IN PROGRESS

## Executive Summary

This audit maps every provider-editable field from the Filament provider panel through the database, API, and into the React Native mobile UI. It identifies connection gaps, broken flows, and missing fallbacks.

---

## PART 1: FIELD INVENTORY BY DOMAIN

### Identity & Branding

| Field | DB Column | Provider Editable | Required | API Returned | Mobile Displayed | Current Behavior if Empty |
|-------|-----------|------------------|----------|--------------|------------------|--------------------------|
| Business Name | `business_name` | ✅ Yes | ✅ Required | ✅ Yes (as `name`) | ✅ Yes | Falls back to user→name, then "مقدم خدمة" |
| Provider Type | `provider_type` | ✅ Yes | ✅ Required | ❌ No | ❌ No | **MISSING FROM API** |
| Category | `category_id` | ✅ Yes | ✅ Required | ✅ Yes | ✅ Yes | Displays category name or subcategory fallback |
| Subcategories | N/A (BelongsToMany) | ✅ Yes (multiple) | ✅ Required | ✅ Yes | ✅ Yes (as `services`) | Hides if empty |
| City | `city_id` | ✅ Yes | ✅ Required | ✅ Yes | ✅ Yes | Displays city name |
| Logo | `logo` | ✅ Yes | ✅ Required | ✅ Yes (as `logo_url`) | ✅ Yes (as `avatarUrl`) | Fallback to Unsplash placeholder |
| Cover Image | `cover_image` | ✅ Yes | ✅ Required | ✅ Yes (as `cover_url`) | ✅ Yes (as `coverUrl`) | Fallback to first portfolio image or Unsplash placeholder |

### Professional Information

| Field | DB Column | Provider Editable | Required | API Returned | Mobile Displayed | Current Behavior if Empty |
|-------|-----------|------------------|----------|--------------|------------------|--------------------------|
| Bio/About | `bio` | ✅ Yes | ❌ Optional | ✅ Yes (as `description`) | ✅ Yes (as `about`) | Hides section if empty |
| Years Experience | `experience_years` | ❌ No in form | ❌ Optional | ✅ Yes (as `years_experience`) | ✅ Yes (as `yearsExperience`) | Shows null; should hide |
| Service Area Note | `service_area_note` | ❌ No in form | ❌ Optional | ✅ Yes | ✅ Yes | Shows null; should hide |
| Remote Work | `offers_remote_work` | ✅ Yes | ❌ Optional | ✅ Yes | ✅ Yes (as `worksRemotely`) | Defaults to false, displays correctly |

### Contact Information

| Field | DB Column | Provider Editable | Required | API Returned | Mobile Displayed | Current Behavior if Empty |
|-------|-----------|------------------|----------|--------------|------------------|--------------------------|
| Phone | `phone` | ✅ Yes | ✅ Required | ✅ Yes | ✅ Yes | Shows button; should hide if empty |
| WhatsApp | `whatsapp` | ✅ Yes | ✅ Required | ✅ Yes (as `whatsapp_url`) | ✅ Yes | Shows button; should hide if empty |
| Email | N/A (from `users` table) | ❌ No direct field | ❌ Optional | ❌ No | ⚠️ Partial (accessed as `anyProvider.email`) | **NOT OFFICIALLY IN API** |
| Website | `website` | ✅ Yes | ❌ Optional | ✅ Yes | ✅ Yes (in socialLinks) | Hides if empty |
| Map URL | `map_url` | ✅ Yes | ❌ Optional | ✅ Yes | ✅ Yes (in socialLinks) | Hides if empty |

### Social Links

| Field | DB Column | Provider Editable | Required | API Returned | Mobile Displayed | Current Behavior if Empty |
|-------|-----------|------------------|----------|--------------|------------------|--------------------------|
| Facebook | `facebook_slug` (input: `facebook_slug`) | ✅ Yes | ❌ Optional | ✅ Yes (in `social_links.facebook`) | ✅ Yes | Hides if empty |
| Instagram | `instagram_handle` (input: `instagram_handle`) | ✅ Yes | ❌ Optional | ✅ Yes (in `social_links.instagram`) | ✅ Yes | Hides if empty |
| LinkedIn | `linkedin_slug` (input: `linkedin_slug`) | ✅ Yes | ❌ Optional | ✅ Yes (in `social_links.linkedin`) | ✅ Yes | Hides if empty |
| GitHub | `github_username` (input: `github_username`) | ✅ Yes | ❌ Optional | ✅ Yes (in `social_links.github`) | ✅ Yes | Hides if empty |

### Content & Credentials

| Field | DB Column | Provider Editable | Required | API Returned | Mobile Displayed | Current Behavior if Empty |
|-------|-----------|------------------|----------|--------------|------------------|--------------------------|
| Portfolio Items | N/A (HasMany) | ✅ Yes (separate resource) | ❌ Optional | ✅ Yes | ✅ Yes (as `projects`) | Hides section if empty |
| Credentials | N/A (HasMany) | ✅ Yes (separate resource) | ❌ Optional | ✅ Yes | ✅ Yes | Hides section if empty |

### System & Metadata

| Field | DB Column | Provider Editable | Required | API Returned | Mobile Displayed | Current Behavior if Empty |
|-------|-----------|------------------|----------|--------------|------------------|--------------------------|
| Slug | `slug` | ❌ Auto-generated | N/A | ✅ Yes | ✅ Yes | Always present |
| Rating Average | N/A (computed from `profile_stats.rating_avg`) | ❌ No | N/A | ✅ Yes | ✅ Yes | Shows 0.0 if no reviews |
| Reviews Count | N/A (computed from approved reviews) | ❌ No | N/A | ✅ Yes | ✅ Yes | Shows 0 if no reviews |
| Is Favorited | N/A (computed per user) | ❌ No | N/A | ✅ Yes | ✅ Yes | False if not authenticated |
| Can Review | N/A (computed per user) | ❌ No | N/A | ✅ Yes | ✅ Yes | False if conditions not met |
| Review Status Message | N/A (computed) | ❌ No | N/A | ✅ Yes | ✅ Yes | Optional message string |
| Is Featured | N/A (from `profile_stats.is_homepage_featured`) | ❌ No (admin only) | N/A | ✅ Yes | ❌ No | **NOT DISPLAYED IN MOBILE** |

---

## PART 2: FIELD CONNECTION STATUS

### ✅ CONNECTED (Form → DB → API → UI)

These fields have complete, working flow:

1. **business_name** → Always shown as provider name
2. **category_id** → Displayed as category name
3. **subcategories** → Displayed as services list
4. **city_id** → Displayed as city name
5. **logo** → Displayed as avatar with fallback
6. **cover_image** → Displayed as cover with fallback
7. **bio** → Displayed as "about" section
8. **phone** → Displayed as call button
9. **whatsapp** → Displayed as WhatsApp button
10. **website** → Displayed in social links
11. **facebook_slug** → Displayed in social links
12. **instagram_handle** → Displayed in social links
13. **linkedin_slug** → Displayed in social links
14. **github_username** → Displayed in social links
15. **map_url** → Displayed in social links
16. **offers_remote_work** → Displayed as "works remotely" badge (DEBUG LOG PRESENT)
17. **portfolio_items** → Displayed as projects gallery
18. **credentials** → Displayed as credentials section

### ⚠️ PARTIAL (Form → DB → API but UI issues)

These fields reach the API but have display issues:

1. **years_experience** - In form? NO. In API? YES. In UI? YES but shows null when empty instead of hiding. 
2. **service_area_note** - In form? NO. In API? YES. In UI? YES but shows null when empty instead of hiding.

### 🚫 MISSING (Form → DB → API but NOT displayed)

These fields are in the API but not displayed in mobile UI:

1. **provider_type** - In form? YES. In API? NO. In UI? NO. **NOT RETURNED BY API**
2. **is_featured** - In API? YES. In UI? NO. **INTENTIONAL HIDE** (maybe for homepage-only feature)
3. **email** - In API? NO (unofficial hack in mapper). In UI? Partial (as `anyProvider.email`).

### ❌ MISSING FROM FORM (Not editable by provider)

These fields should be editable but aren't available in the provider panel form:

1. **years_experience** - Should be editable
2. **service_area_note** - Should be editable
3. **provider_type** - In form but not validated to be saved (saveProviderData doesn't include it)

### ❌ BROKEN FALLBACK BEHAVIOR

1. **yearsExperience** - API returns value but mobile shows null for 0 or null
2. **serviceAreaNote** - API returns value but mobile shows null for empty string
3. **email** - Accessed via hack (`anyProvider.email`) instead of official API contract
4. **offers_remote_work** - Debug log present in mobile code, should be removed

---

## PART 3: API CONTRACT ANALYSIS

### Current ProviderDetailResource Response

```php
[
    'id' => $this->id,
    'slug' => $this->slug,
    'name' => $this->business_name ?: ($this->user->name ?? ''),
    'description' => $this->bio,
    'category' => new CategoryResource($this->whenLoaded('category')),
    'subcategories' => SubcategoryResource::collection($this->whenLoaded('subcategories')),
    'city' => new CityResource($this->whenLoaded('city')),
    'rating_average' => $ratingAverage,
    'reviews_count' => $reviewsCount,
    'logo_url' => $this->logo ? asset('storage/'.$this->logo) : $this->getFallbackLogo((int) $this->id),
    'cover_url' => $this->cover_image ? asset('storage/'.$this->cover_image) : $this->getFallbackCover((int) $this->id),
    'portfolio_images' => $portfolioImages,
    'portfolio_items' => PortfolioItemResource::collection($this->whenLoaded('portfolioItems')),
    'phone' => $this->phone,
    'whatsapp_url' => $this->whatsapp ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $this->whatsapp) : null,
    'website' => $this->website,
    'social_links' => [
        'facebook' => $this->facebook,
        'instagram' => $this->instagram,
        'linkedin' => $this->linkedin,
        'github' => $this->github,
    ],
    'service_area_note' => $this->service_area_note,
    'offers_remote_work' => (bool) $this->offers_remote_work,
    'years_experience' => $this->experience_years,
    'is_favorited' => $isFavorited,
    'can_review' => $canReview,
    'review_status_message' => $reviewStatusMessage,
    'credentials' => $this->whenLoaded('credentials'),
    'reviews' => ReviewResource::collection($this->whenLoaded('approvedReviews')),
]
```

### Issues Found

1. **Missing `provider_type`** - Form has it, database has it, but API doesn't return it
2. **Missing `credentials`** - Should use `ProviderCredentialResource` collection, not raw values
3. **Using `$this->facebook` etc.** - These are Eloquent accessors that reconstruct URLs from slugs, not raw database values. Should clarify API contract.
4. **Fallback logos/covers hardcoded** - Using Unsplash images as fallback, which is good
5. **No explicit null handling** - Empty strings vs null not normalized

---

## PART 4: FRONTEND MAPPER ANALYSIS

### Current mapProviderProfile Function

Location: [slug].tsx:87-197

**Issues:**
1. Checks for 'placeholder' and 'localhost:8000' in URLs (fragile)
2. Uses fallback portfolio image as cover if no cover image
3. Builds socialLinks array with hardcoded icons and colors
4. Accesses `anyProvider.email` which is not in official API (hack)
5. Uses `anyProvider.offers_remote_work` which is not official (hack)
6. No null checking for nested objects (could crash)
7. No trimming of strings
8. No validation of URLs before rendering

---

## PART 5: GAPS SUMMARY

### Critical Issues (Fix Required)

| Issue | Impact | Location | Priority |
|-------|--------|----------|----------|
| `email` accessed via hack | Not in API contract | Frontend mapper | HIGH |
| `offers_remote_work` accessed via hack | Not in API contract | Frontend mapper | HIGH |
| Debug log in production code | Noise in logs | Frontend screen | MEDIUM |
| `yearsExperience` not hidden when empty | Shows null | Frontend UI | MEDIUM |
| `serviceAreaNote` not hidden when empty | Shows null | Frontend UI | MEDIUM |
| `provider_type` not returned by API | Cannot display | Backend API | MEDIUM |

### Missing Features (Enhancement)

| Feature | Benefit | Complexity |
|---------|---------|-----------|
| Frontend normalizer function | Safe data handling, single source of truth | Low |
| Null/empty string handling in API | Consistent contract | Low |
| Email field in API contract | Official instead of hack | Low |
| Additional fallback rules | Better UX for incomplete profiles | Low |

---

## PART 6: FORM VALIDATION AUDIT

### Provider Panel Form (ProfileResource)

**Required fields:**
- business_name (max 500)
- provider_type (required, searchable)
- category_id (required, searchable, live)
- subcategories (required, multiple, searchable, live)
- city_id (required, searchable)
- phone (tel, required, max 20) ✅ Has validation rule (should normalize)
- whatsapp (tel, required, max 20) ✅ Helper text good
- logo (required, image, max 2MB)
- cover_image (required, image, max 4MB)

**Optional fields:**
- bio (max 500) ✅
- offers_remote_work (toggle) ✅
- website (URL, SafeExternalUrl rule) ✅
- instagram_handle (SocialProfileReference rule) ✅
- facebook_slug (SocialProfileReference rule) ✅
- linkedin_slug (SocialProfileReference rule) ✅
- github_username (SocialProfileReference rule) ✅
- map_url (URL, SafeExternalUrl with whitelist) ✅

**Issues:**
1. **years_experience** - Missing from form but in database and API
2. **service_area_note** - Missing from form but in database and API
3. **Phone normalization** - Form accepts tel but doesn't normalize (spaces, + prefix, etc.)
4. **Provider type not saved** - Form includes it but saveProviderData() doesn't save it

---

## PART 7: FIELDS NOT IN PROVIDER FORM BUT IN API

These fields are in the database and API but NOT editable by providers in their panel:

1. **years_experience** - Should be editable (likely typo in column name)
2. **service_area_note** - Should be editable
3. **provider_access_ends_at** - Admin-only field, not in provider form (correct)

---

## RECOMMENDATIONS

1. ✅ **Add missing fields to provider form** - years_experience, service_area_note
2. ✅ **Normalize phone/whatsapp on save** - Remove spaces, normalize format
3. ✅ **Fix API to return provider_type** - Currently missing
4. ✅ **Normalize email field** - Make official in API instead of hack
5. ✅ **Create frontend normalizer function** - Safe, type-safe data mapping
6. ✅ **Implement clean fallback rules** - Hide empty fields, show appropriate defaults
7. ✅ **Remove debug logs** - Clean up production code
8. ✅ **Add null/empty checks in mapper** - Prevent crashes on missing nested objects
9. ✅ **Validate URLs before rendering** - Prevent injection or broken links
10. ✅ **Fix credential display** - Use proper resource formatting

---

## Next Steps

See: [provider-profile-api-contract.md](provider-profile-api-contract.md)
See: [provider-profile-ui-fallbacks.md](provider-profile-ui-fallbacks.md)
