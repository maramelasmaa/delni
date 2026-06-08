<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\User\UpdateOwnAccountRequest;
use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Services\AccountSecurityService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private AccountSecurityService $security) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $email = $request->string('email')->lower()->toString();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->security->recordFailedAttempt($email);

            return back()->withErrors([
                'email' => __('messages.credentials_no_match'),
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if ($this->security->isLocked($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('messages.account_locked'),
            ])->onlyInput('email');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('messages.account_deactivated'),
            ])->onlyInput('email');
        }

        if ($user->is_suspended) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('messages.account_suspended'),
            ])->onlyInput('email');
        }

        // ⚠️ SECURITY: Providers and admins cannot login from public site
        if ($user->hasRole('provider') || $user->hasRole('super_admin')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'لا يمكن تسجيل الدخول عبر هذه الصفحة. الرجاء استخدام لوحة التحكم المخصصة.',
            ])->onlyInput('email');
        }

        $this->security->recordSuccessfulLogin($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showAccountEditForm(): View|RedirectResponse
    {
        $user = Auth::user();

        // Providers cannot access public account edit
        if ($user->hasRole('provider')) {
            abort(403);
        }

        return view('auth.account-edit', [
            'user' => $user,
        ]);
    }

    public function updateAccount(UpdateOwnAccountRequest $request): RedirectResponse
    {
        $user = Auth::user();

        // Providers should not use this route
        if ($user->hasRole('provider')) {
            abort(403);
        }

        $user->update($request->validated());

        return back()->with('success', __('messages.account_updated'));
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->string('email')->lower()->toString();
        $user = User::where('email', $email)->first();

        // Always respond identically to prevent user enumeration
        if ($user === null) {
            return back()->with('status', __('auth.password_reset_link_sent'));
        }

        $token = Password::createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        Mail::queue(new PasswordResetMail(
            email: $user->email,
            resetLink: $resetUrl,
            userName: $user->name,
        ));

        return back()->with('status', __('auth.password_reset_link_sent'));
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        $token = $request->string('token');
        $email = $request->string('email');

        if (! $token || ! $email) {
            return redirect('/forgot-password')->withErrors([
                'token' => __('auth.invalid_reset_link'),
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->updatePassword($password);
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', __('auth.password_reset_success'));
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => __($status),
        ]);
    }
}
