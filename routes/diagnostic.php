<?php

// Quick diagnostic routes - DELETE BEFORE PRODUCTION
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/diagnostic/admin-test', function () {
        $admin = User::where('email', 'vtech@gmail.com')->first();

        if (! $admin) {
            return 'Admin not found';
        }

        return [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
            'has_super_admin_role' => $admin->hasRole('super_admin'),
            'all_roles' => $admin->getRoleNames()->toArray(),
            'is_active' => $admin->is_active,
            'is_suspended' => $admin->is_suspended,
            'email_verified' => $admin->email_verified_at !== null,
        ];
    });

    Route::get('/diagnostic/manually-login/{id}', function ($id) {
        $user = User::findOrFail($id);
        auth()->login($user);

        return "Logged in as: {$user->email}. Visit /cp/admin";
    });
});
