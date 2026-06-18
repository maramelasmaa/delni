<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Provider\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ProviderPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('provider')
            ->path('provider')
            ->brandLogo(fn () => new HtmlString('
                <div style="display: flex; align-items: center; gap: 10px; flex-direction: row;">
                    <img src="'.asset('images/logo.jpg').'" style="height: 36px; border-radius: 8px; display: inline-block;" alt="دلني" />
                    <span style="font-size: 20px; font-weight: 950; color: #0b1a34; letter-spacing: -0.5px; display: inline-block; font-family: Cairo, sans-serif;">
                        دلني
                    </span>
                </div>
            '))
            ->darkModeBrandLogo(fn () => new HtmlString('
                <div style="display: flex; align-items: center; gap: 10px; flex-direction: row;">
                    <img src="'.asset('images/logo.jpg').'" style="height: 36px; border-radius: 8px; display: inline-block;" alt="دلني" />
                    <span style="font-size: 20px; font-weight: 950; color: #ffffff; letter-spacing: -0.5px; display: inline-block; font-family: Cairo, sans-serif;">
                        دلني
                    </span>
                </div>
            '))
            ->brandName('دلني')
            ->login(Login::class)
            ->homeUrl('/provider/dashboard')
            ->profile()
            ->colors([
                'primary' => Color::hex('#F1620F'),
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::hex('#22C55E'),
                'warning' => Color::hex('#F59E0B'),
            ])
            ->discoverResources(in: app_path('Filament/Provider/Resources'), for: 'App\Filament\Provider\Resources')
            ->discoverPages(in: app_path('Filament/Provider/Pages'), for: 'App\Filament\Provider\Pages')
            ->discoverWidgets(in: app_path('Filament/Provider/Widgets'), for: 'App\Filament\Provider\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'account.locked',
                'user.active',
                'user.not_suspended',
                'provider',
                'provider.active_subscription',
            ])
            ->darkMode()
            ->breadcrumbs(false);
    }
}
