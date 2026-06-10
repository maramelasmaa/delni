# Security Audit Findings
## Delni Marketplace Platform

**Date:** 2026-06-10  
**Classification:** Confidential — Internal Security Review  

---

## Critical Vulnerabilities (🔴 BLOCK DEPLOYMENT)

### SEC-001: Hardcoded Credentials in `.env`
**Severity:** 🔴 CRITICAL  
**CVSS Score:** 9.8 (Critical)  

**Location:** `.env` lines 54-55, 68-70
```env
MAIL_USERNAME=ab809a001@smtp-brevo.com
MAIL_PASSWORD=cAKnmMNPDszEQxW1
SUPER_ADMIN_PASSWORD=vtechcomingsoon5
```

**Vulnerability:**
- Plaintext credentials stored in version-controlled `.env` file
- If repo exposed (public, compromised account, CI/CD logs), attackers gain:
  - Email service access (send phishing emails, spam)
  - Admin panel access (full data breach, modify database)

**Exploitation Scenario:**
1. Attacker finds repo on GitHub/GitLab
2. Reads `.env` credentials
3. Logs into admin panel with super_admin password
4. Modifies reviews, deletes profiles, exports user data
5. Sends phishing emails via Brevo account

**Impact:**
- **Confidentiality:** HIGH (database exposure)
- **Integrity:** HIGH (data modification)
- **Availability:** HIGH (account lockouts, spam)

**Remediation:**
1. **Immediate:** Rotate all credentials in Brevo and app
   ```bash
   # Generate new password
   php artisan tinker
   > hash_hmac('sha256', uniqid(), 'your_key')
   ```
2. **Move secrets to `.env.local`** (not committed):
   ```bash
   git rm --cached .env
   echo ".env*" >> .gitignore
   mv .env .env.local
   cp .env.example .env  # Template only
   ```
3. **Audit version history:**
   ```bash
   git log --all --full-history -- ".env" | head -50
   git show <commit>:.env  # Check if exposed
   ```
4. **Regenerate if already in public repo:**
   - Use BFG Repo-Cleaner or git-filter-branch
   - Force push (consider security implications)

**Evidence:**
- File: `C:\laragon\www\delni\.env` (contains plaintext passwords)
- Not in `.env.example` (good), but committed `.env` is worse

**Priority:** ⚠️ IMMEDIATE

---

### SEC-002: APP_DEBUG=true in Production Configuration
**Severity:** 🔴 CRITICAL (if deployed)  
**CVSS Score:** 7.5 (High)  

**Location:** `.env` line 4
```env
APP_DEBUG=true
```

**Vulnerability:**
- Debug mode exposes sensitive information to all users
- Stack traces, SQL queries, environment variables leaked

**Information Disclosed:**
```
[Example Laravel debug screen]
- Full file paths (/var/www/delni/app/Models/User.php)
- SQL queries with values (SELECT * FROM users WHERE email = ?)
- Database structure and table names
- Route definitions and controller names
- Installed packages and versions
- Config values from config/app.php, config/database.php
- $_SERVER variables (sometimes)
```

**Exploitation Scenario:**
1. User accesses any endpoint that throws error
2. Laravel debug screen shows full stack trace
3. Attacker maps application structure:
   - Discovers User model at `app/Models/User.php`
   - Sees database tables: `users`, `profiles`, `reviews`
   - Finds obscure routes: `/admin`, `/provider-panel`
   - Learns package versions (target known CVEs)
4. Craft targeted exploit with knowledge of exact structure

**Impact:**
- **Confidentiality:** CRITICAL (full application map exposed)
- **Integrity:** MEDIUM (information gathering for further attacks)
- **Availability:** LOW

**Remediation:**
```env
# Production .env
APP_DEBUG=false
APP_ENV=production
```

**Verification:**
```bash
# Test on deployment
curl https://production.app/invalid-route
# Should return generic error page, not debug screen
```

**Priority:** ⚠️ IMMEDIATE (if in production)

---

## High-Severity Vulnerabilities (🟠 FIX BEFORE LAUNCH)

### SEC-003: Locale Parameter Injection
**Severity:** 🟠 HIGH  
**CVSS Score:** 6.5 (Medium)  

**Location:** `app/Http/Controllers/Public/FrontendController.php` lines 102-108

**Code:**
```php
public function switchLocale(string $locale, Request $request): RedirectResponse
{
    $request->session()->put('locale', 'ar');  // HARDCODED!
    Cookie::queue('locale', 'ar', 60 * 24 * 365);  // Ignores $locale
    return back();
}
```

**Vulnerability:**
- Route parameter `$locale` accepted but ignored
- Always sets to 'ar' regardless of input
- No validation on locale value

**Risk Analysis:**
1. **Unexpected Behavior:** Route `/locale/en` sets Arabic (confusing API)
2. **Potential XSS:** If hardcoding removed later without validation:
   ```php
   ->put('locale', $locale);  // Would accept any string!
   ```

