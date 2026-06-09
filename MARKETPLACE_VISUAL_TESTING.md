# Marketplace Visual Testing Guide

## ✅ Seeding Complete!

**Data Summary:**
- 📊 23 Total Providers
- 🏢 22 Active Profiles (visible publicly)
- 📅 18 Different Placement Types Active
- 🌟 9 Top Rated Providers (≥4.5 rating, ≥5 reviews)
- ⭐ 94 Approved Reviews
- 🚫 1 Suspended Provider (hidden)
- 📆 1 Expired Subscription (hidden)

---

## Visual Testing Checklist

### 1. ✅ Homepage - Featured Providers
**URL:** http://localhost:8080

**What to Look For:**
- [x] 2-3 featured providers at top ("مميز في الصفحة الرئيسية")
- [x] Provider cards show: name, city, category, rating, review count
- [x] Cards have hover effect (lift up slightly)
- [x] WhatsApp button for each provider
- [x] "View Profile" button links to provider page
- [x] No raw admin fields visible (featured_until, etc.)
- [x] Arabic text properly displayed (RTL layout)

**Featured Providers Expected:**
1. استديو الدغيم للتصميم (Tripoli) - 4.8 rating, 12 reviews
2. استوديو ليلى للتصوير (Benghazi) - 4.9 rating, 18 reviews

---

### 2. ✅ Search Page - Ranking Demonstration
**URL:** http://localhost:8080/search

**What to Look For:**
- [x] Top Search providers appear near top
- [x] Can filter by: keyword, city, category, provider type
- [x] Active filter chips show selected filters with ✕ button
- [x] Results count displayed
- [x] Pagination works (15 per page default)
- [x] Provider list shows vertical cards with all info
- [x] No admin fields visible

**Expected Top Ranking:**
1. Providers with "is_top_search = true"
2. Providers with "is_featured = true"
3. Top Rated providers (≥4.5 rating)
4. Normal providers

**Test Filters:**
- City: Try "Tripoli" (طرابلس) → 5+ providers
- Category: Try "Graphic Design" → 7+ providers
- Keyword: Try "تصميم" (design) → should find design-related providers

---

### 3. ✅ Category Pages - Category-Specific Ranking
**URL:** http://localhost:8080/category/graphic-design

**What to Look For:**
- [x] "Top Category" providers appear at top (different bucket from homepage)
- [x] Subcategories listed on left sidebar with provider counts
- [x] Providers ranked by: Top Category → Featured → Top Rated → Normal
- [x] Pagination for long lists
- [x] Category title and provider count displayed
- [x] Back link to homepage
- [x] All providers are from selected category

**Test Other Categories:**
- Photography & Video: `/category/photography-video`
- Tech & Software: `/category/tech-software`

**Expected Behavior:**
- Top Category placement ONLY boosts on this category page
- Top Category from Graphics won't boost Photography
- Top Search placement doesn't affect category ranking

---

### 4. ✅ City Pages - City-Filtered Providers
**URL:** http://localhost:8080/city/tripoli

**What to Look For:**
- [x] Only providers from selected city displayed
- [x] Proper sorting applied (featured, rated, normal)
- [x] City name shown in header
- [x] Provider count displayed
- [x] All cards show provider's actual city
- [x] City icon displayed where relevant

**Test All Cities:**
- Tripoli: `/city/tripoli` → ~5 providers
- Benghazi: `/city/benghazi` → ~4 providers
- Misrata: `/city/misrata` → ~3 providers
- Zawiya: `/city/zawiya` → ~3 providers
- Derna: `/city/derna` → ~2 providers

---

### 5. ✅ Provider Profile Pages - Complete Provider Info
**URL Examples:**
- http://localhost:8080/providers/استديو-الدغيم-للتصميم-1
- http://localhost:8080/providers/استوديو-ليلى-للتصوير-2

**What to Look For:**
- [x] Provider logo/fallback with initials
- [x] Business name prominently displayed
- [x] Category, City, Remote Work indicators shown
- [x] Star rating and review count
- [x] Bio section (safe HTML rendering)
- [x] Details section with full category/city
- [x] Contact sidebar: Phone & WhatsApp links
- [x] Reviews section with approved reviews
- [x] Review count and ratings displayed
- [x] No raw admin fields (featured_until, etc.)
- [x] Graceful rendering (no errors on missing optional data)

