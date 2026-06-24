<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProviderRootController
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        if ($user !== null && $user->hasRole('provider') && $user->is_active && ! $user->is_suspended) {
            return redirect('/provider/dashboard');
        }

        if ($user === null) {
            return redirect('/provider/login');
        }

        throw new HttpException(403, 'Unauthorized to access provider panel');
    }
}
