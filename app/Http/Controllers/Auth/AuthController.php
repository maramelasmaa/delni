<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateOwnAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
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
}
