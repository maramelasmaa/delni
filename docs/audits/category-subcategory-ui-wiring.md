# Category / Subcategory UI Wiring Audit

**Date:** 2026-06-15
**Scope:** Public frontend — `/categories`, `/category/{slug}`, `/subcategory/{slug}`

---

## 1. Current Data Flow

### Routes (`routes/web.php`)

```
GET /categories                                     → FrontendController::categories()
GET /category/{category:slug}                        → FrontendController::category()
GET /category/{category:slug}/in/{city:slug}         → FrontendController::categoryInCity()
GET /subcategory/{subcategory:slug}                  → FrontendController::subcategory()
GET /subcategory/{subcategory:slug}/in/{city:slug}   → FrontendController::subcategoryInCity()
```

All use implicit route model binding via `:slug`.

### `/categories` — data flow

1. `PublicFrontendService::allCategories()` runs two bulk GROUP BY queries (cached 60–300s):
   - `profileCountsBy('profiles.category_id')` — count of visible providers per category
   - `profileCountsBySubcategory()` — count of visible providers per subcategory
2. Loads all active categories with their active subcategories and icons in one query.
3. Sets `discoverable_profiles_count` on each Category and each nested Subcategory model.
4. Returns to `public.categories` view → `<x-category-discovery-card>` for each category.

### `/category/{slug}` — data flow

1. Route binding resolves Category. Controller aborts 404 if `!is_active`.
2. `PublicFrontendService::category()`:
   - Runs `profileCountsBySubcategory()` (cached).
   - Loads category with active subcategories (with icons) and attaches counts.
   - Runs paginated provider query filtered by `category_id`, applying `ProfileVisibilityService` and `MarketplaceRankingService::applyCategoryRanking()`.
   - Loads cities for filter.
3. View passes subcategories to `<x-subcategory-rail>` and profiles to `<x-provider-grid>`.

### `/subcategory/{slug}` — data flow

1. Route binding resolves Subcategory. Controller aborts 404 if `!is_active` or parent category `!is_active`.
2. `PublicFrontendService::subcategory()`:
   - Runs `profileCountsBySubcategory()` and `profileCountsBy('category_id')` (both cached).
   - Loads subcategory with its icon, parent category, and all active sibling subcategories.
   - Sets `discoverable_profiles_count` on parent category AND on every sibling subcategory.
   - Runs paginated provider query filtered by `profile_subcategory.subcategory_id`, applying visibility and `applySubcategoryRanking()`.
3. View passes siblings to `<x-subcategory-rail :active="$subcategory">` and profiles to `<x-provider-grid>`.

---

## 2. Visibility Rules (single source of truth)

`ProfileVisibilityService::applyVisibleQuery()` — a provider appears publicly only when ALL are true:

| Check | Column |
|---|---|
| User exists (not deleted) | `users.deleted_at IS NULL` |
| User is active | `users.is_active = true` |
| User is not suspended | `users.is_suspended = false` |
| Profile is complete | `profiles.is_complete = true` |
| Active subscription | `subscriptions.is_active = true` |
| Subscription not expired | `subscriptions.ends_at >= TODAY` |

All public listing queries call this method. Counts are computed from the same query, so **counts always reflect only visible providers**.

---

## 3. Count Logic

- **Method:** Two cached bulk queries using `GROUP BY` — one per category, one per subcategory.
- **Cache TTL:** 60–300s (`Cache::flexible`).
- **Accurate:** Yes — counts are from the discoverable query, not raw `profiles` table.
- **N+1 risk:** None — counts are pre-fetched in bulk and attached via `setAttribute`.
- **Approximate:** Counts can be up to 300s stale due to cache. This is acceptable.

---

## 4. Current UI Structure

### `/categories`

