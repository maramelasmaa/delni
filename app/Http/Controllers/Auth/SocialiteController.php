<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function __construct(private GoogleAuthService $googleAuth) {}

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = $this->googleAuth->getGoogleUser();
            $user = $this->googleAuth->findOrCreateUser($googleUser);

            if (!$user->is_active || $user->is_suspended) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => __('messages.account_suspended')]);
            }

            $this->googleAuth->assignUserRole($user);
            Auth::login($user, remember: true);

            return redirect()->intended(route('home'));
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['google' => __('messages.google_auth_failed')]);
        }
    }
}
