<?php

declare(strict_types=1);

namespace App\Filament\Provider\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (session('subscription_expired')) {
            Notification::make()
                ->title(__('messages.subscription_expired_login_blocked'))
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
