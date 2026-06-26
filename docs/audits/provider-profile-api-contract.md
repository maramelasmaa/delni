# Provider Profile API Contract

**Stable Public API Contract for /api/v1/providers/{slug}**

---

## Principles

1. Return raw values, not formatted UI strings
2. Return null for missing optional fields (never empty string or "N/A")
3. Absolute URLs for all assets (not relative)
4. Never return admin-only or private fields
5. Nested objects always protected with null checks
6. Phone numbers raw (normalized by frontend if needed)
7. URLs safe and validated

---

## Response Contract

### Root Level

```typescript
{
  // Identity
  id: number,
  slug: string,
  name: string,  // business_name OR user.name
  provider_type: string | null,  // NEW: was missing

  // Branding
  logo_url: string | null,  // absolute URL or null
  cover_url: string | null,  // absolute URL or null

  // Classification
  category: {
    id: number,
    name: string,
    slug: string,
    icon_url: string | null,
  } | null,
  subcategories: Array<{
    id: number,
    name: string,
    slug: string,
    icon_url: string | null,
  }>,
  city: {
    id: number,
    name: string,
    slug: string,
  } | null,

  // Engagement Metrics
  rating_average: number,  // 0.0 - 5.0
  reviews_count: number,
  is_featured: boolean,
  is_favorited: boolean,
  can_review: boolean,
  review_status_message: string | null,

  // Contact Information
  phone: string | null,  // raw, not formatted
  whatsapp_url: string | null,  // normalized: https://wa.me/COUNTRY+NUMBER
  email: string | null,  // NEW: official in contract (was undocumented hack)

  // Web Presence
  website: string | null,  // absolute URL
  social_links: {
    facebook: string | null,  // absolute URL
    instagram: string | null,  // absolute URL
    linkedin: string | null,  // absolute URL
    github: string | null,  // absolute URL
  },
  map_url: string | null,  // absolute URL to maps service

  // Professional Info
  description: string | null,  // bio/about (max 500 chars)
  years_experience: number | null,  // 0-80, null if not set
  service_area_note: string | null,  // service area description
  offers_remote_work: boolean,  // false if not set

  // Content
  portfolio_items: Array<{
    id: number,
    title: string | null,
    short_description: string | null,
    description: string | null,
    link: string | null,
    images: string[],  // absolute URLs
  }>,
  credentials: Array<{
    id: number,
    title: string,
    issuer: string | null,
    issue_date: string | null,  // ISO date
    verification_url: string | null,  // absolute URL
    notes: string | null,
  }>,

  // Reviews (detail endpoint only)
  reviews: Array<{
    id: number,
    rating: number,
    comment: string | null,
    user_name: string,
    status: string,
    created_at: string,  // ISO datetime
  }>,
}
```

---

## Changes from Current API

### Added Fields

1. **provider_type** (string | null)
   - Source: `Profile.provider_type`
   - Use: Display business type to users
   - Example: "business", "freelancer", "enterprise"

2. **email** (string | null)
   - Source: `User.email`
   - Use: Display public contact email if provider allows
   - Note: Officially in API (was accessed via hack: `anyProvider.email`)

### Modified Fields

1. **whatsapp_url** (was not present)
   - Should be official part of response
   - Currently computed in API

2. **credentials** (partial resource)
   - Should use `ProviderCredentialResource::collection()`
   - Should not return raw model

---

## Implementation Steps

### 1. Update ProviderDetailResource

```php
public function toArray(Request $request): array
{
    // ... existing code ...
    
    return [
        // ... existing fields ...
        'provider_type' => $this->provider_type,
        'email' => $this->user?->email,
        'credentials' => ProviderCredentialResource::collection($this->whenLoaded('credentials')),
        // ... rest ...
    ];
}
```

### 2. Ensure Relationships Loaded

In `ProviderController::show()`:

```php
$provider = Profile::with([
    'user',
    'category',
    'subcategories',
    'city',
    'stats',
    'portfolioItems.images',
    'credentials',  // Ensure loaded
    'approvedReviews',
])->visible()->firstOrFail();
```

### 3. Create ProviderCredentialResource (if not exists)

```php
class ProviderCredentialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'issuer' => $this->issuer,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'verification_url' => $this->verification_url,
            'notes' => $this->notes,
        ];
    }
}
```

---

## Frontend Expectations

After this contract is finalized, the frontend mapper expects:

```typescript
interface Provider {
  id: number;
  slug: string;
  name: string;
  provider_type: string | null;  // NEW
  
  logo_url: string | null;
  cover_url: string | null;
  
  category: { id: number; name: string; slug: string; } | null;
  subcategories: { id: number; name: string; slug: string; }[];
  city: { id: number; name: string; slug: string; } | null;
  
  rating_average: number;
  reviews_count: number;
  is_featured: boolean;
  is_favorited: boolean;
  can_review: boolean;
  review_status_message: string | null;
  
  phone: string | null;
  whatsapp_url: string | null;
  email: string | null;  // NEW: official
  
  website: string | null;
  social_links: {
    facebook: string | null;
    instagram: string | null;
    linkedin: string | null;
    github: string | null;
  };
  map_url: string | null;
  
  description: string | null;
  years_experience: number | null;
  service_area_note: string | null;
  offers_remote_work: boolean;
  
  portfolio_items: PortfolioItem[];
  credentials: ProviderCredential[];
  reviews: Review[];
}
```

No more hacks or undocumented field access.

---

## Backwards Compatibility

**Breaking Changes:** None expected since:
- Only ADDING fields (provider_type, email)
- Only CLARIFYING existing fields
- Mobile app already handles missing fields gracefully
- Old providers won't have provider_type set (will be null)

---

## Testing

```bash
# Get provider detail
curl https://localhost:8000/api/v1/providers/provider-slug \
  -H "Accept: application/json"

# Verify response includes:
# - provider_type
# - email
# - credentials (proper resource)
```

---

## Next Phase

See: [provider-profile-ui-fallbacks.md](provider-profile-ui-fallbacks.md)
