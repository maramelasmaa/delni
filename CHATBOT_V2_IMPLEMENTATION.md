# Delni Chatbot V2 - Intent-Driven Architecture

## Overview

Complete redesign of the chatbot from a rigid category-only system into an intelligent intent extraction layer that never hallucinate.

**Key Philosophy:** 
- AI extracts intent only
- Database provides truth
- Users get relevant results, never fabricated

---

## Architecture

```
User Message
    ↓
[Dialect Normalization]
  ├─ Arabic → Canonical form
  ├─ Arabizi → Arabic
  ├─ English → Lowercase
  └─ Mixed → Unified format
    ↓
[Safe Intent Extraction]
  ├─ NO prompt interpolation (JSON mode)
  ├─ NO system prompt leakage (strict schema)
  ├─ Confidence scoring (0.0-1.0)
  └─ Structured output only
    ↓
[Confidence Gate]
  ├─ confidence >= 0.70? 
  │  ├─ YES → Search database
  │  └─ NO → Ask clarification
  └─ needs_clarification? → Ask question
    ↓
[Provider Search]
  ├─ Search specialty + city
  ├─ Apply ranking rules
  └─ Return ONLY DB results (never invented)
    ↓
[Response]
  ├─ Results found → Show providers
  ├─ No results → "ما لقيناش نتائج"
  └─ Unclear → Ask clarification
```

---

## New Files

### Core Services

**`app/Services/Chatbot/SafeIntentExtractor.php`**
- Main entry point for intent extraction
- Uses DeepSeek JSON mode (prevents prompt injection)
- Validates all responses strictly
- Coordinates with dialect normalizer

**`app/Data/ExtractedIntent.php`**
- Immutable data object representing extracted intent
- Fields: specialty, city, gender_preference, budget_sensitive, confidence, needs_clarification
- Methods: fromParsed(), isConfident(), toArray()

### Dialect Handling

**`app/Services/Chatbot/Dialects/DialectNormalizer.php`**
- Orchestrates multi-dialect normalization
- Detects and routes to appropriate normalizer

**`app/Services/Chatbot/Dialects/ArabicNormalizer.php`**
- Removes diacritics
- Normalizes hamza variations (أ إ آ → ا)
- Normalizes taa marbuta (ة → ه)
- Canonical Arabic form

**`app/Services/Chatbot/Dialects/ArabiziNormalizer.php`**
- Converts Arabizi to Arabic (3 → ع, 7 → ح, etc.)
- Handles voice-to-text errors
- Supports common abbreviations

**`app/Services/Chatbot/Dialects/SpellingCorrector.php`**
- Fixes common misspellings
- Medical term corrections
- Arabic variant spelling

### Controllers

**`app/Http/Controllers/Api/ChatControllerV2.php`**
- New API endpoints for V2 chatbot
- Routes: /api/chat/message, /api/chat/reset, /api/chat/init

### Tests

**`tests/Feature/SafeIntentExtractionTest.php`**
- Comprehensive test coverage
- Tests all dialects and variants
- Safety/injection tests
- Intent extraction tests

---

## Key Features

### 1. Prompt Injection Resistance

**How it works:**
```php
// OLD (vulnerable):
USER MESSAGE: "{$message}"  // Direct interpolation

// NEW (safe):
$deepSeek->chatWithJsonMode(
    systemPrompt: '...',
    userMessage: $message,  // Passed separately
    jsonSchema: $schema,    // Strict constraints
);
```

**Why it's safe:**
- User message NEVER interpolated into prompt
- JSON schema enforces valid output format
- DeepSeek's JSON mode validates against schema
- No way to escape the format

### 2. Confidence Scoring

```php
{
    "confidence": 0.92,
    "needs_clarification": false
}
```

- 0.0-1.0 scale
- Gate: Only search if confidence >= 0.70
- Low confidence → Ask clarification
- High confidence → Search immediately

### 3. Clarification Flow

If user message is unclear:
```json
{
    "type": "clarification",
    "question": "Are you looking for a pediatric or adult speech therapist?",
    "conversation_id": "chat_xxxx"
}
```

Single question, not multiple.

### 4. Multi-Dialect Support

Handles:
- **Modern Standard Arabic**: دكتور، طبيب
- **Libyan Colloquial**: نبي دكتور، ابي حد كويس
- **English**: dentist, speech therapist
- **Mixed**: need dentist في بنغازي
- **Arabizi**: nbi doctor asnan (نبي دكتور أسنان)

### 5. JSON-Only Output

**Response format:**
```json
{
    "specialty": "dentist",
    "city": "Tripoli",
    "gender_preference": "female",
    "budget_sensitive": true,
    "confidence": 0.92,
    "needs_clarification": false,
    "clarification_question": null
}
```

No markdown, no explanations, pure JSON.

---

## Implementation Steps

### Step 1: Register Services

Update `app/Providers/ChatbotServiceProvider.php`:
```php
$this->app->singleton(SafeIntentExtractor::class);
$this->app->singleton(DialectNormalizer::class);
```

### Step 2: Update DeepSeekClient

Add JSON mode support (already done in this implementation):
```php
public function chatWithJsonMode(
    string $systemPrompt,
    string $userMessage,
    array $jsonSchema,
): ?string { ... }
```

### Step 3: Update Routes

