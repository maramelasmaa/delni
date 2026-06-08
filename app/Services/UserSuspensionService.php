<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserSuspensionService
{
    public function suspend(User $user, string $reason): void
    {
        if (Auth::id() === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'You cannot suspend your own account.',
            ]);
        }

        $user->update([
            'is_suspended' => true,
            'suspension_reason' => $reason,
            'suspended_at' => now(),
            'suspended_by' => Auth::id(),
        ]);
    }

    public function reinstate(User $user, string $reason): void
    {
        $user->update([
            'is_suspended' => false,
            'reinstatement_reason' => $reason,
            'reinstated_at' => now(),
            'reinstated_by' => Auth::id(),
        ]);
    }
}
