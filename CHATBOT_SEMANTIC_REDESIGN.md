# Delni Chatbot Semantic Redesign

## Overview

The Delni chatbot has been completely reengineered from a rigid category-only form wizard into a **semantic AI-assisted marketplace search system** that understands natural language, recognizes entity names, and performs intelligent multi-layer searches.

### Old Behavior ❌
```
User: "فني زياد"
Bot:  "ما فهمت طلبك بوضوح"
```

### New Behavior ✅
```
User: "فني زياد"
Bot:  [finds provider named "زياد" in technician category]
      "لقيتلك مقدمي خدمة بـ خدمة فني واسم زياد:"
```

---

## Architecture

### Core Philosophy

**Search First, Ask Second**

The chatbot now:
1. Attempts intelligent semantic search immediately
2. Only asks clarifying questions if the search is insufficient
3. Uses DeepSeek for natural language understanding
4. Leverages Laravel as the database truth layer

### Key Components

#### 1. **IntentExtractionService** (NEW)
Deep semantic analysis of user messages using DeepSeek.

**Extracts:**
- Possible provider names (e.g., "زياد")
- Business entity names (e.g., "شركة المدار")
- Service/category hints (e.g., "فني", "محامي")
- City references
- Search confidence score
- Whether to prioritize search

**Example extraction:**
```php
$extraction = $intentExtraction->extract('فني زياد بنغازي');
// Returns:
[
    'provider_name_query' => 'زياد',
    'service_query' => 'فني',
    'city' => 'بنغازي',
    'search_mode' => 'mixed',
    'should_search_first' => true,
    'confidence' => 0.92,
]
```

#### 2. **Multi-Layer ProviderSearchForChatService** (ENHANCED)
Semantic search with fallback strategy.

**Search Priority:**
1. Exact provider name match
2. Exact business name match
3. Mixed entity + service search
4. Service-only category search
5. Fallback searches

**New Methods:**
- `searchSemantic()` - Smart multi-layer search
- `searchByProviderName()` - Search users by name
- `searchByBusinessName()` - Search by business name
- `searchProviderEntity()` - Combined entity + service search
- `searchByService()` - Category/service search

**Example:**
```php
// Find providers matching "زياد" in any capacity
$results = $providerSearch->searchSemantic(
    providerNameQuery: 'زياد',
    businessNameQuery: null,
    serviceQuery: 'فني',
    cityId: $benghaziId,
    categoryHint: $technicianCategoryId,
);
```

#### 3. **Updated ChatOrchestratorService** (REFACTORED)
New search-first orchestration logic.

**Key Changes:**
- Uses `IntentExtractionService` for entity detection
- Calls `searchSemantic()` immediately if confident
- Only asks questions for missing critical information
- Maintains conversation state across turns
- No hallucination - only returns DB providers

**New Flow:**
```
User message
    ↓
Safety validation
    ↓
Semantic extraction (DeepSeek)
    ↓
Aggressive semantic search
    ├─ Found results? → Return immediately
    └─ No results? → Ask clarification or city
        ↓
City provided → Search with city filter
```

---

## Search Modes Supported

The chatbot now supports comprehensive search modes:

| Mode | Example | Behavior |
|------|---------|----------|
| **provider_name** | "زياد" | Search by provider name across all providers |
| **business_name** | "شركة المدار" | Search by business name |
| **mixed** | "فني زياد" | Combine entity name + service hint |
| **category** | "محامي" | Search by service type |
| **semantic** | "مصور أفراح اسمه محمد" | Deep semantic understanding |
| **city_only** | "بنغازي" | Set city context for follow-up |
| **conversational_followup** | (responding to pending question) | Answer pending question |

---

## Search Fields & Matching

Providers can now be found across multiple fields with intelligent matching:

### Provider Search Fields
- User name (محمد, أحمد, زياد, etc.)
- Business name (شركة المدار, دار الفن, etc.)
- Profile slug
- Bio/description
- Category specialization
- Subcategory tags

### Matching Strategy
- **Exact match:** Priority 1 (e.g., name is exactly "زياد")
- **Partial match:** Priority 2 (e.g., name contains "زياد")
- **Semantic match:** Priority 3 (bio mentions service, category aligns)
- **Fallback:** Expand search by removing filters

### Example Search Logic
```
Query: "فني زياد"
    ├─ Exact provider name match? ("زياد")
    │  └─ Yes → Return (Priority 1)
    ├─ Partial provider name? ("زياد" in name)
    │  └─ Yes + category matches → Return (Priority 2)
    ├─ Service match with name hint?
    │  └─ Yes → Return (Priority 3)
    └─ Category "technician" only
       └─ Return all technicians
```

---

## Test Coverage

All 25 tests pass, covering:

### Basic Tests (1-15)
- Service term extraction
- City resolution and transliteration
- State management
- Decision logic
- API response validation

### Semantic Search Tests (16-25)
✅ **Test 16:** Search by provider name ("زياد")
✅ **Test 17:** Search by business name ("شركة المدار")
✅ **Test 18:** Mixed entity + service ("مصور محمد")
✅ **Test 19:** Service-only search ("محامي")
✅ **Test 20:** Visibility rules (hidden providers never returned)
✅ **Test 21:** Multi-layer exact match priority
✅ **Test 22:** Fallback to service search
✅ **Test 23:** City filter enforcement
✅ **Test 24:** No hallucination (empty results return empty)
✅ **Test 25:** Partial name matching ("زيا" matches "زياد")

