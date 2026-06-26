<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Browser-based password reset, linked from the reset email so the flow works
 * everywhere (desktop webmail included) — not only inside the mobile app.
 * Mirrors the API reset logic in Api\V1\AuthController::resetPassword().
 */
class ResetPasswordWebController extends Controller
{
    public function show(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => (string) $request->query('token', ''),
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            [
                'email' => mb_strtolower($validated['email']),
                'password' => $validated['password'],
                'password_confirmation' => $request->string('password_confirmation')->value(),
                'token' => $validated['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();
            },
        );

        if ($status !== Password::PasswordReset) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.invalid_reset_link')]);
        }

        return view('auth.reset-password', ['success' => true]);
    }
}