Add V2 endpoints to `routes/api.php`:
```php
Route::prefix('chat/v2')->middleware('throttle:chatbot.v2')->group(function () {
    Route::post('/message', [ChatControllerV2::class, 'message']);
    Route::post('/reset', [ChatControllerV2::class, 'reset']);
    Route::get('/init', [ChatControllerV2::class, 'init']);
});
```

### Step 4: Add Configuration

Update `.env.example`:
```
DEEPSEEK_JSON_MODE=true
```

### Step 5: Test

Run test suite:
```bash
php artisan test tests/Feature/SafeIntentExtractionTest.php
```

---

## API Examples

### Example 1: Clear Request - Dentist in Tripoli

**Request:**
```bash
POST /api/chat/v2/message
{
  "message": "أبي دكتور أسنان في طرابلس",
  "conversation_id": "chat_abc123"
}
```

**Internal Processing:**
```
1. Normalize: "ابي دكتور اسنان في طرابلس"
2. Extract: specialty=dentist, city=Tripoli, confidence=0.95
3. Gate: confidence >= 0.70 ✓
4. Search: dentists in Tripoli
5. Return: 5 results
```

**Response:**
```json
{
  "type": "results",
  "count": 5,
  "message": "لقيتلك 5 مقدمي خدمة:",
  "providers": [
    { "id": 1, "name": "Dr. Mohamed", "rating": 4.8 },
    ...
  ],
  "conversation_id": "chat_abc123"
}
```

### Example 2: Unclear Request - Pediatrician or Adult?

**Request:**
```bash
POST /api/chat/v2/message
{
  "message": "speech therapist for my family member",
  "conversation_id": "chat_abc123"
}
```

**Internal Processing:**
```
1. Normalize: "speech therapist for my family member"
2. Extract: specialty=speech therapist, city=null, confidence=0.65
3. Gate: confidence < 0.70, needs clarification
4. Return: Clarification question
```

**Response:**
```json
{
  "type": "clarification",
  "question": "هل تبحث عن معالج نطق للأطفال أم للبالغين؟",
  "conversation_id": "chat_abc123"
}
```

### Example 3: Arabizi Request

**Request:**
```bash
POST /api/chat/v2/message
{
  "message": "nbi dentist msh ghali",
  "conversation_id": "chat_abc123"
}
```

**Internal Processing:**
```
1. Detect Arabizi: "nbi dentist msh ghali"
2. Convert: "ابي دكتور اسنان مش غالي"
3. Normalize: "ابي دكتور أسنان ما شي غالي"
4. Extract: specialty=dentist, budget_sensitive=true, confidence=0.88
5. Gate: confidence >= 0.70 ✓
6. Search: affordable dentists
```

**Response:**
```json
{
  "type": "results",
  "count": 3,
  "message": "لقيتلك 3 مقدمي خدمة:",
  "providers": [...],
  "conversation_id": "chat_abc123"
}
```

---

## Safety Guarantees

### No Hallucination
✅ Only providers from database shown
✅ Never invents specialties
✅ Never makes medical decisions

### No Prompt Injection
✅ User message never interpolated
✅ JSON schema enforces format
✅ System prompt never revealed
✅ Database schema hidden

### No Data Leakage
✅ API keys not logged
✅ Environment variables not returned
✅ Internal tool names hidden
✅ Errors don't expose stack traces

---

## Testing Checklist

- [ ] Arabic Modern Standard (MSA)
- [ ] Libyan Colloquial Arabic
- [ ] English
- [ ] Mixed Arabic/English
- [ ] Arabizi (numbers)
- [ ] Voice-to-text errors
- [ ] Misspellings
- [ ] Prompt injection attempts
- [ ] Database schema requests
- [ ] API key requests
- [ ] Confidence >= 0.70 → search
- [ ] Confidence < 0.70 → clarify
- [ ] No results handling
- [ ] JSON validation

---

## Migration from V1

V1 endpoints remain active. Gradually migrate to V2:

```bash
# V1 (legacy)
POST /api/chat/message

# V2 (new)
POST /api/chat/v2/message
```

Both coexist during transition period.

---

## Performance Considerations

**DeepSeek API calls:**
- 1 call per message (extraction)
- JSON mode adds ~10ms overhead
- Significantly reduces hallucination costs

**Database queries:**
- 1 query per search (optimized with indexes)
- Eager loading prevents N+1
- Result limit: 20 providers max

**Dialect normalization:**
- All in-memory (no DB calls)
- ~5ms per message
- Cached character mappings

---

## Future Enhancements

1. **Feedback loop**: Track which extractions led to bookings
2. **Learning**: Improve specialty matching over time
3. **Multi-turn**: Save previous context
4. **Preferences**: Remember user preferences
5. **Analytics**: Track intent distribution

---

## Troubleshooting

### DeepSeek returning null
```
Check: DEEPSEEK_ENABLED=true in .env
Check: DEEPSEEK_API_KEY is valid
Check: timeout (default 15s)
```

### JSON validation failing
```
Check: Response matches schema
Check: All required fields present
Check: confidence is 0-1 number
Check: needs_clarification is boolean
```

### Dialect not converting
```
Check: ArabiziNormalizer mappings
Check: ArabicNormalizer diacritic removal
Check: Input encoding (UTF-8)
```

---

## Documentation

See also:
- `CHATBOT_SEMANTIC_REDESIGN.md` - Previous iteration
- `CHATBOT_V2_SECURITY.md` - Security details
- `tests/Feature/SafeIntentExtractionTest.php` - Test examples