---

## Key Guarantees

### No Hallucination
- Only DB providers are returned
- DeepSeek extracts, doesn't invent
- If no match found, say so clearly
- Example: "ما لقيناش نتيجة مطابقة حالياً."

### Visibility Always Enforced
- Hidden/inactive providers never appear
- Suspended providers never appear
- Expired subscriptions filter out
- All via `Profile::visible()` scope

### Conversation State Management
- City context persists across turns
- "Pending field" system for clarifications
- One hour cache for conversation state
- Multi-turn "which city?" flows work correctly

### Confidence-Based Decisions
- Low confidence (< 0.3) triggers clarification
- Medium confidence proceeds with partial search
- High confidence returns results immediately
- DeepSeek confidence scoring prevents over-searching

---

## User Experience Examples

### Example 1: Provider Name Search
```
User:  "فني زياد"
Bot:   [IntentExtraction: provider="زياد", service="فني", confidence=0.92]
       [searchSemantic finds provider]
       "لقيتلك مقدمي خدمة في تخصص الفنيين:"
       [Shows providers including "زياد"]
```

### Example 2: Business Name Search
```
User:  "شركة المدار"
Bot:   [IntentExtraction: business="المدار", confidence=0.95]
       [searchByBusinessName finds exact match]
       "لقيتلك الشركة:"
       [Shows شركة المدار profile]
```

### Example 3: Mixed Search with City
```
User:  "مصور محمد في بنغازي"
Bot:   [IntentExtraction: provider="محمد", service="تصوير", city="بنغازي", confidence=0.88]
       [searchProviderEntity with city filter]
       "لقيتلك مصورين اسمهم محمد في بنغازي:"
       [Shows matching photographers]
```

### Example 4: Service-Only with City Question
```
User:  "محامي"
Bot:   [IntentExtraction: service="محامي", no_city, confidence=0.85]
       [No results without city context, ask for it]
       "في أي مدينة تبحث عن محامي؟"

User:  "طرابلس"
Bot:   [searchByService with city filter]
       "لقيتلك محامين في طرابلس:"
       [Shows lawyers]
```

---

## Database Integrity

### Visibility Scope
The `Profile::visible()` scope enforces:
```php
// Only returned if:
- User is active (is_active = true)
- User not suspended (is_suspended = false)
- Profile is complete (is_complete = true)
- User has active subscription (ends_at >= today)
```

### No N+1 Queries
All semantic searches use eager loading:
```php
->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
```

### Ranking Applied
All searches respect marketplace ranking rules via `MarketplaceRankingService`.

---

## Migration Guide

### For Developers
The new system is **backwards compatible**:
- Old `search()` method still works
- Old `DeepSeekProviderMatcher` still available
- Gradual migration encouraged

### Recommended Updates
1. Replace `DeepSeekProviderMatcher::match()` with `IntentExtractionService`
2. Use `searchSemantic()` instead of category-only searches
3. Test flows with multi-turn conversations
4. Monitor confidence scores to tune thresholds

### No Breaking Changes
- All existing routes work unchanged
- ChatOrchestratorService backward compatible
- Response format identical
- No database schema changes needed

---

## Performance Considerations

### DeepSeek API Calls
- 1 call per user message (extraction)
- Cached across conversation (1 hour TTL)
- Graceful fallback if API unavailable
- No impact on search performance

### Database Queries
- Each `searchSemantic()` makes 1-3 queries:
  - Try exact name match (1 query)
  - Try partial match (1 query)
  - Try fallback (1 query)
- Eager loading prevents N+1
- Indexed columns: name, business_name, slug

### Recommended Indexes
```sql
-- Already present in migrations
- profiles(user_id)
- profiles(business_name)
- profiles(slug)
- profiles(city_id)
- profiles(category_id)
- users(name)  -- Add if not present
```

---

## Configuration

No new configuration required. Settings:
- DeepSeek API: uses existing `DEEPSEEK_*` env vars
- Conversation TTL: 1 hour (configurable via code)
- Confidence threshold: 0.3 (configurable via code)

---

## Future Enhancements

Possible future improvements:
1. Learning from user behavior to improve search weights
2. Provider alias support ("زياد المصور" = "زياد")
3. Advanced filters (experience, ratings, portfolio)
4. Specialized search for "nearby" providers (location-aware)
5. Conversation analysis for intent refinement

---

## File Changes

### New Files
- `app/Services/Chatbot/IntentExtractionService.php`

### Modified Files
- `app/Services/Chatbot/ProviderSearchForChatService.php` (+5 new search methods)
- `app/Services/Chatbot/ChatOrchestratorService.php` (new search-first flow)
- `app/Providers/ChatbotServiceProvider.php` (register IntentExtractionService)
- `tests/Feature/ChatbotFlowTest.php` (+10 semantic search tests)

### Code Formatting
- All files passed `vendor/bin/pint`
- PSR-12 compliance verified

---

## Final Verdict

**The Delni chatbot now feels like a smart human assistant**, not a form wizard.

Users feel: **"It understands what I mean"** ✅
NOT: **"It is a rigid form"** ❌

Key achievement: **Semantic entity marketplace search with DB as single source of truth**
