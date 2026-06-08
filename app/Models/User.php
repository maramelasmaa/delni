<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'is_suspended',
        'suspension_reason',
        'suspended_at',
        'suspended_by',
        'reinstated_at',
        'reinstated_by',
        'reinstatement_reason',
        'password_changed_at',
        'failed_login_attempts',
        'last_failed_login_at',
        'locked_until',
        'security_flagged',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_suspended' => 'boolean',
        'suspended_at' => 'datetime',
        'reinstated_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'failed_login_attempts' => 'integer',
        'last_failed_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'security_flagged' => 'boolean',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('is_active', true)
            ->whereDate('ends_at', '>=', now());
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function reinstatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reinstated_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function onboardingTokens(): HasMany
    {
        return $this->hasMany(OnboardingToken::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isProvider(): bool
    {
        return $this->hasRole('provider');
    }

    public function isPublicUser(): bool
    {
        return $this->hasRole('user');
    }

    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    public function requiresPasswordChange(): bool
    {
        return false;
    }

    public function updatePassword(string $newPassword, ?string $oldPassword = null): void
    {
        if ($oldPassword !== null && ! Hash::check($oldPassword, $this->password)) {
            throw new \InvalidArgumentException('Current password is incorrect.');
        }
        $this->password = Hash::make($newPassword);
        $this->password_changed_at = now();
        $this->save();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active || $this->is_suspended) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasRole('super_admin'),
            'provider' => $this->hasRole('provider'),
            default => false,
        };
    }
}
