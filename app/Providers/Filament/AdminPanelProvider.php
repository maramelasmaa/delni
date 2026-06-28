<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path(env('FILAMENT_PATH', 'cp/admin'))
            ->brandLogo(fn () => $this->brandLogoHtml('#0F172A'))
            ->darkModeBrandLogo(fn () => $this->brandLogoHtml('#F1F5F9'))
            ->brandName('دلني')
            ->favicon('data:,')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::hex('#1E40AF'),
                'danger' => Color::hex('#EF4444'),
                'gray' => Color::Slate,
                'info' => Color::hex('#60A5FA'),
                'success' => Color::hex('#10B981'),
                'warning' => Color::hex('#E1AD01'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                'admin',
            ])
            ->navigationGroups([
                __('filament.nav.providers'),
                __('filament.nav.marketplace'),
                __('filament.nav.community'),
                __('filament.nav.system'),
            ])
            ->darkMode()
            ->breadcrumbs(true);

        return $panel;
    }

    private function brandLogoHtml(string $textColor): HtmlString
    {
        return new HtmlString('
            <div style="display:flex;align-items:center;gap:10px;flex-direction:row;">
                <img src="'.asset('images/photo_2026-06-22_23-21-55.jpg').'" style="height:36px;width:36px;border-radius:10px;display:inline-block;object-fit:cover;" alt="دلني" />
                <span style="font-size:20px;font-weight:950;color:'.$textColor.';letter-spacing:-0.5px;display:inline-block;font-family:system-ui,sans-serif;">
                    دلني
                </span>
            </div>
        ');
    }
}
