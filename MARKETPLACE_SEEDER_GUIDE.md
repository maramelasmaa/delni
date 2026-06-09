# Marketplace Placement Seeder Guide

## Quick Start

### 1. Run the Seeder

```bash
# Fresh database with marketplace data
php artisan migrate:fresh --seed

# Or just seed marketplace data to existing database
php artisan db:seed --class=MarketplacePlacementSeeder
```

When running `php artisan migrate:fresh --seed`, you'll be prompted:
```
Seed marketplace placement test data? (for testing ranking system) (yes/no) [yes]:
```
Press Enter or type `yes` to seed test providers.

---

## What Gets Seeded

### Cities (5)
- 🏙️ Tripoli (طرابلس)
- 🏙️ Benghazi (بنغازي)
- 🏙️ Misrata (مصراتة)
- 🏙️ Zawiya (الزاوية)
- 🏙️ Derna (درنة)

### Categories (3)
- 🎨 Graphic Design (تصميم)
- 📸 Photography & Video (تصوير)
- 💻 Tech & Software (تطوير ويب)

### Providers (18 total)

#### Placement Types

**1. Homepage Featured (مميز في الصفحة الرئيسية)** [2 providers]
- Appears at top of homepage
- Active until ~3 months from now
- High ratings (4.8-4.9)

**2. Top Search (أعلى البحث)** [2 providers]
- Ranks above normal on `/search`
- Active until ~1-2 months from now
- Strong ratings (4.6-4.7)

**3. Top Category (أعلى التصنيف)** [2 providers]
- Ranks high on `/category/{slug}`
- Active until ~1-3 months from now
- Good ratings (4.4-4.5)

**4. Top Subcategory (أعلى الفئة الفرعية)** [1 provider]
- Ranks high on subcategory pages
- Active until ~2 months from now
- Rating 4.3

**5. Featured (مميز)** [2 providers]
- Basic featured status
- Active until ~1-3 months from now
- Ratings 4.1-4.2

**6. Top Rated (الأعلى تقييماً)** [3 providers]
- Achieved through reviews (≥5 reviews, ≥4.5 rating)
- No explicit placement needed
- Ratings 4.6-4.8

**7. Normal Providers (عادي)** [4 providers]
- No placements
- Ratings 2.5-3.8
- 1-4 reviews each

**8. Expired Placements** [2 providers]
- Placements ended (past dates)
- Should appear as NORMAL on public pages
- Ratings 3.9-4.0

**9. Inactive/Hidden** [2 providers]
- 1 Suspended user (not visible publicly)
- 1 Expired subscription (not visible publicly)

---

## Testing the Marketplace

### 1. View Homepage Featured
```
http://localhost:8080
```
Should show featured providers at the top.

### 2. Test Search Ranking
```
http://localhost:8080/search
```
Top search providers should appear near the top.

### 3. Category Ranking
```
http://localhost:8080/category/graphic-design
http://localhost:8080/category/photography-video
http://localhost:8080/category/tech-software
```
Providers should be ranked by placement tier then by rating.

### 4. Individual Provider Page
```
http://localhost:8080/providers/استديو-الدغيم-للتصميم-1
```
Full provider profiles with reviews, portfolio, links.

---

## Verify Seeded Data

### Check Providers Across Cities

```bash
# View providers by city
php artisan tinker
>>> Profile::with('user', 'city', 'stats')->get()->map(fn($p) => [
    'name' => $p->business_name,
    'city' => $p->city->name,
    'featured' => $p->stats->is_homepage_featured ? '⭐' : '',
    'rating' => $p->stats->rating_avg,
    'reviews' => $p->stats->reviews_count,
]);
```

### Check Placement Types Active

```bash
php artisan tinker

# Homepage featured
>>> ProfileStats::where('is_homepage_featured', true)->whereDate('homepage_featured_until', '>=', now())->count();

# Top search
>>> ProfileStats::where('is_top_search', true)->whereDate('top_search_until', '>=', now())->count();

# Top rated
>>> ProfileStats::where('is_top_rated', true)->count();

# All reviews
>>> Review::where('status', 'approved')->count();
```

### Check Visibility Rules

```bash
php artisan tinker

# Active profiles (should be visible)
>>> Profile::whereHas('user', fn($q) => $q->where('is_active', true)->where('is_suspended', false))->count();

# Suspended users (should NOT be visible)
>>> User::role('provider')->where('is_suspended', true)->count();

# Expired subscriptions (should NOT be visible)
>>> Subscription::whereDate('ends_at', '<', now())->count();
```