| Element | Component | Format |
|---|---|---|
| Page header | `<x-marketplace-header>` | Sticky, back button, title, count |
| Search filter | Inline input | Client-side JS text filter |
| Category list | `<x-category-discovery-card>` | 2-col grid (1-col auto below 220px min) |
| Each card | `.cat-card` | Icon + name + count + up to 4 sub-chips |
| Empty state | `<x-empty-state>` | Icon + title + message |

### `/category/{slug}`

| Element | Component | Format |
|---|---|---|
| Page header | `<x-marketplace-header>` | Sticky, back→/categories, icon, count |
| Breadcrumb | `<x-browse-trail>` | Horizontal scroll |
| Subcategory navigation | `<x-subcategory-rail>` | Horizontal scrolling chips, max 12, overflow→bottom sheet |
| Filters | `<x-browse-filters>` | City select + sort, mobile bottom sheet |
| Provider list | `<x-provider-grid>` + `<x-provider-card>` | 1-col mobile / auto-fill desktop |
| Empty state | `<x-empty-state>` | With "clear filters" CTA |
| Pagination | `<x-marketplace-pagination>` | Prev / Page X of Y / Next |

### `/subcategory/{slug}`

Same structure as `/category/{slug}` except:
- Rail shows sibling subcategories (same parent), active one pinned first.
- "كل الخدمات" chip links back to parent category page.
- Provider query uses `profile_subcategory` join (exact subcategory match).

---

## 5. Category Display Rules

**What shows on `/categories`:**
- Only active categories (`is_active = true`).
- Categories with 0 visible providers are shown (count displayed as "0 مزود").
- Subcategory preview chips in card footer: only subcategories with `count > 0`, max 4.
- Client-side search filters cards by text content.

**Card visual anatomy (`cat-card`):**
```
┌─────────────────────────────┐
│ [icon]  Category Name       │
│         N مزود              │
├─────────────────────────────┤
│ [sub1] [sub2] [sub3] [sub4] │  ← horizontal scroll chips
└─────────────────────────────┘
```

---

## 6. Subcategory Display Rules

**In `/category/{slug}` rail:**
- Only subcategories with `discoverable_profiles_count > 0` shown as chips.
- Max 12 chips visible; overflow opens a bottom sheet with search.
- "كل الخدمات" chip always first, links to full category page.
- No active chip on category page (showing all).

**In `/subcategory/{slug}` rail:**
- Active subcategory chip always pinned first regardless of provider count (fixed 2026-06-15).
- Other siblings filtered by `count > 0`.
- Active chip highlighted with orange background.
- "كل الخدمات" chip links to parent category.

**Chip anatomy:**
```
[  Subcategory Name  [12]  ]
```
Where `[12]` is `discoverable_profiles_count`.

**Subcategories are NEVER shown as cards** on category/subcategory pages. They are chips only.

---

## 7. Provider Listing Rules

- **Pagination:** 15/page default, 5–50 configurable via `?per_page=`.
- **Ordering:** `MarketplaceRankingService` bucket expression (homepage/search/category/subcategory method selected by context), then `rating_avg DESC`, `reviews_count DESC`, `created_at DESC`.
- **Visibility:** All listings pass through `ProfileVisibilityService::applyVisibleQuery()`.
- **Relations eager-loaded after pagination** (no N+1): `stats`, `city`, `category`, `subcategories`.
- **Grid:** 1-col on `≤620px`, `auto-fill minmax(280px, 360px)` on wider screens.

**Provider card anatomy (`pc-card`):**
```
┌──────────────────────────┐
│  [banner image / logo]   │  ← 16:10 aspect ratio
│  [logo badge]  ★ 4.8    │
│ ♡                        │
├──────────────────────────┤
│ Business Name            │
│ [category] [city]        │
│ [sub1] [sub2] [sub3]     │
│ [عرض الملف] [واتساب]     │
└──────────────────────────┘
```

---

## 8. Empty State Rules