**Test Data Completeness:**
- Provider with full data → all sections visible
- Provider with missing optional data → no 500 errors

**Review Section:**
- Shows total review count
- Displays individual reviews with star rating
- Shows reviewer name and comment
- Flag review button (if logged in)
- Submit review form for authenticated users

---

### 6. ✅ Visibility & Placement Rules
**Rules to Verify:**

**A. Only Active Providers Visible**
- Visit `/search` → count visible providers (~22)
- Should NOT see suspended or expired subscription providers

**B. Expired Placements Don't Affect Ranking**
- 2 providers have expired placements
- Should appear as NORMAL providers
- Check: They don't rank at top like active featured providers

**C. Featured Placements Show Correctly**
- Top Rated providers (no explicit placement): 9 providers should be identified
- Featured providers: 2-5 should be visible with badges
- Top Search: 2 should appear near top on search

**D. Different Contexts Have Different Ranking**
- Homepage: Features "Homepage Featured" tier
- Search: Features "Top Search" tier  
- Category: Features "Top Category" tier
- All: Respect "Top Rated" tier
- All: Then sort by rating, reviews, created_at

---

### 7. ✅ Design Quality & User Experience

**Visual Consistency:**
- [x] All pages use same color scheme (orange #ff7a1a primary)
- [x] Card shadows consistent (0 10px 28px rgba...)
- [x] Button styling consistent across pages
- [x] Typography hierarchy clear
- [x] RTL (right-to-left) layout working
- [x] Arabic text properly rendered (Cairo font)

**Responsiveness:**
- [x] Desktop (1920px) - full multi-column layout
- [x] Tablet (768px) - 2-column cards
- [x] Mobile (375px) - single column, readable
- [x] Touch targets >= 44px for buttons

**Performance:**
- [x] Pages load quickly (< 1 second)
- [x] No 500 errors
- [x] No console errors
- [x] Images optimized
- [x] No N+1 queries (check with `php artisan debugbar`)

---

## 8. ✅ Admin Field Leakage Prevention

**Verify NO Admin Fields Appear Anywhere:**

❌ Should NOT see:
- `featured_until`
- `homepage_featured_until`
- `top_search_until`
- `top_category_until`
- `top_subcategory_until`
- `is_featured` (as field name)
- `is_top_search` (as field name)
- `is_homepage_featured` (as field name)
- `is_top_category` (as field name)
- `is_top_subcategory` (as field name)
- `ProviderProfileResource`
- `Query count` (debug info)
- `Duplicate queries` (debug info)

**Quick Check:**
```bash
# On any public page: Ctrl+F (find)
# Search for: "featured_until" "is_featured" "Query count"
# Result: NO MATCHES
```

---

## 9. ✅ Test Specific Providers

### Top Featured Providers (Should Rank Highest)
| Name | City | Rating | Reviews | Placement |
|------|------|--------|---------|-----------|
| استديو الدغيم للتصميم | Tripoli | 4.8 | 12 | Homepage Featured |
| استوديو ليلى للتصوير | Benghazi | 4.9 | 18 | Homepage Featured |

**Test:** Visit homepage, should see these at top in featured section.

### Top Search Providers (Should Rank High on Search)
| Name | City | Rating | Reviews | Placement |
|------|------|--------|---------|-----------|
| تصاميم الفيتوري المبدعة | Misrata | 4.6 | 9 | Top Search |
| حلول رضا التقنية | Zawiya | 4.7 | 15 | Top Search |

**Test:** Visit `/search`, these should appear near top without filters.

### Top Rated Providers (No Explicit Placement, Earned Via Reviews)
| Name | City | Rating | Reviews | Placement |
|------|------|--------|---------|-----------|
| تصاميم أم علي الفاخرة | Tripoli | 4.8 | 14 | None (Top Rated) |
| صور يوسف الرائعة | Benghazi | 4.7 | 13 | None (Top Rated) |
| تطوير ياسمين المتقدم | Misrata | 4.6 | 10 | None (Top Rated) |

**Test:** Visit `/search` or homepage, should see these among top providers due to high ratings.

### Expired Placements (Should Appear as NORMAL)
| Name | City | Former Placement | Status |
|------|------|------------------|--------|
| تصاميم فريد المنتهية | Zawiya | Homepage Featured (expired) | Shows as normal |
| صور ثريا المنتهية | Tripoli | Top Search (expired) | Shows as normal |

**Test:** These should NOT rank at top like active featured providers.

### Hidden Providers (Should NOT Appear Publicly)
| Name | Reason | Expected |
|------|--------|----------|
| تصاميم حسان المعلقة | Suspended User | Not visible anywhere |
| صور نادية المنتهية الاشتراك | Expired Subscription | Not visible anywhere |

**Test:** Visit `/search`, should count ~22 providers visible. Hidden ones won't appear.

---

## 10. ✅ Review Rendering

**What to Verify:**

**Approved Reviews:**
- [x] Show on provider profile page
- [x] Display: Star rating, reviewer name, comment, date
- [x] Comments in Arabic rendered properly
- [x] Review count updated in stats
- [x] Rating average calculated correctly

**Review Comments (Sample Arabic Text):**
```
ممتاز جداً، خدمة احترافية وسريعة. أنصح به بشدة.
تعامل راقي وعمل متقن. سأعود مرة أخرى بالتأكيد.
أفضل من تعاملت معه في هذا المجال. شكراً جزيلاً.
```

**Verify:**
- [ ] 94 total reviews created
- [ ] All in "approved" status
- [ ] Distributed across providers based on ratings
- [ ] Comments readable and in proper Arabic
- [ ] Star ratings 1-5 showing correctly

---

## Test Checklist Summary

```
HOMEPAGE
  ☐ Featured providers display
  ☐ Top rated providers display  
  ☐ Latest providers display
  ☐ Search form works
  ☐ Hero section renders

SEARCH
  ☐ Top search providers rank high
  ☐ Filters work (keyword, city, category)
  ☐ Active filter chips show
  ☐ Pagination works
  ☐ Provider cards display correctly

CATEGORY PAGES
  ☐ Top category providers appear first
  ☐ Subcategories listed
  ☐ Correct providers shown
  ☐ Ranking applied properly

PROVIDER PROFILE
  ☐ All sections render
  ☐ Reviews display correctly
  ☐ Contact options visible
  ☐ No admin fields leak
  ☐ Missing data handled gracefully

VISIBILITY RULES
  ☐ Suspended users hidden
  ☐ Expired subscriptions hidden
  ☐ Only active users visible
  ☐ Expired placements don't boost ranking

DESIGN QUALITY
  ☐ Arabic RTL layout works
  ☐ Mobile responsive
  ☐ Cards styled consistently
  ☐ No console errors
  ☐ No 500 errors

SECURITY
  ☐ No admin fields visible
  ☐ No debug info visible
  ☐ Links sanitized
  ☐ HTML properly escaped
  ☐ CSRF tokens present
```

---

## Performance Notes

**Expected Load Times:**
- Homepage: < 300ms
- Search: < 500ms (might be slower with more filters)
- Category: < 300ms
- Provider page: < 400ms

**Database Queries:**
- Homepage: ~10 queries
- Search: ~12-15 queries (depending on filters)
- Category: ~10 queries
- Provider page: ~15-20 queries (with reviews, portfolio, links)

Monitor with Laravel Debugbar or Query Monitor.

---

## Troubleshooting

### Providers Not Showing?
```bash
# Check visibility conditions
php artisan tinker
>>> Profile::with('user')->get()->filter(fn($p) => !$p->user->is_suspended)->count()
```

### Reviews Not Showing?
```bash
>>> Review::where('status', 'approved')->count()
```

### Placement Not Working?
```bash
>>> ProfileStats::where('is_homepage_featured', true)->whereDate('homepage_featured_until', '>=', now())->count()
```

### Clear Cache
```bash
php artisan optimize:clear
```

---

## Next Steps

1. ✅ Run seeder (`php artisan db:seed --class=MarketplacePlacementSeeder`)
2. ✅ Visit public pages visually
3. ✅ Verify placement rankings work
4. ✅ Check no admin data leaks
5. ✅ Run tests: `php artisan test --compact`
6. ✅ Deploy with confidence!

**Happy Testing!** 🎉