---

## Provider Data Included

Each seeded provider includes:
- ✅ User account (active/suspended)
- ✅ Profile (complete with Arabic names/bios)
- ✅ City and category assignments
- ✅ Subcategory assignments
- ✅ Experience years (2-20)
- ✅ Contact info (WhatsApp, phone)
- ✅ Placement status (featured, top search, etc.)
- ✅ Rating and review count
- ✅ Approved reviews (with Arabic comments)
- ✅ Active subscription (monthly or yearly)

---

## Rating Distribution

Providers are seeded with realistic rating distributions:

| Rating | Count | Type |
|--------|-------|------|
| 4.6+ | 8 | Top-rated, featured providers |
| 4.0-4.5 | 6 | Category-ranked providers |
| 3.0-3.9 | 4 | Normal providers |
| < 3.0 | 1 | Low-rated provider |

---

## Database State

### Providers by Status

```
✅ Active & Visible:        14 providers
⏰ Suspended (hidden):      1 provider
📅 Expired Subscription:    1 provider
🎯 Total:                   18 providers
```

### Placements Status

| Placement Type | Count | Status |
|---|---|---|
| Homepage Featured | 2 | Active |
| Top Search | 2 | Active |
| Top Category | 2 | Active |
| Top Subcategory | 1 | Active |
| Featured | 2 | Active |
| Top Rated | 3 | Active (via reviews) |
| Normal | 4 | No placement |
| Expired | 2 | Inactive (show as normal) |

---

## Testing Placement Logic

### Expected Behavior

#### Homepage (`/`)
1. Homepage Featured providers at top
2. Top Rated providers
3. Latest providers

#### Search (`/search`)
1. Top Search providers at top
2. Featured providers
3. Top Rated providers
4. Normal providers

#### Category (`/category/graphic-design`)
1. Top Category providers at top
2. Featured providers
3. Top Rated providers
4. Normal providers

#### Individual Page (`/providers/{slug}`)
- Shows full profile with reviews
- Never shows raw admin fields (featured_until, etc.)
- Shows proper badges only
- Handles missing optional data gracefully

---

## Cleanup

### Reset Database
```bash
php artisan migrate:fresh

# Or reset and reseed
php artisan migrate:fresh --seed
```

### Clear Cache
```bash
php artisan optimize:clear
```

---

## Troubleshooting

### Providers Not Showing Publicly?
Check visibility conditions:
- User is_active = true
- User is_suspended = false
- Profile is_complete = true
- Subscription ends_at >= today

```bash
php artisan tinker
>>> app('App\Services\ProfileVisibilityService')->evaluate($profile)
```

### Placements Not Working?
Verify placement dates are in the future:

```bash
php artisan tinker
>>> ProfileStats::where('is_homepage_featured', true)->get(['homepage_featured_until']);
```

### Reviews Not Showing?
Ensure reviews have status = 'approved':

```bash
php artisan tinker
>>> Review::where('profile_id', $profileId)->get(['status', 'rating', 'comment']);
```

---

## Sample Queries

### Get Featured Providers Across Cities
```php
$providers = Profile::query()
    ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
    ->where('profile_stats.is_homepage_featured', true)
    ->whereDate('profile_stats.homepage_featured_until', '>=', now())
    ->with(['city', 'category', 'stats', 'user'])
    ->orderBy('profiles.created_at', 'desc')
    ->get();
```

### Get Top Rated Providers
```php
$topRated = Profile::query()
    ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
    ->where('profile_stats.is_top_rated', true)
    ->with(['city', 'stats', 'approvedReviews'])
    ->orderByDesc('profile_stats.rating_avg')
    ->get();
```

### Get Providers by City
```php
$tripoliProviders = Profile::whereCityId($tripoli->id)
    ->with(['category', 'stats', 'user'])
    ->get();
```

---

## Notes

- All seeded data uses realistic Arabic names and business names
- Bios are in Arabic describing the service
- Emails follow pattern: `firstname.lastname@example.ly`
- Default password for all accounts: `Demo@1234`
- Subscription dates are current/future (except expired ones)
- Reviews are created only for active, visible providers
- Suspended/expired providers have no reviews to avoid confusion

---

## Next Steps

After seeding:
1. ✅ Visit `/` to see featured providers
2. ✅ Test search filters at `/search`
3. ✅ Verify category ranking
4. ✅ Check provider profiles
5. ✅ Run tests: `php artisan test`
6. ✅ Verify no admin data leaks to public pages

Enjoy testing the marketplace! 🎉
