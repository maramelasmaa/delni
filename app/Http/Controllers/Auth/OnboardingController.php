<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Models\OnboardingToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function showSetPasswordForm(Request $request, string $token): View|RedirectResponse
    {
        $token = trim((string) $token);

        $validationResult = $this->validateToken($token);
        if ($validationResult instanceof RedirectResponse) {
            return $validationResult;
        }

        $onboardingToken = $validationResult;

        if ($request->user() && $request->user()->id !== $onboardingToken->user_id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.set-password', [
            'token' => $token,
            'email' => $onboardingToken->user->email,
        ]);
    }

    public function setPassword(SetPasswordRequest $request): RedirectResponse
    {
        $token = trim((string) $request->validated('token'));

        $validationResult = $this->validateToken($token, isFormSubmission: true);
        if ($validationResult instanceof RedirectResponse) {
            return $validationResult;
        }

        $tokenString = $validationResult->token;

        // Re-acquire the token inside a transaction with a row lock to prevent
        // two simultaneous requests from both succeeding with the same token.
        $success = DB::transaction(function () use ($tokenString, $request): bool {
            $locked = OnboardingToken::where('token', $tokenString)
                ->whereNull('used_at')
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->user || $locked->isExpired()) {
                return false;
            }

            $locked->user->updatePassword((string) $request->string('password'));
            $locked->markAsUsed();

            return true;
        });

        if (! $success) {
            return back()
                ->withErrors(['token' => __('auth.onboarding_link_used')])
                ->withInput();
        }

        // Redirect to login page (not authenticated yet)
        return redirect()->route('filament.provider.auth.login')
            ->with('status', __('auth.password_set_success'));
    }

    private function validateToken(string $token, bool $isFormSubmission = false): OnboardingToken|RedirectResponse
    {
        $errorRedirect = function (string $key) use ($isFormSubmission): RedirectResponse {
            $errors = ['token' => __("auth.onboarding_link_{$key}")];

            return $isFormSubmission
                ? back()->withErrors($errors)->withInput()
                : redirect('/login')->withErrors($errors);
        };

        $onboardingToken = OnboardingToken::where('token', $token)->first();

        if (! $onboardingToken || ! $onboardingToken->user) {
            return $errorRedirect('invalid');
        }

        if ($onboardingToken->used_at !== null) {
            return $errorRedirect('used');
        }

        if ($onboardingToken->isExpired()) {
            return $errorRedirect('expired');
        }

        return $onboardingToken;
    }
}