**Exploitation Scenario:**
```
GET /locale/"><script>alert('xss')</script>
→ Sets session['locale'] = 'ar' (hardcoded, so XSS blocked)
→ But code is fragile, future dev might remove hardcoding
```

**Remediation:**
```php
public function switchLocale(string $locale, Request $request): RedirectResponse
{
    $allowedLocales = ['en', 'ar'];
    abort_if(!in_array($locale, $allowedLocales), 404);
    
    $request->session()->put('locale', $locale);
    Cookie::queue('locale', $locale, 60 * 24 * 365);
    return back();
}
```

**Priority:** HIGH (prevent future XSS regression)

---

### SEC-004: Database Query Stats Exposed in Views
**Severity:** 🟠 HIGH  
**CVSS Score:** 7.2 (High)  

**Location:** `app/Services/PublicFrontendService.php` lines 364-398

**Code:**
```php
public function home(): array
{
    $queries = [];
    DB::listen(fn($query) => $queries[] = [
        'sql' => $query->sql,
        'time' => $query->time,
    ]);
    
    // ... code ...
    
    return [
        'data' => $data,
        'queryStats' => $this->queryStats($queries),  // EXPOSED
    ];
}

private function queryStats(array $queries): array
{
    return [
        'count' => count($queries),
        'queries' => $queries,  // Raw SQL exposed!
    ];
}
```

**Vulnerability:**
- Raw SQL queries passed to Blade templates
- Queries contain table names, column names, WHERE conditions
- If debug toolbar renders or view echoes `$queryStats`, SQL exposed

**Information Disclosed:**
```
SELECT * FROM reviews WHERE profile_id = ? AND is_flagged = ?
SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND ends_at > ?
SELECT ... FROM portfolio_items WHERE profile_id = ? ORDER BY sort_order
```

**Exploitation Scenario:**
1. Attacker finds debug toolbar or view source
2. Reads SQL queries in `$queryStats`
3. Maps database structure:
   - Column names: `is_flagged`, `ends_at`, `sort_order`
   - Table names: `reviews`, `subscriptions`, `portfolio_items`
   - Relationship types: profile_id joins reviews
4. Craft targeted SQL injection or IDOR attacks

**Impact:**
- **Confidentiality:** HIGH (schema reconnaissance)
- **Integrity:** MEDIUM (enables targeted attacks)

**Remediation:**
```php
public function home(): array
{
    $queries = config('app.debug') ? $this->recordQueries() : [];
    
    return [
        'data' => $data,
        'queryStats' => config('app.debug') ? $this->queryStats($queries) : null,
    ];
}
```

**Priority:** HIGH (prevent reconnaissance)

---

### SEC-005: N+1 Query on Review User Relationship
**Severity:** 🟠 HIGH (Performance-based DoS)  
**CVSS Score:** 7.1 (High)  

**Location:** `app/Services/PublicFrontendService.php` line 243

**Code:**
```php
public function detail(Profile $profile): array
{
    return [
        'profile' => $profile,
        'approvedReviews' => $profile->approvedReviews,  // No ->with('user')
        'portfolio' => $profile->portfolio,
    ];
}
```

**Blade Template (likely):**
```blade
@foreach($approvedReviews as $review)
    <div class="review">
        <p>{{ $review->user->name }}</p>  <!-- Triggers query -->
        <p>{{ $review->rating }} stars</p>
    </div>
@endforeach
```

**Vulnerability:**
- Reviews loaded without eager-loading user relationship
- Each review access to `$review->user` triggers 1 SELECT query
- 100 reviews = 100 extra queries

**Exploitation Scenario:**
```
1. Provider has 100 reviews
2. User requests /providers/john-doe
3. Expected queries: 2 (profile + reviews)
4. Actual queries: 104 (profile + reviews + 100x user loads)
5. Response time: 2.5s instead of 50ms
6. Attacker floods requests → DB connection exhaustion → DoS
```

**Performance Impact:**
| Reviews | Expected Queries | Actual Queries | Est. Time |
|---------|-----------------|----------------|-----------|
| 10 | 3 | 13 | 150ms |
| 50 | 3 | 53 | 600ms |
| 100 | 3 | 103 | 1.2s |
| 500 | 3 | 503 | 6s |

**Remediation:**
```php
'approvedReviews' => $profile->approvedReviews()->with('user')->get(),
```

**Verification:**
```php
// Test in tinker
DB::listen(fn($q) => dump($q->sql));
$profile = Profile::find(1);
count($profile->approvedReviews);  // Should be 1 query, not N+1
```

**Priority:** HIGH (scale risk)

---

### SEC-006: Missing Input Validation on Review Flag Reason
**Severity:** 🟠 MEDIUM-HIGH  
**CVSS Score:** 6.3 (Medium)  

