# Provider Panel — PHASE 1 Corrected Implementation Plan

**Status:** Ready to code. All corrections verified against actual models.

---

## 1. Corrections to PHASE 0 Plan

### ✅ Ownership Checks (CORRECTED)

**Wrong:** `auth()->user()->profile_id === request()->route('profile')->id`

**Correct:**
```php
// Profile ownership
$profile->user_id === auth()->id()

// Portfolio ownership
$portfolioItem->profile->user_id === auth()->id()

// Credential ownership
$credential->profile->user_id === auth()->id()

// Link ownership
$link->profile->user_id === auth()->id()

// Review visibility (read-only)
$review->profile->user_id === auth()->id()
```

### ✅ Field Names (CORRECTED)

| WRONG | CORRECT | Notes |
|-------|---------|-------|
| `provider_type_id` | `provider_type` | String field, no FK to ProviderType model yet |
| `avatar` | `logo` | Profile model uses `logo`, not `avatar` |
| view count | N/A | No `views` field in ProfileStats |
| profile views | N/A | No tracking for view count |

### ✅ Actual Dashboard Fields (VERIFIED)

Provider dashboard PHASE 1 can safely display:
- `Profile.is_complete` → completeness indicator
- `ProfileStats.rating_avg`, `ProfileStats.reviews_count` → from stats (read-only)
- `ProfileStats.is_featured`, `ProfileStats.is_homepage_featured` → placement status (read-only)
- `Subscription.is_active`, `Subscription.ends_at` → active sub status (read-only)
- `PortfolioItem` count via `$profile->portfolioItems()->count()`
- `ProviderCredential` count via `$profile->credentials()->count()`
- `ProviderLink` count via `$profile->links()->count()`
- `ProfileVisibilityService::getVisibility($profile)` → visibility status

Do NOT reference: views, impressions, analytics, unconfirmed fields.

### ✅ Middleware Scope (CORRECTED)

**Do NOT create:**
- `provider.owns-profile`
- `provider.owns-resource`

**Only add route middleware:**
- `auth`
- `provider.role` (new) — checks `hasRole('provider')`
- `user.active` (exists)
- `user.not_suspended` (exists)
- `account.locked` (exists)

**Ownership enforcement via:**
- Filament Policies (ProviderProfilePolicy)
- `getEloquentQuery()` scope in Filament pages
- Form access checks in pages

### ✅ Filament Panel Location (CORRECTED)

**Correct path:**
```
app/Providers/Filament/ProviderPanelProvider.php
```

NOT `app/Filament/Provider/PanelProvider.php`

Pages/Resources can live under:
```
app/Filament/Provider/Pages/
app/Filament/Provider/Resources/
```

### ✅ Onboarding Redirect (CORRECTED)

After provider sets password via `/onboarding/set-password`:
- Redirect to `/provider/dashboard` (provider panel)
- NOT to `/dashboard` (public user dashboard)
- NOT to `/register`

Check/update `app/Http/Controllers/Auth/OnboardingController.php` line ~75.

---

## 2. PHASE 1 Scope (CORRECTED)

### Build ONLY:

**Files to create:**
1. `app/Providers/Filament/ProviderPanelProvider.php` — Filament panel config
2. `app/Filament/Provider/Pages/Dashboard.php` — Dashboard shell
3. `app/Http/Middleware/ProviderRole.php` — Role check middleware
4. `app/Policies/ProviderProfilePolicy.php` — Ownership policy
5. `tests/Feature/ProviderPanelPhase1Test.php` — Test suite (13 tests)

**Files to update:**
1. `config/filament.php` or panel provider registration — register ProviderPanelProvider
2. `app/Http/Controllers/Auth/OnboardingController.php` — redirect to `/provider/dashboard` after password set
3. `routes/web.php` or panel routes — ensure `/provider` is accessible

### Dashboard PHASE 1 shows (read-only):

```php
// Profile section
Profile.business_name
Profile.provider_type
Profile.is_complete → completeness badge

// Visibility section
ProfileVisibilityService::getVisibility($profile) → visibility status

// Subscription section
Subscription.is_active → active/inactive badge
Subscription.plan_id → plan name (if active)
Subscription.ends_at → expiry date (if active)
"No active subscription" if null

// Stats section (null-safe)
ProfileStats.rating_avg ?? 0 → rating
ProfileStats.reviews_count ?? 0 → reviews count
ProfileStats.is_featured ?? false → featured status
ProfileStats.is_homepage_featured ?? false → homepage featured status

// Portfolio section
$profile->portfolioItems()->count() → "0 projects" etc.

// Links section
$profile->activeLinks()->count() → "0 links" etc.

// Credentials section
$profile->credentials()->count() → "0 credentials" etc.
```

### PHASE 1 does NOT include:

- Profile edit form
- Portfolio CRUD
- Credentials CRUD
- Links CRUD
- Subscription management
- Review management
- Settings/preferences
- Email change
- Analytics
- Payment flow

---

## 3. Filament Panel Config

**File:** `app/Providers/Filament/ProviderPanelProvider.php`

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Support\ServiceProvider;

class ProviderPanelProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public static function register(): Panel
    {
        return Panel::make()
            ->id('provider')
            ->path('provider')
            ->login()
            ->colors([
                'primary' => '#F1620F', // match app brand
            ])
            ->discoverPages(
                in: app_path('Filament/Provider/Pages'),
                for: 'App\\Filament\\Provider\\Pages'
            )
            ->discoverResources(
                in: app_path('Filament/Provider/Resources'),
                for: 'App\\Filament\\Provider\\Resources'
            )
            ->middleware([
                'auth',
                'provider.role',
                'user.active',
                'user.not_suspended',
                'account.locked',
            ])
            ->authGuard('web')
            ->brand(config('app.name', 'دلني'));
    }
}
```

Then register in `config/filament.php` or `app/Providers/FilamentServiceProvider.php`:
```php
ProviderPanelProvider::register(),
```

---

## 4. Middleware: ProviderRole

**File:** `app/Http/Middleware/ProviderRole.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProviderRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole('provider')) {
            abort(403, 'Only providers can access this area.');
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php` (or `bootstrap/app.php` in Laravel 13):
```php
'provider.role' => \App\Http\Middleware\ProviderRole::class,
```

---

## 5. Policy: ProviderProfilePolicy

**File:** `app/Policies/ProviderProfilePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return false; // Providers never list all profiles
    }

    public function view(User $user, Profile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false; // Profile created on user creation
    }

    public function update(User $user, Profile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    public function delete(User $user, Profile $profile): bool
    {
        return false; // Providers cannot delete own profile
    }

    public function restore(User $user, Profile $profile): bool
    {
        return false;
    }

    public function forceDelete(User $user, Profile $profile): bool
    {
        return false;
    }
}
```

Register in `app/Providers/AuthServiceProvider.php`:
```php
protected $policies = [
    Profile::class => ProviderProfilePolicy::class,
];
```

---

## 6. Dashboard Page

**File:** `app/Filament/Provider/Pages/Dashboard.php`

```php
<?php

namespace App\Filament\Provider\Pages;