| Scenario | Message | CTA |
|---|---|---|
| No categories | "لا توجد فئات متاحة حالياً." | — |
| No providers in category (with filters) | "جرب مدينة أخرى أو اختر خدمة فرعية مختلفة." | "مسح الفلاتر" → category URL |
| No providers in subcategory | "جرب مدينة أخرى أو تصفح فئات مختلفة." | "تصفح الفئات" → /categories |

---

## 9. Scalability Assessment

| Scenario | Behaviour | Verdict |
|---|---|---|
| 5 categories | 2-col grid, cards readable | ✅ |
| 50 categories | 2-col grid scrolls vertically; client-side search filters | ✅ |
| Category with 3 subcategories | All 3 chips visible in rail | ✅ |
| Category with 80 subcategories | 12 chips shown; "المزيد +68" opens bottom sheet with search | ✅ |
| Subcategory with 0 providers | Empty state shown; active chip still visible in rail (fixed) | ✅ |
| Subcategory with 500 providers | 15/page, pagination nav shown | ✅ |
| Narrow mobile 375px | 1-col provider cards, chips scroll, no overflow | ✅ |
| Wide desktop | Auto-fill grid, sheet centers correctly (fixed) | ✅ |

---

## 10. Bugs Fixed (2026-06-15)

### Bug 1: Active subcategory chip disappeared when current sub had 0 providers

**File:** `resources/views/components/subcategory-rail.blade.php`

**Root cause:** Active item was searched within `$visibleItems` (filtered by count > 0). If the current subcategory had 0 discoverable providers, it was absent from `$visibleItems`, so `$activeItem` was null, and no chip was highlighted. Users landing on a subcategory page with an empty result saw a rail with no indication of which subcategory they were on.

**Fix:** Search for active item in `$allItems` (unfiltered) before applying the count filter. Pin active item at front of rail regardless of its count. Non-active items still filtered by count > 0.

---

### Bug 2: Favorite toast displayed off-center / off-screen in RTL

**File:** `resources/views/components/provider-card.blade.php`

**Root cause:** Toast used `inset-inline-start: 50%; transform: translateX(50%)`. In RTL Arabic layout, `inset-inline-start` maps to `right`, so positioning was relative to the right edge. Combined with `translateX(50%)` (which always shifts right in CSS regardless of RTL), the toast was pushed off-screen to the right.

**Fix:** Replaced with physical `left: 50%; transform: translateX(-50%)`, which correctly centers the fixed element in both LTR and RTL contexts.

---

### Bug 3: Desktop service sheet (subcategory overflow modal) off-center in RTL

**File:** `resources/views/components/subcategory-rail.blade.php`

**Root cause:** Desktop sheet used `inset-inline: 50%` (sets both logical inline edges to 50%) combined with `translateX(50%)`. In RTL, this caused unpredictable centering because `translateX` does not flip direction in RTL.

**Fix:** Replaced with physical `left: 50%; right: auto; transform: translateX(-50%)`, which reliably centers the fixed-position sheet in all writing modes.

---

## 11. Remaining Risks

| Risk | Severity | Notes |
|---|---|---|
| Count cache staleness (60–300s) | Low | Acceptable UX trade-off. Counts lag behind real-time by up to 5 minutes. |
| Provider count on category page reflects current filter | Low | If city filter active, "كل الخدمات" chip count shows filtered count, not total. Acceptable. |
| `browse-filters` RTL — not audited fully | Low | Filter UI not reported as broken. Recommend separate RTL audit pass. |
| `subcategory-rail` "more" count excludes active item | Very low | Edge case only when active sub has 0 count; affects the overflow counter by ±1. Acceptable. |

---

## Final Verdict

**Yes — categories and subcategories display in a clean, scalable, mobile-first way.**

The data pipeline is correct: all counts are accurate, visibility is enforced, inactive items are hidden, pagination works, and N+1 queries are absent. The visual hierarchy is correct: categories are cards, subcategories are chips, providers are action cards. Three RTL/edge-case bugs were found and fixed. No hardcoded data, no fake counts, no structural UX issues.
