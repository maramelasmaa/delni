<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Notifications\Notification;
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
            ->favicon(asset('images/icon-192.png'))
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
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::hex('#F1620F'),
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::hex('#22C55E'),
                'warning' => Color::hex('#F59E0B'),
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

        if (class_exists(Notification::class)) {
            $panel->databaseNotifications();
        }

        return $panel;
    }
}
