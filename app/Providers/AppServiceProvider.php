<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProviderCredential;
use App\Models\ProviderLink;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use App\Observers\PortfolioImageObserver;
use App\Observers\PortfolioItemObserver;
use App\Observers\ProfileObserver;
use App\Observers\ProfilePublicCacheObserver;
use App\Observers\ProviderAssetLimitObserver;
use App\Observers\ReviewObserver;
use App\Observers\SubcategoryObserver;
use App\Observers\UserObserver;
use App\Policies\ActivityLogPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CityPolicy;
use App\Policies\PortfolioImagePolicy;
use App\Policies\PortfolioItemPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\ProviderCredentialPolicy;
use App\Policies\ProviderLinkPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\SubcategoryPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Prevent N+1 queries by detecting lazy-loaded relationships in development
        Model::preventLazyLoading(! app()->isProduction());

        // Behind a TLS-terminating proxy, force HTTPS only when the configured app URL is HTTPS.
        // Local Docker uses APP_ENV=production with an HTTP localhost URL for deployment parity.
        if (app()->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        User::observe(UserObserver::class);
        Profile::observe(ProfileObserver::class);
        Profile::observe(ProfilePublicCacheObserver::class);
        ProviderLink::observe(ProviderAssetLimitObserver::class);
        PortfolioItem::observe(ProviderAssetLimitObserver::class);
        PortfolioItem::observe(PortfolioItemObserver::class);
        PortfolioImage::observe(ProviderAssetLimitObserver::class);
        PortfolioImage::observe(PortfolioImageObserver::class);
        Review::observe(ReviewObserver::class);
        Subcategory::observe(SubcategoryObserver::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Subcategory::class, SubcategoryPolicy::class);
        Gate::policy(City::class, CityPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(PortfolioItem::class, PortfolioItemPolicy::class);
        Gate::policy(ProviderLink::class, ProviderLinkPolicy::class);
        Gate::policy(PortfolioImage::class, PortfolioImagePolicy::class);
        Gate::policy(ProviderCredential::class, ProviderCredentialPolicy::class);

        View::composer(['components.provider-card', 'components.public.provider-card'], function ($view): void {
            $user = request()->user();
            $favoriteProfileIds = [];

            if ($user !== null) {
                $favoriteProfileIds = request()->attributes->get('favorite_profile_ids');

                if (! is_array($favoriteProfileIds)) {
                    $favoriteProfileIds = $user->favorites()
                        ->pluck('profile_id')
                        ->all();

                    request()->attributes->set('favorite_profile_ids', $favoriteProfileIds);
                }
            }

            $view->with('favoriteProfileIds', $favoriteProfileIds);
        });

        Event::listen(Attempting::class, function (Attempting $event) {
            try {
                if (DB::connection()->getDatabaseName()) {
                    $user = User::where('email', $event->credentials['email'] ?? null)->first();

                    if ($user && $user->is_suspended) {
                        throw ValidationException::withMessages([
                            'email' => __('auth.account_suspended'),
                        ]);
                    }
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                // Database unavailable, skip check
            }
        });

        Event::listen(Login::class, function (Login $event) {
            try {
                if (DB::connection()->getDatabaseName() && $event->user->is_suspended) {
                    Auth::logout();
                    request()->session()->invalidate();
                    request()->session()->regenerateToken();
                }
            } catch (\Exception $e) {
                // Database unavailable, skip check
            }
        });

        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinutes(15, 10)
                ->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('onboarding.show', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('onboarding.set-password', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Search API rate limiter - attached to GET /api/profiles/search
        RateLimiter::for('search', function (Request $request): Limit {
            if ($request->user() !== null) {
                return Limit::perMinute(60)->by('search|user:'.$request->user()->id);
            }

            return Limit::perMinute(20)->by('search|ip:'.$request->ip());
        });

        // Review creation rate limiter - attached to POST /providers/{slug}/review
        // Paired with EnsureReviewEligible middleware for redundant protection
        RateLimiter::for('reviews.create', function (Request $request): Limit {
            return Limit::perDay(10)->by('reviews.create|user:'.$request->user()?->id);
        });

        // Review flagging rate limiter - attached to POST /reviews/{id}/flag
        RateLimiter::for('reviews.flag', function (Request $request): Limit {
            return Limit::perDay(20)->by('reviews.flag|user:'.$request->user()?->id);
        });

        // Email verification resend limiter - currently unused, available for future use
        RateLimiter::for('verification.resend', function (Request $request): Limit {
            return Limit::perHour(3)->by('verification.resend|user:'.$request->user()?->id);
        });
    }
}