**Location:** `app/Http/Controllers/Public/ReviewController.php` line 36

**Code:**
```php
public function flag(Review $review, Request $request): RedirectResponse
{
    $review->update([
        'is_flagged' => true,
        'flagged_by' => $request->user()->id,
        'flagged_reason' => $request->string('reason')->value(),  // No max!
    ]);
    return back();
}
```

**Vulnerability:**
- `reason` field accepts unlimited string length
- No max_length validation
- No type validation (assumes string)

**Exploitation Scenario:**
```
POST /reviews/123/flag
{
    "reason": "AAAA..." * 1000000  // 1MB of text
}
```

**Impact:**
1. **Database Bloat:** `flagged_reason` column grows unbounded
   - 10,000 flagged reviews × 1MB = 10GB+ table bloat
   - Slow queries on reviews table
2. **Memory Exhaustion:** `$request->string('reason')->value()` loads entire string into memory
3. **DoS:** Repeated large-text requests exhaust DB resources

**Evidence:**
- No validation rule on `reason` in controller
- Column definition likely `TEXT` or `VARCHAR(255)` (unchecked)

**Remediation:**
```php
// In controller or Form Request:
'reason' => ['required', 'string', 'max:1000'],
```

**Verification:**
```bash
# Test that large input is rejected
curl -X POST http://localhost/reviews/1/flag \
  -d 'reason=AAA...AAA' \  # 2000 characters
# Should return 422 Unprocessable Entity
```

**Priority:** HIGH (DoS risk)

---

## Medium-Severity Vulnerabilities (🟡 MONITOR)

### SEC-007: Soft-Deleted Profiles Not Filtered in Visibility Query
**Severity:** 🟡 MEDIUM  
**CVSS Score:** 5.3 (Medium)  

**Location:** `app/Services/ProfileVisibilityService.php` line 177

**Code:**
```php
public function applyVisibleQuery(Builder $query): Builder
{
    return $query
        ->whereNull('users.deleted_at')  // ✓ User soft-deletes excluded
        // ❌ MISSING: ->whereNull('profiles.deleted_at')
        ->where('users.is_active', true)
        ->where('users.is_suspended', false)
        ->whereExists(...);  // Subscription check
}
```

**Vulnerability:**
- Soft-deleted users are filtered (good)
- Soft-deleted profiles are NOT filtered (bad)
- Provider deletes profile → profile still visible in marketplace

**Exploitation Scenario:**
```
1. Provider has active profile: "John's Services"
2. Provider deletes profile (soft-delete)
3. User searches marketplace
4. "John's Services" still appears in results (deleted_at = not null, but no filter)
5. User clicks profile → 404 or 500 error
```

**Data Integrity Issue:**
- User experiences confusion
- Marketplace appears broken
- Admin cannot distinguish deleted vs. active profiles easily

**Remediation:**
```php
->whereNull('profiles.deleted_at')
```

**Verification:**
```bash
# In tinker
$profile = Profile::first();
$profile->delete();  // Soft delete

# Query without filter
Profile::count();  // Includes deleted

# Query with filter (after fix)
Profile::whereNull('deleted_at')->count();  // Excludes deleted
```

**Priority:** MEDIUM (data integrity)

---

### SEC-008: Review Creation Auto-Approves (No Moderation Queue)
**Severity:** 🟡 MEDIUM  
**CVSS Score:** 5.7 (Medium)  

**Location:** `app/Http/Controllers/Public/ReviewController.php` line 19

**Code:**
```php
public function store(Profile $profile, CreateReviewRequest $request): RedirectResponse
{
    Review::create([
        'profile_id' => $profile->id,
        'user_id' => $request->user()->id,
        'status' => ReviewStatus::APPROVED,  // Hard-coded!
        'rating' => $request->integer('rating'),
    ]);
    return back()->with('success', 'Review submitted!');
}
```

**Design Decision (As Documented):**
- Comments indicate "Reviews are intentionally live by default"
- No admin approval queue
- Spam/false reviews go live immediately

**Vulnerability:**
- Malicious user can create 100 fake reviews/day
- All go live instantly, damage reputation immediately
- No way to batch-delete spam reviews (no moderation queue)

**Exploitation Scenario:**
```
1. Competitor creates account
2. Posts 10 1-star reviews with fake comments:
   - "Terrible service, delayed 3 months"
   - "Took my money and never delivered"
   - "Worst experience ever"
3. All reviews live immediately
4. Provider's rating drops from 4.8 to 2.1
5. Lost business for weeks until admin manually moderates
```

**Impact:**
- **Reputation:** Provider reputation destroyed in hours
- **Business:** Lost revenue during moderation period
- **Trust:** Marketplace credibility damaged