use App\Models\Profile;
use App\Services\ProfileVisibilityService;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Dashboard';
    protected static string $view = 'filament.provider.pages.dashboard';

    public Profile $profile;

    public function mount(): void
    {
        $this->profile = auth()->user()->profile;
        
        if (!$this->profile) {
            abort(403, 'Provider profile not found.');
        }

        // Authorization via policy
        $this->authorize('view', $this->profile);
    }
}
```

**View:** `resources/views/filament/provider/pages/dashboard.blade.php`

```blade
<x-filament-panels::page>
    <div class="grid gap-6">
        <!-- Profile Section -->
        <x-filament::section heading="{{ __('messages.public.profile') }}">
            <div class="space-y-2">
                <p><strong>{{ __('messages.public.business_name') }}</strong></p>
                <p>{{ $profile->business_name ?? __('messages.public.not_provided') }}</p>
                
                <p class="pt-3"><strong>{{ __('messages.public.contact_information') }}</strong></p>
                <p>{{ $profile->provider_type ?? __('messages.public.not_provided') }}</p>
                
                <div class="pt-3">
                    <x-filament::badge :color="$profile->is_complete ? 'success' : 'warning'">
                        {{ $profile->is_complete ? __('messages.public.complete') : __('messages.public.incomplete') }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        <!-- Subscription Section -->
        <x-filament::section heading="{{ __('messages.public.subscription') }}">
            @if($profile->user->activeSubscription)
                <div class="space-y-2">
                    <p><strong>{{ __('messages.public.plan') }}</strong></p>
                    <p>{{ $profile->user->activeSubscription->plan->name ?? 'N/A' }}</p>
                    
                    <p class="pt-3"><strong>{{ __('messages.public.expiry') }}</strong></p>
                    <p>{{ $profile->user->activeSubscription->ends_at->format('Y-m-d') }}</p>
                    
                    <div class="pt-3">
                        <x-filament::badge color="success">
                            {{ __('messages.public.active') }}
                        </x-filament::badge>
                    </div>
                </div>
            @else
                <p>{{ __('messages.public.no_active_subscription') }}</p>
            @endif
        </x-filament::section>

        <!-- Stats Section -->
        <x-filament::section heading="{{ __('messages.public.statistics') }}">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">{{ __('messages.public.rating') }}</p>
                    <p class="text-2xl font-bold">{{ $profile->stats?->rating_avg ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">{{ __('messages.public.reviews') }}</p>
                    <p class="text-2xl font-bold">{{ $profile->stats?->reviews_count ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">{{ __('messages.public.portfolio') }}</p>
                    <p class="text-2xl font-bold">{{ $profile->portfolioItems()->count() }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
```

**Safe for Arabic:** All strings use `{{ __('...') }}` translation keys.

---

## 7. Test Suite

**File:** `tests/Feature/ProviderPanelPhase1Test.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderPanelPhase1Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_redirects_to_provider_login(): void
    {
        $response = $this->get('/provider/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [302, 401]));
    }

    #[Test]
    public function normal_user_denied_provider_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [403]));
    }

    #[Test]
    public function provider_allowed_on_dashboard(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertSuccessful();
    }

    #[Test]
    public function suspended_provider_blocked(): void
    {
        $provider = $this->createProvider();
        $provider->update(['is_suspended' => true]);

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [403]));
    }

    #[Test]
    public function inactive_provider_blocked(): void
    {
        $provider = $this->createProvider();
        $provider->update(['is_active' => false]);

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [403]));
    }

    #[Test]
    public function super_admin_blocked_unless_provider_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->getStatusCode(), [403]));
    }

    #[Test]
    public function provider_cannot_access_admin(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)->get('/cp/admin');
        // Should be 302 redirect or 403
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    #[Test]
    public function dashboard_renders_with_complete_profile(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertSuccessful();
        // View should contain profile data
        $response->assertViewHas('profile', $provider->profile);
    }

    #[Test]
    public function dashboard_renders_with_missing_stats(): void
    {
        $provider = $this->createProvider();
        // Do NOT create ProfileStats

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        // Should NOT 500, should show safe defaults
        $this->assertSuccessful();
    }

    #[Test]
    public function dashboard_renders_with_missing_subscription(): void
    {
        $provider = $this->createProvider();
        // Do NOT create subscription

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        // Should NOT 500, should show inactive message
        $this->assertSuccessful();
    }

    #[Test]
    public function dashboard_renders_with_missing_profile(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        // User has no profile

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        // Should abort 403 gracefully
        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function route_cache_does_not_break_provider_routes(): void
    {
        $this->artisan('route:cache')->assertSuccessful();

        $provider = $this->createProvider();
        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertSuccessful();
    }

    #[Test]
    public function config_cache_does_not_break_provider_routes(): void
    {
        $this->artisan('config:cache')->assertSuccessful();

        $provider = $this->createProvider();
        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertSuccessful();
    }
}
```

**Test helper** (add to `tests/TestCase.php` or create trait):
```php
protected function createProvider(): User
{
    $user = User::factory()->create();
    $user->assignRole('provider');
    $user->profile()->create([
        'business_name' => 'Test Provider',
        'provider_type' => 'individual',
        'is_complete' => true,
    ]);
    return $user->refresh();
}
```

---

## 8. Registration/Routing Changes

### Update OnboardingController

**File:** `app/Http/Controllers/Auth/OnboardingController.php` (around line 60-80)

Find this line:
```php
// Current (probably redirects to /dashboard)
return redirect()->route('dashboard');
```

Change to:
```php
// CORRECTED
if (auth()->user()->hasRole('provider')) {
    return redirect()->route('filament.provider.pages.dashboard');
}
return redirect()->route('dashboard');
```

### Register Filament Provider Panel

In your Filament panel provider registration (check `config/filament.php` or `app/Providers/FilamentServiceProvider.php`):

```php
use App\Providers\Filament\ProviderPanelProvider;

public function boot(): void
{
    FilamentManager::registerPanelProviders([
        AdminPanelProvider::class,
        ProviderPanelProvider::class, // ADD THIS
    ]);
}
```

Or if using auto-discovery, ensure `ProviderPanelProvider` is in `app/Providers/Filament/` directory and named `*PanelProvider.php`.

---

## 9. Summary Table

| Aspect | PHASE 0 Plan | PHASE 1 Correction |
|--------|--------------|-------------------|
| Ownership | `user->profile_id` (WRONG) | `profile->user_id === auth()->id()` ✅ |
| Avatar field | `avatar` | `logo` ✅ |
| Provider type FK | `provider_type_id` | `provider_type` (string) ✅ |
| Filament panel path | `app/Filament/Provider/` | `app/Providers/Filament/` ✅ |
| Middleware | Generic `provider.owns-*` | Policy + getEloquentQuery() ✅ |
| Dashboard data | Invented analytics | Confirmed DB fields only ✅ |
| Tests | 9 tests | 13 tests (more null-safety) ✅ |

---

## 10. Files to Create (PHASE 1)

```
✅ app/Providers/Filament/ProviderPanelProvider.php
✅ app/Filament/Provider/Pages/Dashboard.php
✅ app/Http/Middleware/ProviderRole.php
✅ app/Policies/ProviderProfilePolicy.php
✅ tests/Feature/ProviderPanelPhase1Test.php
✅ resources/views/filament/provider/pages/dashboard.blade.php
```

---

## 11. Pre-Code Checklist

Before I start coding:

- [ ] Verify `app/Providers/Filament/` directory exists (or will create)
- [ ] Verify `app/Filament/Provider/` directory will be created
- [ ] Confirm ProviderPanelProvider auto-discovery location
- [ ] Confirm OnboardingController redirect target
- [ ] Confirm role/middleware naming in codebase
- [ ] Confirm all 13 tests match project conventions

**Ready to build PHASE 1?**

Reply with:
- ✅ if all corrections are confirmed and we proceed to coding
- Specific adjustments needed before coding
