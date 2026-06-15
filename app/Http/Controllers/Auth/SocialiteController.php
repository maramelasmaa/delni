<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteController extends Controller
{
    public function __construct(private GoogleAuthService $googleAuth) {}

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = $this->googleAuth->getGoogleUser();
            $user = $this->googleAuth->findOrCreateUser($googleUser);

            if (! $user->is_active) {
                Auth::logout();

                return redirect()->route('login')
                    ->withErrors(['email' => __('messages.account_deactivated')]);
            }

            if ($user->is_suspended) {
                Auth::logout();

                return redirect()->route('login')
                    ->withErrors(['email' => __('messages.account_suspended')]);
            }

            $this->googleAuth->assignUserRole($user);
            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        } catch (Throwable $exception) {
            Log::warning('Google OAuth login failed', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('login')
                ->withErrors(['google' => __('messages.google_auth_failed')]);
        }
    }
}