**Mitigation Strategies:**
1. **Abuse Detection:** Flag suspicious reviews (same user, same provider, low-effort text)
2. **Review Queue:** Option to require approval for first 5 reviews from new users
3. **IP Reputation:** Check if review poster has history of flagged reviews
4. **CAPTCHA:** On review submission for new users
5. **Monitoring:** Alert on review-creation spike (10+ reviews in 1 hour from 1 user)

**Note:** This is a design choice, not necessarily a security bug. But requires active abuse monitoring.

**Priority:** MEDIUM (design decision, but monitor for abuse)

---

## Low-Severity Findings (🟢 NICE-TO-HAVE)

### SEC-009: No Rate Limiting on Profile Views
**Severity:** 🟢 LOW  
**CVSS Score:** 3.7 (Low)  

**Location:** `routes/web.php` lines 35-38

**Route Definition:**
```php
Route::get('/providers/{slug}', [FrontendController::class, 'detail'])
    // ❌ No ->middleware('throttle:...')
```

**Vulnerability:**
- Any user can request profile pages unlimited times
- No rate limiting on `/providers/{slug}`

**Exploitation Scenario:**
```
1. Scraper downloads all 10,000 provider profiles
2. No rate limit
3. Scraper enumerates marketplace:
   - Discovers all providers and their services
   - Extracts pricing, experience, reviews
4. Competitor uses data to undercut pricing
5. Platform loses competitive advantage
```

**Impact:**
- **Confidentiality:** LOW (public data already listed)
- **Business:** Medium (market intelligence extraction)
- **Availability:** LOW (not heavy traffic impact)

**Remediation:**
```php
Route::get('/providers/{slug}', [FrontendController::class, 'detail'])
    ->middleware('throttle:60,1');  // 60 requests per minute per IP
```

**Note:** Routes like `POST /reviews` already have throttling — this is inconsistent.

**Priority:** LOW (informational scraping risk)

---

### SEC-010: Contact Model Dead Code
**Severity:** 🟢 LOW  
**CVSS Score:** 1.0  

**Location:** `app/Models/Contact.php`

**Code:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
}
```

**Issue:**
- Empty model stub
- No relationships, no properties, never used in codebase
- Leftover from project initialization

**Impact:**
- Code clutter
- Maintenance confusion ("Is this used?")
- Future developers waste time investigating

**Remediation:**
- Delete file if not planned
- Or document planned use in README

**Priority:** LOW (cleanup)

---

## Authorization & Access Control Review

### Authentication Flow
✓ **Good:**
- Session-based auth (secure, not JWT)
- Password hashing via Laravel (bcrypt)
- Email verification on registration
- Password reset flow with time-limited tokens (60 min)
- Failed login attempt tracking

❌ **Issues:**
- No 2FA/MFA
- No session timeout (relies on cookie expiry)
- No IP whitelisting for admin
- No audit log of admin logins

### Authorization Flow
✓ **Good:**
- Role-based access control (super_admin, provider, user)
- Policy-based authorization
- Explicit `hasRole()` checks prevent unintended access

❌ **Issues:**
- Before-hook bypass on super_admin could silently grant unintended access if new roles added
- Not all mutation points require authorization checks (e.g., profile suspension)

### Data Access Control
✓ **Good:**
- Profiles only visible with active subscription
- Reviews unique per user per provider (prevents duplicate reviews)
- Providers can only edit own profiles

❌ **Issues:**
- Deleted profiles still visible (see SEC-007)
- No field-level authorization (admin sees all fields always)

---

## Secrets & Credential Management

### Hardcoded Secrets Found
1. **MAIL_USERNAME** = `ab809a001@smtp-brevo.com` (line 54)
2. **MAIL_PASSWORD** = `cAKnmMNPDszEQxW1` (line 55)
3. **SUPER_ADMIN_PASSWORD** = `vtechcomingsoon5` (line 68)

### Secrets NOT Found (Good)
✓ No API keys in code  
✓ No database passwords in source  
✓ No private keys or certificates  
✓ No auth tokens hardcoded  

### Recommendations
1. Use environment variables ONLY (✓ correct pattern)
2. Use `config()` helper in code (✓ correct)
3. Add secrets to `.env.local` (never commit)
4. Use `.env.example` as template only
5. Rotate credentials periodically
6. Never log secrets (check logging config)

---

## Summary: Top 5 Security Priorities

| Priority | Issue | Action |
|----------|-------|--------|
| 🔴 1 | Hardcoded credentials | Rotate + move to .env.local |
| 🔴 2 | APP_DEBUG=true | Set false in production |
| 🟠 3 | N+1 on reviews | Add .with('user') |
| 🟠 4 | QueryStats exposed | Gate behind config check |
| 🟡 5 | Soft-deleted profiles visible | Filter deleted_at |

---

**Report Generated:** 2026-06-10  
**Next Review:** After critical issues remediated
